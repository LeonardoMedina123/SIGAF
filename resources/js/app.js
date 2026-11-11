import {
	normalizePastedDate,
	normalizePastedNumber,
	normalizePastedOptionText,
	normalizePurchaseOrderPasteValues,
	purchaseOrderPasteFields,
	requisitionPasteFields,
} from './requisition-paste.js';
import { initializeHomeFilters } from './home-filters.js';

initializeHomeFilters();

const archiveDialog = document.querySelector('#document-archive-dialog');
const archiveDialogOpenButton = document.querySelector('[data-archive-dialog-open]');

if (archiveDialog instanceof HTMLDialogElement && archiveDialogOpenButton instanceof HTMLButtonElement) {
	const updateArchiveDialogTriggerState = () => {
		archiveDialogOpenButton.setAttribute('aria-expanded', String(archiveDialog.open));
	};

	if (archiveDialog.dataset.openOnLoad === 'true') {
		archiveDialog.showModal();
		updateArchiveDialogTriggerState();
	}

	archiveDialogOpenButton.addEventListener('click', () => {
		archiveDialog.showModal();
		updateArchiveDialogTriggerState();
		archiveDialog.querySelector('input[name="from_date"]')?.focus();
	});

	archiveDialog.addEventListener('close', updateArchiveDialogTriggerState);

	archiveDialog.querySelectorAll('[data-archive-dialog-close]').forEach((button) => {
		button.addEventListener('click', () => archiveDialog.close());
	});

	const fromDateInput = archiveDialog.querySelector('input[name="from_date"]');
	const toDateInput = archiveDialog.querySelector('input[name="to_date"]');

	if (fromDateInput instanceof HTMLInputElement && toDateInput instanceof HTMLInputElement) {
		toDateInput.min = fromDateInput.value;
		fromDateInput.addEventListener('change', () => {
			toDateInput.min = fromDateInput.value;
		});
	}
}

document.querySelectorAll('[data-notification-read-form]').forEach((form) => {
	form.addEventListener('submit', async (event) => {
		event.preventDefault();

		if (!(form instanceof HTMLFormElement) || form.dataset.submitting === 'true') {
			return;
		}

		form.dataset.submitting = 'true';
		form.parentElement?.querySelector('[data-notification-error]')?.remove();

		const notificationId = form.dataset.notificationId;
		const items = [...document.querySelectorAll('[data-notification-item]')]
			.filter((item) => item.dataset.notificationId === notificationId);
		const snapshots = items.map((item) => {
			const readForm = item.querySelector('[data-notification-read-form]');
			const readLabel = document.createElement('span');
			const className = item.className;

			readLabel.className = readForm?.dataset.readLabelClass ?? '';
			readLabel.textContent = 'Leída';
			readLabel.setAttribute('role', 'status');
			readLabel.tabIndex = -1;

			if (readForm) {
				readForm.replaceWith(readLabel);
			}

			const unreadClasses = item.dataset.notificationUnreadClasses?.split(' ').filter(Boolean) ?? [];
			const readClasses = item.dataset.notificationReadClasses?.split(' ').filter(Boolean) ?? [];

			if (unreadClasses.length > 0) {
				item.classList.remove(...unreadClasses);
			}
			if (readClasses.length > 0) {
				item.classList.add(...readClasses);
			}

			return {
				item,
				readForm,
				readLabel,
				className,
			};
		});
		const unreadBadge = document.querySelector('[data-unread-notification-count]');
		const previousUnreadCount = unreadBadge ? Number(unreadBadge.textContent) : null;

		if (unreadBadge && previousUnreadCount !== null) {
			unreadBadge.textContent = String(Math.max(0, previousUnreadCount - 1));
			unreadBadge.classList.toggle('hidden', previousUnreadCount <= 1);
		}
		snapshots.find((snapshot) => snapshot.readForm === form)?.readLabel.focus();

		try {
			const response = await fetch(form.action, {
				method: form.method,
				body: new FormData(form),
				headers: {
					Accept: 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
			});

			if (!response.ok) {
				throw new Error('No fue posible guardar el estado de la notificación.');
			}

			const result = await response.json();
			if (unreadBadge && Number.isInteger(result.unread_count)) {
				unreadBadge.textContent = String(result.unread_count);
				unreadBadge.classList.toggle('hidden', result.unread_count === 0);
			}
		} catch {
			for (const snapshot of snapshots) {
				if (snapshot.readForm && snapshot.readLabel.isConnected) {
					snapshot.readLabel.replaceWith(snapshot.readForm);
				}

				snapshot.item.className = snapshot.className;
			}

			if (unreadBadge && previousUnreadCount !== null) {
				unreadBadge.textContent = String(previousUnreadCount);
				unreadBadge.classList.remove('hidden');
			}

			const errorMessage = document.createElement('p');
			errorMessage.dataset.notificationError = '';
			errorMessage.setAttribute('role', 'alert');
			errorMessage.className = 'mt-2 text-xs text-[#8b3030]';
			errorMessage.textContent = 'No se pudo marcar como leída. Inténtalo de nuevo.';
			form.insertAdjacentElement('afterend', errorMessage);
			form.querySelector('button')?.focus();
		} finally {
			delete form.dataset.submitting;
		}
	});
});

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
