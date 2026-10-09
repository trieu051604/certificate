<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCertificationMail;

class SendCertificationMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $personCertificationTest;
    public function __construct($personCertificationTest)
    {
        $this->personCertificationTest = $personCertificationTest;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->personCertificationTest->isPass()) return;

        try {
            Mail::to($this->personCertificationTest->email)->send(new SendCertificationMail([
                'name' => mb_strtoupper($this->personCertificationTest->fullname, 'UTF-8'),
                'registration_data' => $this->personCertificationTest->registration_data ?? [],
                'completed_date' => $this->personCertificationTest->created_at->format('d/m/Y'),
                'school' => $this->personCertificationTest->name_school ?: ($this->personCertificationTest->school?->school ?? ''),
                'province' => $this->personCertificationTest->province?->province ?? '',
                'course' => $this->personCertificationTest->certification->course,
                'certification' => $this->personCertificationTest->certification,
                "title" => $this->personCertificationTest->certification->title,
                "content" => $this->personCertificationTest->certification->content,
                'subject' => $this->personCertificationTest->certification->subject,
            ]));
            $this->personCertificationTest->status_send_mail = 200;
            $this->personCertificationTest->msg_send_mail = 'SendMail Success';
            $this->personCertificationTest->save();
        } catch (\Throwable $th) {
            $this->personCertificationTest->status_send_mail = 500;
            $this->personCertificationTest->msg_send_mail = 'SendMail error';
            $this->personCertificationTest->save();
            \Log::info($th->getMessage());
        }
    }
}
