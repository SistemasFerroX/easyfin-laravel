<?php

use Illuminate\Database\Migrations\Migration;   // ← te faltaba esta línea
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('tipo_documento', 5)->nullable()->after('identificacion'); // cc, ce, ppt
            $table->string('empresa_nit')->nullable()->after('empresa');

            // si aún no las añadiste:
            $table->string('doc_cedula_path')->nullable()->after('empresa_nit');
            $table->string('cert_bancario_path')->nullable()->after('doc_cedula_path');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_documento',
                'empresa_nit',
                'doc_cedula_path',
                'cert_bancario_path',
            ]);
        });
    }
};
