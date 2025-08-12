@extends('layouts.app')

@section('title', 'Job Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Job Details</h1>
            <p class="mt-2 text-sm text-gray-700">Scraping job: {{ ucfirst($job->platform) }} - {{ ucfirst($job->scraper_type) }}</p>
        </div>
        <div class="mt-4 sm:mt-0 space-x-3">
            <a href="{{ route('scraping.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Back to Scraping
            </a>
            @if($job->status === 'failed')
                <form action="{{ route('scraping.jobs.retry', $job) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                        Retry Job
                    </button>
                </form>
            @endif
            @if($job->status === 'pending')
                <form action="{{ route('scraping.jobs.execute', $job) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        Execute Now
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Job Information -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Job Information</h3>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Platform</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $job->platform }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Scraper Type</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $job->scraper_type }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Target</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->target }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($job->status === 'completed') bg-green-100 text-green-800
                            @elseif($job->status === 'failed') bg-red-100 text-red-800
                            @elseif($job->status === 'running') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $job->formatted_status }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Results Count</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->results_count }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Max Results</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->max_results ?: 'Unlimited' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->created_at->format('M j, Y g:i A') }}</dd>
                </div>

                @if($job->started_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Started</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->started_at->format('M j, Y g:i A') }}</dd>
                </div>
                @endif

                @if($job->completed_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Completed</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->completed_at->format('M j, Y g:i A') }}</dd>
                </div>
                @endif

                @if($job->duration)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Duration</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $job->duration }}</dd>
                </div>
                @endif
            </dl>

            @if($job->error_message)
            <div class="mt-6">
                <dt class="text-sm font-medium text-gray-500">Error Message</dt>
                <dd class="mt-1 text-sm text-red-600 bg-red-50 p-3 rounded-md">{{ $job->error_message }}</dd>
            </div>
            @endif

            <div class="mt-6">
                <dt class="text-sm font-medium text-gray-500">Snscrape Command</dt>
                <dd class="mt-1">
                    <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $job->snscrape_command }}</code>
                </dd>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Actions</h3>

            <div class="flex flex-wrap gap-3">
                @if($job->scrapedData->count() > 0)
                    <a href="{{ route('scraping.jobs.data', $job) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        View Data ({{ $job->scrapedData->count() }})
                    </a>

                    <a href="{{ route('scraping.jobs.export', $job) }}?format=json" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Export JSON
                    </a>

                    <a href="{{ route('scraping.jobs.export', $job) }}?format=csv" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Export CSV
                    </a>
                @endif

                <form action="{{ route('scraping.jobs.destroy', $job) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this job?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                        Delete Job
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Data Preview -->
    @if($job->scrapedData->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Data</h3>
                <a href="{{ route('scraping.jobs.data', $job) }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all data</a>
            </div>

            <div class="flow-root">
                <ul class="-my-5 divide-y divide-gray-200">
                    @foreach($job->scrapedData->take(5) as $data)
                    <li class="py-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    @if($data->author){{ $data->author }}@else Unknown Author @endif
                                </p>
                                <p class="text-sm text-gray-500 truncate">{{ $data->formatted_content }}</p>
                                <p class="text-xs text-gray-400">{{ $data->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($data->likes_count > 0)
                                <span class="text-xs text-gray-500">❤️ {{ $data->likes_count }}</span>
                                @endif
                                @if($data->media_count > 0)
                                <span class="text-xs text-gray-500">📷 {{ $data->media_count }}</span>
                                @endif
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
