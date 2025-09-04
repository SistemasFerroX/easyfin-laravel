<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SolicitudController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /* --------------------------------------------------------
     | Catálogos (usados por create/store)
     -------------------------------------------------------- */
    private function companies(): array
    {
        // Ajusta nombres/NIT según tu lista. Se pueden agregar más.
        return [
            'ferbienes'  => ['name' => 'INVERSIONES FERBIENES S.A.S.',      'nit' => '890924840-5'],
            'fertrans'   => ['name' => 'FERTRANS S.A.S',                     'nit' => '900108215-7'],
            'flexilog'   => ['name' => 'FLEXIBILIDAD LOGISTICA S.A.S',       'nit' => '901446513-1'],
            'ferraceros' => ['name' => 'FERRACEROS S.A.S',                   'nit' => '900061096-2'],
            'agrosigo'   => ['name' => 'AGROSIGO S.A.S',                     'nit' => null],
            'tribilin'   => ['name' => 'INVERSIONES TRIBILIN S.A.S',         'nit' => null],
            'catalan'    => ['name' => 'COMERCIALIZADORA CATALAN',           'nit' => null],
            'msg3'       => ['name' => 'INVERSIONES MSG3',                   'nit' => null],
            // Especial: requiere adjuntos
            'bravass'    => ['name' => 'CONFECCIONES BRAVASS (VANESSA)',     'nit' => '800148853-4'],
        ];
    }

    private function docTypes(): array
    {
        return ['cc','ce','ppt'];
    }

    /* --------------------------------------------------------
     | /solicitudes  -> solo PENDIENTES del usuario
     -------------------------------------------------------- */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return redirect()->route('admin.solicitudes.index');
        }

        $q = Solicitud::where('user_id', $user->id)
            ->where('status', 'pendiente')
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
        $historial   = false;

        return view('solicitudes.index', compact('solicitudes', 'historial'));
    }

    /* --------------------------------------------------------
     | /solicitudes/historial -> APROBADAS + RECHAZADAS
     -------------------------------------------------------- */
    public function history(Request $request)
    {
        $user = $request->user();

        $q = Solicitud::where('user_id', $user->id)
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

    /* --------------------------------------------------------
     | Formulario nueva solicitud
     -------------------------------------------------------- */
    public function create()
    {
        $docTypes  = $this->docTypes();     // ['cc','ce','ppt']
        $companies = $this->companies();    // catálogo de empresas
        return view('solicitudes.create', compact('docTypes','companies'));
    }

    /* --------------------------------------------------------
     | Guardar nueva solicitud
     -------------------------------------------------------- */
    public function store(Request $request)
    {
        // Permite “20.000.000”
        $rawMonto = preg_replace('/\D+/', '', (string) $request->input('monto_solicitado'));
        $request->merge(['monto_solicitado' => $rawMonto ?: null]);

        // 1) Validación base (empresa por clave del catálogo)
        $data = $request->validate([
            'nombre_completo'   => ['required','string','max:255'],
            'tipo_documento'    => ['required', Rule::in($this->docTypes())],
            'identificacion'    => ['required','numeric'],
            'fecha_nacimiento'  => ['required','date'],
            'email'             => ['required','email','max:255'],
            'telefono'          => ['required','numeric'],
            'direccion'         => ['required','string','max:255'],

            // El select envía la clave, no el nombre
            'empresa_key'       => ['required','string'],

            'monto_solicitado'  => ['required','numeric','min:1'],
            'plazo_meses'       => ['required','integer','min:1'],
        ]);

        // 2) Resolver empresa/nit desde la clave
        $catalog  = $this->companies();
        $key      = $data['empresa_key'];
        $empresa  = $catalog[$key] ?? null;

        if (!$empresa) {
            return back()->withErrors(['empresa_key' => 'Empresa no válida.'])->withInput();
        }

        $requiereAdjuntos = ($empresa['nit'] === '800148853-4'); // Bravass

        // 3) Validación condicional de adjuntos (solo Bravass)
        $request->validate([
            'doc_cedula'     => [$requiereAdjuntos ? 'required' : 'nullable','file','mimes:pdf,jpg,jpeg,png','max:5120'],
            'cert_bancario'  => [$requiereAdjuntos ? 'required' : 'nullable','file','mimes:pdf,jpg,jpeg,png','max:5120'],
        ]);

        // 4) Crear registro
        $solicitud = Solicitud::create([
            'user_id'          => $request->user()->id,
            'status'           => 'pendiente',
            'tasa_interes'     => 2.2, // ajusta según tu lógica

            'nombre_completo'  => $data['nombre_completo'],
            'tipo_documento'   => $data['tipo_documento'],
            'identificacion'   => $data['identificacion'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'email'            => $data['email'],
            'telefono'         => $data['telefono'],
            'direccion'        => $data['direccion'],

            'empresa'          => $empresa['name'],
            'empresa_nit'      => $empresa['nit'],

            'monto_solicitado' => $data['monto_solicitado'],
            'plazo_meses'      => $data['plazo_meses'],
        ]);

        // 5) Guardar adjuntos (si aplican)
        if ($requiereAdjuntos) {
            $dir = "solicitudes/{$solicitud->id}/adjuntos";
            $paths = [];

            if ($request->hasFile('doc_cedula')) {
                $f = $request->file('doc_cedula');
                $paths['doc_cedula_path'] = $f->storeAs($dir, 'doc_cedula.'.$f->getClientOriginalExtension(), 'public');
            }
            if ($request->hasFile('cert_bancario')) {
                $f = $request->file('cert_bancario');
                $paths['cert_bancario_path'] = $f->storeAs($dir, 'cert_bancario.'.$f->getClientOriginalExtension(), 'public');
            }

            if (!empty($paths)) {
                $solicitud->update($paths);
            }
        }

        return redirect()->route('solicitudes.index')
            ->with('success', '¡Solicitud enviada con éxito!');
    }

    /* =========================================================
     | RESPUESTA DEL USUARIO A LA CONTRAOFERTA DEL ADMIN
     ========================================================= */
    public function acceptProposal(Solicitud $solicitud)
    {
        abort_unless($solicitud->user_id === auth()->id(), 403, 'No autorizado.');
        if ($solicitud->propuesta_estado !== 'enviada') {
            return back()->with('error', 'No hay una propuesta pendiente.');
        }

        $solicitud->update([
            'monto_solicitado' => $solicitud->propuesta_monto,
            'plazo_meses'      => $solicitud->propuesta_plazo_meses,
            'status'           => 'aprobada',
            'fecha_aprobacion' => now(),
            'propuesta_estado' => 'aceptada',
        ]);

        return back()->with('success', 'Has aceptado la propuesta. ¡Solicitud aprobada!');
    }

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

        return back()->with('success', 'Has rechazado la propuesta.');
    }

    /** Informe simple (solo vía rutas protegidas para admin/super-admin) */
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
