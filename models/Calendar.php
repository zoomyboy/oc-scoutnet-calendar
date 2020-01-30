<?php namespace Zoomyboy\Scoutnet\Models;

use BackendAuth;
use Model;
use Backend;
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

    public function getHasCredentialsAttribute() {
        return $this->provider && $this->aes_key && $this->aes_iv;
    }

    public function getApiReturnUrlAttribute() {
        return Backend::url('zoomyboy/scoutnet/calendar/callback/'.$this->id);
    }

    public function setLogin($key) {
        $api = $this->getApi();

        $data = $api->getApiKeyFromData();

        $this->credentials()->updateOrCreate(['backend_user_id' => BackendAuth::getUser()->id], array_merge([
            'backend_user_id' => BackendAuth::getUser()->id,
        ], array_only($data, ['api_key', 'user', 'time', 'firstname', 'surname'])));
    }

    public function logout() {
        $this->credentials()->where('backend_user_id', BackendAuth::getUser()->id)->delete();
    }

    public function getIsConnectedAttribute() {
        return $this->currentCredential()->exists();
    }

    public function buttonText($connection) {
        return $this->isConnected ? 'zoomyboy.scoutnet::api.'.$connection.'.connected' : 'zoomyboy.scoutnet::api.'.$connection.'.connect';
    }

    public function getApi() {
        return app('scoutnet.api')->group($this->scoutnet_id);
    }

    public function keyOf(BackendUser $user) {
        return $this->credentials()->where('backend_user_id', $user->id)->first()->auth_key;
    }

}
