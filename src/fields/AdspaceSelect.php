<?php

namespace escape\info\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use craft\helpers\Json;

use craft\helpers\UrlHelper;
use escape\info\EscapeInfo;

class AdspaceSelect extends Field
{

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

    /**
     * @param mixed $value
     * @param ElementInterface|null $element
     * @return array
     */
    public function normalizeValue($value, ElementInterface $element = null): array
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        }

        if (!is_array($value) || empty($value)) {
            return [];
        }

        return $value;
    }

    /**
     * @param $value
     * @param ElementInterface|null $element
     * @return array|mixed|string|null
     */
//    public function serializeValue($value, ElementInterface $element = null)
//    {
//        $value = $value ?? [];
//        foreach ($value as &$item) {
//            if (($item['uid'] ?? null) || !($item['source'] ?? null) || !($item['id'] ?? null)) {
//                continue;
//            }
//            $data = Playground::getInstance()->feeds->getFeed($item['source'], "{$this->endpoint}/{$item['id']}.json");
//            if ($data) {
//                $item = \array_merge($item, $data);
//            }
//        }
//        return $value;
//    }

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
        $siteSources = \array_map(function (array $site) {
            return [
                'label' => $site['name'],
                'value' => $site['handle'],
            ];
        }, $sites);

        $settings = EscapeInfo::getInstance()->getSettings();
        $defaultSite = $settings->defaultSite;

        $ads = EscapeInfo::getInstance()->adspace->getAds();

        return Craft::$app->getView()->renderTemplate('escape-info/_components/fields/AdspaceSelect/input.twig', [
            'id' => $namespacedId,
            'name' => $this->handle,
            'sites' => $siteSources,
            'selectedSite' => $defaultSite,
            'ads' => $ads,
            'value' => Json::encode($value),
        ]);
    }

    /**
     * @return null
     */
    public function getSettingsHtml()
    {
        return null;
    }

}
