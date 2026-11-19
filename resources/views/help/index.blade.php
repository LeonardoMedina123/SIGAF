<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/png" href="{{ asset('favicom.png') }}">
	<title>SIGAF | Ayuda</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f2f6f9] text-[#263746]">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'help'])

	<main class="mx-auto flex w-full max-w-5xl flex-col gap-5 px-4 py-8 md:px-8">
		<header>
			<h1 class="text-2xl font-semibold text-[#123f57]">Ayuda y contacto</h1>
			<p class="mt-1 text-sm text-[#526777]">Envía un mensaje desde SIGAF o comunícate directamente con administración.</p>
		</header>

		@if (session('success'))
			<p role="status" class="rounded-xl border border-[#b7e3ce] bg-[#e8f8f0] px-4 py-3 text-sm text-[#145538]">
				{{ session('success') }}
			</p>
		@endif

		@if ($errors->any())
			<div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
				<ul class="list-inside list-disc">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<section class="grid gap-5 md:grid-cols-2">
			<article class="rounded-2xl border border-[#d7e1e8] bg-white p-5 shadow-sm">
				<h2 class="text-lg font-semibold text-[#123f57]">Enviar mensaje por SIGAF</h2>
				<p class="mt-1 text-sm text-[#526777]">Tu mensaje y correo de usuario llegarán al centro de notificaciones de las cuentas administradoras.</p>
				<form action="{{ route('help.messages.store') }}" method="POST" class="mt-4 flex flex-col gap-3">
					@csrf
					<label for="message" class="text-sm font-semibold text-[#34495b]">Mensaje</label>
					<textarea id="message" name="message" rows="6" maxlength="5000" required class="w-full rounded-xl border border-[#ccd8de] px-3 py-2 text-sm text-[#17252b] outline-none focus:border-[#07577f] focus:ring-2 focus:ring-[#07577f]/20">{{ old('message') }}</textarea>
					<button type="submit" class="self-start rounded-lg bg-[#07577f] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#064665] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f]">
						Enviar mensaje
					</button>
				</form>
			</article>

			<article class="flex flex-col gap-4 rounded-2xl border border-[#d7e1e8] bg-white p-5 shadow-sm">
				<div>
					<h2 class="text-lg font-semibold text-[#123f57]">Contactar por correo</h2>
					@if ($administrators->isEmpty())
						<p class="mt-1 text-sm text-[#526777]">No hay una cuenta administradora disponible.</p>
					@else
						<ul class="mt-2 flex flex-col gap-2">
							@foreach ($administrators as $administrator)
								<li>
									<a href="mailto:{{ $administrator->email }}?subject={{ rawurlencode('Ayuda de SIGAF') }}" class="break-all text-sm font-medium text-[#07529a] underline underline-offset-2">
										{{ $administrator->name }} — {{ $administrator->email }}
									</a>
								</li>
							@endforeach
						</ul>
					@endif
				</div>

				<div class="border-t border-[#e2e8ed] pt-4">
					<h2 class="text-lg font-semibold text-[#123f57]">Contactar por teléfono</h2>
					@if ($supportPhoneDigits !== '')
						<p class="mt-2 text-sm text-[#526777]">{{ $supportPhone }}</p>
						<div class="mt-3 flex flex-wrap gap-3">
							<a href="tel:+{{ $supportPhoneDigits }}" class="rounded-lg border border-[#07577f] px-4 py-2 text-sm font-semibold text-[#07577f] transition hover:bg-[#edf7fb]">Llamar</a>
							<a href="https://wa.me/{{ $supportPhoneDigits }}?text={{ rawurlencode('Hola, necesito ayuda con SIGAF.') }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-[#07577f] px-4 py-2 text-sm font-semibold text-[#07577f] transition hover:bg-[#edf7fb]">WhatsApp</a>
						</div>
					@else
						<p class="mt-1 text-sm text-[#526777]">El teléfono de soporte no está configurado.</p>
					@endif
				</div>
			</article>
		</section>
	</main>
</body>
</html>
