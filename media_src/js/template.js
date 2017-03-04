smoothScroll.init({
	// selector: '[data-scroll]', // Selector for links (must be a class, ID, data attribute, or element tag)
	// selectorHeader: null, // Selector for fixed headers (must be a valid CSS selector) [optional]
	speed: 600, // Integer. How fast to complete the scroll in milliseconds
	easing: 'easeInOutCubic', // Easing pattern to use
	offset: 40, // Integer. How far to offset the scrolling anchor location in pixels
	// callback: function ( anchor, toggle ) {} // Function to run after scrolling
});

var getHeight = function (elem) {
	return Math.max(elem.scrollHeight, elem.offsetHeight, elem.clientHeight);
};

var height = 20;
var elems = document.querySelectorAll('[data-display="equal"]');

if (elems) {
	for (var i = 0; i < elems.length; i++) {
		var hTmp = getHeight(elems[i]);
		if (hTmp > height)
			height = hTmp;
	}

	for (var i = 0; i < elems.length; i++) {
		elems[i].style.height = height + 'px';
	}
}
