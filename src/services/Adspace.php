<?php

namespace escape\info\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\ConfigHelper;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\web\View;

use escape\info\assetbundles\AdspacePopupBundle;
use escape\info\helpers\AdspaceHelper;

use escape\info\helpers\EscapeInfoHelper;

use GuzzleHttp\Exception\GuzzleException;

use Illuminate\Support\Collection;
use yii\base\InvalidConfigException;

/**
 *
 */
class Adspace extends Component
{

    /**
     * @return array
     * @throws InvalidConfigException
     */
    public function getSites(): array
    {
        return Craft::$app->getCache()
            ->getOrSet([
                __METHOD__,
            ], static function () {
                try {
                    return AdspaceHelper::getSitesFromApi();
                } catch (\Throwable $e) {
                    Craft::error($e, __METHOD__);
                    return false;
                }
            }, ConfigHelper::durationInSeconds('PT5M'));
    }

    /**
     * @return array
     */
    public function getAllAds(): array
    {
        // The ads JSON file should be updated with a cronjob
        // But if it doesn't exist at all, allow to create it
        $adsRepositoryFilePath = $this->getAdsRepositoryFilePath();
        if (!file_exists($adsRepositoryFilePath) || (time() - filemtime($adsRepositoryFilePath)) > 86400) {
            if (!$this->updateAdsRepository()) {
                return [];
            }
        }

        try {
            $data = Json::decode(file_get_contents($adsRepositoryFilePath));
            if (!is_array($data)) {
                throw new \Exception('Invalid data in JSON ads repository');
            }
        } catch (\Throwable $e) {
            Craft::error($e, __METHOD__);
            return [];
        }

        return $data;
    }

    /**
     * Queries the Playground API for ads, and saves the payload to the ads repository JSON file
     *
     * @return bool|int Returns the number of ads cached, or false if something failed.
     */
    public function updateAdsRepository(): bool|int
    {
        try {
            $ads = AdspaceHelper::getAdsFromApi();
        } catch (\Throwable $e) {
            Craft::error($e, __METHOD__);
            return false;
        }

        try {
            FileHelper::createDirectory($this->getAdsRepositoryFolderPath());
            $result = file_put_contents($this->getAdsRepositoryFilePath(), json_encode($ads, JSON_PRETTY_PRINT));
            if (empty($result)) {
                throw new \Exception('Failed to save ads repository JSON file');
            }
        } catch (\Throwable $e) {
            Craft::error($e, __METHOD__);
            return false;
        }

        return count($ads);
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
    public function renderShoutoutsPopup(?array $selectedAds = null): string
    {
        return '';
//        if (!$selectedAds || empty($selectedAds)) {
//            return '';
//        }
//        $allAdsByKey = $this->getAllAdsByKey();
//        $adsToDisplay = \array_reduce($selectedAds, function (array $carry, array $selectedAd) use ($allAdsByKey) {
//            $key = "{$selectedAd['uid']}:{$selectedAd['siteUid']}";
//            $ad = $allAdsByKey[$key] ?? null;
//            // Only live ads please!
//            if (!$ad || $ad['status'] !== Entry::STATUS_LIVE) {
//                return $carry;
//            }
//            $carry[] = \array_merge($selectedAd, [
//                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
//                    'container' => AdspaceHelper::CONTAINER_SHOUTOUT,
//                ]),
//            ]);
//            return $carry;
//        }, []);
//        if (empty($adsToDisplay)) {
//            return '';
//        }
//        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-shoutouts-popup.twig', [
//            'ads' => $adsToDisplay,
//        ], View::TEMPLATE_MODE_CP);
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
        // TODO
        return '';
//        if (!$selectedAds || empty($selectedAds)) {
//            return '';
//        }
//        $allAdsByKey = $this->getAllAdsByKey();
//        $adsToDisplay = \array_reduce($selectedAds, function (array $carry, array $selectedAd) use ($allAdsByKey) {
//            $key = "{$selectedAd['uid']}:{$selectedAd['siteUid']}";
//            $ad = $allAdsByKey[$key] ?? null;
//            // Only live ads please!
//            if (!$ad || $ad['status'] !== Entry::STATUS_LIVE) {
//                return $carry;
//            }
//            $carry[] = \array_merge($selectedAd, [
//                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
//                    'container' => AdspaceHelper::CONTAINER_POPUP,
//                ]),
//            ]);
//            return $carry;
//        }, []);
//        if (empty($adsToDisplay)) {
//            return '';
//        }
//        Craft::$app->getView()->registerAssetBundle(AdspacePopupBundle::class);
//        return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-popup.twig', [
//            'ads' => $adsToDisplay,
//        ], View::TEMPLATE_MODE_CP);
    }

    /**
     * @param string $adUid
     * @param string $siteUid
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function renderBanner(string $adUid, string $siteUid): string
    {
        if (EscapeInfoHelper::isSandbox()) {
            return '';
        }

        $allAds = $this->getAllAdsByKey();
        $ad = $allAds["$adUid:$siteUid"] ?? null;
        if (empty($ad) || $ad['status'] !== Entry::STATUS_LIVE) {
            return '';
        }

        $html = Craft::$app->getView()->renderTemplate('escape-info/_components/playground/adspace-banner.twig', [
            'ad' => [
                ...$ad,
                'url' => AdspaceHelper::getUrl("adspace/serve/{$ad['siteUid']}/{$ad['uid']}", [
                    'container' => AdspaceHelper::CONTAINER_BANNER,
                ])
            ],
        ], View::TEMPLATE_MODE_CP);
        if (empty($html)) {
            return '';
        }

        return $html;
    }

    /**
     * @return string
     */
    private function getAdsRepositoryFolderPath(): string
    {
        return Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . 'adspace';
    }

    /**
     * @return string
     */
    private function getAdsRepositoryFilePath(): string
    {
        return $this->getAdsRepositoryFolderPath() . DIRECTORY_SEPARATOR . 'ads.json';
    }

    /**
     * @return array
     */
    private function getAllAdsByKey(): array
    {
        $allAds = Collection::make($this->getAllAds());
        return $allAds
            ->keyBy(static fn(array $ad) => "{$ad['uid']}:{$ad['siteUid']}")
            ->all();
    }

}
