<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Requisiciones</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
	<style>
		#filter-menu {
			width: min(400px, calc(100vw - 24px));
			max-height: calc(100vh - 24px);
			padding: 20px;
			border: 1px solid #d7e1ea;
			border-radius: 18px;
			box-shadow: 0 18px 48px rgba(10, 35, 55, 0.24);
		}

		#filter-menu:not(.hidden) {
			animation: filter-menu-enter 160ms ease-out;
		}

		#filter-menu > div:first-child {
			height: 44px;
			margin-bottom: 16px;
			padding: 0 12px;
			border-radius: 12px;
			background: #eaf4f9;
		}

		#filter-menu-title {
			font-size: 16px;
			font-weight: 700;
			color: #07577f;
		}

		#filter-menu-content {
			gap: 12px;
		}

		#filter-menu-content label {
			grid-template-columns: 104px minmax(0, 1fr);
			gap: 10px;
			font-size: 14px;
			font-weight: 600;
			color: #34495b;
		}

		#filter-menu-content input,
		#filter-menu-content select {
			min-width: 0;
			height: 42px;
			border: 1px solid #d5dee7;
			border-radius: 10px;
			background: #f6f8fa;
			padding: 0 12px;
			font-size: 14px;
			color: #20384c;
			transition: border-color 140ms ease, box-shadow 140ms ease;
		}

		#filter-menu-content input:focus,
		#filter-menu-content select:focus {
			border-color: #1684b5;
			outline: none;
			box-shadow: 0 0 0 3px rgba(22, 132, 181, 0.15);
		}

		#filter-menu [data-filter-close] {
			height: 30px;
			width: 30px;
			background: #cbd5df;
			color: #34495b;
		}

		#filter-menu [data-filter-apply] {
			margin-top: 18px;
			border-radius: 10px;
			padding: 10px 18px;
			font-size: 14px;
			font-weight: 700;
		}

		@keyframes filter-menu-enter {
			from { opacity: 0; transform: translateY(-5px) scale(0.98); }
			to { opacity: 1; transform: translateY(0) scale(1); }
		}

		@media (prefers-reduced-motion: reduce) {
			#filter-menu:not(.hidden) { animation: none; }
		}
	</style>
</head>
<body class="min-h-screen overflow-hidden bg-[#f4f4f4] text-[#161616] antialiased">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'home'])

	<main class="px-3 pb-4 md:px-4">
		<div class="mb-3 flex items-center justify-between gap-3">
			@if (session('success'))
				<p role="status" class="rounded-lg bg-[#e8f8f0] px-4 py-2 text-sm font-medium text-[#145538]">{{ session('success') }}</p>
			@endif
		</div>
		@if ($errors->any())
			<div role="alert" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
				<p class="font-semibold">No se pudo guardar la información. Revisa los datos e inténtalo de nuevo.</p>
				<ul class="mt-1 list-inside list-disc">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif
		<section aria-label="Requisiciones" class="overflow-auto rounded-[20px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
			<table class="w-full min-w-[1500px] border-separate border-spacing-0 text-left">
				<thead class="bg-[#07577f] text-white">
					<tr>
						<th scope="col" class="rounded-tl-[19px] p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="requisicion" data-filter-title="Requisición" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Requisición</button>
						</th>
						<th scope="col" class="p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="orden-compra" data-filter-title="Orden de compra" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Orden de compra</button>
						</th>
						<th scope="col" class="p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="factura" data-filter-title="Factura" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Factura</button>
						</th>
						<th scope="col" class="p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="validacion" data-filter-title="Validación" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Validación</button>
						</th>
						<th scope="col" class="p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="pago" data-filter-title="Pago" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Pago</button>
						</th>
						<th scope="col" class="p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="validacion-financiera" data-filter-title="Validación Recursos Financieros" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Validación Recursos Financieros</button>
						</th>
						<th scope="col" class="rounded-tr-[19px] p-0 text-sm font-semibold">
							<button type="button" data-filter-trigger="complemento" data-filter-title="Complemento proveedor" aria-haspopup="dialog" aria-expanded="false" class="flex w-full items-center px-4 py-3 text-left transition-colors hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-white/80">Complemento proveedor</button>
						</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($requisitions as $requisition)
						<tr class="text-sm text-[#263b46] odd:bg-white even:bg-[#f5f9fb]">
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $requisition->folio }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $requisition->purchaseOrder?->folio ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">—</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">—</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">—</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $requisition->purchaseOrder?->rf ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">—</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="border-t border-[#d3dfe5] px-4 py-14 text-center text-sm text-[#60737d]">
								Aún no hay requisiciones registradas.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</section>
	</main>

	<div id="filter-menu" role="dialog" aria-modal="false" aria-labelledby="filter-menu-title" class="fixed z-50 hidden w-[290px] max-w-[calc(100vw-24px)] overflow-y-auto rounded-[18px] bg-white p-3 text-[#171717] shadow-[0_5px_18px_rgba(0,0,0,0.24)]">
		<div class="mb-2 flex h-5 items-center justify-between rounded-full bg-[#e7f5fc] px-2">
			<h2 id="filter-menu-title" class="text-xs font-semibold"></h2>
			<button type="button" data-filter-close aria-label="Cerrar filtros" class="flex h-5 w-5 items-center justify-center rounded-full bg-[#b9b9b9] text-white transition hover:bg-[#8997a3]">
				<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
					<path d="m6 6 12 12M18 6 6 18" />
				</svg>
			</button>
		</div>
		<div id="filter-menu-content" class="space-y-2"></div>
		<button type="button" data-filter-apply class="mt-3 rounded-full bg-[#08bf62] px-4 py-1.5 text-[10px] font-semibold text-white transition hover:bg-[#06ad58]">Aplicar filtros</button>
	</div>

	<template id="filter-requisicion">
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Folio:</span><input type="text" placeholder="Escribe aquí" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Partida:</span><input type="text" class="h-7 min-w-0 rounded-full border-0 bg-[#b7b7b7] px-3 text-[10px] outline-none focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
		<label class="grid grid-cols-[58px_1fr] items-center gap-2 text-xs"><span>Piezas:</span><input type="number" placeholder="0" class="h-7 min-w-0 rounded-full border-0 bg-[#e5e5e5] px-3 text-[10px] outline-none placeholder:italic focus:ring-2 focus:ring-[#0c8bca]/30" /></label>
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
				<button type="button" data-order-close aria-label="Cerrar captura" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#edf1f3] text-[#46545b] transition hover:bg-[#dce5e9]">
					<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
						<path d="m6 6 12 12M18 6 6 18" />
					</svg>
				</button>
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

			<form id="order-wizard-form" action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data" class="px-4 pb-5 pt-4 md:px-8 md:pb-7" novalidate>
				@csrf
				<fieldset data-order-step class="grid gap-5 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-[1.15fr_0.85fr] md:gap-8 md:p-6">
					<legend class="sr-only">Paso 1: Requisición</legend>
					<div class="space-y-3">
						<h2 class="text-xl font-semibold text-[#07577f] md:text-2xl">Folio de requisición</h2>
						<div class="grid gap-3 pt-2 sm:grid-cols-2">
							<label class="grid gap-1 text-sm">Folio <input name="requisition_folio" type="text" required maxlength="100" autocomplete="off" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Fecha de creación <input name="requisition_created_on" type="date" value="{{ now()->format('Y-m-d') }}" required class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Fecha de solicitud <input name="requisition_requested_on" type="date" value="{{ now()->format('Y-m-d') }}" required class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Importe de requisición <input name="requisition_amount" type="number" required min="0" step="0.01" inputmode="decimal" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm sm:col-span-2">Clave presupuestal <input name="budget_key" type="text" required autocomplete="off" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Partida <input name="item_number" type="text" required autocomplete="off" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">SP <input name="requisition_sp" type="text" maxlength="255" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">PPP <input name="requisition_ppp" type="text" maxlength="255" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Sup <input name="requisition_sup" type="text" maxlength="255" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Dir <input name="requisition_dir" type="text" maxlength="255" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Proceso <input name="requisition_process" type="text" maxlength="255" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">RF <input name="requisition_rf" type="text" maxlength="255" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Cantidad <input name="quantity" type="number" required min="1" step="1" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm">Unidad <select name="unit" required class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20"><option value="">Selecciona</option><option>Pieza</option><option>Servicio</option><option>Paquete</option><option>Lote</option><option>Otro</option></select></label>
							<label class="grid gap-1 text-sm">Costo unitario <input name="unit_cost" type="number" required min="0.01" step="0.01" inputmode="decimal" class="h-10 w-full rounded-full border border-transparent bg-white px-4 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20" /></label>
							<label class="grid gap-1 text-sm sm:col-span-2">Descripción <textarea name="description" required rows="2" class="w-full resize-y rounded-2xl border border-transparent bg-white px-4 py-2 text-sm text-[#17252b] outline-none focus:border-[#087aa7] focus:ring-2 focus:ring-[#087aa7]/20"></textarea></label>
						</div>
					</div>
					<div class="space-y-2">
						<label for="requisition-pdf" class="block text-sm font-medium">PDF de requisición <span class="text-[#8a2f2f]">*</span></label>
						<input id="requisition-pdf" name="requisition_pdf" type="file" required accept="application/pdf,.pdf" class="block w-full cursor-pointer text-xs text-[#164d65] file:mr-2 file:rounded-full file:border-0 file:bg-[#aab1b5] file:px-4 file:py-2 file:text-sm file:text-[#171717] hover:file:bg-[#9ba4a8]" />
						<label for="requisition-xml" class="block pt-2 text-sm font-medium">XML de requisición <span class="text-[#8a2f2f]">*</span></label>
						<input id="requisition-xml" name="requisition_xml" type="file" required accept="application/xml,text/xml,.xml" class="block w-full cursor-pointer text-xs text-[#164d65] file:mr-2 file:rounded-full file:border-0 file:bg-[#aab1b5] file:px-4 file:py-2 file:text-sm file:text-[#171717] hover:file:bg-[#9ba4a8]" />
						<p class="text-xs text-[#315a6b]">Los documentos se guardan como archivos privados; la base de datos conserva sus rutas. Máximo 10 MB por archivo.</p>
						<div id="file-preview-box" class="hidden min-h-[220px] overflow-hidden rounded border border-[#68777d] bg-[#e7eef1]">
							<div id="file-preview-name" class="truncate bg-[#56646b] px-3 py-1.5 text-xs font-semibold text-white"></div>
							<iframe id="pdf-preview" class="hidden h-[340px] w-full bg-white" title="Vista previa del PDF"></iframe>
						</div>
					</div>
				</fieldset>

				<fieldset data-order-step hidden class="grid gap-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-2 md:p-6">
					<legend class="sr-only">Paso 2: Solicitud</legend>
					<div class="md:col-span-2"><h2 class="text-xl font-semibold md:text-2xl">Datos de la solicitud</h2><p class="mt-1 text-sm">Indica el motivo y la fecha en que se requiere el bien o servicio.</p></div>
					<label class="grid gap-1 text-sm">Área solicitante <input name="department" type="text" value="{{ auth()->user()->department?->name }}" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm">Fecha requerida <input name="needed_by" type="date" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30" /></label>
					<label class="grid gap-1 text-sm md:col-span-2">Justificación <textarea name="justification" required rows="4" class="rounded-2xl border-0 bg-white px-4 py-3 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30"></textarea></label>
				</fieldset>

				<fieldset data-order-step hidden class="grid gap-4 rounded-[14px] bg-[#b7dcef] p-4 text-[#07577f] md:grid-cols-2 md:p-6">
					<legend class="sr-only">Paso 3: Cotización</legend>
					<div class="md:col-span-2"><h2 class="text-xl font-semibold md:text-2xl">Cotización</h2><p class="mt-1 text-sm">Registra los datos de la cotización de referencia.</p></div>
					<label class="grid gap-1 text-sm">Proveedor cotizante
						<select name="supplier_id" required class="h-10 rounded-full border-0 bg-white px-4 text-sm text-[#17252b] outline-none focus:ring-2 focus:ring-[#087aa7]/30">
							<option value="">Selecciona un proveedor</option>
							@foreach ($suppliers as $supplier)
								<option value="{{ $supplier->id }}" data-iva="{{ $supplier->iva_percentage }}">{{ $supplier->name }} ({{ $supplier->rfc }})</option>
							@endforeach
						</select>
					</label>
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
					<div class="grid gap-3 rounded-xl bg-white p-4 sm:grid-cols-2">
						<h3 class="text-lg font-semibold sm:col-span-2">Orden de compra</h3>
						<label class="grid gap-1 text-sm">Folio <input name="purchase_order_folio" type="text" required maxlength="100" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
						<label class="grid gap-1 text-sm">Fecha <input name="purchase_order_date" type="date" required value="{{ now()->format('Y-m-d') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
						<label class="grid gap-1 text-sm">Partida <input name="purchase_order_partida" type="text" required maxlength="100" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
						<label class="grid gap-1 text-sm">I.V.A. (%) <input name="purchase_order_iva" type="number" required min="0" max="100" step="0.01" value="16" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
						<label class="grid gap-1 text-sm">RMS <input name="purchase_order_rms" type="text" maxlength="255" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
						<label class="grid gap-1 text-sm">RF <input name="purchase_order_rf" type="text" maxlength="255" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
						<p class="text-xs text-[#315a6b] sm:col-span-2">El importe de la orden se toma del importe cotizado en la tarjeta Cotización.</p>
						<label class="grid gap-1 text-sm">PDF de orden de compra <input name="purchase_order_pdf" type="file" required accept="application/pdf,.pdf" class="block w-full cursor-pointer text-xs text-[#164d65] file:mr-2 file:rounded-full file:border-0 file:bg-[#aab1b5] file:px-4 file:py-2 file:text-sm file:text-[#171717]" /></label>
						<label class="grid gap-1 text-sm">XML de orden de compra <input name="purchase_order_xml" type="file" required accept="application/xml,text/xml,.xml" class="block w-full cursor-pointer text-xs text-[#164d65] file:mr-2 file:rounded-full file:border-0 file:bg-[#aab1b5] file:px-4 file:py-2 file:text-sm text-[#171717]" /></label>
					</div>
					<div id="order-summary" class="grid gap-x-6 gap-y-3 rounded-xl bg-white p-4 text-sm sm:grid-cols-2"></div>
					<label class="flex items-start gap-3 rounded-xl border border-[#8ebdd1] p-4 text-sm text-[#173f50]"><input name="review_confirmed" type="checkbox" required class="mt-0.5 h-4 w-4 accent-[#00b96b]" /><span>Confirmo que los datos capturados son correctos.</span></label>
					<p class="text-xs text-[#315a6b]">Al finalizar se guardarán la requisición, la orden de compra y las rutas de sus documentos.</p>
				</fieldset>

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
		const supplierSelect = orderForm.elements.namedItem('supplier_id');
		const purchaseOrderIva = orderForm.elements.namedItem('purchase_order_iva');
		supplierSelect.addEventListener('change', () => {
			const selectedOption = supplierSelect.selectedOptions[0];
			if (selectedOption?.dataset.iva) {
				purchaseOrderIva.value = selectedOption.dataset.iva;
			}
		});
		const orderSteps = [...document.querySelectorAll('[data-order-step]')];
		const stepIndicators = [...document.querySelectorAll('[data-step-indicator]')];
		const orderProgress = document.querySelector('#order-progress');
		const previousButton = document.querySelector('#order-previous');
		const nextButton = document.querySelector('#order-next');
		const orderActions = document.querySelector('#order-actions');
		const requisitionPdf = document.querySelector('#requisition-pdf');
		const previewBox = document.querySelector('#file-preview-box');
		const previewName = document.querySelector('#file-preview-name');
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
				['Folio de requisición', 'requisition_folio'],
				['Importe de requisición', 'requisition_amount'],
				['Descripción', 'description'],
				['Cantidad', 'quantity'],
				['Unidad', 'unit'],
				['Costo unitario', 'unit_cost'],
				['Área solicitante', 'department'],
				['Proveedor cotizante', 'supplier_id'],
				['Importe cotizado', 'quote_amount'],
				['Lugar de entrega', 'delivery_location'],
				['Folio de orden de compra', 'purchase_order_folio'],
			];
			summary.replaceChildren(...fields.map(([label, name]) => {
				const item = document.createElement('p');
				const field = orderForm.elements.namedItem(name);
				const value = name === 'supplier_id'
					? field.selectedOptions[0]?.textContent || '—'
					: field.value || '—';
				item.textContent = `${label}: ${value}`;
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
			activeOrderStep = 0;
			releasePreview();
			previewBox.classList.add('hidden');
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
				orderForm.requestSubmit();
				return;
			}
			activeOrderStep += 1;
			renderOrderStep();
		});
		requisitionPdf.addEventListener('change', () => {
			releasePreview();
			pdfPreview.classList.add('hidden');
			const file = requisitionPdf.files[0];
			if (!file) {
				previewBox.classList.add('hidden');
				return;
			}
			if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
				requisitionPdf.setCustomValidity('Selecciona un archivo PDF.');
				requisitionPdf.reportValidity();
				requisitionPdf.value = '';
				requisitionPdf.setCustomValidity('');
				previewBox.classList.add('hidden');
				return;
			}
			previewUrl = URL.createObjectURL(file);
			previewName.textContent = file.name;
			previewBox.classList.remove('hidden');
			pdfPreview.src = previewUrl;
			pdfPreview.classList.remove('hidden');
		});

		renderOrderStep();
	</script>
</body>
</html>
