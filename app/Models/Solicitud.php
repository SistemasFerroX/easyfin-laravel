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
        'empresa',
        'monto_solicitado',
        'plazo_meses',
        'tasa_interes',
        'status',
        'observaciones',
    ];

    protected $casts = [
        'monto_solicitado' => 'integer',
        'plazo_meses'      => 'integer',
        'tasa_interes'     => 'float',
        'fecha_nacimiento' => 'date',
        'created_at'       => 'datetime',
    ];

    // Si no mandan status desde el back, queda pendiente
    protected $attributes = [
        'status' => 'pendiente',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
