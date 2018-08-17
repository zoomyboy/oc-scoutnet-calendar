<?php namespace Zoomyboy\Scoutnet;

use Backend;
use System\Classes\PluginBase;
use Zoomyboy\Scoutnet\Models\Settings;


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
            'description' => 'This Plugin adds features to integrate Scoutnet features in October CMS',
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
                'tab' => 'General',
                'label' => 'Edit settings'
            ],
			'zoomyboy.scoutnet.calendar' => [
				'tab' => 'Calendar',
				'label' => 'Calendar Overview'
			],
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
                'label'       => 'Scoutnet',
                'url'         => Backend::url('zoomyboy/scoutnet/calendar/index'),
                'icon'        => 'icon-calendar',
                'permissions' => ['zoomyboy.scoutnet.*'],
                'order'       => 500,
				'sideMenu' => [
					'calendar' => [
						'label' => 'Calendars',
						'icon' => 'icon-calendar',
						'url' => Backend::url('zoomyboy/scoutnet/calendar/index'),
						'permissions' => ['zoomyboy.scoutnet.calendar']
					]
				]
            ],
        ];
    }

	public function registerSettings() {
		return [
			'settings' => [
				'label'       => 'Scoutnet',
				'description' => 'Manage Global Settings for Scoutnet',
				'category'    => 'Plugins',
				'icon'        => 'icon-calendar',
				'class'       => 'Zoomyboy\Scoutnet\Models\Settings',
				'order'       => 500,
				'keywords'    => 'security location',
				'permissions' => ['zoomyboy.scoutnet.settings']
			]
		];
	}
}
