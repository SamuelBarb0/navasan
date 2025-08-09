<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropFkIfExists(string $table, string $column): void
    {
        $row = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$table, $column]);

        if ($row && isset($row->CONSTRAINT_NAME)) {
            // No se puede usar Schema::table->dropForeign si no sabemos el nombre exacto generado,
            // así que hacemos SQL directo con el nombre detectado.
            DB::statement(sprintf(
                "ALTER TABLE `%s` DROP FOREIGN KEY `%s`",
                $table,
                $row->CONSTRAINT_NAME
            ));
        }
    }

    public function up(): void
    {
        // 1) Quitar FK de orden_id si existe
        if (Schema::hasColumn('suajes', 'orden_id')) {
            $this->dropFkIfExists('suajes', 'orden_id');
        }

        // 2) Quitar columnas que ya no usaremos (si existen)
        Schema::table('suajes', function (Blueprint $table) {
            foreach (['producto_id', 'proceso', 'realizado_por', 'fecha_fin'] as $col) {
                if (Schema::hasColumn('suajes', $col)) {
                    try { $table->dropColumn($col); } catch (\Throwable $e) {}
                }
            }
            // si existía con otro tipo, la eliminamos y luego la creamos abajo
            if (Schema::hasColumn('suajes', 'cantidad_pliegos_impresos')) {
                try { $table->dropColumn('cantidad_pliegos_impresos'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('suajes', 'cantidad_liberada')) {
                try { $table->dropColumn('cantidad_liberada'); } catch (\Throwable $e) {}
            }
        });

        // 3) Asegurar columnas finales
        Schema::table('suajes', function (Blueprint $table) {
            // orden_id (si no existe, crearlo; si existe, dejamos y luego recreamos FK)
            if (!Schema::hasColumn('suajes', 'orden_id')) {
                $table->foreignId('orden_id')->after('id');
            }

            // campos deseados
            $table->unsignedInteger('cantidad_liberada')->default(0)->after('orden_id');
            $table->unsignedInteger('cantidad_pliegos_impresos')->nullable()->after('cantidad_liberada');

            // timestamps si no existen
            if (!Schema::hasColumn('suajes', 'created_at') && !Schema::hasColumn('suajes', 'updated_at')) {
                $table->timestamps();
            }
        });

        // 4) Recrear FK de orden_id (RESTRICT o SET NULL; aquí RESTRICT)
        // Si tienes registros huérfanos en suajes.orden_id, esto fallará; limpia datos primero si es necesario.
        DB::statement("
            ALTER TABLE `suajes`
            ADD CONSTRAINT `suajes_orden_id_foreign`
            FOREIGN KEY (`orden_id`) REFERENCES `orden_produccions`(`id`)
            ON DELETE RESTRICT
        ");
    }

    public function down(): void
    {
        // Revertir: quitar FK y columnas nuevas
        $this->dropFkIfExists('suajes', 'orden_id');

        Schema::table('suajes', function (Blueprint $table) {
            if (Schema::hasColumn('suajes', 'cantidad_pliegos_impresos')) {
                try { $table->dropColumn('cantidad_pliegos_impresos'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('suajes', 'cantidad_liberada')) {
                try { $table->dropColumn('cantidad_liberada'); } catch (\Throwable $e) {}
            }
            // No borramos orden_id para no dejar huérfana la tabla en down; ajusta si quieres
        });

        // (Opcional) Podrías recrear las columnas antiguas aquí si lo necesitas.
    }
};
