<?php

namespace App\Models;

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
        ];
    }

    /**
     * Get the site that this lead belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
