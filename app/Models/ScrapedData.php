<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapedData extends Model
{
    protected $fillable = [
        'scraping_job_id',
        'platform',
        'content_type',
        'external_id',
        'author',
        'content',
        'url',
        'media',
        'metadata',
        'published_at',
        'raw_data',
    ];

    protected $casts = [
        'media' => 'array',
        'metadata' => 'array',
        'raw_data' => 'array',
        'published_at' => 'datetime',
    ];

    public function scrapingJob(): BelongsTo
    {
        return $this->belongsTo(ScrapingJob::class);
    }

    public function getFormattedContentAttribute(): string
    {
        if (strlen($this->content) <= 200) {
            return $this->content;
        }

        return substr($this->content, 0, 200) . '...';
    }

    public function getMediaCountAttribute(): int
    {
        if (!$this->media) {
            return 0;
        }

        return count($this->media);
    }

    public function getLikesCountAttribute(): int
    {
        return $this->metadata['likes'] ?? 0;
    }

    public function getSharesCountAttribute(): int
    {
        return $this->metadata['shares'] ?? 0;
    }

    public function getCommentsCountAttribute(): int
    {
        return $this->metadata['comments'] ?? 0;
    }
}
