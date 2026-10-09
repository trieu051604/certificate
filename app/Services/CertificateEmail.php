<?php

namespace App\Services;

class CertificateEmail
{
    public static function render(string $template, array $info, bool $html = false): string
    {
        $values = [
            '[name]' => $info['name'] ?? '',
            '[unit]' => CertificateData::organization($info) ?: 'Không cung cấp',
            '[course]' => $info['course'] ?? '',
            '[cer]' => ($info['certification'] ?? null)?->certificateTemplate?->name
                ?? (($info['title'] ?? '') ?: ($info['course'] ?? '')),
            '[completed_date]' => $info['completed_date'] ?? date('d/m/Y'),
        ];

        // Replace once so placeholders inside submitted values remain literal text.
        $values = array_map(fn ($value) => $html ? e($value) : (string) $value, $values);
        $result = strtr($template, $values);
        return $html ? $result : preg_replace('/[\r\n]+/', ' ', $result);
    }
}
