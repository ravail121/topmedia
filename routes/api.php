<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {
    Route::get('content/{type}', 'GuestController@content');
    Route::post('login', 'GuestController@login');
    Route::post('testingNotifications', 'GuestController@testingNoti');
    Route::post('signup', 'GuestController@signup');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('version_checker', 'GuestController@version_checker');
    Route::post('social_login', 'GuestController@CheckSocialAbility');
    Route::post('social_register', 'GuestController@SocialRegister');

    // Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::group(['prefix' => 'user'], function () {

            Route::get('profile', 'UserController@getProfile');
            Route::post('update/password', 'UserController@ChangePassword');
            Route::post('update/profile', 'UserController@updateProfile');
            Route::post('update/crypto_address', 'UserController@UpdateCryptoWallet');
            Route::get('get_crypto_balance', 'UserController@GetCryptoBalance');
            Route::get('logout', 'UserController@logout');

            Route::get('details/{id}', 'UserController@GetFollowerDetails');
            Route::get('follow/{id}', 'UserController@StartFollow');
            Route::get('view/{id}', 'UserController@updateProfileAsViewed');
            Route::post('viewer_list', 'UserController@GetViewerList');

            Route::post('follower', 'UserController@GetFollowers');
            Route::post('following', 'UserController@GetFollowing');

            Route::post("help_request", "UserController@SubmitHelpRequest");
            Route::post("home", "UserController@Home");

            Route::post("report", "UserController@ReportUser");
            Route::get("reported/list", "UserController@getReportedFollowers");

            Route::get("remove_follower/{id}", "UserController@RemoveFollower");
            Route::get("delete_profile", "UserController@deleteProfile");
            Route::post("notification", "UserController@GetNotificationList");

            Route::get("notification/remove_all", "UserController@removeAllNotifications");
            Route::post('send_notifications_to_all_users', 'FcmController@sendNotificationToAllUsers');

            Route::post('agora_token_generator', 'TokenGeneratorAgora@generateToken');

            Route::post('search_user', 'UserController@searchUsers');
        });

        Route::group(['prefix' => 'posts'], function () {
            Route::post('like_comment', 'PostController@GetLikeAndCommentHistory');
            Route::post('all_non_media', 'PostController@GetNonMediaPosts');
            Route::post('all_media', 'PostController@GetAllMedia');
            Route::post('list', 'PostController@List');
            Route::post('video_list', 'PostController@VideoList');
            Route::post("report", "UserController@ReportPost");
            Route::post("hide", "UserController@HidePost");
            Route::post('create', 'PostController@create');
            Route::post('edit', 'PostController@edit');
            Route::get('details/{id}', 'PostController@Details');
            Route::get('delete/{id}', 'PostController@delete');
            Route::get('like_unlike/{id}', 'PostController@LikePost');
            Route::group(['prefix' => 'comment'], function () {
                Route::post('list', 'PostController@GetCommentList');
                Route::post('create', 'PostController@AddComment');
                Route::post('edit', 'PostController@EditComment');
                Route::get('delete/{id}', 'PostController@DeleteComment');
                Route::get('like_unlike/{id}', 'PostController@LikeComment');
                Route::group(['prefix' => 'reply'], function () {
                    Route::post('list', 'PostController@GetCommentReplyList');
                    Route::post('create', 'PostController@AddReplyComment');
                    Route::post('edit', 'PostController@EditReplyComment');
                    Route::get('delete/{id}', 'PostController@DeleteReplyComment');
                    Route::get('like_unlike/{id}', 'PostController@LikeCommentReply');
                });
            });
        });
    });
});
