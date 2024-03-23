<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_shortcut_item_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_shortcut_item_id');
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->foreign('tag_shortcut_item_id')
                ->references('id')
                ->on('tag_shortcut_items')
                ->cascadeOnDelete();

            $table->unique(['tag_shortcut_item_id', 'tag_id']);
        });
    }
};
