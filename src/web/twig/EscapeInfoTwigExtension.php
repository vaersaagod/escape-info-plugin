<?php

namespace escape\info\web\twig;

use Craft;
use Twig\Extension\AbstractExtension;

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
        return [];
    }
    
    public function getFunctions()
    {
        return [];
    }

}
