<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{

    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
    ];

    public $translatable = ['name'];

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'materials_to_tags');
    }
}
