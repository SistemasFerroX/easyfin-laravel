{{-- resources/views/solicitudes/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Mis Solicitudes
  </h2>
@endsection

@section('content')
  <div class="container py-5">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($solicitudes->isEmpty())
      <div class="alert alert-info">
        No tienes solicitudes registradas.
      </div>
    @else
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Monto</th>
            <th>Plazo (meses)</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          @foreach($solicitudes as $sol)
            <tr>
              <td>{{ $sol->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $sol->nombre_completo }}</td>
              <td>${{ number_format($sol->monto_solicitado, 0, ',', '.') }}</td>
              <td>{{ $sol->plazo_meses ?? '—' }}</td>
              <td>
                {{-- Asumiendo que tienes un campo status --}}
                {{ $sol->status ?? 'Pendiente' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <a href="{{ route('solicitudes.create') }}" class="btn btn-primary">
      Nueva Solicitud
    </a>
  </div>
@endsection
{{-- resources/views/solicitudes/index.blade.php --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Mis Solicitudes
  </h2>
@endsection

@section('content')
  <div class="container py-5">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($solicitudes->isEmpty())
      <div class="alert alert-info">
        No tienes solicitudes registradas.
      </div>
    @else
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Monto</th>
            <th>Plazo (meses)</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          @foreach($solicitudes as $sol)
            <tr>
              <td>{{ $sol->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $sol->nombre_completo }}</td>
              <td>${{ number_format($sol->monto_solicitado, 0, ',', '.') }}</td>
              <td>{{ $sol->plazo_meses ?? '—' }}</td>
              <td>
                {{-- Asumiendo que tienes un campo status --}}
                {{ $sol->status ?? 'Pendiente' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <a href="{{ route('solicitudes.create') }}" class="btn btn-primary">
      Nueva Solicitud
    </a>
  </div>
@endsection
