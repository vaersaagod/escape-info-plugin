<?php

namespace escape\info\web\twig\variables;

use Craft;

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
    public function renderShoutoutsButton(?array $selectedAds = null)
    {
        return EscapeInfo::getInstance()->adspace->renderShoutoutsButton($selectedAds);
    }

}
