<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_ARTICLE         = 'article';
    const TYPE_TOS             = 'tos';
    const TYPE_SHIPPING_INFO   = 'shipping_info';
    const TYPE_RETURN_POLICY   = 'return_policy';

    const STATIC_TYPES = [
        self::TYPE_TOS,
        self::TYPE_SHIPPING_INFO,
        self::TYPE_RETURN_POLICY,
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'author_id',
        'updated_by',
        'type',
        'title',
        'slug',
        'body',
        'excerpt',
        'thumbnail',
        'tags',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'tags'          => 'array',
        'published_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Content $content) {
            if (empty($content->slug)) {
                $content->slug = static::generateUniqueSlug($content->title);
            }
        });

        static::updating(function (Content $content) {
            if ($content->isDirty('title') && ! $content->isDirty('slug')) {
                $content->slug = static::generateUniqueSlug($content->title, $content->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $cont = 1;

        while (
            static::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeArticles($query)
    {
        return $query->where('type', self::TYPE_ARTICLE);
    }

    public function scopeStaticPages($query)
    {
        return $query->whereIn('type', self::STATIC_TYPES);
    }
    
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('excerpt', 'like', "%{$keyword}%");
        });
    }

    public function isArticle(): bool
    {
        return $this->type === self::TYPE_ARTICLE;
    }

    public function isStaticPage(): bool
    {
        return in_array($this->type, self::STATIC_TYPES);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }
}
