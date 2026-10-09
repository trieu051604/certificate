<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class CertificatePdf
{
    public function make(array $info): PdfDocument
    {
        $course = $info['certification'] ?? null;
        $background = $course?->certificateBackground()
            ?? config('certificates.default_background', 'css/submit-certification/cer.jpg');
        $imagePath = public_path($background);
        $layout = $course?->certificateTemplate?->layout ?? 'school';
        if (!is_file($imagePath)) {
            $imagePath = public_path(config('certificates.default_background', 'css/submit-certification/cer.jpg'));
            $layout = 'school';
        }

        $backgroundUrl = 'file://'.str_replace('\\', '/', $imagePath);
        $css = file_get_contents(public_path('css/submit-certification/generate-image.css'));
        // The unused Dancing font is not bundled; DejaVu Sans supports Vietnamese in PDFs.
        $css = preg_replace('/@font-face\s*\{.*?\}/s', '', $css);
        $css .= "\n@page { margin: 0; }";

        return Pdf::loadView('send-mail.image-certification', [
            'infoSendMail' => $info,
            'certificateCss' => $css,
            'certificateBackground' => $backgroundUrl,
            'certificateLayout' => $layout,
            'certificateOrganization' => CertificateData::organization($info),
            'certificateCourseTitle' => $course?->certificate_template_id
                ? $info['course'] : (($info['title'] ?? '') ?: $info['course']),
        ])->setPaper([0, 0, 1536, 1086], 'portrait');
    }

}
