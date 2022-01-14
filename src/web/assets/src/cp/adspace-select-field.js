window.EscapeInfoAdspaceSelectField = function (id, config) {

    var $el = $(id).eq(0);
    var $sourceSelect = $el.find('select').eq(0);
    var $feedInput = $el.find('input.selectize-text').eq(0);
    var $hiddenInput = $el.find('input[type="hidden"]').eq(0);

    var ads = JSON.parse(config.ads);

    var initialSelectedElements = $hiddenInput.val() ? JSON.parse($hiddenInput.val()) : [];

    $feedInput.selectize({
        create: false,
        placeholder: Craft.t('site', 'Search for and select ads'),
        sortField: 'title',
        valueField: 'uid',
        labelField: 'title',
        searchField: ['title'],
        plugins: ["remove_button"],
        items: initialSelectedElements.map(function (element) {
            return element.uid;
        }),
        options: initialSelectedElements
    });

    var feedElements = null;
    var selectize = $feedInput.get(0).selectize;

    function fetchOptions() {
        selectize.clearOptions();
        var site = $sourceSelect.val();
        feedElements = [];
        for (var i = 0; i < ads.length; ++i) {
            if (ads[i].site !== site) {
                continue;
            }
            feedElements.push(ads[i]);
        }
        feedElements.forEach(ad => {
            selectize.addOption(ad);
        });
        selectize.refreshOptions();
        // var url = Craft.getActionUrl('playground/feeds/get-feed', {
        //     source: $sourceSelect.val(),
        //     endpoint: endpoint + '.json'
        // });
        // $.ajax({
        //     url: url,
        //     success: function (res) {
        //         feedElements = res;
        //         res.forEach(function (obj) {
        //             selectize.addOption(obj);
        //         });
        //         selectize.refreshOptions();
        //     }
        // });
    }

    selectize.on('focus', function () {
        // if (!feedElements) {
        //     fetchOptions();
        // }
        fetchOptions();
    });

    var prevSelectedElements = initialSelectedElements;

    selectize.on('change', function () {
        var selectedUids = this.items;
        var prevSelectedElementsByUid = prevSelectedElements.reduce(function (carry, element) {
            carry[element.uid] = element;
            return carry;
        }, {});
        var newSelectedElements = (feedElements || initialSelectedElements).reduce(function (carry, element) {
            var uid = element.uid;
            if (selectedUids.indexOf(uid) === -1) {
                return carry;
            }
            if (prevSelectedElementsByUid[uid] && !!prevSelectedElementsByUid[uid].uid) {
                element = prevSelectedElementsByUid[uid];
            }
            element.site = element.site || $sourceSelect.val();
            return carry.concat(element);
        }, []);

        var newSelectedElementUids = newSelectedElements.map(function (element) {
            return element.uid;
        });

        prevSelectedElements.forEach(function (element) {
            var uid = element.uid;
            if (selectedUids.indexOf(uid) === -1 || newSelectedElementUids.indexOf(uid) !== -1) {
                return;
            }
            newSelectedElements.push(element);
        });

        $hiddenInput.val(JSON.stringify(newSelectedElements));

        prevSelectedElements = newSelectedElements;

        if (window.draftEditor) {
            window.draftEditor.checkForm();
        }
    });

    $sourceSelect.on('change', function () {
        fetchOptions();
    });

};
