<?php

namespace escape\info\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\UrlHelper;
use craft\web\View;
use escape\info\assetbundles\AdspaceBannerBundle;
use escape\info\assetbundles\AdspacePopupBundle;
use escape\info\assetbundles\AdspaceShoutoutsBundle;
use escape\info\EscapeInfo;
use escape\info\helpers\AdspaceHelper;
use GuzzleHttp\Client;

/**
 *
 */
class Adspace extends Component
{

    /**
     * Get Adspace-enabled Playground sites
     *
     * @param bool $bypassCache
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getSites(bool $bypassCache = false): array
    {
        $cacheKey = static::getCacheKey('adspace-sites');
        $cachesEnabled = !$bypassCache && EscapeInfo::getInstance()->getSettings()->cachesEnabled;
        if ($cachesEnabled) {
            $cachedData = Craft::$app->getCache()->get($cacheKey);
            if ($cachedData && \is_array($cachedData)) {
                return $cachedData;
            }
        }
        $client = $this->getGuzzleClient();
        try {
            $response = $client->get('adspace/sites');
            $data = \json_decode($response->getBody()->getContents(), true)['data'] ?? null;
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }
            return Craft::$app->getCache()->get($cacheKey) ?? [];
        }
        if (!$data) {
            Craft::$app->getCache()->delete($cacheKey);
            return [];
        }
        Craft::$app->getCache()->set($cacheKey, $data, 'P1D');
        return $data;
    }

    /**
     * @param bool $bypassCache
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function getAds(bool $bypassCache = false): array
    {
        $cacheKey = static::getCacheKey('adspace-ads');
        $cachesEnabled = !$bypassCache && EscapeInfo::getInstance()->getSettings()->cachesEnabled;
        if ($cachesEnabled) {
            $cachedData = Craft::$app->getCache()->get($cacheKey);
            if ($cachedData && \is_array($cachedData)) {
                return $cachedData;
            }
        }
        $client = $this->getGuzzleClient();
        try {
            $response = $client->get('adspace/ads');
            $data = \json_decode($response->getBody()->getContents(), true)['data'] ?? null;
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }
            return Craft::$app->getCache()->get($cacheKey) ?? [];
        }
        if (!$data) {
            Craft::$app->getCache()->delete($cacheKey);
            return [];
        }
        Craft::$app->getCache()->set($cacheKey, $data, 'P1D');
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
    public function renderShoutouts(?array $selectedAds = null): string
    {
        if (!$selectedAds || empty($selectedAds)) {
            return '';
        }
        $allAdsByKey = $this->getAllAdsByKey();
        $adsToDisplay = \array_reduce($selectedAds, function (array $carry, array $selectedAd) use ($allAdsByKey) {
            $key = "{$selectedAd['uid']}:{$selectedAd['siteUid']}";
            $ad = $allAdsByKey[$key] ?? null;
            // Only live ads please!
            if (!$ad || $ad['status'] !== Entry::STATUS_LIVE) {
                return $carry;
            }
            $carry[] = \array_merge($selectedAd, [
                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
                    'container' => AdspaceHelper::CONTAINER_SHOUTOUT,
                ]),
            ]);
            return $carry;
        }, []);
        if (empty($adsToDisplay)) {
            return '';
        }
        Craft::$app->getView()->registerAssetBundle(AdspaceShoutoutsBundle::class);
        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-shoutouts.twig', [
            'ads' => $adsToDisplay,
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param array|null $selectedAds
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function renderPopup(?array $selectedAds = null): string
    {
        if (!$selectedAds || empty($selectedAds)) {
            return '';
        }
        $allAdsByKey = $this->getAllAdsByKey();
        $adsToDisplay = \array_reduce($selectedAds, function (array $carry, array $selectedAd) use ($allAdsByKey) {
            $key = "{$selectedAd['uid']}:{$selectedAd['siteUid']}";
            $ad = $allAdsByKey[$key] ?? null;
            // Only live ads please!
            if (!$ad || $ad['status'] !== Entry::STATUS_LIVE) {
                return $carry;
            }
            $carry[] = \array_merge($selectedAd, [
                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
                    'container' => AdspaceHelper::CONTAINER_POPUP,
                ]),
            ]);
            return $carry;
        }, []);
        if (empty($adsToDisplay)) {
            return '';
        }
        Craft::$app->getView()->registerAssetBundle(AdspacePopupBundle::class);
        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-popup.twig', [
            'ads' => $adsToDisplay,
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param string $adUid
     * @param string $siteUid
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function renderBanner(string $adUid, string $siteUid): string
    {
        // Make sure that this is a valid ad
        $allAdsByKey = $this->getAllAdsByKey();
        $ad = $allAdsByKey["$adUid:$siteUid"] ?? null;
        if (!$ad || $ad['status'] !== Entry::STATUS_LIVE) {
            return '';
        }
        Craft::$app->getView()->registerAssetBundle(AdspaceBannerBundle::class);
        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-banner.twig', [
            'ad' => \array_merge($ad, [
                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
                    'container' => AdspaceHelper::CONTAINER_BANNER,
                ]),
            ]),
        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getCacheKey(string $path): string
    {
        return 'playground-' . EscapeInfo::getInstance()->getVersion() . '-' . $path;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    protected function getAllAdsByKey(): array
    {
        // If sandbox mode, render nothing for anonymous users
        $settings = EscapeInfo::getInstance()->getSettings();
        if ($settings->sandboxMode && !Craft::$app->getUser()->getId()) {
            return [];
        }
        $allAds = EscapeInfo::getInstance()->adspace->getAds();
        return \array_reduce($allAds, function (array $carry, array $ad) {
            $carry["{$ad['uid']}:{$ad['siteUid']}"] = $ad;
            return $carry;
        }, []);
    }

    /**
     * @param array $config
     * @return Client
     */
    protected function getGuzzleClient(array $config = []): Client
    {
        return Craft::createGuzzleClient(\array_merge([
            'base_uri' => EscapeInfo::getInstance()->getSettings()->escapeInfoUrl,
            'connect_timeout' => 2,
            'read_timeout' => 2,
        ], $config));
    }

}
