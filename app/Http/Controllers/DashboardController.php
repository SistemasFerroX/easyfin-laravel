<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Sólo usuarios autenticados
        $this->middleware('auth');
    }

    /**
     * Muestra el dashboard con estadísticas de solicitudes,
     * adaptadas según el rol del usuario.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Partimos de todas las solicitudes...
        $query = Solicitud::query();

        // Super-admin ve TODO
        if ($user->hasRole('super-admin')) {
            // nada más que hacer
        }
        // Admin ve sólo las de su empresa (si tiene columna empresa)
        elseif ($user->hasRole('admin')) {
            if (! empty($user->empresa)) {
                $query->where('empresa', $user->empresa);
            }
        }
        // Usuario normal ve sólo las suyas
        else {
            $query->where('user_id', $user->id);
        }

        // Agregamos los conteos en una sola consulta
        $stats = $query
            ->selectRaw(/**/
                'COUNT(*)                              AS total,
                 SUM(status = "pendiente")             AS pendientes,
                 SUM(status = "aprobada")              AS aprobadas,
                 SUM(status = "rechazada")             AS rechazadas'
            )
            ->first();

        // Extraemos los valores (o 0 si es null)
        $total      = $stats->total      ?? 0;
        $pendientes = $stats->pendientes ?? 0;
        $aprobadas  = $stats->aprobadas  ?? 0;
        $rechazadas = $stats->rechazadas ?? 0;

        // Para el bloque @role('user') en la vista
        $totalUsuario      = $total;
        $pendientesUsuario = $pendientes;
        $aprobadasUsuario  = $aprobadas;
        $rechazadasUsuario = $rechazadas;

        return view('dashboard', compact(
            'user',
            // para admin / super-admin
            'total', 'pendientes', 'aprobadas', 'rechazadas',
            // para user
            'totalUsuario', 'pendientesUsuario', 'aprobadasUsuario', 'rechazadasUsuario'
        ));
    }
}
