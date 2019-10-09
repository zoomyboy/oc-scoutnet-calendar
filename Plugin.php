<?php namespace Zoomyboy\Scoutnet;

use Str;
use URL;
use Event;
use Input;
use Backend;
use Cms\Classes\Theme;
use System\Classes\PluginBase;
use Cms\Classes\Page as CmsPage;
use Zoomyboy\Scoutnet\Models\Setting;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Classes\IcalGenerator;
use Zoomyboy\Scoutnet\Classes\EventRepository;
use October\Rain\Router\Helper as RouterHelper;
use Zoomyboy\Scoutnet\FormWidgets\ConnectButton;


/**
 * scoutnet Plugin Information File
 */
class Plugin extends PluginBase
{
    public static $jsonUrl = 'https://www.scoutnet.de/jsonrpc/server.php';
    public static $loginUrl = 'https://www.scoutnet.de/community/scoutnetconnect.html';

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'scoutnet',
            'description' => 'zoomyboy.scoutnet::global.description',
            'author'      => 'zoomyboy',
            'icon'        => 'icon-calendar'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConsoleCommand(
            'zoomyboy.scoutnetsync',
            \Zoomyboy\Scoutnet\Console\ScoutnetSync::class
        );
        $this->registerConsoleCommand(
            'zoomyboy.scoutnetgooglesync',
            \Zoomyboy\Scoutnet\Console\ScoutnetGooglesync::class
        );

        $this->app->bind('scoutnet.ical', function() {
            return new IcalGenerator();
        });
        $this->app->bind('scoutnet.events', function() {
            return new EventRepository();
        });

        $this->app->bind('scoutnet.api', function() {
            return new class(static::$jsonUrl, static::$loginUrl) {
                public $jsonUrl;
                public $loginUrl;

                public function __construct($jsonUrl, $loginUrl) {
                    $this->jsonUrl = $jsonUrl;
                    $this->loginUrl = $loginUrl;
                }

                public function group($group) {
                    $group = Calendar::where('scoutnet_id', $group)->firstOrFail();

                    return new \ScoutNet\Api\ScoutnetApi(
                        $this->jsonUrl,
                        $this->loginUrl,
                        $group->provider,
                        $group->aes_key,
                        $group->aes_iv
                    );
                }
            };
        });
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                'zoomyboy-scoutnet-single-calendar' => 'Single Calendar'
            ];
        });
        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type != 'zoomyboy-scoutnet-single-calendar') { return; }

            $calendars = Calendar::get()->pluck('title', 'slug')->toArray();
            $theme = Theme::getActiveTheme();

            return [
                'dynamicItems' => false,
                'nesting' => false,
                'references' => $calendars,
                'cmsPages' => CmsPage::listInTheme($theme, true)->filter(function($page) {
                    return $page->hasComponent('singleCalendar');
                })
            ];
        });
        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type != 'zoomyboy-scoutnet-single-calendar') { return; }

            $page = CmsPage::loadCached($theme, $item->cmsPage);

            if (!$page) return;

            $itemUrl = CmsPage::url($page->getBaseFileName(), ['slug' => $item->reference]);
            $isActive = $url == $itemUrl;

            return [
                'isActive' => $isActive,
                'url' => $itemUrl
            ];
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Zoomyboy\Scoutnet\Components\SingleCalendar' => 'singleCalendar',
        ];
    }

    public function registerPageSnippets()
    {
        return [
            'Zoomyboy\Scoutnet\Components\SingleCalendar' => 'singleCalendar',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'zoomyboy.scoutnet.settings' => [
                'tab' => 'Scoutnet',
                'label' => 'zoomyboy.scoutnet::lang.permissions.settings'
            ],
            'zoomyboy.scoutnet.calendar' => [
                'tab' => 'Scoutnet',
                'label' => 'zoomyboy.scoutnet::lang.permissions.calendar'
            ],
            'zoomyboy.scoutnet.keyword' => [
                'tab' => 'Scoutnet',
                'label' => 'zoomyboy.scoutnet::lang.permissions.keyword'
            ],
            'zoomyboy.scoutnet.tag' => [
                'tab' => 'Scoutnet',
                'label' => 'zoomyboy.scoutnet::lang.permissions.tag'
            ]
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'scoutnet' => [
                'label'       => 'zoomyboy.scoutnet::menu.scoutnet',
                'url'         => Backend::url('zoomyboy/scoutnet/calendar/index'),
                'icon'        => 'icon-calendar',
                'permissions' => ['zoomyboy.scoutnet.*'],
                'order'       => 500,
                'sideMenu' => [
                    'calendar' => [
                        'label' => 'zoomyboy.scoutnet::menu.calendar',
                        'icon' => 'icon-calendar',
                        'url' => Backend::url('zoomyboy/scoutnet/calendar/index'),
                        'permissions' => ['zoomyboy.scoutnet.calendar'],
                        'attributes'  => ['data-menu-item' => 'calendar'],
                    ],
                    'keyword' => [
                        'label' => 'zoomyboy.scoutnet::menu.keyword',
                        'icon' => 'icon-tag',
                        'url' => Backend::url('zoomyboy/scoutnet/keyword/index'),
                        'permissions' => ['zoomyboy.scoutnet.calendar'],
                        'attributes'  => ['data-no-side-panel' => true],
                    ],
                    'tag' => [
                        'label' => 'zoomyboy.scoutnet::menu.tag',
                        'icon' => 'icon-tag',
                        'url' => Backend::url('zoomyboy/scoutnet/tag/index'),
                        'permissions' => ['zoomyboy.scoutnet.tag'],
                        'attributes'  => ['data-no-side-panel' => true],
                    ]
                ]
            ],
        ];
    }

    public function registerSettings() {
        return [
            'settings' => [
                'label'       => 'Scoutnet',
                'description' => 'zoomyboy.scoutnet::settings.description',
                'category'    => 'Plugins',
                'icon'        => 'icon-calendar',
                'class'       => Setting::class,
                'order'       => 500,
                'keywords'    => 'calendar scoutnet scout',
                'permissions' => ['zoomyboy.scoutnet.settings']
            ]
        ];
    }

    public function registerSchedule($schedule) {
        $schedule->command('scoutnet:sync')->everyFiveMinutes();
    }

    public function registerFormWidgets() {
        return [
            ConnectButton::class => 'zoomyboy_scoutnet_connect_button'
        ];
    }
}
