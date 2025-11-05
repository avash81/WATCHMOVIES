<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            
            // TMDB Integration Fields
            $table->integer('tmdb_id')->unique();
            $table->string('title');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('release_date')->nullable();
            $table->decimal('vote_average', 3, 1)->default(0);
            $table->integer('vote_count')->default(0);
            $table->json('genre_ids')->nullable();
            $table->decimal('popularity', 10, 3)->default(0);
            $table->string('original_language', 10)->default('en');
            $table->string('original_title');
            $table->boolean('video')->default(false);
            $table->boolean('adult')->default(false);
            $table->json('details')->nullable();
            
            // Your original fields (keeping for compatibility)
            $table->text('description')->nullable();
            $table->string('release_year')->nullable();
            $table->string('genre')->nullable();
            $table->string('external_link')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->text('cast')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('box_office', 15, 2)->nullable();
            $table->string('download_480p')->nullable();
            $table->string('download_720p')->nullable();
            $table->string('download_1080p')->nullable();
            $table->string('category')->default('Hollywood');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('tmdb_id');
            $table->index('popularity');
            $table->index('vote_average');
            $table->index('release_date');
            $table->index(['vote_average', 'vote_count']);
            $table->index('original_language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};