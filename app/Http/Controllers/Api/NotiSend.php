<?php

namespace App\Http\Controllers\Api;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotiSend
{


    static function sendNotif2($token, $receiver, $title, $msg)
    {
        // Initialize Firebase
        $serviceAccountPath = base_path('app/hidden/rings-a4af2-c80f6226aaeb.json'); // Ensure path is correct
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $messaging = $factory->createMessaging();

        // Create Notification
        $notification = Notification::create($title, $msg);

        // Create Android-Specific Config
        $androidConfig = [
            'priority' => 'high',
            'notification' => [
                'channel_id' => 'realbazar', // Set your Android Channel ID
                'icon' => 'https://image.flaticon.com/icons/png/512/270/270014.png', // Optional
                'sound' => 'mySound', // Optional
            ],
        ];

        // Create Cloud Message
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData(['receiver' => $receiver]) // Optional additional data
            ->withAndroidConfig($androidConfig);

        // Send Message
        try {
            $messaging->send($message);
            return "Notification sent successfully.";
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            return "Messaging error: " . $e->getMessage();
        } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
            return "Firebase error: " . $e->getMessage();
        }
    }
    static  function sendNotif($token, $receiver, $title, $msg)
    {
        $serviceAccount = base_path('app/hidden/rings-a4af2-c80f6226aaeb.json'); // Update path as needed
        echo $serviceAccount;
        $msg = array(
            'body'  => "$msg",
            'title' => "$title",
            'android_channel_id' => "realbazar",
            'receiver' => $receiver,
            'icon'  => "https://image.flaticon.com/icons/png/512/270/270014.png",/*Default Icon*/
            'sound' => 'mySound'/*Default sound*/
        );

        $fields = array(
            'to'        => $token,
            'notification'  => $msg,
            'data' => [
                'user_id' => "$receiver"
            ]
        );

        $headers = array(
            'Authorization: key=' . $serviceAccount,
            'Content-Type: application/json'
        );
        //#Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
