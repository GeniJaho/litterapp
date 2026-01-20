<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->unsignedSmallInteger('size_kb')
                ->nullable()
                ->after('original_file_name')
                ->index();
        });
    }
};
