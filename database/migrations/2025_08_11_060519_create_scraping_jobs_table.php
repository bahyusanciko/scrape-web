<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scraping_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // twitter, instagram, facebook, etc.
            $table->string('scraper_type'); // user, hashtag, search, etc.
            $table->string('target'); // username, hashtag, search term
            $table->integer('max_results')->nullable(); // max number of results to scrape
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->text('options')->nullable(); // JSON options for the scraper
            $table->text('error_message')->nullable();
            $table->integer('results_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_jobs');
    }
};
