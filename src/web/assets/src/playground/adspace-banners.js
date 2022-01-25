(() => {

    const createBanner = (placeholderNode, html) => {
        const node = document.createElement('div');
        node.innerHTML = html;
        placeholderNode.replaceWith(node.firstElementChild);// node.innerHTML = html;
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

})();
