<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Notificaciones</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f2f6f9] text-[#263746]">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'notifications'])

	<main class="mx-auto flex w-full max-w-5xl flex-col gap-5 px-4 py-8 md:px-8">
		<div>
			<h1 class="text-2xl font-semibold text-[#123f57]">Centro de notificaciones</h1>
			<p class="mt-1 text-sm text-[#526777]">Consulta los movimientos relacionados con tus órdenes y documentos.</p>
		</div>

		@if (session('success'))
			<p role="status" class="rounded-xl border border-[#b7e3ce] bg-[#e8f8f0] px-4 py-3 text-sm text-[#145538]">
				{{ session('success') }}
			</p>
		@endif

		<section aria-label="Historial de notificaciones" class="flex flex-col gap-3">
			@forelse ($notifications as $notification)
				<article @class([
					'rounded-2xl border p-5 shadow-sm',
					'border-[#a8d7eb] bg-white' => $notification->read_at === null,
					'border-[#d7e1e8] bg-[#f9fbfc]' => $notification->read_at !== null,
				])>
					<div class="flex flex-wrap items-start justify-between gap-3">
						<div class="flex min-w-0 flex-col gap-1">
							<h2 class="font-semibold text-[#123f57]">{{ $notification->data['title'] ?? 'Notificación' }}</h2>
							<p class="text-sm leading-6 text-[#34495b]">{{ $notification->data['message'] ?? '' }}</p>
							<p class="text-xs text-[#647887]">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
						</div>

						@if ($notification->read_at === null)
							<form action="{{ route('notifications.read', $notification->id) }}" method="POST">
								@csrf
								<button type="submit" class="rounded-lg border border-[#07577f] px-3 py-2 text-xs font-semibold text-[#07577f] transition hover:bg-[#edf7fb] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f]">
									Marcar como leída
								</button>
							</form>
						@else
							<span class="rounded-full bg-[#e8eef2] px-3 py-1 text-xs font-medium text-[#526777]">Leída</span>
						@endif
					</div>
				</article>
			@empty
				<p class="rounded-2xl border border-[#d7e1e8] bg-white p-5 text-sm text-[#526777]">Aún no tienes notificaciones.</p>
			@endforelse
		</section>

		{{ $notifications->links() }}
	</main>
</body>
</html>
