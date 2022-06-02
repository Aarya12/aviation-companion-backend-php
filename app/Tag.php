<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [];

    public function scopeSimpleDetails($query)
    {
        return $query->select(['id','tag']);
    }
}
