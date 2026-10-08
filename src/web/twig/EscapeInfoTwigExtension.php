<?php

namespace escape\info\web\twig;

use Craft;
use craft\helpers\Json;

use escape\info\assetbundles\AdspacePopupBundle;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EscapeInfoTwigExtension extends AbstractExtension
{

    /**
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'escape-info';
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderAdspaceBannerPlaceholder', [$this, 'renderAdspaceBannerPlaceholderFunction'], ['is_safe' => ['html']]),
            new TwigFunction('renderAdspacePopupPlaceholder', [$this, 'renderAdspacePopupPlaceholderFunction'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $adUid
     * @param string $siteUid
     * @param array $attributes
     * @return string
     */
    public function renderAdspaceBannerPlaceholderFunction(string $adUid, string $siteUid, array $attributes = []): string
    {
        // Render a placeholder as an HTML comment. It's signed, so that only placeholders rendered here are replaced
        // with a banner. JSON_HEX_TAG keeps a "-->" in an attribute from closing the comment
        $data = Json::encode([
            'ad' => [
                'uid' => $adUid,
                'siteUid' => $siteUid,
            ],
            'attributes' => $attributes,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
        $data = Craft::$app->getSecurity()->hashData($data);
        return "<!-- playground-banner:$data -->";
    }

    /**
     * @param array|null $selectedAds
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function renderAdspacePopupPlaceholderFunction(?array $selectedAds = null): string
    {
        return '';

//        if (empty($selectedAds)) {
//            return '';
//        }
//        // Render a placeholder as an HTML comment
//        $data = Json::encode([
//            'ads' => array_map(function (array $ad) {
//                return [
//                    'uid' => $ad['uid'],
//                    'siteUid' => $ad['siteUid'],
//                    'position' => $ad['position'] ?? 'center',
//                ];
//            }, $selectedAds),
//        ]);
//        Craft::$app->getView()->registerAssetBundle(AdspacePopupBundle::class);
//        return "<!-- playground-popup:$data -->";
    }

}
