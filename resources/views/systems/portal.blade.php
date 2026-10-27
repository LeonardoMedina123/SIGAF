<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Órdenes de reembolso</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-[#161616] antialiased">
	@include('partials.app-header')

	<main class="px-3 pb-8 pt-3 md:px-6">
		<div class="mx-auto max-w-[1440px]">
			<div class="mb-3 flex justify-end">
				<form action="{{ route('logout') }}" method="POST">
					@csrf
					<button type="submit" class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-sm font-semibold text-[#444] transition hover:bg-[#f1f1f1] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#07577f]">
						Salir
						<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
						</svg>
					</button>
				</form>
			</div>

			<section aria-label="Órdenes de reembolso" class="overflow-hidden rounded-[28px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
				<div class="max-h-[calc(100vh-190px)] overflow-auto">
					<table class="w-full min-w-[760px] border-separate border-spacing-0 text-left">
						<caption class="sr-only">Órdenes de reembolso, carga de documentos, estado y fecha de modificación. Datos de ejemplo.</caption>
						<thead class="sticky top-0 z-10 bg-[#07577f] text-white">
							<tr>
								<th scope="col" class="rounded-tl-[24px] px-5 py-4 text-sm font-semibold md:px-8">Orden de reembolso</th>
								<th scope="col" class="px-5 py-4 text-sm font-semibold md:px-8">Cargar documento</th>
								<th scope="col" class="px-5 py-4 text-sm font-semibold md:px-8">Semáforo de estado</th>
								<th scope="col" class="rounded-tr-[24px] px-5 py-4 text-sm font-semibold md:px-8">Fecha de modificación</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($reimbursements as $reimbursement)
								<tr class="text-sm text-[#202020] odd:bg-white even:bg-[#fbfbfb]">
									<td class="border-t border-[#b7b7b7] px-5 py-3 md:px-8">
										<span class="whitespace-nowrap text-[#07529a] underline decoration-1 underline-offset-2">{{ $reimbursement['folio'] }}</span>
									</td>
									<td class="border-t border-[#b7b7b7] px-5 py-2 md:px-8">
										@if ($reimbursement['documentsSubmitted'])
											<span class="inline-flex min-h-9 items-center justify-center rounded-full bg-[#b5b5b5] px-4 text-center text-xs text-[#222]">Ya se adjuntaron los archivos solicitados</span>
										@else
											<label class="inline-flex min-h-9 cursor-pointer items-center justify-center gap-2 rounded-full bg-[#00e885] px-4 text-xs font-semibold text-[#111] transition hover:bg-[#00d77b] focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-[#07577f]">
												<span aria-hidden="true" class="text-xl font-light leading-none">+</span>
												<span>Adjunta documento de reembolso</span>
												<input type="file" class="sr-only" aria-label="Adjuntar documento de reembolso">
											</label>
										@endif
									</td>
									<td class="border-t border-[#b7b7b7] px-5 py-2 text-center md:px-8">
										<span @class([
											'inline-flex min-h-9 min-w-32 items-center justify-center rounded-full px-4 text-xs font-medium text-black',
											'bg-[#bd1e2d] text-white' => $reimbursement['status'] === 'Enviado',
											'bg-[#ffea00]' => $reimbursement['status'] === 'En proceso',
											'bg-[#00e885]' => $reimbursement['status'] === 'Completado',
										])>{{ $reimbursement['status'] }}</span>
									</td>
									<td class="border-t border-[#b7b7b7] px-5 py-3 text-center text-sm md:px-8">{{ $reimbursement['modifiedAt'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</section>

			<p class="mt-3 px-1 text-xs text-[#64748b]">Los registros son de ejemplo; la carga todavía no envía documentos al sistema.</p>
		</div>
	</main>
</body>
</html>
