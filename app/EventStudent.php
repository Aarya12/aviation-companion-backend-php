<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventStudent extends Model
{
    protected $guarded = [];

    public function scopeSimpleDetails($query)
    {
        return $query->select(['id','student_id', 'event_id']);
    }
    public function scopeSimpleEmailDetails($query)
    {
        return $query->select(['id','email', 'event_id']);
    }
    public function student()
    {
        return $this->belongsTo(User::class,'student_id','id')->simpleStudentDetails();
    }

    public function getStudentIdAttribute($val)
    {
        $user = User::where('id', $val)->simpleStudentDetails()->first();
        return $user;
    }

    public function eventDetail(){
        return $this->belongsTo(Event::class,'event_id','id')->simpleDetails();
    }
    // public function getEventIdAttribute($val)
    // {
    //     //dd($val);
    //     $user = Event::where('id', $val)->simpleDetails()->first();
    //     return $user;
    // }
}
