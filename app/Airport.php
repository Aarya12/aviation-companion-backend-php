<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $guarded = [];

    public function scopeSimpleDetails($query)
    {
        return $query->select(['id', 'name', 'lat', 'lng', 'unq_id', 'ident', 'local_code', 'type', 'iso_country', 'iso_region']);
    }
}
