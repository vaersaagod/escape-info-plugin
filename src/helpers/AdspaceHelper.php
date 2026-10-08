<?php

namespace escape\info\helpers;

use Craft;
use craft\helpers\UrlHelper;

use escape\info\EscapeInfo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use yii\base\InvalidConfigException;

class AdspaceHelper
{

    /** @var string */
    const string CONTAINER_SHOUTOUT = 'shoutout';

    /** @var string */
    const string CONTAINER_BANNER = 'banner';

    /** @var string */
    const string CONTAINER_POPUP = 'popup';

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    public static function getUrl(string $path, array $params = []): string
    {
        $settings = EscapeInfo::getInstance()->getSettings();
        $playgroundUrl = \rtrim($settings->escapeInfoUrl, '/');
        return UrlHelper::url($playgroundUrl . "/$path", $params);
    }

    /**
     * Get Adspace-enabled Playground sites from the Playground API
     *
     * @param bool $bypassCache
     * @return array
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public static function getSitesFromApi(): array
    {
        $client = self::getClient();
        $response = $client->get('adspace/sites');
        $data = json_decode($response->getBody()->getContents(), true)['data'] ?? null;
        if (!is_array($data)) {
            throw new \Exception("Invalid data from Playground");
        }
        return $data;
    }

    /**
     * Queries the Playground API for fresh ads
     *
     * @return array
     * @throws GuzzleException
     */
    public static function getAdsFromApi(): array
    {
        $client = AdspaceHelper::getClient();
        $response = $client->get('adspace/ads');
        $data = json_decode($response->getBody()->getContents(), true)['data'] ?? null;
        if (!is_array($data)) {
            throw new \Exception("Invalid data from Playground");
        }

        return $data;
    }

    /**
     * @return Client
     */
    private static function getClient(): Client
    {
        return Craft::createGuzzleClient([
            'base_uri' => EscapeInfo::getInstance()->getSettings()->escapeInfoUrl,
            'connect_timeout' => 5,
            'read_timeout' => 5,
            'timeout' => 10,
        ]);
    }
}
