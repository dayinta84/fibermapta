<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ports', 'connected_titik_id')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->foreignId('connected_titik_id')
                    ->nullable()
                    ->after('keterangan_redaman')
                    ->constrained('titik_jaringan')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ports', 'connected_titik_id')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->dropConstrainedForeignId('connected_titik_id');
            });
        }
    }
};