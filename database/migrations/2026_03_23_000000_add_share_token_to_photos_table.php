<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->string('share_token')->nullable()->unique()->after('size_kb');
            $table->timestamp('share_expires_at')->nullable()->after('share_token');
            $table->integer('share_view_count')->default(0)->after('share_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropColumn(['share_token', 'share_expires_at', 'share_view_count']);
        });
    }
};
