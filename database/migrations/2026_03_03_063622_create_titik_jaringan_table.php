<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('titik_jaringan', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['OLT', 'ODC', 'ODP']);
            $table->string('nama');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->text('keterangan')->nullable();

            // khusus ODP (opsional sekarang, bisa dipakai nanti)
            $table->enum('rasio_splitter', ['1:4', '1:8', '1:16'])->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titik_jaringan');
    }
};
