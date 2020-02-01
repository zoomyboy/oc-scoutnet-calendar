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
        $client = new Client(['base_uri' => 'http://www.scoutnet.de/api/0.2/group/']);
        $lastYear = Carbon::now()->subYear(1)->startOfYear()->format('Y-m-d');

        Calendar::get()->each(function($calendar) use ($client, $lastYear) {
            $response = $client->get("{$calendar->scoutnet_id}/events/?json=[\"start_date >= '".$lastYear."'\"]");
            $response = json_decode((string) $response->getBody());

            foreach($response->elements as $event) {
                Event::createFromScoutnet($event);
            }
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
