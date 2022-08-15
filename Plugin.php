<?php namespace Zoomyboy\Scoutnet;

use Backend;
use Input;
use System\Classes\PluginBase;
use Zoomyboy\Scoutnet\Classes\IcalGenerator;
use Zoomyboy\Scoutnet\Components\EventList;
use Zoomyboy\Scoutnet\Components\EventSlider;
use Zoomyboy\Scoutnet\Components\SingleCalendar;
use Zoomyboy\Scoutnet\FormWidgets\ConnectButton;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Models\Setting;

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
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            SingleCalendar::class => 'singleCalendar',
            EventSlider::class => 'scoutnet_event_slider',
            EventList::class => 'scoutnet_event_list',
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
