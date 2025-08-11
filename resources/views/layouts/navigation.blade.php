{{-- resources/views/layouts/navigation.blade.php --}}
<nav class="app-topbar relative bg-white border-b border-gray-100">
  {{-- Logo en el gutter de la sidebar --}}
  <a href="{{ route('dashboard') }}" class="topbar-logo">
    <img src="{{ asset('img/logo-easyfin.png') }}" alt="EASYFIN" class="h-7 w-auto">
  </a>

  {{-- Contenedor que reserva espacio para el logo --}}
  <div class="topbar-inner max-w-7xl mx-auto px-2 sm:px-3 lg:px-4">
    <div class="flex justify-between h-16">

      {{-- Izquierda: links --}}
      <div class="flex items-center gap-5">
        <div class="hidden sm:flex items-center gap-2">
          <a href="{{ route('dashboard') }}"
             class="px-3 py-2 rounded-lg font-semibold
                    {{ request()->routeIs('dashboard') ? 'text-[#25356C] bg-indigo-50' : 'text-gray-700 hover:text-[#25356C]' }}">
            Dashboard
          </a>

          <a href="{{ route('profile.edit') }}"
             class="px-3 py-2 rounded-lg font-semibold
                    {{ request()->routeIs('profile.*') ? 'text-[#25356C] bg-indigo-50' : 'text-gray-700 hover:text-[#25356C]' }}">
            Perfil
          </a>
        </div>
      </div>

      {{-- Derecha: nombre (sin dropdown) --}}
      <div class="hidden sm:flex items-center">
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 border border-gray-200
                  px-3 py-2 rounded-xl font-semibold hover:bg-indigo-50 hover:border-indigo-200">
          {{ Auth::user()->name }}
        </a>
      </div>

    </div>
  </div>
</nav>
