import Flickity from 'flickity';

(() => {

    const STORAGE_KEY_SEEN_ADS = 'playground-seen-shoutouts';

    const button = document.getElementById('shoutouts-button');

    if (!button) {
        return;
    }

    let popup;
    let isOpen = false;
    let iframeLoaded = false;
    let scrollPosWhenOpened = null;
    let flkty;
    let adsContainer;
    let firstIframe;
    let afterCloseTimeout = null;
    let numAds;
    let scrollHandler;

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

    const getSeenAds = () => window.localStorage ? (window.localStorage.getItem(STORAGE_KEY_SEEN_ADS) || '').split(',').filter(value => !!value) : [];

    const seenAd = key => {
        if (!window.localStorage) {
            return;
        }
        const seenAds = getSeenAds();
        if (seenAds.indexOf(key) > -1) {
            return;
        }
        window.localStorage.setItem(STORAGE_KEY_SEEN_ADS, seenAds.concat(key).join(','));
    };

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
        if (flkty) {
            return;
        }
        flkty = new Flickity(adsContainer, {
            contain: true,
            dragThreshold: 15,
            cellAlign: 'left',
            prevNextButtons: false,
            pageDots: numAds > 1,
            draggable: false,
            freeScroll: false,
            freeScrollFriction: 0.045,
            wrapAround: true,
            autoPlay: 3000,
            adaptiveHeight: false,
            setGallerySize: false,
            on: {
                select: function () {
                    if (!isOpen) {
                        return;
                    }
                    seenAd(this.selectedCell.element.dataset.playgroundAd);
                }
            }
        });
        flkty.pausePlayer();
    };

    const positionPopup = () => {
        if (!isOpen) {
            return;
        }
        adsContainer.style.left = '0px';
        const {left, width} = popup.getBoundingClientRect();
        const {height} = adsContainer.getBoundingClientRect();
        const viewW = viewportWidth();
        const leftBound = (left + width + 20);
        const rightBound = left;
        if (leftBound > viewW) {
            adsContainer.style.left = `-${Math.round(leftBound - viewW)}px`;
        } else if (rightBound < 20) {
            adsContainer.style.left = `${Math.round(20 - rightBound)}px`;
        }
        // Scale
        const scale = width / 400;
        const adFrames = popup.querySelectorAll('[data-playground-frame]')
        adFrames.forEach(frame => {
            frame.style.transformOrigin = 'left top';
            frame.style.transform = `scale(${scale})`;
            frame.style.width = `${Math.round(width * (1 / scale))}px`;
            frame.style.height = `${Math.round(height * (1 / scale))}px`;
        });
    };

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

    const createPopup = html => {

        const node = document.createElement('div');
        node.innerHTML = html;
        popup = node.firstElementChild;

        adsContainer = popup.querySelector('[data-playground-ads]');
        firstIframe = popup.querySelector('iframe');

        if (!adsContainer || !firstIframe) {
            return;
        }

        button.parentNode.appendChild(popup);
        button.setAttribute('aria-expanded', 'false');
        button.style.opacity = 1;
        button.style.pointerEvents = 'auto';

        numAds = popup.querySelectorAll('iframe').length;

        button.addEventListener('click', onClick);
        button.addEventListener('mouseenter', onMouseEnter);
        window.addEventListener('resize', onResize);
        window.addEventListener('scroll', onScroll);
        document.body.addEventListener('click', onBodyClick);

        firstIframe.addEventListener('lazyloaded', onIframeLoad);

        const dot = button.querySelector('[data-playground-dot]');
        if (dot) {
            const adKeys = [...popup.querySelectorAll('[data-playground-ad]')].map(ad => ad.dataset.playgroundAd);
            const seenAds = getSeenAds();
            const hasUnseenAds = !!adKeys.filter(key => seenAds.indexOf(key) === -1).length;
            if (hasUnseenAds) {
                dot.classList.remove('tw-hidden');
            }
        }

        createFlickity();

    };

    const init = () => {

        const selectedAds = JSON.parse(button.dataset.playgroundAds);
        if (!selectedAds) {
            return;
        }

        const request = new XMLHttpRequest();
        request.open('POST', '/playground/get-shoutouts-html', true);
        request.setRequestHeader('Content-Type', 'application/json');
        request.setRequestHeader('Accept', 'application/json');

        request.onreadystatechange = function () {
            if (this.readyState != 4 || this.status !== 200 || !this.responseText) {
                return;
            }
            try {
                const data = JSON.parse(this.responseText);
                const { html } = data || {};
                if (!html) {
                    return;
                }
                createPopup(html);
            } catch (error) {
                console.error(error);
            }
        }

        request.send(JSON.stringify({ ads: selectedAds }));

    };

    window.addEventListener('DOMContentLoaded', init);

})();
