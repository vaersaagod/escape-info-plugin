window.EscapeInfoAdspaceSelectField = function (id, config) {

    var $el = $(id).eq(0);
    var $sourceSelect = $el.find('select').eq(0);
    var $feedInput = $el.find('input.selectize-text').eq(0);
    var $hiddenInput = $el.find('input[type="hidden"]').eq(0);

    var ads = JSON.parse(config.ads);
    var sites = JSON.parse(config.sites);

    var limit = config.limit || null;
    var sitesByUid = sites.reduce(function (carry, site) {
        carry[site.uid] = site;
        return carry;
    }, {});

    function createKey(obj) {
        obj.key = obj.uid + ':' + obj.siteUid;
        return obj;
    }

    var initialSelectedElements = ($hiddenInput.val() ? JSON.parse($hiddenInput.val()) : []).map(createKey);

    var options = {
        create: false,
        placeholder: Craft.t('site', 'Search for and select ads'),
        sortField: 'title',
        labelField: 'title',
        valueField: 'key',
        searchField: ['title'],
        plugins: ["remove_button"],
        render: {
            item: function (data) {
                var site = sitesByUid[data.siteUid];
                return '<div class="item active" data-value="' + data.key + '"><span class="status ' + data.status + '"></span><span>' + (data.title + ' (' + site.handle + ')') + '</span></div>';
            },
            option: function (data) {
                var site = sitesByUid[data.siteUid];
                return '<div class="option" data-value="' + data.key + '"><span class="status ' + data.status + '"></span><span>' + (data.title + ' (' + site.handle + ')') + '</span></div>';
            }
        },
        items: initialSelectedElements.map(function (element) {
            return element.key;
        }),
        options: initialSelectedElements
    };

    if (limit) {
        options.maxItems = limit;
    }

    $feedInput.selectize(options);

    var feedElements = ads;
    var selectize = $feedInput.get(0).selectize;

    function fetchOptions() {

        selectize.clearOptions();

        if ($sourceSelect.length) {
            var siteUid = $sourceSelect.val();
            feedElements = [];
            for (var i = 0; i < ads.length; ++i) {
                if (ads[i].siteUid !== siteUid) {
                    continue;
                }
                feedElements.push(ads[i]);
            }
        }

        feedElements.forEach(ad => {
            selectize.addOption(createKey(ad));
        });

        selectize.refreshOptions();
    }

    selectize.on('focus', function () {
        fetchOptions();
    });

    var prevSelectedElements = initialSelectedElements;

    selectize.on('change', function () {

        var selectedKeys = this.items;

        var prevSelectedElementsByKey = prevSelectedElements.reduce(function (carry, element) {
            carry[element.key] = element;
            return carry;
        }, {});

        var newSelectedElements = (feedElements || initialSelectedElements).reduce(function (carry, element) {
            var key = element.key;
            if (selectedKeys.indexOf(key) === -1) {
                return carry;
            }
            if (prevSelectedElementsByKey[key] && !!prevSelectedElementsByKey[key].key) {
                element = prevSelectedElementsByKey[key];
            }
            return carry.concat(element);
        }, []);

        var newSelectedElementKeys = newSelectedElements.map(function (element) {
            return element.key;
        });

        prevSelectedElements.forEach(function (element) {
            var key = element.key;
            if (selectedKeys.indexOf(key) === -1 || newSelectedElementKeys.indexOf(key) !== -1) {
                return;
            }
            newSelectedElements.push(element);
        });

        prevSelectedElements = newSelectedElements;

        $hiddenInput.val(JSON.stringify(newSelectedElements));

        if (window.draftEditor) {
            window.draftEditor.checkForm();
        }
    });

    if ($sourceSelect.length) {
        $sourceSelect.on('change', function () {
            fetchOptions();
        });
    }

};
