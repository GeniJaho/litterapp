<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_shortcut_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tag_shortcut_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->boolean('picked_up')->default(false);
            $table->boolean('recycled')->default(false);
            $table->boolean('deposit')->default(false);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }
};
