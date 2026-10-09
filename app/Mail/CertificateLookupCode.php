<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CertificateLookupCode extends Mailable
{
    public function __construct(public string $code) {}

    public function build(): static
    {
        return $this->subject('Mã xác minh tra cứu chứng chỉ')->view('send-mail.lookup-code');
    }
}
