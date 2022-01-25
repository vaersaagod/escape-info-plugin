(() => {

    const STORAGE_KEY_DISMISSED_ADS = 'playground-dismissed-popups';
    const STORAGE_KEY_HAS_SEEN_POPUP = 'playground-has-seen-popup';

    if (window.sessionStorage && !!window.sessionStorage.getItem(STORAGE_KEY_HAS_SEEN_POPUP)) {
        return;
    }

    const focusableQuery = 'a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])';

    let isVisible = false;
    let ad;
    let activeElementBeforeOpen;
    let popup;
    let inner;
    let iframe;
    let closeBtn;

    const getDismissedAds = () => window.localStorage ? (window.localStorage.getItem(STORAGE_KEY_DISMISSED_ADS) || '').split(',').filter(value => !!value) : [];

    const dismissAd = key => {
        if (!window.localStorage) {
            return;
        }
        const dismissedAds = getDismissedAds();
        if (dismissedAds.indexOf(key) > -1) {
            return;
        }
        window.localStorage.setItem(STORAGE_KEY_DISMISSED_ADS, dismissedAds.concat(key).join(','));
    };

    const scalePopup = () => {
        if (!isVisible) {
            return;
        }
        const { width, height } = inner.getBoundingClientRect();
        const scale = width / 400;
        iframe.style.transformOrigin = 'left top';
        iframe.style.transform = `scale(${scale})`;
        iframe.style.width = `${Math.round(width * (1 / scale))}px`;
        iframe.style.height = `${Math.round(height * (1 / scale))}px`;
    };

    const positionPopup = position => {
        // TODO
        // if (position === 'bottomLeft') {
        //     popup.classList.remove('tw-justify-center');
        //     popup.classList.remove('tw-items-center');
        //     popup.classList.add('tw-justify-start');
        //     popup.classList.add('tw-items-end');
        // }
    };

    const dismiss = () => {
        if (!isVisible) {
            return;
        }
        isVisible = false;
        popup.classList.remove('is-visible');
        setTimeout(() => {
            popup.classList.add('tw-hidden');
        }, 500);
        if (activeElementBeforeOpen) {
            activeElementBeforeOpen.focus();
        } else {
            try {
                document.body.querySelector(focusableQuery).focus();
            } catch (error) {}
        }
        dismissAd(`${ad.uid}:${ad.siteUid}`);
    };

    const reveal = () => {
        if (isVisible) {
            return;
        }
        activeElementBeforeOpen = document.activeElement || null;
        isVisible = true;
        popup.classList.remove('tw-hidden');
        closeBtn.focus();
        closeBtn.addEventListener('click', dismiss);
        setTimeout(() => {
            popup.classList.add('is-visible');
        }, 0);
    };

    const onResize = () => {
        scalePopup();
    };

    const onLoad = () => {
        reveal();
        //positionPopup(ad.position || 'center');
        scalePopup();
        iframe.removeEventListener('load', onLoad);
        if (window.sessionStorage) {
            window.sessionStorage.setItem(STORAGE_KEY_HAS_SEEN_POPUP, true);
        }
    };

    const onBodyClick = e => {
        if (!isVisible) {
            return;
        }
        const {target} = e;
        if (target === closeBtn || target === inner || closeBtn.contains(target) || inner.contains(target)) {
            return;
        }
        dismiss();
    };

    const onBodyFocus = e => {
        if (!isVisible || inner.contains(e.target)) {
            return;
        }
        closeBtn.focus();
    };

    const onBodyKeyUp = e => {
        if (!isVisible) {
            return;
        }
        if (e.key === 'Escape' || e.keyCode === 27) {
            dismiss();
        }
    };

    const createPopup = (placeholderNode, html) => {

        const node = document.createElement('div');
        node.innerHTML = html;
        popup = node.firstElementChild;

        // Figure out if there's a viable ad to display
        const allAds = JSON.parse(popup.dataset.ads) || [];
        const dismissedAds = getDismissedAds();

        for (let i = 0; i < allAds.length; i += 1) {
            const key = `${allAds[i].uid}:${allAds[i].siteUid}`;
            if (dismissedAds.indexOf(key) === -1) {
                ad = allAds[i];
                break;
            }
        }

        if (!ad) {
            return;
        }

        placeholderNode.replaceWith(popup);
        inner = popup.firstElementChild;
        iframe = inner.querySelector('iframe')
        closeBtn = popup.querySelector('[data-playground-close]');

        window.addEventListener('resize', onResize);
        document.body.addEventListener('click', onBodyClick);
        document.body.addEventListener('focusin', onBodyFocus);
        document.body.addEventListener('keyup', onBodyKeyUp);

        iframe.addEventListener('load', onLoad);
        iframe.setAttribute('src', ad.url);

    };

    const init = () => {

        let placeholderNode;
        let placeholderData;

        try {
            const iterator = document.createNodeIterator(document.body, NodeFilter.SHOW_COMMENT, () => NodeFilter.FILTER_ACCEPT, false);
            while (placeholderNode = iterator.nextNode()) {
                const text = placeholderNode.nodeValue.toString().trim();
                if (text.startsWith('playground-popup:')) {
                    placeholderData = JSON.parse(text.split('playground-popup:')[1] || '');
                    break;
                }
            }

        } catch (error) {
            console.error(error);
        }

        if (!placeholderData) {
            return;
        }

        const request = new XMLHttpRequest();
        request.open('POST', '/playground/get-popup-html', true);
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
                createPopup(placeholderNode, html);
            } catch (error) {
                console.error(error);
            }
        }

        request.send(JSON.stringify(placeholderData));

    };

    window.addEventListener('DOMContentLoaded', init);

})();
