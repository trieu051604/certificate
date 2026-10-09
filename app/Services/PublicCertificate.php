<?php

namespace App\Services;

use App\Models\PersonCertificationTest;
use Illuminate\Support\Facades\URL;

class PublicCertificate
{
    public static function result(PersonCertificationTest $student): array
    {
        $passed = (bool) $student->isPass();
        return [
            'id' => $student->id,
            'fullname' => $student->fullname,
            'course' => $student->certification->course,
            'course_slug' => $student->certification->slug,
            'point' => (int) $student->point,
            'total_questions' => $student->certification->questions()->count(),
            'isPass' => $passed,
            'status_send_mail' => $student->status_send_mail === null ? null : (int) $student->status_send_mail,
            'msg_send_mail' => $student->msg_send_mail,
            'completed_date' => $student->created_at->format('d/m/Y'),
            'organization' => CertificateData::organization(['registration_data' => $student->registration_data ?? [], 'school' => $student->name_school]),
            'certificate_name' => $student->certification->certificateTemplate?->name ?? $student->certification->title,
            'download_url' => $passed ? URL::temporarySignedRoute(
                'api.certification.download', now()->addHour(), ['studentId' => $student->id]
            ) : null,
        ];
    }
}
