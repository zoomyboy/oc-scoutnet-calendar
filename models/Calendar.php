<?php namespace Zoomyboy\Scoutnet\Models;

use \October\Rain\Database\Traits\Validation;
use Backend\Models\User as BackendUser;
use BackendAuth;
use Model;
use October\Rain\Database\Traits\Sortable;
use ScoutNet\Api\ScoutnetApi;
use ScoutNet\Api\ScoutnetException;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;
use Zoomyboy\Scoutnet\Models\Event;

/**
 * Calendar Model
 */
class Calendar extends Model
{
    use Sortable;
    use Validation;

    public static $icon = 'oc-icon-calendar';
    public static $tabTitle = 'zoomyboy.scoutnet::lang.newCalendar';

    public $rules = [
        'title' => 'required',
        'scoutnet_id' => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'zoomyboy_scoutnet_calendars';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['scoutnet_id', 'title'];

    /**
     * @var array Relations
     */
    public $hasOne = [
        'currentCredential' => [Credential::class, 'scope' => 'currentUser']
    ];
    public $hasMany = [
        'events' => [Event::class],
        'credentials' => [Credential::class]
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function scoutnetSync() {
        return ScoutnetSync::fromGroup($this->scoutnet_id, $this);
    }

    public static function forSelect(): array
    {
        return self::pluck('title', 'id')->toArray();
    }

    public function connectionService($connection) {
        $cls = '\\Zoomyboy\\Scoutnet\\Classes\\'.studly_case($connection);
        return $cls::fromCalendar($this);
    }

    public function getGoogleCalendarOptions() {
        return $this->connectionService('google_calendar')->getCalendars();
    }
}
