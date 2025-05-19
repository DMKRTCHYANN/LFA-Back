<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Spatie\Translatable\HasTranslations;

class Material extends Model
{
    use HasFactory, HasTranslations;
    use HasSpatial;


    protected $fillable = [
        'language_id',
        'topic_id',
        'country_id',
        'poster',
        'title',
        'author',
        'short_description',
        'start_year',
        'end_year',
        'medium',
        'full_text',
        'location',
        'book_url',
        'video',
        'source_url',
        'source',
        'author_url',
    ];


    protected $casts = [
        'location' => Point::class,
    ];

    public $translatable = ['title','author','short_description','tags','full_text','source',];


    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'materials_to_tags');
    }
}
