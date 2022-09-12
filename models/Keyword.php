<?php

namespace Zoomyboy\Scoutnet\Models;

use Model;

/**
 * Calendar Model.
 */
class Keyword extends Model
{
    /**
     * @var string the database table used by the model
     */
    public $table = 'zoomyboy_scoutnet_keywords';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'scoutnet_id'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'calendar' => Calendar::class,
    ];
    public $belongsToMany = [
        'events' => [Event::class, 'table' => 'zoomyboy_scoutnet_event_keyword'],
        'tags' => [Tag::class, 'table' => 'zoomyboy_scoutnet_keyword_tag'],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        'images' => \System\Models\File::class,
    ];
}
