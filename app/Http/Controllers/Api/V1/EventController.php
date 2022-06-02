<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\StudentRegisterdManually;
use App\Http\Controllers\Api\ResponseController;
use App\InstuctorDetails;
use App\StudentNote;
use App\Listeners\RegisterStudentManually;
use App\Mail\General\UserAddManually;
use App\Mail\General\EventInfo;
use App\Providers\SendEmailVerificationNotification;
use App\User;
use App\Event;
use App\EventStudent;
use App\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EventController extends ResponseController
{
    public function addEditEvent(Request $request){
        $this->directValidation([
            'datetime' => ['required'],
            'agenda' => ['required'],
            'location' => ['required','numeric',Rule::exists('airports', 'id')->where('id', $request->location)],
        ]);
        $instructor=$request->user();
        $location = Airport::where(['id' => $request->location])->first();
        $event = Event::updateOrCreate(
            ['id' => $request->event_id],
            [
                'instructor_id' => $instructor->id,
                'datetime' => general_date($request->datetime),
                'agenda' => $request->agenda,
                'description' => $request->description,
                'location' => $request->location,
                'latitude' => $location->lat,
                'longitude' => $location->lng,
                'country_name' => $request->country_name,
                'country_code' => $request->country_code,
                'mobile' => $request->mobile,
            ],
        );
        if(count($request->students) > 0){
            foreach($request->students as $student_id){
                $exist = EventStudent::where(['student_id' => $student_id,'event_id' => $event->id])->first();

                if($exist == null){
                    EventStudent::create([
                        'event_id' => $event->id,
                        'student_id' => $student_id,
                        'type' => 'student'
                    ]);
                    $user_email = User::where('id',$student_id)->first();
                    Mail::to($user_email['email'])->send(new EventInfo($instructor,$event,$location));
                }
            }
        }
        if(count($request->emails) > 0){
            foreach($request->emails as $email){

                $email_sent = Mail::to($email)->send(new EventInfo($instructor,$event,$location));
                //dd($email_sent);
                $exist = EventStudent::where(['email' => $email,'event_id' => $event->id])->first();
                if($exist == null){
                    $user = User::where(['email' => $email])->first();
                    if($user){
                        $exist_user = EventStudent::where(['student_id' => $user->id,'event_id' => $event->id])->first();
                        if($exist_user == null){
                            EventStudent::create([
                                'event_id' => $event->id,
                                'student_id' => $user->id,
                                'email' => $email,
                                'type' => 'email'
                            ]);
                        }
                    }else{
                        EventStudent::create([
                            'event_id' => $event->id,
                            'email' => $email,
                            'type' => 'email'
                        ]);
                    }

                }
            }
        }
        if(isset($request->event_id) && $request->event_id > 0){
            $this->sendResponse(200, __('api.succ_event_update'));
        }else{
            $this->sendResponse(200, __('api.succ_event_added'));
        }

    }


}
