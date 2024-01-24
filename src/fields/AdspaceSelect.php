<?php

namespace escape\info\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use craft\helpers\Json;

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
    public function getContentColumnType(): string
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
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): array
    {

        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (!is_array($value) || empty($value)) {
            return [];
        }

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
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {

        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Get sites
        try {
            $sites = EscapeInfo::getInstance()->adspace->getSites();
        } catch (\Throwable $e) {
            Craft::error($e, __METHOD__);
            return Html::tag('span', "Error: {$e->getMessage()}", ['class' => 'warning with-icon']);
        }

        $settings = EscapeInfo::getInstance()->getSettings();
        $defaultSite = $settings->defaultSite;

        // Get all ads from Playground
        try {
            $ads = EscapeInfo::getInstance()->adspace->getAds();
        } catch (\Throwable $e) {
            Craft::error($e, __METHOD__);
            return Html::tag('span', "Error: {$e->getMessage()}", ['class' => 'warning with-icon']);
        }

        // Filter by container
        if ($this->containers && \is_array($this->containers)) {
            $ads = array_values(array_filter($ads, function (array $ad) {
                $adMetaData = $ad['metaData'] ?? [];
                $adContainers = array_filter(array_keys($adMetaData), static function (string $key) use ($adMetaData) {
                    return !empty($adMetaData[$key]);
                });
                return !empty(array_intersect($this->containers, $adContainers));
            }));
        }

        // Filter by allowed sites
        if ($this->siteSources && is_array($this->siteSources)) {
            $sites = array_values(array_filter($sites, function (array $site) {
                return in_array($site['uid'], $this->siteSources);
            }));
            $ads = array_values(array_filter($ads, function (array $ad) {
                return in_array($ad['siteUid'], $this->siteSources);
            }));
        }

        $value = $value ?? [];

        // Update ad statuses etc
        $value = \array_reduce($value, function (array $carry, array $valueAd) use ($ads) {
            foreach ($ads as $ad) {
                if ($ad['uid'] === $valueAd['uid'] && $ad['siteUid'] === $valueAd['siteUid']) {
                    $carry[] = \array_merge($valueAd, $ad);
                    return $carry;
                }
            }
            $carry[] = $valueAd;
            return $carry;
        }, []);

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
    public function getSettingsHtml(): ?string
    {
        try {
            $sites = EscapeInfo::getInstance()->adspace->getSites();
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            return Html::tag('span', "Error: {$e->getMessage()}", ['class' => 'warning with-icon']);
        }
        return Craft::$app->getView()->renderTemplate('escape-info/_components/fields/AdspaceSelect/settings.twig', [
            'sites' => $sites,
            'field' => $this,
        ]);
    }

}
