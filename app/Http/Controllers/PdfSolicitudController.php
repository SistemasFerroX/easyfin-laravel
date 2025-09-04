<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Support\Facades\Storage;

class PdfSolicitudController extends Controller
{
    /**
     * Amortización:
     *  - Dueño de la solicitud o Admin/Super-admin.
     *  - Solo si está aprobada y el archivo existe.
     */
    public function amortizacion(Solicitud $solicitud)
    {
        $this->authorizeOwnerOrAdmin($solicitud);

        $path = $solicitud->amortizacion_pdf_path;
        abort_unless($this->isApprovedAndExists($solicitud, $path), 404, 'PDF no disponible');

        $absPath = Storage::disk('public')->path($path);
        return response()->file($absPath, ['Content-Type' => 'application/pdf']);
        // Si prefieres descarga forzada:
        // return response()->download($absPath, "amortizacion-{$solicitud->id}.pdf");
    }

    /**
     * Certificado:
     *  - Solo Admin/Super-admin.
     *  - Solo si está aprobada y el archivo existe.
     */
    public function certificado(Solicitud $solicitud)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin','super-admin']), 403);

        $path = $solicitud->certificado_pdf_path;
        abort_unless($this->isApprovedAndExists($solicitud, $path), 404, 'PDF no disponible');

        $absPath = Storage::disk('public')->path($path);
        return response()->file($absPath, ['Content-Type' => 'application/pdf']);
        // return response()->download($absPath, "certificado-{$solicitud->id}.pdf");
    }

    /* ================= Helpers ================= */

    /** Permite acceso al dueño o a admin/super-admin. */
    protected function authorizeOwnerOrAdmin(Solicitud $s): void
    {
        $u = auth()->user();
        if ($u->hasAnyRole(['admin','super-admin'])) return;

        abort_unless((int) $s->user_id === (int) $u->id, 403);
    }

    /** Debe estar aprobada y existir el archivo en storage/public. */
    protected function isApprovedAndExists(Solicitud $s, ?string $relativePath): bool
    {
        if (strtolower((string) $s->status) !== 'aprobada') return false;
        if (!$relativePath) return false;

        return Storage::disk('public')->exists($relativePath);
    }
}
