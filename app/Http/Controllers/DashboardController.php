<?php

namespace App\Http\Controllers;

use App\Models\ScrapingJob;
use App\Models\ScrapedData;
use App\Services\SnscrapeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private SnscrapeService $snscrapeService
    ) {}

    public function index(): View
    {
        $stats = [
            'total_jobs' => ScrapingJob::count(),
            'completed_jobs' => ScrapingJob::where('status', 'completed')->count(),
            'failed_jobs' => ScrapingJob::where('status', 'failed')->count(),
            'running_jobs' => ScrapingJob::where('status', 'running')->count(),
            'total_data' => ScrapedData::count(),
            'platforms' => ScrapedData::distinct('platform')->pluck('platform')->toArray(),
        ];

        $recentJobs = ScrapingJob::with('scrapedData')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentData = ScrapedData::with('scrapingJob')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $platformStats = ScrapedData::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->get();

        $snscrapeInstalled = $this->snscrapeService->checkSnscrapeInstallation();
        $snscrapeVersion = $this->snscrapeService->getSnscrapeVersion();

        return view('dashboard.index', compact(
            'stats',
            'recentJobs',
            'recentData',
            'platformStats',
            'snscrapeInstalled',
            'snscrapeVersion'
        ));
    }

    public function jobs(): View
    {
        $jobs = ScrapingJob::with('scrapedData')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('jobs.index', compact('jobs'));
    }

    public function data(): View
    {
        $data = ScrapedData::with('scrapingJob')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('data.index', compact('data'));
    }

    public function platformStats(string $platform): View
    {
        $stats = [
            'total_posts' => ScrapedData::where('platform', $platform)->count(),
            'unique_authors' => ScrapedData::where('platform', $platform)->distinct('author')->count(),
            'with_media' => ScrapedData::where('platform', $platform)
                ->whereNotNull('media')
                ->where('media', '!=', '[]')
                ->count(),
        ];

        $recentData = ScrapedData::where('platform', $platform)
            ->with('scrapingJob')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $topAuthors = ScrapedData::where('platform', $platform)
            ->whereNotNull('author')
            ->selectRaw('author, COUNT(*) as count')
            ->groupBy('author')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('platform.stats', compact('platform', 'stats', 'recentData', 'topAuthors'));
    }
}
