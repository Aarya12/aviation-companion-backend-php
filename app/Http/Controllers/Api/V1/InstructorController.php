<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\StudentRegisterdManually;
use App\Http\Controllers\Api\ResponseController;
use App\InstuctorDetails;
use App\StudentNote;
use App\Event;
use App\EventStudent;
use App\Listeners\RegisterStudentManually;
use App\Mail\General\UserAddManually;
use App\Providers\SendEmailVerificationNotification;
use App\User;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use PDF;
use Storage;

class InstructorController extends ResponseController
{
    public function allStudentsList(Request $request)
    {
        $instructor=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $search = $request->search;
        $allStudentList=User::where(['type'=>'student']);
        if($search) {
            $allStudentList->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }
        $allStudentList = $allStudentList->simpleStudentDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
        foreach ($allStudentList as $key=>$val){
            $exist=InstuctorDetails::where(['instructor_id'=>$instructor->id,'student_id'=>$val->id])->count();
            if($exist){
                $allStudentList[$key]['is_already_student']=__('api.yes');
            }else{
                $allStudentList[$key]['is_already_student']=__('api.no');
            }
        }
        if($allStudentList){
            $this->sendResponse(200, __('api.succ'),$allStudentList);
        }else{
            $this->sendResponse(412, "Students not found");
        }
    }

    public function addStudent(Request $request){
        $this->directValidation([
            'student_id' => ['required','numeric',Rule::exists('users', 'id')->where('id', $request->student_id)->where('type','student')],
        ]);
        $instructor=$request->user();
        $exist=InstuctorDetails::where(['instructor_id'=>$instructor->id,'student_id'=>$request->student_id])->count();
        if(!$exist){
            $details=New InstuctorDetails();
            $details->student_id=$request->student_id;
            $details->instructor_id=$instructor->id;
            if($details->save()){
                $this->sendResponse(200, __('api.succ_student_added'));
            }else{
                $this->sendResponse(412, __("api.err_something_went_wrong"));
            }
        }else{
            $this->sendResponse(412, __("api.err_already_assigned_student_for_this_instructor"));
        }


    }

    public function addStudentNote(Request $request){
        $instructor=$request->user();
        $this->directValidation([
            'student_id' => ['required','numeric',Rule::exists('instuctor_details', 'student_id')->where('student_id', $request->student_id)->where('instructor_id',$instructor->id)],
            'datetime' => ['required'],
            'total_hours' => ['required'],
            'note' => ['required'],
        ]);

        $note = StudentNote::updateOrCreate(
            ['id' => $request->note_id],
            [
                'student_id' => $request->student_id,
                'datetime' => general_date($request->datetime),
                'instructor_id' => $instructor->id,
                'tags' => $request->tags,
                'note' => $request->note,
                'private_note' => $request->private_note ?? "",
                'total_hours' => $request->total_hours,
                'created_at' => general_date(date('Y-m-d')),
                'updated_at' => general_date(date('Y-m-d'))
            ]
        );
        if($request->tags != ''){
            $tags = explode(',',$request->tags);
            foreach($tags as $tag){
                $exist=Tag::where(['tag'=>$tag])->first();
                if($exist){
                    $exist->update(['use_count' => $exist->use_count + 1]);
                }else{
                    Tag::Create([
                        'tag' => $tag,
                        'use_count' => 1
                    ]);
                }
            }
        }
        if(isset($request->note_id) && $request->note_id > 0){
            $un_link = 'uploads/pdf/note_pdf_'.$note->id.'.pdf';
            un_link_file($un_link);
        }
        $data = StudentNote::where(['id'=>$note->id])->simpleDetails()->first();
        $data->name = $instructor->name;
        $pdf=PDF::loadView('pdf.note_pdf', ['data' => $data]);
        $file_name = 'uploads/pdf/note_pdf_'.$note->id.'.pdf';
        Storage::put($file_name, $pdf->output());
        $pdf_url = url('/').'/'.$file_name;
        $result['pdf_url'] = $pdf_url;
        if(isset($request->note_id) && $request->note_id > 0){
            $this->sendResponse(200, __('Student note successfully updated'),$result);
        }else{
            $this->sendResponse(200, __('api.succ_note_added'),$result);
        }

    }

    public function deleteStudentNote(Request $request){
        $this->directValidation([
            'note_id' => ['required','numeric',Rule::exists('student_notes', 'id')->where('id', $request->note_id)],
        ]);
        $instructor=$request->user();
        StudentNote::where(['id'=>$request->note_id])->delete();
        $this->sendResponse(200, __('api.succ_note_deleted'));

    }

    public function getNoteDetails(Request $request){
        $this->directValidation([
            'note_id' => ['required','numeric',Rule::exists('student_notes', 'id')->where('id', $request->note_id)],
        ]);
        $instructor=$request->user();
        $note = StudentNote::where(['id'=>$request->note_id])->simpleDetails()->first();
        $file_name = 'uploads/pdf/note_pdf_'.$note->id.'.pdf';
        $pdf_url = url('/').'/'.$file_name;
        $note->pdf_url = $pdf_url;

        $this->sendResponse(200, __('api.succ_note_deleted'),$note);

    }

    public function removeStudent(Request $request){
        $this->directValidation([
            'student_id' => ['required','numeric',Rule::exists('users', 'id')->where('id', $request->student_id)->where('type','student')],
        ]);
        $instructor=$request->user();
        $exist=InstuctorDetails::where(['instructor_id'=>$instructor->id,'student_id'=>$request->student_id])->count();
        if($exist){
            InstuctorDetails::where(['instructor_id'=>$instructor->id,'student_id'=>$request->student_id])->delete();
            $this->sendResponse(200, __('api.succ_student_removed'));
        }else{
            $this->sendResponse(412, __("api.err_student_not_listed"));
        }


    }

    public function studentNotes(Request $request)
    {
        $this->directValidation([
            'student_id' => ['required','numeric',Rule::exists('users', 'id')->where('id', $request->student_id)->where('type','student')],
        ]);
        $notes = [];
        $instructor=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $notes=StudentNote::Where('tags', 'like', '%' . $request->search . '%')->where(['student_id'=>$request->student_id,'instructor_id'=>$instructor->id])->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();

        $this->sendResponse(200, __('api.succ'),$notes);
    }

    public function myStudentsList(Request $request)
    {
        $instructor=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $search = $request->search;
        //dd($search);
        $myStudentList = InstuctorDetails::where(['instructor_id'=>$instructor->id]);
        if (!empty($request->search)) {
            $myStudentList->whereHas('students', function ($query) use ($request) {
                $query->whereRaw('name like "%' . $request->search . '%"')
                    ->orWhereRaw('email like "%' . $request->search . '%"');
            });
        }else{
            $myStudentList->whereHas('students', function ($query) use ($request) {
                $query->select('id','name','email','profile_image');
            });
        }
        $myStudentList = $myStudentList->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();

        if($myStudentList){
            foreach($myStudentList as $key => $student){
                $notes_count = StudentNote::where(['student_id'=>$student->students->id,'instructor_id'=>$instructor->id])->count();
                $student->students->total_notes = $notes_count;
                $myStudentList[$key]['students'] = $student->students;
            }
            $this->sendResponse(200, __('api.succ'),$myStudentList);
        }else{
            $this->sendResponse(412,  __("api.err_no_my_student_found"));
        }
    }

    /*
    -------------------------------------------------------------------------------------
    -------------------While student will add by custom name and email-------------------
    -------------------------------------------------------------------------------------
    */
    public function addStudentViaEmail(Request $request){
        $this->directValidation([
            'name' => ['required','max:255'],
            'email' => ['required','max:255','email',Rule::unique('users', 'email')],
            'certificate_id' => ['required'],
        ],
        [
            'email.unique' => __('api.err_email_is_already_in_student'),
        ]
        );

        $instructor=$request->user();
        $userDetails=array('name'=>$request->name,'email'=>$request->email,'certificate_id'=>$request->certificate_id,'role'=>'student');
        $userData=event(new StudentRegisterdManually($instructor,$userDetails));
           if(is_array($userData)){
               $user=User::find($userData[0]);
               Mail::to($request->email)->send(new UserAddManually($instructor,$user));
               $details=New InstuctorDetails();
               $details->student_id=$user->id;
               $details->instructor_id=$instructor->id;
               if($details->save()){
                   $this->sendResponse(200, __('api.succ_student_added'));
               }else{
                   $this->sendResponse(412, __("api.err_something_went_wrong"));
               }
           }else{
               $this->sendResponse(412, __("api.err_something_went_wrong"));
           }
    }

}
