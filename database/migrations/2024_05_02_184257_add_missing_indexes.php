<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->index('created_at');
            $table->index('taken_at_local');
            $table->index('latitude');
            $table->index('longitude');
        });

        Schema::table('photo_items', function (Blueprint $table): void {
            $table->index('picked_up');
            $table->index('recycled');
            $table->index('deposit');
            $table->index('quantity');
        });
    }
};
