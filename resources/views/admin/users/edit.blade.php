@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h2>Editar Usuario</h2>

  <form action="{{ route('admin.users.update',$user) }}" method="POST" class="mt-3">
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="name" value="{{ old('name',$user->name) }}"
             class="form-control @error('name') is-invalid @enderror">
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" value="{{ old('email',$user->email) }}"
             class="form-control @error('email') is-invalid @enderror">
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Rol</label>
      <select name="role" class="form-select @error('role') is-invalid @enderror">
        <option value="user" {{ $user->role=='user'?'selected':'' }}>Usuario</option>
        <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
        <option value="superadmin" {{ $user->role=='superadmin'?'selected':'' }}>SuperAdmin</option>
      </select>
      @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-success">Guardar cambios</button>
  </form>
</div>
@endsection
