<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'version',
        'size',
        'developer',
        'license',
        'platform',
        'icon',
        'screenshot',
        'changelog',
        'features',
        'download_url',
        'download_url_external',
        'download_mirrors',
        'torrent_url',
        'torrent_file_path',
        'magnet_link',
        'has_torrent',
        'featured',
        'popular',
        'published',
        'downloads',
        'rating',
        'reviews_count',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'download_mirrors' => 'array',
            'has_torrent' => 'boolean',
            'featured' => 'boolean',
            'popular' => 'boolean',
            'published' => 'boolean',
            'downloads' => 'integer',
            'rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'released_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            cache()->forget('sitemap_xml');
        });

        static::deleted(function () {
            cache()->forget('sitemap_xml');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ApplicationVersion::class)->orderBy('released_at', 'desc');
    }

    public function currentVersion()
    {
        return $this->hasOne(ApplicationVersion::class)->where('is_current', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ApplicationImage::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('approved', true);
    }

    public function rootApprovedReviews(): HasMany
    {
        return $this->hasMany(Review::class)
            ->where('approved', true)
            ->whereNull('parent_id')
            ->with('approvedReplies');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopePopular($query)
    {
        return $query->where('popular', true);
    }

    public function scopeOrderedByDownloads($query)
    {
        return $query->orderBy('downloads', 'desc');
    }

    public function scopeOrderedByRating($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    public function scopeOrderedByNewest($query)
    {
        return $query->orderBy('released_at', 'desc');
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim($term ?? '');
        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('developer', 'like', "%{$term}%")
                ->orWhereHas('category', function ($cat) use ($term) {
                    $cat->where('name', 'like', "%{$term}%");
                })
                ->orWhereHas('tags', function ($tag) use ($term) {
                    $tag->where('name', 'like', "%{$term}%");
                });
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getFormattedDownloadsAttribute(): string
    {
        if ($this->downloads >= 1000000) {
            return number_format($this->downloads / 1000000, 1).'M';
        }
        if ($this->downloads >= 1000) {
            return number_format($this->downloads / 1000, 1).'K';
        }

        return (string) $this->downloads;
    }

    public function getFormattedSizeAttribute(): string
    {
        return $this->size ?? 'N/A';
    }

    public function getIconUrlAttribute(): string
    {
        if ($this->icon) {
            if (str_starts_with($this->icon, 'http://') || str_starts_with($this->icon, 'https://')) {
                return $this->icon;
            }

            return asset('storage/'.$this->icon);
        }

        return asset('images/default-icon.svg');
    }

    public function getScreenshotUrlAttribute(): ?string
    {
        if ($this->screenshot) {
            return asset('storage/'.$this->screenshot);
        }

        $firstImage = $this->images->first();
        if ($firstImage) {
            return $firstImage->url;
        }

        return null;
    }

    public function getWhatsNewAttribute(): ?string
    {
        return $this->changelog ?? $this->currentVersion?->changelog;
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads');
    }

    public function recalculateRating(): void
    {
        $avg = $this->approvedReviews()->avg('rating');
        $count = $this->approvedReviews()->count();

        $this->update([
            'rating' => $avg ? round($avg, 2) : 0,
            'reviews_count' => $count,
        ]);
    }
}
