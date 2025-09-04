<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // si aún no la tienes, descomenta:
            // $table->timestamp('fecha_aprobacion')->nullable()->after('status');

            $table->string('amortizacion_pdf_path')->nullable()->after('status');
            $table->string('certificado_pdf_path')->nullable()->after('amortizacion_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn(['amortizacion_pdf_path', 'certificado_pdf_path']);
            // $table->dropColumn('fecha_aprobacion'); // solo si la agregaste arriba
        });
    }
};
