import SmoothScroll from 'smooth-scroll';

new SmoothScroll('[data-scroll]', {
    header: '[data-scroll-header]',
    speed: 600,
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
