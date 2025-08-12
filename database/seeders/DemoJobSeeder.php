<?php

namespace Database\Seeders;

use App\Models\ScrapingJob;
use App\Models\ScrapedData;
use Illuminate\Database\Seeder;

class DemoJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo scraping jobs
        $jobs = [
            [
                'platform' => 'twitter',
                'scraper_type' => 'user',
                'target' => 'textfiles',
                'max_results' => 10,
                'status' => 'completed',
                'results_count' => 10,
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHours(1),
            ],
            [
                'platform' => 'twitter',
                'scraper_type' => 'hashtag',
                'target' => 'laravel',
                'max_results' => 20,
                'status' => 'completed',
                'results_count' => 20,
                'started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(3),
            ],
            [
                'platform' => 'instagram',
                'scraper_type' => 'hashtag',
                'target' => 'php',
                'max_results' => 15,
                'status' => 'completed',
                'results_count' => 15,
                'started_at' => now()->subHours(6),
                'completed_at' => now()->subHours(5),
            ],
            [
                'platform' => 'reddit',
                'scraper_type' => 'subreddit',
                'target' => 'programming',
                'max_results' => 25,
                'status' => 'pending',
                'results_count' => 0,
            ],
        ];

        foreach ($jobs as $jobData) {
            $job = ScrapingJob::create($jobData);

            // Create demo scraped data for completed jobs
            if ($job->status === 'completed') {
                $this->createDemoData($job);
            }
        }
    }

    private function createDemoData(ScrapingJob $job): void
    {
        $authors = [
            'john_doe', 'jane_smith', 'dev_user', 'tech_enthusiast', 'code_master',
            'web_developer', 'python_lover', 'laravel_fan', 'php_expert', 'fullstack_dev'
        ];

        $contents = [
            'Just deployed my new Laravel application! 🚀 #laravel #php',
            'Learning about social media scraping with snscrape. Very powerful tool!',
            'Building a web interface for data collection. This is going to be amazing!',
            'Check out this awesome tutorial on web scraping with Python and PHP',
            'Finally got my scraping job working perfectly. The data looks great!',
            'Working on a new project that combines Laravel and data analysis',
            'Just discovered how easy it is to scrape social media data programmatically',
            'This new tool is going to revolutionize how we collect social data',
            'Amazing results from our latest scraping experiment!',
            'Building a dashboard to visualize scraped social media data'
        ];

        for ($i = 0; $i < $job->results_count; $i++) {
            $author = $authors[array_rand($authors)];
            $content = $contents[array_rand($contents)];

            ScrapedData::create([
                'scraping_job_id' => $job->id,
                'platform' => $job->platform,
                'content_type' => $job->platform === 'twitter' ? 'tweet' : 'post',
                'external_id' => 'demo_' . $job->id . '_' . $i,
                'author' => $author,
                'content' => $content,
                'url' => 'https://example.com/demo/' . $job->id . '/' . $i,
                'media' => rand(0, 1) ? [['type' => 'image', 'url' => 'https://example.com/image.jpg']] : null,
                'metadata' => [
                    'likes' => rand(0, 100),
                    'shares' => rand(0, 50),
                    'comments' => rand(0, 25),
                ],
                'published_at' => now()->subDays(rand(1, 30)),
                'raw_data' => [
                    'demo' => true,
                    'job_id' => $job->id,
                    'index' => $i,
                ],
            ]);
        }
    }
}
