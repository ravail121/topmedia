<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Google\Client;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Support\Facades\Auth;
use App\DeviceToken;
use Exception;

class FcmController extends ResponseController
{
    public function sendNotificationToAllUsers(Request $request)
    {
        $userId = Auth::id(); // Get the logged-in user's ID

        try {
            // Get the access token for FCM
            $token = $this->getAccessToken();

            // Retrieve device tokens excluding the logged-in user's tokens
            $deviceTokens = DeviceToken::where('user_id', '!=', $userId)
                ->pluck('token')
                ->toArray();

            // Check if there are device tokens to notify
            if (empty($deviceTokens)) {
                return response()->json(['message' => 'No devices to notify.'], 200);
            }

            $responses = [];
            foreach ($deviceTokens as $deviceToken) {
                $notificationData = [
                    "message" => [
                        "token" => $deviceToken, // Single device token
                        "notification" => [
                            "body" => "This is an FCM notification message!",
                            "title" => "FCM Message",
                        ]
                    ]
                ];

                $response = $this->sendNotification($token, $notificationData);
                $responses[$deviceToken] = $response; // Store response by device token
            }

            $this->sendResponse(200, __('api.succ_notifications_sent'));

        } catch (Exception $e) {
            $this->sendError(__('api.err_something_went_wrong'));
        }
    }

    private function sendNotification($token, $notificationData)
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/topmedia-3dcbe/messages:send');
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
