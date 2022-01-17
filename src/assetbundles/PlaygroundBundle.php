<?php

namespace escape\info\assetbundles;

use Craft;
use craft\web\AssetBundle;

class PlaygroundBundle extends AssetBundle
{
    public function init()
    {

        $this->sourcePath = '@escapeinfoplugin/web/assets/dist';

        $this->css = [
            'playground.css',
        ];

        parent::init();
    }
}
