<?php

namespace escape\info\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use craft\helpers\Json;

use craft\helpers\UrlHelper;
use escape\info\EscapeInfo;
use yii\db\Schema;

class AdspaceSelect extends Field
{

    /**
     * @var int|null The maximum number of ads this field can have
     */
    public $limit;

    /** @var string[]|string|null The Playground sites to select ads from */
    public $siteSources = '*';

    /** @var string[]|string|null The Playground containers to allow ads from */
    public $containers = '*';

    /** @inheritdoc */
    public static function displayName(): string
    {
        return Craft::t('site', 'Escape Info: Adspace Select');
    }

    /** @inheritdoc **/
    public static function hasContentColumn(): bool
    {
        return true;
    }

    /** @inheritdoc */
    public static function valueType(): string
    {
        return 'mixed';
    }

    /** @inheritdoc */
    public function getContentColumnType()
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @param $value
     * @param ElementInterface|null $element
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function normalizeValue($value, ElementInterface $element = null): array
    {

        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (!is_array($value) || empty($value)) {
            return [];
        }

        try {
            $sites = EscapeInfo::getInstance()->adspace->getSites();
            $ads = EscapeInfo::getInstance()->adspace->getAds();
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            if (Craft::$app->getConfig()->getGeneral()->devMode) {
                throw $e;
            }
            return $value;
        }

        if (\is_array($this->siteSources)) {
            $siteSources = $this->siteSources;
        } else {
            $siteSources = \array_values(\array_map(function (array $site) {
                return $site['uid'];
            }, $sites));
        }

        // Filter out any selected ads not available from Playground
        // Also fetch the updated status from Playground
        // Also filter by allowed site sources
        $value = \array_reduce($value, function (array $carry, array $selectedAd) use ($ads, $siteSources) {
            foreach ($ads as $ad) {
                if (\in_array($ad['siteUid'], $siteSources) && $ad['uid'] === $selectedAd['uid'] && $ad['siteUid'] === $selectedAd['siteUid']) {
                    $carry[] = $ad;
                    return $carry;
                }
            }
            return $carry;
        }, []);

        // Account for limit
        if ($this->limit) {
            $value = \array_values(\array_slice($value, 0, $this->limit));
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {

        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Get sites
        $sites = EscapeInfo::getInstance()->adspace->getSites();

        $settings = EscapeInfo::getInstance()->getSettings();
        $defaultSite = $settings->defaultSite;

        $ads = EscapeInfo::getInstance()->adspace->getAds(true);

        // Filter by allowed sites
        if ($this->siteSources && \is_array($this->siteSources)) {
            $sites = array_values(\array_filter($sites, function (array $site) {
                return \in_array($site['uid'], $this->siteSources);
            }));
            $ads = \array_values(\array_filter($ads, function (array $ad) {
                return \in_array($ad['siteUid'], $this->siteSources);
            }));
        }

        return Craft::$app->getView()->renderTemplate('escape-info/_components/fields/AdspaceSelect/input.twig', [
            'id' => $namespacedId,
            'name' => $this->handle,
            'sites' => $sites,
            'selectedSite' => $defaultSite,
            'ads' => $ads,
            'field' => $this,
            'value' => Json::encode($value),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $sites = EscapeInfo::getInstance()->adspace->getSites();
        return Craft::$app->getView()->renderTemplate('escape-info/_components/fields/AdspaceSelect/settings.twig', [
            'sites' => $sites,
            'field' => $this,
        ]);
    }

}
