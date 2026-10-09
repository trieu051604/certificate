<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\Lead;
use App\Models\Major;
use App\Models\Major_Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $leads;
    protected $import;
    private $countDuplicate = 1;
    /**
     * Create a new job instance.
     */
    public function __construct($leads, Import $import)
    {
        $this->leads = $leads;
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $majors = Major::all();
            $majorGroups = Major_Group::all();
            $majorConvert = [];
            foreach ($majors as $major) {
                $majorConvert[trim(strtolower($major->major))] = $major->id;
            }

            // $leadError = [];

            foreach ($majorGroups as $majorGroup) {
                $listMajorInGroup = $majorGroup->majors()->pluck('id')->toArray();
                $leadByMajorGroup = $this->leads->filter(function($lead) use ($listMajorInGroup, $majorConvert) {
                    $majorLead = isset($lead[3]) ? $majorConvert[trim(strtolower($lead[3]))] : NULL;
                    return isset($majorLead) && $majorLead && in_array($majorLead, $listMajorInGroup);
                });
                foreach ($leadByMajorGroup as $index => $lead) {
                    $majorLead = $majorConvert[trim(strtolower($lead[3]))];
                    $typeLead = $index % 2 == 0 ? 'DEFAULT' : 'PERFORMANCE';
                    $this->saveLead($lead, $majorLead, $typeLead);
                    // $leadError[] = $lead[4];
                }
            }
            
            // $leadErrorShow = $this->leads->filter(function($lead) use ($leadError) {
            //     return !in_array($lead[4], $leadError);
            // });
            
            // if (count($leadError) > 0) {
            //     \Log::info('                               ');
            //     \Log::info('===============================');
            //     \Log::info('List Lead Error:');
            //     \Log::info($leadErrorShow);
            //     \Log::info('===============================');
            //     \Log::info('                               ');
            // }

        } catch (\Exception $e) {
            \Log::info('Import Lead: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            $this->import->status = 'ERROR';
            $this->import->messange = $e->getMessage();
            $this->import->save();
            
        }

    }

    public function checkDuplicate($phone)
    {
        return Lead::where('phone', $phone)->first();
    }

    public function saveLead($lead, $majorConvert, $typeLead)
    {
        try {
            DB::beginTransaction();
            if ($leadDuplicate = $this->checkDuplicate($lead[4])) {
                $this->import->increment('duplicate', 1);
                $leadDuplicate->count_duplicate = $leadDuplicate->count_duplicate + $this->countDuplicate;
                $leadDuplicate->save();
            } else {
                $schoolInfo = explode(" - ", $lead[2]);
                if (count($schoolInfo) > 1) {
                    $schoolId = $schoolInfo[0];
                    $provinceId = $schoolInfo && isset($schoolInfo[2]) ? $schoolInfo[2] : NULL;
                } else {
                    $schoolId = NULL;
                    $provinceId = $schoolInfo[0];
                }
                $source = explode(" - ", $lead[9]);
                Lead::create([
                    'fullname' => $lead[0],
                    'birth_day' => $lead[1],
                    'school_id' => $schoolId,
                    'major_id' => $majorConvert,
                    'phone' => $lead[4],
                    'email' => $lead[5],
                    'link_facebook' => $lead[6],
                    'address' => $lead[7],
                    'time_contact' => $lead[8],
                    'source' => $source && isset($source[1]) ? $source[1] : NULL,
                    'note' => $lead[10],
                    'province_id' => $provinceId,
                    'import_id' => $this->import->id,
                    'type' => $typeLead
                ]);
            }
            $this->import->status = 'SUCCESS';
            $this->import->messange = 'SUCCESS';
            $this->import->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->import->status = 'ERROR';
            $this->import->messange = $this->import->messange . 'SĐT: ' . $lead[4] . ' bị lỗi ' . $e->getMessage() . '\n';
            $this->import->save();
        }
    }
}
