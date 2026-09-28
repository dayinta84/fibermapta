<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titik_jaringan', function (Blueprint $table) {
            $table->unsignedInteger('kapasitas_port')
                ->nullable()
                ->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('titik_jaringan', function (Blueprint $table) {
            $table->dropColumn('kapasitas_port');
        });
    }
};
