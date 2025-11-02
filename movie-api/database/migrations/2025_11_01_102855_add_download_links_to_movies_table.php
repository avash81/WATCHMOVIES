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
  // database/migrations/xxxx_add_download_links_to_movies_table.php
public function up()
{
    Schema::table('movies', function (Blueprint $table) {
        $table->string('download_480p')->nullable()->after('box_office');
        $table->string('download_720p')->nullable();
        $table->string('download_1080p')->nullable();
        $table->string('category')->default('Hollywood'); // Bollywood, Web Series, etc.
    });
}

public function down()
{
    Schema::table('movies', function (Blueprint $table) {
        $table->dropColumn(['download_480p', 'download_720p', 'download_1080p', 'category']);
    });
}
};
