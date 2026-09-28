<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulasi_topologi_plb', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->text('catatan')->nullable();

            // parameter default simulasi, bukan ambil dari menu Parameter PLB lama
            $table->decimal('ptx', 8, 2)->default(9.00);
            $table->decimal('alpha_serat', 8, 3)->default(0.350);
            $table->decimal('loss_konektor', 8, 3)->default(0.250);
            $table->decimal('loss_splice', 8, 3)->default(0.100);
            $table->decimal('safety_margin', 8, 2)->default(3.00);
            $table->decimal('threshold_rx', 8, 2)->default(-28.00);

            // tabel loss splitter default per simulasi, tetap bisa diubah
            $table->json('splitter_losses')->nullable();

            // data whiteboard
            $table->json('nodes')->nullable();
            $table->json('edges')->nullable();

            // hasil hitung terakhir
            $table->json('hasil')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulasi_topologi_plb');
    }
};
