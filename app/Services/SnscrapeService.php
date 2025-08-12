<?php

namespace App\Services;

use App\Models\ScrapingJob;
use App\Models\ScrapedData;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class SnscrapeService
{
    protected array $supportedPlatforms = [
        'twitter' => [
            'user' => 'twitter-user',
            'hashtag' => 'twitter-hashtag',
            'search' => 'twitter-search',
            'list' => 'twitter-list',
        ],
        'instagram' => [
            'user' => 'instagram-user',
            'hashtag' => 'instagram-hashtag',
            'location' => 'instagram-location',
        ],
        'facebook' => [
            'user' => 'facebook-user',
            'group' => 'facebook-group',
            'community' => 'facebook-community',
        ],
        'reddit' => [
            'user' => 'reddit-user',
            'subreddit' => 'reddit-subreddit',
            'search' => 'reddit-search',
        ],
        'telegram' => [
            'channel' => 'telegram-channel',
        ],
        'mastodon' => [
            'user' => 'mastodon-user',
            'toot' => 'mastodon-toot',
        ],
    ];

    protected string $path;

    public function __construct()
    {
        $this->path = base_path('venv/');
    }

    public function getSupportedPlatforms(): array
    {
        return $this->supportedPlatforms;
    }

    public function createJob(string $platform, string $scraperType, string $target, ?int $maxResults = null, array $options = []): ScrapingJob
    {
        return ScrapingJob::create([
            'platform' => $platform,
            'scraper_type' => $scraperType,
            'target' => $target,
            'max_results' => $maxResults,
            'options' => $options,
            'status' => 'pending',
        ]);
    }

    public function executeJob(ScrapingJob $job): bool
    {
        try {
            $job->update([
                'status' => 'running',
                'started_at' => now(),
            ]);

            $command = $this->buildCommand($job);
            $process = new Process($command);
            $process->setTimeout(3600); // 1 hour timeout

            Log::info("Starting snscrape job", [
                'job_id' => $job->id,
                'command' => $command,
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();

                // Chec            k for Python 3.13 compatibility issue
                if (str_contains($errorOutput, 'AttributeError') && str_contains($errorOutput, 'find_module')) {
                    throw new \Exception("Snscrape compatibility issue detected. This is a known issue with Python 3.13. Please use Python 3.11 or 3.12, or install snscrape from source.");
                }

                throw new \Exception("Snscrape command failed: " . $errorOutput);
            }

            $output = $process->getOutput();
            $results = $this->parseOutput($output, $job->platform);

            $this->saveResults($job, $results);

            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
                'results_count' => count($results),
            ]);

            Log::info("Snscrape job completed successfully", [
                'job_id' => $job->id,
                'results_count' => count($results),
            ]);

                    return true;

        } catch (\Exception $e) {
            Log::error("Snscrape job failed", [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
            ]);

            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return false;
        }
    }

    protected function buildCommand(ScrapingJob $job): array
    {
        $scraperName = $this->supportedPlatforms[$job->platform][$job->scraper_type] ?? null;

        if (!$scraperName) {
            throw new \Exception("Unsupported platform/scraper combination: {$job->platform}/{$job->scraper_type}");
        }

        // Use virtual environment if available
                    if (is_dir($this->path)) {
            $command = [$this->path . './bin/snscrape', '--jsonl', $scraperName, $job->target];

            if ($job->max_results) {
                $command = [$this->path . './bin/snscrape', '--jsonl', '--max-results', $job->max_results, $scraperName, $job->target];
            }
        } else {
            // Fallback to system snscrape
            $command = ['snscrape', '--jsonl', $scraperName, $job->target];

            if ($job->max_results) {
                $command = ['snscrape', '--jsonl', '--max-results', $job->max_results, $scraperName, $job->target];
            }
        }
        return $command;
    }

    protected function parseOutput(string $output, string $platform): array
    {
        $lines = array_filter(explode("\n", trim($output)));
        $results = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $results[] = $this->normalizeData($data, $platform);
            }
        }

        return $results;
    }

    protected function normalizeData(array $data, string $platform): array
    {
        $normalized = [
            'platform' => $platform,
            'raw_data' => $data,
        ];

        switch ($platform) {
            case 'twitter':
                $normalized['content_type'] = 'tweet';
                $normalized['external_id'] = $data['id'] ?? null;
                $normalized['author'] = $data['user']['username'] ?? null;
                $normalized['content'] = $data['rawContent'] ?? null;
                $normalized['url'] = $data['url'] ?? null;
                $normalized['published_at'] = isset($data['date']) ? date('Y-m-d H:i:s', strtotime($data['date'])) : null;
                $normalized['media'] = $this->extractTwitterMedia($data);
                $normalized['metadata'] = [
                    'likes' => $data['likeCount'] ?? 0,
                    'retweets' => $data['retweetCount'] ?? 0,
                    'replies' => $data['replyCount'] ?? 0,
                    'quotes' => $data['quoteCount'] ?? 0,
                ];
                break;

            case 'instagram':
                $normalized['content_type'] = 'post';
                $normalized['external_id'] = $data['shortcode'] ?? null;
                $normalized['author'] = $data['owner']['username'] ?? null;
                $normalized['content'] = $data['edge_media_to_caption']['edges'][0]['node']['text'] ?? null;
                $normalized['url'] = "https://www.instagram.com/p/{$data['shortcode']}/";
                $normalized['published_at'] = isset($data['taken_at_timestamp']) ? date('Y-m-d H:i:s', $data['taken_at_timestamp']) : null;
                $normalized['media'] = $this->extractInstagramMedia($data);
                $normalized['metadata'] = [
                    'likes' => $data['edge_media_preview_like']['count'] ?? 0,
                    'comments' => $data['edge_media_to_comment']['count'] ?? 0,
                ];
                break;

            default:
                $normalized['content_type'] = 'post';
                $normalized['external_id'] = $data['id'] ?? null;
                $normalized['author'] = $data['author'] ?? $data['username'] ?? null;
                $normalized['content'] = $data['content'] ?? $data['text'] ?? null;
                $normalized['url'] = $data['url'] ?? null;
                $normalized['published_at'] = isset($data['d        ate']) ? date('Y-m-d H:i:s', strtotime($data['date'])) : null;
                $normalized['media'] = $data['media'] ?? null;
                $normalized['metadata'] = $data['metadata'] ?? [];
        }

        return $normalized;
    }

    protected function extractTwitterMedia(array $data): array
    {
        $media = [];

        if (isset($data['media']['photos'])) {
            foreach ($data['media']['photos'] as $photo) {
                $media[] = [
                    'type' => 'image',
                    'url' => $photo['url'] ?? null,
                ];
            }
        }

        if (isset($data['media']['videos'])) {
            foreach ($data['media']['videos'] as $video) {
                $media[] = [
                    'type' => 'video',
                    'url' => $video['url'] ?? null,
                            'thumbnail' => $video['thumbnailUrl'] ?? null,
                ];
            }
        }

        return $media;
    }

    protected function extractInstagramMedia(array $data): array
    {
        $media = [];

        if (isset($data['display_url'])) {
            $media[] =         [
                'type' => 'image',
                'url' => $data['display_url'],
            ];
        }

        if (isset($data['video_url'])) {
            $media[] = [
                'type' => 'video',
                'url' => $data['video_url'],
                'thumbnail' => $data['display_url'] ?? null,
            ];
        }

        return $media;
    }

    protected function saveResults(ScrapingJob $job, array $results): void
    {
        foreach ($results as $result) {
            ScrapedData::create(array_merge($result, [
                'scraping_job_id' => $job->id,
            ]));
        }
    }

    public function checkSnscrapeInstallation(): bool
    {
        // Check virtual environment first
        if (is_dir($this->path)) {
            $process = new Process([$this->path . './bin/snscrape', '--help']);
            $process->run();

            if ($process->isSuccessful()) {
                return true;
            }
        }

        // Fallback to system snscrape
        $process = new Process(['snscrape', '--help']);
        $process->run();

        return $process->isSuccessful();
    }

    public function getSnscrapeVersion(): ?string
    {
        // Check virtual environment first
        if (is_dir($this->path)) {
            $process = new Process([$this->path . './bin/snscrape', '--version']);
            $process->run();
            if ($process->isSuccessful()) {
                return trim($process->getOutput());
            }
        }

        // Fallback to system snscrape
        $process = new Process(['snscrape', '--version']);
        $process->run();
        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }

        return null;
    }

    public function getSnscrapePath(): string
    {
        if (is_dir($this->path)) {
            return $this->path . './bin/snscrape';
        }

        return 'snscrape';
    }

    public function getPythonVersion(): ?string
    {
        $process = new Process(['python3', '--version']);
        $process->run();

        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }

        return null;
    }

    public function getCompatibilityStatus(): array
    {
        $pythonVersion = $this->getPythonVersion();
        $snscrapeInstalled = $this->checkSnscrapeInstallation();

        $status = [
            'python_version' => $pythonVersion,
            'snscrape_installed' => $snscrapeInstalled,
            'compatible' => false,
            'issues' => [],
        ];

        if ($pythonVersion) {
            if (str_contains($pythonVersion, 'Python 3.13')) {
                $status['issues'][] = 'Python 3.13 has known compatibility issues with snscrape. Consider using Python 3.11 or 3.12.';
            } elseif (str_contains($pythonVersion, 'Python 3.11') || str_contains($pythonVersion, 'Python 3.12')) {
                $status['compatible'] = true;
            }
        }

        if (!$snscrapeInstalled) {
            $status['issues'][] = 'Snscrape is not installed or not working properly.';
        }

        return $status;
    }
}
