<?php

namespace App\Console\Commands;

use App\Jobs\InsertLeadCRM;
use App\Models\Lead;
use App\Models\Major_Group;
use App\Models\User;
use App\Models\ShareLead as ShareLeadModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ShareLead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:share-lead';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $majorGroups = Major_Group::all();
        $cacheKey = 'share_lead_cron';
        
        if (!Cache::add($cacheKey, true, 20 * 60)) {
            return;
        }

        foreach ($majorGroups as $majorGroup) {
            $sales = $majorGroup->users()->where('type', 'MANAGER')->get();

            // lấy all leads của các ngành
            $allLeads = Lead::whereIn('major_id', $majorGroup->majors()->pluck('id')->toArray())
                                // ->when($majorGroup->majors()->whereNull('id')->exists(), function ($query) {
                                //     $query->orWhereNull('major_id');
                                // })
                                ->where('status', 'PENDING')
                                // ->where('type', 'DEFAULT')
                                ->get();
            
            if (count($sales)> 0 && count($allLeads) > 0) {

                // lấy lượt chia mới nhất
                $TurnShareLeadNewestByMajorGroup = $majorGroup->share_lead()
                                                        // ->where('list_user_miss', '!=', NULL)
                                                        // ->where('list_user_miss', '!=', '')
                                                        ->orderBy('id', 'desc')
                                                        ->first();

                $shareLead = new ShareLeadModel();
                $shareLead->major_group_id = $majorGroup->id;
                // $shareLead->save();

                try {
                    //chia lead thiếu
                    $allLeadAfterShareMissed = $TurnShareLeadNewestByMajorGroup ? $this->shareLeadMissed($allLeads, $TurnShareLeadNewestByMajorGroup) : $allLeads;

                    if ($allLeadAfterShareMissed && count($allLeadAfterShareMissed) > 0) {

                        //chia lead hiện tại
                        $saleMissed = $this->shareLeadCurrent(collect($allLeadAfterShareMissed), $sales, $shareLead);

                        // lưu danh sách sale thiếu từ mảng => chuỗi
                        $shareLead->list_user_miss = implode(',', $saleMissed);
                        $shareLead->status = 'SUCCESS';
                        $shareLead->messange = 'Share Lead Success';
                    }
                } catch (\Exception $e) {
                    //throw $th;
                    \Log::channel('crm')->info('ERROR SHARE LEAD' . $e->getMessage());
                    $shareLead->status = 'ERROR';
                    $shareLead->messange = $e->getMessage() . '- File:' . $e->getFile() . '- Line:' . $e->getLine();
                } finally {
                    // id chưa chọn ngành
                    if (($majorGroup->id != 5 || ($majorGroup->id == 5 && $shareLead->status == 'SUCCESS')) && $shareLead->status) $shareLead->save();
                    Cache::forget($cacheKey);
                }   
            }
        }
        // try {
        //     dispatch(new InsertLeadCRM());
        // } catch (\Exception $e) {
        //     \Log::channel('crm')->info('CALL JOB ERROR InsertLeadCRM' . $e->getMessage());
        //     Cache::forget($cacheKey);
        // }

        Cache::forget($cacheKey);
    }
    public function shareLeadMissed($leads, $TurnShareLeadNewestByMajorGroup)
    {
        // lấy sale bị thiếu từ lượt share gần nhất ở cột list_user_miss (là một chuổi id sale)

        $saleMissed = $TurnShareLeadNewestByMajorGroup->list_user_miss;

        if ($saleMissed) {

            $saleMissedArray = explode(',', $saleMissed);

            if (count($leads) < count($saleMissedArray)) return NULL;

            $sales = User::whereIn('id', $saleMissedArray)->where('type', 'MANAGER')->get();
            if ($sales) {
                // số tách mảng để chia lead thiếu
                $numberDivide = count($sales);

                // trường hợp số leads nhỏ hơn sale
                $arrayHanleMissed = count($leads) == $numberDivide ? $leads : collect($leads)->slice(0, $numberDivide)->all();
                foreach ($sales as $index => $sale) {
                    $leadHanleShareMissed = $arrayHanleMissed[$index];
                    $leadHanleShareMissed->status = 'SHORTAGE';
                    $leadHanleShareMissed->sale_id = $sale->id;
                    $leadHanleShareMissed->share_lead_id = $TurnShareLeadNewestByMajorGroup->id;
                    $leadHanleShareMissed->save();
                }
                if (count($leads) == $numberDivide) {
                    $TurnShareLeadNewestByMajorGroup->update(['list_user_miss' => ""]);
                    return NULL;
                } else {
                    return collect($leads)->slice($numberDivide)->all();
                }
            }
        }
        return $leads;
    }

    public function shareLeadCurrent($leads, $sales, $shareLead): array
    {
        // chia mảng nhỏ lead theo số sale
        $salesMissed = [];

        $numberLeadShare = ceil(count($leads) / count($sales));
        $allLeadChucks = $this->splitCollection($leads, $sales);

        // var_dump($allLeadChucks);

        foreach ($sales as $index => $sale) {
            // so sánh 2 số lượng lead được chia và số lượng lead phải chia
            if (isset($allLeadChucks[$index])) {
                $hanleLeads = $allLeadChucks[$index];

                if (count($hanleLeads) != (int) $numberLeadShare) $salesMissed[] = $sale->id;

                // share lead
                foreach ($hanleLeads as $lead) {
                    $lead->status = 'DONE';
                    $lead->sale_id = $sale->id;
                    $lead->share_lead_id = $shareLead->id;
                    $lead->save();
                }
            } else {
                $salesMissed[] = $sale->id;
            }
        }

        return $salesMissed;
    }

    function splitCollection($leads, $sales) {
        if (count($leads) < count($sales)) {
            $countLeadShare = 1;
            $leadShareChuck = $leads->chunk($countLeadShare);
        } else {
            $countLeadShare = floor(count($leads) / count($sales));
            $countLeadExcesses = count($leads) % count($sales);

            if (count($leads) == 1) {
                $leadShareChuck[0][] = $leads->first();
            } else if ($countLeadExcesses !== 0) {
                $leadShareChuck = $leads->slice(0, -$countLeadExcesses)->chunk($countLeadShare);
                $leadsExcesses = $leads->slice(-$countLeadExcesses)->reverse()->values();

                foreach ($leadsExcesses as $index => $lead) {
                    if (isset($leadShareChuck[$index])) $leadShareChuck[$index][] = $lead;
                } 
            } else {
                $leadShareChuck = $leads->chunk($countLeadShare);
            }
        }   
        return $leadShareChuck;
    }
}
