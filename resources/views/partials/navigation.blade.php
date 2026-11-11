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
				<a href="{{ route('admin.system-reimbursements.index') }}" @class([
					'rounded-lg px-4 py-2 text-sm font-semibold transition',
					'bg-[#07577f] text-white' => $activePage === 'system-reimbursements',
					'text-[#34495b] hover:bg-[#edf7fb]' => $activePage !== 'system-reimbursements',
				]) @if ($activePage === 'system-reimbursements') aria-current="page" @endif>Reembolsos de Sistemas</a>
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
				{{ auth()->user()->isSupplier() ? 'Centro de notificaciones' : 'Notificaciones' }}
				@if ($unreadNotificationCount > 0)
					<span data-unread-notification-count aria-live="polite" class="ml-1 rounded-full bg-[#c94747] px-2 py-0.5 text-xs text-white">{{ $unreadNotificationCount }}</span>
				@endif
				<span aria-hidden="true">▾</span>
			</summary>
			<div class="absolute right-0 top-full z-40 mt-2 flex max-h-[min(70vh,32rem)] w-[min(22rem,calc(100vw-2rem))] flex-col gap-3 overflow-y-auto rounded-xl border border-[#d7e1e8] bg-white p-4 text-sm text-[#34495b] shadow-lg">
				<p class="font-semibold text-[#123f57]">{{ auth()->user()->isSupplier() ? 'Centro de notificaciones' : 'Notificaciones' }}</p>
				@if ($recentNotifications->isEmpty())
					<p>Aún no tienes notificaciones.</p>
				@else
					<ul class="flex flex-col gap-3">
						@foreach ($recentNotifications as $notification)
							<li @class([
								'rounded-lg border p-3',
								'border-[#a8d7eb] bg-[#f4fbfe]' => $notification->read_at === null,
								'border-[#e2e8ed] bg-[#f9fbfc]' => $notification->read_at !== null,
							]) data-notification-item data-notification-id="{{ $notification->id }}" data-notification-unread-classes="border-[#a8d7eb] bg-[#f4fbfe]" data-notification-read-classes="border-[#e2e8ed] bg-[#f9fbfc]">
								<p class="font-semibold text-[#123f57]">{{ $notification->data['title'] ?? 'Notificación' }}</p>
								<p class="mt-1 text-xs leading-5">{{ $notification->data['message'] ?? '' }}</p>
								<p class="mt-1 text-xs text-[#647887]">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
								@if ($notification->read_at === null)
									<form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="mt-2" data-notification-read-form data-notification-id="{{ $notification->id }}" data-read-label-class="text-xs font-medium text-[#526777]">
										@csrf
										<button type="submit" class="text-xs font-semibold text-[#07529a] underline underline-offset-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#07577f]">
											Marcar como leída
										</button>
									</form>
								@endif
							</li>
						@endforeach
					</ul>
				@endif
				<a href="{{ route('notifications.index') }}" class="text-center text-xs font-semibold text-[#07529a] underline underline-offset-2">Ver todas</a>
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
