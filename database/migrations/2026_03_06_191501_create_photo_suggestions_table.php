<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }
};
