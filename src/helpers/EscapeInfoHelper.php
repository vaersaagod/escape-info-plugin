<?php

namespace escape\info\helpers;

use Craft;
use escape\info\EscapeInfo;

final class EscapeInfoHelper
{
    /** @var bool */
    private static bool $isSandBox;

    /**
     * If sandbox mode, render nothing for anonymous users
     *
     * @return bool
     */
    public static function isSandbox(): bool
    {
        if (!isset(self::$isSandBox)) {
            self::$isSandBox = EscapeInfo::getInstance()->getSettings()->sandboxMode && !Craft::$app->getUser()->getId();
        }
        return self::$isSandBox;
    }
}
