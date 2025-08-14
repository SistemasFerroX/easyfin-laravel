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

        // ---- Propuesta / contraoferta del admin ----
        'propuesta_por',             // id del admin que envía la propuesta
        'propuesta_monto',
        'propuesta_plazo_meses',
        'propuesta_estado',          // enviada | aceptada | rechazada
        'propuesta_mensaje',
        'propuesta_enviada_at',

        // ---- Gestión ----
        'fecha_aprobacion',          // cuando se aprueba
    ];

    protected $casts = [
        'monto_solicitado'      => 'integer',
        'plazo_meses'           => 'integer',
        'tasa_interes'          => 'float',
        'fecha_nacimiento'      => 'date',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',

        // Propuesta
        'propuesta_monto'       => 'integer',
        'propuesta_plazo_meses' => 'integer',
        'propuesta_enviada_at'  => 'datetime',

        // Gestión
        'fecha_aprobacion'      => 'datetime',
    ];

    // Por defecto las nuevas solicitudes quedan pendientes
    protected $attributes = [
        'status' => 'pendiente',
    ];

    /* =======================
     |   Relaciones
     ======================= */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Admin que envió la propuesta (si aplica)
    public function proponente()
    {
        return $this->belongsTo(\App\Models\User::class, 'propuesta_por');
    }

    /* =======================
     |   Scopes / helpers
     ======================= */
    public function scopeStatus($q, string $status)
    {
        return $q->where('status', $status);
    }

    public function getTienePropuestaPendienteAttribute(): bool
    {
        return $this->propuesta_estado === 'enviada';
    }

    /* =======================
     |   Mutators opcionales
     |   (sanitizan números si llegan con puntos/comas)
     ======================= */
    public function setMontoSolicitadoAttribute($value): void
    {
        $this->attributes['monto_solicitado'] = is_null($value)
            ? null
            : (int) preg_replace('/\D+/', '', (string) $value);
    }

    public function setPropuestaMontoAttribute($value): void
    {
        $this->attributes['propuesta_monto'] = is_null($value)
            ? null
            : (int) preg_replace('/\D+/', '', (string) $value);
    }
}
