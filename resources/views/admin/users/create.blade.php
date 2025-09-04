@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
  <link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('title','EasyFin – Nuevo usuario')

@section('content')
<div class="solicitudes-wrapper">
  <header class="page-head">
    <div class="page-title">
      <img src="{{ asset('img/logo-easyfin.png') }}" class="brand-mark" alt="EasyFin">
      <div>
        <h1>Nuevo usuario</h1>
        <p class="muted">Crea cuentas para tu equipo.</p>
      </div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn-ghost">← Volver</a>
  </header>

  @if($errors->any()) <div class="alert error">Revisa los datos del formulario.</div> @endif

  <div class="form-card">
    <form action="{{ route('admin.users.store') }}" method="POST">
      @csrf

      <div class="grid-2">
        <div class="field">
          <label>Nombre</label>
          <input type="text" name="name" value="{{ old('name') }}"
                 class="@error('name') invalid @enderror" required maxlength="255">
          @error('name')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email') }}"
                 class="@error('email') invalid @enderror" required maxlength="255">
          @error('email')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
          <label>Rol</label>
          <select name="role" class="@error('role') invalid @enderror" required>
            <option value="user"        @selected(old('role')==='user')>Usuario</option>
            <option value="admin"       @selected(old('role')==='admin')>Admin</option>
            <option value="super-admin" @selected(old('role')==='super-admin')>Super-admin</option>
          </select>
          @error('role')<small class="error">{{ $message }}</small>@enderror
        </div>
      </div>

      <p class="muted mt-2">Se enviará un enlace al correo para establecer la contraseña.</p>

      <footer class="form-actions">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost">Cancelar</a>
        <button class="btn-primary">Crear usuario</button>
      </footer>
    </form>
  </div>
</div>
@endsection
