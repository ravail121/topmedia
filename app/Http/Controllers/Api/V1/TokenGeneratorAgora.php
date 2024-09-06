<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use BoogieFromZk\AgoraToken\RtcTokenBuilder2;

class TokenGeneratorAgora extends ResponseController
{
    public function generateToken(Request $request)
    {
        $this->directValidation([
            'channel_name' => ['required', 'max:100'],
            'uid' => ['required', 'max:100']
        ]);
        $appId = config('constants.agora.app_id');
        $appCertificate = config('constants.agora.app_certificate');

        $channelName = $request->channel_name;
        $uid = $request->uid;
        $tokenExpirationInSeconds = 3600;
        $privilegeExpirationInSeconds = 3600;

        $token = RtcTokenBuilder2::buildTokenWithUid($appId, $appCertificate, $channelName, $uid, RtcTokenBuilder2::ROLE_PUBLISHER, $tokenExpirationInSeconds, $privilegeExpirationInSeconds);
        $this->sendResponse(200, __('api.suc_token_generation'), $token);


    }
}
