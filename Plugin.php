<?php namespace Zoomyboy\Scoutnetcalendar;

use Backend;
use System\Classes\PluginBase;
use Zoomyboy\Scoutnetcalendar\Models\Settings;


/**
 * scoutnetcalendar Plugin Information File
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
            'name'        => 'scoutnetcalendar',
            'description' => 'This Plugin adds features to display a Scoutnet Calendar on a page easily.',
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
            'Zoomyboy\Scoutnetcalendar\Components\ScoutnetcalendarSingle' => 'scoutnetcalendar_single',
        ];
    }

    public function registerPageSnippets()
    {
        return [
            'Zoomyboy\Scoutnetcalendar\Components\ScoutnetcalendarSingle' => 'scoutnetcalendar_single',
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
            'zoomyboy.scoutnetcalendar.settings' => [
                'tab' => 'Scoutnet-Calendar',
                'label' => 'Einstellungen verwalten'
            ],
			'zoomyboy.scoutnetcalendar.calendar' => [
				'tab' => 'Scoutnet-Calendar',
				'label' => 'Calendar-Overview'
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
            'scoutnetcalendar' => [
                'label'       => 'Scoutnet-Calendar',
                'url'         => Backend::url('zoomyboy/scoutnetcalendar/calendar'),
                'icon'        => 'icon-calendar',
                'permissions' => ['zoomyboy.scoutnetcalendar.*'],
                'order'       => 500,
				'sideMenu' => [
					'calendar' => [
						'label' => 'Calendars',
						'icon' => 'icon-calendar',
						'url' => Backend::url('zoomyboy/scoutnetcalendar/calendar'),
						'permissions' => ['zoomyboy.scoutnetcalendar.calendar']
					]
				]
            ],
        ];
    }

	public function registerSettings() {
		return [
			'settings' => [
				'label'       => 'Scoutnet-Calendar',
				'description' => 'Manage Global Settings for Scoutnet-Calendar',
				'category'    => 'Plugins',
				'icon'        => 'icon-calendar',
				'class'       => 'Zoomyboy\Scoutnetcalendar\Models\Settings',
				'order'       => 500,
				'keywords'    => 'security location',
				'permissions' => ['zoomybo0y.scoutnetcalendar.settings']
			]
		];
	}

}
