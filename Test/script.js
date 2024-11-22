window.addEventListener('load', () => {
	const slider1 = new DoubleRangeSlider('#rangeWrapper1');
	const slider2 = new DoubleRangeSlider('#rangeWrapper2');
	const slider3 = new DoubleRangeSlider('#rangeWrapper3');
	const slider4 = new DoubleRangeSlider('#rangeWrapper4');

	slider1.suscribe_change((values) => {
		array_values = [values, slider2.getValues(), slider3.getValues(), slider4.getValues()]
		console.log(array_values);
		console.log("------------------");
	})

	slider2.suscribe_change((values) => {
		array_values = [slider1.getValues(), values, slider3.getValues(), slider4.getValues()]
		console.log(array_values);
		console.log("------------------");
	})

	slider3.suscribe_change((values) => {
		array_values = [slider1.getValues(), slider2.getValues(), values, slider4.getValues()]
		console.log(array_values);
		console.log("------------------");
	})

	slider4.suscribe_change((values) => {
		array_values = [slider1.getValues(), slider2.getValues(), slider3.getValues(), values]
		console.log(array_values);
		console.log("------------------");
	})
})