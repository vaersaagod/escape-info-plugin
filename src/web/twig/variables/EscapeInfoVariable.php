<?php

namespace escape\info\web\twig\variables;

use Craft;

use craft\helpers\Json;
use craft\helpers\Template;
use craft\web\View;

use escape\info\assetbundles\AdspacePopupBundle;
use escape\info\assetbundles\AdspaceShoutoutsBundle;
use escape\info\EscapeInfo;
use escape\info\models\Settings;

class EscapeInfoVariable
{

    /**
     * @param array|null $selectedAds
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function renderAdspaceShoutoutsButton(?array $selectedAds = null): string
    {
        return '';

//        if (empty($selectedAds)) {
//            return '';
//        }
//        // If sandbox mode, render nothing for anonymous users
//        $settings = EscapeInfo::getInstance()->getSettings();
//        if ($settings->sandboxMode && !Craft::$app->getUser()->getId()) {
//            return '';
//        }
//        Craft::$app->getView()->registerAssetBundle(AdspaceShoutoutsBundle::class);
//        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-shoutouts-button.twig', [
//            'ads' => $selectedAds,
//        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return EscapeInfo::getInstance()->getSettings();
    }

}
