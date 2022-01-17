<?php

namespace escape\info\helpers;

use Craft;
use craft\helpers\UrlHelper;
use escape\info\EscapeInfo;

class AdspaceHelper
{

    /** @var string */
    const CONTAINER_SHOUTOUT = 'shoutout';

    /** @var string */
    const CONTAINER_BANNER = 'banner';

    /** @var string */
    const CONTAINER_POPUP = 'popup';

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    public static function getUrl(string $path, array $params = []): string
    {
        $settings = EscapeInfo::getInstance()->getSettings();
        $playgroundUrl = \rtrim($settings->escapeInfoUrl, '/');
        return UrlHelper::url($playgroundUrl . "/$path", $params);
    }
}
