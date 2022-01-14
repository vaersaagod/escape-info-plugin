<?php

namespace escape\info\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class AdspaceSelectFieldBundle extends AssetBundle
{

    public function init()
    {

        $this->sourcePath = '@escapeinfoplugin/web/assets/dist/cp';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'adspace-select-field.js',
        ];

        $this->css = [
            'adspace-select-field.css',
        ];

        parent::init();
    }
    
}
