<?php namespace Zoomyboy\Scoutnet\Models;

use Queue;
use Model;
use BackendAuth;
use Carbon\Carbon;
use Zoomyboy\Scoutnet\Models\Keyword;
use Zoomyboy\Scoutnet\Models\Calendar;
use Zoomyboy\Scoutnet\Classes\PushToApi;
use \October\Rain\Database\Traits\Validation;
/**
 * Calendar Model
 */
class Event extends Model
{
    use Validation;

    public static $icon = 'oc-icon-clock-o';
    public static $tabTitle = 'zoomyboy.scoutnet::lang.newEvent';

    public $rules = [
        'title' => 'required',
        'starts_at' => 'required|date',
        'calendar_id' => 'required|exists:zoomyboy_scoutnet_calendars,id'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'zoomyboy_scoutnet_events';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    protected $dates = ['starts_at', 'ends_at'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['calendar_id', 'title', 'starts_at', 'ends_at', 'location', 'organizer', 'target', 'url', 'url_text', 'description', 'scoutnet_id'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'calendar' => Calendar::class
    ];
    public $belongsToMany = [
        'keywords' => [Keyword::class, 'table' => 'zoomyboy_scoutnet_event_keyword']
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        'images' => \System\Models\File::class
    ];

    /**
     * @todo über mysql holen mit DATETIME_FORMAT
     */
    public function getDisplayDateAttribute() {
        $startDate = $this->starts_at->format('d.m.Y');

        $endDate = $this->ends_at
            ? $this->ends_at->format('d.m.Y')
            : null;

        if ($endDate && $startDate == $endDate) {
            return $startDate;
        }

        if (!$endDate && $startDate) {
            return $startDate;
        }

        if ($startDate !== $endDate) {
            return $startDate.' - '.$endDate;
        }

        return $this->starts_at->format('d.m.Y H:i:s')
        .(!empty($this->ends_at) ? ' - '.$this->ends_at->format('d.m.Y H:i') : '');
    }

    /**
     * @todo über mysql holen mit DATETIME_FORMAT
     */
    public function getDisplayTimeAttribute() {
        $startTime = $this->starts_at->format('H:i') !== '00:00'
            ? $this->starts_at->format('H:i')
            : null;

        $endTime = $this->ends_at && $this->ends_at->format('H:i') !== '00:00'
            ? $this->ends_at->format('H:i')
            : null;

        if ($endTime && $startTime == $endTime) {
            return $startTime;
        }

        if (!$endTime && $startTime) {
            return $startTime;
        }

        if (!$endTime && !$startTime) {
            return null;
        }

        if ($startTime !== $endTime) {
            return $startTime.' - '.$endTime;
        }

        return $this->starts_at->format('d.m.Y H:i')
        .(!empty($this->ends_at) ? ' - '.$this->ends_at->format('d.m.Y H:i') : '');
    }

    public function getIcalStartAttribute() {
        return $this->starts_at;
    }

    public function getIcalEndAttribute() {
        return $this->ends_at ?: $this->starts_at->addMinutes(15);
    }

    public static function createFromScoutnet($event) {
        $calendar = Calendar::where(['scoutnet_id' => $event->group_id])->firstOrFail();

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
        foreach($event->keywords as $keywordId => $keyword) {
            $keywords->push(Keyword::updateOrCreate(['scoutnet_id' => $keywordId], [
                'scoutnet_id' => $keywordId,
                'title' => $keyword
            ]));
        }
        $local->keywords()->sync($keywords->pluck('id')->toArray());
    }

    public static function boot() {
        parent::boot();

        /* @todo push this only when changed via backend 
        static::saved(function($event) {
            if (!$event->calendar->connectionService('scoutnet_connect')->hasCredentials() || !$event->calendar->connectionService('scoutnet_connect')->isConnected()) {
                return;
            }

            Queue::push(PushToApi::class, [
                'event_id' => $event->id,
                'credential_id' => $event->calendar->getCurrentCredential('scoutnet')->id
            ]);
        });
         */
    }
}
