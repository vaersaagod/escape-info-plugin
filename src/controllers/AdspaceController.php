<?php

namespace escape\info\controllers;

use craft\web\Controller;

use escape\info\EscapeInfo;

use yii\web\BadRequestHttpException;
use yii\web\Response;

class AdspaceController extends Controller
{

    /** @var bool */
    public array|int|bool $allowAnonymous = true;

    /** @var bool */
    public $enableCsrfValidation = false;

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionGetShoutoutsHtml(): Response
    {
        $this->requireAcceptsJson();
        $selectedAds = $this->request->getRequiredParam('ads');
        return $this->asJson([
            'html' => \trim(EscapeInfo::getInstance()->adspace->renderShoutoutsPopup($selectedAds)),
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetPopupHtml(): Response
    {
        $this->requireAcceptsJson();
        $selectedAds = $this->request->getRequiredParam('ads');
        return $this->asJson([
            'html' => \trim(EscapeInfo::getInstance()->adspace->renderPopup($selectedAds)),
        ]);
    }
}
