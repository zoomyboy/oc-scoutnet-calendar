<?php namespace Zoomyboy\Scoutnet\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Zoomyboy\Scoutnet\Models\Calendar;

class ScoutnetGooglesync extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'scoutnet:googlesync';

    /**
     * @var string The console command description.
     */
    protected $description = 'No description provided yet...';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        foreach (Calendar::get() as $calendar) {
            foreach ($calendar->events()->withIsAllDay()->get() as $event) {
                $calendar->connectionService('google_calendar')->saveEvent($event, null);
            }
        }
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
