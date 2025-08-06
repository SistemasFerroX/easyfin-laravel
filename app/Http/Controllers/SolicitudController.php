<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class SolicitudController extends Controller
{
    /**
     * Mostrar listado de solicitudes del usuario autenticado.
     */
    public function index()
    {
        // Obtiene solo las solicitudes del usuario actual, ordenadas por fecha
        $solicitudes = Solicitud::where('user_id', auth()->id())
                                 ->latest()
                                 ->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Mostrar el formulario para crear una nueva solicitud.
     */
    public function create()
    {
        return view('solicitudes.create');
    }

    /**
     * Almacenar una solicitud nueva en la base de datos.
     */
    public function store(Request $r)
    {
        // 1) Validación
        $data = $r->validate([
            'nombre_completo'   => 'required|string',
            'identificacion'    => 'required|numeric',
            'fecha_nacimiento'  => 'required|date',
            'email'             => 'required|email',
            'telefono'          => 'required|numeric',
            'direccion'         => 'required|string',
            'monto_solicitado'  => 'required|numeric',
            'plazo_meses'       => 'nullable|integer|min:1',
        ]);

        // 2) Añadir campos extra antes de crear
        $data['tasa_interes'] = 2.2;
        $data['user_id']      = auth()->id();

        // 3) Crear registro
        Solicitud::create($data);

        // 4) Redirigir al listado con mensaje de éxito
        return redirect()
            ->route('solicitudes.index')
            ->with('success', '¡Solicitud enviada con éxito!');
    }
}
