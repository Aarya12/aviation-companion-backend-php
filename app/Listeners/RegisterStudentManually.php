<?php

namespace App\Listeners;

use App\Events\StudentRegisterdManually;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class RegisterStudentManually
{

    public function handle(StudentRegisterdManually $event)
    {
        $userDetails=$event->userDetails;
        $user = User::create([
            'name' => $userDetails['name'],
            'email' => $userDetails['email'],
            'certificate_id' => $userDetails['certificate_id'],
            'password' => config('constants.default.user_password'),
            'username' => '',
            'type'=>$userDetails['role'],
            'country_code'=>"",
            'mobile'=>"",
            'profile_image' => config('constants.default.user_image'),
        ]);
        if ($user) {
            return $user->id;
        }else{
            return false;
        }

    }
}
