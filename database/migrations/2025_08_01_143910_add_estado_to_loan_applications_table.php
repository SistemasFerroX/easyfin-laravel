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
         Schema::table('loan_applications', function (Blueprint $table) {
        $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])
              ->default('pendiente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Elimina la columna estado
            $table->dropColumn('estado');
        });
    }
};
