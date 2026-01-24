<?php

namespace App\Models;

use App\Services\EmailFlagDetector;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'form_name',
        'form_data',
        'status',
        'ip_address',
        'user_agent',
        'submitted_at',
        'flag_reason',
        'flagged_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'submitted_at' => 'datetime',
            'flagged_at' => 'datetime',
            'status' => 'string',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->autoFlagIfNeeded();
        });
    }

    /**
     * Get the site that this lead belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Auto-flag lead if email matches test/fake/spam patterns
     */
    private function autoFlagIfNeeded(): void
    {
        // Only auto-flag if not already manually flagged
        if ($this->flag_reason) {
            return;
        }

        $email = $this->extractEmail();
        if (! $email) {
            return;
        }

        $detector = new EmailFlagDetector;
        $flagReason = $detector->detect($email);

        if ($flagReason) {
            $this->flag_reason = $flagReason;
            $this->flagged_at = now();
        }
    }

    /**
     * Extract email from form data
     */
    private function extractEmail(): ?string
    {
        $formData = $this->form_data;

        if (! is_array($formData)) {
            return null;
        }

        $emailKeys = ['email', 'e-mail', 'email_address', 'contact_email', 'sender_email', 'your-email'];

        foreach ($emailKeys as $key) {
            if (isset($formData[$key]) && filter_var($formData[$key], FILTER_VALIDATE_EMAIL)) {
                return $formData[$key];
            }
        }

        // Try to find any valid email in the form data
        foreach ($formData as $value) {
            if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
        }

        return null;
    }
}
