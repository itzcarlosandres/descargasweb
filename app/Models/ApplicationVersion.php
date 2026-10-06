<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'version',
        'changelog',
        'download_url',
        'download_mirrors',
        'torrent_url',
        'torrent_file_path',
        'size',
        'is_current',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'download_mirrors' => 'array',
            'is_current' => 'boolean',
            'released_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
