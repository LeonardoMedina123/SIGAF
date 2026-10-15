export const requisitionPasteFields = [
	'requisition_folio',
	'requisition_created_on',
	'requisition_requested_on',
	'requisition_amount',
	'item_number',
	'requisition_sp',
	'requisition_ppp',
	'requisition_sup',
	'requisition_dir',
	'requisition_process',
	'requisition_rf',
];

export const purchaseOrderPasteFields = [
	'purchase_order_folio',
	'purchase_order_date',
	'purchase_order_supplier',
	'requisition_folio',
	'quote_amount',
	'purchase_order_partida',
	'purchase_order_iva_included',
	'purchase_order_rms',
	'purchase_order_rf',
];

const monthNumbers = {
	ene: 1, jan: 1,
	feb: 2,
	mar: 3,
	abr: 4, apr: 4,
	may: 5,
	jun: 6,
	jul: 7,
	ago: 8, aug: 8,
	sep: 9, sept: 9,
	oct: 10,
	nov: 11,
	dic: 12, dec: 12,
};

function normalizeYear(year) {
	const parsedYear = Number(year);

	return year.length === 2 ? (parsedYear >= 50 ? 1900 : 2000) + parsedYear : parsedYear;
}

export function normalizePastedDate(value) {
	const trimmedValue = value.trim().toLowerCase();
	const isoParts = trimmedValue.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
	const numericParts = trimmedValue.match(/^(\d{1,2})[/-](\d{1,2})[/-](\d{2}|\d{4})$/);
	const namedParts = trimmedValue.match(/^(\d{1,2})[-\s]([a-záéíóú]+)[-\s](\d{2}|\d{4})$/i);
	const parts = isoParts
		? [Number(isoParts[1]), Number(isoParts[2]), Number(isoParts[3])]
		: numericParts
			? [normalizeYear(numericParts[3]), Number(numericParts[2]), Number(numericParts[1])]
			: namedParts && monthNumbers[namedParts[2]]
				? [normalizeYear(namedParts[3]), monthNumbers[namedParts[2]], Number(namedParts[1])]
				: null;

	if (!parts) {
		return '';
	}

	const [year, month, day] = parts;
	const date = new Date(Date.UTC(year, month - 1, day));
	if (date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) {
		return '';
	}

	return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

export function normalizePurchaseOrderPasteValues(values) {
	const hasLeadingRowNumber = /^\d+$/.test(values[0]?.trim() ?? '')
		&& values[1]?.trim() === ''
		&& normalizePastedDate(values[3] ?? '') !== '';
	const hasLeadingBlank = values[0]?.trim() === ''
		&& normalizePastedDate(values[2] ?? '') !== '';

	if (!hasLeadingRowNumber && !hasLeadingBlank) {
		return values;
	}

	const normalizedValues = values.slice(hasLeadingRowNumber ? 2 : 1);
	const hasMissingPartidaAndIva = normalizedValues.length === 7
		&& normalizePastedNumber(normalizedValues[4]) !== ''
		&& ['si', 'no'].includes(normalizePastedOptionText(normalizedValues[6]));

	if (hasMissingPartidaAndIva) {
		normalizedValues.splice(5, 0, '', '');
	}

	return normalizedValues.concat(Array(Math.max(purchaseOrderPasteFields.length - normalizedValues.length, 0)).fill(''));
}

export function normalizePastedOptionText(value) {
	return value.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

export function normalizePastedNumber(value) {
	let normalizedValue = value.trim().replace(/[^\d,.-]/g, '');
	const lastComma = normalizedValue.lastIndexOf(',');
	const lastPeriod = normalizedValue.lastIndexOf('.');
	if (lastComma !== -1 && lastPeriod !== -1) {
		const decimalSeparator = lastComma > lastPeriod ? ',' : '.';
		const thousandsSeparator = decimalSeparator === ',' ? '.' : ',';
		normalizedValue = normalizedValue.split(thousandsSeparator).join('').replace(decimalSeparator, '.');
	} else if (lastComma !== -1) {
		const decimalDigits = normalizedValue.length - lastComma - 1;
		normalizedValue = decimalDigits > 0 && decimalDigits <= 2
			? normalizedValue.replace(',', '.')
			: normalizedValue.replace(/,/g, '');
	}

	return /^-?\d+(?:\.\d+)?$/.test(normalizedValue) ? normalizedValue : '';
}
