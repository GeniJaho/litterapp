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
        Schema::create('photo_item_tag', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('photo_item_id');
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->foreign('photo_item_id')->references('id')->on('photo_items')->cascadeOnDelete();

            $table->unique(['photo_item_id', 'tag_id']);
        });
    }
};
