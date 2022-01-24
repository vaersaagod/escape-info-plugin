<?php

namespace escape\info\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{

    /** @var string */
    public string $escapeInfoUrl = '';

    /** @var bool */
    public bool $sandboxMode = false;

    /** @var bool */
    public bool $cachesEnabled = true;

    /** @var string|null */
    public ?string $defaultSite = null;

    /** @var array */
    public array $theme;

    /** @var string */
    public string $siteName;

}
