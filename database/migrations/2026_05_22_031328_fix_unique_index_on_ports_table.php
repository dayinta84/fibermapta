<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $databaseName = DB::getDatabaseName();

        $this->dropForeignKeysThatUseOldIndex($databaseName);

        if ($this->indexExists($databaseName, 'ports_titik_jaringan_id_nomor_port_unique')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->dropUnique('ports_titik_jaringan_id_nomor_port_unique');
            });
        }

        if (!$this->indexExists($databaseName, 'ports_titik_jenis_nomor_unique')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->unique(
                    ['titik_jaringan_id', 'jenis_port', 'nomor_port'],
                    'ports_titik_jenis_nomor_unique'
                );
            });
        }

        $this->addForeignKeysBack($databaseName);
    }

    public function down(): void
    {
        $databaseName = DB::getDatabaseName();

        $this->dropForeignKeysThatUseOldIndex($databaseName);

        if ($this->indexExists($databaseName, 'ports_titik_jenis_nomor_unique')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->dropUnique('ports_titik_jenis_nomor_unique');
            });
        }

        if (!$this->indexExists($databaseName, 'ports_titik_jaringan_id_nomor_port_unique')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->unique(
                    ['titik_jaringan_id', 'nomor_port'],
                    'ports_titik_jaringan_id_nomor_port_unique'
                );
            });
        }

        $this->addForeignKeysBack($databaseName);
    }

    private function indexExists(string $databaseName, string $indexName): bool
    {
        $indexes = DB::select("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'ports'
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$databaseName, $indexName]);

        return !empty($indexes);
    }

    private function foreignKeyExists(string $databaseName, string $constraintName): bool
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'ports'
              AND CONSTRAINT_NAME = ?
            LIMIT 1
        ", [$databaseName, $constraintName]);

        return !empty($foreignKeys);
    }

    private function dropForeignKeysThatUseOldIndex(string $databaseName): void
    {
        if ($this->foreignKeyExists($databaseName, 'ports_titik_jaringan_id_foreign')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->dropForeign('ports_titik_jaringan_id_foreign');
            });
        }

        if ($this->foreignKeyExists($databaseName, 'ports_connected_titik_id_foreign')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->dropForeign('ports_connected_titik_id_foreign');
            });
        }
    }

    private function addForeignKeysBack(string $databaseName): void
    {
        if (!$this->foreignKeyExists($databaseName, 'ports_titik_jaringan_id_foreign')) {
            Schema::table('ports', function (Blueprint $table) {
                $table->foreign('titik_jaringan_id', 'ports_titik_jaringan_id_foreign')
                    ->references('id')
                    ->on('titik_jaringan')
                    ->cascadeOnDelete();
            });
        }

        if (
            Schema::hasColumn('ports', 'connected_titik_id') &&
            !$this->foreignKeyExists($databaseName, 'ports_connected_titik_id_foreign')
        ) {
            Schema::table('ports', function (Blueprint $table) {
                $table->foreign('connected_titik_id', 'ports_connected_titik_id_foreign')
                    ->references('id')
                    ->on('titik_jaringan')
                    ->nullOnDelete();
            });
        }
    }
};
