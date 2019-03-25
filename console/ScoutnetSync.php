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
                $start = $event->start_date ? $event->start_date : '';
                $start .= $event->start_time ? ' '.$event->start_time : '';

                $end = $event->end_date ? $event->end_date : '';
                $end .= $event->end_time ? ' '.$event->end_time : '';

                $local = Event::updateOrCreate(['scoutnet_id' => $event->id], [
                    'calendar_id' => $calendar->id,
                    'title' => $event->title,
                    'location' => $event->location && $event->location !== 'NULL'
                        ? $event->location
                        : null,
                    'starts_at' => $start ? Carbon::parse($start) : null,
                    'ends_at' => $end ? Carbon::parse($end) : null,
                    'organizer' => $event->organizer ?: null,
                    'target' => $event->target_group ?: null,
                    'url' => $event->url ?: null,
                    'url_text' => $event->url_text ?: null,
                    'description' => $event->description ?: null,
                    'scoutnet_id' => $event->id
                ]);

                $keywords = collect([]);
                foreach($event->keywords->elements as $keywordId => $keyword) {
                    $keywords->push(Keyword::updateOrCreate(['scoutnet_id' => $keywordId], [
                        'scoutnet_id' => $keywordId,
                        'title' => $keyword
                    ]));
                }
                $local->keywords()->sync($keywords->pluck('id')->toArray());
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
