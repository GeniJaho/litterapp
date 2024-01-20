<?php

use App\Console\Commands\FillOriginalFileNames;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->string('original_file_name')->after('path');

            $table->unique(['user_id', 'original_file_name']);
        });

        Artisan::call(FillOriginalFileNames::class);
    }
};
