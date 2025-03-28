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
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->text('access_token')->change(); // Change access_token to TEXT
            $table->text('refresh_token')->nullable()->change(); // Also increase refresh_token size
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->string('access_token', 255)->change(); // Revert to original length
            $table->string('refresh_token', 255)->nullable()->change();
        });

    }
};
