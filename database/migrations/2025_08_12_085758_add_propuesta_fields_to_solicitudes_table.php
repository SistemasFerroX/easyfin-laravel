<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_propuesta_fields_to_solicitudes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->unsignedBigInteger('propuesta_por')->nullable()->after('status'); // admin id
            $table->bigInteger('propuesta_monto')->nullable()->after('monto_solicitado');
            $table->integer('propuesta_plazo_meses')->nullable()->after('plazo_meses');
            $table->string('propuesta_estado')->nullable()->after('status'); // enviada|aceptada|rechazada
            $table->text('propuesta_mensaje')->nullable();
            $table->timestamp('propuesta_enviada_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn([
                'propuesta_por','propuesta_monto','propuesta_plazo_meses',
                'propuesta_estado','propuesta_mensaje','propuesta_enviada_at'
            ]);
        });
    }
};
