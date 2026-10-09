<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationTest extends Model
{
    use HasFactory;
    protected $table = 'certification_test';
    protected $fillable = [
        'slug', 'course', 'status', 'passing_score', 'title', 'content', 'image', 'subject','date_expire', 'certificate_template_id', 'registration_form_id'
    ];

    public static function availableSlug(string $name, ?int $exceptId = null): string
    {
        $base = substr(\Illuminate\Support\Str::slug($name) ?: 'khoa-hoc', 0, 230);
        $slug = $base;
        $suffix = 2;
        while (static::where('slug', $slug)->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    public function registrationForm()
    {
        return $this->belongsTo(RegistrationForm::class);
    }

    public function registrationFields(): array
    {
        return $this->registrationForm?->fields ?? \App\Services\RegistrationFields::defaults($this->type === 'QUIZGAME');
    }

    public function certificateBackground(): string
    {
        return $this->certificateTemplate?->background
            ?? config('certificates.default_background', 'css/submit-certification/cer.jpg');
    }

    public function questions()
    {
        return $this->hasMany(QuestionCertificationTest::class);
    }

    public function persons()
    {
        return $this->hasMany(PersonCertificationTest::class);
    }

    public function person_game()
    {
        return $this->hasMany(PersonGameCertificationTest::class);
    }
}
