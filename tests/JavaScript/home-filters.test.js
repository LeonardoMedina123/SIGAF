import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { matchesFilterValues } from '../../resources/js/home-filters.js';

describe('matchesFilterValues', () => {
	it('matches text without regard to letter case and applies multiple filters together', () => {
		assert.equal(matchesFilterValues({
			'order-folio': 'OC-2026-019',
			'payment-status': 'Pagado',
		}, [
			{ field: 'order-folio', operator: 'contains', type: 'text', value: 'oc-2026' },
			{ field: 'payment-status', operator: 'contains', type: 'select', value: 'Pagado' },
		]), true);
	});

	it('matches dates exactly and supports numeric comparison operators', () => {
		assert.equal(matchesFilterValues({
			'requisition-amount': '2500.00',
			'order-date': '2026-10-27',
		}, [
			{ field: 'requisition-amount', operator: '>', type: 'number', value: '2000' },
			{ field: 'order-date', operator: 'contains', type: 'date', value: '2026-10-27' },
		]), true);
	});

	it('does not match numeric filters when a row has no amount', () => {
		assert.equal(matchesFilterValues({
			'requisition-amount': '',
		}, [
			{ field: 'requisition-amount', operator: '=', type: 'number', value: '0' },
		]), false);
	});
});
