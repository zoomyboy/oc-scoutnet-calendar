<?php

namespace Zoomyboy\Scoutnet\Models;

use Illuminate\Database\Eloquent\Builder;
use Model;
use October\Rain\Database\Traits\Validation;
use Queue;
use Zoomyboy\Scoutnet\Classes\PushToApi;

/**
 * Calendar Model.
 */
class Event extends Model
{
    use Validation;

    public static $icon = 'oc-icon-clock-o';
    public static $tabTitle = 'zoomyboy.scoutnet::lang.newEvent';

    public $rules = [
        'title' => 'required',
        'starts_at' => 'required|date',
        'calendar_id' => 'required|exists:zoomyboy_scoutnet_calendars,id',
    ];

    public $casts = ['is_one_day' => 'boolean', 'is_all_day' => 'boolean'];

    /**
     * @var string the database table used by the model
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
        'calendar' => Calendar::class,
    ];
    public $belongsToMany = [
        'keywords' => [Keyword::class, 'table' => 'zoomyboy_scoutnet_event_keyword'],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        'images' => \System\Models\File::class,
    ];

    /**
     * @todo über mysql holen mit DATETIME_FORMAT
     */
    public function getDisplayDateAttribute()
    {
        $start = $this->starts_at->format('d.m.Y');
        $start .= $this->starts_at->startOfDay()->notEqualTo($this->ends_at->startOfDay())
            ? ' - '.$this->ends_at->format('d.m.Y')
            : '';

        return $start;
    }

    /**
     * @todo über mysql holen mit DATETIME_FORMAT
     */
    public function getDisplayTimeAttribute()
    {
        return '00:00:00' != $this->starts_at->format('H:i:s') || '00:00:00' != $this->ends_at->format('H:i:s')
            ? $this->starts_at->format('H:i').' - '.$this->ends_at->format('H:i')
            : '';
    }

    public function scopeWithIsOneDay($q)
    {
        $q->select('*');
        $oneDayQuery = 'DATE_FORMAT(starts_at, "%d%m%Y") = DATE_FORMAT(ends_at, "%d%m%Y")';
        $q->selectSub($oneDayQuery, 'is_one_day');
    }

    public function scopeWithIsAllDay($q)
    {
        $q->select('*');
        $allDayQuery = 'DATE_FORMAT(starts_at, "%T") = DATE_FORMAT(ends_at, "%T")';
        $q->selectSub($allDayQuery, 'is_all_day');
    }

    public function scopeRelevant(Builder $query): Builder
    {
        return $query
            ->whereBetween('starts_at', [now()->subYear()->startOfYear(), now()->addYear()->endOfYear()])
            ->whereHas('calendar', fn ($query) => $query->where('is_main', true))
            ->orderBy('starts_at');
    }

    public function getIcalStartAttribute()
    {
        return $this->starts_at;
    }

    public function getIcalEndAttribute()
    {
        return $this->ends_at ?: $this->starts_at->addMinutes(15);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($event) {
            // Queue::push(PushToApi::class, [
            //     'event_id' => $event->id,
            //     'original' => $event->getOriginal(),
            //     'user_id' => null
            // ]);
        });

        static::updated(function ($event) {
            // Queue::push(PushToApi::class, [
            //     'event_id' => $event->id,
            //     'original' => $event->getOriginal(),
            //     'user_id' => null
            // ]);
        });
    }
}
