<?php namespace Zoomyboy\Scoutnet\Console;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Zoomyboy\Scoutnet\Models\Event;
use Zoomyboy\Scoutnet\Models\Keyword;
use Zoomyboy\Scoutnet\Models\Calendar;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ScoutnetSync extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'scoutnet:sync';

    /**
     * @var string The console command description.
     */
    protected $description = 'Pulls Events from scoutnet API from the saved calendars';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        Calendar::get()->each(function($calendar) {
            $calendar->scoutnetSync()->sync();
        });

        $this->info('Events synchronisiert');
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
