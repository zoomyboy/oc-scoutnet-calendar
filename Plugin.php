<?php namespace Zoomyboy\Scoutnet;

use Input;
use Backend;
use System\Classes\PluginBase;
use Zoomyboy\Scoutnet\Models\Setting;
use Zoomyboy\Scoutnet\Classes\IcalGenerator;
use Zoomyboy\Scoutnet\Classes\EventRepository;


/**
 * scoutnet Plugin Information File
 */
class Plugin extends PluginBase
{

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

        $this->app->bind('scoutnet.ical', function() {
            return new IcalGenerator();
        });
        $this->app->bind('scoutnet.events', function() {
            return new EventRepository();
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
        $schedule->command('scoutnet:sync')->hourly();
    }
}
