<?php

use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'guest', 'namespace' => 'General'], function () {
    Route::post('login', 'GeneralController@login')->name('login_post');
    Route::get('login', 'GeneralController@Panel_Login')->name('login');
    Route::get('forgot_password', 'GeneralController@Panel_Pass_Forget')->name('forgot_password');
    Route::post('forgot_password', 'GeneralController@ForgetPassword')->name('forgot_password_post');
});

Route::group(['middleware' => 'Is_Admin'], function () {
    Route::get('/', 'General\GeneralController@Admin_dashboard')->name('dashboard');
    Route::get('/totalusers', 'General\GeneralController@totalusers')->name('totalusers');
    Route::get('/profile', 'General\GeneralController@get_profile')->name('profile');
    Route::post('/profile', 'General\GeneralController@post_profile')->name('post_profile');
    Route::get('/update_password', 'General\GeneralController@get_update_password')->name('get_update_password');
    Route::post('/update_password', 'General\GeneralController@update_password')->name('update_password');
    Route::get('/site_settings', 'General\GeneralController@get_site_settings')->name('get_site_settings');
    Route::post('/site_settings', 'General\GeneralController@site_settings')->name('site_settings');
    Route::group(['namespace' => 'Admin'], function () {
        //        User Module
        Route::get('user/following_listing/{id}', 'UsersController@followingListing')->name('user.following_listing');
        Route::get('user/follower_listing/{id}', 'UsersController@followerListing')->name('user.follower_listing');
        Route::get('user/post_listing/{id}', 'UsersController@postListing')->name('user.post_listing');
        Route::get('user/listing', 'UsersController@listing')->name('user.listing');
        Route::get('user/status_update/{id}', 'UsersController@status_update')->name('user.status_update');
        Route::resource('user', 'UsersController')->except(['create', 'store']);

        //Help Request
        Route::get('help-requests/listing', 'HelpController@listing')->name('help_requests.listing');
        Route::resource('help-requests', 'HelpController')->except(['create', 'store', "edit", "update", "show"]);

        //Posts
        Route::get('posts/listing', 'PostsController@listing')->name('posts.listing');
        Route::resource('posts', 'PostsController')->except(['create', 'store', "edit", "update"]);

        //Reported User
        Route::get('reported-users/listing', 'ReportedUsersController@listing')->name('reported_user.listing');
        Route::resource('reported-users', 'ReportedUsersController')->except(['create', 'store', "edit", "update", "show"]);

        //Reported User
        Route::get('reported-posts/listing', 'ReportedPostsController@listing')->name('reported_post.listing');
        Route::resource('reported-posts', 'ReportedPostsController')->except(['create', 'store', "edit", "update", "show"]);

        //        Content Module
        Route::resource('content', 'ContentController')->except(['show', 'create', 'store', 'destroy']);
        Route::get('content/listing', 'ContentController@listing')->name('content.listing');
    });
});
