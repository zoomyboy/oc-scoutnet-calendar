<?php namespace Zoomyboy\Scoutnet\Models;

use Model;
use BackendAuth;
use Backend\Models\User;
use ScoutNet\Api\ScoutnetApi;

class Credential extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'zoomyboy_scoutnet_credentials';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['backend_user_id', 'data', 'calendar_id', 'connection'];

    public $jsonable = ['data'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'backendUser' => [ User::class ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function scopeCurrentUser($q) {
        return $q->where('backend_user_id', BackendAuth::getUser()->id);
    }

    public function configure(ScoutnetApi $api) {
        $api->loginUser($this->user, $this->api_key);
    }
}
