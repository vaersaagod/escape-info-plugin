<?php

namespace escape\info\web\twig\variables;

use Craft;

use craft\helpers\Json;
use craft\helpers\Template;
use escape\info\EscapeInfo;

class EscapeInfoVariable
{

    /**
     * @param array[]|null $selectedAds
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function renderAdspaceShoutouts(?array $selectedAds = null): string
    {
        return EscapeInfo::getInstance()->adspace->renderShoutouts($selectedAds);
    }

    /**
     * @param string $adUid
     * @param string $siteUid
     * @param array $attributes
     * @return string
     */
    public function renderAdspaceBannerPlaceholder(string $adUid, string $siteUid, array $attributes = []): string
    {
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
