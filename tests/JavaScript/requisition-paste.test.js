import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import {
	normalizePastedDate,
	normalizePastedNumber,
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
			'supplier_id',
			'requisition_folio',
			'quote_amount',
			'purchase_order_partida',
			'purchase_order_iva',
			'purchase_order_rms',
			'purchase_order_rf',
		]);
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
