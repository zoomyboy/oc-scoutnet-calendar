<?php namespace Zoomyboy\Scoutnet\Models;

use Model;
use Zoomyboy\Scoutnet\Models\Event;
use October\Rain\Database\Traits\Sortable;

/**
 * Calendar Model
 */
class Calendar extends Model
{
    use Sortable;

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
    protected $fillable = ['scoutnet_id', 'name'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'events' => [Event::class]
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
