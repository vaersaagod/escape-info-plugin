<?php

namespace escape\info\web\twig;

use Craft;
use escape\info\assetbundles\AdspaceBannersBundle;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EscapeInfoTwigExtension extends AbstractExtension
{

    /**
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'escape-info';
    }

    public function getFilters()
    {
        return [
            new TwigFilter('renderPlaygroundPlaceholders', [$this, 'renderPlaygroundPlaceholdersFilter'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions()
    {
        return [];
    }

    public function renderPlaygroundPlaceholdersFilter(string $html): string
    {
        // Banner placeholders
        \preg_match_all('/(<!-- playground-banner:)(.*)( -->)/', $html, $matches);
        if (!empty($matches[0] ?? null)) {
            Craft::$app->getView()->registerAssetBundle(AdspaceBannersBundle::class);
        }
        return $html;
    }

}
