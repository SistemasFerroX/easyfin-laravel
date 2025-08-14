<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // Monto y propuesta en entero grande sin decimales (hasta 999.999.999.999)
            $table->decimal('monto_solicitado', 12, 0)->change();
            if (Schema::hasColumn('solicitudes', 'propuesta_monto')) {
                $table->decimal('propuesta_monto', 12, 0)->nullable()->change();
            }

            // Dirección/empresa más largas
            if (Schema::hasColumn('solicitudes', 'direccion')) {
                $table->string('direccion', 255)->change();
            }
            if (Schema::hasColumn('solicitudes', 'empresa')) {
                $table->string('empresa', 255)->nullable()->change();
            }

            // Mensaje de propuesta más largo (si hoy es string corto)
            if (Schema::hasColumn('solicitudes', 'propuesta_mensaje')) {
                $table->text('propuesta_mensaje')->nullable()->change();
            }

            // (Opcional) tasa con 2 decimales y rango cómodo
            if (Schema::hasColumn('solicitudes', 'tasa_interes')) {
                $table->decimal('tasa_interes', 5, 2)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // Revertir con tus tamaños anteriores si los conoces; si no, dejarlos así.
            // $table->integer('monto_solicitado')->change();
            // $table->string('direccion', 100)->change();
        });
    }
};
