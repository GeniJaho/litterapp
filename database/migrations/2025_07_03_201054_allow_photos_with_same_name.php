<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('photos_user_id_foreign');
            }

            $table->dropUnique(['user_id', 'original_file_name']);

            $table->unique(['user_id', 'original_file_name', 'taken_at_local']);

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'original_file_name', 'taken_at_local']);

            $table->unique(['user_id', 'original_file_name']);
        });
    }
};
