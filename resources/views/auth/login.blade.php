<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EasyFin – Iniciar sesión</title>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">

  {{-- Toast styles mínimos (puedes moverlos a login.css si quieres) --}}
  <style>
    .toast{position:fixed;right:16px;top:16px;z-index:9999;display:none;min-width:260px;
           border-radius:12px;padding:12px 14px;box-shadow:0 10px 25px rgba(0,0,0,.12);
           font:14px/1.4 Roboto,system-ui,-apple-system,Segoe UI,Arial}
    .toast.show{display:block;animation:fadein .2s ease}
    .toast--error{background:#fee2e2;border:1px solid #fecaca;color:#7f1d1d}
    .toast--ok{background:#ecfeff;border:1px solid #a5f3fc;color:#0c4a6e}
    .toast b{display:block;margin-bottom:2px}
    @keyframes fadein{from{opacity:.0;transform:translateY(-4px)}to{opacity:1}}
  </style>
</head>
<body>
  <div class="login-background">
    <div class="login-card">
      <div class="login-header">
        <img src="{{ asset('img/logo-easyfin.png') }}" alt="EasyFin Logo">
        <h2>Iniciar sesión</h2>
      </div>

      {{-- Toasts: error de credenciales y mensaje de cierre de sesión --}}
      @if ($errors->any())
        <div id="toast-error" class="toast toast--error show" role="alert" aria-live="assertive">
          <b>Ups…</b> {{ $errors->first() ?: 'Usuario o contraseña incorrectos.' }}
        </div>
      @endif
      @if (session('status'))
        <div id="toast-ok" class="toast toast--ok show" role="status" aria-live="polite">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Usuario (email) --}}
        <label for="email">Correo electrónico:</label>
        <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus>

        {{-- Contraseña --}}
        <label for="password">Contraseña:</label>
        <input id="password" type="password" name="password" required>

        {{-- Botón --}}
        <button type="submit" class="btn-login">Ingresar</button>

        {{-- Olvidé contraseña --}}
        @if(Route::has('password.request'))
          <p class="forgot">
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
          </p>
        @endif
      </form>
    </div>
  </div>

  <script>
    // Ocultar toasts después de 4s
    setTimeout(()=>{ document.querySelectorAll('.toast.show').forEach(t=>t.classList.remove('show')); }, 4000);
  </script>
</body>
</html>
