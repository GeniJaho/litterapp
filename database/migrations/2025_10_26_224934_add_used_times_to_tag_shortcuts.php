<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tag_shortcuts', function (Blueprint $table) {
            $table->unsignedInteger('used_times')->default(0)->after('shortcut');
        });
    }
};
