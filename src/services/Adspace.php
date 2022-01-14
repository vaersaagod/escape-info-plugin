<?php

namespace escape\info\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\web\View;
use escape\info\EscapeInfo;

/**
 *
 */
class Adspace extends Component
{

    /**
     * Get Adspace-enabled Playground sites
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getSites(): array
    {
        $cacheKey = EscapeInfo::getInstance()->getVersion() . '-adspace-sites';
        if ($cachesEnabled = EscapeInfo::getInstance()->getSettings()->cachesEnabled) {
            $cachedData = Craft::$app->getCache()->get($cacheKey);
            if ($cachedData && \is_array($cachedData)) {
                return $cachedData;
            }
        }
        $client = Craft::createGuzzleClient([
            'base_uri' => EscapeInfo::getInstance()->getSettings()->escapeInfoUrl,
        ]);
        try {
            $response = $client->get('adspace/sites');
            $data = \json_decode($response->getBody()->getContents(), true)['data'] ?? null;
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }
        }
        if (!$data) {
            return [];
        }
        Craft::$app->getCache()->set($cacheKey, $data);
        return $data;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getAds(): array
    {
        $cacheKey = EscapeInfo::getInstance()->getVersion() . '-adspace-ads';
        if ($cachesEnabled = EscapeInfo::getInstance()->getSettings()->cachesEnabled) {
            $cachedData = Craft::$app->getCache()->get($cacheKey);
            if ($cachedData && \is_array($cachedData)) {
                return $cachedData;
            }
        }
        $client = Craft::createGuzzleClient([
            'base_uri' => EscapeInfo::getInstance()->getSettings()->escapeInfoUrl,
        ]);
        try {
            $response = $client->get('adspace/ads');
            $data = \json_decode($response->getBody()->getContents(), true)['data'] ?? null;
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }
        }
        if (!$data) {
            return [];
        }
        Craft::$app->getCache()->set($cacheKey, $data, 'PT5M');
        return $data;
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function renderShoutoutsButton(): string
    {
        return Craft::$app->getView()->renderTemplate('escape-info/_components/adspace/shoutouts-button.twig', [], View::TEMPLATE_MODE_CP);
    }
    
}
