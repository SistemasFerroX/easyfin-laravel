@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h2>Gestión de Usuarios</h2>
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-striped mt-3">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $u)
      <tr>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ ucfirst($u->role) }}</td>
        <td>
          <a href="{{ route('admin.users.edit',$u) }}" class="btn btn-sm btn-primary">Editar</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{ $users->links() }}
</div>
@endsection
