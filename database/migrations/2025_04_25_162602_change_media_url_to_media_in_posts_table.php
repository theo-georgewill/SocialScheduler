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
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('media_url'); // remove old media_url column
            $table->json('media')->nullable()->after('content'); // add new media column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('media'); // remove the new media column
            $table->string('media_url')->nullable()->after('content'); // restore old media_url
        });

    }
};
