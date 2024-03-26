<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->boolean('picked_up')->default(false);
            $table->boolean('recycled')->default(false);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }
};
