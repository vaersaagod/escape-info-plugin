import Flickity from 'flickity';

(() => {

    const button = document.getElementById('shoutouts-button');
    
    let flkty;

    if (!button) {
        return;
    }

    const popup = button.nextElementSibling;
    const adsContainer = popup.querySelector('[data-ads]');

    let isOpen = false;

    const vw = () => Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);

    const vh = () => Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

    const loadIframes = () => {
        popup.querySelectorAll('iframe').forEach(iframe => {
            iframe.setAttribute('src', iframe.dataset.src);
        });
    };

    const createFlickity = () => {
        if (flkty) {
            return;
        }
        flkty = new Flickity(adsContainer, {
            contain: true,
            dragThreshold: 15,
            cellAlign: 'left',
            prevNextButtons: false,
            pageDots: true,
            draggable: false,
            freeScroll: false,
            freeScrollFriction: 0.045,
            wrapAround: true,
            autoPlay: 3000
        });
        loadIframes();
    };

    const positionPopup = () => {
        if (!isOpen) {
            return;
        }
        adsContainer.style.left = '0px';
        const popupRect = popup.getBoundingClientRect();
        const { left, width } = popupRect;
        const viewportWidth = vw();
        const leftBound = left + width;
        const overX = leftBound - viewportWidth;
        if (overX) {
            adsContainer.style.left = `-${overX}px`;
        }
    };

    const open = () => {
        if (isOpen) {
            return;
        }
        isOpen = true;
        button.setAttribute('aria-expanded', 'true');
        popup.classList.remove('hidden');
        positionPopup();
        if (!flkty) {
            createFlickity();
        }
    };

    const close = () => {
        if (!isOpen) {
            return;
        }
        isOpen = false;
        button.setAttribute('aria-expanded', 'false');
        popup.classList.add('hidden');
    };

    const toggle = () => {
        if (isOpen) {
            close();
        } else {
            open();
        }
    };

    const onClick = e => {
        toggle();
    };

    const onBodyClick = e => {
        if (!isOpen) {
            return;
        }
        const { target } = e;
        if (target === button || target === popup || button.contains(target) || popup.contains(target)) {
            return;
        }
        close();
    };

    const onResize = () => {
        positionPopup();
    };

    button.addEventListener('click', onClick);
    //button.addEventListener('mouseenter', createFlickity);
    window.addEventListener('resize', onResize);
    document.body.addEventListener('click', onBodyClick);

})();
