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
        Schema::table('photo_suggestions', function (Blueprint $table): void {
            $table->json('predictions')->nullable();
            $table->unsignedTinyInteger('accepted_item_rank')->nullable();
            $table->boolean('brand_accepted')->nullable();
            $table->boolean('content_accepted')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photo_suggestions', function (Blueprint $table): void {
            $table->dropColumn(['predictions', 'accepted_item_rank', 'brand_accepted', 'content_accepted']);
        });
    }
};
