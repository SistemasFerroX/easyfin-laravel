<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>EasyFin – Iniciar sesión</title>
  <!-- Tu CSS de login -->
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-background">
    <div class="login-card">
      <div class="login-header">
        <img src="{{ asset('img/logo-easyfin.png') }}" alt="EasyFin Logo">
        <h2>Iniciar sesión</h2>
      </div>

      {{-- Ventana emergente de error --}}
      @if ($errors->any())
        <script>
          window.onload = function() {
            alert("{{ $errors->first() }}");
          }
        </script>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Usuario --}}
        <label for="email">Nombre de usuario:</label>
        <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus>

        {{-- Contraseña --}}
        <label for="password">Contraseña:</label>
        <input id="password" type="password" name="password" required>

        {{-- Botón --}}
        <button type="submit" class="btn-login">Ingresar</button>

        {{-- Forgot --}}
        @if(Route::has('password.request'))
          <p class="forgot">
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
          </p>
        @endif
      </form>
    </div>
  </div>
</body>
</html>