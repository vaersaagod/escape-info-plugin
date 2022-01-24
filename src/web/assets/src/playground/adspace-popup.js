(() => {

    const storageKey = 'playground-dismissed-popups';
    const focusableQuery = 'a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])';
    const popup = document.getElementById('adspace-popup');
    const inner = popup ? popup.firstElementChild : null;

    let isVisible = false;
    let ad = null;
    let activeElementBeforeOpen;

    if (!popup || !inner) {
        return;
    }

    const iframe = inner.querySelector('iframe');
    if (!iframe) {
        return;
    }

    const closeBtn = popup.querySelector('[data-playground-close]');

    const getDismissedAds = () => window.localStorage ? (window.localStorage.getItem(storageKey) || '').split(',').filter(value => !!value) : [];

    const dismissAd = key => {
        if (!window.localStorage) {
            return;
        }
        const dismissedAds = getDismissedAds();
        if (dismissedAds.indexOf(key) > -1) {
            return;
        }
        window.localStorage.setItem(storageKey, dismissedAds.concat(key).join(','));
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

    const positionPopup = () => {};

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

    const ads = JSON.parse(popup.dataset.ads) || [];

    // Get ad to render
    const dismissedAds = getDismissedAds();

    for (let i = 0; i < ads.length; i += 1) {
        const key = `${ads[i].uid}:${ads[i].siteUid}`;
        if (dismissedAds.indexOf(key) === -1) {
            ad = ads[i];
            break;
        }
    }

    if (!ad) {
        return;
    }

    const onResize = () => {
        scalePopup();
    };

    const onLoad = () => {
        reveal();
        positionPopup();
        scalePopup();
        iframe.removeEventListener('load', onLoad);
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

    window.addEventListener('resize', onResize);
    document.body.addEventListener('click', onBodyClick);
    document.body.addEventListener('focusin', onBodyFocus);
    document.body.addEventListener('keyup', onBodyKeyUp);

    iframe.addEventListener('load', onLoad);
    iframe.setAttribute('src', ad.url);

})();
