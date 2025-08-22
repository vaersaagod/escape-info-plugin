<?php

namespace escape\info\console\controllers;

use craft\console\Controller;

use escape\info\EscapeInfo;

use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * Ads controller
 */
class AdsController extends Controller
{

    /**
     * @return int
     */
    public function actionUpdateAds(): int
    {
        $then = time();
        $result = EscapeInfo::getInstance()->adspace->updateAdsRepository();
        $duration = time() - $then;
        if ($result === false) {
            $this->stdout("Failed to update the ads repository in $duration seconds." . PHP_EOL, BaseConsole::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->stdout("Saved $result ads from Playground in $duration seconds." . PHP_EOL, BaseConsole::FG_GREEN);
        return ExitCode::OK;
    }
}
