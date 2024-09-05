<?php

namespace App\Http\Controllers\Api\V1;

use App\Content;
use App\Http\Controllers\Api\ResponseController;
use App\User;
use App\DeviceToken;
use App\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuestController extends ResponseController
{

    public function login(Request $request)
    {
        $rules = [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required'],
            'push_token' => ['nullable'],
            'device_type' => ['required', 'in:android,ios'],
            'device_id' => ['required', 'max:255'],
        ];
        $messages = ['email.exists' => __('api.err_email_not_register')];
        $this->directValidation($rules, $messages);
        $attempt = ['email' => $request->email, 'password' => $request->password, 'type' => 'user', 'status' => 'active'];
        if (Auth::attempt($attempt)) {
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }


    public function signup(Request $request)
    {
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'country_iso_code' => ['nullable', "max:4"],
            'country_code' => ['nullable', "max:10"],
            'mobile' => ['nullable', 'max:15', Rule::unique('users', "mobile")->where("country_code", $request->country_code)->whereNull('deleted_at')],
            'password' => ['required'],
            'name' => ['nullable', 'max:200'],
            'dob' => ['nullable', 'date_format:Y-m-d'],
            'username' => ['nullable', Rule::unique('users')->whereNull('deleted_at')],
            'push_token' => ['nullable'],
            'device_id' => ['required', 'max:255'],
            'device_type' => ['required', 'in:android,ios'],
            'firebase_uid' => ['required'],
        ], [
            'mobile.unique' => __('api.err_mobile_is_exits'),
            'email.unique' => __('api.err_email_is_exits'),
        ]);
        $user = User::create([
            'name' => $request->name,
            // 'date_of_birth' => $request->dob,
            'email' => $request->email,
            'country_iso_code' => $request->country_iso_code,
            'country_code' => $request->country_code,
            'mobile' => $request->mobile,
            'username' => $request->username,
            'password' => $request->password,
            'firebase_uid' => $request->firebase_uid,
        ]);
        if ($user) {
            Auth::loginUsingId($user->id);
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.succ'), $this->get_user_data($token));
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
            'type' => ['required', 'in:username,crypto_address,email'],
            'value' => ['required'],
            'country_code' => ['required_if:type,mobile']
        ];
        $user_id = $request->user()->id ?? 0;
        if ($type == "email") {
            $rules['value'][] = 'email';
            $rules['value'][] = Rule::unique('users', 'email')->ignore($user_id)->whereNull('deleted_at');
        } elseif ($type == "crypto_address") {
            $rules['value'][] = 'crypto_address';
            $rules['value'][] = Rule::unique('users', 'crypto_address')->ignore($user_id)->whereNull('deleted_at');
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
            'device_id' => ['nullable', 'max:255'],
        ]);

        $data = [
            'is_force_update' => ($type == "ios") ? IOS_Force_Update : Android_Force_Update,
        ];

        if ($request->device_id && Auth::id()) {
            DeviceToken::updateOrCreate(
                ['device_id' => $request->device_id, 'type' => $request->device_type],
                ['device_id' => $request->device_id, 'type' => $request->device_type, 'badge' => 0, 'user_id' => Auth::id() ?? 1, 'token' => ""]
            );
        }

        $check = ($type == "ios") ? (IOS_Version <= $version) : (Android_Version <= $version);
        if ($check) {
            $this->sendResponse(200, __('api.succ'), $data);
        } else {
            $this->sendResponse(412, __('api.err_new_version_is_available'), $data);
        }
    }

    public function CheckSocialAbility(Request $request)
    {
        $user_id = 0;
        $email = $request->email;
        $provider = $request->type;
        $social_id = $request->social_id;
        $this->directValidation([
            'type' => ['required', 'in:facebook,google,apple'],
            'social_id' => ['required'],
            'device_id' => ['required'],
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

    public function SocialRegister(Request $request)
    {
        $provider = $request->type;
        $social_id = $request->social_id;
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users', "email")->whereNull('deleted_at')],
            'country_iso_code' => ['nullable', "max:4"],
            'country_code' => ['nullable', "max:10"],
            'mobile' => ['nullable', 'max:15', Rule::unique('users', "mobile")->where("country_code", $request->country_code)->whereNull('deleted_at')],
            'dob' => ['nullable', 'date_format:Y-m-d'],
            'username' => ['nullable', Rule::unique('users')->whereNull('deleted_at')],
            'type' => ['required', 'in:facebook,google,apple'],
            'social_id' => ['required'],
            'name' => ['nullable', 'max:200'],
            'firebase_uid' => ['required'],
            'push_token' => ['nullable'],
            'device_id' => ['required', 'max:255'],
            'device_type' => ['required', 'in:android,ios'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country_iso_code' => $request->country_iso_code,
            'country_code' => $request->country_code,
            'mobile' => $request->mobile,
            'date_of_birth' => $request->dob,
            'username' => $request->username,
            'firebase_uid' => $request->firebase_uid,
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
}
