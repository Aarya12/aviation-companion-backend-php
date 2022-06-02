<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $guarded = [];

    public function scopeSimpleDetails($query)
    {
        return $query->select(['id','name']);
    }
}
