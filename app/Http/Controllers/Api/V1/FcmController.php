<?php

namespace App\Http\Controllers\Api\V1;

use App\AgoraToken;
use Illuminate\Http\Request;
use Google\Client;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Support\Facades\Auth;
use App\DeviceToken;
use Exception;
use App\User;

class FcmController extends ResponseController
{
    public function sendNotificationToAllUsers(Request $request)
    {
        $this->directValidation([
            'channel_name' => ['required', 'max:100']
        ]);
        $userId = Auth::id(); // Get the logged-in user's ID

        try {
            // Get the access token for FCM
            $token = $this->getAccessToken();

            // Retrieve the IDs of the logged-in user's followers
            $followerIds = User::join("followers", "followers.profile_id", "=", "users.id")
            ->where("followers.profile_id", $userId)
            ->pluck("followers.user_id");
            if (empty($followerIds)) {
                $this->sendError(__('api.err_no_followers'),false);
            }

            // Retrieve device tokens for the followers, excluding tokens of the logged-in user
            $deviceTokens = DeviceToken::whereIn('user_id', $followerIds)
                ->pluck('push_token')
                ->toArray();

            // Check if there are device tokens to notify
            if (empty($deviceTokens)) {
                $this->sendError(__('api.err_no_devices'), false);
            }

            $responses = [];
            foreach ($deviceTokens as $deviceToken) {
                $notificationData = [
                    "message" => [
                        "token" => $deviceToken, // Single device token
                        "notification" => [
                            "body" => Auth::user()->name." is going live",
                            "title" => "LIVE!!!",
                        ],
                        "data"=>[
                            "channel_name" => $request->channel_name
                        ]
                    ]
                ];

                $response = $this->sendNotification($token, $notificationData);
                $responses[$deviceToken] = $response; // Store response by device token
            }

            $user = User::find(Auth::id());
            $user->is_live = 1;
            $user->save();

            $this->sendResponse(200, __('api.succ_notifications_sent'));

        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function sendNotification($token, $notificationData)
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/nextgen-1665319772916/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response status code
    
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL error: $errorMsg");
        }
    
        curl_close($ch);
    
        // Check for HTTP status code errors
        if ($httpCode >= 400) {
            $responseData = json_decode($response, true);
            $errorMsg = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Unknown error';
            throw new \Exception("HTTP error $httpCode: $errorMsg");
        }
    
        return $response;
    }
    

    private function getAccessToken()
    {
        $client = new Client();
        $client->setAuthConfig(base_path('firebase.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $token = $client->fetchAccessTokenWithAssertion();
        
        if (isset($token['access_token'])) {
            return $token['access_token'];
        }

        throw new Exception('Unable to fetch access token.');
    }
}
