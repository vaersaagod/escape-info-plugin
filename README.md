# Escape Info

Shows ads from a Playground site on Craft CMS sites.

Editors pick ads with the **Escape Info: Adspace Select** field. Templates render a placeholder for each banner, and the
plugin replaces it with the banner after the page has rendered, so pages can be cached without caching the ads.

## Requirements

Craft CMS 5.8 or later.

## Setup

Copy `src/config.php` to `config/escape-info.php` and set `escapeInfoUrl` to the URL of the Playground site.

The plugin keeps a copy of the Playground's ads in Craft's temp folder, and refreshes it when it's more than a day old.
To keep page loads from waiting on the Playground, refresh it with a cron job instead:

```bash
php craft escape-info/ads/update-ads
```

## Rendering banners

```twig
{% set ad = entry.adspaceAd|first %}
{% if ad %}
    {{ renderAdspaceBannerPlaceholder(ad.uid, ad.siteUid, { class: 'banner' }) }}
{% endif %}
```

The attributes in the third argument are added to the banner's outer element. Placeholders are signed with the site's
security key, so cached placeholders stop rendering if the key changes. Clear the data caches if that happens.

Add `{% hook 'escape-info-head' %}` to the `<head>` to load the banners' styles and theme colours.

Brought to you by [Værsågod](https://vaersaagod.no)
