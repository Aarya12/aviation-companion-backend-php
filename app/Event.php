<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];

    public function location(){
        return $this->belongsTo(Airport::class,'location','id')->simpleDetails();
    }
    public function getLocationAttribute($val)
    {
        $user = Airport::where('id', $val)->simpleDetails()->first();
        return $user;
    }
    public function joined_students()
    {
        return $this->hasMany(EventStudent::class,'event_id','id')->where('student_id','!=',null)->simpleDetails();
    }
    public function event_emails()
    {
        return $this->hasMany(EventStudent::class,'event_id','id')->where('type','email')->simpleEmailDetails();
    }
    public function scopeSimpleDetails($query)
    {
        return $query->select(['id','instructor_id', 'datetime', 'agenda', 'description', 'location', 'latitude', 'longitude', 'country_name', 'country_code', 'mobile']);
    }
    public function instructor()
    {
        return $this->belongsTo(User::class,'instructor_id','id')->simpleStudentDetails();
    }
}
