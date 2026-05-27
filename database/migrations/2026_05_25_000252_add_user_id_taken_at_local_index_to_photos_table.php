<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->index(['user_id', 'taken_at_local']);
            $table->dropIndex(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->index('user_id');
            $table->dropIndex(['user_id', 'taken_at_local']);
        });
    }
};
