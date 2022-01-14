(() => {

    const button = document.getElementById('shoutouts-button');

    if (!button) {
        return;
    }

    const popup = button.nextElementSibling;

    let isOpen = false;

    const positionPopup = () => {
        if (!isOpen) {
            return;
        }
        // TODO make sure the popup is within the viewport
    };

    const open = () => {
        if (isOpen) {
            return;
        }
        isOpen = true;
        button.setAttribute('aria-expanded', 'true');
        popup.classList.remove('hidden');
        positionPopup();
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
    window.addEventListener('resize', onResize);
    document.body.addEventListener('click', onBodyClick);

})();
