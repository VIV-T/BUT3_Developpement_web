window.addEventListener('load', () => {
	const slider1 = new DoubleRangeSlider('#rangeWrapper1');
	const slider2 = new DoubleRangeSlider('#rangeWrapper2');
	const slider3 = new DoubleRangeSlider('#rangeWrapper3');
	const slider4 = new DoubleRangeSlider('#rangeWrapper4');

	slider1.suscribe((values) => {
		console.log(values);
	})
})