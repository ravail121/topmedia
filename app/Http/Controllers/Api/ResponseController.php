<?php

namespace App\Http\Controllers\Api;

use App\Cart;
use App\Countries;
use App\Followers;
use App\Gamification;
use App\Http\Controllers\Controller as Controller;
use App\SocialAccount;
use App\User;
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

    public function get_user_data($token = null, $id = "")
    {
        $user_data = ($id) ? User::find($id) : Auth::user();
        $return = [
            'id' => $user_data->id,
            'name' => $user_data->name ?? "",
            'about' => $user_data->about ?? "",
            'email' => $user_data->email ?? "",
            'country_iso_code' => $user_data->country_iso_code ?? "",
            'country_code' => $user_data->country_code ?? "",
            'mobile' => $user_data->mobile ?? "",
            'profile_image' => $user_data->profile_image,
            'background_image' => $user_data->background_image,
            'audio_file' => $user_data->audio_file,
            'username' => $user_data->username ?? "",
            'date_of_birth' => $user_data->date_of_birth ?? "",
            'crypto_address' => $user_data->crypto_address ?? "",
            'firebase_uid' => $user_data->firebase_uid ?? "",
            'country_short_code' => $user_data->country_short_code ?? "",
            'latitude' => $user_data->latitude ?? "",
            'longitude' => $user_data->longitude ?? "",
            'profile_viewing' => $user_data->profile_viewing ?? "",
            'country_code_short' => $user_data->country_short_code ?? "",
            'followers_count' => $user_data->followers->count('id'),
            'following_count' => $user_data->following->count('id'),
            'is_social_login' => SocialAccount::where("user_id", $user_data->id)->count() ?? 0,
            'token' => $token ?? get_header_auth_token(),
        ];
        if (!$id) {
            $return['gamification'] = Gamification::FindNotCompletedGamification($user_data);
        }
        if ($id) {
            $return["is_following"] = count(Followers::where(["user_id" => Auth::id(), "profile_id" => $id])->get());
        }

        return $return;
    }
}
