<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->after('path')->nullable();
            $table->decimal('longitude', 11, 8)->after('latitude')->nullable();
        });
    }
};
