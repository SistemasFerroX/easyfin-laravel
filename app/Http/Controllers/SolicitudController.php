<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitudAprobadaMail;
use App\Mail\SolicitudRechazadaMail;

class SolicitudController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * /solicitudes  -> SOLO PENDIENTES del usuario
     * Si es admin/super-admin, redirige al panel admin.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return redirect()->route('admin.solicitudes.index');
        }

        $q = Solicitud::query()
            ->where('user_id', $user->id)
            ->where('status', 'pendiente')
            ->latest();

        // Búsqueda simple (opcional)
        if ($term = trim((string) $request->input('q', ''))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('nombre_completo','like',"%{$term}%")
                   ->orWhere('identificacion','like',"%{$term}%");
                if (is_numeric($term)) $qq->orWhere('id',(int)$term);
            });
        }

        // Orden (opcional)
        switch ($request->input('order')) {
            case 'monto_desc': $q->orderByDesc('monto_solicitado'); break;
            case 'monto_asc':  $q->orderBy('monto_solicitado');    break;
            default:           $q->latest();
        }

        $solicitudes = $q->paginate(12)->withQueryString();
        $historial   = false;

        return view('solicitudes.index', compact('solicitudes', 'historial'));
    }

    /**
     * /solicitudes/historial -> APROBADAS + RECHAZADAS del usuario
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $q = Solicitud::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['aprobada','rechazada'])
            ->latest();

        if ($term = trim((string) $request->input('q', ''))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('nombre_completo','like',"%{$term}%")
                   ->orWhere('identificacion','like',"%{$term}%");
                if (is_numeric($term)) $qq->orWhere('id',(int)$term);
            });
        }

        switch ($request->input('order')) {
            case 'monto_desc': $q->orderByDesc('monto_solicitado'); break;
            case 'monto_asc':  $q->orderBy('monto_solicitado');    break;
            default:           $q->latest();
        }

        $solicitudes = $q->paginate(12)->withQueryString();
        $historial   = true;

        return view('solicitudes.index', compact('solicitudes', 'historial'));
    }

    /** Formulario nueva solicitud */
    public function create()
    {
        return view('solicitudes.create');
    }

    /** Guardar nueva solicitud */
    public function store(Request $request)
    {
        // Permite “20.000.000” en UI
        $rawMonto = preg_replace('/\D+/', '', (string) $request->input('monto_solicitado'));
        $request->merge(['monto_solicitado' => $rawMonto ?: null]);

        $data = $request->validate([
            'nombre_completo'   => ['required','string','max:255'],
            'identificacion'    => ['required','numeric'],
            'fecha_nacimiento'  => ['required','date'],
            'email'             => ['required','email','max:255'],
            'telefono'          => ['required','numeric'],
            'direccion'         => ['required','string','max:255'],
            'empresa'           => ['nullable','string','max:255'],
            'monto_solicitado'  => ['required','numeric','min:1'],
            'plazo_meses'       => ['required','integer','min:1'],
        ]);

        $data['user_id']      = $request->user()->id;
        $data['status']       = 'pendiente';
        $data['tasa_interes'] = 2.2; // ajusta según tu lógica

        Solicitud::create($data);

        return redirect()->route('solicitudes.index')
            ->with('success', '¡Solicitud enviada con éxito!');
    }

    /* =========================================================
     *   RESPUESTA DEL USUARIO A LA CONTRAOFERTA DEL ADMIN
     * ========================================================= */

    /** El usuario ACEPTA la propuesta => aprueba con condiciones propuestas */
    public function acceptProposal(Solicitud $solicitud)
    {
        abort_unless($solicitud->user_id === auth()->id(), 403, 'No autorizado.');
        if ($solicitud->propuesta_estado !== 'enviada') {
            return back()->with('error', 'No hay una propuesta pendiente.');
        }

        $solicitud->update([
            'monto_solicitado'     => $solicitud->propuesta_monto,
            'plazo_meses'          => $solicitud->propuesta_plazo_meses,
            'status'               => 'aprobada',
            'fecha_aprobacion'     => now(),
            'propuesta_estado'     => 'aceptada',
        ]);

        // Notifica a usuario y al proponente (si existe relación)
        try {
            Mail::to(trim($solicitud->email))
                ->send(new SolicitudAprobadaMail($solicitud, false));
            if ($solicitud->proponente?->email) {
                Mail::to($solicitud->proponente->email)
                    ->send(new SolicitudAprobadaMail($solicitud, true));
            }
        } catch (\Throwable $e) {
            // Log::warning('Error mail aprobada por usuario: '.$e->getMessage());
        }

        return back()->with('success', 'Has aceptado la propuesta. ¡Solicitud aprobada!');
    }

    /** El usuario RECHAZA la propuesta => rechaza solicitud */
    public function rejectProposal(Solicitud $solicitud)
    {
        abort_unless($solicitud->user_id === auth()->id(), 403, 'No autorizado.');
        if ($solicitud->propuesta_estado !== 'enviada') {
            return back()->with('error', 'No hay una propuesta pendiente.');
        }

        $solicitud->update([
            'status'           => 'rechazada',
            'propuesta_estado' => 'rechazada',
        ]);

        try {
            Mail::to(trim($solicitud->email))
                ->send(new SolicitudRechazadaMail($solicitud, false));
            if ($solicitud->proponente?->email) {
                Mail::to($solicitud->proponente->email)
                    ->send(new SolicitudRechazadaMail($solicitud, true));
            }
        } catch (\Throwable $e) {
            // Log::warning('Error mail rechazada por usuario: '.$e->getMessage());
        }

        return back()->with('success', 'Has rechazado la propuesta.');
    }

    /** Informe simple (solo admin/super-admin vía rutas) */
    public function informes()
    {
        $stats = Solicitud::selectRaw(
            'COUNT(*) AS total,
             SUM(status = "pendiente") AS pendientes,
             SUM(status = "aprobada")  AS aprobadas,
             SUM(status = "rechazada") AS rechazadas'
        )->first();

        return view('solicitudes.informes', compact('stats'));
    }
}
