<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'audience',
        'user_id',
        'is_active',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: only active and currently visible notifications.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: filter by audience role.
     */
    public function scopeForAudience($query, ?string $role)
    {
        return $query->where(function ($q) use ($role) {
            // Broadcast notifications (no specific user target)
            $q->where(function ($inner) use ($role) {
                $inner->whereNull('user_id')
                    ->where(function ($aq) use ($role) {
                        $aq->where('audience', 'all');
                        if ($role) {
                            $aq->orWhere('audience', $role);
                        }
                    });
            });
            // Per-user targeted notifications
            if (auth()->check()) {
                $q->orWhere('user_id', auth()->id());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
