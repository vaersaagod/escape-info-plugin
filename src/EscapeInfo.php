<?php

namespace escape\info;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\TemplateEvent;
use craft\helpers\App;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\i18n\PhpMessageSource;
use craft\log\MonologTarget;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;

use escape\info\assetbundles\PlaygroundBundle;
use escape\info\fields\AdspaceSelect;
use escape\info\helpers\EscapeInfoHelper;
use escape\info\models\Settings;
use escape\info\services\Adspace;
use escape\info\web\twig\EscapeInfoTwigExtension;
use escape\info\web\twig\variables\EscapeInfoVariable;

use Psr\Log\LogLevel;
use yii\base\Event;

/**
 * @property Adspace $adspace
 */
class EscapeInfo extends Plugin
{

    /**
     * @inheritdoc
     */
    public function init()
    {

        Craft::setAlias('@escapeinfoplugin', __DIR__);

        parent::init();

        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'escape-info',
            'categories' => ['escape-info', 'escape\\info\\*'],
            'extractExceptionTrace' => !App::devMode(),
            'allowLineBreaks' => App::devMode(),
            'level' => App::devMode() ? LogLevel::INFO : LogLevel::WARNING,
            'logContext' => false,
            'maxFiles' => 10,
        ]);

        // Register services
        $this->setComponents([
            'adspace' => Adspace::class,
        ]);

        // Register module variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('escapeinfo', EscapeInfoVariable::class);
            }
        );

        // Register Twig extension
        Craft::$app->getView()->registerTwigExtension(new EscapeInfoTwigExtension());

        // Register custom fields
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = AdspaceSelect::class;
        });

        // Add theme CSS
        Craft::$app->view->hook('escape-info-head', function (array &$context) {
            Craft::$app->getView()->registerAssetBundle(PlaygroundBundle::class);
            return Craft::$app->getView()->renderTemplate('escape-info/_components/playground/playground-theme.twig', [
                'theme' => $this->getSettings()->theme,
            ], View::TEMPLATE_MODE_CP);
        });

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['playground/get-shoutouts-html'] = 'escape-info/adspace/get-shoutouts-html';
                $event->rules['playground/get-popup-html'] = 'escape-info/adspace/get-popup-html';
            }
        );

        // Render banner placeholders
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Event::on(
                View::class,
                View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
                static function (TemplateEvent $event) {
                    $html = $event->output;
                    $event->output = preg_replace_callback('/<!--\s*playground-banner:(\{.*?\})\s*-->/s', function ($matches) {
                        if (EscapeInfoHelper::isSandbox()) {
                            // If we're sandboxed, just return an empty string to replace the placeholder
                            return '';
                        }

                        $json = $matches[1];
                        $data = Json::decodeIfJson($json);
                        if (empty($data)) {
                            return '';
                        }

                        $adUid = $data['ad']['uid'] ?? null;
                        $adSiteUid = $data['ad']['siteUid'] ?? null;
                        if (empty($adUid) || empty($adSiteUid)) {
                            return '';
                        }

                        try {
                            $banner = EscapeInfo::getInstance()->adspace->renderBanner($adUid, $adSiteUid);
                        } catch (\Throwable $e) {
                            Craft::error($e, __METHOD__);
                            return '';
                        }

                        if (empty($banner)) {
                            return '';
                        }

                        return Html::modifyTagAttributes($banner, $data['attributes'] ?? []);
                    }, $html);
                }
            );
        }

    }

    /**
     * @return Settings
     */
    public function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @return Settings
     */
    public function getSettings(): ?\craft\base\Model
    {
        return parent::getSettings(); // TODO: Change the autogenerated stub
    }

}
