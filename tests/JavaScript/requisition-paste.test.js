import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import {
	normalizePastedDate,
	normalizePastedNumber,
	normalizePastedOptionText,
	normalizePurchaseOrderPasteValues,
	purchaseOrderPasteFields,
	requisitionPasteFields,
} from '../../resources/js/requisition-paste.js';

describe('normalizePastedDate', () => {
	it('converts the Spanish month abbreviations used in copied spreadsheet rows', () => {
		assert.equal(normalizePastedDate('29-sep-26'), '2026-09-29');
		assert.equal(normalizePastedDate('02-oct-26'), '2026-10-02');
	});

	it('supports numeric and ISO dates while rejecting invalid calendar dates', () => {
		assert.equal(normalizePastedDate('29/09/2026'), '2026-09-29');
		assert.equal(normalizePastedDate('2026-10-02'), '2026-10-02');
		assert.equal(normalizePastedDate('31-sep-26'), '');
	});
});

describe('requisitionPasteFields', () => {
	it('matches the column order of a copied requisition row', () => {
		assert.deepEqual(requisitionPasteFields, [
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
		]);
	});
});

describe('purchaseOrderPasteFields', () => {
	it('matches the column order of a copied purchase order row', () => {
		assert.deepEqual(purchaseOrderPasteFields, [
			'purchase_order_folio',
			'purchase_order_date',
			'purchase_order_supplier',
			'requisition_folio',
			'quote_amount',
			'purchase_order_partida',
			'purchase_order_iva_included',
			'purchase_order_rms',
			'purchase_order_rf',
		]);
	});
});

describe('normalizePurchaseOrderPasteValues', () => {
	it('keeps the copied purchase order columns aligned, including blank cells', () => {
		const values = [
			'1', '05-oct-26', 'ROCIO DEL SOCORRO DIAZ DE LEON GONZALEZ',
			'REQ/IP/DRMS/2026/0399', '4,872.07', '21601', '', '', 'Si',
		];

		assert.deepEqual(normalizePurchaseOrderPasteValues(values), values);
	});

	it('aligns the copied row and inserts missing Partida and IVA cells before RMS and RF', () => {
		const values = [
			'', 'OC/IP/2026/0795', '05-oct-26', 'ROCIO DEL SOCORRO DIAZ DE LEON GONZALEZ',
			'REQ/IP/DRMS/2026/0399', '4,872.07', '21601', 'Si',
		];

		const normalizedValues = normalizePurchaseOrderPasteValues(values);
		assert.equal(normalizedValues[0], 'OC/IP/2026/0795');
		assert.equal(normalizePastedDate(normalizedValues[1]), '2026-10-05');
		assert.equal(normalizedValues[2], 'ROCIO DEL SOCORRO DIAZ DE LEON GONZALEZ');
		assert.equal(normalizedValues[3], 'REQ/IP/DRMS/2026/0399');
		assert.equal(normalizedValues[4], '4,872.07');
		assert.equal(normalizedValues[5], '');
		assert.equal(normalizedValues[6], '');
		assert.equal(normalizedValues[7], '21601');
		assert.equal(normalizedValues[8], 'Si');
	});

	it('keeps rows without the leading row-number pattern unchanged', () => {
		const values = ['OC/IP/2026/0795', '05-oct-26'];

		assert.deepEqual(normalizePurchaseOrderPasteValues(values), values);
	});
});

describe('normalizePastedOptionText', () => {
	it('matches pasted Spanish yes values with accented option labels', () => {
		assert.equal(normalizePastedOptionText('Si'), normalizePastedOptionText('Sí'));
	});
});

describe('normalizePastedNumber', () => {
	it('converts the comma thousands separator in copied amounts', () => {
		assert.equal(normalizePastedNumber('3,650.37'), '3650.37');
	});

	it('removes the percent sign from copied tax values', () => {
		assert.equal(normalizePastedNumber('16%'), '16');
	});
});
