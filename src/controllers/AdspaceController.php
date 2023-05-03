<?php

namespace escape\info\controllers;

use Craft;
use craft\helpers\Html;
use craft\web\Controller;

use escape\info\EscapeInfo;

use yii\web\BadRequestHttpException;
use yii\web\Response;

class AdspaceController extends Controller
{

    /** @var bool */
    public $allowAnonymous = true;

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
    public function actionGetBannerHtml(): Response
    {
        $this->requireAcceptsJson();
        $uid = $this->request->getRequiredParam('uid');
        $siteUid = $this->request->getRequiredParam('siteUid');
        $attributes = $this->request->getParam('attributes', []);
        $html = EscapeInfo::getInstance()->adspace->renderBanner($uid, $siteUid);
        if (!empty($attributes)) {
            $html = Html::modifyTagAttributes($html, $attributes);
        }
        // TODO Remove HTML comments (?)
        return $this->asJson([
            'html' => \trim($html),
        ]);
    }

}
