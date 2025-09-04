<?php

namespace App\Services;

use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfSolicitudService
{
    /**
     * Genera y guarda ambos PDFs. Devuelve [amortPath, certPath].
     */
    public static function generarYGuardar(Solicitud $s): array
    {
        // 1) Amortización
        $tasaMensual = ($s->tasa_interes ?? 0) / 100; // % → decimal mensual
        $tabla = AmortizacionService::generar(
            (float) $s->monto_solicitado,
            (float) $tasaMensual,
            (int) $s->plazo_meses
        );

        $amortPdf = Pdf::loadView('pdfs.amortizacion', [
            'solicitud'       => $s,
            'cuota'           => $tabla['cuota'],
            'rows'            => $tabla['rows'],
            'total_intereses' => $tabla['total_intereses'],
            'total_pagado'    => $tabla['total_pagado'],
        ])->setPaper('a4', 'portrait');

        $dir = "solicitudes/{$s->id}";
        $amortPath = "{$dir}/amortizacion.pdf";
        Storage::disk('public')->put($amortPath, $amortPdf->output());

        // 2) Certificado (con espacio de firmas)
        $certPdf = Pdf::loadView('pdfs.certificado_aprobacion', [
            'solicitud' => $s,
        ])->setPaper('a4', 'portrait');

        $certPath = "{$dir}/certificado_aprobacion.pdf";
        Storage::disk('public')->put($certPath, $certPdf->output());

        // Guarda rutas en DB
        $s->update([
            'amortizacion_pdf_path' => $amortPath,
            'certificado_pdf_path'  => $certPath,
        ]);

        return [$amortPath, $certPath];
    }
}
