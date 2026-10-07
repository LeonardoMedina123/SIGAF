<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Administración de usuarios</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f4f4] text-[#161616] antialiased">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'admin-users'])

	<main class="px-3 pb-6 pt-5 md:px-6">
		<div class="mb-5">
			<p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#087aa7]">Administración</p>
			<h1 class="mt-1 text-2xl font-semibold text-[#123f57]">Administrar usuarios</h1>
			<p class="mt-1 text-sm text-[#52636c]">Crea cuentas, consulta usuarios y actualiza sus datos y tipo de acceso.</p>
		</div>

		@if (session('success'))
			<p role="status" class="mb-4 rounded-lg bg-[#e8f8f0] px-4 py-3 text-sm font-medium text-[#145538]">{{ session('success') }}</p>
		@endif
		@if ($errors->any())
			<div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
				<p class="font-semibold">No se pudo completar la operación. Revisa los datos e inténtalo de nuevo.</p>
				<ul class="mt-1 list-inside list-disc">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<details @if ($errors->any()) open @endif class="mb-5 overflow-hidden rounded-2xl border border-[#d7e1e8] bg-white shadow-sm">
			<summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-[#07577f] hover:bg-[#f4fafc]">+ Dar de alta un usuario</summary>
			<form action="{{ route('admin.users.store') }}" method="POST" class="grid gap-4 border-t border-[#e3ebef] p-5 md:grid-cols-2">
				@csrf
				<label class="grid gap-1 text-sm font-medium text-[#34495b]">Nombre completo
					<input name="name" required maxlength="255" value="{{ old('name') }}" autocomplete="name" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
				</label>
				<label class="grid gap-1 text-sm font-medium text-[#34495b]">Correo electrónico
					<input name="email" type="email" required maxlength="255" value="{{ old('email') }}" autocomplete="email" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
				</label>
				<label class="grid gap-1 text-sm font-medium text-[#34495b]">Tipo de usuario
					<select name="department_id" required class="h-10 rounded-lg border border-[#ccd8de] bg-white px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20">
						<option value="">Selecciona un tipo</option>
						@foreach ($departments as $department)
							<option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
						@endforeach
					</select>
				</label>
				<div class="grid gap-4 sm:grid-cols-2">
					<label class="grid gap-1 text-sm font-medium text-[#34495b]">Contraseña
						<input name="password" type="password" required minlength="8" autocomplete="new-password" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
					</label>
					<label class="grid gap-1 text-sm font-medium text-[#34495b]">Confirmar contraseña
						<input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
					</label>
				</div>
				<div class="flex justify-end md:col-span-2">
					<button type="submit" class="rounded-lg bg-[#07577f] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#064967]">Crear usuario</button>
				</div>
			</form>
		</details>

		<section aria-label="Usuarios registrados" class="overflow-x-auto rounded-[20px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
			<table class="w-full min-w-[760px] border-separate border-spacing-0 text-left">
				<thead class="bg-[#07577f] text-white">
					<tr>
						<th scope="col" class="rounded-tl-[19px] px-4 py-3 text-sm font-semibold">Nombre</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Correo electrónico</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Tipo de usuario</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Alta</th>
						<th scope="col" class="rounded-tr-[19px] px-4 py-3 text-sm font-semibold">Acciones</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($users as $user)
						<tr class="text-sm text-[#263b46] odd:bg-white even:bg-[#f5f9fb]">
							<td class="border-t border-[#d3dfe5] px-4 py-3 font-medium">{{ $user->name }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $user->email }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $user->department?->name ?? 'Sin tipo' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $user->created_at->format('d/m/Y') }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">
								<a href="#user-edit-{{ $user->id }}" onclick="document.querySelector('#user-edit-{{ $user->id }} details').open = true" class="font-semibold text-[#07577f] hover:underline" aria-label="Editar a {{ $user->name }}">Editar</a>
							</td>
						</tr>
						<tr id="user-edit-{{ $user->id }}">
							<td colspan="5" class="border-t border-[#d3dfe5] bg-[#f5f9fb] px-4 py-4">
								<details>
									<summary class="cursor-pointer text-sm font-semibold text-[#07577f]">Editar {{ $user->name }}</summary>
									<div class="mt-4 rounded-xl border border-[#d7e1e8] bg-white p-4">
										<form action="{{ route('admin.users.update', $user) }}" method="POST" class="grid gap-4 md:grid-cols-2">
											@csrf
											@method('PUT')
											<label class="grid gap-1 text-sm font-medium text-[#34495b]">Nombre completo
												<input name="name" required maxlength="255" value="{{ $user->name }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
											</label>
											<label class="grid gap-1 text-sm font-medium text-[#34495b]">Correo electrónico
												<input name="email" type="email" required maxlength="255" value="{{ $user->email }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
											</label>
											<label class="grid gap-1 text-sm font-medium text-[#34495b]">Tipo de usuario
												<select name="department_id" required class="h-10 rounded-lg border border-[#ccd8de] bg-white px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20">
													@foreach ($departments as $department)
														<option value="{{ $department->id }}" @selected((int) $user->department_id === (int) $department->id)>{{ $department->name }}</option>
													@endforeach
												</select>
											</label>
											<label class="grid gap-1 text-sm font-medium text-[#34495b]">Nueva contraseña <span class="font-normal text-[#657780]">Déjala vacía para conservar la actual.</span>
												<input name="password" type="password" minlength="8" autocomplete="new-password" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
											</label>
											<label class="grid gap-1 text-sm font-medium text-[#34495b] md:col-span-2">Confirmar nueva contraseña
												<input name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="h-10 rounded-lg border border-[#ccd8de] px-3 font-normal text-[#17252b] focus:border-[#087aa7] focus:outline-none focus:ring-2 focus:ring-[#087aa7]/20" />
											</label>
											<div class="flex flex-wrap items-center justify-between gap-3 md:col-span-2">
												@if ($user->is(auth()->user()))
													<p class="text-xs text-[#657780]">No puedes eliminar la cuenta con la que iniciaste sesión.</p>
												@else
													<button type="submit" form="delete-user-{{ $user->id }}" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-[#a13232] transition hover:bg-red-50" onclick="return confirm('¿Eliminar este usuario?')">Eliminar usuario</button>
												@endif
												<button type="submit" class="rounded-lg bg-[#07577f] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#064967]">Guardar cambios</button>
											</div>
										</form>
										@if (! $user->is(auth()->user()))
											<form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="hidden">
												@csrf
												@method('DELETE')
											</form>
										@endif
									</div>
								</details>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="border-t border-[#d3dfe5] px-4 py-14 text-center text-sm text-[#60737d]">No hay usuarios registrados.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</section>
		<div class="mt-4">{{ $users->links() }}</div>
	</main>
</body>
</html>
