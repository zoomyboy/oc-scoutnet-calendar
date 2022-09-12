<?php

namespace Zoomyboy\Scoutnet\Models;

use Model;

/**
 * Tag Model.
 */
class Tag extends Model
{
    /**
     * @var string the database table used by the model
     */
    public $table = 'zoomyboy_scoutnet_tags';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title'];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'keywords' => [Keyword::class, 'table' => 'zoomyboy_scoutnet_keyword_tag'],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public static function forSelect(): array
    {
        return static::pluck('title', 'id')->toArray();
    }
}
