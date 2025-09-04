<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\SolicitudAprobadaMail;
use App\Mail\SolicitudRechazadaMail;
use App\Mail\PropuestaEnviadaMail;
use App\Services\PdfSolicitudService;

class SolicitudAdminController extends Controller
{
    /** Empresas que requieren documentos del usuario para aprobar */
    private const EMPRESAS_REQUIERE_DOCS = ['bravass'];

    public function __construct()
    {
        $this->middleware(['auth','role:admin|super-admin']);
    }

    /** Pendientes */
    public function index(Request $request)
    {
        $q = Solicitud::withoutGlobalScopes()->where('status', 'pendiente');

        if ($term = trim((string) $request->input('q', ''))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('nombre_completo', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%")
                   ->orWhere('identificacion', 'like', "%{$term}%");
                if (is_numeric($term)) $qq->orWhere('id', (int) $term);
            });
        }

        switch ($request->input('order')) {
            case 'monto_desc': $q->orderByDesc('monto_solicitado'); break;
            case 'monto_asc' : $q->orderBy('monto_solicitado');     break;
            default          : $q->latest();
        }

        $solicitudes = $q->paginate(12)->withQueryString();
        $historial   = false;

        return view('admin.solicitudes.index', compact('solicitudes','historial'));
    }

    /** Historial (aprobadas + rechazadas) */
    public function history(Request $request)
    {
        $q = Solicitud::withoutGlobalScopes()->whereIn('status', ['aprobada','rechazada']);

        if ($term = trim((string) $request->input('q', ''))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('nombre_completo', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%")
                   ->orWhere('identificacion', 'like', "%{$term}%");
                if (is_numeric($term)) $qq->orWhere('id', (int) $term);
            });
        }

        switch ($request->input('order')) {
            case 'monto_desc': $q->orderByDesc('monto_solicitado'); break;
            case 'monto_asc' : $q->orderBy('monto_solicitado');     break;
            default          : $q->latest();
        }

        $solicitudes = $q->paginate(12)->withQueryString();
        $historial   = true;

        return view('admin.solicitudes.index', compact('solicitudes','historial'));
    }

    /** Aprobar: valida docs (si aplica), genera PDFs y notifica */
    public function approve(Solicitud $solicitud)
    {
        // Si la empresa requiere documentos, valida que existan
        if ($this->requiereDocs($solicitud) &&
            (empty($solicitud->doc_cedula_path) || empty($solicitud->cert_bancario_path))) {
            return back()->withErrors([
                'docs' => 'Para esta empresa es obligatorio tener CÉDULA y CERTIFICADO BANCARIO adjuntos antes de aprobar.',
            ]);
        }

        $solicitud->update([
            'status'            => 'aprobada',
            'fecha_aprobacion'  => now(),
            'propuesta_estado'  => null,
        ]);

        // Genera y guarda amortización + certificado (se ven en la app)
        PdfSolicitudService::generarYGuardar($solicitud);

        // Notificaciones (sin adjuntar PDFs)
        $toUser  = trim((string) $solicitud->email);
        $toAdmin = optional(auth()->user())->email;

        try {
            if (filter_var($toUser, FILTER_VALIDATE_EMAIL)) {
                Mail::to($toUser)->send(new SolicitudAprobadaMail($solicitud, false));
            }
            if ($toAdmin) {
                Mail::to($toAdmin)->send(new SolicitudAprobadaMail($solicitud, true));
            }
        } catch (\Throwable $e) {
            Log::warning('Error correo aprobada: '.$e->getMessage(), ['solicitud'=>$solicitud->id]);
        }

        return back()->with('success', "Solicitud #{$solicitud->id} aprobada. PDFs generados.");
    }

    /** Rechazar: notifica */
    public function reject(Solicitud $solicitud)
    {
        $solicitud->update([
            'status'           => 'rechazada',
            'propuesta_estado' => null,
        ]);

        $toUser  = trim((string) $solicitud->email);
        $toAdmin = optional(auth()->user())->email;

        try {
            if (filter_var($toUser, FILTER_VALIDATE_EMAIL)) {
                Mail::to($toUser)->send(new SolicitudRechazadaMail($solicitud, false));
            }
            if ($toAdmin) {
                Mail::to($toAdmin)->send(new SolicitudRechazadaMail($solicitud, true));
            }
        } catch (\Throwable $e) {
            Log::warning('Error correo rechazada: '.$e->getMessage(), ['solicitud'=>$solicitud->id]);
        }

        return back()->with('success', "Solicitud #{$solicitud->id} rechazada.");
    }

    /** Contraoferta */
    public function counter(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'propuesta_monto'        => ['required','numeric','min:1'],
            'propuesta_plazo_meses'  => ['required','integer','min:1'],
            'propuesta_mensaje'      => ['nullable','string','max:2000'],
        ]);

        $solicitud->update([
            'propuesta_por'          => auth()->id(),
            'propuesta_monto'        => $data['propuesta_monto'],
            'propuesta_plazo_meses'  => $data['propuesta_plazo_meses'],
            'propuesta_mensaje'      => $data['propuesta_mensaje'] ?? null,
            'propuesta_estado'       => 'enviada',
            'propuesta_enviada_at'   => now(),
            'status'                 => 'pendiente',
        ]);

        $toUser = trim((string) $solicitud->email);
        try {
            if (filter_var($toUser, FILTER_VALIDATE_EMAIL)) {
                Mail::to($toUser)->send(new PropuestaEnviadaMail($solicitud));
            }
        } catch (\Throwable $e) {
            Log::warning('Error correo propuesta: '.$e->getMessage(), ['solicitud'=>$solicitud->id]);
        }

        return back()->with('success', "Propuesta enviada para la solicitud #{$solicitud->id}.");
    }

    /* ============
     *  Adjuntos
     * ============*/

    /** Subir/actualizar PDF del admin (visible para todos los admins) */
    public function uploadAdminPdf(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'admin_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ]);

        if ($solicitud->admin_pdf_path) {
            Storage::disk('public')->delete($solicitud->admin_pdf_path);
        }

        $path = $request->file('admin_pdf')
                       ->store("solicitudes/{$solicitud->id}/admin", 'public');

        $solicitud->update(['admin_pdf_path' => $path]);

        return back()->with('success', 'PDF del administrador adjuntado correctamente.');
    }

    /** Ver/descargar el PDF del admin */
    public function viewAdminPdf(Solicitud $solicitud)
    {
        abort_unless($solicitud->admin_pdf_path, 404);
        return response()->file(storage_path('app/public/'.$solicitud->admin_pdf_path));
    }

    /** Ver cédula o certificado bancario del usuario (solo admin/super-admin) */
    public function viewUserDoc(Solicitud $solicitud, string $tipo)
    {
        $cols = [
            'cedula' => 'doc_cedula_path',
            'banco'  => 'cert_bancario_path',
        ];
        abort_unless(isset($cols[$tipo]), 404);
        $col = $cols[$tipo];
        abort_unless($solicitud->$col, 404);

        return response()->file(storage_path('app/public/'.$solicitud->$col));
    }

    /* Helpers */
    private function requiereDocs(Solicitud $s): bool
    {
        return in_array(strtolower((string) $s->empresa_key), self::EMPRESAS_REQUIERE_DOCS, true);
    }
}
