<?php

namespace escape\info\web\twig\variables;

use Craft;
use craft\helpers\Template;

use escape\info\assetbundles\ShoutoutsButtonBundle;
use escape\info\EscapeInfo;

class EscapeInfoVariable
{

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function renderShoutoutsButton()
    {
        Craft::$app->getView()->registerAssetBundle(ShoutoutsButtonBundle::class);
        return Template::raw(EscapeInfo::getInstance()->adspace->renderShoutoutsButton());
    }

}
