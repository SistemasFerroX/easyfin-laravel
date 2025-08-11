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
     * Listado de solicitudes:
     * - super-admin y admin ven todas
     * - user ve sólo las suyas
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Solicitud::query();

        if (!$user->hasAnyRole(['super-admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

        // Orden por defecto: recientes
        $solicitudes = $query->latest()->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Formulario nueva solicitud
     */
    public function create(Request $request)
    {
        return view('solicitudes.create');
    }

    /**
     * Guardar nueva solicitud
     */
    public function store(Request $request)
    {
        // Sanitizar monto: dejar SÓLO dígitos (permite que el usuario escriba 20.000.000)
        $rawMonto = preg_replace('/\D+/', '', (string) $request->input('monto_solicitado'));
        $request->merge(['monto_solicitado' => $rawMonto ?: null]);

        // 1) Validación con nombres reales de columnas
        $data = $request->validate([
            'nombre_completo'   => 'required|string|max:255',
            'identificacion'    => 'required|numeric',
            'fecha_nacimiento'  => 'required|date',
            'email'             => 'required|email|max:255',
            'telefono'          => 'required|numeric',
            'direccion'         => 'required|string|max:255',
            'empresa'           => 'nullable|string|max:255',
            'monto_solicitado'  => 'required|numeric|min:1',
            'plazo_meses'       => 'required|integer|min:1',
        ]);

        // 2) Campos adicionales
        $data['user_id']      = auth()->id();
        $data['status']       = 'pendiente';
        $data['tasa_interes'] = 2.2; // si luego lo tomas de parámetro, cámbialo aquí

        // 3) Crear
        Solicitud::create($data);

        // 4) Redirigir
        return redirect()
            ->route('solicitudes.index')
            ->with('success', '¡Solicitud enviada con éxito!');
    }

    public function approve(Solicitud $solicitud)
    {
        $solicitud->update([
            'status'           => 'aprobada',
            'fecha_aprobacion' => now(),
        ]);

        return back()->with('success', 'Solicitud aprobada.');
    }

    public function reject(Solicitud $solicitud)
    {
        $solicitud->update(['status' => 'rechazada']);

        return back()->with('success', 'Solicitud rechazada.');
    }

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
