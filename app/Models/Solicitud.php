<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'identificacion',
        'fecha_nacimiento',
        'direccion',
        'telefono',
        'email',
        'empresa',         // ← aquí
        'monto_solicitado',
        'plazo_meses',
        'tasa_interes',
        'status',
    ];


    /**
     * Relación a User: cada solicitud pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
