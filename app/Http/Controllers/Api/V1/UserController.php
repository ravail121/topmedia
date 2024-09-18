<?php

namespace App\Http\Controllers\Api\V1;

use App\AgoraToken;
use App\BlockedUser;
use App\DeviceToken;
use App\Events\Api\GamificationEvent;
use App\Followers;
use App\HelpRequests;
use App\HidePosts;
use App\NotificationList;
use App\Posts;
use App\ReportedPosts;
use App\ReportedUser;
use App\User;
use App\utility\Coin;
use App\ViewedProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends ResponseController
{
    public function getProfile()
    {
        $this->sendResponse(200, __('api.succ'), $this->get_user_data());
    }

    public function GetFollowerDetails($id)
    {

        $user = Auth::user();
        if (Auth::user()->profile_viewing == "public") {
            $data = [
                "user_id" => Auth::id(),
                "profile_id" => $id,
            ];

            $viewed = ViewedProfile::where($data)->first();

            if (!$viewed && $id != Auth::id()) {
                $push_data = [
                    'user_id' => $id,
                    'from_user_id' => Auth::id(),
                    'push_type' => 7,
                    'push_title' => $user->name . " viewed your profile",
                    'push_message' => $user->name . " viewed your profile",
                    'object_id' => $id,
                    "extra" => [
                        "name" => $user->name,
                        "profile_image" => $user->profile_image,
                    ],
                ];
                send_push($id, $push_data);
            }

            ViewedProfile::updateOrCreate($data, $data);
        }

        $this->sendResponse(200, __('api.succ'), $this->get_user_data("", $id));
    }

    public function logout(Request $request)
    {
        DeviceToken::where('token', get_header_auth_token())->delete();
        $this->sendResponse(200, __('api.succ_logout'), false);
    }

    public function UpdateCryptoWallet(Request $request, Coin $coin)
    {
        $user_data = $request->user();
        $this->directValidation([
            'crypto_address' => ['required', Rule::unique('users', "crypto_address")->ignore($user_data->id)->whereNull('deleted_at')],
        ], [
            'crypto_address.unique' => __("api.crypto_address_non_unique")
        ]);
        $valid_wallet = $coin->Validate($request->crypto_address);
        if ($valid_wallet) {
            $user_data->update(['crypto_address' => $request->crypto_address]);
            $this->sendResponse(200, __('api.suc_user_crypto_save'), $this->get_user_data());
        }
        $this->sendError(__("api.invalid_crypto_address"));
    }

    public function GetCryptoBalance(Request $request, Coin $coin)
    {
        $user = $request->user();
        if ($user->crypto_address) {
            $balance = $coin->GetBalance($user->crypto_address);
            if (!is_null($balance)) {
                $this->sendResponse(200, __('api.succ'), $balance);
            }
            $this->sendError(__("api.err_something_went_wrong"));
        }
        $this->sendError(__("api.err_crypto_add"));
    }

    public function updateProfile(Request $request)
    {

        $user_data = $request->user();
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at')],
            'username' => ['nullable', Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at')],
            'country_iso_code' => ['nullable', "max:4"],
            'country_code' => ['nullable', "max:10"],
            'mobile' => ['nullable', 'max:15', Rule::unique('users', "mobile")->ignore($user_data->id)->where("country_code", $request->country_code)->whereNull('deleted_at')],
            'name' => ['nullable', 'max:200'],
            'about' => ['nullable'],
            'country_code_short' => ['nullable'],
            'dob' => ['nullable'],
            'profile_viewing' => ['required', 'in:public,private'],

        ]);

        $file = $user_data->getRawOriginal("profile_image");
        if ($request->hasFile("profile_image")) {
            $up = upload_file("profile_image", "user_profile_image");
            if ($up) {
                $file = $up;
            }
        }

        $back_file = $user_data->getRawOriginal("background_image");
        if ($request->hasFile("background_image")) {
            $up = upload_file("background_image", "user_profile_image");
            if ($up) {
                $back_file = $up;
            }
        }

        $audio_file = $user_data->getRawOriginal("audio_file");
        if ($request->hasFile("audio_file")) {
            $audio_upload = upload_file("audio_file", "user_audio_file");
            if ($audio_upload) {
                $audio_file = $audio_upload;
            }
        }

        $user_data->update([
            'name' => $request->name,
            'date_of_birth' => $request->dob,
            'email' => $request->email,
            'country_iso_code' => $request->country_iso_code,
            'country_code' => $request->country_code,
            'mobile' => $request->mobile,
            'username' => $request->username,
            'profile_image' => $file,
            'about' => $request->about,
            'country_short_code' => $request->country_code_short,
            'profile_viewing' => $request->profile_viewing,
            'background_image' => $back_file,
            'audio_file' => $audio_file
        ]);
        $this->sendResponse(200, __('api.succ_profile_update'), $this->get_user_data());
    }

    public function ChangePassword(Request $request)
    {
        $user = $request->user();
        $this->directValidation([
            'current_password' => ['required', 'max:100'],
            'new_password' => ['required', 'max:100'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);
        if (Hash::check($request->current_password, $user->password)) {
            $user->update(['password' => $request->new_password]);
            $this->sendResponse(200, __('api.succ_password_changed'), $this->get_user_data());
        } else {
            $this->sendError(__('api.err_enter_valid_old_pass'));
        }
    }

    public function StartFollow($id)
    {
        $find = User::find($id);

        if ($find) {

            $follow = Followers::where([
                "user_id" => Auth::id(),
                "profile_id" => $id
            ])->first();

            if (!$follow) {
                $follow = Followers::create([
                    "user_id" => Auth::id(),
                    "profile_id" => $id
                ]);
                event(new GamificationEvent($follow->user, 'followers'));
                $push_data = [
                    'user_id' => $id,
                    'from_user_id' => Auth::id(),
                    'push_type' => 2,
                    'push_title' => Auth::user()->name . " starts following you",
                    'push_message' => Auth::user()->name . " starts following you",
                    'object_id' => Auth::id(),
                    "extra" => [
                        "name" => Auth::user()->name,
                        "profile_image" => Auth::user()->profile_image,
                    ],
                ];

                if ($id != Auth::id()) {
                    send_push($id, $push_data);
                }

                $message = __('api.succ_following');
            } else {
                $follow->delete();

                $message = __('api.succ_unfollowing');
            }

            $this->sendResponse(200, $message, [
                'followers_count' => Auth::user()->followers()->count('id'),
                'following_count' => Auth::user()->following()->count('id'),
            ]);
        }

        $this->sendError(__('api.err_enter_invalid_user_id'));
    }

    public function GetFollowers(Request $request)
    {
        $otherUserId = $request->has('user_id') && $request->get('user_id');
        $follow = User::select("users.id", "users.name", "users.username", "users.profile_image")
            ->join("followers", "followers.user_id", "=", "users.id")->where([
                "followers.profile_id" => $otherUserId ? $request->user_id : Auth::id()
            ])->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $follow);
    }

    public function GetFollowing(Request $request)
    {
        $otherUserId = $request->has('user_id') && $request->get('user_id');
        $follow = User::select("users.id", "users.name", "users.username", "users.profile_image")
            ->join("followers", "followers.profile_id", "=", "users.id")->where([
                "followers.user_id" => $otherUserId ? $request->user_id : Auth::id()
            ])->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $follow);
    }

    public function SubmitHelpRequest(Request $request)
    {
        $this->directValidation([
            "type_of_issue" => ["required"],
            "description" => ["required"],
        ]);

        HelpRequests::create([
            "type_of_issue" => $request->type_of_issue,
            "description" => $request->description,
            "user_id" => $request->user()->id,
        ]);

        $this->sendResponse(200, __("api.succ"), []);
    }

    public function Home(Request $request)
    {

        $posts = Posts::SimpleDetails()->whereNotIn("user_id", function ($query) {
            $query->select("id")->from("reported_users")->where(function ($query1) {
                $query1->where("user_id", Auth::id())->orWhere("profile_id", Auth::id());
            })->get();
        })
            ->with(["user"])->withCount("comments_list")->latest()->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        if ($posts) {
            foreach ($posts as $key => $value) {
                $posts[$key]->is_following = Followers::where(["user_id" => Auth::id(), "profile_id" => $value->user_id])->count();
            }
        }

        $this->sendResponse(200, __("api.succ"), $posts);
    }


    public function ReportUser(Request $request)
    {
        $this->directValidation([
            "id" => ["required", Rule::exists("users", "id")->whereNull("deleted_at")],
            "reason" => ["nullable"],
            "description" => ["nullable"],
        ]);

        ReportedUser::updateOrCreate([
            "user_id" => Auth::id(),
            "profile_id" => $request->id
        ], [
            "user_id" => Auth::id(),
            "profile_id" => $request->id,
            "reason" => $request->reason,
            "description" => $request->description,
        ]);

        Followers::where(["user_id" => Auth::id(), "profile_id" => $request->id])->delete();
        Followers::where(["user_id" => $request->id, "profile_id" => Auth::id()])->delete();

        $message = __('api.succ_reported');

        $this->sendResponse(200, $message, []);
    }

    public function ReportPost(Request $request)
    {
        $this->directValidation([
            "id" => ["required", Rule::exists("posts", "id")],
            "reason" => ["required"],
            "description" => ["required"],
        ]);

        $follow = ReportedPosts::where([
            "user_id" => Auth::id(),
            "post_id" => $request->id
        ])->first();

        if (!$follow) {
            ReportedPosts::create([
                "user_id" => Auth::id(),
                "post_id" => $request->id,
                "reason" => $request->reason,
                "description" => $request->description,
            ]);
        }

        $message = __('api.succ_post_reported');

        $this->sendResponse(200, $message, []);
    }

    public function HidePost(Request $request)
    {
        $this->directValidation([
            "id" => ["required", Rule::exists("posts", "id")],
        ]);

        $follow = HidePosts::where([
            "user_id" => Auth::id(),
            "post_id" => $request->id
        ])->first();

        if (!$follow) {
            HidePosts::create([
                "user_id" => Auth::id(),
                "post_id" => $request->id,
            ]);
        }

        $message = __('api.succ_post_hided');

        $this->sendResponse(200, $message, []);
    }

    public function getReportedFollowers(Request $request)
    {
        $user = User::select("users.id", "users.name", "users.username", "users.profile_image")
            ->join("reported_users", "users.id", "=", "reported_users.profile_id")->where([
                "reported_users.user_id" => Auth::id()
            ])->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $user);
    }

    public function updateProfileAsViewed($id, Request $request)
    {
        $user = User::find($id);

        if ($user) {
            if (Auth::user()->profile_viewing == "public") {
                $data = [
                    "user_id" => Auth::id(),
                    "profile_id" => $id,
                ];
                ViewedProfile::updateOrCreate($data, $data);
            }

            $this->sendResponse(200, __("api.succ"), []);
        }

        $this->sendError(__("api.err_enter_invalid_user_id"));
    }

    public function GetViewerList(Request $request)
    {
        $profiles = User::select("id", "name", "profile_image")->whereIn("id", function ($query) use ($request) {
            $query->select("user_id")->from("viewed_profiles")->where("profile_id", Auth::id())->latest()->get();
        })->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $profiles);
    }

    public function GetNotificationList(Request $request)
    {
        $notification_list = NotificationList::where("user_id", Auth::id())
            ->with("from_user")
            ->limit($request->limit ?? 10)->offset($request->offset ?? 0)->latest()->get();

        $this->sendResponse(200, __("api.succ"), $notification_list);
    }

    public function RemoveFollower($id)
    {
        $user = Followers::where("profile_id", Auth::id())->where("user_id", $id)->first();

        if ($user) {
            $user->delete();
            $this->sendResponse(200, __("api.succ_follower_removed"), [
                'followers_count' => Auth::user()->followers()->count('id'),
                'following_count' => Auth::user()->following()->count('id'),
            ]);
        } else {
            $this->sendError(__("api.err_enter_invalid_user_id"));
        }
    }

    public function removeAllNotifications()
    {
        NotificationList::where("user_id", Auth::id())->delete();

        $this->sendResponse(200, __("api.succ_notifications_removed"), []);
    }

    public function searchUsers(Request $request)
    {


        $searchQuery = $request->search;
        $userId = Auth::id();
        $users = User::with('reported_users')
            ->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', "%$searchQuery%")
                    ->where('type', 'user')
                    ->orWhere('username', 'like', "%$searchQuery%");
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('profile_id')
                    ->from('reported_users');
            })
            ->limit($request->limit)
            ->get()
            ->map(function ($user) {
                return [
                    "id" => $user->id,
                    "email" => $user->email,
                    "name" => $user->name,
                    "profileImage" => $user->profile_image,
                    "username" => $user->username
                ];
            });

        $this->sendResponse(200, __("api.succ"), $users);
    }

    public function deleteProfile(Request $request)
    {

        $user = User::find(Auth::id());
        $user->post_comments()->delete();
        $user->post_likes()->delete();
        $user->posts()->delete();
        $user->fromUserNotificationList()->delete();
        $user->toUserNotificationList()->delete();
        $user->delete();


        $this->sendResponse(200, __("api.succ"));
    }

    public function SetLiveStatusOff(Request $request)
    {
        $user = User::find(Auth::id());
        $user->is_live = 0;
        $user->save();

            // Find and delete entries from AgoraToken where uid matches the user ID
        $agoraTokens = AgoraToken::where('uid', $user->id)->get();

        foreach ($agoraTokens as $token) {
            // Delete any entries that have the same channel_name
            AgoraToken::where('channel_name', $token->channel_name)->delete();

            // Delete the current token entry
            $token->delete();
        }

        $this->sendResponse(200, __("api.succ"));
    }

    public function GetLiveStatus(Request $request)
    {
        // Validate the request to ensure 'user_id' is provided
        $request->validate([
            'user_id' => 'required',
        ]);

        // Find the user by the provided user_id
        $user = User::find($request->user_id);

        // Check if the user exists
        if ($user) {
             // Initialize the response data
            $data = [
                "live_status" => $user->is_live == 1 ? true : false,
                "channel_name" => null, // Default channel name as null
            ];

            // If the user is live, fetch the latest AgoraToken record for that user
            if ($user->is_live == 1) {
                $agoraToken = AgoraToken::where('uid', $user->id)
                                ->orderBy('created_at', 'desc') // Get the latest record first
                                ->first();

                // If an AgoraToken exists, include the channel_name in the response
                if ($agoraToken) {
                    $data['channel_name'] = $agoraToken->channel_name;
                }
            }
            $this->sendResponse(200, __("api.succ"), $data);
        }
    }
}
