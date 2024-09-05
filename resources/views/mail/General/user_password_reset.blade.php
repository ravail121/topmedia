@extends('layouts.mail.app')
@section('content')
    <tr>
        <td class="pb-4" style="padding-bottom:  1.5rem!important; padding-right: 45px; padding-left: 45px;">
            <h1 style="font-size: 30px;color: #404040;">
                Password reset
            </h1>
        </td>
    </tr>
    <tr>
        <td class="pb-4" style="padding-bottom:  1.5rem!important; padding-right: 45px; padding-left: 45px;">
            Hello there,
        </td>
    </tr>
    <tr>
        <td class="pb-4" style="padding-bottom:  1.5rem!important; padding-right: 45px; padding-left: 45px;">
            We’ve received a request to reset your password for your {{site_name}} account.
        </td>
    </tr>
    <tr>
        <td class="pb-4" style="padding-bottom:  1.5rem!important; padding-right: 45px; padding-left: 45px;">
            <span>
                <b>To reset your password, please click on the button below.</b>
            </span>
            This link is valid for the next 30 minutes.
        </td>
    </tr>
    <tr>
        <td class="pb-4" style="padding-bottom:  1.5rem!important; padding-right: 45px; padding-left: 45px;">
            <a style="border-radius: 0.3rem; background: #000; padding: 5px;text-decoration: underline;color: #fff;font-weight: 500;"
               href="{{route('front.forgot_password_view',$user->reset_token)}}">
                PASSWORD RESET
            </a>
        </td>
    </tr>
    <tr>
        <td class="pb-4" style="padding-bottom:  1.5rem!important; padding-right: 45px; padding-left: 45px;">
            If you did not request your password to be reset but have received this email, please check your notify us
            at <a href="mailto:{{ADMIN_EMAIL}}"><b>{{ADMIN_EMAIL}}</b></a> immediately.
        </td>
    </tr>
@endsection
