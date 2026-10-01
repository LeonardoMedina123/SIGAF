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
							<button type="button" data-filter-trigger="requisicion" data-filter-title="Requisición" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-1 text-left">Requisición <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<button type="button" data-filter-trigger="orden-compra" data-filter-title="Orden de compra" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-1 text-left">Orden de compra <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<button type="button" data-filter-trigger="factura" data-filter-title="Factura" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-1 text-left">Factura <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<button type="button" data-filter-trigger="validacion" data-filter-title="Validación" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-1 text-left">Validación <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
						</th>
						<th scope="col" class="px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<button type="button" data-filter-trigger="pago" data-filter-title="Pago" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-1 text-left">Pago <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
						</th>
						<th scope="col" class="px-3 py-2 text-center text-[15px] font-semibold leading-[1.05]">
							<button type="button" data-filter-trigger="validacion-financiera" data-filter-title="Validación Recursos Financieros" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-2 text-left">Validación Recursos Financieros <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
						</th>
						<th scope="col" class="rounded-tr-[19px] px-3 py-2 text-[15px] font-semibold leading-[1.05]">
							<button type="button" data-filter-trigger="complemento" data-filter-title="Complemento proveedor" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center justify-between gap-1 text-left">Complemento proveedor <span class="shrink-0 text-[17px] leading-none text-white/60" aria-hidden="true">⌄</span></button>
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

	<div id="filter-menu" role="dialog" aria-modal="false" aria-labelledby="filter-menu-title" class="fixed z-50 hidden w-[290px] max-w-[calc(100vw-24px)] overflow-y-auto rounded-[18px] bg-white p-3 text-[#171717] shadow-[0_5px_18px_rgba(0,0,0,0.24)]">
		<div class="mb-2 flex h-5 items-center justify-between rounded-full bg-[#e7f5fc] px-2">
			<h2 id="filter-menu-title" class="text-xs font-semibold"></h2>
			<button type="button" data-filter-close aria-label="Cerrar filtros" class="flex h-5 w-5 items-center justify-center rounded-full bg-[#b9b9b9] text-sm font-bold leading-none text-white">×</button>
		</div>
		<div id="filter-menu-content" class="space-y-2"></div>
		<button type="button" data-filter-apply class="mt-3 rounded-full bg-[#08bf62] px-4 py-1.5 text-[10px] font-semibold text-white transition hover:bg-[#06ad58]">Aplicar filtros</button>
	</div>

	<template id="filter-requisicion">
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Folio:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Partida:</span><input type="text" class="h-7 min-w-0 rounded-full border-0 bg-[#b7b7b7] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Piezas:</span><input type="number" placeholder="2548" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Monto:</span><span class="flex min-w-0 gap-1"><select aria-label="Operador de monto" class="h-7 rounded-full border-0 bg-[#e5e5e5] px-2 text-[10px] outline-none"><option>=</option><option>&gt;</option><option>&lt;</option></select><input type="number" placeholder="$" class="h-7 min-w-0 flex-1 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></span></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
	</template>
	<template id="filter-orden-compra">
		<label class="grid grid-cols-[82px_1fr] items-center gap-2 text-xs"><span>Folio:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[82px_1fr] items-center gap-2 text-xs"><span>Área solicitante:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[82px_1fr] items-center gap-2 text-xs"><span>Monto:</span><span class="flex min-w-0 gap-1"><select aria-label="Operador de monto" class="h-7 rounded-full border-0 bg-[#e5e5e5] px-2 text-[10px] outline-none"><option>=</option><option>&gt;</option><option>&lt;</option></select><input type="number" placeholder="$" class="h-7 min-w-0 flex-1 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></span></label>
		<label class="grid grid-cols-[82px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
	</template>
	<template id="filter-factura">
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Folio:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>UUID:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>RFC:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Total:</span><span class="flex min-w-0 gap-1"><select aria-label="Operador del total" class="h-7 rounded-full border-0 bg-[#e5e5e5] px-2 text-[10px] outline-none"><option>=</option><option>&gt;</option><option>&lt;</option></select><input type="number" placeholder="$" class="h-7 min-w-0 flex-1 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></span></label>
	</template>
	<template id="filter-validacion">
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Estado:</span><select class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30"><option value="">Todos</option><option>Pendiente</option><option>Aceptada</option><option>Rechazada</option></select></label>
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
	</template>
	<template id="filter-pago">
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Estado:</span><select class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30"><option value="">Todos</option><option>Pendiente</option><option>Pagado</option></select></label>
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
	</template>
	<template id="filter-validacion-financiera">
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Estado:</span><select class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30"><option value="">Todos</option><option>Pendiente</option><option>Validado</option><option>Rechazado</option></select></label>
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
	</template>
	<template id="filter-complemento">
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>RFC:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Estado:</span><select class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30"><option value="">Todos</option><option>Pendiente</option><option>Recibido</option></select></label>
		<label class="grid grid-cols-[68px_1fr] items-center gap-2 text-xs"><span>Fecha:</span><input type="date" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
	</template>

	<script>
		const filterMenu = document.querySelector('#filter-menu');
		const filterTitle = document.querySelector('#filter-menu-title');
		const filterContent = document.querySelector('#filter-menu-content');
		let activeFilterTrigger = null;

		function closeFilterMenu() {
			filterMenu.classList.add('hidden');
			activeFilterTrigger?.setAttribute('aria-expanded', 'false');
			activeFilterTrigger = null;
		}

		document.querySelectorAll('[data-filter-trigger]').forEach((trigger) => {
			trigger.addEventListener('click', () => {
				if (activeFilterTrigger === trigger) {
					closeFilterMenu();
					return;
				}

				activeFilterTrigger?.setAttribute('aria-expanded', 'false');
				activeFilterTrigger = trigger;
				activeFilterTrigger.setAttribute('aria-expanded', 'true');
				filterTitle.textContent = trigger.dataset.filterTitle;
				filterContent.replaceChildren(document.querySelector(`#filter-${trigger.dataset.filterTrigger}`).content.cloneNode(true));
				filterMenu.classList.remove('hidden');

				const bounds = trigger.getBoundingClientRect();
				const left = Math.min(Math.max(12, bounds.left), window.innerWidth - filterMenu.offsetWidth - 12);
				const top = Math.min(bounds.bottom, window.innerHeight - filterMenu.offsetHeight - 12);
				filterMenu.style.left = `${left}px`;
				filterMenu.style.top = `${Math.max(12, top)}px`;
			});
		});

		filterMenu.addEventListener('click', (event) => {
			if (event.target.closest('[data-filter-close], [data-filter-apply]')) {
				closeFilterMenu();
			}
		});

		document.addEventListener('click', (event) => {
			if (!filterMenu.contains(event.target) && !event.target.closest('[data-filter-trigger]')) {
				closeFilterMenu();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closeFilterMenu();
			}
		});

		window.addEventListener('resize', closeFilterMenu);
		 document.querySelector('section[aria-label="Requisiciones"]').addEventListener('scroll', closeFilterMenu);
	</script>

	<button type="button" class="fixed bottom-5 right-5 z-20 flex h-20 items-center gap-2 rounded-full bg-[#08bf62] px-7 text-[20px] font-semibold text-[#062b19] shadow-[0_3px_9px_rgba(0,0,0,0.18)] transition hover:bg-[#06ad58] md:bottom-9 md:right-10" aria-label="Crear nueva orden">
		<span aria-hidden="true" class="text-[30px] font-light leading-none">+</span>
		<span>Crear nueva orden</span>
	</button>
</body>
</html>
