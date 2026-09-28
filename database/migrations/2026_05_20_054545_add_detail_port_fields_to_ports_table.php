<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom detail port sudah dibuat langsung di migration create_ports_table.
        // File ini dibiarkan kosong agar tidak terjadi duplicate column saat migrate.
    }

    public function down(): void
    {
        // Tidak ada perubahan yang perlu di-rollback.
    }
};
