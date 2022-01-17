<?php

namespace escape\info\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\View;
use escape\info\assetbundles\ShoutoutsButtonBundle;
use escape\info\EscapeInfo;
use escape\info\helpers\AdspaceHelper;

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
     * @param array|null $selectedAds
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function renderShoutoutsButton(?array $selectedAds = null): string
    {
        if (!$selectedAds || empty($selectedAds)) {
            return '';
        }
        // If sandbox mode, render nothing for anonymous users
        $settings = EscapeInfo::getInstance()->getSettings();
        if ($settings->sandboxMode && !Craft::$app->getUser()->getId()) {
            return '';
        }
        $allAds = EscapeInfo::getInstance()->adspace->getAds();
        $allAdsByKey = \array_reduce($allAds, function (array $carry, array $ad) {
            $carry["{$ad['uid']}:{$ad['siteUid']}"] = $ad;
            return $carry;
        }, []);
        $adsToDisplay = \array_reduce($selectedAds, function (array $carry, array $selectedAd) use ($allAdsByKey) {
            $key = "{$selectedAd['uid']}:{$selectedAd['siteUid']}";
            $ad = $allAdsByKey[$key] ?? null;
            if (!$ad || $ad['status'] !== Entry::STATUS_LIVE) {
                return $carry;
            }
            $carry[] = \array_merge($ad, [
                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
                    'container' => AdspaceHelper::CONTAINER_SHOUTOUT,
                ]),
            ]);
            return $carry;
        }, []);
        if (empty($adsToDisplay)) {
            return '';
        }
        Craft::$app->getView()->registerAssetBundle(ShoutoutsButtonBundle::class);
        return Craft::$app->getView()->renderTemplate('escape-info/_components/adspace/shoutouts-button.twig', [
            'ads' => $adsToDisplay,
        ], View::TEMPLATE_MODE_CP);
    }

}
