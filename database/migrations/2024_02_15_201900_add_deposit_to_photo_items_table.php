<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_items', function (Blueprint $table) {
            $table->boolean('deposit')->default(false)->after('recycled');
        });
    }
};
