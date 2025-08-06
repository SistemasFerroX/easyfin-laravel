<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameProfesionToEmpresaAndDropIngresosOnSolicitudes extends Migration
{
    public function up()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // Renombra la columna profesion a empresa
            $table->renameColumn('profesion', 'empresa');

            // Elimina ingresos_mensuales
            $table->dropColumn('ingresos_mensuales');
        });
    }

    public function down()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // Regresa cambios: empresa → profesion
            $table->renameColumn('empresa', 'profesion');

            // Vuelve a crear ingresos_mensuales (nullable)
            $table->decimal('ingresos_mensuales', 10, 2)
                  ->nullable()
                  ->after('profesion');
        });
    }
}
