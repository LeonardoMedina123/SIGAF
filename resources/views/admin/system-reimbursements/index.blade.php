<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Reembolsos de Sistemas</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f4f4] text-[#161616] antialiased">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'system-reimbursements'])

	<main class="px-3 pb-8 pt-5 md:px-6">
		<div class="mx-auto max-w-[1440px]">
			<h1 class="mb-4 text-xl font-bold text-[#123f57]">Reembolsos del Departamento de Sistemas</h1>
			<section aria-label="Reembolsos del Departamento de Sistemas" class="overflow-hidden rounded-[20px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
				<div class="overflow-x-auto">
					<table class="w-full min-w-[680px] border-separate border-spacing-0 text-center text-sm">
						<thead class="bg-[#07577f] text-white">
							<tr>
								<th scope="col" class="px-4 py-3 font-semibold">Orden</th>
								<th scope="col" class="px-4 py-3 font-semibold">Folio</th>
								<th scope="col" class="px-4 py-3 font-semibold">REEMBOLSO</th>
								<th scope="col" class="px-4 py-3 font-semibold">Estado</th>
								<th scope="col" class="px-4 py-3 font-semibold">Fecha de recepción</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($reimbursements as $reimbursement)
								<tr class="text-[#202020] odd:bg-white even:bg-[#fbfbfb]">
									<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $reimbursement['orderNumber'] }}</td>
									<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $reimbursement['folio'] }}</td>
									<td class="break-all border-t border-[#d3dfe5] px-4 py-3">
										@if ($reimbursement['document'])
											<a href="{{ route('systems.reimbursements.documents.download', $reimbursement['orderNumber']) }}" class="font-semibold text-[#07577f] underline hover:text-[#064967]">{{ $reimbursement['document']->original_name }}</a>
										@else
											<span class="text-[#64748b]">Sin archivo</span>
										@endif
									</td>
									<td class="border-t border-[#d3dfe5] px-4 py-3">
										<span @class([
											'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
											'bg-[#e5e7eb] text-[#374151]' => $reimbursement['document'],
											'bg-[#fff7ed] text-[#9a3412]' => ! $reimbursement['document'],
										])>{{ $reimbursement['document'] ? 'Recibido' : 'Pendiente' }}</span>
									</td>
									<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $reimbursement['modifiedAt'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</main>
</body>
</html>
