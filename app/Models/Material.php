<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
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
        'book_url',
        'video_player',
        'source_url',
        'source',
        'author_url',
    ];

    protected $casts = [
        'title' => 'array',
        'author' => 'array',
        'short_description' => 'array',
        'source' => 'array',
        'full_text' => 'array',
        'location' => 'array',
    ];

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
