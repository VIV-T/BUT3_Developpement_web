class DoubleRangeSlider {
	constructor(selector, min = 0, max = 100, step = 1) {
		const parentElement = document.querySelector(selector);
		if (!parentElement) {
			throw new Error(`Aucun élément trouvé avec le selecteur "${id}"`);
		}

		const container = document.createElement('span');
		container.className = 'multi-range';

		const lowerInput = document.createElement('input');
		lowerInput.type = 'range';
		lowerInput.min = `${min}`;
		lowerInput.max = `${max}`;
		lowerInput.step = `${step}`;
		lowerInput.value = `${min}`;
		lowerInput.id = 'lower';
		this.lowerInput = lowerInput;

		const upperInput = document.createElement('input');
		upperInput.type = 'range';
		upperInput.min = `${min}`;
		upperInput.max = `${max}`;
		upperInput.step = `${step}`;
		upperInput.value = `${max}`;
		upperInput.id = 'upper';
		this.upperInput = upperInput;

		container.appendChild(lowerInput);
		container.appendChild(upperInput);

		parentElement.appendChild(container);

		let lowerVal = parseInt(lowerInput.value);
		let upperVal = parseInt(upperInput.value);

		upperInput.addEventListener('input', () => {
			lowerVal = parseInt(lowerInput.value);
			upperVal = parseInt(upperInput.value);

			if (upperVal < lowerVal + step) {
				lowerInput.value = `${upperVal - step}`;

				if (lowerVal === lowerInput.min) {
					upperInput.value = `${step}`;
				}
			}
		});

		lowerInput.addEventListener('input', () => {
			lowerVal = parseInt(lowerInput.value);
			upperVal = parseInt(upperInput.value);

			if (lowerVal > upperVal - step) {
				upperInput.value = `${lowerVal + step}`;

				if (upperVal === upperInput.max) {
					lowerInput.value = `${parseInt(upperInput.max) - step}`;
				}
			}
		});
	}

	suscribe(callback) {
		this.upperInput.addEventListener('input', () => {
			callback(this.getValues());
		});
		this.lowerInput.addEventListener('input', () => {
			callback(this.getValues());
		});
	}

	getValues() {
		return [
			parseInt(this.lowerInput.value),
			parseInt(this.upperInput.value)
		];
	}
}