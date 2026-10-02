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

	<button id="create-order-button" type="button" class="fixed bottom-5 right-5 z-20 flex h-20 items-center gap-2 rounded-full bg-[#08bf62] px-7 text-[20px] font-semibold text-[#062b19] shadow-[0_3px_9px_rgba(0,0,0,0.18)] transition hover:bg-[#06ad58] md:bottom-9 md:right-10" aria-label="Crear nueva orden">
		<span aria-hidden="true" class="text-[30px] font-light leading-none">+</span>
		<span>Crear nueva orden</span>
	</button>

	<div id="order-dialog" class="fixed inset-0 z-40 hidden overflow-y-auto bg-black/45 px-2 py-3 md:px-5 md:py-6" role="dialog" aria-modal="true" aria-labelledby="order-title">
		<div class="mx-auto w-full max-w-[980px] overflow-hidden rounded-[18px] bg-white shadow-[0_12px_40px_rgba(0,0,0,0.25)]">
			<header class="flex items-start justify-between gap-4 border-b border-[#d5e3e9] px-5 py-4 md:px-8">
				<div>
					<p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#087aa7]">Nueva orden de compra</p>
					<h1 id="order-title" class="mt-1 text-xl font-semibold text-[#123f57] md:text-2xl">Captura de requisición</h1>
				</div>
				<button type="button" data-order-close aria-label="Cerrar captura" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#edf1f3] text-2xl leading-none text-[#46545b] transition hover:bg-[#dce5e9]">&times;</button>
			</header>

			<div class="px-4 pt-5 md:px-8 md:pt-6">
				<ol id="order-stepper" class="relative grid grid-cols-6 gap-1" aria-label="Progreso de la orden">
					<div class="absolute left-[8%] right-[8%] top-4 h-1 rounded-full bg-[#d8dfe2]" aria-hidden="true"><div id="order-progress" class="h-full w-0 rounded-full bg-[#00c978] transition-[width] duration-300"></div></div>
					<li data-step-indicator class="relative flex min-w-0 flex-col items-center gap-1.5 text-center" aria-current="step"><span data-step-circle class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#aab1b5] text-sm font-semibold text-white ring-2 ring-[#aab1b5]">1</span><span class="text-[10px] leading-tight text-[#3f4a50] md:text-xs">Requisición</span></li>
					<li data-step-indicator class="relative flex min-w-0 flex-col items-center gap-1.5 text-center"><span data-step-circle class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#e2e5e7] bg-[#e2e5e7] text-sm font-semibold text-[#5e666a]">2</span><span class="text-[10px] leading-tight text-[#697277] md:text-xs">Solicitud</span></li>
					<li data-step-indicator class="relative flex min-w-0 flex-col items-center gap-1.5 text-center"><span data-step-circle class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#e2e5e7] bg-[#e2e5e7] text-sm font-semibold text-[#5e666a]">3</span><span class="text-[10px] leading-tight text-[#697277] md:text-xs">Cotización</span></li>
					<li data-step-indicator class="relative flex min-w-0 flex-col items-center gap-1.5 text-center"><span data-step-circle class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#e2e5e7] bg-[#e2e5e7] text-sm font-semibold text-[#5e666a]">4</span><span class="text-[10px] leading-tight text-[#697277] md:text-xs">Entrega</span></li>
					<li data-step-indicator class="relative flex min-w-0 flex-col items-center gap-1.5 text-center"><span data-step-circle class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#e2e5e7] bg-[#e2e5e7] text-sm font-semibold text-[#5e666a]">5</span><span class="text-[10px] leading-tight text-[#697277] md:text-xs">Validación</span></li>
					<li data-step-indicator class="relative flex min-w-0 flex-col items-center gap-1.5 text-center"><span data-step-circle class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#e2e5e7] bg-[#e2e5e7] text-sm font-semibold text-[#5e666a]">6</span><span class="text-[10px] leading-tight text-[#697277] md:text-xs">Confirmación</span></li>
				</ol>
			</div>

			<form id="order-wizard-form" class="px-4 pb-5 pt-4 md:px-8 md:pb-7" novalidate>
				<fieldset data-order-step class="grid gap-5 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-[1.15fr_0.85fr] md:gap-8 md:p-6">
					<legend class="sr-only">Paso 1: Requisición</legend>
					<div class="space-y-3">
						<h2 class="text-xl font-semibold text-[#07577f] md:text-2xl">Folio de requisición</h2>
						<p class="text-sm font-medium text-[#163943]">REQ/IP/DME/2026/0115</p>
						<div class="grid gap-3 pt-2 sm:grid-cols-2">
							<label class="grid gap-1 text-sm sm:col-span-2">Clave presupuestal <input name="budget_key" type="text" required autocomplete="off" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Partida <input name="item_number" type="text" required autocomplete="off" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Cantidad <input name="quantity" type="number" required min="1" step="1" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Unidad <select name="unit" required class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20"><option value="">Selecciona</option><option>Pieza</option><option>Servicio</option><option>Paquete</option><option>Lote</option><option>Otro</option></select></label>
							<label class="grid gap-1 text-sm">Costo unitario <input name="unit_cost" type="number" required min="0.01" step="0.01" inputmode="decimal" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm sm:col-span-2">Descripción <textarea name="description" required rows="2" class="w-full resize-y rounded-2xl border border-transparent bg-white px-4 py-2 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20"></textarea></label>
						</div>
					</div>
					<div class="space-y-2">
						<label for="requisition-file" class="block text-sm font-medium">Adjuntar requisición <span class="text-[#8a2f2f]">*</span></label>
						<input id="requisition-file" name="requisition_file" type="file" required accept="application/pdf,image/jpeg,image/png,image/webp" class="block w-full cursor-pointer text-xs text-[#164d65] file:mr-2 file:rounded-full file:border-0 file:bg-[#aab1b5] file:px-4 file:py-2 file:text-sm file:text-[#171717] hover:file:bg-[#9ba4a8]" />
						<p class="text-xs text-[#315a6b]">PDF, JPG, PNG o WebP. Vista previa solo en este navegador.</p>
						<div id="file-preview-box" class="hidden min-h-[220px] overflow-hidden rounded border border-[#68777d] bg-[#e7eef1]">
							<div id="file-preview-name" class="truncate bg-[#56646b] px-3 py-1.5 text-xs font-semibold text-white"></div>
							<img id="image-preview" class="hidden max-h-[340px] w-full object-contain" alt="Vista previa del archivo seleccionado" />
							<iframe id="pdf-preview" class="hidden h-[340px] w-full bg-white" title="Vista previa del PDF"></iframe>
						</div>
					</div>
				</fieldset>

				<fieldset data-order-step hidden class="grid gap-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-2 md:p-6">
					<legend class="sr-only">Paso 2: Solicitud</legend>
					<div class="md:col-span-2"><h2 class="text-xl font-semibold md:text-2xl">Datos de la solicitud</h2><p class="mt-1 text-sm">Indica el motivo y la fecha en que se requiere el bien o servicio.</p></div>
					<label class="grid gap-1 text-sm">Área solicitante <input name="department" type="text" value="{{ auth()->user()->department }}" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">Fecha requerida <input name="needed_by" type="date" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm md:col-span-2">Justificación <textarea name="justification" required rows="4" class="rounded-2xl border-0 bg-white px-4 py-3 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30"></textarea></label>
				</fieldset>

				<fieldset data-order-step hidden class="grid gap-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-2 md:p-6">
					<legend class="sr-only">Paso 3: Cotización</legend>
					<div class="md:col-span-2"><h2 class="text-xl font-semibold md:text-2xl">Cotización</h2><p class="mt-1 text-sm">Registra los datos de la cotización de referencia.</p></div>
					<label class="grid gap-1 text-sm">Proveedor cotizante <input name="quote_vendor" type="text" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">RFC del proveedor <input name="quote_rfc" type="text" required minlength="12" maxlength="13" class="h-10 rounded-full border-0 bg-white px-4 text-sm uppercase text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">Correo de contacto <input name="quote_email" type="email" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">Importe cotizado <input name="quote_amount" type="number" required min="0.01" step="0.01" class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
				</fieldset>

				<fieldset data-order-step hidden class="grid gap-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-2 md:p-6">
					<legend class="sr-only">Paso 4: Entrega</legend>
					<div class="md:col-span-2"><h2 class="text-xl font-semibold md:text-2xl">Datos de entrega</h2><p class="mt-1 text-sm">¿Dónde y quién recibirá los bienes o servicios?</p></div>
					<label class="grid gap-1 text-sm md:col-span-2">Domicilio o lugar de entrega <input name="delivery_location" type="text" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">Persona que recibe <input name="receiver" type="text" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">Teléfono de contacto <input name="receiver_phone" type="tel" required pattern="[0-9+() -]{10,20}" class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
				</fieldset>

				<fieldset data-order-step hidden class="space-y-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:p-6">
					<legend class="sr-only">Paso 5: Validación</legend>
					<h2 class="text-xl font-semibold md:text-2xl">Validación presupuestal</h2>
					<p class="text-sm">Verifica que la clave y la partida correspondan al gasto solicitado.</p>
					<label class="flex items-start gap-3 rounded-xl bg-white p-4 text-sm text-[#173f50]"><input name="budget_confirmed" type="checkbox" required class="mt-0.5 h-4 w-4 accent-[#00b96b]" /><span>Confirmo que existe suficiencia presupuestal para esta requisición.</span></label>
				</fieldset>

				<fieldset data-order-step hidden class="space-y-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:p-6">
					<legend class="sr-only">Paso 6: Confirmación</legend>
					<h2 class="text-xl font-semibold md:text-2xl">Revisión final</h2>
					<div id="order-summary" class="grid gap-x-6 gap-y-3 rounded-xl bg-white p-4 text-sm sm:grid-cols-2"></div>
					<label class="flex items-start gap-3 rounded-xl border border-[#8ebdd1] p-4 text-sm text-[#173f50]"><input name="review_confirmed" type="checkbox" required class="mt-0.5 h-4 w-4 accent-[#00b96b]" /><span>Confirmo que los datos capturados son correctos.</span></label>
					<p class="text-xs text-[#315a6b]">La captura se valida en esta pantalla; todavía no se guarda ni se envía al servidor.</p>
				</fieldset>

				<div id="order-complete" class="hidden rounded-[14px] bg-[#e8f8f0] p-6 text-center text-[#145538]" role="status">
					<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#00c978] text-2xl font-bold text-white" aria-hidden="true">&#10003;</div>
					<h2 class="mt-3 text-xl font-semibold">Captura completada</h2>
					<p class="mt-1 text-sm">Los datos quedaron validados en esta sesión. No se han guardado en el sistema.</p>
				</div>

				<div id="order-actions" class="mt-5 flex items-center justify-between gap-3">
					<button id="order-previous" type="button" class="invisible rounded-full border border-[#82939a] px-5 py-2 text-sm font-semibold text-[#34464e] transition hover:bg-white">Anterior</button>
					<button id="order-next" type="button" class="rounded-full bg-[#00bf70] px-6 py-2 text-sm font-semibold text-[#073620] transition hover:bg-[#00ab63]">Siguiente</button>
				</div>
			</form>
		</div>
	</div>

	<script>
		const createOrderButton = document.querySelector('#create-order-button');
		const orderDialog = document.querySelector('#order-dialog');
		const orderForm = document.querySelector('#order-wizard-form');
		const orderSteps = [...document.querySelectorAll('[data-order-step]')];
		const stepIndicators = [...document.querySelectorAll('[data-step-indicator]')];
		const orderProgress = document.querySelector('#order-progress');
		const previousButton = document.querySelector('#order-previous');
		const nextButton = document.querySelector('#order-next');
		const orderActions = document.querySelector('#order-actions');
		const orderComplete = document.querySelector('#order-complete');
		const requisitionFile = document.querySelector('#requisition-file');
		const previewBox = document.querySelector('#file-preview-box');
		const previewName = document.querySelector('#file-preview-name');
		const imagePreview = document.querySelector('#image-preview');
		const pdfPreview = document.querySelector('#pdf-preview');
		let activeOrderStep = 0;
		let previewUrl = null;

		function renderOrderStep() {
			orderSteps.forEach((step, index) => {
				step.hidden = index !== activeOrderStep;
				step.style.display = index === activeOrderStep ? '' : 'none';
				stepIndicators[index].setAttribute('aria-current', index === activeOrderStep ? 'step' : 'false');
				const circle = stepIndicators[index].querySelector('[data-step-circle]');
				const complete = index < activeOrderStep;
				circle.className = complete
					? 'flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#00c978] bg-[#00c978] text-sm font-semibold text-white'
					: index === activeOrderStep
						? 'flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-[#aab1b5] text-sm font-semibold text-white ring-2 ring-[#aab1b5]'
						: 'flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#e2e5e7] bg-[#e2e5e7] text-sm font-semibold text-[#5e666a]';
				circle.textContent = complete ? '\u2713' : String(index + 1);
				stepIndicators[index].lastElementChild.className = `text-[10px] leading-tight md:text-xs ${index <= activeOrderStep ? 'text-[#3f4a50]' : 'text-[#697277]'}`;
			});
			orderProgress.style.width = `${(activeOrderStep / (orderSteps.length - 1)) * 100}%`;
			previousButton.classList.toggle('invisible', activeOrderStep === 0);
			nextButton.textContent = activeOrderStep === orderSteps.length - 1 ? 'Finalizar captura' : 'Siguiente';
			if (activeOrderStep === orderSteps.length - 1) {
				updateOrderSummary();
			}
		}

		function updateOrderSummary() {
			const summary = document.querySelector('#order-summary');
			const fields = [
				['Descripción', 'description'],
				['Cantidad', 'quantity'],
				['Unidad', 'unit'],
				['Costo unitario', 'unit_cost'],
				['Área solicitante', 'department'],
				['Proveedor cotizante', 'quote_vendor'],
				['Importe cotizado', 'quote_amount'],
				['Lugar de entrega', 'delivery_location'],
			];
			summary.replaceChildren(...fields.map(([label, name]) => {
				const item = document.createElement('p');
				const field = orderForm.elements.namedItem(name);
				item.textContent = `${label}: ${field.value || '—'}`;
				item.className = 'break-words text-[#173f50]';
				return item;
			}));
		}

		function validateCurrentStep() {
			const invalidField = orderSteps[activeOrderStep].querySelector(':invalid');
			if (invalidField) {
				invalidField.reportValidity();
				return false;
			}
			return true;
		}

		function releasePreview() {
			if (previewUrl) {
				URL.revokeObjectURL(previewUrl);
				previewUrl = null;
			}
		}

		function closeOrderDialog() {
			orderDialog.classList.add('hidden');
			orderForm.reset();
			orderForm.classList.remove('hidden');
			orderComplete.classList.add('hidden');
			orderActions.classList.remove('hidden');
			activeOrderStep = 0;
			releasePreview();
			previewBox.classList.add('hidden');
			imagePreview.classList.add('hidden');
			pdfPreview.classList.add('hidden');
			renderOrderStep();
			createOrderButton.focus();
		}

		createOrderButton.addEventListener('click', () => {
			orderDialog.classList.remove('hidden');
			activeOrderStep = 0;
			renderOrderStep();
			orderSteps[0].querySelector('input').focus();
		});
		orderDialog.querySelectorAll('[data-order-close]').forEach((button) => button.addEventListener('click', closeOrderDialog));
		orderDialog.addEventListener('click', (event) => {
			if (event.target === orderDialog) {
				closeOrderDialog();
			}
		});
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !orderDialog.classList.contains('hidden')) {
				closeOrderDialog();
			}
		});
		previousButton.addEventListener('click', () => {
			if (activeOrderStep > 0) {
				activeOrderStep -= 1;
				renderOrderStep();
			}
		});
		nextButton.addEventListener('click', () => {
			if (!validateCurrentStep()) {
				return;
			}
			if (activeOrderStep === orderSteps.length - 1) {
				orderForm.classList.add('hidden');
				orderComplete.classList.remove('hidden');
				orderActions.classList.add('hidden');
				return;
			}
			activeOrderStep += 1;
			renderOrderStep();
		});
		requisitionFile.addEventListener('change', () => {
			releasePreview();
			imagePreview.classList.add('hidden');
			pdfPreview.classList.add('hidden');
			const file = requisitionFile.files[0];
			if (!file) {
				previewBox.classList.add('hidden');
				return;
			}
			if (!['application/pdf', 'image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
				requisitionFile.setCustomValidity('Selecciona un archivo PDF, JPG, PNG o WebP.');
				requisitionFile.reportValidity();
				requisitionFile.value = '';
				requisitionFile.setCustomValidity('');
				previewBox.classList.add('hidden');
				return;
			}
			previewUrl = URL.createObjectURL(file);
			previewName.textContent = file.name;
			previewBox.classList.remove('hidden');
			if (file.type === 'application/pdf') {
				pdfPreview.src = previewUrl;
				pdfPreview.classList.remove('hidden');
			} else {
				imagePreview.src = previewUrl;
				imagePreview.classList.remove('hidden');
			}
		});

		renderOrderStep();
	</script>
</body>
</html>
