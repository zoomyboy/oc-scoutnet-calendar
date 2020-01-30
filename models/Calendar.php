<?php namespace Zoomyboy\Scoutnet\Models;

use BackendAuth;
use Model;
use Zoomyboy\Scoutnet\Models\Event;
use ScoutNet\Api\ScoutnetException;
use ScoutNet\Api\ScoutnetApi;
use Backend\Models\User as BackendUser;
use October\Rain\Database\Traits\Sortable;
use Zoomyboy\Scoutnet\Classes\ScoutnetSync;
use \October\Rain\Database\Traits\Validation;

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

    public function pullEvents() {
        $group = ScoutnetSync::fromGroup($this->scoutnet_id);
        $this->update(['title' => $group->getName()]);

        $events = $group->events()->ofYears(range(date('Y')-1, date('Y')+1))
            ->get();

        $events->each(function($event) {
            Event::createFromScoutnet($event);
        });
    }

    public function connectionService($connection) {
        $cls = '\\Zoomyboy\\Scoutnet\\Classes\\'.studly_case($connection);
        return $cls::fromCalendar($this);
    }
}
