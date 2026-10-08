<nav aria-label="Navegación principal" class="relative z-30 flex flex-wrap items-center justify-between gap-2 border-b border-[#d7e1e8] bg-white px-3 py-2 shadow-sm md:px-6">
	<div class="flex flex-wrap items-center gap-1">
		<a href="{{ route('home') }}" @class([
			'rounded-lg px-4 py-2 text-sm font-semibold transition',
			'bg-[#07577f] text-white' => $activePage === 'home',
			'text-[#34495b] hover:bg-[#edf7fb]' => $activePage !== 'home',
		]) @if ($activePage === 'home') aria-current="page" @endif>Inicio</a>
		@if (! auth()->user()->isSupplier())
			<a href="{{ route('suppliers.index') }}" @class([
				'rounded-lg px-4 py-2 text-sm font-semibold transition',
				'bg-[#07577f] text-white' => $activePage === 'suppliers',
				'text-[#34495b] hover:bg-[#edf7fb]' => $activePage !== 'suppliers',
			]) @if ($activePage === 'suppliers') aria-current="page" @endif>Catálogo de proveedores</a>
			@if (auth()->user()->isAdministrator())
				<a href="{{ route('admin.users.index') }}" @class([
					'rounded-lg px-4 py-2 text-sm font-semibold transition',
					'bg-[#07577f] text-white' => $activePage === 'admin-users',
					'text-[#34495b] hover:bg-[#edf7fb]' => $activePage !== 'admin-users',
				]) @if ($activePage === 'admin-users') aria-current="page" @endif>Administrar usuarios</a>
			@endif
		@endif
	</div>

	<div class="flex flex-wrap items-center gap-1">
		<details class="relative">
			<summary class="cursor-pointer list-none rounded-lg px-3 py-2 text-sm font-semibold text-[#34495b] transition hover:bg-[#edf7fb] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#07577f]">
				<svg class="mr-1 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75a2.25 2.25 0 0 1-4.5 0m9-2.25H5.25c1.1-1.14 1.5-2.39 1.5-4.5v-1.5a5.25 5.25 0 0 1 10.5 0V12c0 2.11.4 3.36 1.5 4.5Z" />
				</svg>
				{{ auth()->user()->isSupplier() ? 'Centro de notificaciones' : 'Notificaciones' }} <span aria-hidden="true">▾</span>
			</summary>
			<div class="absolute right-0 top-full z-40 mt-2 w-64 rounded-xl border border-[#d7e1e8] bg-white p-4 text-sm text-[#34495b] shadow-lg">
				<p class="font-semibold text-[#123f57]">{{ auth()->user()->isSupplier() ? 'Centro de notificaciones' : 'Notificaciones' }}</p>
				<p class="mt-2">No tienes notificaciones nuevas.</p>
			</div>
		</details>

		<details class="relative">
			<summary class="cursor-pointer list-none rounded-lg px-3 py-2 text-sm font-semibold text-[#34495b] transition hover:bg-[#edf7fb] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#07577f]">
				{{ auth()->user()->isSupplier() ? 'Ayuda' : 'Cuenta / Ayuda' }} <span aria-hidden="true">▾</span>
			</summary>
			<div class="absolute right-0 top-full z-40 mt-2 w-72 rounded-xl border border-[#d7e1e8] bg-white p-4 text-sm text-[#34495b] shadow-lg">
				@if (! auth()->user()->isSupplier())
					<p class="font-semibold text-[#123f57]">{{ auth()->user()->name }}</p>
					<p class="mt-1 break-all">{{ auth()->user()->email }}</p>
					<p>{{ auth()->user()->department?->name }}</p>
					<hr class="my-3 border-[#e2e8ed]" />
				@endif
				<p class="font-semibold text-[#123f57]">Ayuda</p>
				<p class="mt-1">
					@if (auth()->user()->isSupplier())
						Consulta tus órdenes de compra y revisa las indicaciones de carga de documentos. Para asistencia, contacta al administrador del sistema.
					@else
						Usa la barra para ir al inicio o consultar el catálogo de proveedores. Para asistencia, contacta al administrador del sistema.
					@endif
				</p>
			</div>
		</details>

		<form action="{{ route('logout') }}" method="POST">
			@csrf
			<button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#8b3030] transition hover:bg-[#fff0f0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#8b3030]">
				Cerrar sesión
			</button>
		</form>
	</div>
</nav>
