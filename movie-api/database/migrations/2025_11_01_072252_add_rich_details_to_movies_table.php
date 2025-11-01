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
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            // New fields for rich content and details
            $table->string('poster_url')->nullable()->after('genre')->comment('URL for the movie poster image');
            $table->string('trailer_url')->nullable()->after('poster_url')->comment('YouTube or external link for the trailer');
            $table->json('cast')->nullable()->comment('JSON array of main cast members');
            $table->decimal('budget', 15, 2)->nullable()->comment('Movie production budget');
            $table->decimal('box_office', 15, 2)->nullable()->comment('Real-time overall income/box office total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['poster_url', 'trailer_url', 'cast', 'budget', 'box_office']);
        });
    }
};
