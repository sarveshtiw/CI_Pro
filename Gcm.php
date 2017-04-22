<?php
class GCM {

    // constructor
    function __construct() {

    }

    // sending push message to single user by gcm registration id
    public function send($api_key, $to, $message) {
       $message = json_encode($message);
        $fields = array(
            'to' => $to,
            'notification' => $message,
        );
        return $this->sendPushNotification($fields,$api_key);
    }

    // Sending message to a topic by topic id
    public function sendToTopic($to, $message) {
        $fields = array(
            'to' => '/topics/' . $to,
            'data' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    // sending push message to multiple users by gcm registration ids
    public function sendMultiple($registration_ids, $message) {
        $fields = array(
            'registration_ids' => $registration_ids,
            'data' => $message,
        );

        return $this->sendPushNotification($fields);
    }

    // function makes curl request to gcm servers
    public function sendPushNotification($google_api_key, $deviceToken, $message) {

       //$deviceToken = array($deviceToken);
       $push_data['message'] = $message;
       $fields = array(
         "registration_ids" => array($deviceToken),
         "data" => $push_data
       );// print_r($fields); exit;

        // Set POST variables
        //  $GOOGLE_API_KEY = "AIzaSyCz6RYftgpYPx9ka-532kCeQs8jynAQFr4";
        $url = 'https://android.googleapis.com/gcm/send';
        //print_r($fields); exit;
        $headers = array(
            'Authorization: key=' . $google_api_key,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        //print_r($result); exit;
        //if ($result === FALSE) {
        //    die('Curl failed: ' . curl_error($ch));
        //}

        // Close connection
        curl_close($ch);

        return $result;
    }

}
