<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SIGAF | Portal de proveedor</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-[#161616] antialiased">
	@include('partials.app-header')
	@include('partials.navigation', ['activePage' => 'home'])

	<main class="px-3 pb-8 pt-6 md:px-6 md:pt-8">
		<div class="mx-auto max-w-[1440px]">
			@if (session('success'))
				<p role="status" class="mb-3 rounded-lg bg-[#e8f8f0] px-4 py-2 text-sm font-medium text-[#145538]">{{ session('success') }}</p>
			@endif
			@if ($errors->any())
				<div role="alert" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
					<p class="font-semibold">No se pudieron enviar los documentos. Revisa los archivos e inténtalo de nuevo.</p>
					<ul class="mt-1 list-inside list-disc">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<div class="mb-3 flex flex-wrap items-center gap-2 px-1">
				<h1 class="text-lg font-semibold text-[#123f57] md:text-xl">Órdenes de compra</h1>
			</div>

			<div class="overflow-hidden rounded-[28px] border border-[#b7b7b7] bg-white shadow-[0_2px_5px_rgba(0,0,0,0.1)]">
				<div class="max-h-[calc(100vh-190px)] overflow-auto">
					<table class="w-full min-w-[800px] border-separate border-spacing-0 text-left">
						<caption class="sr-only">Órdenes de compra y estado de los documentos del proveedor</caption>
						<thead class="sticky top-0 z-10 bg-[#07577f] text-white">
							<tr>
								<th scope="col" class="rounded-tl-[24px] px-5 py-4 text-sm font-semibold md:px-8">Orden de compra</th>
								<th scope="col" class="px-5 py-4 text-sm font-semibold md:px-8">Factura y XML</th>
								<th scope="col" class="px-5 py-4 text-sm font-semibold md:px-8">Validación de pago</th>
								<th scope="col" class="rounded-tr-[24px] px-5 py-4 text-sm font-semibold md:px-8">Fecha de modificación</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($purchaseOrders as $purchaseOrder)
								<tr class="text-sm text-[#202020] odd:bg-white even:bg-[#fbfbfb]">
									<td class="border-t border-[#b7b7b7] px-5 py-3 md:px-8">
										<a href="{{ route('supplier.purchase-orders.pdf', $purchaseOrder) }}" target="_blank" rel="noopener" class="whitespace-nowrap font-medium text-[#07529a] underline decoration-1 underline-offset-2">{{ $purchaseOrder->folio }}</a>
									</td>
									<td class="border-t border-[#b7b7b7] px-5 py-2 md:px-8">
										@if ($purchaseOrder->hasDocument('invoice_pdf') && $purchaseOrder->hasDocument('invoice_xml'))
											<span class="inline-flex min-h-9 items-center justify-center rounded-full bg-[#e8f8f0] px-4 text-center text-xs font-medium text-[#145538]">Factura y XML recibidos</span>
										@else
											<button type="button" data-open-invoice-dialog data-order-folio="{{ $purchaseOrder->folio }}" data-invoice-url="{{ route('supplier.purchase-orders.invoices.store', $purchaseOrder) }}" data-pdf-url="{{ route('supplier.purchase-orders.pdf', $purchaseOrder) }}" class="inline-flex min-h-9 cursor-pointer items-center justify-center gap-2 rounded-full bg-[#22a06b] px-4 text-xs font-semibold text-white transition hover:bg-[#198754] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f]">
												<span aria-hidden="true" class="text-xl font-light leading-none">+</span>
												<span>Adjunta factura y XML</span>
											</button>
										@endif
									</td>
									<td class="border-t border-[#b7b7b7] px-5 py-2 md:px-8">
										@if ($purchaseOrder->payment_validated_at === null)
											<span class="text-xs text-[#64748b]">Pendiente de validación del pago</span>
										@elseif ($purchaseOrder->hasDocument('payment_complement'))
											<span class="inline-flex min-h-9 items-center justify-center rounded-full bg-[#e8f8f0] px-4 text-center text-xs font-medium text-[#145538]">Complemento recibido</span>
										@else
											<button type="button" data-open-payment-dialog data-order-folio="{{ $purchaseOrder->folio }}" data-payment-url="{{ route('supplier.purchase-orders.payment-complement.store', $purchaseOrder) }}" data-pdf-url="{{ route('supplier.purchase-orders.pdf', $purchaseOrder) }}" class="inline-flex min-h-9 cursor-pointer items-center justify-center gap-2 rounded-full bg-[#22a06b] px-4 text-xs font-semibold text-white transition hover:bg-[#198754] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f]">
												<span aria-hidden="true" class="text-xl font-light leading-none">+</span>
												<span>Adjunta complemento de pago</span>
											</button>
										@endif
									</td>
									<td class="border-t border-[#b7b7b7] px-5 py-3 text-center text-sm md:px-8">{{ $purchaseOrder->updated_at->format('d/m/Y') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="border-t border-[#b7b7b7] px-5 py-12 text-center text-sm text-[#64748b]">
										Aún no hay órdenes de compra para este proveedor.
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</main>

	<dialog id="invoice-dialog" aria-labelledby="invoice-dialog-title" class="m-auto w-[calc(100%-2rem)] max-w-[880px] overflow-visible rounded-[48px] border-0 bg-[#d7f1ff] p-0 text-[#07577f] shadow-[0_16px_48px_rgba(0,0,0,0.24)] backdrop:bg-black/35">
		<div class="relative rounded-[48px] px-6 py-7 sm:px-10 sm:py-8 md:px-12 md:py-8">
			<button type="button" data-close-invoice-dialog aria-label="Cerrar ventana" class="absolute right-5 top-4 flex h-9 w-9 items-center justify-center rounded-full text-2xl leading-none text-[#07577f] transition hover:bg-white/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#07577f]">
				<span aria-hidden="true">&times;</span>
			</button>

			<h2 id="invoice-dialog-title" class="pr-10 text-lg font-semibold sm:text-xl">
				Orden de compra:
				<span data-dialog-order-folio class="font-normal text-[#161616] underline decoration-1 underline-offset-2"></span>
			</h2>

			<a data-dialog-pdf href="#" target="_blank" rel="noopener" class="mt-4 inline-block text-sm font-semibold text-[#07577f] underline decoration-1 underline-offset-2">Consultar PDF de la orden de compra</a>

			<form data-invoice-form method="POST" enctype="multipart/form-data" class="mt-6">
				@csrf
				<div class="grid gap-5 sm:grid-cols-2 sm:gap-8">
					<label class="grid content-start gap-3 text-base font-medium sm:text-lg">
						<span>Añadir Factura:</span>
						<span data-upload-control data-selected="false" class="inline-flex min-h-10 w-fit max-w-full cursor-pointer items-center gap-2 rounded-full bg-[#073b5c] px-4 text-sm font-semibold text-white transition hover:bg-[#052f49] data-[selected=true]:bg-[#b5b5b5] data-[selected=true]:text-[#222] data-[selected=true]:hover:bg-[#a4a4a4] focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-[#07577f]">
							<span data-upload-add-icon aria-hidden="true" class="text-xl font-light leading-none">+</span>
							<svg data-upload-modify-icon class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M7.5 16.5l8.25-8.25a1.875 1.875 0 1 1 2.652 2.652L10.152 19.15a4.5 4.5 0 0 1-1.897 1.13L4.5 21l.72-3.755A4.5 4.5 0 0 1 6.35 15.35Z" />
							</svg>
							<span data-invoice-file-label class="truncate">Adjunta Factura</span>
							<input type="file" name="invoice_pdf" accept=".pdf,application/pdf" required class="sr-only" aria-label="Adjuntar factura en formato PDF" data-invoice-file>
						</span>
						<span data-invoice-file-name aria-live="polite" class="hidden max-w-full truncate text-xs text-[#31576a]"></span>
					</label>

					<label class="grid content-start gap-3 text-base font-medium sm:text-lg">
						<span>Añadir XML:</span>
						<span data-upload-control data-selected="false" class="inline-flex min-h-10 w-fit max-w-full cursor-pointer items-center gap-2 rounded-full bg-[#073b5c] px-4 text-sm font-semibold text-white transition hover:bg-[#052f49] data-[selected=true]:bg-[#b5b5b5] data-[selected=true]:text-[#222] data-[selected=true]:hover:bg-[#a4a4a4] focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-[#07577f]">
							<span data-upload-add-icon aria-hidden="true" class="text-xl font-light leading-none">+</span>
							<svg data-upload-modify-icon class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M7.5 16.5l8.25-8.25a1.875 1.875 0 1 1 2.652 2.652L10.152 19.15a4.5 4.5 0 0 1-1.897 1.13L4.5 21l.72-3.755A4.5 4.5 0 0 1 6.35 15.35Z" />
							</svg>
							<span data-xml-file-label class="truncate">Adjunta archivo XML</span>
							<input type="file" name="invoice_xml" accept=".xml,application/xml,text/xml" required class="sr-only" aria-label="Adjuntar factura en formato XML" data-xml-file>
						</span>
						<span data-xml-file-name aria-live="polite" class="hidden max-w-full truncate text-xs text-[#31576a]"></span>
					</label>
				</div>

				<div class="mt-6 rounded-[36px] bg-[#e1e1e1] px-5 py-4 text-sm leading-relaxed text-[#202020] sm:px-7">
					<p>Antes de enviar estos documentos, le solicitamos revisar que sean los correctos.</p>
					<p class="mt-2">El complemento de pago estará disponible en esta pantalla cuando administración valide el pago.</p>
					<p class="mt-2">Agradecemos su atención.</p>
					<label class="mt-3 flex cursor-pointer items-start gap-2 font-medium">
						<input type="checkbox" name="review_confirmed" value="1" required class="mt-1 h-4 w-4 shrink-0 accent-[#08d985]" data-confirm-invoice>
						<span>He leído y confirmo la información anterior.</span>
					</label>
					<div class="mt-2 flex justify-center">
						<button type="submit" data-invoice-submit disabled class="cursor-not-allowed rounded-full bg-[#b5b5b5] px-5 py-1 text-base font-semibold text-[#555] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f] disabled:cursor-not-allowed enabled:cursor-pointer enabled:bg-[#08d985] enabled:text-[#073a28] enabled:hover:bg-[#00c976]">ENVIAR</button>
					</div>
				</div>
			</form>
		</div>
	</dialog>

	<dialog id="payment-dialog" aria-labelledby="payment-dialog-title" class="m-auto w-[calc(100%-2rem)] max-w-[880px] overflow-visible rounded-[48px] border-0 bg-[#d7f1ff] p-0 text-[#07577f] shadow-[0_16px_48px_rgba(0,0,0,0.24)] backdrop:bg-black/35">
		<div class="relative rounded-[48px] px-6 py-7 sm:px-10 sm:py-8 md:px-12 md:py-8">
			<button type="button" data-close-payment-dialog aria-label="Cerrar ventana" class="absolute right-5 top-4 flex h-9 w-9 items-center justify-center rounded-full text-2xl leading-none text-[#07577f] transition hover:bg-white/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#07577f]">
				<span aria-hidden="true">&times;</span>
			</button>

			<h2 id="payment-dialog-title" class="pr-10 text-lg font-semibold sm:text-xl">
				Orden de compra:
				<span data-payment-order-folio class="font-normal text-[#161616] underline decoration-1 underline-offset-2"></span>
			</h2>

			<a data-dialog-pdf href="#" target="_blank" rel="noopener" class="mt-4 inline-block text-sm font-semibold text-[#07577f] underline decoration-1 underline-offset-2">Consultar PDF de la orden de compra</a>

			<form data-payment-form method="POST" enctype="multipart/form-data" class="mt-5">
				@csrf
				<label class="grid justify-items-center gap-3 text-base font-medium sm:text-lg">
					<span>Añadir complemento de pago:</span>
					<span data-upload-control data-selected="false" class="inline-flex min-h-10 w-fit max-w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-[#073b5c] px-5 text-sm font-semibold text-white transition hover:bg-[#052f49] data-[selected=true]:bg-[#b5b5b5] data-[selected=true]:text-[#222] data-[selected=true]:hover:bg-[#a4a4a4] focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-[#07577f]">
						<span data-upload-add-icon aria-hidden="true" class="text-xl font-light leading-none">+</span>
						<svg data-upload-modify-icon class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M7.5 16.5l8.25-8.25a1.875 1.875 0 1 1 2.652 2.652L10.152 19.15a4.5 4.5 0 0 1-1.897 1.13L4.5 21l.72-3.755A4.5 4.5 0 0 1 6.35 15.35Z" />
						</svg>
						<span data-payment-file-label class="truncate">Añadir complemento</span>
						<input type="file" name="payment_complement" accept=".pdf,application/pdf" required class="sr-only" aria-label="Adjuntar complemento de pago en formato PDF" data-payment-file>
					</span>
					<span data-payment-file-name aria-live="polite" class="hidden max-w-full truncate text-xs text-[#31576a]"></span>
				</label>

				<div class="mt-6 rounded-[36px] bg-[#e1e1e1] px-5 py-4 text-sm leading-relaxed text-[#202020] sm:px-7">
					<p>Antes de enviar estos documentos, le solicitamos revisar que sean los correctos.</p>
					<p class="mt-2">Este complemento se solicita porque administración ya validó el pago.</p>
					<p class="mt-2">Agradecemos su atención.</p>
					<label class="mt-3 flex cursor-pointer items-start gap-2 font-medium">
						<input type="checkbox" name="review_confirmed" value="1" required class="mt-1 h-4 w-4 shrink-0 accent-[#08d985]" data-confirm-payment>
						<span>He leído y confirmo la información anterior.</span>
					</label>
					<div class="mt-2 flex justify-center">
						<button type="submit" data-payment-submit disabled class="cursor-not-allowed rounded-full bg-[#b5b5b5] px-5 py-1 text-base font-semibold text-[#555] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#07577f] disabled:cursor-not-allowed enabled:cursor-pointer enabled:bg-[#08d985] enabled:text-[#073a28] enabled:hover:bg-[#00c976]">ENVIAR</button>
					</div>
				</div>
			</form>
		</div>
	</dialog>

	<script>
		const invoiceDialog = document.querySelector('#invoice-dialog');
		const invoiceForm = document.querySelector('[data-invoice-form]');
		const invoiceFile = document.querySelector('[data-invoice-file]');
		const xmlFile = document.querySelector('[data-xml-file]');
		const confirmInvoice = document.querySelector('[data-confirm-invoice]');
		const invoiceSubmit = document.querySelector('[data-invoice-submit]');
		const paymentDialog = document.querySelector('#payment-dialog');
		const paymentForm = document.querySelector('[data-payment-form]');
		const paymentFile = document.querySelector('[data-payment-file]');
		const confirmPayment = document.querySelector('[data-confirm-payment]');
		const paymentSubmit = document.querySelector('[data-payment-submit]');
		let currentInvoiceOrderFolio = null;
		let currentPaymentOrderFolio = null;
		const updateInvoiceSubmitState = () => {
			invoiceSubmit.disabled = !confirmInvoice.checked || !invoiceFile.files?.length || !xmlFile.files?.length;
		};
		const updatePaymentSubmitState = () => {
			paymentSubmit.disabled = !confirmPayment.checked || !paymentFile.files?.length;
		};
		const updateUploadControl = (input, labelSelector, fileNameSelector, actionLabel, modifyLabel) => {
			const selectedFile = input.files?.[0];
			const control = input.closest('[data-upload-control]');
			const fileName = document.querySelector(fileNameSelector);

			control.dataset.selected = selectedFile ? 'true' : 'false';
			control.querySelector('[data-upload-add-icon]').classList.toggle('hidden', Boolean(selectedFile));
			control.querySelector('[data-upload-modify-icon]').classList.toggle('hidden', !selectedFile);
			document.querySelector(labelSelector).textContent = selectedFile ? modifyLabel : actionLabel;
			fileName.textContent = selectedFile?.name ?? '';
			fileName.classList.toggle('hidden', !selectedFile);
		};

		document.querySelectorAll('[data-open-invoice-dialog]').forEach((button) => {
			button.addEventListener('click', () => {
				const orderFolio = button.dataset.orderFolio;

				invoiceForm.action = button.dataset.invoiceUrl;
				document.querySelector('[data-dialog-order-folio]').textContent = orderFolio;
				document.querySelectorAll('[data-dialog-pdf]').forEach((link) => {
					link.href = button.dataset.pdfUrl;
				});
				if (currentInvoiceOrderFolio !== orderFolio) {
					invoiceForm.reset();
					updateUploadControl(invoiceFile, '[data-invoice-file-label]', '[data-invoice-file-name]', 'Adjunta Factura', 'Modificar factura');
					updateUploadControl(xmlFile, '[data-xml-file-label]', '[data-xml-file-name]', 'Adjunta archivo XML', 'Modificar XML');
					currentInvoiceOrderFolio = orderFolio;
				}
				updateInvoiceSubmitState();
				invoiceDialog.showModal();
			});
		});

		document.querySelectorAll('[data-open-payment-dialog]').forEach((button) => {
			button.addEventListener('click', () => {
				const orderFolio = button.dataset.orderFolio;

				paymentForm.action = button.dataset.paymentUrl;
				document.querySelector('[data-payment-order-folio]').textContent = orderFolio;
				document.querySelectorAll('[data-dialog-pdf]').forEach((link) => {
					link.href = button.dataset.pdfUrl;
				});
				if (currentPaymentOrderFolio !== orderFolio) {
					paymentForm.reset();
					updateUploadControl(paymentFile, '[data-payment-file-label]', '[data-payment-file-name]', 'Añadir complemento', 'Modificar complemento');
					currentPaymentOrderFolio = orderFolio;
				}
				updatePaymentSubmitState();
				paymentDialog.showModal();
			});
		});

		document.querySelector('[data-close-payment-dialog]').addEventListener('click', () => {
			paymentDialog.close();
		});

		paymentDialog.addEventListener('click', (event) => {
			if (event.target === paymentDialog) {
				paymentDialog.close();
			}
		});

		document.querySelector('[data-close-invoice-dialog]').addEventListener('click', () => {
			invoiceDialog.close();
		});

		invoiceDialog.addEventListener('click', (event) => {
			if (event.target === invoiceDialog) {
				invoiceDialog.close();
			}
		});

		invoiceFile.addEventListener('change', () => {
			updateUploadControl(invoiceFile, '[data-invoice-file-label]', '[data-invoice-file-name]', 'Adjunta Factura', 'Modificar factura');
			updateInvoiceSubmitState();
		});
		xmlFile.addEventListener('change', () => {
			updateUploadControl(xmlFile, '[data-xml-file-label]', '[data-xml-file-name]', 'Adjunta archivo XML', 'Modificar XML');
			updateInvoiceSubmitState();
		});

		confirmInvoice.addEventListener('change', updateInvoiceSubmitState);
		paymentFile.addEventListener('change', () => {
			updateUploadControl(paymentFile, '[data-payment-file-label]', '[data-payment-file-name]', 'Añadir complemento', 'Modificar complemento');
			updatePaymentSubmitState();
		});
		confirmPayment.addEventListener('change', updatePaymentSubmitState);

		updateUploadControl(invoiceFile, '[data-invoice-file-label]', '[data-invoice-file-name]', 'Adjunta Factura', 'Modificar factura');
		updateUploadControl(xmlFile, '[data-xml-file-label]', '[data-xml-file-name]', 'Adjunta archivo XML', 'Modificar XML');
		updateUploadControl(paymentFile, '[data-payment-file-label]', '[data-payment-file-name]', 'Añadir complemento', 'Modificar complemento');
		updateInvoiceSubmitState();
		updatePaymentSubmitState();
	</script>
</body>
</html>
