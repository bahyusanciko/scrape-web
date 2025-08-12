<?php

namespace App\Http\Controllers;

use App\Models\ScrapingJob;
use App\Models\ScrapedData;
use App\Services\SnscrapeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ScrapingController extends Controller
{
    public function __construct(
        private SnscrapeService $snscrapeService
    ) {}

    public function index(): View
    {
        $supportedPlatforms = $this->snscrapeService->getSupportedPlatforms();
        $recentJobs = ScrapingJob::orderBy('created_at', 'desc')->limit(5)->get();

        return view('scraping.index', compact('supportedPlatforms', 'recentJobs'));
    }

    public function create(): View
    {
        $supportedPlatforms = $this->snscrapeService->getSupportedPlatforms();

        return view('scraping.create', compact('supportedPlatforms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'platform' => 'required|string',
            'scraper_type' => 'required|string',
            'target' => 'required|string',
            'max_results' => 'nullable|integer|min:1|max:10000',
        ]);

        $job = $this->snscrapeService->createJob(
            $request->platform,
            $request->scraper_type,
            $request->target,
            $request->max_results,
            $request->except(['platform', 'scraper_type', 'target', 'max_results'])
        );

        if ($request->boolean('execute_now')) {
            $this->snscrapeService->executeJob($job);
        }

        return redirect()->route('scraping.jobs.show', $job)
            ->with('success', 'Scraping job created successfully.');
    }

    public function show(ScrapingJob $job): View
    {
        $job->load('scrapedData');

        return view('scraping.show', compact('job'));
    }

    public function execute(ScrapingJob $job): RedirectResponse
    {
        if ($job->status === 'running') {
            return back()->with('error', 'Job is already running.');
        }

        if ($job->status === 'completed') {
            return back()->with('error', 'Job has already been completed.');
        }

        $success = $this->snscrapeService->executeJob($job);

        if ($success) {
            return back()->with('success', 'Job executed successfully.');
        } else {
            return back()->with('error', 'Job execution failed. Check the error message for details.');
        }
    }

    public function destroy(ScrapingJob $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('scraping.index')
            ->with('success', 'Job deleted successfully.');
    }

    public function data(ScrapingJob $job): View
    {
        $data = $job->scrapedData()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('scraping.data', compact('job', 'data'));
    }

    public function export(ScrapingJob $job, Request $request)
    {
        $format = $request->get('format', 'json');
        $data = $job->scrapedData()->get();
        switch ($format) {
            case 'csv':
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => sprintf('attachment; filename="job_%d_data_%s.csv"', $job->id, uniqid()),
                ];
                $callback = function() use ($data) {
                    $file = fopen('php://output', 'w');
                    fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                    fputcsv($file, ['ID', 'Platform', 'Author', 'Content', 'URL', 'Published At', 'Likes', 'Shares', 'Comments']);
                    foreach ($data as $row) {
                        fputcsv($file, [
                            $row->id,
                            $row->platform,
                            $row->author,
                            $row->content,
                            $row->url,
                            $row->published_at ? $row->published_at->format('Y-m-d H:i:s') : '',
                            $row->likes_count,
                            $row->shares_count,
                            $row->comments_count,
                        ]);
                    }

                    fclose($file);
                };
                $uniqueFilename = sprintf("job_%d_data_%s.csv", $job->id, uniqid());
                return response()->streamDownload($callback, $uniqueFilename, $headers);
            case 'json':
            default:
                return response()->json([
                    'job' => $job,
                    'data' => $data,
                ]);
        }
    }

    public function status(): JsonResponse
    {
        $stats = [
            'total_jobs' => ScrapingJob::count(),
            'pending_jobs' => ScrapingJob::where('status', 'pending')->count(),
            'running_jobs' => ScrapingJob::where('status', 'running')->count(),
            'completed_jobs' => ScrapingJob::where('status', 'completed')->count(),
            'failed_jobs' => ScrapingJob::where('status', 'failed')->count(),
            'total_data' => ScrapedData::count(),
        ];

        return response()->json($stats);
    }

    public function retry(ScrapingJob $job): RedirectResponse
    {
        if ($job->status !== 'failed') {
            return back()->with('error', 'Only failed jobs can be retried.');
        }

        $job->update([
            'status' => 'pending',
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);

        $success = $this->snscrapeService->executeJob($job);

        if ($success) {
            return back()->with('success', 'Job retried successfully.');
        } else {
            return back()->with('error', 'Job retry failed.');
        }
    }
}
