(() => {

    // TODO check sessionStorage for dismissed popup

    const popup = document.getElementById('adspace-popup');
    const inner = popup ? popup.firstElementChild : null;

    if (!popup || !inner) {
        return;
    }

    const ads = JSON.parse(popup.dataset.ads) || [];

    // Get ad to render
    let ad = null;

    for (let i = 0; i < ads.length; i += 1) {
        // TODO check localStorage for seen ads
        ad = ads[i];
        break;
    }

    if (!ad) {
        return;
    }

    const iframe = inner.querySelector('iframe');
    if (!iframe) {
        return;
    }

    const onLoad = () => {
        console.log('I loaded!');
    };

    iframe.addEventListener('load', onLoad);
    iframe.setAttribute('src', ad.url);

})();
