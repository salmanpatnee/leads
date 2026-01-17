<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    /** @use HasFactory<\Database\Factories\SiteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_name',
        'domain',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model and attach event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Site $site): void {
            if (empty($site->api_key)) {
                $site->api_key = (string) Str::uuid();
            }
            $site->domain = strtolower($site->domain);
        });
    }
}
