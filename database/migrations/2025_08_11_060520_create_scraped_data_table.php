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
        Schema::create('scraped_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraping_job_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // twitter, instagram, facebook, etc.
            $table->string('content_type'); // tweet, post, comment, etc.
            $table->string('external_id')->nullable(); // original ID from the platform
            $table->string('author')->nullable(); // username/author
            $table->text('content')->nullable(); // main content text
            $table->text('url')->nullable(); // original URL
            $table->json('media')->nullable(); // images, videos, etc.
            $table->json('metadata')->nullable(); // likes, shares, comments count, etc.
            $table->timestamp('published_at')->nullable(); // when it was published
            $table->json('raw_data')->nullable(); // complete raw data from snscrape
            $table->timestamps();

            $table->index(['platform', 'external_id']);
            $table->index(['scraping_job_id', 'content_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_data');
    }
};
