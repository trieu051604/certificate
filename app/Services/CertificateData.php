<?php

namespace App\Services;

use Illuminate\Support\Str;

class CertificateData
{
    public static function organization(array $info): string
    {
        if (isset($info['organization']) && is_string($info['organization'])) {
            return $info['organization'];
        }

        // Custom form keys are generated; the saved label identifies the unit field.
        foreach ($info['registration_data'] ?? [] as $field) {
            if (!is_array($field) || !is_string($field['value'] ?? null)) continue;
            if (in_array(Str::slug($field['label'] ?? ''), ['don-vi', 'ten-don-vi', 'don-vi-cong-tac'], true)) {
                return trim($field['value']);
            }
        }

        return $info['school'] ?? '';
    }
}
