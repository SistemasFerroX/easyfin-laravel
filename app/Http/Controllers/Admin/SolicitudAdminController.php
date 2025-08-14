<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitudAprobadaMail;
use App\Mail\SolicitudRechazadaMail;
use App\Mail\PropuestaEnviadaMail;

class SolicitudAdminController extends Controller
{
    /**
     * Pendientes (todas)
     */
    public function index(Request $request)
    {
        $q = Solicitud::withoutGlobalScopes()
            ->where('status', 'pendiente')
            ->latest();

        // Buscar
        if ($term = trim((string) $request->input('q', ''))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('nombre_completo', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%")
                   ->orWhere('identificacion', 'like', "%{$term}%");
                if (is_numeric($term)) $qq->orWhere('id', (int) $term);
            });
        }

        // Orden
        switch ($request->input('order')) {
            case 'monto_desc': $q->orderByDesc('monto_solicitado'); break;
            case 'monto_asc':  $q->orderBy('monto_solicitado');    break;
            default:           $q->latest();
        }

        $solicitudes = $q->paginate(12)->withQueryString();
        $historial   = false;

        return view('admin.solicitudes.index', compact('solicitudes','historial'));
    }

    /**
     * Historial (aprobadas + rechazadas, todas)
     */
    public function history(Request $request)
    {
        $q = Solicitud::withoutGlobalScopes()
            ->whereIn('status', ['aprobada','rechazada'])
            ->latest();

        // Buscar
        if ($term = trim((string) $request->input('q', ''))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('nombre_completo', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%")
                   ->orWhere('identificacion', 'like', "%{$term}%");
                if (is_numeric($term)) $qq->orWhere('id', (int) $term);
            });
        }

        // Orden
        switch ($request->input('order')) {
            case 'monto_desc': $q->orderByDesc('monto_solicitado'); break;
            case 'monto_asc':  $q->orderBy('monto_solicitado');    break;
            default:           $q->latest();
        }

        $solicitudes = $q->paginate(12)->withQueryString();
        $historial   = true;

        return view('admin.solicitudes.index', compact('solicitudes','historial'));
    }

    public function approve(Solicitud $solicitud)
    {
        $solicitud->update([
            'status'            => 'aprobada',
            'propuesta_estado'  => null,
        ]);

        $toUser  = trim((string) $solicitud->email);
        $toAdmin = optional(auth()->user())->email;

        try {
            if (filter_var($toUser, FILTER_VALIDATE_EMAIL)) {
                // al usuario (asunto: “¡Tu solicitud… fue aprobada!”)
                Mail::to($toUser)->send(new SolicitudAprobadaMail($solicitud, false));
            }
            if ($toAdmin) {
                // al admin (asunto: “Aprobaste la solicitud…”)
                Mail::to($toAdmin)->send(new SolicitudAprobadaMail($solicitud, true));
            }
        } catch (\Throwable $e) {
            Log::warning('Error correo aprobada: '.$e->getMessage(), ['solicitud'=>$solicitud->id]);
        }

        return back()->with('success', 'Solicitud aprobada.');
    }

    public function reject(Solicitud $solicitud)
    {
        $solicitud->update([
            'status'            => 'rechazada',
            'propuesta_estado'  => null,
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

        return back()->with('success', 'Solicitud rechazada.');
    }

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

        $toUser  = trim((string) $solicitud->email);
        $toAdmin = optional(auth()->user())->email;

        try {
            if (filter_var($toUser, FILTER_VALIDATE_EMAIL)) {
                Mail::to($toUser)->send(new PropuestaEnviadaMail($solicitud)); // usuario
            }
            if ($toAdmin) {
                // si quieres copia al admin (mismo contenido); o crea un mailable con asunto “Enviaste una propuesta…”
                // Mail::to($toAdmin)->send(new PropuestaEnviadaMail($solicitud));
            }
        } catch (\Throwable $e) {
            Log::warning('Error correo propuesta: '.$e->getMessage(), ['solicitud'=>$solicitud->id]);
        }

        return back()->with('success', 'Propuesta enviada al usuario.');
    }
}
