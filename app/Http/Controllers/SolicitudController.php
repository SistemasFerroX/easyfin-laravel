<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class SolicitudController extends Controller
{
    public function __construct()
    {
        // Sólo usuarios autenticados con rol super-admin, admin o user
        $this->middleware(['auth', 'role:super-admin|admin|user']);
    }

    /**
     * Mostrar listado de solicitudes según rol:
     * - super-admin y admin ven todas las solicitudes
     * - user ve solo sus propias solicitudes
     */
    public function index()
    {
        $user = auth()->user();
        $query = Solicitud::query();

        if (! $user->hasAnyRole(['super-admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        $solicitudes = $query->latest()->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Mostrar el formulario para crear una nueva solicitud.
     */
    public function create(Request $request)
    {
        // Si quieres depurar:
        // dd(
        //     'Roles: ' . $request->user()->getRoleNames()->join(', '),
        //     'hasRole("user")? ' . ($request->user()->hasRole('user') ? '✅' : '❌')
        // );

        return view('solicitudes.create');
    }

    /**
     * Almacenar una nueva solicitud en la base de datos.
     */
    public function store(Request $request)
    {
        // 1) Validación
        $data = $request->validate([
            'nombre_completo'   => 'required|string|max:255',
            'identificacion'    => 'required|numeric',
            'fecha_nacimiento'  => 'required|date',
            'email'             => 'required|email|max:255',
            'telefono'          => 'required|numeric',
            'direccion'         => 'required|string|max:255',
            'empresa'           => 'nullable|string|max:255',
            'monto_solicitado'  => 'required|numeric',
            'plazo_meses'       => 'required|integer|min:1',
        ]);

        // 2) Campos adicionales
        $data['user_id']     = auth()->id();
        $data['status']      = 'pendiente';
        $data['tasa_interes']= 2.2;

        // 3) Crear registro
        Solicitud::create($data);

        // 4) Redirigir con mensaje
        return redirect()
            ->route('solicitudes.index')
            ->with('success', '¡Solicitud enviada con éxito!');
    }

    /**
     * Aprobar una solicitud (sólo admin/super-admin).
     */
    public function approve(Solicitud $solicitud)
    {
        $solicitud->update([
            'status'           => 'aprobada',
            'fecha_aprobacion' => now(),
        ]);

        return back()->with('success', 'Solicitud aprobada.');
    }

    /**
     * Rechazar una solicitud (sólo admin/super-admin).
     */
    public function reject(Solicitud $solicitud)
    {
        $solicitud->update(['status' => 'rechazada']);

        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Mostrar informes agregados (sólo admin/super-admin).
     */
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
