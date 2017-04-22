<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Campaign extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('credit_card_helper', 'time'));
        $this->load->library(array('form_validation', 'facebook', 'email', 'image_lib', 'user_agent', 'Mobile_Detect', 'session'));
        $this->load->model(array('user_model','score_model', 'offer_model', 'email_model', 'notification_model', 'country_model', 'administrator_model', 'campaign_model', 'social_model', 'businessstore_model','location_model'));
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active == 0) {
          redirect(base_url());
        }
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    public static function ago_time($date = NULL) {
        if (empty($date)) {
            return "No date provided";
        }

        $periods = array("s", "m", "h", "d", "w", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $unix_date = strtotime($date);

        // check validity of date
        if (empty($unix_date)) {
            return "Bad date";
        }

        // is it future date or past date
        /* echo $now.' </br>' ;
          echo $unix_date ; */
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";
        } else {
            $difference = $unix_date - $now;
            //$tense         = "from now";
            $tense = "ago";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        /* if($difference != 1) {
          $periods[$j].= "s";
          } */

        $showtime = "$difference $periods[$j]";

        if ($periods[$j] == 'month' || $periods[$j] == 'year') {
            $showtime = date("d/m/y", strtotime($date));
        }

        return $showtime;
    }

    function createCampaign($typeId,$userIds = false,$totalUsers = false) {

        $login = $this->administrator_model->front_login_session();

        $userid = $login->businessId;

        $userPackage = $this->campaign_model->getUserPackageInfo($userid);
        if (count($userPackage) > 0) {
            $data['countTotalCampaign'] = $userPackage->totalCampaigns;
        } else {
            $data['countTotalCampaign'] = 0;
        }

        // get all locations


        if($login->usertype == 7 || $login->usertype == 2){


          $data['locations'] = $this->location_model->getUserLocations1($userid);
        }
        else if($login->usertype == 6){
          $data['locations'] = $this->user_model->getAllBranchesByUserId($login->businessId,$userid);
        }
         //  echo '<pre>'; print_r($data['locations']); exit;

        //Extra Campaigns
        //Package id 1, 2, 3 for create campaigns
        $extraPackage = $this->campaign_model->gerUserPackage($userid);
        //echo '<pre>'; print_r( $data['locations']);exit;
        //echo $this->db->last_query(); die;
        if (count($extraPackage) > 0) {
            $data['extraCampaignQuantity'] = $extraPackage->quantity;
        } else {
            $data['extraCampaignQuantity'] = 0;
        }
        $data['typeId'] = $typeId;

        $data['userIds'] = $userIds;
        $data['totalUsers'] = $totalUsers;
        if($typeId == 1){
            $this->load->view('create_campaign', $data);
        }else{
            $this->load->view('insights_create_campaign', $data);
        }
    }

    function saveCampaign() {

        unset($_SESSION['locations']);

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;
        $username = $login->username;

        if (@$_FILES['imageCampaign']['size'] > 0) {

            // Image upload in full size in profile directory
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');

            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $userid;
            $mediumImagePath = 'upload/status_image/medium/' . $userid;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $tmp_name = $_FILES["imageCampaign"]["tmp_name"];
            $name = mktime() . $_FILES["imageCampaign"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            // image resize in medium size in medium directory
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = "upload/status_image/full/" . $userid . "/" . $name;
            $config['new_image'] = 'upload/status_image/medium/' . $userid . "/" . $name;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 400;
            $config['height'] = 350;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();

            $notification_image = $userid . "/" . $name;
            // Image Upload End
        } else {
            $notification_image = '';
        }

        $notification = $_POST['notification'];

        if ($_POST['type'] == 'offer') {
            $discountPercentage = '';
            $type = 1;
        } else {
            $discountPercentage = $_POST['discoutCampaign'];
            $type = 2;
        }
        $minAge = isset($_POST['minAge'])?$_POST['minAge']:0;
        $maxAge = isset($_POST['maxAge'])?$_POST['maxAge']:0;
        $gender = isset($_POST['gender'])?$_POST['gender']:0;
        if (isset($_POST['gender']) == 'all') {
            $gender = 1;
        } elseif (isset($_POST['gender']) == 'men') {
            $gender = 2;
        } elseif (isset($_POST['gender']) == 'women') {
            $gender = 3;
        }

        $users_who_have = isset($_POST['users_who_have'])?$_POST['users_who_have']:'';
        $available = $_POST['availabe'];
       // $locationId = $_POST['location'][$i];
        $this->load->helper('string');
        //echo random_string('numeric', 4); die;
        /* $randomString = $this->RandomStringQRCode(4);
          if(strlen($randomString) == '4'){
          $qrCode = $randomString;
          }else{
          $qrCode = mt_rand(1000,9999);
          } */
        $startdate = $_POST['day'];
        $enddate = $_POST['endday'];

        $qrCode = mt_rand(1000, 9999);
        $date = date('Y-m-d H:i:s');
        $insert = array(
            'campaign_id' => '',
            'user_id' => $userid,
            'notification' => $notification,
            'notification_image' => $notification_image,
            'type' => $type,
            'coins' => $_POST['campaign_coin'],
            'discount_percentage' => $discountPercentage,
            'min_age' => $minAge,
            'max_age' => $maxAge,
            'gender' => $gender,
            'users_who_have' => $users_who_have,
            'availability' => $available,
            'startDate' => $startdate,
            'businessId' => $login->businessId,
            'CampType' => $_POST['CampType'],
            'totalAvailable'=>$available,
            'campaignRadius'=>1000,
            'endDate' => $enddate,
            'qr_code' => $qrCode,
            'isActive' => 1,
            'isDelete' => 0,
            'createdDate' => $date
        );

        $insertId = $this->campaign_model->insert_campaign($insert);
        if(!empty($_POST['location'])){
          $this->campaign_model->campaignLocationMap($insertId,$_POST['location']);
        }
        //Create status for campaign

        $userstatus = $notification;
        /* Array of Status */
        $status['status_id'] = '';
        $status['userid'] = $userid;
        $status['status_image'] = $notification_image;
        $status['createdDate'] = date('YmdHis');
        $status['usermentioned'] = $username;
        $status['hasgTag'] = '';

        $statusid = $this->user_model->saveUserStatus($status);   //// save new status into database
        $redeemUrl = base_url() . 'redeemOffer/index/' . $insertId;

        $arr_status['status'] = str_replace('changeparameter(New);', "changeparameter(" . $statusid . ");", $userstatus . '<br><br> ' . $redeemUrl);
        $arr_status['status_id'] = $statusid;
        $statusid = $this->user_model->saveUserStatus($arr_status);   //// update last inserted status
        //End Create status for campaign
        //Update extra package
        $extraPackage = $this->campaign_model->gerUserPackage($userid);
        if (count($extraPackage) > 0 && ($extraPackage->quantity > 0 || $extraPackage->quantity == 'unlimited')) {
            if ($extraPackage->quantity != 'unlimited') {
                $update['quantity'] = $extraPackage->quantity - 1;
            } else {
                $update['quantity'] = $extraPackage->quantity;
            }
            //$update['userid'] = $userid;
            //$update['packageid'] = $extraPackage->packageid;
            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
            $this->campaign_model->updateExtraPackage($update);
        } else {
            //Update total campaigns
            $userPackage = $this->campaign_model->getUserPackageInfo($userid);
            $totalCampaigns = $userPackage->totalCampaigns;
            $updateTotalCampaigns = $totalCampaigns - 1;

            $update = array(
                'user_id' => $userid,
                'totalCampaigns' => $updateTotalCampaigns
            );
            $this->campaign_model->updateTotalCampaigns($update);
        }

        $allIds = $this->user_model->getAllfolloweduserIds($userid,$gender,$minAge,$maxAge);

        if(count($allIds)>0){
            for($i = 0; $i< count($allIds); $i++){
                 // web notification code
                $arr_notice['notification_id'] = '';
                $arr_notice['actionFrom'] = $userid;
                $arr_notice['actionTo'] = $allIds[$i];
                $arr_notice['action'] = 'PO';
                $arr_notice['actionString'] = 'Here is an amazing offer!';
                $arr_notice['message'] = '';
                $arr_notice['statusid'] = $statusid;
                $arr_notice['challangeid'] = '';
                $arr_notice['offerId'] = $insertId;
                $arr_notice['active'] = '1';
                $arr_notice['createdDate'] = date('YmdHis');

                $notice_id = $this->notification_model->savenotification($arr_notice);

                // end
            }
        }

        $getBusinessName = $this->user_model->getBusinessName($userid);
        if ($discountPercentage == '') {
            $autotext = ucfirst($getBusinessName->businessName) . 'has ' . $discountPercentage . ' off.';
        } else {
            $autotext = ucfirst($getBusinessName->businessName) . ' is running an offer.';
        }

        $firstname = isset($login->firstname) ? $login->firstname : '';
        $lastname = isset($login->lastname) ? $login->lastname : '';
        $name = $firstname . ' ' . $lastname;
        if (!empty($notification_image)) {
            $offerimage = base_url() . 'upload/status_image/full/' . $notification_image;
        } else {
            $offerimage = '';
        }
        $notification = $notification .' from '.'@'.ucfirst($login->username);
        if (isset($_POST['gender']) == 'all') {
            $gender = 'all';
        } elseif (isset($_POST['gender']) == 'men') {
            $gender = 'male';
        } elseif (isset($_POST['gender']) == 'women') {
            $gender = 'female';
        }
        $typeId = $_POST['CampType'];
        if ($typeId ==1){

            $data1 ['offerId'] = $insertId;
            $data1 ['offerUrl'] = base_url() . 'redeemOffer/index/' . $insertId;
            $data1 ['name' ] = $name;
            $data1 ['gender'] = $_POST['gender'];
            $data1 ['minAge'] = $minAge;
            $data1 ['maxAge'] = $maxAge;
            $data1 ['username'] = $login->username;
            $data1 ['userimage'] = base_url() . 'upload/profile/thumbnail/' . $login->image;
            $data1 ['offerimage'] = $offerimage;
            $data1 ['createdDate' ] = $date;
            $data1 ['availability'] = $_POST['availabe'];
            $data1 ['startDate'] = $startdate;
            $data1 ['autoText' ] = $notification;
            $data1 ['discountValue' ] = $discountPercentage;
            $data1 ['coins' ] = $_POST['campaign_coin'];
            $data1 ['type' ] = 'publicOffer';
            $data1 ['userid'] = $userid;

            $this->sendOfferPushNotification($data1);
        }
        // craete campaign for new users
        else if ($typeId == 2 || $typeId == 3 || $typeId == 4 || $typeId== 5 || $typeId == 6)
        {
            $data1 ['offerId'] = $insertId;
            $data1 ['offerUrl'] = base_url() . 'redeemOffer/index/' . $insertId;
            $data1 ['name' ] = $name;
            $data1 ['gender'] = $gender;
            $data1 ['minAge'] = $minAge;
            $data1 ['maxAge'] = $maxAge;
            $data1 ['username'] = $login->username;
            $data1 ['userimage'] = base_url() . 'upload/profile/thumbnail/' . $login->image;
            $data1 ['offerimage'] = $offerimage;
            $data1 ['createdDate' ] = $date;
            $data1 ['availability'] = $available;
            $data1 ['autoText' ] = $notification;
            $data1 ['discountValue' ] = $discountPercentage;
            $data1 ['coins' ] = $_POST['campaign_coin'];
            $data1 ['type' ] = 'publicOffer';
            $data1 ['userid'] = $_POST['userIds'];

            $this->sendPushNotification($data1);
        }
        else if($typeId == 8 || 9)
        {
            if($_POST['userIds'] == ('male' || 'female')){
                   $gendergroup = $_POST['userIds'];

                    $data1 ['offerId'] = $insertId;
                    $data1 ['offerUrl'] = base_url() . 'redeemOffer/index/' . $insertId;
                    $data1 ['name' ] = $name;
                    $data1 ['gender'] = $gender;
                    $data1 ['minAge'] = $minAge;
                    $data1 ['maxAge'] = $maxAge;
                    $data1 ['username'] = $login->username;
                    $data1 ['userimage'] = base_url() . 'upload/profile/thumbnail/' . $login->image;
                    $data1 ['offerimage'] = $offerimage;
                    $data1 ['createdDate' ] = $date;
                    $data1 ['availability'] = $available;
                    $data1 ['autoText' ] = $notification;
                    $data1 ['discountValue' ] = $discountPercentage;
                    $data1 ['coins' ] = $_POST['campaign_coin'];
                    $data1 ['type' ] = 'publicOffer';
                    $data1 ['userid'] = $gendergroup;

                    $this->sendPushNotificationForGender($data1);
             }else{

                  $agegroup = $_POST['userIds'];

                  $data1 ['offerId'] = $insertId;
                  $data1 ['offerUrl'] = base_url() . 'redeemOffer/index/' . $insertId;
                  $data1 ['name' ] = $name;
                  $data1 ['gender'] = $gender;
                  $data1 ['minAge'] = $minAge;
                  $data1 ['maxAge'] = $maxAge;
                  $data1 ['username'] = $login->username;
                  $data1 ['userimage'] = base_url() . 'upload/profile/thumbnail/' . $login->image;
                  $data1 ['offerimage'] = $offerimage;
                  $data1 ['createdDate' ] = $date;
                  $data1 ['availability'] = $available;
                  $data1 ['autoText' ] = $notification;
                  $data1 ['discountValue' ] = $discountPercentage;
                  $data1 ['coins' ] = $_POST['campaign_coin'];
                  $data1 ['type' ] = 'publicOffer';
                  $data1 ['userid'] = $agegroup;

                 $this->sendPushNotificationForAgeGroup($data1);
            }
        }


      $totalEmail = $this->offer_model->getRedeemedUsersEmail($login->user_id);

        if(isset($totalEmail[1]) && count($totalEmail[1]) >0){

            for($i = 0; $i<count($totalEmail[1]); $i++){


            $userResult = $this->user_model->getUsernameByEmail($totalEmail[1][$i]->email);

            $username = $userResult->username;
            $BusinessUsername = $login->username;
            $notification = $notification;
            $url = base_url() . 'redeemOffer/index/' . $insertId;
            $data['BusinessUsername'] = $BusinessUsername;
            $data['username'] =$username;
            $data['notification'] = $notification;
            $data['url'] = $url;

            $message = $this->offer_model->sendEmailToInvitedUsers($data);


             $insertData[$i] =
                     array(
                        'email_id'=> $totalEmail[1][$i]->email,
                        'subject'=> "Here's an amazing offer!",
                        'userid'=>$userResult->user_Id,
                        'campaignId'=>$insertId,
                        'message'=> $message,
                        'from_email'=>'Hello@Hurree.co',
                        'emailSentOn'=> date('y-m-d h:i:s'),
                        'emailSentByUser'=>  $login->user_id,
                        'created_on'=> date('y-m-d h:i:s'),
                        'active'=> 0,
                        'userType'=> $userResult->usertype

                     );

          }

//             $checkredeemedUsersIds = array();
//             for($i = 0; $i< count($totalEmail[0]); $i++){
//                 if(!in_array($totalEmail[0][$i],$checkredeemedUsersIds)){
//                 array_push($checkredeemedUsersIds,$totalEmail[0][$i]);
//                 }
//            }
//
//            for($i = 0; $i< count($checkredeemedUsersIds); $i++){
//
//              $data1[$i] = array(
//                           'userid' => $checkredeemedUsersIds[$i] ,
//                           'offerid' => $insertId,
//                           'isEmailSent'=>1,
//                    'active'=>1
//                        );
//            }
//            echo '<pre>'; print_r($data1); exit;
             //$this->db->insert_batch('redeem_offer', $data1);
                    $this->db->insert_batch('email_info', $insertData);
       }

        // get locations name  by location ids
      if(!empty($_POST['location'])){
        $locationsName = $this->location_model->getLocationName($_POST['location']);
        $locations= array();
        foreach($locationsName as $location){
            $locations[] = $location->store_name;
        }

        // send email to users which allocated by these locations
        $result = $this->location_model->getUsersLocation($userid,$_POST['location']);

        $emails = array(); $emailArray= array();
        foreach($result as $email){
            $emailArray[] = $email->email;

         }
      }
           // send email to master admin which are created by logged in users

            $result = $this->user_model->getAllMasterAdmins($login->businessId);

            foreach($result as $details){
              $emailArray[] = $details->email;

          }
          for($i = 0; $i<count($emailArray); $i++){
            $username = $this->user_model->getUsernameByEmail($emailArray[$i]);
            $data['qrCode'] =  $qrCode;
            $data['email'] = $emailArray[$i];
            $data['businessUsername'] = $login->username;
            $data['Username'] = $username->username;
            if(!empty($_POST['location'])){
               $data['locations'] = implode($locations);
            }
            $data['notification'] = $notification;
            $data['startDate'] = $startdate;
            $data['endDate'] = $enddate;
            $message = $this->emailOffer($data);
            // attachment
            if($i == 0){
              $attachment = $this->mailPdf($insertId);
            }
             $insertData[$i] =
                     array(
                        'email_id'=> $emailArray[$i],
                        'subject'=> "There's a campaign for your location!",
                        'message'=> $message,
                        'userid'=>$username->user_Id,

                        'campaignId'=>$insertId,
                        'from_email'=>'Hello@Hurree.co',
                        'attachment'=> $_SERVER["DOCUMENT_ROOT"].'upload/Campaign_pdf/Hurree-QR-code-offer.pdf',
                        'emailSentOn'=> date('y-m-d h:i:s'),
                        'emailSentByUser'=>  $login->user_id,
                        'created_on'=> date('y-m-d h:i:s'),
                        'active'=> 0,
                        'userType'=>  $username->usertype

                     );

          }
           $this->db->insert_batch('email_info', $insertData);

           //echo '<pre>'; print_r($emailArray); exit;




            // end
      echo $insertId; exit;
   }

   function sendPushNotificationForGender($data1){
       // send aws notification code start
    	  $this->load->model('user_model');

        $deviceInfo = $this->user_model->getAllUsersDeviceTokenForGender($data1['userid']);

        if(count($deviceInfo)>0){
            foreach ($deviceInfo as $device) {
                $deviceToken = $device->key;
                $deviceType = $device->deviceTypeID;

                $title = 'My Test Message';
                $sound = 'default';
                $msgpayload=json_encode(array(
                        'aps' => array(
                        'alert' => $data1['autoText'],
                        'offerId'=> $data1['offerId'],
                        'OfferUrl' => $data1['offerUrl'],
                        'name' => $data1['name'],
                        'username'=>$data1['username'],
                        'userimage'=>$data1['userimage'],
                        'offerimage'=>$data1['offerimage'],
                        'createdDate'=>$data1['createdDate'],
                        'availability'=>$data1['availability'],
                        'discountValue'=>$data1['discountValue'],
                        'coins'=>$data1['coins'],
                        'type'=>$data1['type'],
                        'sound'=>$sound

                        )));

                $message = json_encode(array(
                    'default' => $title,
                    'APNS_SANDBOX' => $msgpayload
                ));

                $result = $this->amazonSns($deviceToken,$message,$deviceType);
            }
        }
    }

    function sendPushNotificationForAgeGroup($data1){
       // send aws notification code start
    	  $this->load->model('user_model');

        $deviceInfo = $this->user_model->getAllUsersDeviceTokenForAgeGroup($data1['userid']);

        if(count($deviceInfo)>0){
            foreach ($deviceInfo as $device) {
                $deviceToken = $device->key;
                $deviceType = $device->deviceTypeID;

                $title = 'My Test Message';
                $sound = 'default';
                $msgpayload=json_encode(array(
                        'aps' => array(
                        'alert' => $data1['autoText'],
                        'offerId'=> $data1['offerId'],
                        'OfferUrl' => $data1['offerUrl'],
                        'name' => $data1['name'],
                        'username'=>$data1['username'],
                        'userimage'=>$data1['userimage'],
                        'offerimage'=>$data1['offerimage'],
                        'createdDate'=>$data1['createdDate'],
                        'availability'=>$data1['availability'],
                        'discountValue'=>$data1['discountValue'],
                        'coins'=>$data1['coins'],
                        'type'=>$data1['type'],
                        'sound'=>$sound

                        )));


                $message = json_encode(array(
                    'default' => $title,
                    'APNS_SANDBOX' => $msgpayload
                ));

                $result = $this->amazonSns($deviceToken,$message,$deviceType);
            }
        }
    }

    function sendOfferPushNotification($data1){

       // send aws notification code start
    	  $this->load->model('user_model');

        $deviceInfo = $this->user_model->getAllDeviceToken($data1['userid'],$data1['gender'],$data1['minAge'],$data1['maxAge']);
         //echo '<pre>'; print_r($deviceInfo); exit;
        if(count($deviceInfo)>0){
            foreach ($deviceInfo as $device) {
                $deviceToken = $device->key;
                $deviceType = $device->deviceTypeID;

                $title = 'My Test Message';
                $sound = 'default';
                $msgpayload=json_encode(array(
                        'aps' => array(
                        'alert' => $data1['autoText'],
                        'offerId'=> $data1['offerId'],
                        'OfferUrl' => $data1['offerUrl'],
                        'name' => $data1['name'],
                        'username'=>$data1['username'],
                        'startDate'=>$data1['startDate'],
                        'userimage'=>$data1['userimage'],
                        'offerimage'=>$data1['offerimage'],
                        'createdDate'=>$data1['createdDate'],
                        'availability'=>$data1['availability'],
                        'discountValue'=>$data1['discountValue'],
                        'coins'=>$data1['coins'],
                        'type'=>$data1['type'],
                        'sound'=>$sound

                        )));


                $message = json_encode(array(
                'default' => $title,
                'APNS_SANDBOX' => $msgpayload
                ));

                $result = $this->amazonSns($deviceToken,$message,$deviceType);
            }
       }

    // end
    }

    function sendPushNotification($data1){
       // send aws notification code start
    	  $this->load->model('user_model');

        $deviceInfo = $this->user_model->getAllUsersDeviceToken($data1['userid']);
         //echo '<pre>'; print_r($deviceInfo); exit;
        if(count($deviceInfo)>0){
            foreach ($deviceInfo as $device) {
                $deviceToken = $device->key;
                $deviceType = $device->deviceTypeID;

                $title = 'My Test Message';
                $sound = 'default';
                $msgpayload=json_encode(array(
                        'aps' => array(
                        'alert' => $data1['autoText'],
                        'offerId'=> $data1['offerId'],
                        'OfferUrl' => $data1['offerUrl'],
                        'name' => $data1['name'],
                        'username'=>$data1['username'],
                        'userimage'=>$data1['userimage'],
                        'offerimage'=>$data1['offerimage'],
                        'createdDate'=>$data1['createdDate'],
                        'availability'=>$data1['availability'],
                        'discountValue'=>$data1['discountValue'],
                        'coins'=>$data1['coins'],
                        'type'=>$data1['type'],
                        'sound'=>$sound

                        )));


                $message = json_encode(array(
                'default' => $title,
                'APNS_SANDBOX' => $msgpayload
                ));

                $result = $this->amazonSns($deviceToken,$message,$deviceType);
            }
        }
    }

 // aws push notification
		public function amazonSns($deviceToken,$message,$deviceType){

  		  $this->load->library('Aws_sdk');
  		  $Aws_sdk = new Aws_sdk();
  		  if($deviceType == 'ios'){
    		  $iOS_AppArn = "arn:aws:sns:us-west-2:831947047245:app/APNS_SANDBOX/Hurree";

    		  $endpoint = $Aws_sdk->generateEndpoint($deviceToken,$iOS_AppArn);

    		  $result = $Aws_sdk->SendPushNotification($message,$endpoint,$deviceToken);

    		  return $result;
  		  }
		}

    function createOffer() {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $data['statusid'] = $this->uri->segment('3');
        $data['receiverid'] = $this->uri->segment('4');

        $offer = $this->campaign_model->getUserOffers($data);
        $data['offerCount'] = count($offer);

        $userPackage = $this->campaign_model->getUserPackageInfo($userid);
        if (count($userPackage) > 0) {
            $data['countTotalIndividualCampaigns'] = $userPackage->totalIndividualCampaigns;
        } else {
            $data['countTotalIndividualCampaigns'] = 0;
        }

        $extraPackage = $this->campaign_model->gerUserIndivdualPackage($userid);
        //echo $this->db->last_query(); die;
        if (count($extraPackage) > 0) {
            $data['extraIndividualQuantity'] = $extraPackage->quantity;
        } else {
            $data['extraIndividualQuantity'] = 0;
        }
        $this->load->view('createoffer', $data);
    }

    function saveOffer() {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        if (@$_FILES['imageOffer']['size'] > 0) {

            // Image upload in full size in profile directory
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $userid;
            $mediumImagePath = 'upload/status_image/medium/' . $userid;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $tmp_name = $_FILES["imageOffer"]["tmp_name"];
            $name = mktime() . $_FILES["imageOffer"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            // image resize in medium size in medium directory
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = "upload/status_image/full/" . $userid . "/" . $name;
            $config['new_image'] = 'upload/status_image/medium/' . $userid . "/" . $name;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 400;
            $config['height'] = 350;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();

            $notification_image = $userid . "/" . $name;
            // Image Upload End
        } else {
            $notification_image = '';
        }

        $notification = $_POST['notification'];

        if ($_POST['type'] == 'offer') {
            $discountPercentage = '';
            $type = 1;
        } else {
            $discountPercentage = $_POST['discoutPercentage'];
            $type = 2;
        }

        //$qrCode = $this->RandomStringQRCode(5);
        $date = date('Y-m-d H:i:s');
        $statusid = $_POST['statusid'];
        $receiverid = $_POST['receiverid'];
        /* $qrCode = $this->RandomStringQRCode(4); */
        $qrCode = mt_rand(1000, 9999);

        $insert = array(
            'offer_id' => '',
            'user_id' => $userid,
            'receiver_id' => $receiverid,
            'status_id' => $statusid,
            'notification' => $notification,
            'notification_image' => $notification_image,
            'type' => $type,
            'discount_percentage' => $discountPercentage,
            'qr_code' => $qrCode,
            'isActive' => 1,
            'isDelete' => 0,
            'createdDate' => $date
        );

        $insertId = $this->campaign_model->insert_offers($insert);

        //Update Extra Individual Campaigns
        $extraPackage = $this->campaign_model->gerUserIndivdualPackage($userid);
        if (count($extraPackage) > 0 && $extraPackage->quantity > 0) {

            $update['quantity'] = $extraPackage->quantity - 1;

            //$update['userid'] = $userid;
            //$update['packageid'] = $extraPackage->packageid;
            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
            $this->campaign_model->updateExtraPackage($update);
        } else {
            //Update total individual campaigns
            $userPackage = $this->campaign_model->getUserPackageInfo($userid);
            $totalIndividualCampaigns = $userPackage->totalIndividualCampaigns;
            $updateTotalIndividualCampaigns = $totalIndividualCampaigns - 1;

            $update = array(
                'user_id' => $userid,
                'totalIndividualCampaigns' => $updateTotalIndividualCampaigns
            );
            $this->campaign_model->updateIndividualCampaigns($update);
        }
        $lastId = $this->offer_model->getLastId();

        // web notification code
        $arr_notice['notification_id'] = '';
        $arr_notice['actionFrom'] = $userid;
        $arr_notice['actionTo'] = $receiverid;
        $arr_notice['action'] = 'IO';
        $arr_notice['actionString'] = 'Here is an amazing offer!';
        $arr_notice['message'] = '';
        $arr_notice['statusid'] = $statusid;
        $arr_notice['challangeid'] = '';
        $arr_notice['offerId'] = $lastId->offer_id;
        $arr_notice['active'] = '1';
        $arr_notice['createdDate'] = date('YmdHis');
        $notice_id = $this->notification_model->savenotification($arr_notice);

        // end
        //push notification code
        $firstname = isset($login->firstname) ? $login->firstname : '';
        $lastname = isset($login->lastname) ? $login->lastname : '';
        $name = $firstname . ' ' . $lastname;
        if (!empty($notification_image)) {
            $offerimage = base_url() . 'upload/status_image/full/' . $notification_image;
        } else {
            $offerimage = '';
        }
        $availability = '';
        $fields = array(
            'offerId' => urlencode($insertId),
            'offerUrl' => urlencode(base_url() . 'redeemOffer/index/' . $insertId),
            'receiverid' => urlencode($receiverid),
            'name' => urlencode($name),
            'username' => urlencode($login->username),
            'userimage' => urlencode(base_url() . 'upload/profile/thumbnail/' . $login->image),
            'offerimage' => urlencode($offerimage),
            'createdDate' => urlencode(date('YmdHis')),
            'availability' => urlencode($availability),
            'autoText' => urlencode($notification),
            'discountValue' => urlencode($discountPercentage),
            'coins' => urlencode('200'),
            'type' => urlencode('individualOffer')
        );
        $post = '';

        foreach ($fields as $key => $value) {
            $post .= $key . '=' . $value . '&';
        }

        $post = rtrim($post, '&');

        $ch = curl_init();
        $to = base_url() . 'OfferNotification/sendIndividualOfferPushNotification';
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_POST, 13);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // this results 0 every time
        $response = curl_exec($ch);
        ///echo $response; exit;
        if ($response === false)
            $response = curl_error($ch);
        //echo stripslashes($response); exit;
        curl_close($ch);
        // end

        echo 1;
    }

    function RandomStringQRCode($length) {

        //$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        //    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $token = '';
        $codeAlphabet = "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
        }
        return $token;


        /* $characters = '0123456789';
          $randomString = '';
          for ($i = 0; $i < 4; $i++) {
          $randomString .= $characters[rand(0, strlen($characters) - 1)];
          }
          return $randomString; */

        /* $characters = '0123456789';
          $charactersLength = strlen($characters);
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
          }
          return $randomString; */
    }

    function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 1)
            return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    function getOfferCode() {
        $campaignId = $this->uri->segment(3);
        $campaignData = $this->campaign_model->getCampaigns($campaignId);
        foreach ($campaignData as $campaign) {
            $campaign->qr_code;
        }
        $data['qr_code'] = $campaign->qr_code;
        $this->load->view('get_offer_code', $data);
    }

    function pdf($id) {

        $this->load->helper('pdf_helper');

        //$id = $this->uri->segment(3);
        $arr_where['campaign_id'] = $id;
        $arr_where['isActive'] = 1;
        $arr_where['isDelete'] = 0;
        $data['qrcode'] = $this->campaign_model->getQrCode('*', $arr_where);
        $this->load->view('pdfreport', $data);
    }

    function mailPdf($id) {

        $this->load->helper('pdf_helper');

        //$id = $this->uri->segment(3);
        $arr_where['campaign_id'] = $id;
        $arr_where['isActive'] = 1;
        $arr_where['isDelete'] = 0;
        $data['qrcode'] = $this->campaign_model->getQrCode('*', $arr_where);
        $this->load->view('PDF_for_mail', $data);
    }

    public function postcampaign() {
        require_once APPPATH . "/third_party/tumblr/lib/tumblrPHP.php";
	      $campaignId = $_POST['campaignId'];
        $campaignData = $this->campaign_model->getCampaigns($campaignId);
        $login = $this->administrator_model->front_login_session();
	      if(!empty($campaignData)){
            $campaignOwner = $campaignData->user_id;
            $campaignImage = host_ip_url . 'upload/status_image/full/' . $campaignData->notification_image;

            $campaignText = $campaignData->notification;
            $campaignUrl = base_url() . 'redeemOffer/index/' . $campaignId;
            //campaign user data
            $campaignUserData = $this->user_model->getOneUser($campaignData->user_id);
            $campaignOwnerName = $campaignUserData->firstname . ' ' . $campaignUserData->lastname;
            //socail table data of campain user
            $socialData = $this->social_model->getAllSocialAccountsOfCampaingUser($campaignOwner);

            foreach ($socialData as $socialAccount) {
                switch ($socialAccount->source) {
                    /*case "facebook":
                        $userid  = $campaignOwner;
                        $message = $campaignData[0]->notification;
                        $page_id = $socialAccount->source_id;

                        $appId = $this->config->item('appId');

                        $this->load->library('facebook'); // Automatically picks appId and secret from config

                      			$user = $this->facebook->getUser();
                      			if ($user) {
                      			    $facebook = $this->facebook->api('/me?fields=name,first_name,last_name,email,birthday,education,gender,id');
                      			    $access_token = $this->facebook->getAccessToken();
                      			}else{
                                                  $access_token = $socialAccount->access_token;
                      			}
                       //echo $access_token;
                        $pageAccessToken = file_get_contents("https://graph.facebook.com/$page_id?fields=access_token,name&access_token=$access_token");
                        $pageAccessToken = json_decode($pageAccessToken);
			                  //echo '<pre>'; print_r($pageAccessToken);
	                       $campaignImage = stagehost_ip_url.'upload/amazing_offer1.png';
                        if (!empty($campaignData[0]->notification_image)) {
                            $post_arr = array('access_token' => $pageAccessToken->access_token,
                                'message' => $message . '  ' . base_url() . 'redeemOffer/index/' . $campaignId,
                                'from' => $appId,
                                'to' => $page_id,
                                'caption' => $pageAccessToken->name,
                                'name' => $login->username .' has created an offer!',
                                'link' => base_url() . 'redeemOffer/index/' . $campaignId,
                                'picture' => "$campaignImage",
                                'description' => $message
                            );
                        } else {
                            $post_arr = array('access_token' => $pageAccessToken->access_token,
		                    'message' => $message .'  '.base_url().'redeemOffer/index/'.$campaignId,
		                    'from' => $appId,
		                    'to' => $page_id,
		                    'caption' => $pageAccessToken->name,
		                    'name' => $login->username .' has created an offer!',
		                    'link' => base_url().'redeemOffer/index/'.$campaignId,
		                    'picture' => "$campaignImage",
		                    'description' => $message
		                    );
                        }
                        $url = "https://graph.facebook.com/v2.6/$page_id/feed";
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_REFERER, '');
                        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
                        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_arr);
                        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

                        $json = json_decode(curl_exec($curl));

                        var_dump($json); //exit;
                        //echo '<pre>'; print_r($pageAccessToken); exit;
                        break;
                    */
                    case "twitter":
                		    require_once APPPATH . "/third_party/twitter/twitter-autopost.php";
                  			$Twitauth = new Twitauth(Tw_CONSUMER_KEY, Tw_CONSUMER_SECRET);
                  			$arr = array('oauth_token' => $socialAccount->access_token, 'oauth_token_secret' => $socialAccount->oauth_token_secret, 'oauth_verifier' => $socialAccount->refresh_token, 'text' => $campaignText, 'url' => $campaignUrl, 'image' => $campaignImage);
                  			$result = $Twitauth->postStatus($arr);
            			      //print_r($result);
                      break;

                  /*  case "tumblr":
		                  $tumblr = new Tumblr(tumblr_consumer_key, tumblr_secret_key, $socialAccount->access_token, $socialAccount->oauth_token_secret);
                      $apiKey = tumblr_api_key;
                      $url = urlencode($campaignUrl);
                      $thumbnail = urlencode($campaignImage);
                      $excerpt = urlencode($campaignText);
                      $author = urlencode($campaignOwnerName);
                      $postOnTumblr = $tumblr->oauth_post("/blog/$socialAccount->source_id/post?api-key=$apiKey&title=link&body=link11&type=link&state=published&url=$url&thumbnail=$thumbnail&excerpt=$excerpt&author=$author");
			                //print_r($postOnTumblr);
                      break;*/

                    case "google":
                        try {
                            $accesstoken = '';
                            $client_id = google_sconnect_client_id;
                            $client_secret = google_sconnect_client_secret;
                            $redirect_uri = base_url().'/social/auth';
                            //code to find access token
                            $fields = array('grant_type' => 'refresh_token',
                                'client_id' => $client_id,
                                'client_secret' => $client_secret,
                                'refresh_token' => $socialAccount->refresh_token,);
                            $post = '';
                            foreach ($fields as $key => $value) {
                                $post .= $key . '=' . $value . '&';
                            }
                            $post = rtrim($post, '&');
                            $curl = curl_init();
                            curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
                            curl_setopt($curl, CURLOPT_POST, 4);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                            $result = curl_exec($curl);
                            curl_close($curl);
                            $response = json_decode($result); //print_r($response ); exit;
                        if(isset($response->access_token)){
                            $accesstoken = $response->access_token;}
                            //code to post a new post on google plus api
                            $curl1 = curl_init();
                    			  $campaignImage = base_url() . 'upload/status_image/full/' . $campaignData->notification_image; ;
                    			  if(!empty($campaignData->notification_image)){
                                $data = '{"object":{"originalContent":"' . $campaignText .'   '.$campaignUrl.  '","attachments": [{"url": "' . $campaignImage .'","objectType": "article"}]},"access":{"kind":"plus#acl","items":[{"type" : "domain"}],"domainRestricted":true},"key":"' . $client_id . '"}';
                      			}else{
                      			     $data = '{"object":{"originalContent":"' . $campaignText .'   '.$campaignUrl.  '"},"access":{"kind":"plus#acl","items":[{"type" : "domain"}],"domainRestricted":true},"key":"' . $client_id . '"}';
                      			}
                            curl_setopt($curl1, CURLOPT_URL, 'https://www.googleapis.com/plusDomains/v1/people/' . $socialAccount->source_id . '/activities');
                            curl_setopt($curl1, CURLOPT_HTTPHEADER, array(
                                "Content-Type: application/json",
                                "Authorization:OAuth " . $accesstoken,
                                "Content-Length: " . strlen($data))
                            );
                            curl_setopt($curl1, CURLOPT_CUSTOMREQUEST, "POST");
                            curl_setopt($curl1, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($curl1, CURLOPT_RETURNTRANSFER, TRUE);
                            $http_status = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
                            //print_r($http_status);
                            $result1 = curl_exec($curl1);
                            curl_close($curl1);
                            if ($result1 === false) {
                                echo 'Curl error: ' . curl_error($curl1);
                            }
                        } catch (Exception $e) {
                            die("i am in catch");
                        }
                        break;
                    default:
                        break;
                }
            }
	           echo $campaignId;
        }else{
		        echo 'false';
	      }
    }

    public function fbPostOffer($id=false){
       $data = array();
       if(!empty($id)){
         $data['id'] = $id;
       }
       $campaignData = $this->campaign_model->getCampaigns($id);
       $login = $this->administrator_model->front_login_session();
       $image = stagehost_ip_url.'upload/amazing_offer1.png';
       if (!empty($campaignData->notification_image)) {
          $image = stagehost_ip_url . 'upload/status_image/full/' . $campaignData->notification_image;
       }
       //print_r($campaignData); exit;
       $appId = "986372858079194";//$this->config->item('appId');
       $caption = $login->username .' has created an offer!';
       $fbpostName = $login->username;
       $fbpostUrl =  base_url().'redeemOffer/index/'.$id;
       $message  = $campaignData->notification .'  '.base_url().'redeemOffer/index/'.$id;
       $fbPostdescription = $campaignData->notification;
       $fbRedirectUrl = "";
       $data['postUrl'] = "https://www.facebook.com/dialog/share?app_id=$appId&amp;display=popup&amp;caption=$caption&amp;name=$fbpostName&amp;link=$fbpostUrl&amp;description=$fbPostdescription&amp;picture=$image&amp;href=$fbpostUrl&amp;message=$message&amp;redirect_uri=$fbRedirectUrl";
       $this->load->view('fbPostOffer',$data);
    }

    function store() {
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

            $usertype = $header['login']->usertype;
            if ($usertype == 6 || $usertype == 7) {

                $this->load->helper('convertlink');

                $userid = $header['login']->user_id;
                $data['loginuser'] = $header['login']->user_id;
                $data['user'] = $this->user_model->getOneUser($userid);
                $data['viewPage'] = 'business_store';
                $data['campaigns'] = $this->campaign_model->getAllCampaigns($header['login']->user_id);

                $data['packages'] = $this->businessstore_model->getAllpackages();

                $data['locationPackages'] = $this->location_model->get_packages();

                //$this->load->view('inner_header3.0', $data);
                $this->load->view('business_store', $data);
                //$this->load->view('inner_footer3.0');
            } else {
                redirect(base_url());
            }
        } else {
            $this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
            redirect(base_url());
        }
    }

    function checkout() {
        $businessStoreId = $this->uri->segment('3');
        $data['package'] = $this->businessstore_model->getOnePackage($businessStoreId);
        $data['countries'] = $this->country_model->get_countries();
        $arr_card_type['active'] = 1;
        $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $data['recurringPackage'] = array();
        if($businessStoreId == 6 || $businessStoreId == 7 || $businessStoreId == 8 || $businessStoreId == 9 || $businessStoreId == 10){
            $data['recurringPackage'] = $this->businessstore_model->checkRecurringPackageExist($businessStoreId,$userid);
        }
        $this->load->view('checkout', $data);
    }

    function saveCheckoutPayment() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;
        $email = $login->email;
        $username = $login->username;

        $packageid = $_POST['packageid'];
        $paymentMode = $_POST['paymentMode'];

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $card_type = $_POST['card_type'];
        $card_no = $_POST['card'];
        $exp_month = $_POST['expire_month'];
        $exp_year = $_POST['expire_year'];
        $cvv2 = $_POST['cvv'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip_code = $_POST['zip'];
        $country = $_POST['country'];
        //Get Country Code
        $countryCode = $this->country_model->getcountry('country_id', $country, 1);
        $amount  = $_POST['amount'];
        $currency = $_POST['currency'];

        $paymentType = urlencode('Sale');    // or 'Sale'
        $firstName = urlencode($firstname);
        $lastName = urlencode($lastname);
        $creditCardType = urlencode($card_type);
        $creditCardNumber = urlencode($card_no);
        $expDateMonth = $exp_month;
        // Month must be padded with leading zero
        $padDateMonth = urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));

        $expDateYear = urlencode($exp_year);
        $cvv2Number = urlencode($cvv2);
        $address1 = urlencode($address);
        $city = urlencode($city);
        $state = urlencode($state);
        $zip = urlencode($zip_code);
        $country = urlencode($countryCode->country_code);    // US or other valid country code
        $amount = urlencode($amount);
        $currencyID = urlencode($currency);       // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
        $emails = urlencode($email);

        if($paymentMode == "oneOff"){

            //// CREATE AN STRING
            $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                    "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$emails" .
                    "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";

            //// SEND REQUEST TO PAYPAL
            $httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);
            //echo '<pre>'; print_r($httpParsedResponseAr); exit;//Successecho $httpParsedResponseAr; die;
            if ($httpParsedResponseAr['ACK'] == 'Failure') {

                $payment['business_store_payment_id'] = '';
                $payment['user_id'] = $userid;
                $payment['package_id'] = $packageid;
                $payment['purchasedOn'] = date('YmdHis');
                $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
                $payment['currency'] = $currency;
                $payment['transaction_id'] = 'TRANSACTIONIDFailure';//$httpParsedResponseAr['TRANSACTIONID'];
                $payment['payment_response'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '|CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
                $payment['isActive'] = 1;
                $payment['isDelete'] = 0;
                $payment['createdDate'] = date('YmdHis');

                $last_payment_id = $this->businessstore_model->savepayment($payment);

                $package = $this->businessstore_model->getOnePackage($packageid);
                $description = $package->desription;
                $quantity = $package->quantity;

                //If user don't have any entry in user_profile_info table
                $packageInfo = $this->campaign_model->getBusinessPackagesInfo($businessId);
                if (count($packageInfo) == 0) {
                    //Insert
                    $date = date('YmdHis');
                    $insert['user_pro_id'] = '';
                    $insert['user_id'] = $userid;
                    $insert['businessId'] = $businessId;
                    $insert['totalCoins'] = 0;
                    $insert['totalBeacons'] = 0;
                    $insert['totalCampaigns'] = 0;
                    $insert['totalGeoFence'] = 0;
                    $insert['totalIndividualCampaigns'] = 0;
                    $insert['createdDate'] = $date;
                    $insert['modifiedDate'] = $date;
                    $last_insert_id = $this->user_model->savePackage($insert);
                }

                $extraPackage['businessId'] = $businessId;
                $extraPackage['packageid'] = $packageid;
                $userExtraPackage = $this->campaign_model->getBusinessExtraPackage($extraPackage);

                if (count($userExtraPackage) == 0) {
                    //Insert
                    if ($package->desription == 'Unlimited Campaigns') {
                        $quantity = 'unlimited';
                    } else {
                        $quantity = $package->quantity;
                    }
                    $current_date = date('YmdHis');
                    $userInsert['user_extra_packages_id'] = '';
                    $userInsert['userid'] = $userid;
                    $userInsert['businessId'] = $businessId;
                    $userInsert['packageid'] = $packageid;
                    $userInsert['quantity'] = $quantity;
                    $userInsert['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                    $userInsert['createdDate'] = $current_date;

                    $this->campaign_model->saveExtraPackage($userInsert);
                } else {
                    //Update
                    if ($package->desription == 'Unlimited Campaigns') {
                        $quantity = 'unlimited';
                    } else {
                        $quantity = $package->quantity;
                    }
                    $current_date = date('YmdHis');
                    $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;
                    //$update['userid'] = $userid;
                    //$update['packageid'] = $packageid;
                    $update['quantity'] = $quantity;
                    $update['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                    $update['createdDate'] = $current_date;

                    $this->campaign_model->updateExtraPackage($update);

                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// GET EMAIL FROM DATABASE

                    $email_template = $this->email_model->getoneemail('buy_extra_package');

                    //// MESSAGE OF EMAIL
                    $messages = $email_template->message;

                    //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
                    $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                    $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                    $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                    if ($package->quantity == 0) {
                        $packageName = $package->desription;
                    } else {
                        $packageName = $package->quantity . " " . $package->desription;
                    }
                    $price = "£" . $amount;
                    //// replace strings from message
                    $messages = str_replace('{Username}', ucfirst($username), $messages);
                    $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                    //$messages = str_replace('{App_Store_Image}', $appstore, $messages);
                    //$messages = str_replace('{Google_Image}', $googleplay, $messages);
                    $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                    $messages = str_replace('{price}', $price, $messages);

                    //// FROM EMAIL
                    $this->email->from($email_template->from_email, 'Hurree');
                    $this->email->to($email);
                    $this->email->subject($email_template->subject);
                    $this->email->message($messages);
                    $this->email->send();    ////  EMAIL SEND
                    //Email send to Admin
                    $message = "'<strong>User: ' . ucfirst($username) . '</strong><br>Email: ' . $email . '<br>What the user bought: ' . $packageName . '<br>Total Price: ' . $price . '<br>Address: ' . $address . '<br>Country: ' . $countryCode->country_name . '<br>State: ' . $state . '<br>City: ' . $city . '<br>Zip code: ' . $zip_code";

                    $this->emailConfig();   //Get configuration of email
                    //// FROM EMAIL
                    $this->email->from($email_template->from_email, 'Hurree');
                    $this->email->to('Store@hurree.co');
                    $this->email->subject('User made a purchase!');
                    $this->email->message($message);
                    $this->email->send();    ////  EMAIL SEND
                }

                echo 'Success';
            } else {
                echo 'Failure';
            }
        } else if($paymentMode == 'recurring') {

            $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z"));
            $desc = "Payment of hurree store";

            //// CREATE AN STRING
            $nvpStr ="&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                    "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
                    "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country".
                    "&CURRENCYCODE=$currencyID&PROFILESTARTDATE=$profileStartDate&MAXFAILEDPAYMENTS=3".
                    "&DESC=$desc&BILLINGPERIOD=Month".
                    "&BILLINGFREQUENCY=1";

            //SEND REQUEST TO PAYPAL reoccuring payment
            $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);

            if ($httpParsedResponseAr['ACK'] == 'Failure') {

                      $recurringPayment['business_store_payment_id'] = '';
                      $recurringPayment['user_id'] = $userid;
                      $recurringPayment['package_id'] = $packageid;
                      $recurringPayment['purchasedOn'] = date('YmdHis');
                      $recurringPayment['amount'] = $amount;
                      $recurringPayment['currency'] = $currency;
                      $recurringPayment['profile_id'] = 'Failure1';//$httpParsedResponseAr['PROFILEID'];
                      $recurringPayment['payment_response'] = json_encode($httpParsedResponseAr);
                      $recurringPayment['isActive'] = 1;
                      $recurringPayment['isDelete'] = 0;
                      $recurringPayment['createdDate'] = date('YmdHis');

                      $last_payment_id = $this->businessstore_model->savepayment($recurringPayment);

                      $package = $this->businessstore_model->getOnePackage($packageid);
                      $description = $package->desription;
                      $quantity = $package->quantity;

                                    //echo "recurring called"; echo '<pre>'; print_r($httpParsedResponseAr); exit;
                      //If user don't have any entry in user_profile_info table
                      $packageInfo = $this->campaign_model->getBusinessPackagesInfo($businessId);
                      if (count($packageInfo) == 0) {
                          //Insert
                          $date = date('YmdHis');
                          $recurringInsert['user_pro_id'] = '';
                          $recurringInsert['user_id'] = $userid;
                          $recurringInsert['businessId'] = $businessId;
                          $recurringInsert['totalCoins'] = 0;
                          $recurringInsert['totalBeacons'] = 0;
                          $recurringInsert['totalCampaigns'] = 0;
                          $recurringInsert['totalGeoFence'] = 0;
                          $recurringInsert['totalIndividualCampaigns'] = 0;
                          $recurringInsert['createdDate'] = $date;
                          $recurringInsert['modifiedDate'] = $date;
                          $last_insert_id = $this->user_model->savePackage($recurringInsert);
                      }

                      $extraPackage['businessId'] = $businessId;
                      $extraPackage['packageid'] = $packageid;
                      $userExtraPackage = $this->campaign_model->getBusinessExtraPackage($extraPackage);

                      if (count($userExtraPackage) == 0) {
                          //Insert
                          if ($package->desription == '') {
                              $quantity = '';
                          } else {
                              $quantity = $package->quantity;
                          }
                          $current_date = date('YmdHis');
                          $userInsert['user_extra_packages_id'] = '';
                          $userInsert['userid'] = $userid;
                          $userInsert['businessId'] = $businessId;
                          $userInsert['packageid'] = $packageid;
                          $userInsert['quantity'] = $quantity;
                          $userInsert['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                          $userInsert['createdDate'] = $current_date;

                          $this->campaign_model->saveExtraPackage($userInsert);
                      } else {
                          //Update
                          if ($package->desription == '') {
                              $quantity = '';
                          } else {
                              $quantity = $package->quantity;
                          }
                          $current_date = date('YmdHis');
                          $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;
                          //$update['userid'] = $userid;
                          //$update['packageid'] = $packageid;
                          $update['quantity'] = $quantity;
                          $update['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                          $update['createdDate'] = $current_date;

                          $this->campaign_model->updateExtraPackage($update);

                          //// SEND  EMAIL START
                          $this->emailConfig();   //Get configuration of email
                          //// GET EMAIL FROM DATABASE

                          $email_template = $this->email_model->getoneemail('buy_extra_package');

                          //// MESSAGE OF EMAIL
                          $messages = $email_template->message;

                          //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
                          $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                          $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                          $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                          if ($package->quantity == 0) {
                              $packageName = $package->desription;
                          } else {
                              $packageName = $package->quantity . " " . $package->desription;
                          }
                          $price = "£" . $amount;
                          //// replace strings from message
                          $messages = str_replace('{Username}', ucfirst($username), $messages);
                          $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                          //$messages = str_replace('{App_Store_Image}', $appstore, $messages);
                          //$messages = str_replace('{Google_Image}', $googleplay, $messages);
                          $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                          $messages = str_replace('{price}', $price, $messages);

                          //// FROM EMAIL
                          $this->email->from($email_template->from_email, 'Hurree');
                          $this->email->to($email);
                          $this->email->subject($email_template->subject);
                          $this->email->message($messages);
                          $this->email->send();    ////  EMAIL SEND
                          //Email send to Admin
                          $message = "'<strong>User: ' . ucfirst($username) . '</strong><br>Email: ' . $email . '<br>What the user bought: ' . $packageName . '<br>Total Price: ' . $price . '<br>Address: ' . $address . '<br>Country: ' . $countryCode->country_name . '<br>State: ' . $state . '<br>City: ' . $city . '<br>Zip code: ' . $zip_code";

                          $this->emailConfig();   //Get configuration of email
                          //// FROM EMAIL
                          $this->email->from($email_template->from_email, 'Hurree');
                          $this->email->to('Store@hurree.co');
                          $this->email->subject('User made a purchase!');
                          $this->email->message($message);
                          $this->email->send();    ////  EMAIL SEND
                      }

                  echo 'Success';
            }else{
                echo 'Failure';
            }
        }else{
             echo 'Failure';
        }
    }

    function emailConfig() {

        $this->load->library('email');   //// LOAD LIBRARY

        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'auth.smtp.1and1.co.uk';
        $config['smtp_port'] = '587';
        $config['smtp_timeout'] = '7';
        $config['smtp_user'] = 'support@hurree.co';
        $config['smtp_pass'] = 'aaron8164';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html

        $this->email->initialize($config);
    }

    function PPHttpPost($methodName_, $nvpStr_) {
        //echo $methodName_;
        //echo '<br>'.$nvpStr_; exit;
        //$methodName_ = "DoDirectPayment";
        //$nvpStr_  = "&PAYMENTACTION=Sale&AMT=99.99&CREDITCARDTYPE=Visa&ACCT=4556295574477291&EXPDATE=102018&CVV2=868&FIRSTNAME=sarvesh&LASTNAME=tiwari&EMAIL=demo%40qsstechnosoft.com&STREET=fff&CITY=ggg&STATE=fff&ZIP=201301&COUNTRYCODE=IN&CURRENCYCODE=GBP";
        //echo $nvpStr_; exit;
        $environment = PAYPAL_ENVIRONMENT;

        // Set up your API credentials, PayPal end point, and API version.
        $API_UserName = urlencode(PAYPAL_API_USERNAME);
        $API_Password = urlencode(PAYPAL_APT_PASSWORD);
        $API_Signature = urlencode(PAYPAL_API_SIGNATURE);
        //$environment.
        $API_Endpoint = PAYPAL_END_POINT;
        if ("sandbox" === $environment || "beta-sandbox" === $environment) {
            $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
        }
        $version = urlencode('51.0');
        //echo  $API_Endpoint; exit;
        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $httpResponse = curl_exec($ch);

        if (!$httpResponse) {
            exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            if (sizeof($tmpAr) > 1) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
            exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
        }

        return $httpParsedResponseAr; //exit;
    }

    function reach_campaign() {

        $offerid = $this->uri->segment(3);
        $data['reachPeople'] = $this->campaign_model->reach_campaign($offerid);
        $this->load->view('campaign_reach', $data);
    }

    // email to business users to the created locations by master admin
    function emailOffer($data) {
            $qrCode  =$data['qrCode'];
            $sendemails = $data['email'];
            $username = $data['Username'];
            $businessUsername = $data['businessUsername'];
            $notification = $data['notification'];
            if(!empty($data['location'])){
              $locations = $data['locations'];
            }
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];




			// // SEND EMAIL START
			$this->emailConfig (); // Get configuration of email
			                      // // GET EMAIL FROM DATABASE

			$email_template = $this->email_model->getoneemail ( 'campaign_created' );

			// // MESSAGE OF EMAIL
			$messages = $email_template->message;

			$hurree_image = base_url () . 'assets/template/frontend/img/redeem_success.png';

			// // replace strings from message
			$messages = str_replace ( '{Username}', ucfirst ( $username ), $messages );
                        $messages = str_replace ( '{qrcode}', ucfirst ( $qrCode ), $messages );
                    if(!empty($data['location'])){
                        $messages = str_replace ( '{locations}',  $locations , $messages );
                    }
			$messages = str_replace ( '{MasterAdminName}', ucfirst ( $businessUsername ), $messages );
                        $messages = str_replace ( '{startDate}', ucfirst ( $startDate ), $messages );
                        $messages = str_replace ( '{endDate}', ucfirst ( $endDate ), $messages );

			$messages = str_replace ( '{Hurree_Image}', $hurree_image, $messages );
                        return $messages;

//			// // FROM EMAIL
//			$this->email->from ( $email_template->from_email, 'Hurree' );
//			$this->email->to ( $sendemails );
//			$this->email->subject ( $email_template->subject );
//			$this->email->message ( $messages );
//                        $this->email->attach($_SERVER["DOCUMENT_ROOT"].'/upload/Campaign_pdf/Hurree-QR-code-offer.pdf');
//			$sent = $this->email->send ();
//                        unlink($_SERVER["DOCUMENT_ROOT"].'/upload/Campaign_pdf/Hurree-QR-code-offer.pdf');
//                        $this->email->clear(TRUE);
//
//			// // EMAIL SEND
//			if ($sent) {
//
//				return true;
//			} else {
//
//				return false;
//			}


	}

        function setLocationValue(){
            $val = $_POST['arr'];
            $_SESSION['locations'] = $val;
            //$this->session->set_userdata('locations',$val);
            echo '1'; exit;
        }

        /* function created by sarvesh createInsightsCampaign */
        public function createInsightsCampaign($type=false){
          if($type == 2 || $type == 3){
            echo "<center><h5>No customer exists in this category.</h4></center>";
          }else if($type == 4){
            echo "<center><h5>0 customer in VIP list.</h5></center>";
          }else if($type == 5){
            echo "<center><h5>No customer exists in this category.</h5></center>";
          }else if($type == 6){
            echo "<center><h5>No customer exists in this category.</h5></center>";
          }else if($type == 7){
            echo "<center><h5>No customer exists in this category.</h5></center>";
          }
        }

        public function getUsersList($type=false){
            $header['login'] = $this->administrator_model->front_login_session();
            $businessId = $header['login']->businessId;
            $allUsers = $this->user_model->scannedUsers($businessId);
            $singleScanUsers = array();
            $multipleScanUsers = array();
            $data['users'] = array();
            $multipleUserIds = array();
            $singleUserIds = array();
            $vipUserIds = array();
            $countForVip = array();
            $i = 0;
            $j = 0;
            if(count($allUsers)>0){
                foreach ($allUsers as $user) {
                    $userid = $user->user_Id;
                    $userCoins = array(
                        'userId' => $user->user_Id,
                        'businessId' => $businessId
                    );
                    $results = $this->user_model->countScannedUsers($userCoins);
                    if (count($results) > 1) {
                        if(!in_array($user->user_Id,$multipleUserIds)){
                          array_push($countForVip,count($results));
                          array_push($vipUserIds,$user);
                          $multipleScanUsers[] = $user;
                        }
                        array_push($multipleUserIds,$user->user_Id);
                        //$multipleScanUsers[$i] = $user;

                    } else {

                       if(!in_array($user->user_Id,$singleUserIds)){
                           array_push($singleUserIds,$user->user_Id);
                           $singleScanUsers[] = $user;
                       }
                    }
                }
            }
          //  echo '<pre>'; print_r($data['singleScanUsers']); print_r($data['multipleScanUsers']); exit;
            if($type == 2){
                $data['users'] = $singleScanUsers;
            }else if($type == 3){
                $data['users'] = $multipleScanUsers;
            }else if($type == 4){
                $data['users'] = $vipUserIds;
            }
            //print_r($data); exit;
            $this->load->view('getUsersList',$data);
        }

        //function to send cron job

        function sendEmailtoUsers(){

            $result=  $this->offer_model->getMail();
            if(count($result)>0){
             $emails = array();
             foreach ($result as $data){
                 $emails[] = $data->email_id;
                 $info['from_email'] =  $data->from_email;
                 $info['email_id'] =  $data->email_id;
                 $info['subject'] =  $data->subject;
                 $info['message'] =  $data->message;
                 $info['attachment'] =  $data->attachment;
                 $sent = $this->emailSend($info);
                 if($sent){
                   $this->offer_model->saveEmailStatus($data->userid, $data->campaignId);
                 }

            }
            if(file_exists($_SERVER["DOCUMENT_ROOT"].'/upload/Campaign_pdf/Hurree-QR-code-offer.pdf')){
              unlink($_SERVER["DOCUMENT_ROOT"].'/upload/Campaign_pdf/Hurree-QR-code-offer.pdf');
            }
            $this->offer_model->updateMailStatus($emails);

            return true;
            }
            return true;

        }

        function emailSend($info=false){
            //  $info['from_email'] =  'Hello@hurree.co';
            //  $info['email_id'] =  'shiwangi@qsstechnosoft.com';
            //  $info['subject'] =  'demo';
            //  $info['message'] =  'message123';
            $this->emailConfig();
            $this->email->from ($info['from_email'],'Hurree');
      	    $this->email->to($info['email_id']);
      	    $this->email->subject($info['subject']);
      	    $this->email->message($info['message'] );
            if(!empty($info['attachment'])){
              $this->email->attach ( $info['attachment']);
            }
	          $sent = $this->email->send ();

            $this->email->clear(TRUE);
            // echo $this->email->print_debugger(); exit;
	         // // EMAIL SEND
      	   if ($sent) {
      	     return true;
      	   } else {
             return false;
      	   }
        }


        public function segmentCriteria($type=false){
            $header['login'] = $this->administrator_model->front_login_session();
            $businessId = $header['login']->businessId;
            $data['users'] = array();
            $data['userSegment'] = array();
            $data['type'] = $type;
            $users = array();
            $segmentData = array();
            $segmentAgeArr = array();

            $userSegment = $this->campaign_model->getUserSegment($type,$businessId);
            if(count($userSegment) > 0){
                if(!empty($userSegment->segmentGender) && empty($userSegment->segmentAge)){
                  $segmentData = array('gender' => $userSegment->segmentGender);
                  $users = $this->user_model->getSegmentUsersList($segmentData);
                }else if(!empty($userSegment->segmentAge) && empty($userSegment->segmentGender)){
                  $users = $this->user_model->getSegmentUsersList($segmentData);
                  $ageArray = array();
                  $segmentAge = explode('-',$userSegment->segmentAge);
                  $segmentAgeMin = $segmentAge[0];
                  $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;
                  if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
                  $addUsers = array();
                  foreach($users as $user){
                      $birthday_timestamp = strtotime($user->date_of_birth);
                      $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                      if($age>=$segmentAgeMin && $age <=$segmentAgeMax )
                      {
                          array_push($ageArray,$age);
                          array_push($addUsers,$user);
                      }
                  }
                  $users = $addUsers;
                }else if(!empty($userSegment->segmentWhoHave) && empty($userSegment->segmentGender) && empty($userSegment->segmentAge)){
                    $segmentData = array('segmentWhoHave' => $userSegment->segmentWhoHave, 'businessId'=> $businessId);
                    $users = $this->user_model->getSegmentUsersList($segmentData);
                }else if(!empty($userSegment->segmentGender) && !empty($userSegment->segmentAge) && empty($userSegment->segmentWhoHave)){
                    $segmentData = array('gender' => $userSegment->segmentGender);
                    $users = $this->user_model->getSegmentUsersList($segmentData);
                    $ageArray = array();
                    $segmentAge = explode('-',$userSegment->segmentAge);
                    $segmentAgeMin = $segmentAge[0];
                    $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;
                    if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
                    $addUsers = array();
                    foreach($users as $user){
                        $birthday_timestamp = strtotime($user->date_of_birth);
                        $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                        if($age>=$segmentAgeMin && $age<=$segmentAgeMax)
                        {
                            array_push($ageArray,$age);
                            array_push($addUsers,$user);
                        }
                    }
                    $users = $addUsers;
                }else if(!empty($userSegment->segmentGender) && !empty($userSegment->segmentAge) && !empty($userSegment->segmentWhoHave)){
                    $segmentData = array('gender' => $userSegment->segmentGender);
                    $users = $this->user_model->getSegmentUsersList($segmentData);
                    $ageArray = array();
                    $segmentAge = explode('-',$userSegment->segmentAge);
                    $segmentAgeMin = $segmentAge[0];
                    $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;
                    if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
                    $addUsers = array();
                    $addRedeemUserArr = array();
                    $addNotRedeemUserArr = array();
                    foreach($users as $user){
                        $birthday_timestamp = strtotime($user->date_of_birth);
                        $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                        if($age>=$segmentAgeMin && $age <=$segmentAgeMax )
                        {
                            array_push($ageArray,$age);
                            array_push($addUsers,$user);
                        }
                    }
                    $users = $addUsers;
                    if($userSegment->segmentWhoHave == 1){
                      foreach($users as $user){
                          $userRow = $this->campaign_model->checkUserRedeemOfferOrNot($user->user_Id);
                          if(count($userRow) > 0)
                          {
                              array_push($addRedeemUserArr,$user);
                          }
                      }
                      $users = $addRedeemUserArr;
                    }else{
                      foreach($users as $user){
                          $userRow = $this->campaign_model->checkUserRedeemOfferOrNot($user->user_Id);
                          if(count($userRow) == 0)
                          {
                              array_push($addNotRedeemUserArr,$user);
                          }
                      }
                      $users = $addNotRedeemUserArr;
                    }
                }
            }
            //echo '<pre>'; echo count($users); print_r($users); exit;
            $data['users'] = $users;
            $data['userSegment'] = $userSegment;

            if($type == 1 || $type == 2 || $type == 3){
              $this->load->view('segment_1',$data);
            }
       }

       public Function saveSegment(){
           $header['login'] = $this->administrator_model->front_login_session();
           $businessId = $header['login']->businessId;
           $userId = $header['login']->user_id;
           $segmentType = $this->input->post('segmentType');
           $segment1 = $this->input->post('segment1');
           $segment2 = $this->input->post('segment2');
           $segment3 = $this->input->post('segment3');
           $data = array(
                      'user_id' => $userId,
                      'businessId' => $businessId,
                      'segmentType' => $segmentType,
                      'segmentGender' => $segment1,
                      'segmentAge' => $segment2,
                      'segmentWhoHave' => $segment3,
                      'createdDate' => date('Y-m-d H:i:s')
                   );

           $userSegment = $this->campaign_model->getUserSegment($segmentType,$businessId);
           //print_r($userSegment); exit;
           if(count($userSegment) > 0){
                $data = array_merge($data,array('Id' => $userSegment->Id));
                $this->campaign_model->saveSegment($data);
           }else{
                $this->campaign_model->saveSegment($data);
           }

           $data['users'] = array();
           $data['userSegment'] = array();
           $users = array();
           $segmentData = array();
           $segmentAgeArr = array();

           $userSegment = $this->campaign_model->getUserSegment($segmentType,$businessId);
           if(count($userSegment) > 0){
               if(!empty($userSegment->segmentGender) && empty($userSegment->segmentAge)){
                 $segmentData = array('gender' => $userSegment->segmentGender);
                 $users = $this->user_model->getSegmentUsersList($segmentData);
               }else if(!empty($userSegment->segmentAge) && empty($userSegment->segmentGender)){
                 $users = $this->user_model->getSegmentUsersList($segmentData);
                 $ageArray = array();
                 $segmentAge = explode('-',$userSegment->segmentAge);
                 $segmentAgeMin = $segmentAge[0];
                 $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;
                 if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
                 $addUsers = array();
                 foreach($users as $user){
                     $birthday_timestamp = strtotime($user->date_of_birth);
                     $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                     if($age>=$segmentAgeMin && $age <=$segmentAgeMax )
                     {
                         array_push($ageArray,$age);
                         array_push($addUsers,$user);
                     }
                 }
                 $users = $addUsers;
               }else if(!empty($userSegment->segmentWhoHave) && empty($userSegment->segmentGender) && empty($userSegment->segmentAge)){
                   $segmentData = array('segmentWhoHave' => $userSegment->segmentWhoHave, 'businessId'=> $businessId);
                   $users = $this->user_model->getSegmentUsersList($segmentData);
               }else if(!empty($userSegment->segmentGender) && !empty($userSegment->segmentAge) && empty($userSegment->segmentWhoHave)){
                   $segmentData = array('gender' => $userSegment->segmentGender);
                   $users = $this->user_model->getSegmentUsersList($segmentData);
                   $ageArray = array();
                   $segmentAge = explode('-',$userSegment->segmentAge);
                   $segmentAgeMin = $segmentAge[0];
                   $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;
                   if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
                   $addUsers = array();
                   foreach($users as $user){
                       $birthday_timestamp = strtotime($user->date_of_birth);
                       $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                       if($age>=$segmentAgeMin && $age <=$segmentAgeMax )
                       {
                           array_push($ageArray,$age);
                           array_push($addUsers,$user);
                        }
                    }
                    $users = $addUsers;
               }else if(!empty($userSegment->segmentGender) && !empty($userSegment->segmentAge) && !empty($userSegment->segmentWhoHave)){
                   $segmentData = array('gender' => $userSegment->segmentGender);
                   $users = $this->user_model->getSegmentUsersList($segmentData);
                   $ageArray = array();
                   $segmentAge = explode('-',$userSegment->segmentAge);
                   $segmentAgeMin = $segmentAge[0];
                   $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;
                   if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
                   $addUsers = array();
                   $addRedeemUserArr = array();
                   $addNotRedeemUserArr = array();
                   foreach($users as $user){
                       $birthday_timestamp = strtotime($user->date_of_birth);
                       $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                       if($age>=$segmentAgeMin && $age <=$segmentAgeMax )
                       {
                           array_push($ageArray,$age);
                           array_push($addUsers,$user);
                       }
                   }
                   $users = $addUsers;
                   if($userSegment->segmentWhoHave == 1){
                      foreach($users as $user){
                          $userRow = $this->campaign_model->checkUserRedeemOfferOrNot($user->user_Id);
                          if(count($userRow) > 0)
                          {
                              array_push($addRedeemUserArr,$user);
                          }
                      }
                      $users = $addRedeemUserArr;
                    }else{
                      foreach($users as $user){
                          $userRow = $this->campaign_model->checkUserRedeemOfferOrNot($user->user_Id);
                          if(count($userRow) == 0)
                          {
                              array_push($addNotRedeemUserArr,$user);
                          }
                      }
                      $users = $addNotRedeemUserArr;
                    }
               }
           }
           $data['users'] = $users; //echo count($users); print_r($users); exit;
           $data['userSegment'] = $userSegment;
           $list = '';
           $coins = 0;
           if(count($users) > 0){
             foreach($users as $user){
               $image = base_url().'upload/profile/medium/'.$user->image;
               $username = ucfirst($user->firstname)." ".ucfirst($user->lastname);
               $showtime = ago_time($user->createdDate);
               $gender = ucfirst($user->gender);

               $list .= '<li><div class="propic"><a><img src="'.$image.'" alt="" onerror="this.onerror=null;this.src=\'/upload/profile/medium/user.png\'" /></a></div>';
               $list .= '<div class="info"><div class="title"><strong><a>'. $username.'</a></strong><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</small></div>';
               $list .= '<div class="timeAgo">'.$showtime.'</div>';
               /*if(!empty($user->notification)){
                 $string = (strlen($user->notification) > 25) ? substr($user->notification, 0, 24) . '...' : $user->notification;
                 $list .= '<div class="txt"><label>Notification : </label><p>'. $string.'</p></div>';
               }else{*/
               if($user->bio != ''){
                  if(strlen($user->bio)>30){ $string = substr($user->bio, 0,30).'...';} else { $string = $user->bio; }
               }else{
                  $string = 'No bio';
               }
               $list .= '<div class="txt"><label>Bio : </label><p>'. $string.'</p></div>';
              // }
               $list .= '<div class="txt"><label>Gender : </label><p>'. $gender.'</p></div>';

               if(isset($user->coins)){
                 $list .= '<div class="txt"><label>Coins Rewarded:</label>'. $user->coins.'<p></div></li>';
               }
             }
           }else{
              echo "<center>No customer exists in this category.</center>";
           }
           echo $list; exit;
       }



}
