import Flickity from 'flickity';

(() => {

    const button = document.getElementById('shoutouts-button');

    if (!button) {
        return;
    }

    const popup = button.nextElementSibling;

    if (!popup) {
        return;
    }

    const adsContainer = popup.querySelector('[data-playground-ads]');
    const firstIframe = popup.querySelector('iframe');

    if (!adsContainer || !firstIframe) {
        return;
    }

    const numAds = popup.querySelectorAll('iframe').length;

    let isOpen = false;
    let iframeLoaded = false;
    let scrollPosWhenOpened = null;
    let flkty;

    const viewportWidth = () => Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);

    const viewportHeight = () => Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

    const scrollTop = () => (window.pageYOffset || document.documentElement.scrollTop) - (document.documentElement.clientTop || 0);

    const unveilIframe = () => {
        if (!firstIframe || firstIframe.classList.contains('lazyloaded') || firstIframe.classList.contains('lazyloading') || !window.lazySizes) {
            return;
        }
        lazySizes.loader.unveil(firstIframe);
        firstIframe.classList.add('lazyloading');
    };

    const createFlickity = () => {
        if (flkty || numAds <= 1) {
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
            autoPlay: 3000,
            adaptiveHeight: false,
            setGallerySize: false
        });
        flkty.pausePlayer();
    };

    const positionPopup = () => {
        if (!isOpen) {
            return;
        }
        adsContainer.style.left = '0px';
        const {left, width} = popup.getBoundingClientRect();
        const viewW = viewportWidth();
        const leftBound = left + width;
        const overX = leftBound - viewW;
        if (overX) {
            adsContainer.style.left = `-${overX}px`;
        }
    };

    let afterCloseTimeout = null;

    const open = () => {
        if (isOpen) {
            return;
        }
        if (afterCloseTimeout) {
            clearTimeout(afterCloseTimeout);
            afterCloseTimeout = null;
        }
        isOpen = true;
        button.setAttribute('aria-expanded', 'true');
        popup.classList.remove('tw-hidden');
        scrollPosWhenOpened = scrollTop();
        positionPopup();
        popup.classList.add('is-open');
        if (flkty) {
            flkty.resize();
            if (iframeLoaded) {
                flkty.unpausePlayer();
            }
        } else {
            createFlickity();
        }
        unveilIframe();
    };

    const close = () => {
        if (!isOpen) {
            return;
        }
        if (afterCloseTimeout) {
            clearTimeout(afterCloseTimeout);
            afterCloseTimeout = null;
        }
        isOpen = false;
        button.setAttribute('aria-expanded', 'false');
        if (flkty) {
            flkty.pausePlayer();
        }
        popup.classList.remove('is-open');
        afterCloseTimeout = setTimeout(() => {
            afterCloseTimeout = null;
            popup.classList.add('tw-hidden');
        }, 300);
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
        const {target} = e;
        if (target === button || target === popup || button.contains(target) || popup.contains(target)) {
            return;
        }
        close();
    };

    const onResize = () => {
        positionPopup();
    };

    const onMouseEnter = () => {
        unveilIframe();
    };

    const onIframeLoad = () => {
        iframeLoaded = true;
        if (isOpen && flkty) {
            flkty.unpausePlayer();
        }
    };

    let scrollHandler;

    const onScroll = () => {
        if (scrollHandler) {
            clearTimeout(scrollHandler);
            scrollHandler = null;
        }
        if (!isOpen) {
            return;
        }
        scrollHandler = setTimeout(() => {
            scrollHandler = null;
            if (Math.abs(scrollTop() - scrollPosWhenOpened) > 20) {
                close();
            }
        }, 0);
    };

    button.addEventListener('click', onClick);
    button.addEventListener('mouseenter', onMouseEnter);
    window.addEventListener('resize', onResize);
    window.addEventListener('scroll', onScroll);
    document.body.addEventListener('click', onBodyClick);

    firstIframe.addEventListener('lazyloaded', onIframeLoad);

    createFlickity();

})();
