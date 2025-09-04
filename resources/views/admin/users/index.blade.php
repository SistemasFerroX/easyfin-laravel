@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
  <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('title','EasyFin – Usuarios')

@section('content')
<div class="solicitudes-wrapper">
  {{-- Head --}}
  <header class="page-head">
    <div class="page-title">
      <img src="{{ asset('img/logo-easyfin.png') }}" class="brand-mark" alt="EasyFin">
      <div>
        <h1>Gestión de Usuarios</h1>
        <p class="muted">Administra roles y accesos de la plataforma.</p>
      </div>
    </div>

    <a href="{{ route('admin.users.create') }}" class="btn-primary">Nuevo usuario</a>
  </header>

  {{-- Flash --}}
  @if(session('success')) <div class="alert success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert error">{{ session('error') }}</div>   @endif

  {{-- Filtros --}}
  <form method="GET" class="filters">
    <div class="field">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o email">
    </div>
    <div class="field">
      <select name="role" onchange="this.form.submit()">
        <option value="">Todos los roles</option>
        <option value="user"        @selected(request('role')==='user')>Usuario</option>
        <option value="admin"       @selected(request('role')==='admin')>Admin</option>
        <option value="super-admin" @selected(request('role')==='super-admin')>Super-admin</option>
      </select>
    </div>
    <button class="btn-ghost" type="submit">Filtrar</button>
  </form>

  {{-- Tabla --}}
  <div class="card table-card">
    <table class="nice-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Rol(es)</th>
          <th class="txt-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->getRoleNames()->implode(', ') }}</td>
            <td class="txt-right actions">
              <a href="{{ route('admin.users.edit', $u) }}" class="btn-ghost sm">Editar</a>
              <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                    onsubmit="return confirm('¿Eliminar este usuario?')">
                @csrf @method('DELETE')
                <button class="btn-danger sm" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="txt-center py-4">No hay usuarios</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination mt-3">
    {{ $users->withQueryString()->links() }}
  </div>
</div>
@endsection
