<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('titik_jaringan_id')
                ->constrained('titik_jaringan')
                ->cascadeOnDelete();

            $table->enum('jenis_port', ['in', 'out']);

            $table->unsignedInteger('nomor_port');

            $table->enum('status', ['kosong', 'terpakai'])
                ->default('kosong');

            $table->decimal('redaman_manual', 8, 2)
                ->nullable();

            $table->string('sumber_port')
                ->nullable();

            $table->text('keterangan_redaman')
                ->nullable();

            $table->foreignId('connected_titik_id')
                ->nullable()
                ->constrained('titik_jaringan')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'titik_jaringan_id',
                'jenis_port',
                'nomor_port',
            ], 'ports_titik_jenis_nomor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};
