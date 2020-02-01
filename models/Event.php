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

    public $casts = ['is_one_day' => 'boolean', 'is_all_day' => 'boolean'];

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

    public function scopeWithIsOneDay($q) {
        $q->select('*');
        $oneDayQuery = 'DATE_FORMAT(starts_at, "%T") = "00:00:00" AND (ends_at is NULL OR starts_at = ends_at)';
        $q->selectSub($oneDayQuery, 'is_one_day');
    }

    public function scopeWithIsAllDay($q) {
        $q->select('*');
        $allDayQuery = '
            (DATE_FORMAT(starts_at, "%T") = "00:00:00" AND ends_at is NULL)
            OR (ends_at is not NULL AND DATE_FORMAT(starts_at, "%T") = "00:00:00" AND DATE_FORMAT(ends_at, "%T") = "00:00:00")
        ';
        $q->selectSub($allDayQuery, 'is_all_day');
    }

    public function getIcalStartAttribute() {
        return $this->starts_at;
    }

    public function getIcalEndAttribute() {
        return $this->ends_at ?: $this->starts_at->addMinutes(15);
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
