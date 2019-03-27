<?php namespace Zoomyboy\Scoutnet\Models;

use Model;
use Zoomyboy\Scoutnet\Models\Keyword;
use Zoomyboy\Scoutnet\Models\Calendar;
use \October\Rain\Database\Traits\Validation;
/**
 * Calendar Model
 */
class Event extends Model
{
    use Validation;

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
}
