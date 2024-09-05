<?php

use App\DeviceToken;
use App\PushLog;
use App\User;
use App\NotificationList;
use http\Client;
use http\Client\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

if (!function_exists('send_response')) {

    function send_response($Status, $Message = "", $ResponseData = NULL, $extraData = NULL, $null_remove = null)
    {
        $data = [];

        $Message = ucfirst($Message);
        $valid_status = [412, 200, 401];
        if (is_array($ResponseData)) {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = $ResponseData;
        } else if (!empty($ResponseData)) {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = $ResponseData;
        } else {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = new stdClass();
        }
        if (!empty($extraData) && is_array($extraData)) {
            foreach ($extraData as $key => $val) {
                $data[$key] = $val;
            }
        }
        //        if ($null_remove) {
        //            null_remover($data['data']);
        //        }
        $header_status = in_array($data['status'], $valid_status) ? $data['status'] : 412;
        response()->json($data, $header_status)->header('Content-Type', 'application/json')->send();
        die(0);
    }
}


//function null_remover($response, $ignore = [])
//{
//    array_walk_recursive($response, function (&$item) {
//        if (is_null($item)) {
//            $item = strval($item);
//        }
//    });
//    return $response;
//}

function token_generator()
{
    return genUniqueStr('', 100, 'device_tokens', 'token', true);
}

function get_header_auth_token()
{
    $full_token = request()->header('vAuthorization');
    return (substr($full_token, 0, 7) === 'Bearer ') ? substr($full_token, 7) : null;
}

if (!function_exists('un_link_file')) {
    function un_link_file($image_name = "")
    {
        $pass = true;
        if (!empty($image_name)) {
            try {
                $default_url = URL::to('/');
                $get_default_images = config('constants.default');
                $file_name = str_replace($default_url, '', $image_name);
                $default_image_list = is_array($get_default_images) ? str_replace($default_url, '', array_values($get_default_images)) : [];
                if (!in_array($file_name, $default_image_list)) {
                    Storage::disk(get_constants('upload_type'))->delete($file_name);
                }
            } catch (Exception $exception) {
                $pass = $exception;
            }
        } else {
            $pass = 'Empty Field Name';
        }
        return $pass;
    }
}


function get_asset($val = "", $file_exits_check = true, $no_image_available = null)
{
    $no_image_available = ($no_image_available ?? asset(get_constants('default.no_image_available')));
    if ($val) {
        if ($file_exits_check) {
            return (file_exists(public_path($val))) ? asset($val) : $no_image_available;
        } else {
            return asset($val);
        }
    } else {
        return asset($no_image_available);
    }
}

function print_title($title)
{
    return ucfirst($title);
}

function get_constants($name)
{
    return config('constants.' . $name);
}

function calculate_percentage($amount = 0, $discount = 0)
{
    return ($amount && $discount) ? (($amount * $discount) / 100) : 0;
}

function flash_session($name = "", $value = "")
{
    session()->flash($name, $value);
}

function success_session($value = "")
{
    session()->flash('success', ucfirst($value));
}

function error_session($value = "")
{
    session()->flash('error', ucfirst($value));
}

function getDashboardRouteName()
{
    $name = 'front.dashboard';
    $user_data = Auth::user();
    if ($user_data) {
        if (in_array($user_data->type, ["admin", "local_admin"])) {
            $name = 'admin.dashboard';
        }
    }
    return $name;
}

function admin_modules()
{
    return [
        [
            'route' => route('admin.dashboard'),
            'name' => __('Dashboard'),
            'icon' => 'kt-menu__link-icon fa fa-home',
            'child' => [],
            'all_routes' => [
                'admin.dashboard',
            ]
        ],
        [
            'route' => route('admin.user.index'),
            'name' => __('User'),
            'icon' => 'kt-menu__link-icon fas fa-users',
            'child' => [],
            'all_routes' => [
                'admin.user.index',
                'admin.user.show',
                'admin.user.add',
            ]
        ],
        [
            'route' => route('admin.content.index'),
            'name' => __('Content'),
            'icon' => 'kt-menu__link-icon fas fa-text-height',
            'child' => [],
            'all_routes' => [
                'admin.content.index',
                'admin.content.edit',
            ]
        ],
        [
            'route' => route('admin.help-requests.index'),
            'name' => __('Help Request'),
            'icon' => 'kt-menu__link-icon fas fa-headset',
            'child' => [],
            'all_routes' => [
                'admin.help-requests.index',
            ]
        ],
        [
            'route' => route('admin.posts.index'),
            'name' => __('Posts'),
            'icon' => 'kt-menu__link-icon fas fa-list',
            'child' => [],
            'all_routes' => [
                'admin.posts.index',
            ]
        ],
        [
            'route' => route('admin.reported-users.index'),
            'name' => __('Reported Users'),
            'icon' => 'kt-menu__link-icon fas fa-user-alt-slash',
            'child' => [],
            'all_routes' => [
                'admin.reported-users.index',
            ]
        ],
        [
            'route' => route('admin.reported-posts.index'),
            'name' => __('Reported Posts'),
            'icon' => 'kt-menu__link-icon fas fa-ban',
            'child' => [],
            'all_routes' => [
                'admin.reported-posts.index',
            ]
        ],
        [
            'route' => 'javascript:;',
            'name' => __('General Settings'),
            'icon' => 'kt-menu__link-icon fa fa-home',
            'all_routes' => [
                'admin.get_update_password',
                'admin.get_site_settings',
            ],
            'child' => [
                [
                    'route' => route('admin.get_update_password'),
                    'name' => 'Change Password',
                    'icon' => '',
                    'all_routes' => [
                        'admin.get_update_password',
                    ],
                ],
                [
                    'route' => route('admin.get_site_settings'),
                    'name' => 'Site Settings',
                    'icon' => '',
                    'all_routes' => [
                        'admin.get_site_settings',
                    ],
                ]
            ],
        ],
        [
            'route' => route('front.logout'),
            'name' => __('Logout'),
            'icon' => 'kt-menu__link-icon fas fa-sign-out-alt',
            'child' => [],
            'all_routes' => [],
        ],
    ];
}


function get_error_html($error)
{
    $content = "";
    if ($error->any() !== null && $error->any()) {
        foreach ($error->all() as $value) {
            $content .= '<div class="alert alert-danger alert-dismissible mb-1" role="alert">';
            $content .= '<div class="alert-text">' . $value . '</div>';
            $content .= '<div class="alert-close"><i class="flaticon2-cross kt-icon-sm" data-dismiss="alert"></i></div></div>';
        }
    }
    return $content;
}


function breadcrumb($aBradcrumb = array())
{
    $i = 0;
    $content = '';
    $is_login = Auth::user();
    if ($is_login) {
        if ($is_login->type == "admin") {
            $aBradcrumb = array_merge(['Home' => route('admin.dashboard')], $aBradcrumb);
        } elseif ($is_login->type == "vendor") {
            $aBradcrumb = array_merge(['Home' => route('vendor.dashboard')], $aBradcrumb);
        }
    }
    if (is_array($aBradcrumb) && count($aBradcrumb) > 0) {
        $total_bread_crumbs = count($aBradcrumb);
        foreach ($aBradcrumb as $key => $link) {
            $i += 1;
            $link = (!empty($link)) ? $link : 'javascript:void(0)';

            $content .= '<li class="breadcrumb-item"> <a href="' . $link . '">' . ucfirst($key) . '</a>';


            // $content .= "<a href='" . $link . "' class='kt-subheader__breadcrumbs-link'>" . ucfirst($key) . "</a>";
            // if ($total_bread_crumbs != $i) {
            //     $content .= "<span class='kt-subheader__breadcrumbs-separator'></span>";
            // }
        }
    }
    return $content;
}

function success_error_view_generator()
{
    $content = "";
    if (session()->has('error')) {
        $content = '<div class="alert alert-danger alert-dismissible" role="alert">
                                        <div class="alert-text">' . session('error') . '</div>
                                        <div class="alert-close"><i class="flaticon2-cross kt-icon-sm"
                                                                    data-dismiss="alert"></i></div></div>';
    } elseif (session()->has('success')) {
        $content = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <div class="alert-text">' . session('success') . '</div>
                                        <div class="alert-close"><i class="flaticon2-cross kt-icon-sm"
                                                                    data-dismiss="alert"></i></div></div>';
    }
    return $content;
}

function datatable_filters()
{
    $post = request()->all();
    return array(
        'offset' => isset($post['start']) ? intval($post['start']) : 0,
        'limit' => isset($post['length']) ? intval($post['length']) : 25,
        'sort' => isset($post['columns'][(isset($post["order"][0]['column'])) ? $post["order"][0]['column'] : 0]['data']) ? $post['columns'][(isset($post["order"][0]['column'])) ? $post["order"][0]['column'] : 0]['data'] : 'created_at',
        'order' => isset($post["order"][0]['dir']) ? $post["order"][0]['dir'] : 'DESC',
        'search' => isset($post["search"]['value']) ? $post["search"]['value'] : '',
        'sEcho' => isset($post['sEcho']) ? $post['sEcho'] : 1,
    );
}

function send_push($user_id = 0, $data = [], $notification_entry = true)
{
    $pem_secret = '';
    $bundle_id = 'com.Project.NextgenApp';
    $pem_file = base_path('public/uploads/pushcert.pem');
    $main_name = defined('site_name') ? site_name : env('APP_NAME');
    $push_data = [
        'user_id' => $user_id,
        'from_user_id' => $data['from_user_id'] ?? null,
        'sound' => 'defualt',
        'push_type' => $data['push_type'] ?? 0,
        'push_title' => $data['push_title'] ?? $main_name,
        'push_message' => $data['push_message'] ?? "",
        'object_id' => $data['object_id'] ?? null,
        "extra" => $data['extra'] ?? [],
    ];

    $user = User::find($user_id);
    if ($push_data['user_id'] !== $push_data['from_user_id']) {
        $get_user_tokens = DeviceToken::get_user_tokens($user_id);
        $fire_base_header = ["Authorization: key=" . config('constants.firebase_server_key'), "Content-Type: application/json"];
        if (count($get_user_tokens)) {
            foreach ($get_user_tokens as $value) {
                $curl_extra = [];
                $push_status = "Sent";
                $value->update(['badge' => $value->badge + 1]);
                $device_token = $value['push_token'];
                $device_type = strtolower($value['type']);
                if ($device_token) {
                    if ($device_type == "ios") {
                        try {
                            $url = "https://api.sandbox.push.apple.com/3/device/$device_token";
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                                'aps' => [
                                    'badge' => $value->badge,
                                    'alert' => ['title' => $push_data['push_title'], 'body' => $push_data['push_message']],
                                    'sound' => 'default',
                                    'push_type' => $push_data['push_type']
                                ],
                                'payload' => [
                                    'to' => $value['push_token'],
                                    'notification' => ['title' => $push_data['push_title'], 'body' => $push_data['push_message'], "sound" => "default", "badge" => $value->badge],
                                    'data' => $push_data,
                                    'priority' => 'high'
                                ]
                            ]));

                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array("apns-topic: $bundle_id"));
                            curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
                            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                            if ($httpcode != 200) {
                                if (config('constants.push_log')) {
                                    $push_status = 'Curl Error:' . curl_error($ch);
                                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $push_status, json_encode($push_data), $response);
                                }
                            } else {
                                if (config('constants.push_log')) {
                                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $push_status, json_encode($push_data), $response);
                                }
                            }
                        } catch (Exception $e) {
                            if (config('constants.push_log')) {
                                PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $e->getMessage(), json_encode($push_data));
                            }
                        }
                    } else {
                        $ch = curl_init();
                        $payload_data = [
                            'to' => $value['push_token'],
                            'data' => $push_data,
                            'priority' => 'high'
                        ];
                        $headers = $fire_base_header;
                        curl_setopt_array($ch, array(
                            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_POSTFIELDS => json_encode($payload_data),
                            CURLOPT_POST => 1,
                            CURLOPT_HTTPHEADER => $headers,
                        ));
                        $result = curl_exec($ch);
                        if (curl_errno($ch)) {
                            $push_status = 'Curl Error:' . curl_error($ch);
                        }
                        curl_close($ch);
                        if (config('constants.push_log')) {
                            PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $push_status, json_encode($push_data), $result);
                        }
                    }
                } else {
                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Token Is empty", json_encode($push_data));
                }
            }
        } else {
            if (config('constants.push_log')) {
                PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Users Is not A Login With app");
            }
        }
        if ($notification_entry) {
            NotificationList::create([
                'push_type' => $push_data['push_type'],
                'user_id' => $push_data['user_id'],
                'from_user_id' => $push_data['from_user_id'],
                'push_title' => $push_data['push_title'],
                'push_message' => $push_data['push_message'],
                'object_id' => $push_data['object_id'],
            ]);
        }
    } else {
        if (config('constants.push_log')) {
            PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "User Cant Sent Push To Own Profile.");
        }
    }
}

function number_to_dec($number = "", $show_number = 2, $separated = '.', $thousand_separator = "")
{
    return number_format($number, $show_number, $separated, $thousand_separator);
}

function genUniqueStr($prefix = '', $length = 10, $table, $field, $isAlphaNum = false)
{
    $arr = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
    if ($isAlphaNum) {
        $arr = array_merge($arr, array(
            'a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'R', 'S',
            'T', 'U', 'V', 'X', 'Y', 'Z'
        ));
    }
    $token = $prefix;
    $maxLen = max(($length - strlen($prefix)), 0);
    for ($i = 0; $i < $maxLen; $i++) {
        $index = rand(0, count($arr) - 1);
        $token .= $arr[$index];
    }
    if (isTokenExist($token, $table, $field)) {
        return genUniqueStr($prefix, $length, $table, $field, $isAlphaNum);
    } else {
        return $token;
    }
}

function isTokenExist($token, $table, $field)
{
    if ($token != '') {
        $isExist = DB::table($table)->where($field, $token)->count();
        if ($isExist > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function get_fancy_box_html($path = "", $class = "img_75")
{
    return '<a data-fancybox="gallery" href="' . $path . '"><img class="' . $class . '" src="' . $path . '" alt="image" width=40 height=40></a>';
}

function general_date($date)
{
    return date('Y-m-d', strtotime($date));
}

function current_route_is_same($name = "")
{
    return $name == request()->route()->getName();
}

function is_selected_blade($id = 0, $id2 = "")
{
    return ($id == $id2) ? "selected" : "";
}

function clean_number($number)
{
    return preg_replace('/[^0-9]/', '', $number);
}

function print_query($builder)
{
    $addSlashes = str_replace('?', "'?'", $builder->toSql());
    return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
}

function user_status($status = "", $is_delete_at = false)
{
    if ($is_delete_at) {
        $status = "<span class='badge badge-danger'>Deleted</span>";
    } elseif ($status == "inactive") {
        $status = "<span class='badge badge-warning'>" . ucfirst($status) . "</span>";
    } else {
        $status = "<span class='badge badge-success'>" . ucfirst($status) . "</span>";
    }
    return $status;
}


function is_active_module($names = [])
{
    $current_route = request()->route()->getName();
    return in_array($current_route, $names) ? "kt-menu__item--active kt-menu__item--open" : "";
}

function echo_extra_for_site_setting($extra = "")
{
    $string = "";
    $extra = json_decode($extra);
    if (!empty($extra) && (is_array($extra) || is_object($extra))) {
        foreach ($extra as $key => $item) {
            $string .= $key . '="' . $item . '" ';
        }
    }
    return $string;
}

function upload_file($file_name = "", $path = null)
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

function upload_base_64_img($base64 = "", $path = "uploads/product/")
{
    $file = null;
    if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
        $data = substr($base64, strpos($base64, ',') + 1);
        $up_file = rtrim($path, '/') . '/' . md5(uniqid()) . '.png';
        $img = Storage::disk('local')->put($up_file, base64_decode($data));
        if ($img) {
            $file = $up_file;
        }
    }
    return $file;
}

function upload_multiple_file($files = "", $path = 'users_profile')
{
    $file = "";
    $request = \request();
    $files_names = [];
    if ($request->hasFile($files) && $path) {
        $files_list = $request->file($files);
        $path = "uploads/$path/";

        @mkdir(public_path($path), 0777, true);

        foreach ($files_list as $key => $file) {

            $file_name = md5($file->getClientOriginalName() . time() . $key) . '.' . $file->getClientOriginalExtension();

            $file->move(public_path($path), $file_name);

            $file_name = $path . $file_name;

            $files_names[] = $file_name;
        }

        return $files_names;
    } else {
        echo 'Provide Valid Const from web controller';
        die();
    }
}

function validateWallet($address)
{
    $url = "http://65.1.111.39/validate/$address";
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "$url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    return $response ? $response['valid'] : FALSE;
}

function QueryLogger($return = false)
{
    if ($return) {
        $logs = DB::getQueryLog();
        foreach ($logs as $key => $log) {
            $logs[$key]['raw_query'] = vsprintf(str_replace('?', '%s', str_replace('?', "'?'", $log['query'])), $log['bindings']);
        }
        dd($logs);
    }
    DB::enableQueryLog();
}
