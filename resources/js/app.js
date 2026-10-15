import {
	normalizePastedDate,
	normalizePastedNumber,
	normalizePastedOptionText,
	normalizePurchaseOrderPasteValues,
	purchaseOrderPasteFields,
	requisitionPasteFields,
} from './requisition-paste.js';

const orderForm = document.querySelector('#order-wizard-form');
const requisitionStep = orderForm?.querySelector('[data-order-step]');
const requisitionPdf = orderForm?.elements.namedItem('requisition_pdf');
const requisitionPdfPreviewBox = document.querySelector('#requisition-pdf-preview-box');
const requisitionPdfPreviewName = document.querySelector('#requisition-pdf-preview-name');
const requisitionPdfPreview = document.querySelector('#requisition-pdf-preview');
let requisitionPdfPreviewUrl = null;

function releaseRequisitionPdfPreview() {
	if (requisitionPdfPreviewUrl) {
		URL.revokeObjectURL(requisitionPdfPreviewUrl);
		requisitionPdfPreviewUrl = null;
	}
}

function handleTabularPaste(event, fields) {
	if (!(event.target instanceof HTMLInputElement || event.target instanceof HTMLSelectElement)) {
		return;
	}

	const fieldIndex = fields.indexOf(event.target.name);
	if (fieldIndex === -1 || !event.clipboardData) {
		return;
	}

	const firstRow = event.clipboardData.getData('text/plain').split(/\r?\n/, 1)[0];
	if (!firstRow.includes('\t')) {
		return;
	}

	event.preventDefault();
	let pastedValues = firstRow.split('\t');
	if (fields === purchaseOrderPasteFields && fieldIndex === 0) {
		pastedValues = normalizePurchaseOrderPasteValues(pastedValues);
	}

	pastedValues.forEach((value, index) => {
		const fieldName = fields[fieldIndex + index];
		if (!fieldName) {
			return;
		}

		const field = orderForm.elements.namedItem(fieldName);
		if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement)) {
			return;
		}

		const trimmedValue = value.trim();
		if (field.type === 'date') {
			field.value = normalizePastedDate(trimmedValue);
		} else if (field.type === 'number') {
			field.value = normalizePastedNumber(trimmedValue);
		} else if (field instanceof HTMLSelectElement) {
			const normalizedValue = normalizePastedOptionText(trimmedValue);
			const option = [...field.options].find((item) => (
				normalizePastedOptionText(item.textContent) === normalizedValue
				|| (item.dataset.name && normalizePastedOptionText(item.dataset.name) === normalizedValue)
				|| (item.dataset.rfc && normalizePastedOptionText(item.dataset.rfc) === normalizedValue)
			));
			field.value = option?.value ?? '';
		} else {
			field.value = trimmedValue;
		}

		field.dispatchEvent(new Event('input', { bubbles: true }));
		field.dispatchEvent(new Event('change', { bubbles: true }));
	});
}

if (orderForm && requisitionStep) {
	if (
		requisitionPdf instanceof HTMLInputElement
		&& requisitionPdfPreviewBox
		&& requisitionPdfPreviewName
		&& requisitionPdfPreview instanceof HTMLIFrameElement
	) {
		requisitionPdf.addEventListener('change', () => {
			releaseRequisitionPdfPreview();
			requisitionPdfPreviewBox.classList.add('hidden');
			requisitionPdfPreview.removeAttribute('src');

			const file = requisitionPdf.files?.[0];
			if (!file) {
				return;
			}

			if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
				requisitionPdf.setCustomValidity('Selecciona un archivo PDF.');
				requisitionPdf.reportValidity();
				requisitionPdf.value = '';
				requisitionPdf.setCustomValidity('');
				return;
			}

			requisitionPdfPreviewUrl = URL.createObjectURL(file);
			requisitionPdfPreviewName.textContent = file.name;
			requisitionPdfPreview.src = requisitionPdfPreviewUrl;
			requisitionPdfPreviewBox.classList.remove('hidden');
		});
	}

	requisitionStep.addEventListener('paste', (event) => handleTabularPaste(event, requisitionPasteFields));
	orderForm.querySelector('[data-purchase-order-paste]')?.addEventListener('paste', (event) => {
		handleTabularPaste(event, purchaseOrderPasteFields);
	});

	orderForm.addEventListener('reset', () => {
		releaseRequisitionPdfPreview();
		requisitionPdfPreviewBox?.classList.add('hidden');
		requisitionPdfPreview?.removeAttribute('src');
	});
}
