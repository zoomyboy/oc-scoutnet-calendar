<?php namespace Zoomyboy\Scoutnetcalendar\Models;

use Model;
use Zoomyboy\Scoutnetcalendar\Classes\ScoutnetSync;
use Zoomyboy\Scoutnetcalendar\Exceptions\CalendarNotFoundException;
/**
 * Calendar Model
 */
class Calendar extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'zoomyboy_scoutnetcalendar_calendars';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['scoutnet_id', 'name'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

	public static function exists($id) {
		return self::where('scoutnet_id', '=', $id)->first() != null;
	}

	public static function getSelectArray() {
		return self::get()->pluck('name', 'id')->toArray();
	}

	public function beforeSave() {
		try {
			if (self::exists($this->scoutnet_id)) {
				throw new \ValidationException(['scoutnet_id' => 'Kalender existiert bereits']);
			}
			$calendar = ScoutnetSync::fromGroup($this->scoutnet_id);
			$this->name = $calendar->getName();
		} catch(CalendarNotFoundException $e) {
			throw new \ValidationException(['scoutnet_id' => 'There is no group with Calendar ID '.$this->scoutnet_id]);
		}
	}

	public function getEventsAttribute() {
		return ScoutnetSync::fromGroup($this->scoutnet_id)->events();
	}
}
