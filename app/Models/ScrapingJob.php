<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ScrapingJob extends Model
{
    protected $fillable = [
        'platform',
        'scraper_type',
        'target',
        'max_results',
        'status',
        'options',
        'error_message',
        'results_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'options' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function scrapedData(): HasMany
    {
        return $this->hasMany(ScrapedData::class);
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $duration = $this->completed_at->diffInSeconds($this->started_at);

        if ($duration < 60) {
            return $duration . ' seconds';
        } elseif ($duration < 3600) {
            return round($duration / 60, 1) . ' minutes';
        } else {
            return round($duration / 3600, 1) . ' hours';
        }
    }

    public function getSnscrapeCommandAttribute(): string
    {
        $command = "snscrape {$this->platform}-{$this->scraper_type} {$this->target}";

        if ($this->max_results) {
            $command .= " --max-results {$this->max_results}";
        }

        return $command;
    }
}
