import smoothScroll from 'smooth-scroll';

smoothScroll.init({
    // selector: '[data-scroll]', // Selector for links (must be a class, ID, data attribute, or element tag)
    // selectorHeader: null, // Selector for fixed headers (must be a valid CSS selector) [optional]
    speed: 600, // Integer. How fast to complete the scroll in milliseconds
    easing: 'easeInOutCubic', // Easing pattern to use
    offset: 40, // Integer. How far to offset the scrolling anchor location in pixels
    // callback: function ( anchor, toggle ) {} // Function to run after scrolling
});

function getHeight(elem) {
    return Math.max(elem.scrollHeight, elem.offsetHeight, elem.clientHeight);
}

let height = 20,
    elems = document.querySelectorAll('[data-display="equal"]');

if (elems) {
    for (let i = 0; i < elems.length; i++) {
        let hTmp = getHeight(elems[i]);

        if (hTmp > height) {
            height = hTmp;
        }
    }

    for (let i = 0; i < elems.length; i++) {
        elems[i].style.height = `${height}px`;
    }
}
