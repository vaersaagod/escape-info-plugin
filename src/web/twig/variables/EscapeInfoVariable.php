<?php

namespace escape\info\web\twig\variables;

use Craft;

use craft\helpers\Json;
use craft\helpers\Template;
use craft\web\View;

use escape\info\assetbundles\AdspacePopupBundle;
use escape\info\assetbundles\AdspaceShoutoutsBundle;
use escape\info\EscapeInfo;

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
        if (empty($selectedAds)) {
            return '';
        }
        // If sandbox mode, render nothing for anonymous users
        $settings = EscapeInfo::getInstance()->getSettings();
        if ($settings->sandboxMode && !Craft::$app->getUser()->getId()) {
            return '';
        }
        Craft::$app->getView()->registerAssetBundle(AdspaceShoutoutsBundle::class);
        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-shoutouts-button.twig', [
            'ads' => $selectedAds,
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param array|null $selectedAds
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function renderAdspacePopupPlaceholder(?array $selectedAds = null): string
    {
        if (empty($selectedAds)) {
            return '';
        }
        // If sandbox mode, render nothing for anonymous users
        $settings = EscapeInfo::getInstance()->getSettings();
        if ($settings->sandboxMode && !Craft::$app->getUser()->getId()) {
            return '';
        }
        // Render a placeholder as an HTML comment
        $data = Json::encode([
            'ads' => \array_map(function (array $ad) {
                return [
                    'uid' => $ad['uid'],
                    'siteUid' => $ad['siteUid'],
                    'position' => $ad['position'] ?? 'center',
                ];
            }, $selectedAds),
        ]);
        Craft::$app->getView()->registerAssetBundle(AdspacePopupBundle::class);
        return Template::raw("<!-- playground-popup:$data -->");
    }

    /**
     * @param string $adUid
     * @param string $siteUid
     * @param array $attributes
     * @return string
     */
    public function renderAdspaceBannerPlaceholder(string $adUid, string $siteUid, array $attributes = []): string
    {
        // If sandbox mode, render nothing for anonymous users
        $settings = EscapeInfo::getInstance()->getSettings();
        if ($settings->sandboxMode && !Craft::$app->getUser()->getId()) {
            return '';
        }
        // Render a placeholder as an HTML comment
        $data = Json::encode([
            'ad' => [
                'uid' => $adUid,
                'siteUid' => $siteUid,
            ],
            'attributes' => $attributes,
        ]);
        return Template::raw("<!-- playground-banner:$data -->");
    }

}
