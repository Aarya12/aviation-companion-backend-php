<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    protected $guarded = [];
    public function scopeSimpleStudentDetails($query)
    {
        return $query->select(['id', 'datetime', 'tags','note', 'private_note', 'total_hours']);
    }
    public function scopeSimpleDetails($query)
    {
        return $query->select(['id','student_id','instructor_id','datetime','tags','note','private_note','total_hours','created_at','updated_at']);
    }
    public function instructor(){
        return $this->belongsTo(User::class,'instructor_id','id')->simpleDetails();
    }

    public function getInstructorIdAttribute($val)
    {
        $user = User::where('id', $val)->simpleStudentDetails()->first();
        return $user;
    }
}
