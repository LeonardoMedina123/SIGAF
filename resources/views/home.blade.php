<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Requisiciones</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-hidden bg-[#f4f4f4] text-[#161616] antialiased">
	<header class="relative flex h-[58px] items-center justify-between overflow-hidden border-b-2 border-[#8050e8] bg-[#754b2e] px-3 text-white shadow-sm md:px-6">
		<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('fondo.jfif') }}')" aria-hidden="true"></div>
		<div class="absolute inset-0 bg-[#4b2716]/45" aria-hidden="true"></div>

		<div class="relative z-10 flex min-w-0 items-center gap-3">
			<div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full  p-1 shadow">
				<img src="{{ asset('persona.png') }}" alt="" class="h-full w-full object-contain" />
			</div>
			<div class="min-w-0 text-[13px] font-semibold leading-[1.05] drop-shadow-sm">
				<p class="max-w-[220px] truncate">{{ auth()->user()->name }}</p>
				<p class="max-w-[220px] truncate">{{ auth()->user()->department }}</p>
			</div>
		</div>

		<img src="{{ asset('tecnm.png') }}" alt="Tecnológico Nacional de México" class="relative z-10 h-9 w-auto max-w-[130px] object-contain drop-shadow-sm md:max-w-[180px]" />
	</header>

	<div class="flex h-8 items-center justify-end px-4 md:px-6">
		<form action="{{ route('logout') }}" method="POST">
			@csrf
			<button type="submit" class="flex items-center gap-1 text-xs font-semibold text-[#555] transition hover:text-[#004f7b]">
				Salir <span aria-hidden="true" class="text-base leading-none">↪</span>
			</button>
		</form>
	</div>

	<main class="px-3 pb-4 md:px-4">
		<section aria-label="Requisiciones" class="overflow-x-auto rounded-[20px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
			<table class="w-full min-w-[1080px] table-fixed border-separate border-spacing-0 text-left">
				<colgroup>
					<col class="w-[14%]" />
					<col class="w-[12%]" />
					<col class="w-[10%]" />
					<col class="w-[11%]" />
					<col class="w-[12%]" />
					<col class="w-[22%]" />
					<col class="w-[19%]" />
				</colgroup>
				<thead class="bg-[#07577f] text-white">
					<tr class="h-[42px]">
						<th scope="col" class="rounded-tl-[19px] px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-1">Requisición <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-1">Orden de compra <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-1">Factura <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-1">Validación <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-1">Pago <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
						<th scope="col" class="px-3 py-2 text-center text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-2">Validación Recursos Financieros <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
						<th scope="col" class="rounded-tr-[19px] px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<span class="flex items-center justify-between gap-1">Complemento proveedor <span class="shrink-0 text-[9px] leading-[0.75] text-white/50" aria-hidden="true">▴<br>▾</span></span>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr aria-hidden="true">
						<td colspan="7" class="h-[calc(100vh-160px)] min-h-[300px] border-t border-[#d3d3d3]"></td>
					</tr>
				</tbody>
			</table>
		</section>
	</main>

	<button type="button" class="fixed bottom-5 right-5 z-20 flex h-12 items-center gap-2 rounded-full bg-[#08bf62] px-4 text-[13px] font-semibold text-[#062b19] shadow-[0_3px_9px_rgba(0,0,0,0.18)] transition hover:bg-[#06ad58] md:bottom-7 md:right-10" aria-label="Crear nueva orden">
		<span aria-hidden="true" class="text-[25px] font-light leading-none">+</span>
		<span>Crear nueva orden</span>
	</button>
</body>
</html>
