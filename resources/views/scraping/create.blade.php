@extends('layouts.app')

@section('title', 'Create Scraping Job')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Scraping Job</h1>
            <p class="mt-2 text-sm text-gray-700">Configure a new social media scraping job</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('scraping.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Back to Scraping
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('scraping.store') }}" method="POST" x-data="scrapingForm()">
            @csrf
            <div class="px-4 py-5 sm:p-6 space-y-6">
                <!-- Platform Selection -->
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
                    <select id="platform" name="platform" x-model="selectedPlatform" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select a platform</option>
                        @foreach($supportedPlatforms as $platform => $scrapers)
                            <option value="{{ $platform }}">{{ ucfirst($platform) }}</option>
                        @endforeach
                    </select>
                    @error('platform')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Scraper Type Selection -->
                <div x-show="selectedPlatform">
                    <label for="scraper_type" class="block text-sm font-medium text-gray-700">Scraper Type</label>
                    <select id="scraper_type" name="scraper_type" x-model="selectedScraperType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select scraper type</option>
                        <template x-for="(scraper, type) in availableScrapers" :key="type">
                            <option :value="type" x-text="type.charAt(0).toUpperCase() + type.slice(1)"></option>
                        </template>
                    </select>
                    @error('scraper_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Target Input -->
                <div x-show="selectedScraperType">
                    <label for="target" class="block text-sm font-medium text-gray-700">
                        <span x-text="getTargetLabel()"></span>
                    </label>
                    <div class="mt-1">
                        <input type="text" name="target" id="target" x-model="target"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               :placeholder="getTargetPlaceholder()">
                    </div>
                    <p class="mt-2 text-sm text-gray-500" x-text="getTargetHelp()"></p>
                    @error('target')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Max Results -->
                <div x-show="selectedScraperType">
                    <label for="max_results" class="block text-sm font-medium text-gray-700">Maximum Results (Optional)</label>
                    <div class="mt-1">
                        <input type="number" name="max_results" id="max_results" min="1" max="10000"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="Leave empty for unlimited">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Maximum number of results to scrape. Leave empty to scrape all available results.</p>
                    @error('max_results')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Execute Now Option -->
                <div x-show="selectedScraperType" class="flex items-center">
                    <input id="execute_now" name="execute_now" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="execute_now" class="ml-2 block text-sm text-gray-900">
                        Execute job immediately after creation
                    </label>
                </div>

                <!-- Preview Command -->
                <div x-show="selectedScraperType && target" class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Preview Command</h4>
                    <code class="text-sm bg-white px-2 py-1 rounded border" x-text="getPreviewCommand()"></code>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Job
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function scrapingForm() {
    return {
        selectedPlatform: '',
        selectedScraperType: '',
        target: '',
        supportedPlatforms: @json($supportedPlatforms),

        get availableScrapers() {
            if (!this.selectedPlatform) return {};
            return this.supportedPlatforms[this.selectedPlatform] || {};
        },

        getTargetLabel() {
            if (!this.selectedScraperType) return 'Target';

            const labels = {
                'user': 'Username',
                'hashtag': 'Hashtag',
                'search': 'Search Term',
                'list': 'List Name',
                'location': 'Location',
                'group': 'Group Name',
                'community': 'Community Name',
                'channel': 'Channel Name',
                'subreddit': 'Subreddit Name',
                'toot': 'Toot ID'
            };

            return labels[this.selectedScraperType] || 'Target';
        },

        getTargetPlaceholder() {
            if (!this.selectedScraperType) return '';

            const placeholders = {
                'user': 'e.g., elonmusk, textfiles',
                'hashtag': 'e.g., python, laravel',
                'search': 'e.g., "artificial intelligence"',
                'list': 'e.g., twitter-verified',
                'location': 'e.g., 123456789',
                'group': 'e.g., Laravel Developers',
                'community': 'e.g., PHP Community',
                'channel': 'e.g., @channelname',
                'subreddit': 'e.g., programming',
                'toot': 'e.g., 123456789012345678'
            };

            return placeholders[this.selectedScraperType] || 'Enter target';
        },

        getTargetHelp() {
            if (!this.selectedScraperType) return '';

            const help = {
                'user': 'Enter the username without @ symbol',
                'hashtag': 'Enter the hashtag without # symbol',
                'search': 'Enter search terms in quotes for exact matches',
                'list': 'Enter the list name or ID',
                'location': 'Enter the location ID from Instagram',
                'group': 'Enter the Facebook group name or ID',
                'community': 'Enter the Facebook community name or ID',
                'channel': 'Enter the Telegram channel username or ID',
                'subreddit': 'Enter the subreddit name without r/',
                'toot': 'Enter the Mastodon toot ID'
            };

            return help[this.selectedScraperType] || '';
        },

        getPreviewCommand() {
            if (!this.selectedPlatform || !this.selectedScraperType || !this.target) return '';

            const scraperName = this.supportedPlatforms[this.selectedPlatform][this.selectedScraperType];
            return `snscrape --jsonl ${scraperName} ${this.target}`;
        }
    }
}
</script>
@endsection
