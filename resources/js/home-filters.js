function normalize(value) {
	return value.trim().toLocaleLowerCase();
}

export function matchesFilterValues(values, filters) {
	return filters.every(({ field, operator, type, value }) => {
		const actualValue = values[field] ?? '';

		if (type === 'number') {
			if (actualValue === '') {
				return false;
			}

			const actualNumber = Number(actualValue);
			const expectedNumber = Number(value);

			if (!Number.isFinite(actualNumber) || !Number.isFinite(expectedNumber)) {
				return false;
			}

			if (operator === '>') {
				return actualNumber > expectedNumber;
			}

			if (operator === '<') {
				return actualNumber < expectedNumber;
			}

			return actualNumber === expectedNumber;
		}

		if (type === 'date' || type === 'select') {
			return normalize(actualValue) === normalize(value);
		}

		return normalize(actualValue).includes(normalize(value));
	});
}

export function initializeHomeFilters() {
	const filterMenu = document.querySelector('#filter-menu');
	const filterTitle = document.querySelector('#filter-menu-title');
	const filterContent = document.querySelector('#filter-menu-content');
	const applyButton = filterMenu?.querySelector('[data-filter-apply]');
	const clearButton = filterMenu?.querySelector('[data-filter-clear]');
	const table = document.querySelector('section[aria-label="Requisiciones"]');
	const rows = [...(table?.querySelectorAll('[data-filter-row]') ?? [])];
	const emptyState = table?.querySelector('[data-filter-empty]');

	if (!filterMenu || !filterTitle || !filterContent || !applyButton || !clearButton || !table || rows.length === 0) {
		return;
	}

	const filters = new Map();
	let activeFilterTrigger = null;

	function closeFilterMenu() {
		filterMenu.classList.add('hidden');
		activeFilterTrigger?.setAttribute('aria-expanded', 'false');
		activeFilterTrigger = null;
	}

	function applyFilters() {
		let visibleRows = 0;

		rows.forEach((row) => {
			const values = Object.fromEntries(
				[...filters.keys()].map((field) => [
					field,
					row.getAttribute(`data-filter-${field}`) ?? '',
				]),
			);
			const isVisible = matchesFilterValues(values, [...filters.values()]);

			row.hidden = !isVisible;
			visibleRows += Number(isVisible);
		});

		if (emptyState) {
			emptyState.hidden = visibleRows > 0;
		}
	}

	document.querySelectorAll('[data-filter-trigger]').forEach((trigger) => {
		trigger.addEventListener('click', () => {
			if (activeFilterTrigger === trigger) {
				closeFilterMenu();
				return;
			}

			activeFilterTrigger?.setAttribute('aria-expanded', 'false');
			activeFilterTrigger = trigger;
			activeFilterTrigger.setAttribute('aria-expanded', 'true');
			filterTitle.textContent = trigger.dataset.filterTitle;

			const template = document.querySelector(`#filter-${trigger.dataset.filterTrigger}`);
			if (!template) {
				return;
			}

			filterContent.replaceChildren(template.content.cloneNode(true));
			filterContent.querySelectorAll('[data-filter-field]').forEach((control) => {
				const filter = filters.get(control.dataset.filterField);
				if (filter) {
					control.value = filter.value;
				}
			});
			filterContent.querySelectorAll('[data-filter-operator-for]').forEach((control) => {
				const filter = filters.get(control.dataset.filterOperatorFor);
				if (filter) {
					control.value = filter.operator;
				}
			});

			filterMenu.classList.remove('hidden');
			const bounds = trigger.getBoundingClientRect();
			const left = Math.min(Math.max(12, bounds.left), window.innerWidth - filterMenu.offsetWidth - 12);
			const top = Math.min(bounds.bottom, window.innerHeight - filterMenu.offsetHeight - 12);
			filterMenu.style.left = `${left}px`;
			filterMenu.style.top = `${Math.max(12, top)}px`;
		});
	});

	applyButton.addEventListener('click', () => {
		const controls = [...filterContent.querySelectorAll('[data-filter-field]')];
		const fields = new Set(controls.map((control) => control.dataset.filterField));

		fields.forEach((field) => filters.delete(field));
		controls.forEach((control) => {
			const value = control.value.trim();
			if (value === '') {
				return;
			}

			const operator = [...filterContent.querySelectorAll('[data-filter-operator-for]')]
				.find((candidate) => candidate.dataset.filterOperatorFor === control.dataset.filterField)?.value ?? 'contains';

			filters.set(control.dataset.filterField, {
				field: control.dataset.filterField,
				operator,
				type: control.dataset.filterType ?? 'text',
				value,
			});
		});

		applyFilters();
		closeFilterMenu();
	});

	clearButton.addEventListener('click', () => {
		filters.clear();
		applyFilters();
		closeFilterMenu();
	});

	filterMenu.querySelector('[data-filter-close]')?.addEventListener('click', closeFilterMenu);
	document.addEventListener('click', (event) => {
		if (!filterMenu.contains(event.target) && !event.target.closest('[data-filter-trigger]')) {
			closeFilterMenu();
		}
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeFilterMenu();
		}
	});
	window.addEventListener('resize', closeFilterMenu);
	table.addEventListener('scroll', closeFilterMenu);
}
