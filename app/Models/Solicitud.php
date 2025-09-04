<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Solicitud extends Model
{
    use HasFactory;

    /** Empresas especiales / catálogos simples */
    public const DOC_TYPES = ['cc','ce','ppt'];
    public const EMPRESA_BRAVASS_NAME = 'CONFECCIONES BRAVASS';
    public const EMPRESA_BRAVASS_NIT  = '800148853-4';

    protected $table = 'solicitudes';

    protected $fillable = [
        'user_id',
        'nombre_completo',
        'tipo_documento',        // ⬅️ nuevo
        'identificacion',
        'fecha_nacimiento',
        'direccion',
        'telefono',
        'email',
        'empresa',
        'empresa_nit',           // ⬅️ nuevo
        'monto_solicitado',
        'plazo_meses',
        'tasa_interes',
        'status',
        'observaciones',

        // Propuesta / contraoferta
        'propuesta_por',
        'propuesta_monto',
        'propuesta_plazo_meses',
        'propuesta_estado',
        'propuesta_mensaje',
        'propuesta_enviada_at',

        // Gestión
        'fecha_aprobacion',

        // PDFs generados
        'amortizacion_pdf_path',
        'certificado_pdf_path',

        // Adjuntos del solicitante (⬅️ nuevos)
        'doc_cedula_path',
        'cert_bancario_path',
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

    // Por defecto: pendiente
    protected $attributes = [
        'status' => 'pendiente',
    ];

    /* =======================
     | Relaciones
     ======================= */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function proponente()
    {
        return $this->belongsTo(\App\Models\User::class, 'propuesta_por');
    }

    /* =======================
     | Scopes / helpers
     ======================= */
    public function scopeStatus($q, string $status)
    {
        return $q->where('status', $status);
    }

    public function getTienePropuestaPendienteAttribute(): bool
    {
        return $this->propuesta_estado === 'enviada';
    }

    /** Empresa ‘Bravass’: requiere adjuntar cédula + certificado bancario */
    public function getRequiereAdjuntosAttribute(): bool
    {
        $name = strtoupper((string) $this->empresa);
        $nit  = trim((string) $this->empresa_nit);

        return $nit === self::EMPRESA_BRAVASS_NIT
            || str_contains($name, self::EMPRESA_BRAVASS_NAME);
    }

    /** Conveniente para mostrar “Empresa (NIT)” */
    public function getEmpresaDisplayAttribute(): string
    {
        $emp = (string) $this->empresa ?: '—';
        $nit = (string) $this->empresa_nit ?: '';
        return $nit ? "{$emp} ({$nit})" : $emp;
    }

    /* ===== URLs públicas (requiere `php artisan storage:link`) ===== */
    public function getAmortizacionPdfUrlAttribute(): ?string
    {
        return $this->amortizacion_pdf_path
            ? Storage::disk('public')->url($this->amortizacion_pdf_path)
            : null;
    }

    public function getCertificadoPdfUrlAttribute(): ?string
    {
        return $this->certificado_pdf_path
            ? Storage::disk('public')->url($this->certificado_pdf_path)
            : null;
    }

    public function getDocCedulaUrlAttribute(): ?string
    {
        return $this->doc_cedula_path
            ? Storage::disk('public')->url($this->doc_cedula_path)
            : null;
    }

    public function getCertBancarioUrlAttribute(): ?string
    {
        return $this->cert_bancario_path
            ? Storage::disk('public')->url($this->cert_bancario_path)
            : null;
    }

    /* =======================
     | Mutators (sanitizan)
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

    public function setTipoDocumentoAttribute($value): void
    {
        $val = strtolower((string) $value);
        $this->attributes['tipo_documento'] = in_array($val, self::DOC_TYPES, true) ? $val : null;
    }
}
