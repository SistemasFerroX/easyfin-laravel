<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con estadísticas de solicitudes.
     */
    public function index(Request $request)
    {
        // ① Obtiene el usuario autenticado
        $user = $request->user();

        // ② Prepara la consulta según rol
        $query = Solicitud::query();
        if ($user->role === 'admin') {
            // Si tu tabla tiene columna 'empresa', sino quita esta línea
            $query->where('empresa', $user->empresa);
        } elseif ($user->role !== 'superadmin') {
            // Usuarios “normales” solo ven sus propias solicitudes
            $query->where('user_id', $user->id);
        }

        // ③ Calcula los totales
        $stats = $query
            ->selectRaw('
                COUNT(*)                          AS total,
                SUM(status = "pendiente")         AS pendientes,
                SUM(status = "aprobada")          AS aprobadas,
                SUM(status = "rechazada")         AS rechazadas
            ')
            ->first();

        // ④ Pasa todo a la vista
        return view('dashboard', [
            'user'       => $user,
            'total'      => $stats->total      ?? 0,
            'pendientes' => $stats->pendientes ?? 0,
            'aprobadas'  => $stats->aprobadas  ?? 0,
            'rechazadas' => $stats->rechazadas ?? 0,
        ]);
    }
}
