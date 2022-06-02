<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InstuctorDetails extends Model
{
    protected $guarded = [];

    public function students(){
        return $this->belongsTo(User::class,'student_id','id')->simpleStudentDetails();
    }

    public function instructor(){
        return $this->belongsTo(User::class,'instructor_id','id')->simpleStudentDetails();
    }
    // public function getStudentIdAttribute($val)
    // {
    //     $user = User::where('id', $val)->simpleStudentDetails()->first();
    //     return $user;
    // }
    // public function getInstructorIdAttribute($val)
    // {
    //     $user = User::where('id', $val)->simpleStudentDetails()->first();
    //     return $user;
    // }
    public function scopeSimpleDetails($query)
    {
        return $query->select(['id','student_id','instructor_id']);
    }
}
