<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titik_jaringan', function (Blueprint $table) {
            if (!Schema::hasColumn('titik_jaringan', 'tx_power')) {
                $table->decimal('tx_power', 8, 2)
                    ->nullable()
                    ->after('kapasitas_port');
            }

            if (!Schema::hasColumn('titik_jaringan', 'jarak_udara_meter')) {
                $table->decimal('jarak_udara_meter', 12, 2)
                    ->nullable()
                    ->after('tx_power');
            }

            if (!Schema::hasColumn('titik_jaringan', 'panjang_kabel_meter')) {
                $table->decimal('panjang_kabel_meter', 12, 2)
                    ->nullable()
                    ->after('jarak_udara_meter');
            }

            if (!Schema::hasColumn('titik_jaringan', 'jenis_jalur')) {
                $table->string('jenis_jalur')
                    ->nullable()
                    ->after('panjang_kabel_meter');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titik_jaringan', function (Blueprint $table) {
            if (Schema::hasColumn('titik_jaringan', 'jenis_jalur')) {
                $table->dropColumn('jenis_jalur');
            }

            if (Schema::hasColumn('titik_jaringan', 'panjang_kabel_meter')) {
                $table->dropColumn('panjang_kabel_meter');
            }

            if (Schema::hasColumn('titik_jaringan', 'jarak_udara_meter')) {
                $table->dropColumn('jarak_udara_meter');
            }

            if (Schema::hasColumn('titik_jaringan', 'tx_power')) {
                $table->dropColumn('tx_power');
            }
        });
    }
};
