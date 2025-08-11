{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('title', 'EasyFin – Mi Perfil')

@section('content')
<div class="profile-wrapper">
  <div class="profile-card">
    <header class="profile-head">
      <div class="user-block">
        <div class="avatar" aria-hidden="true">
          {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <div>
          <h1 class="title">Mi perfil</h1>
          <p class="muted">Actualiza tu información y, si deseas, cambia tu contraseña.</p>
        </div>
      </div>

      <div class="meta">
        <div class="pill">ID: #{{ auth()->id() }}</div>
        @if(isset($roles) && $roles->count())
          <div class="pill">{{ $roles->join(' · ') }}</div>
        @endif
      </div>
    </header>

    {{-- Alertas --}}
    @if (session('success'))
      <div class="alert success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert error">Revisa los campos marcados en rojo.</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="form" id="profileForm">
      @csrf
      @method('PATCH')

      <section class="section">
        <div class="section-head">
          <div class="section-icon">👤</div>
          <h2>Información básica</h2>
        </div>

        <div class="grid-2">
          <div class="field">
            <label for="name">Nombre</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
            @error('name') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <small class="error">{{ $message }}</small> @enderror
          </div>
        </div>
      </section>

      <section class="section">
        <div class="section-head">
          <div class="section-icon">🔒</div>
          <h2>Cambiar contraseña <small class="muted">(opcional)</small></h2>
        </div>

        <div class="grid-3">
          <div class="field">
            <label for="current_password">Contraseña actual</label>
            <div class="input-group">
              <input id="current_password" name="current_password" type="password" autocomplete="current-password">
              <button type="button" class="eye" data-toggle="#current_password" aria-label="Mostrar/Ocultar">👁</button>
            </div>
            @error('current_password') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="password">Nueva contraseña</label>
            <div class="input-group">
              <input id="password" name="password" type="password" autocomplete="new-password">
              <button type="button" class="eye" data-toggle="#password" aria-label="Mostrar/Ocultar">👁</button>
            </div>
            @error('password') <small class="error">{{ $message }}</small> @enderror
          </div>

          <div class="field">
            <label for="password_confirmation">Confirmar nueva</label>
            <div class="input-group">
              <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
              <button type="button" class="eye" data-toggle="#password_confirmation" aria-label="Mostrar/Ocultar">👁</button>
            </div>
          </div>
        </div>
      </section>

      <footer class="actions">
        <a href="{{ route('dashboard') }}" class="btn-ghost">← Volver</a>
        <button type="submit" class="btn-primary">Guardar cambios</button>
      </footer>
    </form>
  </div>

  <div class="danger-card">
    <h3>Zona peligrosa</h3>
    <p class="muted">Eliminar tu cuenta es irreversible.</p>
    <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('¿Seguro que deseas eliminar tu cuenta? Esta acción no se puede deshacer.');">
      @csrf
      @method('DELETE')
      <div class="danger-actions">
        <input type="password" name="password" placeholder="Contraseña actual" required>
        <button type="submit" class="btn-danger">Eliminar cuenta</button>
      </div>
      @error('password') <small class="error">{{ $message }}</small> @enderror
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Mostrar / ocultar contraseñas
  document.querySelectorAll('.eye').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.querySelector(btn.dataset.toggle);
      if (!input) return;
      input.type = input.type === 'password' ?
