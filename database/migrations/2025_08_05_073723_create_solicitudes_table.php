<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('solicitudes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // tus datos de “encuesta”
        $table->string('nombre_completo');
        $table->string('identificacion');
        $table->date('fecha_nacimiento');
        $table->string('direccion');
        $table->string('telefono');
        $table->string('email');
        $table->string('profesion')->nullable();
        $table->decimal('ingresos_mensuales', 10, 2)->nullable();

        // campos de préstamo
        $table->decimal('monto_solicitado', 10, 2);
        $table->integer('plazo_meses');
        $table->decimal('tasa_interes', 5, 2)->default(5);

        // control de estado
        $table->enum('status',['pendiente','aprobada','rechazada'])
              ->default('pendiente');
        $table->timestamp('fecha_solicitud')->useCurrent();
        $table->timestamp('fecha_aprobacion')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('solicitudes');
}
};
