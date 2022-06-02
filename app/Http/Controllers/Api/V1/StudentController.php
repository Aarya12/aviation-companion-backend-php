<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\StudentRegisterdManually;
use App\Http\Controllers\Api\ResponseController;
use App\InstuctorDetails;
use App\StudentNote;
use App\Listeners\RegisterStudentManually;
use App\Mail\General\UserAddManually;
use App\Providers\SendEmailVerificationNotification;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class StudentController extends ResponseController
{
    public function allInstructorList(Request $request)
    {
        $this->directValidation([
            'latitude' => ['required'],
            'longitude' => ['required'],
        ]);
        $data = [];
        $student=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $search = $request->search;
        $allInstructor=User::select(DB::raw('id,name , name as user_name, profile_image,email,approx_hours,experience_in_year,rate_per_hour,airport_id,( 6367 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latitude ) ) ) ) AS distance'))->where(['type'=>'instructor']);
        if($search) {
            $allInstructor->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhereHas('airport', function ($query) use ($search) {
                    $query->whereRaw('name like "%' . $search . '%"')
                        ->orWhereRaw('local_code like "%' . $search . '%"');
                });
            });
        }
        $allInstructor = $allInstructor->limit($limit)->offset($offset)->Orderby('distance','asc');
        if(isset($request->experience) && !empty($request->experience)){
            $allInstructor->where('experience_in_year', '>=' ,$request->experience);
        }
        if(isset($request->price) && !empty($request->price)){
            $allInstructor->where('rate_per_hour', '<=' ,$request->price);
        }
        $allInstructor=$allInstructor->get();
        //dd($allStudentList);
        //$allStudentList=User::Where('name', 'like', '%' . $request->search . '%')->where(['type'=>'instructor'])->simpleInstructorDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
        foreach ($allInstructor as $key=>$val){

            $exist=InstuctorDetails::where(['student_id'=>$student->id,'instructor_id'=>$val->id])->count();
            if($exist){
                $allInstructor[$key]['is_already_student']=__('api.yes');
            }else{
                $allInstructor[$key]['is_already_student']=__('api.no');
            }
            $allInstructor[$key]['airport'] = $val->airport ?? '';
        }
        $this->sendResponse(200, __('api.succ'),$allInstructor);
    }
    public function myInstructorsList(Request $request)
    {
        $student=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $myInstructorList = InstuctorDetails::where(['student_id'=>$student->id]);
        if (!empty($request->search)) {
            $myInstructorList->whereHas('instructor', function ($query) use ($request) {
                $query->whereRaw('name like "%' . $request->search . '%"')
                    ->orWhereRaw('email like "%' . $request->search . '%"');
            });
        }else{
            $myInstructorList->whereHas('instructor', function ($query) use ($request) {
                $query->select('id','name','email','profile_image');
            });
        }
        $myInstructorList = $myInstructorList->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();

        if($myInstructorList){
            foreach($myInstructorList as $key => $instructor){
                $notes_count = StudentNote::where(['student_id'=>$instructor->instructor->id,'instructor_id'=>$student->id])->count();
                $instructor->instructor->total_notes = $notes_count;
                $myInstructorList[$key]['instructor'] = $instructor->instructor;
            }
            $this->sendResponse(200, __('api.succ'),$myInstructorList);
        }else{
            $this->sendResponse(412,  __("api.err_no_my_student_found"));
        }
    }

    public function myNotes(Request $request)
    {
        $notes = [];
        $student=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $search = $request->search;
        $notes=StudentNote::where(['student_id'=>$student->id]);
        if($search) {
            $notes->where(function ($query) use ($search) {
                $query->where('tags', 'like', "%$search%")
                ->orWhere('note', 'like', "%$search%");
            });
        }
        $notes = $notes->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
        //$notes=StudentNote::Where('tags', 'like', '%' . $request->search . '%')->where(['student_id'=>$student->id])->simpleStudentDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();

        $this->sendResponse(200, __('api.succ'),$notes);
    }

    public function myInstructorNotes(Request $request)
    {
        $this->directValidation([
            'instructor_id' => ['required']
        ]);
        $notes = [];
        $student=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $search = $request->search;
        $notes=StudentNote::where(['student_id'=>$student->id,'instructor_id'=>$request->instructor_id]);
        if($search) {
            $notes->where(function ($query) use ($search) {
                $query->where('tags', 'like', "%$search%")
                ->orWhere('note', 'like', "%$search%");
            });
        }
        $notes = $notes->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
        //$notes=StudentNote::Where('tags', 'like', '%' . $request->search . '%')->where(['student_id'=>$student->id])->simpleStudentDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();

        $this->sendResponse(200, __('api.succ'),$notes);
    }
}
