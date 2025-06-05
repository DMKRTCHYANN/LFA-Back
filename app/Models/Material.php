<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Spatie\Translatable\HasTranslations;

class Material extends Model
{
    use HasFactory, HasTranslations, HasSpatial;

    protected $fillable = [
        'language_id',
        'topic_id',
        'country_id',
        'poster',
        'tags',
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
        'title' => 'array',
        'tags' => 'array',
        'author' => 'array',
        'short_description' => 'array',
        'full_text' => 'array',
        'source' => 'array',
    ];

    public $translatable = ['title', 'author', 'short_description', 'full_text', 'source'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'materials_to_tags');
    }

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
}
