<?php

namespace App\Http\Controllers\Api;

use App\Cart;
use App\Countries;
use App\Http\Controllers\Controller as Controller;
use App\User;
use App\StudentNote;
use App\UserCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{

    public $errors;

    public function __construct()
    {
        $this->errors = null;
    }

    public function apiValidation($rules, $messages = [], $data = null)
    {
        $data = ($data) ? $data : request()->all();
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $this->errors = $validator->errors()->first();
            return false;
        } else {
            return true;
        }
    }

    public function directValidation($rules, $messages = [], $direct = true, $data = null)
    {
        $data = ($data) ? $data : request()->all();
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $this->errors = $validator->errors()->first();
            if ($direct) {
                $this->sendError(null, null);
            } else {
                return false;
            }
        } else {
            //            return true;
            return $validator->valid();
        }
    }

    public function sendError($message = null, $array = true)
    {
        $empty_object = new \stdClass();
        $message = ($this->errors) ? $this->errors : ($message ? $message : __('api.err_something_went_wrong'));
        send_response(412, $message, ($array) ? [] : $empty_object);
    }

    public function sendResponse($status, $message, $result = null, $extra = null)
    {
        $empty_object = new \stdClass();
//        $data = ($result) ? $empty_object : $result;
//        send_response($status, $message, $data, $extra, ($status != 401));
        send_response($status, $message, $result, $extra, ($status != 401));
    }

    public function get_user_data($token = null)
    {
        $user_data = Auth::user();
        $notes=StudentNote::Where(['student_id'=>$user_data->id])->count();
        return [
            'id' => $user_data->id,
            'name' => $user_data->name,
//            'first_name' => $user_data->first_name,
//            'last_name' => $user_data->last_name,
            'email' => $user_data->email,
            'type'=>$user_data->type,
            'approx_hours'=>$user_data->approx_hours,
            'experience_in_year'=>$user_data->experience_in_year,
            'rate_per_hour'=>$user_data->rate_per_hour,
            'type'=>$user_data->type,
            'country_code' => $user_data->country_code,
            'mobile' => $user_data->mobile,
            'profile_image' => $user_data->profile_image,
            'total_hours' => ($user_data->total_hours==null)?'':$user_data->total_hours,
            'ftn' => ($user_data->ftn==null)?'':$user_data->ftn,
            'total_notes' => $notes,
            'back_story' => ($user_data->back_story==null)?'':$user_data->back_story,
            'certificate_id'=>($user_data->certificate_id==null)?'':$user_data->certificate_id,
            'certificates_data' => ($user_data->certificates==null)?'':$user_data->certificates,
            'airport_id'=>($user_data->airport_id==null)?'':$user_data->airport_id,
            'airport_data'=>($user_data->airport_id==null)?'':$user_data->airport,
            'token' => $token ?? get_header_auth_token(),
        ];
    }
    public function upload_file($file_name = "", $path = "")
    {
        $file = "";
        $request = \request();
        if ($request->hasFile($file_name) && $path) {
            $path = config('constants.upload_paths.' . $path);
            $file = $request->file($file_name)->store($path, config('constants.upload_type'));
        } else {
            echo 'Provide Valid Const from web controller';
            die();
        }
        return $file;
    }

}
