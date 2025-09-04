<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $t) {
            if (!Schema::hasColumn('solicitudes','tipo_documento')) {
                $t->string('tipo_documento', 5)->nullable();
            }
            if (!Schema::hasColumn('solicitudes','empresa_key')) {
                $t->string('empresa_key', 50)->nullable();
            }
            if (!Schema::hasColumn('solicitudes','empresa_nit')) {
                $t->string('empresa_nit', 20)->nullable();
            }
            if (!Schema::hasColumn('solicitudes','doc_cedula_path')) {
                $t->string('doc_cedula_path')->nullable();
            }
            if (!Schema::hasColumn('solicitudes','cert_bancario_path')) {
                $t->string('cert_bancario_path')->nullable();
            }
            if (!Schema::hasColumn('solicitudes','admin_pdf_path')) {
                $t->string('admin_pdf_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $t) {
            if (Schema::hasColumn('solicitudes','tipo_documento'))      $t->dropColumn('tipo_documento');
            if (Schema::hasColumn('solicitudes','empresa_key'))         $t->dropColumn('empresa_key');
            if (Schema::hasColumn('solicitudes','empresa_nit'))         $t->dropColumn('empresa_nit');
            if (Schema::hasColumn('solicitudes','doc_cedula_path'))     $t->dropColumn('doc_cedula_path');
            if (Schema::hasColumn('solicitudes','cert_bancario_path'))  $t->dropColumn('cert_bancario_path');
            if (Schema::hasColumn('solicitudes','admin_pdf_path'))      $t->dropColumn('admin_pdf_path');
        });
    }
};
