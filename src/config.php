<?php

use craft\helpers\App;

/**
 * Copy to config/escape-info.php
 */
return [
    // The URL to the Playground site that serves the ads
    'escapeInfoUrl' => (string)App::env('ESCAPE_INFO_URL'),

    // The UID of the Playground site to preselect in the Adspace Select field
    'defaultSite' => App::env('ESCAPE_INFO_DEFAULT_SITE'),

    // Render no ads on the front end, except for logged-in users
    'sandboxMode' => (bool)App::env('ESCAPE_INFO_ENABLE_SANDBOX_MODE'),

    // Colours for the ad containers, as CSS colour values
    'theme' => [
        'colorPrimary' => null,
        'colorSecondary' => null,
        'colorAccent' => null,
    ],

    // The site's name, as used in the ad containers' labels
    'siteName' => App::env('SITE_NAME'),
];
