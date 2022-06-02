<?php

namespace App\Http\Controllers\Api\V1;


use App\DeviceToken;
use App\User;
use App\Airport;
use App\Event;
use App\EventStudent;
use App\InstuctorDetails;
use App\StudentNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PDF;
use Storage;

class UserController extends ResponseController
{

    public function getProfile()
    {
        $this->sendResponse(200, __('api.succ'), $this->get_user_data());
    }

    public function logout(Request $request)
    {
        DeviceToken::where('token', get_header_auth_token())->delete();
        $this->sendResponse(200, __('api.succ_logout'), false);
    }

    public function update_name(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'first_name' => ['required', 'max:100'],
            'last_name' => ['required', 'max:100'],
        ]);
        $user_data->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);
        $this->sendResponse(200, __('api.succ_name_update'), $this->get_user_data());
    }

    public function update_email(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at')],
        ]);
        $user_data->update([
            'email' => $request->email,
        ]);
        $this->sendResponse(200, __('api.succ_email_update'), $this->get_user_data());
    }

    public function update_mobile_number(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'mobile' => ['required', 'integer', Rule::unique('users')->where('country_code', $request->country_code)->ignore($user_data->id)->whereNull('deleted_at')],
            'country_code' => ['required'],
        ], [
            'mobile.unique' => __('api.err_mobile_is_exits'),
        ]);
        $user_data->update([
            'mobile' => $request->mobile,
            'country_code' => $request->country_code,
        ]);
        $this->sendResponse(200, __('api.succ_number_update'), $this->get_user_data());
    }

    public function update_profile_image(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'profile_image' => ['required', 'file', 'image'],
        ]);
        $up = $this->upload_file('profile_image', 'user_profile_image');
        if ($up) {
            un_link_file($user_data->profile_image);
            $user_data->update(['profile_image' => $up]);
            $this->sendResponse(200, __('api.succ_profile_picture_update'), $this->get_user_data());
        } else {
            $this->sendError(412, __('api.errr_fail_to_upload_image'));
        }
    }

    public function edit_profile(Request $request)
    {
        $user = $request->user();
        $profile_image = $user->getRawOriginal('profile_image');
        $rules = [
            'email' => ['required', 'email', Rule::unique('users')->whereNot('id', $user->id)->whereNull('deleted_at')],
            'name' => ['required', 'max:20'],
            'profile_image' => ['nullable', 'file', 'image'],
            'role'=>['required','in:student,instructor'],
            'approx_hours'=>['required_if:role,instructor'],
            'experience_in_year'=>['required_if:role,instructor'],
            'rate_per_hour'=>['required_if:role,instructor'],
            'airport_id'=>['required_if:role,instructor'],
            'certificate_id'=>['required_if:role,student','numeric',Rule::exists('certificates', 'id')->where('id', $request->certificate_id)],
        ];

        $this->directValidation($rules);
        if ($request->hasFile('profile_image')) {
            $up = $this->upload_file('profile_image', 'user_profile_image');
            if ($up) {
                un_link_file($profile_image);
                $profile_image = $up;
            }
        }
        $airport = Airport::where('id', $request->airport_id)->first();
        if(isset($request->airport_id) && $request->airport_id > 0){
            $latitude = $airport->lat;
            $longitude = $airport->lng;
        }else{
            $latitude = '';
            $longitude = '';
        }
        if($request->role == 'student'){
            $user->update([

                'name' => $request->name,
                'email' => $request->email,
                'profile_image' => $profile_image,
                'country_code' => $request->country_code ?? '',
                'mobile' => $request->mobile ?? '',
                'ftn' => $request->ftn ?? '',
                'home_airport_id' => $request->home_airport_id,
                'certificate_id' => $request->certificate_id
            ]);
        }else{
            $user->update([

                'name' => $request->name,
                'email' => $request->email,
                'profile_image' => $profile_image,
                'approx_hours' => $request->approx_hours,
                'experience_in_year' => $request->experience_in_year,
                'rate_per_hour' => $request->rate_per_hour,
                'airport_id' => $request->airport_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'back_story' => $request->back_story,

            ]);
        }

        $msg = __('api.succ_profile_updated');
        $this->sendResponse(200, $msg, $this->get_user_data());
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $this->directValidation([
            'old_password' => ['required'],
            'password' => ['required', 'same:confirm_password'],
            'confirm_password' => ['required'],
        ]);

        $user = User::find($user->id);
        if (Hash::check($request->old_password, $user->password)) {
            $user = User::where('id', $user->id)->first();
            $user->password = $request->password;
            if ($user->save()) {
                $message = "Password updated successfully";
                return $this->sendResponse(200, $message);
            } else {
                $error = "something went wrong while updating password";
                return $this->sendResponse(412, $error);
            }
        } else {
            $error = "Old Password incorrect";
            $this->sendResponse(412, $error);
        }
    }

    public function switchRole(Request  $request){
        $user = $request->user();
        $this->directValidation([
            'new_role' => ['required','in:student,instructor'],
        ]);

        if($request->new_role==$user->type){
            $this->sendResponse(412, __('api.err_entered_new_role_is_incorrect'));
        }else{
            $user = User::where('id', $user->id)->first();
            $user->type = $request->new_role;
            if ($user->save()) {
                return $this->sendResponse(200, __('api.succ_user_role_updated'));
            } else {
                $error = "something went wrong while updating role";
                return $this->sendResponse(412, $error);
            }
        }

    }

    public function getUserProfile(Request $request)
    {
        $this->directValidation([
            'user_id' => ['required','numeric',Rule::exists('users', 'id')->where('id', $request->user_id)],
        ]);
        $user=User::with(['airport','certificates'])->where('id',$request->user_id)->simpleAirportAndCertificateStudentDetails()->first();
        //dd($user->name);

        $user->back_story = ($user->back_story==null)?'':$user->back_story;
        $user->airport_id = ($user->airport_id==null)?'':$user->airport_id;
        $user->certificate_id = ($user->certificate_id==null)?'':$user->certificate_id;
        $user->airport_data = ($user->airport==null)?'':$user->airport;
        $user->certificates_data = ($user->certificates==null)?'':$user->certificates;
        unset($user->airport);
        unset($user->certificates);
        $this->sendResponse(200, __('api.succ'), $user);
    }

    public function myEventsList(Request $request)
    {
        $this->directValidation([
            'type' => ['required'],
        ]);
        $user=$request->user();
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        $search = $request->search;
        //dd($search);
        $current_date = general_date(date('Y-m-d H:i:s'));
        $myEventsList = [];
        //dd($user);
        if($user->type == 'student'){
            $myEventsList = EventStudent::where(['student_id'=>$user->id]);
            if($request->type == 'upcoming'){
                //dd($current_date);
                if($search != ''){
                    $myEventsList->whereHas('eventDetail', function ($query) use ($request, $current_date, $search) {
                        $query->where('datetime','>=' ,$current_date);
                        $query->where('agenda', 'like', "%$search%")
                        ->orWhere('country_name', 'like', "%$search%");
                    });
                }else{
                    $myEventsList->whereHas('eventDetail', function ($query) use ($request, $current_date, $search) {
                        $query->where('datetime','>=' ,$current_date);
                    });
                }

            }else{
                //dd($user->id);
                if($search != ''){
                    $myEventsList->whereHas('eventDetail', function ($query) use ($request, $current_date, $search) {
                        $query->where('datetime','<' ,$current_date);
                        $query->where('agenda', 'like', "%$search%")
                        ->orWhere('country_name', 'like', "%$search%");
                    });
                }else{
                    $myEventsList->whereHas('eventDetail', function ($query) use ($request, $current_date, $search) {
                        $query->where('datetime','<' ,$current_date);
                    });
                }
            }
            $myEventsList = $myEventsList->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
            foreach($myEventsList as $key => $event){
                //dd();
                $myEventsList[$key]['event_detail'] = $event->eventDetail;
                $myEventsList[$key]['event_emails'] = [];
                $myEventsList[$key]['event_detail']['joined_students'] = $event->eventDetail->joined_students;
            }
        }else{


            if($request->type == 'upcoming'){
                $myEventsList = Event::with(['joined_students'])->where('instructor_id',$user->id)->where('datetime','>=',$current_date);
            }else{
                $myEventsList = Event::with(['joined_students'])->where('instructor_id',$user->id)->where('datetime','<',$current_date);
            }

            if($search) {
                $myEventsList->where(function ($query) use ($search) {
                    $query->where('agenda', 'like', "%$search%")
                    ->orWhere('country_name', 'like', "%$search%");
                });
            }
            $myEventsList = $myEventsList->simpleDetails()->limit($limit)->offset($offset)->Orderby('datetime','asc')->get();
            foreach($myEventsList as $key => $event){
                $myEventsList[$key]['event_emails'] = $event->event_emails;
            }
        }
        // $myEventsList->whereHas('joined_students', function ($query) use ($request) {
        //     $query->select('id');
        // });


        if($myEventsList){
            //$myEventsList->joined_students;

            $this->sendResponse(200, __('api.succ'),$myEventsList);
        }else{
            $this->sendResponse(412,  __("api.err_no_my_student_found"));
        }
    }

    public function eventDetail(Request $request){
        $this->directValidation([
            'event_id' => ['required','numeric',Rule::exists('events', 'id')->where('id', $request->event_id)],
        ]);

        //$eventDetail = Event::with(['joined_students'])->where('id',$request->event_id)->simpleDetails()->first();
        $eventDetail = Event::where('id',$request->event_id)->simpleDetails()->first();
        $eventDetail->instructor = $eventDetail->instructor;
        $eventDetail->joined_students = $eventDetail->joined_students;
        $eventDetail->event_emails = $eventDetail->event_emails;
        $this->sendResponse(200, __('api.succ'),$eventDetail);
    }
    public function home(Request $request)
    {
        $data = ['users'=>[],'events'=>[]];
        $user=$request->user();
        $current_date = general_date(date('Y-m-d H:i:s'));
        if($user->type == 'student'){
            $myEventsList = EventStudent::where(['student_id'=>$user->id]);
            $myEventsList->whereHas('eventDetail', function ($query) use ($request, $current_date) {
                $query->where('datetime','>=' ,$current_date)->Orderby('datetime','asc');
            });
            $myInstructorList = InstuctorDetails::where(['student_id'=>$user->id]);
            $myInstructorList = $myInstructorList->simpleDetails()->limit(10)->Orderby('id','desc')->get();
            foreach($myInstructorList as $key => $instructor){
                //dd();
                $notes_count = StudentNote::where(['student_id'=>$user->id,'instructor_id'=>$instructor->instructor_id])->count();
                $instructor->instructor->total_notes = $notes_count;
                $instructor->instructor->certificates = "";
                $myInstructorList[$key]['user'] = $instructor->instructor;
            }
            $data['users'] = $myInstructorList;
            $myEventsList = $myEventsList->simpleDetails()->limit(10)->get();
            foreach($myEventsList as $key => $event){
                //dd();
                $myEventsList[$key]['event_detail'] = $event->eventDetail;
                $myEventsList[$key]['event_detail']['joined_students'] = $event->eventDetail->joined_students;
            }
            $data['events'] = $myEventsList;
        }else{
            $myEventsList = Event::with(['joined_students'])->where('instructor_id',$user->id)->where('datetime','>=',$current_date);
            $myStudentList = InstuctorDetails::where(['instructor_id'=>$user->id]);
            $myStudentList = $myStudentList->simpleDetails()->limit(10)->Orderby('id','desc')->get();

            foreach($myStudentList as $key => $student){
                $notes_count = StudentNote::where(['student_id'=>$student->students->id,'instructor_id'=>$user->id])->count();
                $student->students->total_notes = $notes_count;
                $user_data = User::where('id',$student->students->id)->simpleAirportAndCertificateStudentDetails()->first();

                $student->students->certificates = $user_data->certificates;
                $myStudentList[$key]['user'] = $student->students;
            }
            $data['users'] = $myStudentList;
            $data['events'] = $myEventsList->simpleDetails()->limit(10)->Orderby('datetime','asc')->get();
        }


        $this->sendResponse(200, __('api.succ'),$data);
    }
    public function deleteEvent(Request $request){
        $this->directValidation([
            'event_id' => ['required','numeric',Rule::exists('events', 'id')->where('id', $request->event_id)],
        ]);
        $instructor=$request->user();
        Event::where(['id'=>$request->event_id])->delete();
        $this->sendResponse(200, __('api.succ_event_deleted'));

    }
    // Generate PDF
    public function createNotePDF(Request $request) {
        $this->directValidation([
            'note_id' => ['required','numeric',Rule::exists('student_notes', 'id')->where('id', $request->note_id)],
        ]);
        $result = [];
        //dd();
        $data = StudentNote::where(['id'=>$request->note_id])->simpleDetails()->first();

        //$pdf=PDF::loadView('pdf.note_pdf', ['data' => $data]);
        $file_name = 'uploads/pdf/note_pdf_'.$request->note_id.'.pdf';
        //Storage::put($file_name, $pdf->output());
        $pdf_url = url('/').'/'.$file_name;
        $result['pdf_url'] = $pdf_url;
        $this->sendResponse(200, __('api.succ'),$result);
    }
}
