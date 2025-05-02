<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'materials_to_tags');
    }
}
