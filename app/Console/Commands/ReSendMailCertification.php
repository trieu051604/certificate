<?php

namespace App\Console\Commands;

use App\Mail\SendCertificationMail;
use App\Models\PersonCertificationTest;
use Cache;
use Illuminate\Console\Command;
use Mail;

class ReSendMailCertification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:re-send-mail-certification';

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
        $cacheKey = 're_send_mail_certification';

        if (!Cache::add($cacheKey, true, 20 * 60)) {
            return;
        }

        try {
            $personCertifications = PersonCertificationTest::where('status_send_mail', 500)
            ->whereHas('certification', function ($query) {
                $query->whereColumn('person_certification_test.point', '>=', 'certification_test.passing_score');
            })
            ->get();

            if (count($personCertifications) > 0) {
                foreach ($personCertifications as $personCertification) {
                    try {
                        Mail::to($personCertification->email)->send(new SendCertificationMail([
                            'name' => $personCertification->fullname,
                            'registration_data' => $personCertification->registration_data ?? [],
                            'completed_date' => $personCertification->created_at->format('d/m/Y'),
                            'school' => $personCertification->name_school ?: ($personCertification->school?->school ?? ''),
                            'province' => $personCertification->province?->province ?? '',
                            'course' => $personCertification->certification->course,
                            'certification' => $personCertification->certification,
                            "title" => $personCertification->certification->title,
                            "content" => $personCertification->certification->content,
                            'subject' => $personCertification->certification->subject,
                        ]));
                        $personCertification->status_send_mail = 200;
                        $personCertification->msg_send_mail = 'SendMail Success';
                        $personCertification->save();
                    } catch (\Throwable $th) {
                        $personCertification->status_send_mail = 500;
                        $personCertification->msg_send_mail = 'SendMail error';
                        $personCertification->save();
                        \Log::info($th->getMessage());
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::info("=== ReSendMailCertification Error ===");
            \Log::info($th->getMessage());
            \Log::info("=====================================");
        } finally {
            Cache::forget($cacheKey);
        }
    }
}
