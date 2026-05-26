<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('content_hash', 64);
            $table->timestamps();

            $table->unique(['user_id', 'content_hash']);
        });

        Schema::table('photos', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'original_file_name', 'taken_at_local']);
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->unique(['user_id', 'original_file_name', 'taken_at_local']);
        });

        Schema::dropIfExists('photo_details');
    }
};
