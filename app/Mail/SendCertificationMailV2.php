<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Services\CertificatePdf;

class SendCertificationMailV2 extends Mailable
{
    use Queueable, SerializesModels;
    protected $infoSendMail;
    /**
     * Create a new message instance.
     */
    public function __construct($infoSendMail)
    {
        //
        $this->infoSendMail = $infoSendMail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Chúc mừng hoàn thành khóa học  "' . $this->infoSendMail['course'] . '"',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'send-mail.certification-v2',
            with: [
                'name' => $this->infoSendMail['name'],
                'course' => $this->infoSendMail['course'],
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(CertificatePdf::class)->make($this->infoSendMail);

        // Đính kèm tệp PDF
        return [
            Attachment::fromData(fn () => $pdf->output(), 'certification.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
