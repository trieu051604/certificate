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
use App\Services\CertificateEmail;

class SendCertificationMail extends Mailable
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
        $subject = trim($this->infoSendMail['subject'] ?? '') ?: config('certificates.email_subject');
        return new Envelope(
            subject: CertificateEmail::render($subject, $this->infoSendMail),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        $content = trim($this->infoSendMail['content'] ?? '') ?: config('certificates.email_content');

        return new Content(
            markdown: 'send-mail.certification',
            with: [
                'htmlContent' => CertificateEmail::render($content, $this->infoSendMail, true)
            ],
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
