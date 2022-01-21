<?php

namespace escape\info\assetbundles;

use Craft;
use craft\web\AssetBundle;

class AdspacePopupBundle extends AssetBundle
{

    public function init()
    {

        $this->sourcePath = '@escapeinfoplugin/web/assets/dist/adspace';

        $this->depends = [
            PlaygroundBundle::class,
        ];

        $this->js = [
            'adspace-popup.js',
        ];

        parent::init();
    }

}
