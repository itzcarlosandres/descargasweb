<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokenLinkReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'type',
        'notes',
        'ip_address',
        'status',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }
}
