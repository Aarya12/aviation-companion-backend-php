<?php

namespace App\Http\Controllers\Api\V1;

use App\Airport;
use App\Certificate;
use App\Content;
use App\Http\Controllers\Api\ResponseController;
use App\DeviceToken;
use App\SocialAccount;
use App\User;
use App\Tag;
use App\EventStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class GuestController extends ResponseController
{

    public function login(Request $request)
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
            //'role'=>['required','in:student,instructor'],
            'push_token' => ['nullable'],
            'device_type' => ['required', 'in:android,ios'],
            'device_id' => ['required', 'max:255'],
        ];
        $messages = ['email.exists' => __('api.err_email_not_register')];
        $this->directValidation($rules, $messages);
        $attempt = ['email' => $request->email, 'password' => $request->password, 'status' => 'active'];
        if (Auth::attempt($attempt)) {
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }

    public function signup(Request $request)
    {

        $valid = $this->directValidation([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required','same:confirm_password'],
            'confirm_password'=>['required'],
            'role'=>['required','in:student,instructor'],
            'approx_hours'=>['required_if:role,instructor'],
            'experience_in_year'=>['required_if:role,instructor'],
            'rate_per_hour'=>['required_if:role,instructor'],
            'airport_id'=>['required_if:role,instructor'],
            'certificate_id'=>['required_if:role,student'],
            'device_id' => ['required', 'max:255'],
            'device_type' => ['required', 'in:android,ios'],
            'push_token' => ['nullable'],
        ], [
            'email.unique' => __('api.err_email_is_exits'),
        ]);
        $airport = Airport::where('id', $request->airport_id)->first();
        if(isset($request->airport_id) && $request->airport_id > 0){
            $latitude = $airport->lat;
            $longitude = $airport->lng;
        }else{
            $latitude = '';
            $longitude = '';
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'username' => '',
            'approx_hours' => $request->approx_hours,
            'type'=>$request->role,
            'experience_in_year' => $request->experience_in_year,
            'rate_per_hour' => $request->rate_per_hour,
            'airport_id' => $request->airport_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'certificate_id' => $request->certificate_id,
            'country_code'=>"",
            'mobile'=>"",
            'profile_image' => config('constants.default.user_image'),
        ]);
        if ($user) {
            //$event_student = EventStudent::where('email', $request->email)->first();
            EventStudent::where('email', $request->email)->update(['student_id' => $user->id]);
            Auth::loginUsingId($user->id);
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_register'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_something_went_wrong'), false);
        }

    }

    public function forgot_password(Request $request)
    {
        $data = User::password_reset($request->email, false);
        $status = $data['status'] ? 200 : 412;
        $this->sendResponse($status, $data['message']);
    }

    public function content(Request $request, $type)
    {
        $data = Content::where('slug', $type)->first();
        return ($data) ? $data->content : "Invalid Content type passed";
    }

    public function check_ability(Request $request)
    {
        $otp = "";
        $type = $request->type;
        $is_sms_need = $request->is_sms_need;
        $rules = [
            'type' => ['required', 'in:username,email,mobile_number'],
            'value' => ['required'],
            'country_code' => ['required_if:type,mobile']
        ];
        $user_id = $request->user_id;
        if ($type == "email") {
            $rules['value'][] = 'email';
            $rules['value'][] = Rule::unique('users', 'email')->ignore($user_id)->whereNull('deleted_at');
        } elseif ($type == "username") {
            $rules['value'][] = 'regex:/^\S*$/u';
            $rules['value'][] = Rule::unique('users', 'username')->ignore($user_id)->whereNull('deleted_at');
        } else {
            $rules['value'][] = 'integer';
            $rules['value'][] = Rule::unique('users', 'mobile')->ignore($user_id)->where('country_code', $request->country_code)->whereNull('deleted_at');
        }
        $this->directValidation($rules, ['regex' => __('api.err_space_not_allowed'), 'unique' => __('api.err_field_is_taken', ['attribute' => str_replace('_', ' ', $type)])]);
        $this->sendResponse(200, __('api.succ'));
    }

    public function version_checker(Request $request)
    {
        $type = $request->type;
        $version = $request->version;
        $this->directValidation([
            'type' => ['required', 'in:android,ios'],
            'version' => 'required',
            'device_id' => ['required', 'max:255'],
            'push_token' => ['required'],
        ]);
        $data = [
            'is_force_update' => ($type == "ios") ? IOS_Force_Update : Android_Force_Update,
        ];
        DeviceToken::updateOrCreate(
            ['device_id' => $request->device_id, 'type' => $request->device_type],
            ['device_id' => $request->device_id, 'type' => $request->device_type, 'push_token' => $request->push_token, 'badge' => 0]
        );
        $check = ($type == "ios") ? (IOS_Version <= $version) : (Android_Version <= $version);
        if ($check) {
            $this->sendResponse(200, __('api.succ'), $data);
        } else {
            $this->sendResponse(412, __('api.err_new_version_is_available'), $data);
        }


    }

    public function check_social_ability(Request $request)
    {
        $user_id = 0;
        $email = $request->email;
        $provider = $request->type;
        $social_id = $request->social_id;
        $this->directValidation([
            'type' => ['required', 'in:facebook,apple,google'],
            'social_id' => ['required'],
            'device_id' => ['required'],
//            'name' => ['required'],
            'device_type' => ['required', 'in:android,ios'],
            'email' => ['nullable', 'email'],
            'push_token' => ['nullable'],
        ]);
        if ($email) {
            $is_user_exits = User::where(['email' => $email])->first();
            if ($is_user_exits) {
                if ($is_user_exits->status == "active") {
                    $user_id = $is_user_exits->id;
                } else {
                    $this->sendResponse(412, __('api.err_account_ban'));
                }
            }
        }
        if (!$user_id) {
            $is_user_exits = SocialAccount::where(['provider' => $provider, 'provider_id' => $social_id])
                ->has('user')->with('user')->first();
            if ($is_user_exits) {
                if ($is_user_exits->user->status == "active") {
                    $user_id = $is_user_exits->user_id;
                } else {
                    $this->sendResponse(412, __('api.err_account_ban'));
                }
            }
        }

//        if (!$user_id) {
//            $user_data = User::create([
//                'name' => $request->name,
//                'email' => $email ?? "",
//                'profile_image' => config('constants.default.user_image'),
//                'referral_code' => User::get_unique_referral_code(),
//            ]);
//            if ($user_data) {
//                $user_id = $user_data->id;
//            } else {
//                $this->sendResponse(412, __('api.err_something_went_wrong'));
//            }
//        }
        if ($user_id) {
            Auth::loginUsingId($user_id);
            Auth::user()->social_logins()->updateOrCreate(
                ['provider' => $provider, 'user_id' => $user_id],
                ['provider' => $provider, 'provider_id' => $social_id]
            );
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
        } else {
            $this->sendResponse(412, __('api.err_please_register_social'));
        }
    }

    public function social_register(Request $request)
    {
        $provider = $request->type;
        $social_id = $request->social_id;
        $this->directValidation([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'role'=>['required','in:student,instructor'],
            'approx_hours'=>['required_if:role,instructor'],
            'experience_in_year'=>['required_if:role,instructor'],
            'rate_per_hour'=>['required_if:role,instructor'],
            'airport_id'=>['required_if:role,instructor'],
            'certificate_id'=>['required_if:role,student'],
            'device_id' => ['required', 'max:255'],
            'device_type' => ['required', 'in:android,ios'],
            'type' => ['required', 'in:facebook,apple,google'],
            'social_id' => ['required'],
            'push_token' => ['nullable'],
            'profile_image' => ['nullable'],
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'approx_hours' => $request->approx_hours,
            'type'=>$request->role,
            'experience_in_year' => $request->experience_in_year,
            'rate_per_hour' => $request->rate_per_hour,
            'airport_id' => $request->airport_id,
            'certificate_id' => $request->certificate_id,
            'country_code'=>"",
            'mobile'=>"",
            'username'=>"",
            'profile_image' => $request->profile_image ?? config('constants.default.user_image'),
        ]);
        if ($user) {
            Auth::loginUsingId($user->id);
            $token = User::AddTokenToUser();
            Auth::user()->social_logins()->updateOrCreate(
                ['provider' => $provider, 'user_id' => $user->id],
                ['provider' => $provider, 'provider_id' => $social_id]
            );
            $this->sendResponse(200, __('api.suc_user_register'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_something_went_wrong'), false);
        }
    }

    public function getAirportList(Request $request){
        // $this->directValidation([
        //     'keywords' => ['required', 'max:255'],
        // ]);
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');
        if(isset($request->keywords) && !empty($request->keywords)){
            $airportsList=Airport::Where('keywords', 'like', '%' . $request->keywords . '%')->orWhere('name', 'like', '%' . $request->keywords . '%')->simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
        }else{
            $airportsList=Airport::simpleDetails()->limit($limit)->offset($offset)->Orderby('id','desc')->get();
        }

        if($airportsList){
            $this->sendResponse(200, __('api.succ'),$airportsList);
        }else{
            $this->sendResponse(412, "Airport not found");
        }
    }

    public function getTagsList(Request $request){
        $this->directValidation([
            'keywords' => ['required'],
        ]);
        $limit=$request->limit??get_constants('default.limit');
        $offset=$request->offset??get_constants('default.offset');

        $tags = explode(' ',$request->keywords);
        $tags_array = [];
        foreach($tags as $tag){
            $tagsList=Tag::where('use_count','>=',10);
            $tagsList->where(function ($query) use ($tag) {
                $query->where('tag', 'like', "%$tag%");
            });
            $tagsList = $tagsList->simpleDetails()->groupBy('tag')->Orderby('id','desc')->get();
            //dd();
            if(count($tagsList) > 0){
                foreach($tagsList as $tag_value){
                    //dd($tag_value->tag);
                    array_push($tags_array,$tag_value->tag);
                }
            }
        }

        $this->sendResponse(200, __('api.succ'),$tags_array);
    }

    public function getCertificatesList(Request $request){

        $certificatesList=Certificate::simpleDetails()->Orderby('id','desc')->get();
        if($certificatesList){
            $this->sendResponse(200, __('api.succ'),$certificatesList);
        }else{
            $this->sendResponse(412, "Airport not found");
        }
    }
    public function upload_airport(){
        $ariports = storage_path('app/public/airports_cleaned.json');
        $file = file_get_contents($ariports);
        $all_airport = json_decode($file);
        //dd();
        foreach ($all_airport->airports as $airport)
        {
            //dd($airport->iata_code);
            // Airport::create([
            //     'name' => $airport->name,
            //     'lat' => $airport->latitude_deg,
            //     'lng' => $airport->longitude_deg,
            //     'unq_id' => $airport->id,
            //     'ident' => $airport->ident,
            //     'local_code' => $airport->local_code,
            //     'keywords' => $airport->local_code,
            //     //'gps_code' => $airport->gps_code,
            //     //'iata_code' => $airport->iata_code,
            //     'type' => $airport->type,
            //     'iso_country' => $airport->iso_country,
            //     'iso_region' => $airport->iso_region,
            // ]);
        }
        $this->sendResponse(200, __('api.succ'));
    }

}
