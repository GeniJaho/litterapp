<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('item_score');
            $table->unsignedInteger('item_count')->default(0);
            $table->foreignId('brand_tag_id')->nullable()->constrained('tags')->cascadeOnDelete();
            $table->unsignedInteger('brand_score')->nullable();
            $table->unsignedInteger('brand_count')->nullable();
            $table->foreignId('content_tag_id')->nullable()->constrained('tags')->cascadeOnDelete();
            $table->unsignedInteger('content_score')->nullable();
            $table->unsignedInteger('content_count')->nullable();
            $table->boolean('is_accepted')->nullable();
        });

        // Migrate existing data from photo_item_suggestions
        DB::statement('
            INSERT INTO photo_suggestions (id, photo_id, item_id, item_score, item_count, is_accepted)
            SELECT id, photo_id, item_id, ROUND(score * 100), 0, is_accepted
            FROM photo_item_suggestions
        ');

        Schema::dropIfExists('photo_item_suggestions');
    }

    public function down(): void
    {
        Schema::create('photo_item_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0.00);
            $table->boolean('is_accepted')->nullable();
        });

        DB::statement('
            INSERT INTO photo_item_suggestions (id, photo_id, item_id, score, is_accepted)
            SELECT id, photo_id, item_id, item_score / 100.0, is_accepted
            FROM photo_suggestions
        ');

        Schema::dropIfExists('photo_suggestions');
    }
};
