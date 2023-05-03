(() => {

    let banners = [];

    const onResize = () => {
        banners.forEach(banner => {
            const iframe = banner.querySelector('iframe');
            const { width: iframeWidth } = iframe.getBoundingClientRect();
            let ratios = null;
            try {
                ratios = JSON.parse(iframe.dataset.ratios);
            } catch (error) {}
            if (!ratios) {
                return;
            }
            const breakpoints = Object
                .keys(ratios)
                .reduce((carry, breakpoint) => breakpoint !== 'default' ? carry.concat(breakpoint) : carry, [])
                .sort()
                .reverse();
            let currentBreakpoint = 'default';
            for (let i = 0; i < breakpoints.length; ++i) {
                const pixels = parseInt(breakpoints[i].replace('px', ''), 10);
                if (pixels <= iframeWidth) {
                    currentBreakpoint = breakpoints[i];
                    break;
                }
            }
            const ratio = ratios[currentBreakpoint] || null;
            if (ratio) {
                iframe.style.aspectRatio = ratio;
            }
        });
    };

    const createBanner = (placeholderNode, html) => {
        const node = document.createElement('div');
        node.innerHTML = html;
        const banner = node.firstElementChild;
        placeholderNode.replaceWith(banner);
        banners.push(banner);
        onResize();
    };

    const init = () => {

        let bannerPlaceholders = [];

        try {

            const iterator = document.createNodeIterator(document.body, NodeFilter.SHOW_COMMENT, () => NodeFilter.FILTER_ACCEPT, false);

            let placeholderNode;
            let placeholderData;

            while (placeholderNode = iterator.nextNode()) {
                const text = placeholderNode.nodeValue.toString().trim();
                if (text.startsWith('playground-banner:')) {
                    placeholderData = JSON.parse(text.split('playground-banner:')[1] || '');
                    bannerPlaceholders.push({
                        node: placeholderNode,
                        data: placeholderData
                    });
                }
            }

        } catch (error) {
            console.error(error);
        }

        bannerPlaceholders.forEach(bannerPlaceholder => {

            const { ad, attributes } = bannerPlaceholder.data || {};
            const { uid, siteUid } = ad || {};

            if (!uid || !siteUid) {
                return;
            }

            const request = new XMLHttpRequest();
            request.open('POST', '/playground/get-banner-html', true);
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
                    createBanner(bannerPlaceholder.node, html);
                } catch (error) {
                    console.error(error);
                }
            }

            request.send(JSON.stringify({
                uid, siteUid, attributes
            }));

        });

    };

    window.addEventListener('DOMContentLoaded', init);
    window.addEventListener('resize', onResize);

})();
