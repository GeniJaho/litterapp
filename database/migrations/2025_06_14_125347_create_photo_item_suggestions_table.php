<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_item_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_item_suggestions');
    }
};
