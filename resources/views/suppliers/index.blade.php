<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Catálogo de proveedores</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f4f4] text-[#161616] antialiased">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'suppliers'])

	<main class="px-3 pb-6 pt-5 md:px-6">
		<div class="mb-4 flex flex-wrap items-end justify-between gap-3">
			<div>
				<p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#087aa7]">Directorio</p>
				<h1 class="mt-1 text-2xl font-semibold text-[#123f57]">Catálogo de proveedores</h1>
				<p class="mt-1 text-sm text-[#52636c]">Consulta los proveedores registrados en el sistema.</p>
			</div>
			@if (session('success'))
				<p role="status" class="rounded-lg bg-[#e8f8f0] px-4 py-2 text-sm font-medium text-[#145538]">{{ session('success') }}</p>
			@endif
		</div>

		@if ($errors->any())
			<div id="supplier-errors" role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
				<p class="font-semibold">No se pudo guardar el proveedor. Revisa los datos e inténtalo de nuevo.</p>
				<ul class="mt-1 list-inside list-disc">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<section aria-label="Catálogo de proveedores" class="overflow-auto rounded-[20px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
			<table class="w-full min-w-[1500px] border-separate border-spacing-0 text-left">
				<thead class="bg-[#07577f] text-white">
					<tr>
						<th scope="col" class="rounded-tl-[19px] px-4 py-3 text-sm font-semibold">Número</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Nombre</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">R.F.C.</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Tipo de persona</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Régimen fiscal</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Uso CFDI</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Correo de contacto</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">C.U.R.P.</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Dirección</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Ciudad</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Teléfono 1</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Nombre de contacto</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Celular</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Act./Eco.</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">I.V.A. (%)</th>
						<th scope="col" class="px-4 py-3 text-sm font-semibold">Estatus</th>
						<th scope="col" class="rounded-tr-[19px] px-4 py-3 text-sm font-semibold">IT Captura</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($suppliers as $supplier)
						<tr class="text-sm text-[#263b46] odd:bg-white even:bg-[#f5f9fb]">
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->number }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3 font-medium">{{ $supplier->name }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->rfc }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $personTypes[$supplier->person_type] ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->tax_regime ? $supplier->tax_regime.' - '.($taxRegimes[$supplier->tax_regime] ?? '') : '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->cfdi_use ? $supplier->cfdi_use.' - '.($cfdiUses[$supplier->cfdi_use] ?? '') : '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->contact_email ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->curp ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->address ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->city ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->phone ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->representative ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->cellphone ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->economic_activity ?? '—' }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ number_format((float) $supplier->iva_percentage, 2) }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->status }}</td>
							<td class="border-t border-[#d3dfe5] px-4 py-3">{{ $supplier->it_capture ?? '—' }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="17" class="border-t border-[#d3dfe5] px-4 py-14 text-center text-sm text-[#60737d]">
								Aún no hay proveedores registrados. Usa el botón para agregar el primero.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</section>
		<div class="mt-4">
			{{ $suppliers->links() }}
		</div>
	</main>

	<button id="add-supplier-button" type="button" class="fixed bottom-5 right-5 z-20 flex h-14 items-center gap-2 rounded-full bg-[#08bf62] px-6 text-base font-semibold text-[#062b19] shadow-[0_3px_9px_rgba(0,0,0,0.18)] transition hover:bg-[#06ad58] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f] md:bottom-9 md:right-10" aria-label="Agregar un nuevo proveedor">
		<span aria-hidden="true" class="text-2xl font-light leading-none">+</span>
		<span>Agregar proveedor</span>
	</button>

	<div id="supplier-dialog" @class([
		'fixed inset-0 z-40 overflow-y-auto bg-black/45 px-2 py-3 md:px-5 md:py-6',
		'hidden' => ! $errors->any(),
	]) role="dialog" aria-modal="true" aria-labelledby="supplier-title" @if ($errors->any()) aria-describedby="supplier-errors" @endif>
		<div class="mx-auto w-full max-w-[850px] overflow-hidden rounded-[18px] bg-white shadow-[0_12px_40px_rgba(0,0,0,0.25)]">
			<header class="flex items-center justify-between gap-4 border-b border-[#d5e3e9] px-5 py-4 md:px-8">
				<div>
					<p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#087aa7]">Catálogo</p>
					<h2 id="supplier-title" class="mt-1 text-xl font-semibold text-[#123f57]">Agregar proveedor</h2>
				</div>
				<button type="button" data-supplier-close aria-label="Cerrar" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#edf1f3] text-[#46545b] transition hover:bg-[#dce5e9]">
					<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
						<path d="m6 6 12 12M18 6 6 18" />
					</svg>
				</button>
			</header>
			<form action="{{ route('suppliers.store') }}" method="POST" class="grid gap-4 px-5 py-5 md:grid-cols-2 md:px-8">
				@csrf
				<label class="grid gap-1 text-sm">Número <input name="number" required maxlength="50" value="{{ old('number') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Nombre <input name="name" required maxlength="255" value="{{ old('name') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">R.F.C. <input name="rfc" required minlength="12" maxlength="13" value="{{ old('rfc') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 uppercase text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Tipo de persona
					<select name="person_type" required class="h-10 rounded-lg border border-[#ccd8de] bg-white px-3 text-[#17252b]">
						<option value="">Selecciona</option>
						@foreach ($personTypes as $value => $label)
							<option value="{{ $value }}" @selected(old('person_type') === $value)>{{ $label }}</option>
						@endforeach
					</select>
				</label>
				<label class="grid gap-1 text-sm">Régimen fiscal
					<select name="tax_regime" required class="h-10 rounded-lg border border-[#ccd8de] bg-white px-3 text-[#17252b]">
						<option value="">Selecciona</option>
						@foreach ($taxRegimes as $value => $label)
							<option value="{{ $value }}" @selected(old('tax_regime') === $value)>{{ $value }} - {{ $label }}</option>
						@endforeach
					</select>
				</label>
				<label class="grid gap-1 text-sm">Uso de CFDI
					<select name="cfdi_use" required class="h-10 rounded-lg border border-[#ccd8de] bg-white px-3 text-[#17252b]">
						<option value="">Selecciona</option>
						@foreach ($cfdiUses as $value => $label)
							<option value="{{ $value }}" @selected(old('cfdi_use') === $value)>{{ $value }} - {{ $label }}</option>
						@endforeach
					</select>
				</label>
				<label class="grid gap-1 text-sm">Correo de contacto <input name="contact_email" type="email" required maxlength="255" value="{{ old('contact_email') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">C.U.R.P. <input name="curp" maxlength="18" value="{{ old('curp') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 uppercase text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm md:col-span-2">Dirección <input name="address" maxlength="1000" value="{{ old('address') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Ciudad <input name="city" maxlength="255" value="{{ old('city') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Teléfono 1 <input name="phone" maxlength="30" value="{{ old('phone') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Nombre de contacto <input name="representative" required maxlength="255" value="{{ old('representative') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Celular <input name="cellphone" maxlength="30" value="{{ old('cellphone') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Act./Eco. <input name="economic_activity" maxlength="255" value="{{ old('economic_activity') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">I.V.A. (%) <input name="iva_percentage" type="number" required min="0" max="100" step="0.01" value="{{ old('iva_percentage', '16') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">Estatus <input name="status" required maxlength="100" value="{{ old('status', 'Activo') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<label class="grid gap-1 text-sm">IT Captura <input name="it_capture" maxlength="255" value="{{ old('it_capture') }}" class="h-10 rounded-lg border border-[#ccd8de] px-3 text-[#17252b]" /></label>
				<div class="flex justify-end gap-3 md:col-span-2">
					<button type="button" data-supplier-close class="rounded-lg border border-[#9aaab2] px-4 py-2 text-sm font-semibold text-[#34464e]">Cancelar</button>
					<button type="submit" class="rounded-lg bg-[#07577f] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#064967]">Guardar proveedor</button>
				</div>
			</form>
		</div>
	</div>

	<script>
		const supplierDialog = document.querySelector('#supplier-dialog');
		const addSupplierButton = document.querySelector('#add-supplier-button');
		addSupplierButton.addEventListener('click', () => {
			supplierDialog.classList.remove('hidden');
			supplierDialog.querySelector('input[name="number"]').focus();
		});
		supplierDialog.querySelectorAll('[data-supplier-close]').forEach((button) => button.addEventListener('click', () => supplierDialog.classList.add('hidden')));
		supplierDialog.addEventListener('click', (event) => {
			if (event.target === supplierDialog) {
				supplierDialog.classList.add('hidden');
			}
		});
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !supplierDialog.classList.contains('hidden')) {
				supplierDialog.classList.add('hidden');
				addSupplierButton.focus();
			}
		});
	</script>
</body>
</html>
