<?php namespace Zoomyboy\Scoutnet\Models;

use Model;
use Backend\Models\User;

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
    protected $fillable = ['backend_user_id', 'auth_key'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'user' => [ User::class ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
