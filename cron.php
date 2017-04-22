<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once('vendor/autoload.php');

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('url', 'hurree'));
        $this->load->library(array('session', 'email'));
        $this->load->model(array('workflow_model', 'user_model', 'brand_model', 'offer_model', 'score_model', 'payment_model', 'administrator_model', 'subscription_model', 'email_model', 'campaign_model', 'inapp_model', 'groupapp_model', 'contact_model','sparkposthistory_model'));
        emailConfig();
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    function businessCoins() {

        $arr_usrr['usertype'] = 2;

        $date = date("Y-m-d H:i:s"); // current date
        $last_sent = strtotime(date("Y-m-d H:i:s", strtotime($date)) . " -30 days");
        $old_Date = date('Y-m-d H:i:s', $last_sent);
        /* $arr_usrr['coinsTakeDate']=2; */

        $busineeuser = $this->user_model->getBusinessUsers($old_Date);

        foreach ($busineeuser as $user) {

            if ($user->package == 1) {
                $coins = '1500';
            }
            if ($user->package == 2) {
                $coins = '2500';
            }
            if ($user->package == 3) {
                $coins = '5000';
            }

            $userCoins = array(
                'userid' => $user->user_Id,
                'coins' => $coins,
                'coins_type' => 9,
                'game_id' => 0,
                'businessid' => $user->branch_id,
                'actionType' => 'add',
                'createdDate' => date('YmdHis')
            );
            $this->score_model->insertCoins($userCoins);

            $arr_coin['userid'] = $user->user_Id;
            $arr_coin['branchid'] = $user->branch_id;

            $userCoins = $this->score_model->getUserTotalCoinDetails('*', $arr_coin);

            $totalcoins = $userCoins->coins + $coins;

            $coins = array(
                'userid' => $user->user_Id,
                'coins' => $totalcoins,
                'branchid' => $user->branch_id
            );

            $this->score_model->updateBusinessCoins($coins);

            $arr_users['branch_id'] = $user->branch_id;
            $arr_users['coinDate'] = date('YmdHis');

            $this->user_model->savebusinessbranch($arr_users);
        }
        //$this->subscription_model->
    }

    function checkcron() {

        //file_put_content("")
        $path = baseurl . 'uploads/';
        $Nmessage = file_get_contents('http://www.nettech.in/e-books/Teach-Yourself-PHP4-in-24-Hours.pdf');

        /* Send a Confirmation Email to User */
        //// SEND  EMAIL START

        $config['mailtype'] = 'html';    //// ENABLE HTML
        $this->email->initialize($config);
        //// GET EMAIL FROM DATABASE
        //// MESSAGE OF EMAIL
        $messages = date('Y-m-d H:i:s') . $Nmessage;

        //// replace strings from message
        //// Email to user
        $this->email->from('neelam.maurya@hotmail.com', 'Hurree');
        $this->email->to('neelam.maurya@hotmail.com');
        $this->email->subject('croncheck ' . date('Y-m-d H:i:s'));
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
    }

    function autoTopUp() {

        $userid = array();
        $businessuser = $this->user_model->autoTopUpBusinessUsers();
        //echo $this->db->last_query();die;
        //echo '<pre>';
        //print_r($businessuser);die;
        foreach ($businessuser as $user) {

            $userCoins = array(
                'userid' => $user->user_Id,
                'coins' => 1000,
                'coins_type' => 10,
                'game_id' => 0,
                'businessid' => $user->branch_id,
                'actionType' => 'add',
                'createdDate' => date('YmdHis')
            );
            $this->score_model->insertCoins($userCoins);

            $totalcoins = $user->coins + 1000;

            $coins = array(
                'userid' => $user->user_Id,
                'coins' => $totalcoins,
                'branchid' => $user->branch_id
            );

            $this->score_model->updateBusinessCoins($coins);
            $userid[] = $user->user_Id;
        }
        $getUserId = (array_unique($userid));
        foreach ($getUserId as $user_id) {
            //echo $user_id;

            $userinfo = $this->user_model->getOneUser($user_id);
            //echo '<pre>';
            //print_r($userinfo);
            $username = $userinfo->username;
            $email = $userinfo->email;

            //// SEND  EMAIL START
            $this->emailConfig();   //Get configuration of email
            //// GET EMAIL FROM DATABASE

            $email_template = $this->email_model->getoneemail('auto_top_up');

            //// MESSAGE OF EMAIL
            $messages = $email_template->message;


            $hurree_image = 'http://54.254.239.126/hurree/assets/template/hurree/images/app-icon.png';
            $appstore = 'http://54.254.239.126/hurree_images/appstore.gif';
            $googleplay = 'http://54.254.239.126/hurree_images/googleplay.jpg';

            //// replace strings from message
            $messages = str_replace('{Username}', ucfirst($username), $messages);
            $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
            $messages = str_replace('{App_Store_Image}', $appstore, $messages);
            $messages = str_replace('{Google_Image}', $googleplay, $messages);

            //// FROM EMAIL
            $this->email->from($email_template->from_email, 'Hurree');
            $this->email->to($email);
            $this->email->subject($email_template->subject);
            $this->email->message($messages);
            $this->email->send();    ////  EMAIL SEND
        }
    }

    function emailConfig() {
        /*
          $this->load->library('email');   //// LOAD LIBRARY

          $config['protocol'] = 'smtp';
          $config['smtp_host'] = 'ssl://email-smtp.eu-west-1.amazonaws.com';//auth.smtp.1and1.co.uk
          $config['smtp_port'] = 465;
          $config['smtp_user'] = 'AKIAJUJGM2OYDQR4TSWA';//support@hurree.co.uk
          $config['smtp_pass'] = 'AkINVk1QbB5FLbvbu43cduRlx4be3zFGmvMqmu99Aw2t';
          $config['charset'] = 'utf-8';
          $config['newline'] = "\r\n";
          $config['mailtype'] = 'html'; // or html


          $this->email->initialize($config); */
    }

    // cron job for email users to download hurree app

    function NotifyUser() {
        $data = $this->user_model->getsendEvent();

        if (count($data) > 0) {

            foreach ($data as $detail) {
                if ($detail->send_date === date('Y-m-d 00:00:00')) {

                    $user = $this->user_model->getOneUser($detail->userid);
                    $username = $user->username;
                    $email = $user->email;

                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// GET EMAIL FROM DATABASE

                    $email_template = $this->email_model->getoneemail('download_app_notify');

                    //// MESSAGE OF EMAIL
                    $messages = $email_template->message;

                    $hurree_image = base_url() . 'assets/template/hurree/images/app-icon.png';
                    $appstore = base_url() . 'hurree_images/appstore.gif';
                    $googleplay = base_url() . 'hurree_images/googleplay.jpg';

                    //// replace strings from message
                    $messages = str_replace('{Username}', ucfirst($username), $messages);
                    $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                    $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                    $messages = str_replace('{Google_Image}', $googleplay, $messages);

                    //// FROM EMAIL
                    $this->email->from($email_template->from_email, 'Hurree');
                    $this->email->to($email);
                    $this->email->subject($email_template->subject);
                    $this->email->message($messages);
                    $sent = $this->email->send();

                    ////  EMAIL SEND
                    if ($sent) {
                        $cron_day = $detail->cron_day;
                        if ($cron_day == 1) {

                            $cron_day = 3; // for third day

                            $mod_date = strtotime($detail->send_date . "+ 3 days");
                            $sendDate = date("Y-m-d 00:00:00", $mod_date);
                        } elseif ($cron_day == 3) {
                            $cron_day = 5; // for fivth day

                            $mod_date = strtotime($detail->send_date . "+ 5 days");
                            $sendDate = date("Y-m-d 00:00:00", $mod_date);
                        } elseif ($cron_day == 5) {
                            $cron_day = 10; // for tenth day

                            $mod_date = strtotime($detail->send_date . "+ 10 days");
                            $sendDate = date("Y-m-d 00:00:00", $mod_date);
                        } else if ($cron_day == 10) {
                            $result = $this->db->where('userid', $detail->userid)
                                ->set('send_date', $detail->send_date)
                                ->set('isSent', 0)
                                ->set('isDelete', 1)
                                ->update('event_send_history');
                            return false;
                        }
                        $result = $this->db->where('userid', $detail->userid)
                            ->set('send_date', $sendDate)
                            ->set('cron_day', $cron_day)
                            ->set('isSent', 0)
                            ->update('event_send_history');
                        // if($result){
                        //            return true;
                        // }else{
                        //            return false;
                        // }
                        return true;
                    } else {

                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
    }
    function dumpExceptionMessage()
    {
        /*
         *
         * dump message into database.
         */
    }

    //Update monthly user_extra_packages table
    function updateExtraPackage() {


        $file_name = 'updateExtraPackage.txt';
        $this->campaign_model->monthlyUpdateExtraPackage();
        $this->brand_model->monthlyUpdateExtraPackage();

    }

    function updateUserDefaultPackage()
    {
        /*
           $file_name = 'updateUserDefaultPackage.txt';
           $file = './upload/cron_lock/' . $file_name;
        */
        $this->campaign_model->updateDefaultPackage();
        echo $this->db->last_query();
        $this->brand_model->updateDefaultPackage();
        echo $this->db->last_query();
    }

    //function to send cron job

    function sendEmailtoUsers() {

        $result = $this->offer_model->getMail();
        if (count($result) > 0) {
            $emails = array();
            foreach ($result as $data) {
                $emails[] = $data->email_id;
                $info['from_email'] = $data->from_email;
                $info['email_id'] = $data->email_id;
                $info['subject'] = $data->subject;
                $info['message'] = $data->message;
                $info['attachment'] = $data->attachment;
                $sent = $this->emailSend($info);
                if ($sent) {
                    $this->offer_model->saveEmailStatus($data->userid, $data->campaignId);
                }
            }
            if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/Campaign_pdf/Hurree-QR-code-offer.pdf')) {
                unlink($_SERVER["DOCUMENT_ROOT"] . '/upload/Campaign_pdf/Hurree-QR-code-offer.pdf');
            }
            $this->offer_model->updateMailStatus($emails);

            return true;
        }
        return true;
    }

    function emailSend($info = false) {
        $this->emailConfig();
        $this->email->from($info['from_email'], 'Hurree');
        $this->email->to($info['email_id']);
        $this->email->subject($info['subject']);
        $this->email->message($info['message']);
        if (!empty($info['attachment'])) {
            $this->email->attach($info['attachment']);
        }
        $sent = $this->email->send();

        $this->email->clear(TRUE);
        //echo $this->email->print_debugger(); exit;
        // // EMAIL SEND
        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

    /* This function is used for insert push campaigns users at launch delivery. */
      public function deliverPushCampaignsAtLaunch() {
          $this->load->helper('cron');
           $isCronActive = isCronActive('deliverPushCampaignsAtLaunch.txt',5);
           if($isCronActive == true){
               return false;
           }
           updateCronTime('deliverPushCampaignsAtLaunch.txt');
          $results = $this->brand_model->getAllPushCampaignsAtLaunch(); //echo '<pre>'; print_r($results); //exit;
          if (count($results) > 0) {
              foreach ($results as $result) {
                  $app_group_id = $result->app_group_id;
                  $platform = $result->platform;
                  $campaignName = $result->campaignName;
                  $campaign_id = $result->id;

                  $notification_arr = array(
                      'campaign_id' => $campaign_id,
                      'app_group_id' => $app_group_id,
                      'platform' => $platform,
                      'notification_alert_count' => 1,
                      'createdDate' => date('Y-m-d H:i:s'),
                      'modifiedDate' => date('Y-m-d H:i:s')
                  );
                  $notificationRow = $this->brand_model->getNotificationByCampaignId($campaign_id); // GET ROW FROM notification_send_details
                  $status = "continue";

                  $platformIds = '';
                  $campaign_send_date_time = '';
                  $platformApps = array();
                  if ($platform == 'android') {
                      $platformIds = $this->groupapp_model->getAndroidApps($app_group_id);
                      $campaign_send_date_time = $platformIds[0]->createdDate;
                      foreach ($platformIds as $platformId) {
                          array_push($platformApps, $platformId->app_group_apps_id);
                      }
                  } else if ($platform == 'iOS') {
                      $platformIds = $this->groupapp_model->getIOSApps($app_group_id);
                      $campaign_send_date_time = $platformIds[0]->createdDate;
                      foreach ($platformIds as $platformId) {
                          array_push($platformApps, $platformId->app_group_apps_id);
                      }
                  }
                  $platformIds = implode(',', $platformApps);
                  $platformIds = rtrim($platformIds, ',');
                  $personaAssignContactArr = array();
                  $listContactArr = array();
                  if (!empty($result->persona_user_id)) {
                      $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                      if (count($personaAssignContacts) > 0) {
                          foreach ($personaAssignContacts as $personaAssignContact) {
                              array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                          }
                      }
                  }
                  if (!empty($result->list_id)) {
                      $listContacts = getContactsByListId($result->list_id, $result->businessId);
                      if (!empty($listContacts)) {
                          $listContactArr = explode(',', $listContacts);
                      }
                  }
                  $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                  $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                  $addtional_users = array_merge($add_int_users, $add_diff_users); // list and persona contacts users
                  $recent_push_device = 0; $limit_ipad_device = 0; $limit_ipod_iphone_device = 0;

                  if ($result->send_push_to_recently_used_device == 1) {
                      $recent_push_device = 1;
                  }
                  if ($result->limit_this_push_to_iPad_devices == 1) {
                      $limit_ipad_device = 1;
                  }
                  if ($result->limit_this_push_to_iphone_and_ipod_devices == 1) {
                      $limit_ipod_iphone_device = 1;
                  }

                  if ($result->delivery_type == 1) { // for schedule delivery
                      if ($result->time_based_scheduling == 1) { // for delivery at launch
                          $next_notification_send_date = $result->createdDate; // notification created date
                          if (count($notificationRow) == 0) {
                              if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                                  $status = "complete";
                              }
                              $notification_arr = array_merge($notification_arr, array('notification_type' => 'launch', 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                          } else {
                              $notification_arr = array_merge($notification_arr, array('notification_id' => $notificationRow[0]->notification_id, 'notification_type' => 'launch', 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                          }// save record in notification_send_details table
                          $this->brand_model->saveNotificationDetails($notification_arr);
                      } else {
                          if ($result->time_based_scheduling == 2) { // designated time
                              $notification_type = $result->send;
                          }
                          if ($result->time_based_scheduling == 3) {  // intelligent time
                              $notification_type = $result->intelligent_send;
                          }

                          if (count($notificationRow) == 0) {
                              $notification_send_date = $result->notification_send_date;
                              $pos = strpos($notification_send_date, "AM");
                              if ($pos !== false) {
                                  $notification_send_date = explode("AM", $notification_send_date);
                                  $notification_send_date = date('Y-m-d H:i:s', strtotime($notification_send_date[0]));
                              }
                              $pos = strpos($notification_send_date, "PM");
                              if ($pos !== false) {
                                  $notification_send_date = explode("PM", $notification_send_date);
                                  $notification_send_date = date('Y-m-d H:i:s', strtotime($notification_send_date[0] . "+12 hours"));
                              }
                              $next_notification_send_date = $notification_send_date;
                              $notification_arr = array_merge($notification_arr, array('notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date, 'status' => $status));

                              $this->brand_model->saveNotificationDetails($notification_arr);  // save data in notification_send_details
                          }
                      }
                  }

                  if ($result->time_based_scheduling == 1) {  // for launch
                      $offset = 0;
                      $limit = 1000;
                      $i = 0;
                      $totalReceiveCampaignsUsers = 0;
                      $countIndex = array();
                      $segments = $result->segments;
                      if (isset($segments)) {
                          $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                      }
                      $filters = $result->filters;
                      if (!empty($filters)) {
                          $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                      }

                      callbegin:
                      $userIds = $deviceIds = $send_notitfication_history = array();
                      $flag = 0;
                      if (count($users) > 0) {
                          foreach ($users as $key => $device) {
                              if (!empty($device->push_notification_token)) {

                                  if (isset($result->receiveCampaignType)) {
                                      $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                                      if ($result->receiveCampaignType == 1) {
                                          $i = $i + 1; // work as count element
                                          if ($i > $totalReceiveCampaignsUsers) {
                                              $flag = 1; break;
                                          }
                                      } else if ($result->receiveCampaignType == 2) {
                                          $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                          if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                              $flag = 1; break;
                                          }
                                      }
                                  }
                                  $notification_timezone_send_time = $next_notification_send_date;
                                  $send_notitfication_history[] = array(
                                      'campaign_id' => $campaign_id,
                                      'user_id' => $device->businessId,
                                      'platform' => $platform,
                                      'app_group_apps_id' => $device->app_group_apps_id,
                                      'active_device_id' => $device->active_device_id,
                                      'deviceToken' => $device->push_notification_token,
                                      'external_user_id' => $device->external_user_id,
                                      'notification_send_time' => $next_notification_send_date,
                                      'notification_timezone_send_time' => $next_notification_send_date,
                                      'is_send' => '0',
                                      'createdDate' => date('Y-m-d H:i:s')
                                  );
                                  $countIndex[] = $key;
                              } // END IF
                          } // END foreach
                      } // END IF
                      if (count($send_notitfication_history) > 0) {
                          $notification_id = $this->brand_model->saveNotificationHistory($send_notitfication_history);
                          updateCronTime('deliverPushCampaignsAtLaunch.txt');
                      }
                      if($flag == 0){
                          $offset = count($countIndex);
                          $limit = 1000;
                          if (isset($segments)) {
                              $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                          }
                          $filters = $result->filters;
                          if (!empty($filters)) {
                              $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                          }
                          if (count($users) > 0) { //echo $offset.':'.$limit.',';
                              goto callbegin;
                          }
                      }
                      $push_campaign_arr = array('id' => $campaign_id, 'send' => 1);
                      $this->brand_model->sendCampaigns($push_campaign_arr); // for launch push event done.
                  } // END IF
              }  // End foreach
          } // end If
      }
      /* This function is used for insert push users at schedule based delivery in designated time and intelligent delivery. */
      public function deliverAllPushCampaignsAtTime() {
          $this->load->helper('cron');
          $isCronActive = isCronActive('deliverAllPushCampaignsAtTime.txt',5);
          if($isCronActive == true){
              return false;
          }
          updateCronTime('deliverAllPushCampaignsAtTime.txt');

          $results = $this->brand_model->getAllActivePushCampaigns(); // echo "<pre>"; print_r($results); exit;
          if (count($results) > 0) {
              foreach ($results as $result) {
                  $currentDate = $result->notification_send_datetime;
                  $campaign_id = $result->campaign_id;
                  $notification_id = $result->notification_id; // work as row id from inapp_notification_send_details
                  $notification_type = $result->notification_type;
                  $status = 'continue';
                  $next_notification_send_date = $result->notification_send_datetime;
                  $notification_alert_count = $result->notification_alert_count + 1;
                  $createdDate = $result->createdDate;
                  if ($result->notification_type == 'launch') { // for launch
                      if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                          $status = 'complete';
                      } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 2) {
                          $status = 'complete';
                      } else { // for launch with reeligibe date
                          if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                              $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                          }
                      }
                  } else if ($result->notification_type == 'once') {  // for once
                      if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                          $status = 'complete';
                      } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 2) {
                          $status = 'complete';
                      } else { // once reeleigible start
                          if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                              $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                          }
                      }
                  }

                  if ($result->delivery_type == 1) {
                      $ending_on_the_date = '';
                      if ($result->time_based_scheduling == 2) { // designated time
                          if ($result->ending == 'never' || $result->ending == 'after') {
                              if ($result->ending_on_the_date != '0000-00-00') {
                                  $ending_on_the_date = $result->ending_on_the_date;
                              }
                          }
                      } else if ($result->time_based_scheduling == 3) { // intelligent time
                          if ($result->ending == 'never' || $result->ending == 'after') {
                              if ($result->intelligent_ending_on_the_date != '0000-00-00') {
                                  $ending_on_the_date = $result->intelligent_ending_on_the_date;
                              }
                          }
                      }
                  }

                  $app_group_id = $result->app_group_id;
                  $platform = $result->platform;

                  $notification_arr = array(
                      'notification_id' => $notification_id,
                      'campaign_id' => $campaign_id,
                      'app_group_id' => $app_group_id,
                      'platform' => $platform,
                      'notification_type' => $notification_type,
                      'notification_alert_count' => $notification_alert_count,
                      'createdDate' => $createdDate,
                      'modifiedDate' => date('Y-m-d H:i:s')
                  );

                  if ($result->delivery_type == 1) {
                      if ($result->send == 'once') {
                          $next_notification_send_date = $next_notification_send_date;
                      }
                      if ($result->time_based_scheduling == 2 && $result->notification_alert_count > 0) { // designated time create new date for continue send
                          $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                          if ($result->send == 'daily') {
                              if (!empty($result->everyDay)) { // daily date
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyDay days"));
                              } else {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                              }
                          } else if ($result->send == 'weekly') {
                              if (!empty($result->weekday)) {  // weekly date
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->weekday"));
                              } else {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                              }
                          } else if ($result->send == 'monthly') { // monthly date
                              if (!empty($result->everyMonth)) {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyMonth Months"));
                              } else {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                              }
                          }
                      } else if ($result->time_based_scheduling == 3 && $result->notification_alert_count > 0) { // intelligent time create new date for continue send
                          $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                          if ($result->send == 'daily') { // daily
                              if (!empty($result->intelligent_everyDay)) {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyDay days"));
                              } else {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                              }
                          } else if ($result->send == 'weekly') { // weekly
                              if (!empty($result->intelligent_weekday)) {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_weekday"));
                              } else {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                              }
                          } else if ($result->send == 'monthly') {  // monthly
                              if (!empty($result->intelligent_weekday)) {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyMonth Months"));
                              } else {
                                  $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                              }
                          }
                      }

                      if ($currentDate <= $ending_on_the_date) { // check end date
                          if ($result->reEligible_to_receive_campaign == 0) {
                              $status = "complete"; //continue;
                          }
                      } elseif ($currentDate >= $ending_on_the_date && $result->reEligible_to_receive_campaign == 1) {
                          $reElegible_date = date('Y-m-d', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                          if ($reElegible_date <= $currentDate) {
                              $status = "complete"; //continue;
                          } else if ($reElegible_date >= $ending_on_the_date && $ending_on_the_date != '') {
                              $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                              $status = "complete"; //continue;
                          }
                      }
                  }
                  $save_notification = array_merge($notification_arr, array('status' => $status, 'notification_send_date' => $next_notification_send_date)); //print_r(  $save_notification); exit;
                  $this->brand_model->saveNotificationDetails($save_notification); // save notification_send_details new date with status // print_r( $save_notification ); exit;

                  $platformIds = '';
                  $campaign_send_date_time = '';
                  $platformApps = array();
                  if ($platform == 'android') {
                      $platformIds = $this->groupapp_model->getAndroidApps($app_group_id);
                      $campaign_send_date_time = $platformIds[0]->createdDate;
                      foreach ($platformIds as $platformId) {
                          array_push($platformApps, $platformId->app_group_apps_id);
                      }
                  } else if ($platform == 'iOS') {
                      $platformIds = $this->groupapp_model->getIOSApps($app_group_id);
                      $campaign_send_date_time = $platformIds[0]->createdDate;
                      foreach ($platformIds as $platformId) {
                          array_push($platformApps, $platformId->app_group_apps_id);
                      }
                  }
                  $platformIds = implode(',', $platformApps);
                  $platformIds = rtrim($platformIds, ',');
                  $personaAssignContactArr = array();
                  $listContactArr = array();
                  if (!empty($result->persona_user_id)) {
                      $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                      if (count($personaAssignContacts) > 0) {
                          foreach ($personaAssignContacts as $personaAssignContact) {
                              array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                          }
                      }
                  }
                  if (!empty($result->list_id)) {
                      $listContacts = getContactsByListId($result->list_id, $result->businessId);
                      if (!empty($listContacts)) {
                          $listContactArr = explode(',', $listContacts);
                      }
                  }
                  $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                  $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                  $addtional_users = array_merge($add_int_users, $add_diff_users);
                  $recent_push_device = 0; $limit_ipad_device = 0; $limit_ipod_iphone_device = 0;

                  if ($result->send_push_to_recently_used_device == 1) {
                      $recent_push_device = 1;
                  }
                  if ($result->limit_this_push_to_iPad_devices == 1) {
                      $limit_ipad_device = 1;
                  }
                  if ($result->limit_this_push_to_iphone_and_ipod_devices == 1) {
                      $limit_ipod_iphone_device = 1;
                  }

                  $segments = $result->segments; //1;//
                  $flag = 0;
                  $offset = 0;
                  $limit = 1000;
                  $i = 0;
                  $totalReceiveCampaignsUsers = 0;
                  $index = array();
                  if (isset($segments)) {
                      $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                  }

                  $filters = $result->filters; //'1,2,10';//
                  if (!empty($filters)) {
                      $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                  }

                  $userIds = $deviceIds = array();

                  callbegin:
                  unset($send_notitfication_history);
                  $send_notitfication_history = array();
                  if (count($users) > 0) {
                      foreach ($users as $key => $device) {
                          if (!empty($device->push_notification_token)) {

                              if (isset($result->receiveCampaignType)) {
                                  $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                                  if ($result->receiveCampaignType == 1) {
                                      $i = $i + 1; // use for count element
                                      if ($i > $totalReceiveCampaignsUsers) {
                                          $flag = 1; break;
                                      }
                                  } else if ($result->receiveCampaignType == 2) {
                                      $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                      if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                          $flag = 1; break;
                                      }
                                  }
                              }

                              $notification_timezone_send_time = $next_notification_send_date;
  				 // used for create user timezone
                              if ($result->time_based_scheduling == 2) {
                                  if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                      if (!empty($device->timezone)) {
                                          $timezone = $device->timezone;
                                          $timezone = explode(" ", $timezone);
                                          $hours = 0;
                                          $minutes = 0;
                                          if ($timezone[0] == "GMT") {
                                              $hours = substr($timezone[1], 0, 3);
                                              $minutes = substr($timezone[1], 3, 5);
                                          }
                                          date_default_timezone_set('GMT');
                                          $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                                      }
                                  }
                              }

                              $send_notitfication_history[] = array(
                                  'campaign_id' => $campaign_id,
                                  'user_id' => $device->businessId,
                                  'platform' => $platform,
                                  'app_group_apps_id' => $device->app_group_apps_id,
                                  'active_device_id' => $device->active_device_id,
                                  'deviceToken' => $device->push_notification_token,
                                  'external_user_id' => $device->external_user_id,
                                  'notification_send_time' => $notification_timezone_send_time,
                                  'notification_timezone_send_time' => $notification_timezone_send_time,
                                  'is_send' => '0',
                                  'createdDate' => date('Y-m-d H:i:s')
                              );

                              $index[] = $key;
                          } // END IF
                      } // END FOREACH
                  } // END IF

                  if (count($send_notitfication_history) > 0) {
                      $notification_id = $this->brand_model->saveNotificationHistory($send_notitfication_history);
                      updateCronTime('deliverAllPushCampaignsAtTime.txt');
                  }

                  if($flag == 0){
                      $offset = count($index);
                      $limit = 1000;
                      if (isset($segments)) {
                          $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                      }
                      $filters = $result->filters;
                      if (!empty($filters)) {
                          $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                      }
                      if (count($users) > 0) {
                          goto callbegin;
                      }
                  }
              } // END foreach
          } // END IF
      }

      /* This function is used for send push to users devices from notification_send_history table. */
          public function deliverNotificationHistory()
          {
              $this->load->helper('cron');
              $isCronActive = isCronActive('deliverNotificationHistory.txt',5);
              if($isCronActive == true){
                  return false;
              }
              updateCronTime('deliverNotificationHistory.txt');
              $limit = $chunckSize = 200;

              $totalCompains = $this->brand_model->countActiveHistoryCampaigns(); // echo "<pre>";
              if($totalCompains == null)
                  return false;
              foreach ($totalCompains as $total) {
                  $tatalCampains = $total->counter;
                  $campaign_id = $total->campaign_id; // used for campaign id
                  updateCronTime('deliverNotificationHistory.txt');
                  if ($chunckSize < $tatalCampains) {
                      $offset = 0;
                      $total_loop = (int)$tatalCampains/ $chunckSize;
                      $extraloop = (int)$tatalCampains % $chunckSize;
                      for($counter =0; $counter < $total_loop; $counter++)
                      {
                          $offset = $counter * $limit;
                          $results = $this->brand_model->getAllActiveHistoryCampaigns($limit, $offset, $campaign_id);
                          if($results != null)
                              $this->innerCallPushNotification($results);
                      }
                      $offset = $total_loop * $limit;
                      $results = $this->brand_model->getAllActiveHistoryCampaigns($extraloop, $offset, $campaign_id);
                      if($results != null)
                          $this->innerCallPushNotification($results);
                  }
                  else
                  {
                      $results = $this->brand_model->getAllActiveHistoryCampaigns($limit, 0, $campaign_id);
                      if($results != null)
                          $this->innerCallPushNotification($results);
                  }
              }

          }

          /*
             * calling this function inner from deliverNotificationHistory() function and passed set of record
             *
             */
            function innerCallPushNotification($results)
            {
                foreach ($results as $result) {
                    $notification_id = $result->notification_id;
                    $deviceToken = $result->deviceToken;
                    $app_group_id = $result->app_group_id;
                    $title = $result->campaignName;
                    $deviceType = $result->platform;
                    $campaignName = $result->campaignName;

                    $firstname = $result->firstName;
                    $lastname = $result->lastName;
                    $gender = $result->gender;
                    $email = $result->email;
                    $phoneNumber = $result->phoneNumber;
                    $company = $result->company;
                    $date_of_birth = $result->date_of_birth;
                    $timezone = $result->timezone;
                    $userRows = $this->brand_model->getExternalUserActiveRow($result->external_user_id);

                    if (count($userRows) > 0) {
                        $last_used_app_date = $userRows->dateTime;
                        $most_recent_app_version = $userRows->sdk_version;
                        $username = '';
                    } else {
                        $last_used_app_date = '';
                        $most_recent_app_version = '';
                        $username = '';
                    }

                    $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($result->campaign_id);
                    // $set_user_to_unsubscribed_url = 'Unsubscribed link: '.base_url() . "hurreeEmail/unsubscribe/" . base64_encode($email)."/".$rowBusinessId->businessId."/".$rowBusinessId->app_group_id;

                    $push_title = $result->push_title;

                    $push_title = str_replace('{{${date_of_birth}}}', $date_of_birth, $push_title);
                    $push_title = str_replace('{{${company}}}', $company, $push_title);
                    $push_title = str_replace('{{${email_address}}}', $email, $push_title);
                    $push_title = str_replace('{{${first_name}}}', $firstname, $push_title);
                    $push_title = str_replace('{{${last_name}}}', $lastname, $push_title);
                    $push_title = str_replace('{{${gender}}}', $gender, $push_title);
                    $push_title = str_replace('{{${last_used_app_date}}}', $last_used_app_date, $push_title);
                    $push_title = str_replace('{{${most_recent_app_version}}}', $most_recent_app_version, $push_title);
                    $push_title = str_replace('{{${phone_number}}}', $phoneNumber, $push_title);
                    $push_title = str_replace('{{${time_zone}}}', $timezone, $push_title);
                    $push_title = str_replace('{{${username}}}', $username, $push_title);
                    $push_title = str_replace('{{campaign.${name}}}', $campaignName, $push_title);
                    // $push_title = str_replace('{{${set_user_to_unsubscribed_url}}}', $set_user_to_unsubscribed_url, $push_title);

                    $push_message = $result->push_message;

                    $push_message = str_replace('{{${date_of_birth}}}', $date_of_birth, $push_message);
                    $push_message = str_replace('{{${company}}}', $company, $push_message);
                    $push_message = str_replace('{{${email_address}}}', $email, $push_message);
                    $push_message = str_replace('{{${first_name}}}', $firstname, $push_message);
                    $push_message = str_replace('{{${last_name}}}', $lastname, $push_message);
                    $push_message = str_replace('{{${gender}}}', $gender, $push_message);
                    $push_message = str_replace('{{${last_used_app_date}}}', $last_used_app_date, $push_message);
                    $push_message = str_replace('{{${most_recent_app_version}}}', $most_recent_app_version, $push_message);
                    $push_message = str_replace('{{${phone_number}}}', $phoneNumber, $push_message);
                    $push_message = str_replace('{{${time_zone}}}', $timezone, $push_message);
                    $push_message = str_replace('{{${username}}}', $username, $push_message);
                    $push_message = str_replace('{{campaign.${name}}}', $campaignName, $push_message);
                    // $push_message = str_replace('{{${set_user_to_unsubscribed_url}}}', $set_user_to_unsubscribed_url, $push_message);

                    if ($result->custom_url == 3) {
                        $push_on_click_behaviour = 3;
                    } else {
                        $push_on_click_behaviour = $result->custom_url;
                        $push_on_click_url = $result->redirect_url;
                    }

                    if (!empty($result->push_icon)) {
                        $push_image_url = base_url() . 'upload/pushNotificationCampaigns/icon/' . $result->push_icon;
                    }

                    if (!empty($result->expandedImage)) {
                        $expanded_img_url = base_url() . 'upload/pushNotificationCampaigns/expandedImage/' . $result->expandedImage;
                    }

                    if (!empty($result->push_img_url)) {
                        $push_image_url = $result->push_img_url;
                    }

                    if (!empty($result->expanded_img_url)) {
                        $expanded_img_url = $result->expanded_img_url;
                    }

                    if ($deviceType == 'iOS') {
                        $certificateType = $result->certificateType; // 1 for production , 2 for developement
                        $certificateName = $result->fileName; // certifivcate file name

                        $sound = 'default';
                        if (!empty($result->fileName)) {
                            $msgpayload = array(
                                'aps' => array(
                                    'alert' => $push_message,
                                    "app_group_id" => $app_group_id,
                                    "notification_id" => "$notification_id",
                                    "campaignName" => $campaignName,
                                    "push_title" => $push_title,
                                    "push_message" => $push_message,
                                    "push_on_click_behaviour" => isset($push_on_click_behaviour) ? "$push_on_click_behaviour" : "3",
                                    "push_on_click_url" => isset($push_on_click_url) ? $push_on_click_url : '',
                                    "push_image_url" => isset($push_image_url) ? $push_image_url : '',
                                    "expand_img_url" => isset($expand_img_url) ? $expand_img_url : '',
                                    'type' => 'sdkNotification',
                                    'sound' => $sound,
                                )
                            );
                            $response = $this->sendIosPush($deviceToken, $msgpayload, $certificateType, $certificateName);
                        }
                    } else if ($deviceType == 'android') {
                        $google_api_key = "AIzaSyBhuz3MFoUyNX3MNLZtma1l89sauNDGT7U"; // defalut key if user have no GCM key
                        $notification = array(
                            'alert' => $push_message,
                            "app_group_id" => $app_group_id,
                            "notification_id" => "$notification_id",
                            "campaignName" => $campaignName,
                            "push_title" => $push_title,
                            "push_message" => $push_message,
                            "push_on_click_behaviour" => isset($push_on_click_behaviour) ? "$push_on_click_behaviour" : "3",
                            "push_on_click_url" => isset($push_on_click_url) ? $push_on_click_url : '',
                            "push_image_url" => isset($push_image_url) ? $push_image_url : '',
                            "expand_img_url" => isset($expanded_img_url) ? $expanded_img_url : '',
                            'type' => 'sdkNotification'
                        );
                        $message = json_encode($notification);
                        if (!empty($result->GCM)) {
                            $google_api_key = $result->GCM;
                        }
                        $response = false;
                        $response = $this->androidPush($google_api_key, $deviceToken, $message);
                        $response = json_decode($response); // print_r(	$response); //exit;
          							if(isset($response) && $response->failure == "0"){
          								$response = true;
          							}
                    }
                    if(isset($response) && $response == "1"){
                      //print_r($response); exit;
                      $this->brand_model->sendNotificationSendHistory($notification_id); // update notifivcation_send_hitory row status for send or not.

                      $eventRow = $this->brand_model->getEventRowByTimezoneTime($result->notification_timezone_send_time, $result->external_user_id, $result->app_group_apps_id);
                      if (count($eventRow) < 1) {
                          $send_event_history = array(
                              'external_user_id' => $result->external_user_id,
                              'app_group_apps_id' => $result->app_group_apps_id,
                              'active_device_id' => $result->active_device_id,
                              'screenName' => 'send ' . ucfirst($campaignName),
                              'eventName' => 'send ' . ucfirst($campaignName),
                              'eventDate' => $result->notification_timezone_send_time,
                              'eventType' => 'campaignNotification',
                              'isExportHubspot' => 0,
                              'isDelete' => 0,
                              'createdDate' => date('Y-m-d H:i:s')
                          );
                          $this->brand_model->saveEventsHistory($send_event_history); // insert event in events table
                      }
                   }
                }
            }
           // This function is not in use now ///

    public function deliverNotificationHistoryOld() {
        $results = $this->brand_model->getAllActiveHistoryCampaigns(); // echo "<pre>"; print_r($results); exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $notification_id = $result->notification_id;
                $deviceToken = $result->deviceToken;
                $app_group_id = $result->app_group_id;
                $title = $result->campaignName;
                $deviceType = $result->platform;
                $campaignName = $result->campaignName;
                $push_title = $result->push_title;
                $push_message = $result->push_message;
                if ($result->custom_url == 3) {
                    $push_on_click_behaviour = 3;
                } else {
                    $push_on_click_behaviour = $result->custom_url;
                    $push_on_click_url = $result->redirect_url;
                }

                if (!empty($result->push_icon)) {
                    $push_image_url = base_url() . 'upload/pushNotificationCampaigns/icon/' . $result->push_icon;
                }

                if (!empty($result->expandedImage)) {
                    $expanded_img_url = base_url() . 'upload/pushNotificationCampaigns/expandedImage/' . $result->expandedImage;
                }

                if (!empty($result->push_img_url)) {
                    $push_image_url = $result->push_img_url;
                }

                if (!empty($result->expanded_img_url)) {
                    $expanded_img_url = $result->expanded_img_url;
                }

                if ($deviceType == 'iOS') {
                    $certificateType = $result->certificateType;
                    $certificateName = $result->fileName;

                    $sound = 'default';
                    if (!empty($result->fileName)) {
                        $msgpayload = array(
                            'aps' => array(
                                'alert' => $push_message,
                                "app_group_id" => $app_group_id,
                                "notification_id" => "$notification_id",
                                "campaignName" => $campaignName,
                                "push_title" => $push_title,
                                "push_message" => $push_message,
                                "push_on_click_behaviour" => isset($push_on_click_behaviour) ? "$push_on_click_behaviour" : "3",
                                "push_on_click_url" => isset($push_on_click_url) ? $push_on_click_url : '',
                                "push_image_url" => isset($push_image_url) ? $push_image_url : '',
                                "expand_img_url" => isset($expand_img_url) ? $expand_img_url : '',
                                'type' => 'sdkNotification',
                                'sound' => $sound,
                            )
                        );
                        $this->sendIosPush($deviceToken, $msgpayload, $certificateType, $certificateName);
                    }
                } else if ($deviceType == 'android') {
                    $notification = array(
                        'alert' => $push_message,
                        "app_group_id" => $app_group_id,
                        "notification_id" => "$notification_id",
                        "campaignName" => $campaignName,
                        "push_title" => $push_title,
                        "push_message" => $push_message,
                        "push_on_click_behaviour" => isset($push_on_click_behaviour) ? "$push_on_click_behaviour" : "3",
                        "push_on_click_url" => isset($push_on_click_url) ? $push_on_click_url : '',
                        "push_image_url" => isset($push_image_url) ? $push_image_url : '',
                        "expand_img_url" => isset($expanded_img_url) ? $expanded_img_url : '',
                        'type' => 'sdkNotification'
                    );
                    $message = json_encode($notification);
                    if (!empty($result->GCM)) {
                        $google_api_key = $result->GCM;
                    } else {
                        $google_api_key = "AIzaSyBhuz3MFoUyNX3MNLZtma1l89sauNDGT7U";
                    }
                    $response = $this->androidPush($google_api_key, $deviceToken, $message);
                }
                //print_r($response); exit;
                $this->brand_model->sendNotificationSendHistory($notification_id);

                $eventRow = $this->brand_model->getEventRowByTimezoneTime($result->notification_timezone_send_time, $result->external_user_id, $result->app_group_apps_id);
                if (count($eventRow) < 1) {
                    $send_event_history = array(
                        'external_user_id' => $result->external_user_id,
                        'app_group_apps_id' => $result->app_group_apps_id,
                        'active_device_id' => $result->active_device_id,
                        'screenName' => 'send ' . ucfirst($campaignName),
                        'eventName' => 'send ' . ucfirst($campaignName),
                        'eventDate' => $result->notification_timezone_send_time,
                        'eventType' => 'campaignNotification',
                        'isExportHubspot' => 0,
                        'isDelete' => 0,
                        'createdDate' => date('Y-m-d H:i:s')
                    );
                    $this->brand_model->saveEventsHistory($send_event_history);
                }
            }
        }
    }

    public function androidPush($google_api_key, $deviceToken, $message) {
        $this->load->library('Gcm');
        $gcm = new GCM();
        $result = $gcm->sendPushNotification($google_api_key, $deviceToken, $message);
        return $result;
    }

    // aws push notification
    public function amazonSns($deviceToken, $message, $deviceType) {
        if ($deviceType == 'iOS') {
            $this->load->library('Aws_sdk');
            $Aws_sdk = new Aws_sdk();
            $iOS_AppArn = "arn:aws:sns:us-west-2:831947047245:app/APNS_SANDBOX/Hurree";
            //$iOS_AppArn = "arn:aws:sns:us-west-2:831947047245:app/APNS/hurree-production";
            $endpoint = $Aws_sdk->generateEndpoint($deviceToken, $iOS_AppArn);
            //echo 'endpoint'; print_r($endpoint); //exit;
            $result = $Aws_sdk->SendPushNotification($message, $endpoint, $deviceToken);
            //echo '<br>result '; print_r($endpoint); exit;
            return $result;
        } else {
            return false;
        }
    }

    // end amazon sns code
    // logout service for delete user's device token
    public function sendIosPush($deviceToken, $payload, $environmentType, $certificateName) {
        $payload = json_encode($payload);
        if ($environmentType == 1) {
            $apnsHost = 'gateway.push.apple.com';          /* for distribution */
        } else {
            $apnsHost = 'gateway.sandbox.push.apple.com';    /* for development  */
        }
        $apnsPort = '2195';

        $apnsCert = getcwd() . '/upload/apps/files/' . $certificateName;

        //echo file_get_contents($apnsCert);
        $passPhrase = '';
        $streamContext = stream_context_create();
        stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
        // Passphrase to the certificate
        stream_context_set_option($streamContext, 'ssl', 'passphrase', '');
        $error = '';
        $errorString = '';
        $apnsConnection = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $streamContext);
        if ($apnsConnection == false) {
            	return false;//  echo "false";die;
        }
        $apnsMessage = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload;
        if (fwrite($apnsConnection, $apnsMessage)) {
          	return true;  //   echo "Done";die;
        }
      //  fclose($apnsConnection);
        //die();
    }

    /* This function is used for update notification view status in notification_send_history table.*/
      public function deliverViewNotificationsUsers() {
          $userEvents = $this->brand_model->getAllEvents(); //echo '<pre>'; print_r($userEvents); exit;
          if (count($userEvents) > 0) {
              foreach ($userEvents as $user) {
                  $notification_id = $user->id;
                  $update = array('id' => $notification_id, 'notification_timezone_view_time' => date('Y-m-d H:i:s'), 'is_view' => '1');
                  $this->brand_model->updateSendHistory($update); // update notification_id status for view.
              }
          }
      }

  /* This url is used for insert email campaigns users at launch delivery. */

  public function deliverEmailCampaignsAtLaunch() {

       $this->load->helper('cron');
       $isCronActive = isCronActive('deliverEmailCampaignsAtLaunch.txt',5);
       if($isCronActive == true){
           return false;
       }
       updateCronTime('deliverEmailCampaignsAtLaunch.txt');

       $results = $this->brand_model->getAllEmailCampaignsAtLaunch();  //echo '<pre>'; print_r($results); exit;
       if (count($results) > 0) {
           foreach ($results as $result) {
               $app_group_id = $result->app_group_id;
               $platform = $result->platform;
               $campaignName = $result->campaignName;
               $push_title = $result->push_title;
               $push_message = $result->push_message;
               $campaign_id = $result->id;
               $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

               if (!empty($result->displayName)) {
                   $displayName = $result->displayName;
               } else {
                   $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
               }

               if (!empty($result->fromAddress)) {
                   $fromAddress = $result->fromAddress;
               } else {
                   $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
               }

               if (!empty($result->replyToAddress)) {
                   $replyToAddress = $result->replyToAddress;
               } else {
                   $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
               }

               $notification_arr = array(
                   'campaign_id' => $campaign_id,
                   'app_group_id' => $app_group_id,
                   'platform' => $platform,
                   'notification_alert_count' => 1,
                   'createdDate' => date('Y-m-d H:i:s'),
                   'modifiedDate' => date('Y-m-d H:i:s')
               );
               $notificationRow = $this->brand_model->getNotificationByCampaignId($result->id); // get row from notification_send_details
               $status = "continue";

               $platformIds = '';
               $campaign_send_date_time = '';
               $platformApps = array();
               $platformIds = $this->groupapp_model->getAllApps($app_group_id);
               if (count($platformIds) > 0) {
                   $campaign_send_date_time = $platformIds[0]->createdDate;
                   foreach ($platformIds as $platformId) {
                       array_push($platformApps, $platformId->app_group_apps_id);
                   }
                   $platformIds = implode(',', $platformApps);
                   $platformIds = rtrim($platformIds, ',');
               }
               $personaAssignContactArr = array();
               $listContactArr = array();
               if (!empty($result->persona_user_id)) {
                   $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                   if (count($personaAssignContacts) > 0) {
                       foreach ($personaAssignContacts as $personaAssignContact) {
                           array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                       }
                   }
               }
               if (!empty($result->list_id)) {
                   $listContacts = getContactsByListId($result->list_id, $result->businessId);
                   if (!empty($listContacts)) {
                       $listContactArr = explode(',', $listContacts);
                   }
               }
               $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
               $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
               $addtional_users = array_merge($add_int_users, $add_diff_users);
               $recent_push_device = $limit_ipad_device = $limit_ipod_iphone_device = 0;

               if ($result->delivery_type == 1) {// for schedule delivery
                   if ($result->time_based_scheduling == 1) { // for delivery at launch
                       $next_notification_send_date = $result->createdDate;// notification created date
                       if (count($notificationRow) == 0) {
                           if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                               $status = "complete";
                           }
                           $notification_arr = array_merge($notification_arr, array('notification_type' => 'launch', 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                       } else {
                           $notification_arr = array_merge($notification_arr, array('notification_id' => $notificationRow[0]->notification_id, 'notification_type' => 'launch', 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                       }// save record in notification_send_details table
                       $this->brand_model->saveNotificationDetails($notification_arr);
                   } else {
                       if ($result->time_based_scheduling == 2) {// designated time
                           $notification_type = $result->send;
                       }
                       if ($result->time_based_scheduling == 3) {// intelligent time
                           $notification_type = $result->intelligent_send;
                       }

                       if (count($notificationRow) == 0) {
                           $notification_send_date = $result->notification_send_date;
                           $pos = strpos($notification_send_date, "AM");
                           if ($pos !== false) {
                               $notification_send_date = explode("AM", $notification_send_date);
                               $notification_send_date = date('Y-m-d H:i:s', strtotime($notification_send_date[0]));
                           }
                           $pos = strpos($notification_send_date, "PM");
                           if ($pos !== false) {
                               $notification_send_date = explode("PM", $notification_send_date);
                               $notification_send_date = date('Y-m-d H:i:s', strtotime($notification_send_date[0] . "+12 hours"));
                           }
                           $next_notification_send_date = $notification_send_date;
                           $notification_arr = array_merge($notification_arr, array('notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date, 'status' => $status));

                           $this->brand_model->saveNotificationDetails($notification_arr); // save data in notification_send_details
                       }
                   }
               }

               if ($result->time_based_scheduling == 1) { // for launch
                   $segments = $result->segments;
                   $offset = 0;
                   $limit = 1000;
                   $i = 0;
                   $totalReceiveCampaignsUsers = 0;
                   $countIndex = array();
                   if (isset($segments)) {
                       $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                   }
                   $filters = $result->filters;
                   if (!empty($filters)) {
                       $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                   } //echo "<pre>"; print_r($users); //exit;

                   $userIds = $emailIds = array();
                   $flag = 0;//exit;

                   callbegin:
                   unset($send_email_notitfication_history);
                   $send_email_notitfication_history = array();
                   if (count($users) > 0) {
                       foreach ($users as $key => $device) {

                           if (isset($result->receiveCampaignType)) {
                               $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                               if ($result->receiveCampaignType == 1) {
                                   $i = $i + 1;// use for count user
                                   if ($i > $totalReceiveCampaignsUsers) {
                                       $flag = 1; break;
                                   }
                               } else if ($result->receiveCampaignType == 2) {
                                   $totalSendCampaignsUsers = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                                   if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                       $flag = 1; break;
                                   }
                               }
                           }

                           $send_email_notitfication_history[] = array(
                               'email_id' => $device->email,
                               'hurree_version' => '3.1',
                               'userid' => $device->external_user_id,
                               'campaignId' => $campaign_id,
                               'app_group_apps_id' => $device->app_group_apps_id,
                               'active_device_id' => $device->active_device_id,
                               'from_email' => $fromAddress,
                               'replyToAddress' => $replyToAddress,
                               'subject' => $push_title,
                               'message' => $push_message,
                               'emailSentOn' => $next_notification_send_date,
                               'emailSentByUser' => $device->businessId,
                               'groupid' => '',
                               'opened' => 0,
                               'openTime' => '',
                               'active' => '1',
                               'created_on' => date('Y-m-d H:i:s')
                           );
                           $countIndex[] = $key;
                           //  } // END IF
                       } // END FOREACH
                   } // END IF

                   if (count($send_email_notitfication_history) > 0) {
                       $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                       updateCronTime('deliverEmailCampaignsAtLaunch.txt');
                   }
                   if($flag == 0){
                       $offset = count($countIndex);
                       $limit = 1000;
                       if (isset($segments)) {
                           $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                       }
                       $filters = $result->filters;
                       if (!empty($filters)) {
                           $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                       }
                       if (count($users) > 0) { //echo $offset.':'.$limit.',';
                           goto callbegin;
                       }
                   }
                   $push_campaign_arr = array('id' => $campaign_id, 'send' => 1); // for launch email laucnh event done.
                   $this->brand_model->sendCampaigns($push_campaign_arr);
               } // END IF
           }
       }
   }
   /* This function is used for insert email users at schedule based delivery in designated time and intelligent delivery. */
       public function deliverAllEmailCampaignsAtTime() {

           $this->load->helper('cron');
           $isCronActive = isCronActive('deliverAllEmailCampaignsAtTime.txt',5);
           if($isCronActive == true){
               return false;
           }
           updateCronTime('deliverAllEmailCampaignsAtTime.txt');

           $results = $this->brand_model->getAllActiveEmailCampaigns(); // echo '<pre>'; print_r($results); exit;
           if (count($results) > 0) {
               foreach ($results as $result) {
                   $currentDate = $result->notification_send_datetime;
                   $campaign_id = $result->campaign_id;
                   $notification_id = $result->notification_id;
                   $notification_type = $result->notification_type;
                   $status = 'continue';
                   $next_notification_send_date = $result->notification_send_datetime;
                   $notification_alert_count = $result->notification_alert_count + 1;
                   $createdDate = $result->createdDate;
                   if ($result->notification_type == 'launch') { // for launch
                       if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                           $status = 'complete';
                       } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 2) {
                           $status = 'complete';
                       } else { // for launch with reeligibe date
                           if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                               $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                           }
                       }
                   } else if ($result->notification_type == 'once') { // for once
                       if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                           $status = 'complete';
                       } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 2) {
                           $status = 'complete';
                       } else {// once reeleigible start
                           if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                               $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                           }
                       }
                   }

                   if ($result->delivery_type == 1) {
                       $ending_on_the_date = '';
                       if ($result->time_based_scheduling == 2) {// designated time
                           if ($result->ending == 'never' || $result->ending == 'after') {
                               if ($result->ending_on_the_date != '0000-00-00') {
                                   $ending_on_the_date = $result->ending_on_the_date;
                               }
                           }
                       } else if ($result->time_based_scheduling == 3) {// intelligent time
                           if ($result->ending == 'never' || $result->ending == 'after') {
                               if ($result->intelligent_ending_on_the_date != '0000-00-00') {
                                   $ending_on_the_date = $result->intelligent_ending_on_the_date;
                               }
                           }
                       }
                   }

                   $app_group_id = $result->app_group_id;
                   $platform = $result->platform;
                   $campaignName = $result->campaignName;
                   $push_title = $result->push_title;
                   $push_message = $result->push_message;

                   $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

                   if (isset($result->displayName)) {
                       $displayName = $result->displayName;
                   } else {
                       $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
                   }

                   if (isset($result->fromAddress)) {
                       $fromAddress = $result->fromAddress;
                   } else {
                       $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
                   }

                   if (isset($result->replyToAddress)) {
                       $replyToAddress = $result->replyToAddress;
                   } else {
                       $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
                   }

                   $notification_arr = array(
                       'notification_id' => $notification_id,
                       'campaign_id' => $campaign_id,
                       'app_group_id' => $app_group_id,
                       'platform' => $platform,
                       'notification_type' => $notification_type,
                       'notification_alert_count' => $notification_alert_count,
                       'createdDate' => $createdDate,
                       'modifiedDate' => date('Y-m-d H:i:s')
                   );

                   if ($result->delivery_type == 1) {
                       if ($result->send == 'once') {
                           $next_notification_send_date = $next_notification_send_date;
                       }

                       if ($result->time_based_scheduling == 2 && $result->notification_alert_count > 0) { //designated time
                           if ($result->send = 'daily') {
                               if (!empty($result->everyDay)) {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyDay days"));
                               } else {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                               }
                           } else if ($result->send == 'weekly') {
                               if (!empty($result->weekday)) {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->weekday"));
                               } else {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                               }
                           } else if ($result->send == 'monthly') {
                               if (!empty($result->everyMonth)) {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyMonth Months"));
                               } else {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                               }
                           }
                       } else if ($result->time_based_scheduling == 3 && $result->notification_alert_count > 0) {  //intellgined time
                           if ($result->send == 'daily') {
                               if (!empty($result->intelligent_everyDay)) {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyDay days"));
                               } else {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                               }
                           } else if ($result->send == 'weekly') {
                               if (!empty($result->intelligent_weekday)) {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_weekday"));
                               } else {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                               }
                           } else if ($result->send == 'monthly') {
                               if (!empty($result->intelligent_weekday)) {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyMonth Months"));
                               } else {
                                   $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                               }
                           }
                       }
                       if ($currentDate <= $ending_on_the_date) {
                           if ($result->reEligible_to_receive_campaign == 0) {
                               $status = "complete"; //continue;
                           }
                       } elseif ($currentDate >= $ending_on_the_date && $result->reEligible_to_receive_campaign == 1) {
                           $reElegible_date = date('Y-m-d', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                           if ($reElegible_date <= $currentDate) {
                               $status = "complete"; //continue;
                           } else if ($reElegible_date >= $ending_on_the_date && $ending_on_the_date != '') {
                               $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                               $status = "complete"; //continue;
                           }
                       }
                   }
                   $save_notification = array_merge($notification_arr, array('status' => $status, 'notification_send_date' => $next_notification_send_date));
                   $this->brand_model->saveNotificationDetails($save_notification); // save in notification_send_details

                   $platformIds = '';
                   $campaign_send_date_time = '';
                   $platformApps = array();
                   $platformIds = $this->groupapp_model->getAllApps($app_group_id);
                   $campaign_send_date_time = $platformIds[0]->createdDate;
                   foreach ($platformIds as $platformId) {
                       array_push($platformApps, $platformId->app_group_apps_id);
                   }
                   $platformIds = implode(',', $platformApps);
                   $platformIds = rtrim($platformIds, ',');
                   $personaAssignContactArr = array();
                   $listContactArr = array();
                   if (!empty($result->persona_user_id)) {
                       $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                       if (count($personaAssignContacts) > 0) {
                           foreach ($personaAssignContacts as $personaAssignContact) {
                               array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                           }
                       }
                   }
                   if (!empty($result->list_id)) {
                       $listContacts = getContactsByListId($result->list_id, $result->businessId);
                       if (!empty($listContacts)) {
                           $listContactArr = explode(',', $listContacts);
                       }
                   }
                   $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                   $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                   $addtional_users = array_merge($add_int_users, $add_diff_users); // concat contacts list and persona
                   $recent_push_device = 0;
                   $limit_ipad_device = 0;
                   $limit_ipod_iphone_device = 0;

                   $segments = $result->segments;
                   $flag = 0;
                   $offset = 0;
                   $limit = 1000;
                   $i = 0;
                   $totalReceiveCampaignsUsers = 0;
                   $index = array();
                   if (isset($segments)) {
                       $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                   }

                   $filters = $result->filters;
                   if (!empty($filters)) {
                       $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                   }  //echo "<pre>"; print_r($users); exit;
                   $userIds = $emailIds = array();

                   callbegin:
                   unset($send_email_notitfication_history);
                   $send_email_notitfication_history = array();
                   if (count($users) > 0) {
                       foreach ($users as $key => $device) {

                           if (isset($result->receiveCampaignType)) {
                               $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                               if ($result->receiveCampaignType == 1) {
                                   $i = $i + 1; // use for count user
                                   if ($i > $totalReceiveCampaignsUsers) {
                                       $flag = 1; break;
                                   }
                               } else if ($result->receiveCampaignType == 2) {
                                   $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                   if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                       $flag = 1; break;
                                   }
                               }
                           }

                           $notification_timezone_send_time = $next_notification_send_date;
   			// used for user local timezone
                           if ($result->time_based_scheduling == 2) {
                               if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                   $timezone = $device->timezone;
                                   $timezone = explode(" ", $timezone);
                                   $hours = 0;
                                   $minutes = 0;
                                   if ($timezone[0] == "GMT") {
                                       $hours = substr($timezone[1], 0, 3);
                                       $minutes = substr($timezone[1], 3, 5);
                                   }
                                   date_default_timezone_set('GMT');
                                   $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                               }
                           }


                           $send_email_notitfication_history[] = array(
                               'email_id' => $device->email,
                               'hurree_version' => '3.1',
                               'userid' => $device->external_user_id,
                               'campaignId' => $campaign_id,
                               'from_email' => $fromAddress,
                               'replyToAddress' => $replyToAddress,
                               'subject' => $push_title,
                               'message' => $push_message,
                               'emailSentOn' => $next_notification_send_date,
                               'emailSentByUser' => $device->businessId,
                               'groupid' => '',
                               'opened' => 0,
                               'openTime' => '',
                               'active' => '1',
                               'created_on' => date('Y-m-d H:i:s')
                           );
                           $index[] = $key;
                       } // END FOREACH
                   } // END IF

                   if (count($send_email_notitfication_history) > 0) {
                       $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                       updateCronTime('deliverAllEmailCampaignsAtTime.txt');
                   }
                   if($flag == 0){
                       $offset = count($index);
                       $limit = 1000;
                       if (isset($segments)) {
                           $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                       }
                       $filters = $result->filters;
                       if (!empty($filters)) {
                           $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                       }
                       if (count($users) > 0) {
                           goto callbegin; //echo "<pre>"; print_r($users); exit;
                       }
                   }
               } // END FOREACH
           } // END IF
       }/* This function is used for insert email users at schedule based delivery in designated time and intelligent delivery. */
    public function deliverAllEmailCampaignsAtTime() {

        $this->load->helper('cron');
        $isCronActive = isCronActive('deliverAllEmailCampaignsAtTime.txt',5);
        if($isCronActive == true){
            return false;
        }
        updateCronTime('deliverAllEmailCampaignsAtTime.txt');

        $results = $this->brand_model->getAllActiveEmailCampaigns(); // echo '<pre>'; print_r($results); exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $currentDate = $result->notification_send_datetime;
                $campaign_id = $result->campaign_id;
                $notification_id = $result->notification_id;
                $notification_type = $result->notification_type;
                $status = 'continue';
                $next_notification_send_date = $result->notification_send_datetime;
                $notification_alert_count = $result->notification_alert_count + 1;
                $createdDate = $result->createdDate;
                if ($result->notification_type == 'launch') { // for launch
                    if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                        $status = 'complete';
                    } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 2) {
                        $status = 'complete';
                    } else { // for launch with reeligibe date
                        if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                        }
                    }
                } else if ($result->notification_type == 'once') { // for once
                    if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                        $status = 'complete';
                    } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 2) {
                        $status = 'complete';
                    } else {// once reeleigible start
                        if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                        }
                    }
                }

                if ($result->delivery_type == 1) {
                    $ending_on_the_date = '';
                    if ($result->time_based_scheduling == 2) {// designated time
                        if ($result->ending == 'never' || $result->ending == 'after') {
                            if ($result->ending_on_the_date != '0000-00-00') {
                                $ending_on_the_date = $result->ending_on_the_date;
                            }
                        }
                    } else if ($result->time_based_scheduling == 3) {// intelligent time
                        if ($result->ending == 'never' || $result->ending == 'after') {
                            if ($result->intelligent_ending_on_the_date != '0000-00-00') {
                                $ending_on_the_date = $result->intelligent_ending_on_the_date;
                            }
                        }
                    }
                }

                $app_group_id = $result->app_group_id;
                $platform = $result->platform;
                $campaignName = $result->campaignName;
                $push_title = $result->push_title;
                $push_message = $result->push_message;

                $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

                if (isset($result->displayName)) {
                    $displayName = $result->displayName;
                } else {
                    $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
                }

                if (isset($result->fromAddress)) {
                    $fromAddress = $result->fromAddress;
                } else {
                    $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
                }

                if (isset($result->replyToAddress)) {
                    $replyToAddress = $result->replyToAddress;
                } else {
                    $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
                }

                $notification_arr = array(
                    'notification_id' => $notification_id,
                    'campaign_id' => $campaign_id,
                    'app_group_id' => $app_group_id,
                    'platform' => $platform,
                    'notification_type' => $notification_type,
                    'notification_alert_count' => $notification_alert_count,
                    'createdDate' => $createdDate,
                    'modifiedDate' => date('Y-m-d H:i:s')
                );

                if ($result->delivery_type == 1) {
                    if ($result->send == 'once') {
                        $next_notification_send_date = $next_notification_send_date;
                    }

                    if ($result->time_based_scheduling == 2 && $result->notification_alert_count > 0) { //designated time
                        if ($result->send = 'daily') {
                            if (!empty($result->everyDay)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyDay days"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'weekly') {
                            if (!empty($result->weekday)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->weekday"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'monthly') {
                            if (!empty($result->everyMonth)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyMonth Months"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        }
                    } else if ($result->time_based_scheduling == 3 && $result->notification_alert_count > 0) {  //intellgined time
                        if ($result->send == 'daily') {
                            if (!empty($result->intelligent_everyDay)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyDay days"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'weekly') {
                            if (!empty($result->intelligent_weekday)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_weekday"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'monthly') {
                            if (!empty($result->intelligent_weekday)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyMonth Months"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        }
                    }
                    if ($currentDate <= $ending_on_the_date) {
                        if ($result->reEligible_to_receive_campaign == 0) {
                            $status = "complete"; //continue;
                        }
                    } elseif ($currentDate >= $ending_on_the_date && $result->reEligible_to_receive_campaign == 1) {
                        $reElegible_date = date('Y-m-d', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                        if ($reElegible_date <= $currentDate) {
                            $status = "complete"; //continue;
                        } else if ($reElegible_date >= $ending_on_the_date && $ending_on_the_date != '') {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                            $status = "complete"; //continue;
                        }
                    }
                }
                $save_notification = array_merge($notification_arr, array('status' => $status, 'notification_send_date' => $next_notification_send_date));
                $this->brand_model->saveNotificationDetails($save_notification); // save in notification_send_details

                $platformIds = '';
                $campaign_send_date_time = '';
                $platformApps = array();
                $platformIds = $this->groupapp_model->getAllApps($app_group_id);
                $campaign_send_date_time = $platformIds[0]->createdDate;
                foreach ($platformIds as $platformId) {
                    array_push($platformApps, $platformId->app_group_apps_id);
                }
                $platformIds = implode(',', $platformApps);
                $platformIds = rtrim($platformIds, ',');
                $personaAssignContactArr = array();
                $listContactArr = array();
                if (!empty($result->persona_user_id)) {
                    $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                    if (count($personaAssignContacts) > 0) {
                        foreach ($personaAssignContacts as $personaAssignContact) {
                            array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                        }
                    }
                }
                if (!empty($result->list_id)) {
                    $listContacts = getContactsByListId($result->list_id, $result->businessId);
                    if (!empty($listContacts)) {
                        $listContactArr = explode(',', $listContacts);
                    }
                }
                $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                $addtional_users = array_merge($add_int_users, $add_diff_users); // concat contacts list and persona
                $recent_push_device = 0;
                $limit_ipad_device = 0;
                $limit_ipod_iphone_device = 0;

                $segments = $result->segments;
                $flag = 0;
                $offset = 0;
                $limit = 1000;
                $i = 0;
                $totalReceiveCampaignsUsers = 0;
                $index = array();
                if (isset($segments)) {
                    $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                }

                $filters = $result->filters;
                if (!empty($filters)) {
                    $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                }  //echo "<pre>"; print_r($users); exit;
                $userIds = $emailIds = array();

                callbegin:
                unset($send_email_notitfication_history);
                $send_email_notitfication_history = array();
                if (count($users) > 0) {
                    foreach ($users as $key => $device) {

                        if (isset($result->receiveCampaignType)) {
                            $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                            if ($result->receiveCampaignType == 1) {
                                $i = $i + 1; // use for count user
                                if ($i > $totalReceiveCampaignsUsers) {
                                    $flag = 1; break;
                                }
                            } else if ($result->receiveCampaignType == 2) {
                                $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                    $flag = 1; break;
                                }
                            }
                        }

                        $notification_timezone_send_time = $next_notification_send_date;
			// used for user local timezone
                        if ($result->time_based_scheduling == 2) {
                            if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                $timezone = $device->timezone;
                                $timezone = explode(" ", $timezone);
                                $hours = 0;
                                $minutes = 0;
                                if ($timezone[0] == "GMT") {
                                    $hours = substr($timezone[1], 0, 3);
                                    $minutes = substr($timezone[1], 3, 5);
                                }
                                date_default_timezone_set('GMT');
                                $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                            }
                        }


                        $send_email_notitfication_history[] = array(
                            'email_id' => $device->email,
                            'hurree_version' => '3.1',
                            'userid' => $device->external_user_id,
                            'campaignId' => $campaign_id,
                            'from_email' => $fromAddress,
                            'replyToAddress' => $replyToAddress,
                            'subject' => $push_title,
                            'message' => $push_message,
                            'emailSentOn' => $next_notification_send_date,
                            'emailSentByUser' => $device->businessId,
                            'groupid' => '',
                            'opened' => 0,
                            'openTime' => '',
                            'active' => '1',
                            'created_on' => date('Y-m-d H:i:s')
                        );
                        $index[] = $key;
                    } // END FOREACH
                } // END IF

                if (count($send_email_notitfication_history) > 0) {
                    $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                    updateCronTime('deliverAllEmailCampaignsAtTime.txt');
                }
                if($flag == 0){
                    $offset = count($index);
                    $limit = 1000;
                    if (isset($segments)) {
                        $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                    }
                    $filters = $result->filters;
                    if (!empty($filters)) {
                        $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                    }
                    if (count($users) > 0) {
                        goto callbegin; //echo "<pre>"; print_r($users); exit;
                    }
                }
            } // END FOREACH
        } // END IF
    }

    /* This function is used for send push users devices from notification_send_history table. */
     public function deliverCampaignsAtActionTrigger(){
         $this->load->helper('cron');
         $isCronActive = isCronActive('deliverCampaignsAtActionTrigger.txt',5);
         if($isCronActive == true){
               return false;
         }
         updateCronTime('deliverCampaignsAtActionTrigger.txt');
         $results = $this->brand_model->getAllActionTriggerCampaigns(); //echo '<pre>'; print_r($results); exit;
         $personaAssignContactArr = array();
         $listContactArr = array();
         if (count($results) > 0) {
             foreach ($results as $result) {
                 $app_group_id = $result->app_group_id;
                 $platform     = $result->platform;
                 $campaignName = $result->campaignName;
                 $notification_alert_count = $result->notification_alert_count + 1;
                 $campaign_id  = $result->id;
                 $notification_type = "action-based";
                 $push_title = $result->push_title;
                 $push_message = $result->push_message;
                 $notification_arr = array(
                     'campaign_id' => $campaign_id,
                     'app_group_id' => $app_group_id,
                     'platform' => $platform,
                     'notification_alert_count' => $notification_alert_count,
                     'createdDate' => date('Y-m-d H:i:s'),
                     'modifiedDate' => date('Y-m-d H:i:s')
                 );
                 $notificationRow = $this->brand_model->getNotificationByCampaignId($campaign_id);

                 $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

                 if (isset($result->displayName)) {
                     $displayName = $result->displayName;
                 } else {
                     $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
                 }

                 if (isset($result->fromAddress)) {
                     $fromAddress = $result->fromAddress;
                 } else {
                     $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
                 }

                 if (isset($result->replyToAddress)) {
                     $replyToAddress = $result->replyToAddress;
                 } else {
                     $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
                 }

                 $status = "continue";
                 $start_date = '';
                 $end_date = '';
                 $triggerAction = $result->triggerAction;
                 $triggerAction = explode(',',$triggerAction);
                 if(!empty($result->campaignDuration_startTime_date)){
                     $notification_start_date = $result->notification_send_date;
                     $pos = strpos($notification_start_date, "AM");
                     if ($pos !== false) {
                         $notification_start_date = explode("AM", $notification_start_date);
                         $notification_start_date = date('Y-m-d H:i:s', strtotime($notification_start_date[0]));
                     }
                     $pos = strpos($notification_start_date, "PM");
                     if ($pos !== false) {
                         $notification_start_date = explode("PM", $notification_start_date);
                         $notification_start_date = date('Y-m-d H:i:s', strtotime($notification_start_date[0] . "+12 hours"));
                     }
                     $start_date = $notification_start_date;
                 }
                 if(!empty($result->campaignDuration_endTime_date)){
                     if($result->campaignDuration_endTime_date != '0000-00-00'){
                         $notification_end_date = $result->campaignDuration_endTime_date .' '.$result->campaignDuration_endTime_hours.':'.$result->campaignDuration_endTime_mins.' '.$result->campaignDuration_endTime_am;
                         $pos = strpos($notification_end_date, "AM");
                         if ($pos !== false) {
                             $notification_end_date = explode("AM", $notification_end_date);
                             $notification_end_date = date('Y-m-d H:i:s', strtotime($notification_end_date[0]));
                         }
                         $pos = strpos($notification_end_date, "PM");
                         if ($pos !== false) {
                             $notification_end_date = explode("PM", $notification_end_date);
                             $notification_end_date = date('Y-m-d H:i:s', strtotime($notification_end_date[0] . "+12 hours"));
                         }
                         $end_date = $notification_end_date;
                     }else{
                         $end_date = "";
                     }//echo $end_date; exit;
                 }

                 if (!empty($result->persona_user_id)) {
                     $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                     if (count($personaAssignContacts) > 0) {
                         foreach ($personaAssignContacts as $personaAssignContact) {
                             array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                         }
                     }
                 }
                 if (!empty($result->list_id)) {
                     $listContacts = getContactsByListId($result->list_id,$result->businessId);
                     if (!empty($listContacts)) {
                         $listContactArr = explode(',',$listContacts);
                     }
                 }
                 $add_int_users = array_intersect($personaAssignContactArr,$listContactArr);
                 $add_diff_users = array_diff($personaAssignContactArr,$listContactArr);
                 $addtional_users = array_merge($add_int_users,$add_diff_users); // use for list and persona contacts
                 $next_notification_send_date = $result->createdDate;
                 if (count($notificationRow) == 0) {
                     if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                         $status = "continue";
                     }
                     $date = date('Y-m-d H:i:s');
                     if ($end_date > $date) {
                         $status = "complete";
                     }
                     $notification_arr = array_merge($notification_arr,array('notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date,'status' => $status));
                 } else {
                     $notification_arr = array_merge($notification_arr,array('notification_id' => $notificationRow[0]->notification_id,'notification_type' => $notification_type,'notification_send_date' => $next_notification_send_date,'status' => $status));
                 }
                 $this->brand_model->saveNotificationDetails($notification_arr); // used for entry in notification_send_details table

                 $segments  = $result->segments;
                 $filters = $result->filters;
                 $recent_push_device = 0;
                 $limit_ipad_device = 0;
                 $limit_ipod_iphone_device = 0;
                 $campaign_send_date_time = '';
                 $platformIds = '';
                 $platformApps = array();

                 if($platform == "iOS" || $platform == "android"){
                     if ($platform == 'android') {
                         $platformIds = $this->groupapp_model->getAndroidApps($app_group_id);
                         $campaign_send_date_time = $platformIds[0]->createdDate;
                         foreach ($platformIds as $platformId) {
                             array_push($platformApps, $platformId->app_group_apps_id);
                         }
                     } else if ($platform == 'iOS') {
                         $platformIds = $this->groupapp_model->getIOSApps($app_group_id);
                         $campaign_send_date_time = $platformIds[0]->createdDate;
                         foreach ($platformIds as $platformId) {
                             array_push($platformApps, $platformId->app_group_apps_id);
                         }
                     }
                     $platformIds = implode(',', $platformApps);
                     $platformIds = rtrim($platformIds, ',');
                     if ($result->send_push_to_recently_used_device == 1) {
                         $recent_push_device = 1;
                     }
                     if ($result->limit_this_push_to_iPad_devices == 1) {
                         $limit_ipad_device = 1;
                     }
                     if ($result->limit_this_push_to_iphone_and_ipod_devices == 1) {
                         $limit_ipod_iphone_device = 1;
                     }
                     $offset = 0;
                     $limit = 250;
                     $totalReceiveCampaignsUsers = 0;
                     $index = array();
                     if (isset($segments)) {
                         $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                     }
                     if (!empty($filters)) {
                         $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                     }
                     $triggerUsers = array();
                     if(count($triggerAction) > 0){
                         foreach($triggerAction as $key => $action){
                             $users_arr = $this->brand_model->getTriggerActionUsers($action,$platformIds,$start_date,$end_date,$campaign_id);
                             if(!empty($users_arr['external_user_id'])){ // print_r($users_arr);
                                 array_push($triggerUsers,$users_arr['external_user_id']);
                             }
                         }
                     }
                     if(count($triggerUsers) > 0){
                         $triggerUsers = implode(',',$triggerUsers);
                         $triggerUsers = explode(',',$triggerUsers);
                         $triggerUsers = array_unique($triggerUsers);
                         $receviedUsers = array();
                         $campaignReceivedUsers = $this->brand_model->getActionTriggerReceivedUsers($campaign_id,"push");
                         if(!empty($campaignReceivedUsers['external_user_id'])){
                             $receviedUsers = explode(',',$campaignReceivedUsers['external_user_id']);
                             $triggerUsers = array_diff($triggerUsers,$receviedUsers);
                         }
                     }
                     $userIds = $deviceIds = array();

                     callPushbegin:
                     $flag = 0;
                     unset($send_notitfication_history);
                     $send_notitfication_history = array();
                     if(count($triggerUsers) > 0){
                         if (count($users) > 0) {
                             foreach ($users as $key => $device) {
                                 if(in_array($device->external_user_id, $triggerUsers)) {
                                     if (isset($result->receiveCampaignType)) {
                                         $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;
                                         if ($result->receiveCampaignType == 1) {
                                             $i = isset($i) ? $i : $i = 0;
                                             $i = $i + 1; // use for count users
                                             if ($i > $totalReceiveCampaignsUsers) {
                                                 $flag = 1; break;
                                             }
                                         } else if ($result->receiveCampaignType == 2) {
                                             $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                             if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                                 $flag = 1; break;
                                             }
                                         }
                                     }

                                     $notification_timezone_send_time = $next_notification_send_date;
                                     /*if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                       if (!empty($device->timezone)) {
                                           $timezone = $device->timezone;
                                           $timezone = explode(" ", $timezone);
                                           $hours = 0;
                                           $minutes = 0;
                                           if ($timezone[0] == "GMT") {
                                               $hours = substr($timezone[1], 0, 3);
                                               $minutes = substr($timezone[1], 3, 5);
                                           }
                                           date_default_timezone_set('GMT');
                                           $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                                       }
                                     }*/
                                     if(!empty($result->scheduleDelay_afterTime)){
                                         $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$result->scheduleDelay_afterTime $result->scheduleDelay_afterTimeInterval"));
                                     }
                                     $send_notitfication_history[] = array(
                                         'campaign_id' => $campaign_id,
                                         'user_id' => $device->businessId,
                                         'platform' => $platform,
                                         'app_group_apps_id' => $device->app_group_apps_id,
                                         'active_device_id' => $device->active_device_id,
                                         'deviceToken' => $device->push_notification_token,
                                         'external_user_id' => $device->external_user_id,
                                         'notification_send_time' => $next_notification_send_date,
                                         'notification_timezone_send_time' => $next_notification_send_date,
                                         'is_send' => '0',
                                         'createdDate' => date('Y-m-d H:i:s')
                                     );
                                 } // END IF
                                 $index[] = $key;
                             } // END FOREACH
                         } // END IF
                     } // END IF
                     if($flag == 0){
                         $offset = count($index);
                         $limit  = 250;
                         if(count($send_notitfication_history) > 0){
                             $notification_id = $this->brand_model->saveNotificationHistory($send_notitfication_history);
                             updateCronTime('deliverCampaignsAtActionTrigger.txt');
                         }
                         if($offset > 0){
                             if (isset($segments)) {
                                 $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                             }
                             $filters = $result->filters;
                             if (!empty($filters)) {
                                 $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                             }
                             if(count($users) > 0){
                                 goto callPushbegin;
                             }
                         }
                     }
                 }else{
                     $platformIds = '';
                     $campaign_send_date_time = '';
                     $platformApps = array();
                     $platformIds = $this->groupapp_model->getAllApps($app_group_id);
                     if(count($platformIds) > 0){
                         $campaign_send_date_time = $platformIds[0]->createdDate;
                         foreach ($platformIds as $platformId) {
                             array_push($platformApps, $platformId->app_group_apps_id);
                         }
                         $platformIds = implode(',', $platformApps);
                         $platformIds = rtrim($platformIds, ',');
                     }
                     $offset = 0;
                     $limit = 250;
                     $i = 0;
                     $totalReceiveCampaignsUsers = 0;
                     $index = array();

                     if (isset($segments)) {
                         $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                     }
                     $filters = $result->filters;
                     if (!empty($filters)) {
                         $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                     }

                     $triggerUsers = array();
                     if(count($triggerAction) > 0){
                         foreach($triggerAction as $key => $action){
                             $users_arr = $this->brand_model->getTriggerActionUsers($action,$platformIds,$start_date,$end_date,$campaign_id);
                             //print_r($users_arr );
                             if(!empty($users_arr['external_user_id'])){ // print_r($users_arr);
                                 array_push($triggerUsers,$users_arr['external_user_id']);
                             }
                         }
                     }
                     if(count($triggerUsers) > 0){
                         $triggerUsers = implode(',',$triggerUsers);
                         $triggerUsers = explode(',',$triggerUsers);
                         $triggerUsers = array_unique($triggerUsers);
                         $receviedUsers = array();
                         $campaignReceivedUsers = $this->brand_model->getActionTriggerReceivedUsers($campaign_id,"email");
                         if(!empty($campaignReceivedUsers['external_user_id'])){
                             $receviedUsers = explode(',',$campaignReceivedUsers['external_user_id']);
                             $triggerUsers = array_diff($triggerUsers,$receviedUsers);
                         }
                     }
                     $userIds = $emailIds = array();
                     //echo "<pre>"; print_r($users); print_r($triggerUsers);  exit;
                     callEmailbegin:
                     $flag = 0;
                     unset($send_email_notitfication_history);
                     $send_email_notitfication_history = array();
                     if(count($triggerUsers) > 0){
                         if (count($users) > 0) {
                             foreach ($users as $key => $device) {
                                 if (in_array($device->external_user_id, $triggerUsers)) {
                                     //array_push($userIds, $device->external_user_id);

                                     if (isset($result->receiveCampaignType)) {
                                         $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                                         if ($result->receiveCampaignType == 1) {
                                             $i = $i + 1; // use for count user
                                             if ($i > $totalReceiveCampaignsUsers) {
                                                 $flag = 1; break;
                                             }
                                         } else if ($result->receiveCampaignType == 2) {
                                             $totalSendCampaignsUsers = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                                             if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                                 $flag = 1; break;
                                             }
                                         }
                                     }
                                     $next_notification_send_date = $next_notification_send_date;
                                     /*if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                       if (!empty($device->timezone)) {
                                           $timezone = $device->timezone;
                                           $timezone = explode(" ", $timezone);
                                           $hours = 0;
                                           $minutes = 0;
                                           if ($timezone[0] == "GMT") {
                                               $hours = substr($timezone[1], 0, 3);
                                               $minutes = substr($timezone[1], 3, 5);
                                           }
                                           date_default_timezone_set('GMT');
                                           $next_notification_send_date= date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                                       }
                                     }*/

                                     if(!empty($result->scheduleDelay_afterTime)){
                                         $next_notification_send_date = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$result->scheduleDelay_afterTime $result->scheduleDelay_afterTimeInterval"));
                                     }
                                     $send_email_notitfication_history[] = array(
                                         'email_id' => $device->email,
                                         'hurree_version' => '3.1',
                                         'userid' => $device->external_user_id,
                                         'campaignId' => $campaign_id,
                                         'app_group_apps_id' => $device->app_group_apps_id,
                                         'active_device_id' => $device->active_device_id,
                                         'from_email' => $fromAddress,
                                         'subject' => $push_title,
                                         'message' => $push_message,
                                         'emailSentOn' => $next_notification_send_date,
                                         'emailSentByUser' => $device->businessId,
                                         'groupid' => '',
                                         'opened' => 0,
                                         'openTime' => '',
                                         'active' => '1',
                                         'created_on' => date('Y-m-d H:i:s')
                                     );
                                 } // END IF
                                 $index[] = $key;
                             } // END FOREACH
                         } // END IF
                     }
                     if($flag == 0){ //print_r($send_email_notitfication_history); exit;
                         $offset = count($index); //echo $offset;
                         $limit  = 250;
                         if(count($send_email_notitfication_history) > 0){
                             $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                             updateCronTime('deliverCampaignsAtActionTrigger.txt');
                         }
                         if($offset > 0){
                             if (isset($segments)) {
                                 $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 1);
                             }
                             $filters = $result->filters;
                             if (!empty($filters)) {
                                 $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 1);
                             }
                             if(count($users) > 0){  //echo "<pre>"; echo "email"; print_r($users); exit;
                                 goto callEmailbegin;
                             }
                         }
                     }// END IF
                 }// END ELSE
             } // END FOREACH
         } // END IF
     }

    /*
     * function to make desired string according to sparkpost
     */
    function replaceSubjectSparkpost($subject)
    {
        $subject = str_replace('{{${date_of_birth}}}', '{{date_of_birth}}', $subject);
        $subject = str_replace('{{${company}}}', '{{company}}', $subject);
        $subject = str_replace('{{${email_address}}}', '{{email_address}}', $subject);
        $subject = str_replace('{{${first_name}}}', '{{first_name}}', $subject);
        $subject = str_replace('{{${last_name}}}', '{{last_name}}', $subject);
        $subject = str_replace('{{${gender}}}', '{{gender}}', $subject);
        $subject = str_replace('{{${last_used_app_date}}}', '{{last_used_app_date}}', $subject);
        $subject = str_replace('{{${most_recent_app_version}}}', '{{most_recent_app_version}}', $subject);
        $subject = str_replace('{{${phone_number}}}', '{{phone_number}}', $subject);
        $subject = str_replace('{{${time_zone}}}', '{{time_zone}}', $subject);
        $subject = str_replace('{{${username}}}', '{{username}}', $subject);
        $subject = str_replace('{{campaign.${name}}}', '{{campaign_name}}', $subject);
        $subject = str_replace('{{${set_user_to_unsubscribed_url}}}', '{{set_user_to_unsubscribed_url}}', $subject);
        $subject = str_replace('{{${set_user_to_subscribed_url}}}', '{{set_user_to_subscribed_url}}', $subject);
        return $subject;
    }

    function replaceMessageSparkpost($message)
    {
        $message = str_replace('{{${date_of_birth}}}', '{{date_of_birth}}', $message);
        $message = str_replace('{{${company}}}', '{{company}}', $message);
        $message = str_replace('{{${email_address}}}', '{{email_address}}', $message);
        $message = str_replace('{{${first_name}}}', '{{first_name}}', $message);
        $message = str_replace('{{${last_name}}}', '{{last_name}}', $message);
        $message = str_replace('{{${gender}}}', '{{gender}}', $message);
        $message = str_replace('{{${last_used_app_date}}}', '{{last_used_app_date}}', $message);
        $message = str_replace('{{${most_recent_app_version}}}', '{{most_recent_app_version}}', $message);
        $message = str_replace('{{${phone_number}}}', '{{phone_number}}', $message);
        $message = str_replace('{{${time_zone}}}', '{{time_zone}}', $message);
        $message = str_replace('{{${username}}}', '{{username}}', $message);
        $message = str_replace('{{campaign.${name}}}', '{{campaign_name}}', $message);
        $message = str_replace('{{${set_user_to_unsubscribed_url}}}', 'If you would prefer not receiving our emails, please <a href="' . base_url() . 'hurreeEmail/unsubscribe/{{base64email}}/{{bussinessId}}/{{groupId}}">click here</a> to unsubscribe.', $message);
        $message = str_replace('{{${set_user_to_subscribed_url}}}', '{{set_user_to_subscribed_url}}', $message);
        return $message;
    }


    /*
     * function is used to send email calling by cron jobs.
     */
    public function deliverEmailNotificationHistoryNEW()
    {
        // denied to direct url request from browser.
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '52.74.49.33')
            die('Permission denied.');

        // Load cron helper
        $this->load->helper('cron');
        // check is any parallel cron active.
        $isCronActive = isCronActive('deliverEmailNotificationHistory.txt',10);
        if($isCronActive == true) {
            return false;
        }

        // set the lock to prevent running cron in parallel
        updateCronTime('deliverEmailNotificationHistory.txt');

        // to check spark post status for daily and monthly limit.
        $sparkpostLastResponse = getSparkpostSendStatus();
        if(!$sparkpostLastResponse) {
            return false;
        }

        // define limit to send emails at single request to spark post.
        $limit = $this->config->item("SPARKPOST_SINGLE_REQUEST_LIMIT");

        // retrieve record to campaigns that are yet to be sent.
        $distinctCampaigns = $this->brand_model->getDistictCampaigns();
        if($distinctCampaigns == null)
        {
            return;
        }

        // send campaign emails
        foreach ($distinctCampaigns as $campaignRow)
        {
            if($campaignRow->total > 0) {

                // check if sparkpost can send emails
                $sparkpostLastResponse = getSparkpostSendStatus();
                if(!$sparkpostLastResponse) {
                    exit;
                }
                // variable indicate total email sent for this request.
                $totalEmailsent = 0;

                //total record in current campaign.
                $campaigntotalRecord = $campaignRow->total;

                // Get sparkpost key
                $sparkpostkey = null;
                $sparkpostkeyResult = $this->workflow_model->getUserSparkPostKey($campaignRow->campaignId);

                // if spark post key for this app campaign app group else to next itaration.
                if(isset($sparkpostkeyResult->sparkpost_key) && $sparkpostkeyResult->sparkpost_key != ''){
                    $sparkpostkey = $sparkpostkeyResult->sparkpost_key;
                } else {
                    continue;
                }
                //show this name on email sender by
                $senderemailname = isset($sparkpostkeyResult->displayName) && !empty($sparkpostkeyResult->displayName) ? $sparkpostkeyResult->displayName: '';

                // sending all emails for campaign.
                while ($totalEmailsent < $campaigntotalRecord) {

                    // check if sparkpost can send emails
                    $sparkpostLastResponse = getSparkpostSendStatus();
                    if(!$sparkpostLastResponse) {
                        exit;
                    }

                    // set the lock to prevent running cron in parallel
                    updateCronTime('deliverEmailNotificationHistory.txt');

                    // Get emails for sending
                    $resultset = $this->brand_model->getAllRecordToSentEmail($campaignRow->campaignId, $limit);

                    // random number uniquely identify of current limit records
                    $resultrandom = $resultset['random'];
                    $resultset = $resultset['result'];

                    if ($resultset != null)
                    {
                        $dummyArray = array();
                        // holds of record to send in spark post.

                        $emailtosparkpost = array();

                        // holds of record that is unsubscribed emails.
                        $emailunsubscribed = array();
                        $i = 0;
                        $userId = array();

                        //hold user ids that post record to stark post
                        $emailsenderrorUserIds = array();
                        // loop for each limit records to make record according to sparkpost.
                        // It also removes the unsubscribe email ids from the list
                        foreach ($resultset as $brandrow) {
                            updateCronTime('deliverEmailNotificationHistory.txt');
                            // check this email user in unsubscribe for current app_group_id if not present then add into recipient array else in email2 array.
                            $unsubscribeEmail = $this->brand_model->checkUnsubscribeEmail($brandrow->email_id, $brandrow->app_group_id);
                            if ($unsubscribeEmail == null) {
                                $totalEmailsent++;
                                $dummyArray[] = array('id' => $brandrow->brand_info_id, 'random'=>$resultrandom);

                                $emailsenderrorUserIds[] = array('id' => $brandrow->brand_info_id, 'is_send'=>5);

                                $userId[$i]['brandrowId'] = $brandrow->brand_info_id;
                                $userId[$i]['userid'] = $brandrow->userid;
                                $userId[$i]['emailSentOn'] = $brandrow->emailSentOn;
                                $userId[$i]['app_group_apps_id'] = $brandrow->app_group_id;
                                $userId[$i]['campaignName'] = $brandrow->campaignName;
                                $userId[$i]['active_device_id'] = $brandrow->active_device_id;

                                $subject = $brandrow->subject;
                                $from_email = isset($brandrow->from_email) && !empty($brandrow->from_email) ? $brandrow->from_email : 'hello@marketmyapp.co';
                                $replyToAddress = isset($brandrow->from_email) && !empty($brandrow->from_email) ? $brandrow->replyToAddress : 'hello@marketmyapp.co';

                                $date_of_birth = $brandrow->date_of_birth;
                                $email_address = $brandrow->email;
                                $first_name = $brandrow->firstName;
                                $last_name = $brandrow->lastName;
                                $gender = $brandrow->gender;
                                $phone_number = $brandrow->phoneNumber;
                                $time_zone = $brandrow->timezone;
                                $company = $brandrow->company;
                                $sdkVersion = $this->brand_model->getExternalUserActiveRow($brandrow->external_user_id);
                                if (count($sdkVersion) > 0) {
                                    $last_used_app_date = $sdkVersion->dateTime;
                                    $most_recent_app_version = $sdkVersion->sdk_version;
                                    $username = '';
                                } else {
                                    $last_used_app_date = '';
                                    $most_recent_app_version = '';
                                    $username = '';
                                }
                                $set_user_to_unsubscribed_url = 'Unsubscribed link: ' . base_url() . "hurreeEmail/unsubscribe/" . base64_encode($email_address) . "/" . $brandrow->businessId . "/" . $brandrow->app_group_id;
                                $set_user_to_subscribed_url = base_url() . "hurreeEmail/subscribe/" . base64_encode($email_address) . "/" . $brandrow->businessId . "/" . $brandrow->app_group_id;
                                $emailtosparkpost[$i]['to'] = array(array(
                                    'email' => $email_address,
                                    'name' => $first_name . ' ' . $last_name
                                ));

                                $recipents[$i]['address'] = array(
                                    'email' => $email_address,
                                    'name' => $first_name . ' ' . $last_name
                                );
                                $recipents[$i]['tags'] = array('learning');
                                $recipents[$i]['substitution_data'] = array(
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'email_address' => $email_address,
                                    'gender' => $gender,
                                    'date_of_birth' => $date_of_birth,
                                    'company' => $company,
                                    'last_used_app_date' => $last_used_app_date,
                                    'most_recent_app_version' => $most_recent_app_version,
                                    'phone_number' => $phone_number,
                                    'time_zone' => $time_zone,
                                    'username' => $username,
                                    'campaign_name' => $brandrow->campaignName,
                                    'set_user_to_unsubscribed_url' => $set_user_to_unsubscribed_url,
                                    'set_user_to_subscribed_url' => $set_user_to_subscribed_url,
                                    'base64email' => base64_encode($email_address),
                                    'bussinessId' => $brandrow->businessId,
                                    'groupId' => $brandrow->app_group_id


                                );
                                $i = $i + 1;
                            } else {
                                $emailunsubscribed[] = array('id'=>$brandrow->brand_info_id, 'is_send'=>3);
                            }
                        }

                        // mark email as unsubscribed
                        if (count($emailunsubscribed) > 0)
                        {
                            //update record of unsubscribe users.
                            $this->brand_model->batchUpdateUnsubscribedEmails($emailunsubscribed);
                        }

                        // send emails to sparkpost
                        if (count($emailtosparkpost) > 0)
                        {
                            // get subject and message
                            $subject = $this->replaceSubjectSparkpost($campaignRow->subject);
                            $message = $this->replaceMessageSparkpost($campaignRow->message);
                            $sendingmetadata = date("Y-m-d H:i:s")."-".$campaignRow->campaignId;
                            $text = array(
                                "options" => array(
                                    "open_tracking" => true,
                                    "click_tracking" => true
                                ),

                                "metadata" => array(
                                    "some_useful_metadata" => $sendingmetadata
                                ),

                                "substitution_data" => array(
                                    "signature" => ''
                                ),

                                "recipients" => $recipents,
                                "content" => array(
                                    "from" => array(
                                        "name" => $senderemailname,
                                        "email" => $from_email
                                    ),
                                    "subject" => $subject,
                                    "reply_to" => $replyToAddress,
                                    "text" => '',
                                    "html" => $message
                                )
                            );

                            // set the lock to prevent running cron in parallel
                            updateCronTime('deliverEmailNotificationHistory.txt');


                            $this->brand_model->batchInsert($dummyArray);

                            $jsonVar = json_encode($text);
                            $request['sparkpost_history_id'] = '';
                            $request['request'] = $jsonVar;
                            $request['response'] = '';
                            $request['createdDate'] = date('Y-m-d H:i:s');

                            // save record into table that is send to spark post.
                            $sparkPostHistoryId = $this->sparkposthistory_model->saveSparkPostHistory($request);

                            try
                            {
                                $this->curl = curl_init();

                                curl_setopt($this->curl, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions');
                                curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $sparkpostkey));
                                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
                                curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $jsonVar);
                                curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);

                                $response = curl_exec($this->curl);
                                $result = json_decode($response);
                                $updateSparkPostHistory['sparkpost_history_id'] = $sparkPostHistoryId;
                                $updateSparkPostHistory['response'] = $response;
                                $updateSparkPostHistory['modified_date'] = date('Y-m-d H:i:s');
                                $this->sparkposthistory_model->updateSparkPostHistory($updateSparkPostHistory);
                                if (isset($result->errors)) {
                                    $this->brand_model->updateEmailSentNotificationHistory($emailsenderrorUserIds);

                                    //check spark post status.
                                    $sparkpostLastResponse = getSparkpostSendStatus();
                                    if (!$sparkpostLastResponse) {
                                        exit;
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                } else {
                                    $sendgrid_message_id = $result->results->id;
                                    $emailsendsuccessUserIds = array();
                                    $send_event_history = array();
                                    // for update is_send flag of email sent users.
                                    foreach ($userId as $rowItem) {
                                        updateCronTime('deliverEmailNotificationHistory.txt');
                                        $emailsendsuccessUserIds[] = array('id' => $rowItem['brandrowId'], 'is_send' => 1, 'sendgrid_message_id' => $sendgrid_message_id);
                                        $send_event_history[] = array(
                                            'external_user_id' => $rowItem['userid'],
                                            'app_group_apps_id' => isset($rowItem['app_group_apps_id']) ? $rowItem['app_group_apps_id'] : "0",
                                            'active_device_id' => isset($rowItem['active_device_id']) ? $rowItem['active_device_id'] : "0",
                                            'screenName' => 'send ' . ucfirst($rowItem['campaignName']),
                                            'eventName' => 'send ' . ucfirst($rowItem['campaignName']),
                                            'eventDate' => $rowItem['emailSentOn'],
                                            'eventType' => 'campaignNotification',
                                            'isExportHubspot' => 0,
                                            'isDelete' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );
                                    }
                                    $this->brand_model->updateEmailSentNotificationHistory($emailsendsuccessUserIds);
                                    if (count($send_event_history))
                                        $this->brand_model->saveEventsHistory($send_event_history);

                                }
                            }
                            catch(Exception $e)
                            {
                                $updateSparkPostHistory['sparkpost_history_id'] = $sparkPostHistoryId;
                                $updateSparkPostHistory['response'] = $e->getMessage();
                                $updateSparkPostHistory['modified_date'] = date('Y-m-d H:i:s');
                                $this->sparkposthistory_model->updateSparkPostHistory($updateSparkPostHistory);
                                $this->brand_model->updateEmailSentNotificationHistory($emailsenderrorUserIds);
                            }
                        }

                    }
                }
            }
        }

    }

    public function deliverEmailNotificationHistory() {
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '52.74.49.33') die('Permission denied.');
        $this->load->helper('cron');

        $isCronActive = isCronActive('deliverEmailNotificationHistory.txt',10);
        if($isCronActive == true){
            return false;
        }
        updateCronTime('deliverEmailNotificationHistory.txt');

        /*ture means email can be sent, false means email can not be sent*/
        $sparkpostLastResponse = getSparkpostSendStatus();
        if(!$sparkpostLastResponse){
            return false;
        }

        $campaignIds = $this->brand_model->getCampaignIdFromBrandEmailCampaignInfo();
        //echo '<pre>';
        //print_r($campaignIds); die;
        if (count($campaignIds) > 0) {
            foreach ($campaignIds as $campId) {

                $appGroupKey = $this->workflow_model->getUserSparkPostKey($campId->campaignId);
                if($appGroupKey->sparkpost_key != ''){
                    $key = $appGroupKey->sparkpost_key;
                }else{
                    return false;
                }

                checkMoreEmaiOfSameCampaign:
                $emails = $this->brand_model->getEmailsForCampaign($campId->campaignId);

                updateCronTime('deliverEmailNotificationHistory.txt');

                //echo '<pre>';
                //print_r($emails); die;
                if (count($emails) > 0) {
                    $email1 = array();
                    $email2 = array();
                    $i=0;
                    $notification_id = array();
                    $userId = array();
                    foreach($emails as $email){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($campId->campaignId);

                        $unsubscribeEmail = $this->brand_model->checkUnsubscribeEmail($email->email_id,$rowBusinessId->app_group_id);

                        if(count($unsubscribeEmail)<= 0){

                            $notification_id[$i] = $email->id;
                            $userId[$i] = $email->userid;
                            $subject = $email->subject;
                            if ($email->from_email != '') {
                                $from_email = $email->from_email;
                            } else {
                                $from_email = 'hello@marketmyapp.co';
                            }
                            if($email->replyToAddress != ''){
                                $replyToAddress = $email->replyToAddress;
                            }else{
                                $replyToAddress = 'hello@marketmyapp.co';
                            }
                            $userRow = $this->brand_model->getExternalUserByEmailId($email->userid);

                            if (count($userRow) > 0) {
                                $date_of_birth = $userRow->date_of_birth;
                                $email_address = $userRow->email;
                                $first_name = $userRow->firstName;
                                $last_name = $userRow->lastName;
                                $gender = $userRow->gender;
                                $phone_number = $userRow->phoneNumber;
                                $time_zone = $userRow->timezone;
                                $company = $userRow->company;
                                $userRows = $this->brand_model->getExternalUserActiveRow($userRow->external_user_id);
                                //echo count($userRows); die;
                                if (count($userRows) > 0) {
                                    $last_used_app_date = $userRows->dateTime;
                                    $most_recent_app_version = $userRows->sdk_version;
                                    $username = '';
                                } else {
                                    $last_used_app_date = '';
                                    $most_recent_app_version = '';
                                    $username = '';
                                }
                            } else {
                                $date_of_birth = '';
                                $email_address = '';
                                $first_name = '';
                                $last_name = '';
                                $gender = '';
                                $phone_number = '';
                                $time_zone = '';
                                $username = '';
                                $last_used_app_date = '';
                                $most_recent_app_version = '';
                            }

                            $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($campId->campaignId);

                            $set_user_to_unsubscribed_url = 'Unsubscribed link: '.base_url() . "hurreeEmail/unsubscribe/" . base64_encode($email_address)."/".$rowBusinessId->businessId."/".$rowBusinessId->app_group_id;
                            $set_user_to_subscribed_url = base_url() . "hurreeEmail/subscribe/" . base64_encode($email_address)."/".$rowBusinessId->businessId."/".$rowBusinessId->app_group_id;

                            $email1[$i]['to'] = array(array(
                                'email' => $email_address,
                                'name' => $first_name . ' ' . $last_name
                            ));

                            $recipents[$i]['address'] = array(
                                'email' => $email_address
                                //'name' => $first_name . ' ' . $last_name
                            );
                            $recipents[$i]['tags'] = array('learning');
                            $recipents[$i]['substitution_data'] = array(
                                'first_name' => $first_name,
                                'last_name' => $last_name,
                                'email_address' => $email_address,
                                'gender' => $gender,
                                'date_of_birth' => $date_of_birth,
                                'company' => $company,
                                'last_used_app_date' => $last_used_app_date,
                                'most_recent_app_version' => $most_recent_app_version,
                                'phone_number' => $phone_number,
                                'time_zone' => $time_zone,
                                'username' => $username,
                                'campaign_name' => $email->campaignName,
                                'set_user_to_unsubscribed_url' => $set_user_to_unsubscribed_url,
                                'set_user_to_subscribed_url' => $set_user_to_subscribed_url,
                                'base64email' => base64_encode($email_address),
                                'bussinessId' => $rowBusinessId->businessId,
                                'groupId' => $rowBusinessId->app_group_id


                            );


                            $email1[$i]['subject'] = $subject;

                            $i = $i + 1;
                        }else{
                            $email2[] = $email->id;
                        }
                    }

                    if(count($email2) > 0){ //print_r($email2); exit;
                        foreach ($email2 as $id) {
                            $update = array('is_send' => 2, 'id' => $id);
                            $this->brand_model->sendEmailNotificationHistory($update);
                        }
                    }
                    if(count($email1) > 0){

                        //print_r($recipents); die;
                        //echo json_encode($arr);die;


                        $recipents1 = json_encode($recipents);
                        //echo $recipents1;
                        $subject = $email->subject;
                        $subject = str_replace('{{${date_of_birth}}}', '{{date_of_birth}}', $subject);
                        $subject = str_replace('{{${company}}}', '{{company}}', $subject);
                        $subject = str_replace('{{${email_address}}}', '{{email_address}}', $subject);
                        $subject = str_replace('{{${first_name}}}', '{{first_name}}', $subject);
                        $subject = str_replace('{{${last_name}}}', '{{last_name}}', $subject);
                        $subject = str_replace('{{${gender}}}', '{{gender}}', $subject);
                        $subject = str_replace('{{${last_used_app_date}}}', '{{last_used_app_date}}', $subject);
                        $subject = str_replace('{{${most_recent_app_version}}}', '{{most_recent_app_version}}', $subject);
                        $subject = str_replace('{{${phone_number}}}', '{{phone_number}}', $subject);
                        $subject = str_replace('{{${time_zone}}}', '{{time_zone}}', $subject);
                        $subject = str_replace('{{${username}}}', '{{username}}', $subject);
                        $subject = str_replace('{{campaign.${name}}}', '{{campaign_name}}', $subject);
                        $subject = str_replace('{{${set_user_to_unsubscribed_url}}}', '{{set_user_to_unsubscribed_url}}', $subject);
                        $subject = str_replace('{{${set_user_to_subscribed_url}}}', '{{set_user_to_subscribed_url}}', $subject);

                        $message = $email->message;
                        $message = str_replace('{{${date_of_birth}}}', '{{date_of_birth}}', $message);
                        $message = str_replace('{{${company}}}', '{{company}}', $message);
                        $message = str_replace('{{${email_address}}}', '{{email_address}}', $message);
                        $message = str_replace('{{${first_name}}}', '{{first_name}}', $message);
                        $message = str_replace('{{${last_name}}}', '{{last_name}}', $message);
                        $message = str_replace('{{${gender}}}', '{{gender}}', $message);
                        $message = str_replace('{{${last_used_app_date}}}', '{{last_used_app_date}}', $message);
                        $message = str_replace('{{${most_recent_app_version}}}', '{{most_recent_app_version}}', $message);
                        $message = str_replace('{{${phone_number}}}', '{{phone_number}}', $message);
                        $message = str_replace('{{${time_zone}}}', '{{time_zone}}', $message);
                        $message = str_replace('{{${username}}}', '{{username}}', $message);
                        $message = str_replace('{{campaign.${name}}}', '{{campaign_name}}', $message);
                        $message = str_replace('{{${set_user_to_unsubscribed_url}}}', 'If you would prefer not receiving our emails, please <a href="'.base_url().'hurreeEmail/unsubscribe/{{base64email}}/{{bussinessId}}/{{groupId}}">click here</a> to unsubscribe.', $message);
                        $message = str_replace('{{${set_user_to_subscribed_url}}}', '{{set_user_to_subscribed_url}}', $message);



                        $text = array(
                            "options" => array(
                                "open_tracking" => true,
                                "click_tracking" =>true
                            ),

                            "metadata" => array(
                                "some_useful_metadata" => 'testing_sparkpost'
                            ),

                            "substitution_data" => array(
                                "signature" => ''
                            ),

                            "recipients" => $recipents,
                            "content" => array(
                                "from" => array(
                                    "name" => 'Hurree',
                                    "email" => $from_email
                                ),
                                "subject" => $subject,
                                "reply_to" => $replyToAddress,
                                "text" => '',
                                "html" => $message
                            )

                        );
                        $jsonVar = json_encode($text);
//echo $jsonVar; die;
                        $request['sparkpost_history_id'] = '';
                        $request['request'] = $jsonVar;
                        $request['response'] = '';
                        $request['createdDate'] = date('Y-m-d H:i:s');
                        $sparkPostHistoryId = $this->sparkposthistory_model->saveSparkPostHistory($request);


                        $this->curl = curl_init();

                        curl_setopt($this->curl,CURLOPT_URL,'https://api.sparkpost.com/api/v1/transmissions');
                        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: '.$key));
                        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($this->curl, CURLOPT_POSTFIELDS,$jsonVar);
                        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
                        //$result = json_decode(curl_exec($this->curl));
                        $response = curl_exec($this->curl);
                        $result = json_decode($response);
                        $updateSparkPostHistory['sparkpost_history_id'] = $sparkPostHistoryId;
                        $updateSparkPostHistory['response'] = $response;
                        $updateSparkPostHistory['modified_date'] = date('Y-m-d H:i:s');
                        $this->sparkposthistory_model->updateSparkPostHistory($updateSparkPostHistory);
                        //print_r($result);
                        // $result = 'stdClass Object ( [results] => stdClass Object ( [total_rejected_recipients] => 0 [total_accepted_recipients] => 3 [id] => 66530138069441362 ) )';
                        //echo $result->results->id; die;
                        if(isset($result->errors)){
                            //echo $result->errors[0]->message;
                            return false;
                        }else{
                            $sendgrid_message_id = $result->results->id;

                            foreach ($notification_id as $id) {
                                $update = array('is_send' => 1, 'id' => $id, 'sendgrid_message_id' => $sendgrid_message_id);
                                $this->brand_model->sendEmailNotificationHistory($update);
                            }
                            foreach ($userId as $user_id) {
                                $eventRow = $this->brand_model->getEventRowByTimezoneTime($email->emailSentOn, $user_id, $email->app_group_apps_id);

                                //if (count($eventRow) < 1) {
                                $send_event_history = array(
                                    'external_user_id' => $user_id,
                                    'app_group_apps_id' => isset($email->app_group_apps_id) ? $email->app_group_apps_id : "0",
                                    'active_device_id' => isset($email->active_device_id) ? $email->active_device_id : "0",
                                    'screenName' => 'send ' . ucfirst($email->campaignName),
                                    'eventName' => 'send ' . ucfirst($email->campaignName),
                                    'eventDate' => $email->emailSentOn,
                                    'eventType' => 'campaignNotification',
                                    'isExportHubspot' => 0,
                                    'isDelete' => 0,
                                    'createdDate' => date('Y-m-d H:i:s')
                                );
                                $this->brand_model->saveEventsHistory($send_event_history);
                                //}
                            }
                        }

                    }

                    $checkEmails = $this->brand_model->getEmailsForCampaign($campId->campaignId);
                    if(count($checkEmails) > 0){
                        updateCronTime('deliverEmailNotificationHistory.txt');
                        $sparkpostLastResponse = getSparkpostSendStatus();
                        if(!$sparkpostLastResponse){
                            return false;
                        }

                        goto checkMoreEmaiOfSameCampaign;
                    }
                }

                updateCronTime('deliverEmailNotificationHistory.txt');
            }
        }
    }


    function getWorkflow($workflowId,$businessId){
        if(!empty($workflowId)){
            $totalSendUsers = 0;
            $workflowRow = $this->workflow_model->getWorkflowRow($workflowId);

            $data = array();
            $dataWorkflow = array();
            $workflow = json_decode($workflowRow['wrkflow_triggerpoint_json'],true); //echo "<pre>";print_r($workflow); exit;
            if (!empty($workflow)) {
                $andOr = "";
                $app_groups = $this->brand_model->getAllAppGroupsByBusinessId($businessId);
                if (!empty($workflow['firstName'])) {
                    $dataWorkflow['firstName'] = $workflow['firstName'];
                }
                if (!empty($workflow['lastName'])) {
                    $dataWorkflow['lastName'] = $workflow['lastName'];
                }
                if (!empty($workflow['timezone'])) {
                    $dataWorkflow['timezone'] = $workflow['timezone'];
                }
                if (!empty($workflow['createdDate'])) {
                    $dataWorkflow['createdDate'] = $workflow['createdDate'];
                }
                if (!empty($workflow['original_source'])) {
                    $dataWorkflow['original_source'] = $workflow['original_source'];
                }
                if (!empty($dataWorkflow)) {
                    $users = $this->workflow_model->getWorkflowTriggersExternalUsers($dataWorkflow, $app_groups);
                }
                if (empty($users[0]->external_user_id)) {
                    $users = array();
                }

                if (!empty($workflow['interection_open_push'])) {
                    $interCamUsers = $this->workflow_model->getPushNotificationsNewOpenedUsers($workflow['interection_open_push'],$businessId);
                    while(key($workflow) !== "interection_open_push") next($workflow);
                    $prevArray = prev($workflow);
                    if (count($interCamUsers) > 0) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) > 0 && !empty ($users)) { //echo "called2"; exit;
                            @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);

                            }
                        }  else {
                            @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                }

                if (!empty($workflow['interection_open_email'])) {
                    $interCamUsers = $this->workflow_model->getEmailInteractionNewOpenedUsers($workflow['interection_open_email'],$businessId); //print_r($interCamUsers); exit;
                    while(key($workflow) !== "interection_open_email") next($workflow);
                    $prevArray = prev($workflow);
                    if (count($interCamUsers) > 0) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else { //echo "clled"; exit;
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) > 0 && !empty ($users)) { //echo "called2"; exit;
                            @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else {
                            @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                }  //print_r($users); exit;  //print_r(explode(",", $users[0]->external_user_id)); die;

                if (!empty($workflow['persona'])) {
                    $personaUsers = $this->workflow_model->getPersonaNewUsers($workflow['persona'], $app_groups); //echo $personaUsers->external_user_id; print_r(  $personaUsers);exit;
                    while(key($workflow) !== "persona") next($workflow);
                    $prevArray = prev($workflow);
                    if (count($personaUsers) > 0) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {  // echo "called"; exit;
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $personaUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) > 0 && !empty ($users)) { //echo "called2"; exit;
                            @$temp2 = explode(',', $personaUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else {
                            @$temp2 = explode(',', $personaUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                }

                if (!empty($workflow['list'])) {
                    $externalUserIdsArray = array();
                    $query = '';
                    $includeArray = array();
                    $excludeArray = array();
                    $externalUserIdsArray1  = array();
                    $data = $workflow['list'];
                    while(key($workflow) !== "list") next($workflow);
                    $prevArray = prev($workflow); //print_r($prevArray); exit;
                    if (count($data) > 0) {
                        foreach ($data['data'] as $key => $row) {
                            $row['option'] == 'include' ? $includeArray[] = $row['id'] : $excludeArray[] = $row['id'];
                        }
                    }
                    if(count($includeArray) > 0 && count($excludeArray) == 0){
                        $lists = $includeArray;
                        foreach ($lists as $list) {
                            $listContacts = getContactsByListId($list, $businessId);
                            $externalUserIdsArray = array_merge($externalUserIdsArray, explode(',', $listContacts));
                        }
                        $listUsers = array_flip($externalUserIdsArray); //print_r($listUsers);
                        $listUsers = array_flip($listUsers); //print_r($listUsers);
                    }else if(count($excludeArray) > 0 && count($includeArray) == 0){
                        $lists1 = $excludeArray;
                        foreach ($lists1 as $list) {
                            $listContacts = getContactsByListId($list, $businessId);
                            $externalUserIdsArray1 = array_merge($externalUserIdsArray1, explode(',', $listContacts));
                        }
                        $listUsers1= array_flip($externalUserIdsArray1);
                        $listUsers1= array_flip($listUsers1);

                        $allUsers = $this->workflow_model->getAllActiveUsers($app_groups);
                        @$temp1 = explode(',', $allUsers[0]->external_user_id);
                        $listUsers = array_diff($temp1, $listUsers1);
                        //echo count($listUsers);  exit; //print_r($listUsers); exit;
                    }else{
                        $lists = $includeArray;
                        foreach ($lists as $list) {
                            $listContacts = getContactsByListId($list, $businessId);
                            $externalUserIdsArray = array_merge($externalUserIdsArray, explode(',', $listContacts));
                        }
                        $listUsers = array_flip($externalUserIdsArray);
                        $listUsers = array_flip($listUsers);
                        $lists1 = $excludeArray;
                        foreach ($lists1 as $list) {
                            $listContacts = getContactsByListId($list, $businessId);
                            $externalUserIdsArray1 = array_merge($externalUserIdsArray1, explode(',', $listContacts));
                        }
                        $listUsers1= array_flip($externalUserIdsArray1);
                        $listUsers1= array_flip($listUsers1);

                        $allUsers = $this->workflow_model->getAllActiveUsers($app_groups);
                        @$temp1 = explode(',', $allUsers[0]->external_user_id);
                        $listUsers1 = array_diff($temp1, $listUsers1);
                        $listUsers= array_flip(array_merge($listUsers,$listUsers1)); //print_r($listUsers); exit;
                        $listUsers = array_flip($listUsers);
                    }
                    if (isset($listUsers)) {
                        @$temp1 = array(); // echo "djhgfhf".count($listUsers); exit;
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = $listUsers;
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >0 && !empty ($users)) { //echo "bdjhgfhf".count($listUsers); exit;
                            //@$temp2 = explode(',', $listUsers);bb
                            // print_r($prevArray);
                            if (isset($prevArray['opt']) && strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, $listUsers); // echo "called2".$prevArray['opt']; print_r($users); echo count($listUsers); exit;
                            } else {
                                $users = array_flip(array_merge($users, $listUsers));
                                $users = array_flip($users);
                            }
                        } else {  //echo "aaadjhgfhf".count($listUsers); exit;
                            @$temp2 = $listUsers;
                            $users = $temp2;
                        }
                    }
                }

                $emailCampaign = $emailUsers = array(); $emailExists = "";
                if (!empty($workflow['received_email'])) {
                    $emailExists = 1;
                    $emailCampaign['received_email'] = $workflow['received_email'];
                    while(key($workflow) !== "received_email") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['opened_email'])) {
                    $emailExists = 1;
                    $emailCampaign['opened_email'] = $workflow['opened_email'];
                    while(key($workflow) !== "opened_email") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['clicked_email'])) {
                    $emailExists = 1;
                    $emailCampaign['clicked_email'] = $workflow['clicked_email'];
                    while(key($workflow) !== "clicked_email") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['unsubscribed_email'])) {
                    $emailExists = 1;
                    $emailCampaign['unsubscribed_email'] = $workflow['unsubscribed_email'];
                    while(key($workflow) !== "unsubscribed_email") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['bounced_email'])) {
                    $emailExists = 1;
                    $emailCampaign['bounced_email'] = $workflow['bounced_email'];
                    while(key($workflow) !== "bounced_email") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['email_not_received'])) {
                    $emailExists = 1;
                    $emailCampaign['email_not_received'] = $workflow['email_not_received'];
                    while(key($workflow) !== "email_not_received") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['email_not_opened'])) {
                    $emailExists = 1;
                    $emailCampaign['email_not_opened'] = $workflow['email_not_opened'];
                    while(key($workflow) !== "email_not_opened") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['email_not_clicked'])) {
                    $emailExists = 1;
                    $emailCampaign['email_not_clicked'] = $workflow['email_not_clicked'];
                    while(key($workflow) !== "email_not_clicked") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                if(!empty($workflow['email_opened_date'])) {
                    $emailExists = 1;
                    $emailCampaign['email_opened_date'] = $workflow['email_opened_date'];
                    while(key($workflow) !== "email_opened_date") next($workflow);
                    $prevArray = prev($workflow);
                    $emailUsers = $this->workflow_model->getEmailTriggersUsers($emailCampaign,$businessId);
                    if (isset($emailUsers)) {
                        @$temp1 = array();
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($temp1, $temp2);
                            } else {
                                $users = array_flip(array_merge($temp1, $temp2));
                                $users = array_flip($users);
                            }
                        } else if (count($users) >= 0 && $emailExists = 1) {
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            if (strcmp($prevArray['opt'], 'AND') == 0) {
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_flip(array_merge($users, @$temp2));
                                $users = array_flip($users);
                            }
                        } else { //echo "called2"; exit;
                            @$temp2 = explode(',', $emailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    unset($emailCampaign);
                }

                $apps = array();
                $platformIds = '';
                $platformApps = array();//print_r($app_groups); exit;
                if(count($app_groups) > 0){
                    $app_groups = $app_groups[0]->app_group_id;
                    $platformIds = $this->groupapp_model->getAllAppsByGroupIds($app_groups);
                    if(count($platformIds) > 0){
                        foreach ($platformIds as $platformId) {
                            array_push($platformApps, $platformId->app_group_apps_id);
                        }
                    }
                    if(!empty($workflow['app_page_viewed'])){
                        $apps['app_page_viewed'] = $workflow['app_page_viewed']; //echo "cakked"; exit;
                        $pageViewedUsers = $this->workflow_model->getAppPageViewedTriggersUsers($apps,$platformApps);
                        while(key($workflow) !== "app_page_viewed") next($workflow);
                        $prevArray = prev($workflow);
                        if (isset($pageViewedUsers)) {
                            @$temp1 = array();
                            if (!empty($users[0]->external_user_id)) {
                                @$temp1 = explode(',', $users[0]->external_user_id);
                                @$temp2 = explode(',', $pageViewedUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($temp1, $temp2);
                                } else {
                                    $users = array_flip(array_merge($temp1, $temp2));
                                    $users = array_flip($users);
                                }
                            } else if (count($users) > 0) {
                                @$temp2 = explode(',', $pageViewedUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_flip(array_merge($users, @$temp2));
                                    $users = array_flip($users);
                                }
                            } else { //echo "called12"; print_r($pageViewedUsers); exit;
                                @$temp2 = explode(',', $pageViewedUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                        }
                    }

                    if(!empty($workflow['app_last_opened'])){
                        $apps['app_last_opened'] = $workflow['app_last_opened'];
                        $appOpenedUsers = $this->workflow_model->getAppOpenedDateTriggersUsers($apps,$platformApps);
                        while(key($workflow) !== "app_last_opened") next($workflow);
                        $prevArray = prev($workflow);
                        if (isset($appOpenedUsers)) {
                            @$temp1 = array();
                            if (!empty($users[0]->external_user_id)) {
                                @$temp1 = explode(',', $users[0]->external_user_id);
                                @$temp2 = explode(',', $appOpenedUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($temp1, $temp2);
                                } else {
                                    $users = array_flip(array_merge($temp1, $temp2));
                                    $users = array_flip($users);
                                }
                            } else if (count($users) > 0) {
                                @$temp2 = explode(',', $appOpenedUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_flip(array_merge($users, @$temp2));
                                    $users = array_flip($users);
                                }
                            } else { //echo "called2"; exit;
                                @$temp2 = explode(',', $appOpenedUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                        }
                    }

                    if(!empty($workflow['app_visit_number'])){
                        $apps['app_visit_number'] = $workflow['app_visit_number'];
                        $appVisitUsers = $this->workflow_model->getAppVisitTriggersUsers($apps,$platformApps);
                        while(key($workflow) !== "app_visit_number") next($workflow);
                        $prevArray = prev($workflow);
                        if (isset($appVisitUsers)) {
                            @$temp1 = array();
                            if (!empty($users[0]->external_user_id)) {
                                @$temp1 = explode(',', $users[0]->external_user_id);
                                @$temp2 = explode(',', $appVisitUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($temp1, $temp2);
                                } else {
                                    $users = array_flip(array_merge($temp1, $temp2));
                                    $users = array_flip($users);
                                }
                            } else if (count($users) > 0) {
                                @$temp2 = explode(',', $appVisitUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_flip(array_merge($users, @$temp2));
                                    $users = array_flip($users);
                                }
                            } else { //echo "called12"; print_r($pageViewedUsers); exit;
                                @$temp2 = explode(',', $appVisitUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                        }
                    }

                    if(!empty($workflow['app_last_seen'])){
                        $apps['app_last_seen'] = $workflow['app_last_seen'];
                        $appLastSeenUsers = $this->workflow_model->getAppLastSeenTriggerUsers($apps,$platformApps);
                        while(key($workflow) !== "app_last_seen") next($workflow);
                        $prevArray = prev($workflow);
                        if (isset($appLastSeenUsers)) {
                            @$temp1 = array();
                            if (!empty($users[0]->external_user_id)) {
                                @$temp1 = explode(',', $users[0]->external_user_id);
                                @$temp2 = explode(',', $appLastSeenUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($temp1, $temp2);
                                } else {
                                    $users = array_flip(array_merge($temp1, $temp2));
                                    $users = array_flip($users);
                                }
                            } else if (count($users) > 0) {
                                @$temp2 = explode(',', $appLastSeenUsers[0]->external_user_id);
                                if (strcmp($prevArray['opt'], 'AND') == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_flip(array_merge($users, @$temp2));
                                    $users = array_flip($users);
                                }
                            } else { //echo "called12"; print_r($pageViewedUsers); exit;
                                @$temp2 = explode(',', $appLastSeenUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                        }
                    }
                }

                $result = $users;
                //$result = array_unique($result, SORT_REGULAR);
                //  print_r($result); exit; //echo "<pre>"; print_r($result); exit;//($users); print_r($listUsers); exit;

                $newUsers = '';
                if (count($result) > 0) {
                    $newArray = array();
                    foreach ($result as $row) {
                        if (!empty($row->external_user_id)) {
                            $newArray[] = $row->external_user_id;
                            $newUsers .= "$row->external_user_id" . ',';
                        } else {
                            $newArray[] = $row;
                            $newUsers .= "$row" . ',';
                        }
                    }
                    $newUsers = rtrim($newUsers, ',');
                }
                return $newUsers;
            } // END For
        }
    }

    /*
     * this function used to update current new user calling from cron job
     */

    function getNewWorkflowUsers()
    {
        $result = $this->workflow_model->getNewWorkflowUsers(); //print_r($result); exit;
        if($result != null)
        {
            foreach ($result as $row)
            {
                $wrkflow_id =  $row->wrkflow_id;
                $businessId =  $row->wrkflow_businessID; //echo $wrkflow_id.' '.$businessId; exit;
                $external_userid_text = $this->getWorkflow($wrkflow_id,$businessId);
                if($external_userid_text == '')
                    continue;
                $extUsers = explode(",",$external_userid_text);
                $where['wrkflow_id'] = $wrkflow_id;
                $existEntry = $this->workflow_model->getWorkflowHistoryUsers($where);
                if(count($existEntry)>0){
                    $str = '';
                    foreach($existEntry as $entry){

                        $str .= $entry -> external_userid_text . ',';

                    }
                    $finalArray = explode(",",$str);
                    $result_array = array_diff($extUsers, $finalArray);
                    //print_r($result_array); die;
                    if(count($result_array)>0){
                        $str1 = implode(",",$result_array);
                        //echo $str1;
                        $insert['wrkflow_id'] = $wrkflow_id;
                        $insert['external_userid_text'] = $str1;
                        $insert['isDraft'] = 0;
                        $insert['isActive'] = 1;
                        $insert['isDelete'] = 0;
                        $insert['createdDate'] = date('Y-m-d H:i:s');

                        $wrkflow_historyId = $this->workflow_model->saveWorkflowHistory($insert);
                    }
                    else
                    {
                        $wrkflow_historyId = $existEntry[0]->wrkflow_historyId;
                    }

                }
                else{
                    $insert['wrkflow_id'] = $wrkflow_id;
                    $insert['external_userid_text'] = $external_userid_text;
                    $insert['isDraft'] = 0;
                    $insert['isActive'] = 1;
                    $insert['isDelete'] = 0;
                    $insert['createdDate'] = date('Y-m-d H:i:s');

                    $wrkflow_historyId = $this->workflow_model->saveWorkflowHistory($insert);
                }

                $result = $this->workflow_model->isTimeDelay($wrkflow_historyId);
                if ($result == null)
                {
                    $timedelays = $this->workflow_model->getTimeDelays($wrkflow_id);
                    $dateStart = date('Y-m-d');
                    foreach($timedelays as $timedelay) {

                        $insert1['wrkflow_id'] = $wrkflow_id;
                        $insert1['wrkflow_historyId'] = $wrkflow_historyId;
                        $insert1['wrkflow_delay_id'] = $timedelay->timeDelay_workflow_id;
                        $insert1['wrkflow_notificationType'] = $timedelay->wrkflow_notificationType;
                        if ($timedelay->wrkflow_TimeDelayType == 'Designated-Time') {
                            $date = date('Y-m-d H:i:s');

                            $minutes = $timedelay->wrkflow_designatedMinutes;
                            $hours = $timedelay->wrkflow_designatedHours;
                            $days = $timedelay->wrkflow_designatedDays;

                            $sendtime = explode(' ', $timedelay->wrkflow_send_date);
                            $sendtime[1];

                            $current = date('Y-m-d', strtotime($dateStart . " + $days days"));
                            $dateStart = $current;
                            $newDate = explode(' ', $current);
                            $newDate[0];
                            $type = 'DT';
                            $religibleDays = '';
                            $time_between = '';


                        } else {
                            $date = date('Y-m-d H:i:s');

                            $minutes = $timedelay->wrkflow_intelligentDeliveryMinutes;
                            $hours = $timedelay->wrkflow_intelligentDeliveryHours;
                            $days = $timedelay->wrkflow_intelligentDeliveryDays;

                            $sendtime = explode(' ', $timedelay->wrkflow_send_date);
                            $sendtime[1];

                            $current = date('Y-m-d', strtotime($dateStart . " + $days days"));
                            $dateStart = $current;
                            $newDate = explode(' ', $current);
                            $newDate[0];
                            $type = 'ID';
                            $religibleDays = '';
                            if($timedelay->wrkflow_intelligentDeliveryReEligible == 1){
                                if($timedelay->wrkflow_ReEligibleDate != '' && $timedelay->wrkflow_ReEligibleDate != 'undefined'){
                                    $religibleDays = $timedelay->wrkflow_ReEligibleDate." ".$timedelay->wrkflow_ReEligibleDays;
                                }else{
                                    $religibleDays = '';
                                }
                            }else{
                                $religibleDays = '';
                            }

                            if($timedelay->wrkflow_intelligentDeliveryBetweenTime == 1){
                                if($timedelay->wrkflow_intelligentDeliveryfrMinutes != ''){
                                    $time_between = $timedelay->wrkflow_intelligentDeliveryfrMinutes."-".$timedelay->wrkflow_intelligentDeliverytoMinutes;
                                }else{
                                    $time_between = '';
                                }
                            }else{
                                $time_between = '';
                            }

                        }
                        $insert1['wrkflow_time'] = $newDate[0] . " " . $sendtime[1];
                        $insert1['type'] = $type;
                        $insert1['days'] = $religibleDays;
                        $insert1['time_between'] = $time_between;
                        $insert1['isDraft'] = 0;
                        $insert1['isActive'] = 1;
                        $insert1['isDelete'] = 0;
                        $insert1['createdDate'] = date('Y-m-d H:i:s');
                        $insert1['modifiedDate'] = date('Y-m-d H:i:s');

                        $this->workflow_model->saveWorkflowHistoryTime($insert1);

                    }
                }

            }
        }
    }


    public function getWorkflowLists() {
        $chunkSize = 500;
        //$startMemory = memory_get_usage();
        //$array = range(1, 1000000);
        //echo memory_get_usage() - $startMemory, ' bytes'; die;
        $workflows = $this->workflow_model->getAllDeliverWorkflows();
        $users = array();
        $interCamUsers = array();
        $personaUsers = array();
        $listUsers = array();
        $receiverEmailUsers = array();
        $openedEmailUsers = array();
        $clickedEmailUsers = array();
        $unsubscribedEmailUsers = array();
        $bouncedEmailUsers = array();
        $notReceivedEmailUsers = array();
        $notOpenedEmailUsers = array();
        $notClickedEmailUsers = array();
        $lastOpenDateUsers = array();
        $pageViewedUsers = array();
        $lastOpenAppUsers = array();
        $NumOfVisitUsers = array();
        $lastPageSeenUsers = array();
        $totalSendUsers = 0;
        $notification_alert_count = 0;
        //echo "<pre>"; print_r($workflows); exit;
        if (count($workflows) > 0) {
            foreach ($workflows as $workflow) {
                $andOr = "";
                $app_groups = $this->brand_model->getAllAppGroupsByBusinessId($workflow->businessId);
                if (!empty($workflow->wrkflow_fname)) {
                    $andOr = $workflow->wrkflow_fnameAndOr;
                    $append = '';
                    $names = explode(',', $workflow->wrkflow_fname);
                    foreach ($names as $name) {
                        $append .= "'$name'" . ',';
                    } $append = rtrim($append, ',');
                    $data['firstName'] = array($append, $workflow->wrkflow_fnameInEx, $workflow->wrkflow_fnameAndOr);
                }
                if (!empty($workflow->wrkflow_lname)) {
                    $andOr = $workflow->wrkflow_lnameAndOr;
                    $append = '';
                    $names = explode(',', $workflow->wrkflow_lname);
                    foreach ($names as $name) {
                        $append .= "'$name'" . ',';
                    } $append = rtrim($append, ',');
                    $data['lastName'] = array($append, $workflow->wrkflow_lnameInEx, $workflow->wrkflow_lnameAndOr);
                }
                if (!empty($workflow->wrkflow_timezone)) {
                    $andOr = $workflow->wrkflow_timezoneAndOr;
                    $append = '';
                    $timezones = explode(',', $workflow->wrkflow_timezone);
                    foreach ($timezones as $name) {
                        $append .= "'$name'" . ',';
                    } $append = rtrim($append, ',');
                    $data['timezone'] = array($append, $workflow->wrkflow_timezoneInEx, $workflow->wrkflow_timezoneAndOr);
                }
                if (!empty($workflow->wrkflow_creation_date)) {
                    $andOr = $workflow->wrkflow_creationDateAndOr;
                    $createdDate = $workflow->wrkflow_creation_date;
                    $data['createdDate'] = array($createdDate, $workflow->wrkflow_creationDateInEx, $workflow->wrkflow_creationDateAndOr);
                }
                if (!empty($workflow->wrkflow_originalSource)) {
                    $andOr = $workflow->wrkflow_originalSourceAndOr;
                    $type = $workflow->wrkflow_originalSource;
                    $inEx = $workflow->wrkflow_originalSourceInEx;
                    $userViaApp = 0;
                    if(strcmp($type,"Offline Source") == 0){
                        $userViaApp = 0;
                    }else{
                        if($inEx == "exclude"){
                            $inEx = "include";
                        }else{
                            $inEx = "exclude";
                        }
                    }
                    $dataWorkflow['app_group_apps_id'] = array($userViaApp,$inEx,$workflow->wrkflow_originalSourceAndOr);
                } //print_r($dataWorkflow); exit;
                if (!empty($dataWorkflow)) {
                    $users = $this->workflow_model->getWorkflowExternalUsers($dataWorkflow,$app_groups);
                }
                if(empty($users[0]->external_user_id)){
                    $users = array();
                }
                ////echo count($users); exit;  print_r($users); exit;
                if (!empty($workflow->wrkflow_interaction)) {
                    if ($workflow->wrkflow_interaction == "push") {
                        if (!empty($workflow->wrkflow_interaction_campaigns)) {
                            $append = '';
                            $interactionCam = explode(',', $workflow->wrkflow_interaction_campaigns);
                            foreach ($interactionCam as $campaign) {
                                $append .= "'$campaign'" . ',';
                            } $append = rtrim($append, ',');
                            $interCamData['campaign_id'] = array($append, $workflow->wrkflow_interCampaignInEx, $workflow->wrkflow_interCampaignAndOr);
                            $interCamUsers = $this->workflow_model->getPushNotificationsOpenedUsers($interCamData);
                            if(count($interCamUsers) > 0){
                                if(!empty($interCamUsers[0]->external_user_id)){
                                    @$temp1 = array();
                                    if(!empty($users[0]->external_user_id)){
                                        @$temp1 = explode(',',$users[0]->external_user_id);
                                        @$temp2 = explode(',',$interCamUsers[0]->external_user_id);
                                        if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                            $users = array_intersect($temp1, $temp2);
                                        }else{
                                            $users = array_unique(array_merge($temp1, $temp2));
                                        }
                                    }else{
                                        @$temp2 = explode(',',$interCamUsers[0]->external_user_id);
                                        $users = $temp2;
                                    }
                                    //$inEx = "include";
                                    //$dataWorkflow['external_user_id'] = array($interCamUsers[0]->external_user_id, $inEx, $workflow['wrkflow_interCampaignAndOr']);
                                }
                            }
                            $andOr = $workflow->wrkflow_interCampaignAndOr;
                        }
                    } else if ($workflow->wrkflow_interaction == "email") {
                        if (!empty($workflow->wrkflow_interaction_campaigns)) {
                            $andOr = $workflow->wrkflow_interCampaignAndOr;
                            $append = '';
                            $interactionCam = explode(',', $workflow->wrkflow_interaction_campaigns);
                            foreach ($interactionCam as $campaign) {
                                $append .= "'$campaign'" . ',';
                            } $append = rtrim($append, ',');
                            $interCamData['campaignId'] = array($append, $workflow->wrkflow_interCampaignInEx, $workflow->wrkflow_interCampaignAndOr);
                            $interCamUsers = $this->workflow_model->getEmailInteractionOpenedUsers($interCamData); //print_r($interCamUsers); exit;
                            if(count($interCamUsers) > 0){
                                if(!empty($interCamUsers[0]->external_user_id)){
                                    @$temp1 = array();
                                    if(!empty($users[0]->external_user_id)){
                                        @$temp1 = explode(',',$users[0]->external_user_id);
                                        @$temp2 = explode(',',$interCamUsers[0]->external_user_id);
                                        if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                            $users = array_intersect($temp1, $temp2);
                                        }else{
                                            $users = array_unique(array_merge($temp1, $temp2));
                                        }
                                    }else{
                                        @$temp2 = explode(',',$interCamUsers[0]->external_user_id);
                                        $users = $temp2;
                                    }
                                    //print_r($temp1);
                                    //echo "<br>";
                                    //print_r($temp2);
                                    // echo "<br>";
                                    // print_r(array_intersect($temp1, $temp2));
                                    //print_r(array_unique(array_merge($temp1, $temp2)));

                                    //$inEx = "include";
                                    //$dataWorkflow['external_user_id'] = array($interCamUsers[0]->external_user_id, $inEx, $workflow['wrkflow_interCampaignAndOr']);
                                }
                            }
                            $andOr = $workflow->wrkflow_interCampaignAndOr;
                        }
                    }
                }
                if (!empty($workflow->wrkflow_persona)) {
                    $andOr = $workflow->wrkflow_personaAndOr;
                    $append = '';
                    $personas = explode(',', $workflow->wrkflow_persona);
                    foreach ($personas as $persona) {
                        $append .= "'$persona'" . ',';
                    }
                    $append = rtrim($append, ',');
                    $personaUser['persona_user_id'] = array($append, $workflow->wrkflow_personaInEx, $workflow->wrkflow_personaAndOr);
                    $personaUsers = $this->workflow_model->getPersonaUsers($personaUser,$app_groups); //echo $personaUsers->external_user_id; print_r(  $personaUsers);exit;
                    if(count($personaUsers) > 0){
                        if(!empty($personaUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$personaUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            }else{
                                @$temp2 = explode(',',$personaUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                            //$dataWorkflow['external_user_id'] = array($personaUsers[0]->external_user_id, $workflow['wrkflow_personaInEx'], $workflow['wrkflow_personaAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_personaAndOr;
                } //echo "<pre>"; print_r($personaUsers); exit;
                if (!empty($workflow->wrkflow_list)) {
                    $externalUserIdsArray = array();
                    $append = '';
                    $lists = explode(',', $workflow->wrkflow_list);
                    foreach ($lists as $list) {
                        $listContacts = getContactsByListId($list, $workflow->businessId);
                        $externalUserIdsArray = array_merge($externalUserIdsArray, explode(',', $listContacts));
                    }
                    $listUsers = array_unique($externalUserIdsArray); //echo count($listUsers); exit;
                    $allUsers = $this->workflow_model->getAllActiveUsers($app_groups);
                    if(count($listUsers) > 0){
                        @$temp1 = array();
                        if(strcmp($workflow->wrkflow_listInEx,'include') == 0){
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $listUsers);
                                }else{
                                    $users = array_unique(array_merge($temp1, $listUsers));
                                }
                            }elseif(count($users) > 0){
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($users, $listUsers);
                                }else{
                                    $users = array_unique(array_merge($users, $listUsers));
                                }
                            }else{
                                if(strcmp($workflow->wrkflow_listInEx,'exclude') == 0){
                                    @$temp1 = explode(',',$allUsers[0]->external_user_id);
                                    $users = array_diff($temp1, $listUsers);
                                }else{
                                    $users = $listUsers;
                                }
                            }
                        }else{
                            if(strcmp($andOr,'OR') == 0){ //echo "jfhjkhjf"; exit;

                                if(!empty($users[0]->external_user_id)){
                                    @$temp1 = explode(',',$users[0]->external_user_id);
                                }elseif(count($users) > 0){
                                    $users = array_intersect($users, $listUsers);
                                }
                                $listUsersCount = count($listUsers);
                                $totalUsers = count($temp1);
                                if($listUsersCount == 0){
                                    $users = $temp1;
                                }
                                //$users = array_unique(array_merge($temp1, $listUsers));
                                //$users = array_intersect($users, $listUsers);
                            }else{
                                @$temp1 = explode(',',$allUsers[0]->external_user_id);
                                $users = array_diff($temp1, $listUsers);
                            }
                        }
                    }
                    $andOr = $workflow->wrkflow_listAndOr;

                }
                $is_received = 0;
                $is_opened = 0;
                $is_clicked = 0;
                if (!empty($workflow->wrkflow_receiverEmail)) {
                    $append = '';
                    $receiverEmails = explode(',', $workflow->wrkflow_receiverEmail);
                    foreach ($receiverEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_received';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_receiverEmailInEx, $workflow->wrkflow_receiverEmailAndOr);
                    $receiverEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($receiverEmailUsers) > 0){
                        if(!empty($receiverEmailUsers[0]->external_user_id)){
                            if(strcmp($workflow->wrkflow_receiverEmailAndOr,'OR') == 0) { $is_received = 1; }
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$receiverEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect(@$temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge(@$temp1, @$temp2));
                                }
                            }else{
                                if(count($users) >= 0){
                                    @$temp2 = explode(',',$receiverEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        $users = array_intersect($users, @$temp2);
                                    }else{
                                        $users = array_unique(array_merge($users,  @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$receiverEmailUsers[0]->external_user_id);
                                    $users = @$temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($receiverEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_receiverEmailAndOr']);
                        }
                    } //print_r($users); exit;
                    $andOr = $workflow->wrkflow_receiverEmailAndOr;
                }
                if (!empty($workflow->wrkflow_openedEmail)) {
                    $append = '';
                    $openedEmails = explode(',', $workflow->wrkflow_openedEmail);
                    foreach ($openedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_opened';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_openedEmailInEx, $workflow->wrkflow_openedEmailAndOr);
                    $openedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($openedEmailUsers) > 0){
                        if(!empty($openedEmailUsers[0]->external_user_id)){
                            if(strcmp($workflow->wrkflow_openedEmailAndOr,'OR') == 0) { $is_opened = 1; }
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$openedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            }else{
                                if(count($users) >= 0){
                                    @$temp2 = explode(',',$openedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        $users = array_intersect($users, @$temp2);
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$openedEmailUsers[0]->external_user_id);
                                    $users = @$temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($openedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_openedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_openedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_clickedEmail)) {
                    $append = '';
                    $clickedEmails = explode(',', $workflow->wrkflow_clickedEmail);
                    foreach ($clickedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_clicked';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_clickedEmailInEx, $workflow->wrkflow_clickedEmailAndOr);
                    $clickedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($clickedEmailUsers) > 0){
                        if(!empty($clickedEmailUsers[0]->external_user_id)){ //echo $clickedEmailUsers[0]->external_user_id; exit;
                            if(strcmp($workflow->wrkflow_clickedEmailAndOr,'OR') == 0) { $is_clicked = 1; }
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$clickedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, @$temp2));
                                }
                            }else{
                                if(count($users) > 0){
                                    @$temp2 = explode(',',$clickedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        $users = array_intersect($users, @$temp2);
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$clickedEmailUsers[0]->external_user_id);
                                    $users = @$temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($clickedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_clickedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_clickedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_unsubscribedEmail)) {
                    $append = '';
                    $unsubscribedEmails = explode(',', $workflow->wrkflow_unsubscribedEmail);
                    foreach ($unsubscribedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_unsubscribed';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_unsubscribedEmailInEx, $workflow->wrkflow_unsubscribedEmailAndOr);
                    $unsubscribedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($unsubscribedEmailUsers) > 0){
                        if(!empty($unsubscribedEmailUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$unsubscribedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($users, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($users, @$temp2));
                                }
                            }else{
                                if(count($users) >= 0){
                                    @$temp2 = explode(',',$unsubscribedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        $users = array_intersect($users, @$temp2);
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    if(count($users) > 0){
                                        @$temp2 = explode(',',$unsubscribedEmailUsers[0]->external_user_id);
                                        if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                            $users = array_intersect($users, @$temp2);
                                        }else{
                                            $users = array_unique(array_merge($users, @$temp2));
                                        }
                                    }else{
                                        @$temp2 = explode(',',$unsubscribedEmailUsers[0]->external_user_id);
                                        $users = @$temp2;
                                    }
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($unsubscribedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_unsubscribedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_unsubscribedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_bouncedEmail)) {
                    $append = '';
                    $bouncedEmails = explode(',', $workflow->wrkflow_bouncedEmail);
                    foreach ($bouncedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_bounced';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_bouncedEmailInEx, $workflow->wrkflow_bouncedEmailAndOr);
                    $bouncedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($bouncedEmailUsers) > 0){
                        if(!empty($bouncedEmailUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$bouncedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, @$temp2));
                                }
                            }else{
                                if(count($users) > 0){
                                    @$temp2 = explode(',',$bouncedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        $users = array_intersect($users, @$temp2);
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$bouncedEmailUsers[0]->external_user_id);
                                    $users = $temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($bouncedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_bouncedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_bouncedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_NotReceivedEmail)) {
                    $append = '';
                    $NotReceivedEmails = explode(',', $workflow->wrkflow_NotReceivedEmail);
                    foreach ($NotReceivedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_not_received';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_NotReceivedEmailInEx, $workflow->wrkflow_NotReceivedEmailAndOr);
                    $notReceivedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($notReceivedEmailUsers) > 0){
                        if(!empty($notReceivedEmailUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$notReceivedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, @$temp2));
                                }
                            }else{ //echo count($users); print_r($users); print_r($notReceivedEmailUsers); exit;
                                if(count($users) > 0){
                                    @$temp2 = explode(',',$notReceivedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        if($is_received == 0){
                                            $users = array_intersect($users, @$temp2);}else{ $users = array_unique(array_merge($users, @$temp2));  }
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$notReceivedEmailUsers[0]->external_user_id);
                                    $users = $temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($notReceivedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_NotReceivedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_NotReceivedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_NotOpenedEmail)) {
                    $append = '';
                    $NotOpenedEmails = explode(',', $workflow->wrkflow_NotOpenedEmail);
                    foreach ($NotOpenedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_not_opened';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_NotOpenedEmailInEx, $workflow->wrkflow_NotOpenedEmailAndOr);
                    $notOpenedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($notOpenedEmailUsers) > 0){
                        if(!empty($notOpenedEmailUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$notOpenedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, @$temp2));
                                }
                            }else{
                                if(count($users) > 0){
                                    @$temp2 = explode(',',$notOpenedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        if($is_opened == 0){ $users = array_intersect($users, @$temp2);}else{ $users = array_unique(array_merge($users, @$temp2));  }
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$notOpenedEmailUsers[0]->external_user_id);
                                    $users = @$temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($notOpenedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_NotOpenedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_NotOpenedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_NotClickedEmail)) {
                    $append = '';
                    $NotClickedEmails = explode(',', $workflow->wrkflow_NotClickedEmail);
                    foreach ($NotClickedEmails as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $type = 'is_not_clicked';
                    $emailCampaign['campaignId'] = array($append, $workflow->wrkflow_NotClickedEmailInEx, $workflow->wrkflow_NotClickedEmailAndOr);
                    $notClickedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($notClickedEmailUsers) > 0){
                        if(!empty($notClickedEmailUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$notClickedEmailUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, @$temp2));
                                }
                            }else{
                                if(count($users) > 0){
                                    @$temp2 = explode(',',$notClickedEmailUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        if($is_clicked == 0){ $users = array_intersect($users, @$temp2);}else{ $users = array_unique(array_merge($users, @$temp2));  }
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$notClickedEmailUsers[0]->external_user_id);
                                    $users = @$temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($notClickedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_NotClickedEmailAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_NotClickedEmailAndOr;
                }
                if (!empty($workflow->wrkflow_lastEmailOpenDate)) {
                    $lastEmailOpenDate = $workflow->wrkflow_lastEmailOpenDate;
                    $emailCampaign['openTime'] = array($lastEmailOpenDate, $workflow->wrkflow_lastEmailOpenDateInEx, $workflow->wrkflow_lastEmailOpenDateAndOr);
                    $type = 'is_opened';
                    $lastOpenDateUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
                    if(count($lastOpenDateUsers) > 0){
                        if(!empty($lastOpenDateUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$lastOpenDateUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, @$temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, @$temp2));
                                }
                            }else{
                                if(count($users) > 0){
                                    @$temp2 = explode(',',$lastOpenDateUsers[0]->external_user_id);
                                    if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                        $users = array_intersect($users, @$temp2);
                                    }else{
                                        $users = array_unique(array_merge($users, @$temp2));
                                    }
                                }else{
                                    @$temp2 = explode(',',$lastOpenDateUsers[0]->external_user_id);
                                    $users = $temp2;
                                }
                            }
                            //$dataWorkflow['external_user_id'] = array($lastOpenDateUsers[0]->external_user_id, 'include', $workflow['wrkflow_lastEmailOpenDateAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_lastEmailOpenDateAndOr;
                }
                if (!empty($workflow->wrkflow_pageViewed)) {
                    $append = '';
                    $appPages = explode(',', $workflow->wrkflow_pageViewed);
                    foreach ($appPages as $page) {
                        $append .= "'$page'" . ',';
                    } $append = rtrim($append, ',');
                    $pages['screenName'] = array($append, $workflow->wrkflow_pageViewedInEx, $workflow->wrkflow_pageViewedAndOr);
                    if (!empty($workflow->wrkflow_NumOfViews)) {
                        $numOfViews = $workflow->wrkflow_NumOfViews;
                        $pageViewedUsers = $this->workflow_model->getAllAppScreenUsers($pages, $numOfViews);
                    } else {
                        $pageViewedUsers = $this->workflow_model->getAllAppScreenUsers($pages);
                    }
                    if(count($pageViewedUsers) > 0){
                        if(!empty($pageViewedUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$pageViewedUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            }else{
                                @$temp2 = explode(',',$pageViewedUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                            //$dataWorkflow['external_user_id'] = array($pageViewedUsers[0]->external_user_id, 'include', $workflow['wrkflow_pageViewedAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_pageViewedAndOr;
                }
                if (!empty($workflow->wrkflow_lastOpenAppDate)) {
                    $lastOpenAppDate = $workflow->wrkflow_lastOpenAppDate;
                    $lastOpenAppArr['dateTime'] = array($lastOpenAppDate, $workflow->wrkflow_lastOpenAppDateInEx, $workflow->wrkflow_lastOpenAppDateAndOr);
                    $lastOpenAppUsers = $this->workflow_model->getExternalUsersByAppOpenedDate($lastOpenAppArr);
                    if(count($lastOpenAppUsers) > 0){
                        if(!empty($lastOpenAppUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$lastOpenAppUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            }else{
                                @$temp2 = explode(',',$lastOpenAppUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                            //$dataWorkflow['external_user_id'] = array($lastOpenAppUsers[0]->external_user_id, 'include', $workflow['wrkflow_lastOpenAppDateAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_lastOpenAppDateAndOr;
                }
                if (!empty($workflow->wrkflow_NumOfVisit)) {
                    $NumOfVisit = $workflow->wrkflow_NumOfVisit;
                    $NumOfVisitArr['dateTime'] = array($NumOfVisit, $workflow->wrkflow_NumOfVisitInEx, $workflow->wrkflow_NumOfVisitAndOr);
                    $NumOfVisitUsers = $this->workflow_model->getExternalUsersByAppOpenedDate($NumOfVisitArr, $NumOfVisit);
                    if(count($NumOfVisitUsers) > 0){
                        if(!empty($NumOfVisitUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$NumOfVisitUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            }else{
                                @$temp2 = explode(',',$NumOfVisitUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                            //$dataWorkflow['external_user_id'] = array($NumOfVisitUsers[0]->external_user_id, 'include', $workflow['wrkflow_NumOfVisitAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_NumOfVisitAndOr;
                }
                if (!empty($workflow->wrkflow_lastPageSeen)) {
                    $append = '';
                    $appPages = explode(',', $workflow->wrkflow_lastPageSeen);
                    foreach ($appPages as $page) {
                        $append .= "'$page'" . ',';
                    } $append = rtrim($append, ',');
                    $pages['screenName'] = array($append, $workflow->wrkflow_lastPageSeenInEx, $workflow->wrkflow_lastPageSeenAndOr);
                    $lastPageSeenUsers = $this->workflow_model->getLastSeenPageUsers($pages);
                    if(count($lastPageSeenUsers) > 0){
                        if(!empty($lastPageSeenUsers[0]->external_user_id)){
                            @$temp1 = array();
                            if(!empty($users[0]->external_user_id)){
                                @$temp1 = explode(',',$users[0]->external_user_id);
                                @$temp2 = explode(',',$lastPageSeenUsers[0]->external_user_id);
                                if(strcmp($andOr,'AND') == 0){ //echo "clled"; exit;
                                    $users = array_intersect($temp1, $temp2);
                                }else{
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            }else{
                                @$temp2 = explode(',',$lastPageSeenUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                            //$dataWorkflow['external_user_id'] = array($lastPageSeenUsers[0]->external_user_id, 'include', $workflow['wrkflow_lastPageSeenAndOr']);
                        }
                    }
                    $andOr = $workflow->wrkflow_lastPageSeenAndOr;
                }

                $result = $users;
                $result = array_unique($result, SORT_REGULAR);
                //  echo count($users); echo "<pre>"; print_r($result); exit;//($users); print_r($listUsers); exit;

                $newUsers = '';
                if (count($result) > 0) {
                    $newArray = array();
                    foreach ($result as $row) { //print_r($row); exit;
                        //if (!in_array($row->external_user_id, $newArray)) {
                        if(!empty($row->external_user_id)){
                            $newArray[] = $row->external_user_id;
                            $newUsers .= "$row->external_user_id" . ',';
                        }else{ //echo $row; exit;
                            $newArray[] = $row;
                            $newUsers .= "$row" . ',';
                        }
                    }
                    $newUsers = rtrim($newUsers, ',');
                } //print_r($result); //exit;
                //print_r($newArray); exit;
                $date = date('Y-m-d H:i:s');
                if (!empty($workflow->wrkflow_TimeDelayType)) {
                    if ($workflow->wrkflow_TimeDelayType == "Designated-Time") {
                        $minutes = $workflow->wrkflow_designatedMinutes;
                        $hours = $workflow->wrkflow_designatedHours;
                        $days = $workflow->wrkflow_designatedDays;
                        $datetime = date('Y-m-d H:i:s', strtotime($date . "$hours hours $minutes minutes $days days"));
                    }
                    if ($workflow->wrkflow_TimeDelayType == "Intelligent-delivery") {
                        $minutes = $workflow->wrkflow_intelligentDeliveryMinutes;
                        $hours = $workflow->wrkflow_intelligentDeliveryHours;
                        $days = $workflow->wrkflow_intelligentDeliveryDays;
                        $datetime = date('Y-m-d H:i:s', strtotime($date . "$hours hours $minutes minutes $days days"));
                    }
                }
                if ($workflow->wrkflow_notificationType == "push") {
                    $userIds = $newUsers;
                    $arr = array();
                    $userIdsArray = explode(',', $userIds);
                    $userIdsArray = array_chunk($userIdsArray, $chunkSize);
                    foreach ($userIdsArray as $userIdsItem) {

                        $pushUsers = $this->workflow_model->getAllExternalUsersDevices(implode(",", $userIdsItem));//  echo "<pre>"; print_r($pushUsers); exit;
                        $totalSendUsers = $totalSendUsers +  count($pushUsers);

                        if (!empty($workflow->wrkflow_iOSNotification)) {
                            $arr = array();
                            $campaignRow = $this->campaign_model->getCampaign($workflow->wrkflow_iOSNotification); //print_r($campaignRow); exit;
                            if (count($campaignRow) > 0 && count($pushUsers) > 0) {
                                foreach ($pushUsers as $user) {
                                    $arr[] = array(
                                        'campaign_id' => $campaignRow->id,
                                        'workflow_id' => $workflow->wrkflow_id,
                                        'external_user_id' => $user->external_user_id,
                                        'user_id' => $user->businessId,
                                        'platform' => 'iOS',
                                        'app_group_apps_id' => $user->app_group_apps_id,
                                        'active_device_id' => $user->active_device_id,
                                        'deviceToken' => $user->push_notification_token,
                                        'notification_send_time' => $datetime,
                                        'notification_timezone_send_time' => $datetime,
                                        'notification_timezone_view_time' => '',
                                        'is_send' => 0,
                                        'is_view' => 0,
                                        'isDelete' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                //  echo "<pre>"; print_r($arr); exit;
                                $this->brand_model->saveNotificationHistory($arr);
                            }
                        }
                        if (!empty($workflow->wrkflow_androidNotification)) {
                            $arr = array();
                            $campaignRow = $this->campaign_model->getCampaign($workflow->wrkflow_androidNotification); //print_r($campaignRow); exit;
                            if (count($campaignRow) > 0 && count($pushUsers) > 0) {
                                foreach ($pushUsers as $user) {  // print_r($user); exit;
                                    $arr[] = array(
                                        'campaign_id' => $campaignRow->id,
                                        'workflow_id' => $workflow->wrkflow_id,
                                        'external_user_id' => $user->external_user_id,
                                        'user_id' => $user->businessId,
                                        'platform' => 'android',
                                        'app_group_apps_id' => $user->app_group_apps_id,
                                        'active_device_id' => $user->active_device_id,
                                        'deviceToken' => $user->push_notification_token,
                                        'notification_send_time' => $datetime,
                                        'notification_timezone_send_time' => $datetime,
                                        'notification_timezone_view_time' => '',
                                        'is_send' => 0,
                                        'is_view' => 0,
                                        'isDelete' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                //                  echo "<pre>"; print_r($arr); exit;
                                $this->brand_model->saveNotificationHistory($arr);
                            }
                        }
                    }

                } else if ($workflow->wrkflow_notificationType == "email") {
                    if (!empty($workflow->wrkflow_emailNotification)) {
                        $arr = array();
                        $userIds = $newUsers;
                        $userIdsArray = explode(',', $userIds);
                        $userIdsArray = array_chunk($userIdsArray, $chunkSize);
                        foreach ($userIdsArray as $userIdsItem) {
                            $emailUsers = $this->workflow_model->getAllExternalUsersEmails(implode(",", $userIdsItem)); // print_r($emailUsers); exit;
                            $campaignRow = $this->campaign_model->getCampaign($workflow->wrkflow_emailNotification); //print_r($campaignRow); exit;
                            $totalSendUsers = $totalSendUsers + count($emailUsers);
                            if (count($campaignRow) > 0 && count($emailUsers) > 0) {
                                $sql = "INSERT INTO `brand_email_campaigns_info` (`email_id`, `workflow_id`, `hurree_version`, `userid`, `campaignId`, `from_email`, `subject`, `message`, `emailSentOn`, `emailSentByUser`, `groupid`, `opened`, `openTime`, `active`, `created_on`) VALUES";
                                foreach ($emailUsers as $user) {
                                    $sql .= " ('" . mysql_real_escape_string($user->email) . "','" . $workflow->wrkflow_id . "','3.1','" . $user->external_user_id . "','" . $campaignRow->id . "','" . $campaignRow->fromAddress . "','" . mysql_real_escape_string($campaignRow->push_title) . "','" . mysql_real_escape_string($campaignRow->push_message) . "','" . $datetime . "','" . $user->businessId . "','','0','','1','".date('Y-m-d H:i:s')."'),";

                                }
                                $sql = rtrim($sql, ',');
                                $this->db->query($sql);
//
                            }
                        }


                    }
                } else if ($workflow->wrkflow_notificationType == "persona") { //echo $workflow->wrkflow_personaNotification; exit;
                    if (!empty($workflow->wrkflow_personaNotification)) {
                        $persona_user_id = $workflow->wrkflow_personaNotification;  //print_r($result); exit;
                        $totalSendUsers = count($result);
                        foreach ($result as $user) {  // print_r($user); exit;
                            $savePersonaContacts = array(
                                'persona_user_id' => $persona_user_id,
                                'external_user_id' => $user->external_user_id,
                                'isDelete' => 0,
                                'createdDate' => date('Y-m-d H:i:s')
                            );
                            $last_insertId = $this->brand_model->saveAssignContacts($savePersonaContacts);
                        }
                    }
                } else if ($workflow->wrkflow_notificationType == "in-app") {
                    $userIds = $newUsers; //echo $userIds; exit;
                    $arr = array();
                    $pushUsers = $this->workflow_model->getAllExternalUsersDevices($userIds);  //print_r($pushUsers); exit;
                    $totalSendUsers = count($pushUsers);
                    if (!empty($workflow->wrkflow_inAppNotification)) {
                        $inAppRow = $this->inapp_model->getCampaign($workflow->wrkflow_inAppNotification); //print_r($inAppRow); exit;
                        if (count($inAppRow) > 0 && $totalSendUsers > 0) {
                            foreach ($pushUsers as $user) {  // print_r($user); exit;
                                $arr = array(
                                    'messaage_id' => $inAppRow->message_id,
                                    'workflow_id' => $workflow->wrkflow_id,
                                    'external_user_id' => $user->external_user_id,
                                    'user_id' => $user->businessId,
                                    'platform' => $inAppRow->platform,
                                    'app_group_apps_id' => $user->app_group_apps_id,
                                    'active_device_id' => $user->active_device_id,
                                    'deviceToken' => $user->push_notification_token,
                                    'notification_send_time' => $datetime,
                                    'notification_timezone_send_time' => $datetime,
                                    'is_send' => 0,
                                    'createdDate' => date('Y-m-d H:i:s')
                                );
                                $this->inapp_model->saveNotificationHistory($arr); //print_r($arr); exit;
                            }
                        }
                    }
                }
                $workflowArr = array('wrkflow_id' => $workflow->wrkflow_id, 'is_send' => 1, 'total_send_users' => $totalSendUsers);
                $this->workflow_model->saveWorkflow($workflowArr);

                $status = "complete";
                $notification_alert_count = $workflow->notification_alert_count + 1;
                $workflow_send_date = $workflow->wrkflow_send_date;
                if($workflow->wrkflow_intelligentDeliveryReEligible == 1){
                    if(empty($workflow->notification_alert_count)){
                        $status = "continue";
                        $wrkflow_ReEligibleDate = $workflow->wrkflow_ReEligibleDate;
                        $wrkflow_ReEligibleDays = $workflow->wrkflow_ReEligibleDays;
                        $workflow_send_date = date('Y-m-d H:i:s', strtotime($workflow_send_date . "$wrkflow_ReEligibleDate $wrkflow_ReEligibleDays"));
                        // echo $workflow_send_date;
                    }
                    if($notification_alert_count == 2){
                        $status = "complete";
                    }
                }
                $timdelay_arr = array('timeDelay_workflow_id' => $workflow->timeDelay_workflow_id, 'wrkflow_send_date' => $workflow_send_date, 'notification_alert_count' => $notification_alert_count, 'status' => $status);
                $this->workflow_model->saveWorkflowTimedelay($timdelay_arr, 1);

            }
        }
        //echo "<pre>"; print_r($users);
        //print_r($workflows); exit;
    }

    public function getWorkflowInsightUsers() {
        $workflows = $this->workflow_model->getWorkflowInsightUsers(); //echo "<pre>"; print_r($workflows); exit;
        if (count($workflows) > 0) {
            foreach ($workflows as $key => $workflow) {
                $totalUsers = $this->workflow_model->getWorkflowActiveUsers($workflow->wrkflow_id, $workflow->workflow_types);
                $workflowArr = array('wrkflow_id' => $workflow->wrkflow_id, 'total_complete_users' => $totalUsers);
                $this->workflow_model->saveWorkflow($workflowArr);
            }
        }
    }

    function testNotiIos() {
        $deviceToken = '8b008ec010337957d06e57be6dd4b7c4e11ed62035c1268085c78fdd75a6d90c';
        $deviceType = 'iOS';
        $title = 'sdk Push Notification';

        $sound = 'default';
        $msgpayload = json_encode(array(
            'aps' => array(
                'alert' => 'Received sdk Push Notification',
                "app_group_id" => "1",
                "notification_id" => "1",
                "campaignName" => 'campaignName',
                "push_title" => 'push_title',
                "push_message" => 'push_message',
                "push_on_click_behaviour" => "3",
                "push_on_click_url" => '',
                "push_image_url" => 'push_image_url',
                "expand_img_url" => 'expand_img_url',
                'type' => 'sdkNotification',
                'sound' => $sound,
            )));

        $message = json_encode(array(
            'default' => $title,
            'APNS' => $msgpayload
        )); //echo '<pre>'; print_r($message); exit;

        $result = $this->amazonSns($deviceToken, $message, $deviceType);

        //$result = $this->sendIosPush($deviceToken, $message);
        print_r($result);
        exit;
    }

    public function testPush() {
        $deviceToken = '9dc85425a26f1bf34854103af2b29398134c07549b728bf15d95f5ebec66948f';
        $message = 'This is test push message!';
        $sound = 'default';
        $payload = array(
            'aps' => array(
                "alert" => 'Received sdk Push Notification',
                "app_group_id" => "1",
                "notification_id" => "1",
                "campaignName" => 'campaignName',
                "push_title" => 'push_title',
                "push_message" => 'push_message',
                "push_on_click_behaviour" => "3",
                "push_on_click_url" => '',
                "push_image_url" => 'push_image_url',
                "expand_img_url" => 'expand_img_url',
                "type" => 'sdkNotification',
                "sound" => $sound
            )
        );
        $this->sendIosPush($deviceToken, $payload, 0, "1471416271file.pem");
    }

    public function testNoitAndroid() {
        $this->load->library('Gcm');
        $gcm = new GCM();
        $api_key = "AIzaSyBhuz3MFoUyNX3MNLZtma1l89sauNDGT7U";
        //	$api_key = "AIzaSyBhuz3MFoUyNX3MNLZtma1l89sauNDGT7U";
        $deviceToken = array("eG7HN48Axq8:APA91bGX6stXlS0HVAl7fZdTs92Nf2WCw3Eyz1NBe818m_1mXWiclvbpqHvjTO50Q4XGnxdYROYF3qvdIvU6Nreb7gPiQLO4QgGBVa6MQ90_w7jxsxxtJepryIs3_tsjuozZ6-0dSkNX", "eG7HN48Axq8:APA91bGX6stXlS0HVAl7fZdTs92Nf2WCw3Eyz1NBe818m_1mXWiclvbpqHvjTO50Q4XGnxdYROYF3qvdIvU6Nreb7gPiQLO4QgGBVa6MQ90_w7jxsxxtJepryIs3_tsjuozZ6-0dSkNX");

        $notification = array("body" => "body", "title" => "title", "icon" => "icon");
        $notification = json_encode($notification);
        $result = $gcm->sendPushNotification($api_key, $deviceToken, $notification);
        echo "<pre>";
        print_r($result);
        exit;
    }

    public function sendGridNotification() {
        $raw_post_data = file_get_contents('php://input');
        //$raw_post_data =  '[{"email":"shankar@yahoo.com","timestamp":1487329188,"ip":"10.43.18.4","sg_event_id":"MDU0ZTUzYTktZDUxOC00ZGM4LThhY2ItMzNhYzRiY2NkZThj","sg_message_id":"NTxgF19MTpm66L7spNWNVA.filter0022p1las1-7990-58A6D742-10.5","useragent":"Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:51.0) Gecko/20100101 Firefox/51.0","event":"spamreport"},{"email":"dev@yahoo.com","timestamp":1487329188,"ip":"10.43.18.4","sg_event_id":"NjJiMGY3ZDgtN2FkMi00ZjhkLWJlNjctNTk0ZmE1NzlmZDll","sg_message_id":"NTxgF19MTpm66L7spNWNVA.filter0022p1las1-7990-58A6D742-10.5","useragent":"Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:51.0) Gecko/20100101 Firefox/51.0","event":"spamreport"}]';
        //echo $raw_post_data; die;

        $insert['json'] = $raw_post_data;
        $insert['createdDate'] = date('Y-m-d H:i:s');
        $this->brand_model->saveSendGridHistory($insert);

        $sendGridNotificationsArray = json_decode($raw_post_data);

        $fp = fopen('/var/www/html/hurree3.1/sendfile.txt', 'a+');
        fwrite($fp, print_r($raw_post_data, TRUE));
        fclose($fp);

        $processedEmail = array();
        $deliveredEmail = array();
        $deferredEmail = array();
        $clickEmail = array();
        $bounceEmail = array();
        $droppedEmail = array();
        $spamreportEmail = array();
        $unsubscribeEmail = array();
        $groupUnsubscribeEmail = array();
        $groupResubscribeEmail = array();
        $spamArray = array();

        foreach ($sendGridNotificationsArray as $key => $sendGridNotification) {
            $messageId = explode(".filter", $sendGridNotification->sg_message_id)[0];


            if ($sendGridNotification->event == 'processed') {
                $processedEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'delivered') {
                $deliveredEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'deferred') {
                $deferredEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'open') {
                //$openEmail[] = $sendGridNotification->email;
                $openTime = date('Y-m-d H:i:s', $sendGridNotification->timestamp);
                $where['sendgrid_message_id'] = $messageId;
                $where['email_id'] = $sendGridNotification->email;
                $updateOpen['opened'] = 1;
                $updateOpen['is_opened'] = 1;
                $updateOpen['openTime'] = $openTime;
                $this->brand_model->updateSendGridOpenEvents($updateOpen, $where);
            }

            if ($sendGridNotification->event == 'click') {
                $clickEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'bounce') {
                $bounceEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'dropped') {
                $droppedEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'spamreport') {
                $spamreportEmail[] = $sendGridNotification->email;

                $row = $this->brand_model->getBusinessIdBySendGridMsgId($messageId,$sendGridNotification->email);
                if(count($row) > 0){
                    $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);

                    $spamArray[] = array(
                        'email' => $sendGridNotification->email,
                        'businessId' => $rowBusinessId->businessId,
                        'type' => 'spam',
                        'app_group_id' => $rowBusinessId->app_group_id
                    );
                }
            }
            if ($sendGridNotification->event == 'unsubscribe') {
                $unsubscribeEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'group_unsubscribe') {
                $groupUnsubscribeEmail[] = $sendGridNotification->email;
            }
            if ($sendGridNotification->event == 'group_resubscribe') {
                $groupResubscribeEmail[] = $sendGridNotification->email;
            }
        }

        if (count($processedEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $processedEmail;
            $updateProcessed['is_processed'] = 1;
            $this->brand_model->updateSendGridEvents($updateProcessed, $where);
        }
        if (count($deliveredEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $deliveredEmail;
            $updateDelivered['is_received'] = 1;
            $this->brand_model->updateSendGridEvents($updateDelivered, $where);
        }
        if (count($deferredEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $deferredEmail;
            $updateDeferred['is_deferred'] = 1;
            $this->brand_model->updateSendGridEvents($updateDeferred, $where);
        }
        if (count($clickEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $clickEmail;
            $updateClicked['is_clicked'] = 1;
            $this->brand_model->updateSendGridEvents($updateClicked, $where);
        }
        if (count($bounceEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $bounceEmail;
            $updateBounced['is_bounced'] = 1;
            $this->brand_model->updateSendGridEvents($updateBounced, $where);
        }
        if (count($droppedEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $droppedEmail;
            $updateDropped['is_dropped'] = 1;
            $this->brand_model->updateSendGridEvents($updateDropped, $where);
        }
        if (count($spamreportEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $spamreportEmail;
            $updateSpam['is_spamreport'] = 1;
            $this->brand_model->updateSendGridEvents($updateSpam, $where);
        }
        if (count($unsubscribeEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $unsubscribeEmail;
            $updateUnsubscribe['is_unsubscribed'] = 1;
            $this->brand_model->updateSendGridEvents($updateUnsubscribe, $where);
        }
        if (count($groupUnsubscribeEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $groupUnsubscribeEmail;
            $updateGroupUnsubscribe['group_unsubscribe'] = 1;
            $this->brand_model->updateSendGridEvents($updateGroupUnsubscribe, $where);
        }
        if (count($groupResubscribeEmail) > 0) {
            $where['sendgrid_message_id'] = $messageId;
            $where['email_id'] = $groupResubscribeEmail;
            $updateGroupResubscribe['group_resubscribe'] = 1;
            $this->brand_model->updateSendGridEvents($updateGroupResubscribe, $where);
        }

        if(count($spamArray) > 0){
            foreach($spamArray as $spam){
                $data['from_email'] = $spam['email'];
                $data['unsubscribe'] = 1;
                $data['type'] = $spam['type'];
                $data['businessId'] = $spam['businessId'];
                $data['app_group_id'] = $spam['app_group_id'];

                $this->db->select('*');
                $this->db->where('from_email',$spam['email']);
                $this->db->where('type','spam');
                $result = $this->db->get('unsubscribe_emails');
                $result = $result->row_array();

                if(count($result) == 0){
                    $this->brand_model->insertSpam($data);
                }

            }
        }

        echo 'updated';
    }

    /*
    * function used to upload contacts using xls/csv file. The code read the file and make entry in external_users table.
    */
    public function uploadContactsFromFile() {

        $this->load->helper('cron');

        $isCronActive = isCronActive('uploadContactsFromFile.txt',5);
        if($isCronActive == true){
            return false;
        }

        updateCronTime('uploadContactsFromFile.txt');
        ini_set('max_execution_time', 500);
        $date = date('YmdHis');

        $this->load->library('excel');
        $login = $this->administrator_model->front_login_session();
        $filesData = $this->contact_model->getContactFiles("0");
        $count = 0;
        if (count($filesData) > 0) {
            foreach ($filesData as $fileData) {

                /* get the user data for send email*/
                $senderData = $this->user_model->getOneUser($fileData['userId']);
                /* get the file data*/
                $file_id = $fileData['file_id'];
                $file_name = $fileData['name'];
                $file = './upload/files/' . $file_name;

                $objReader = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($file));
                $spreadsheetInfo = $objReader->listWorksheetInfo($file);
                /**  Create a new Instance of our Read Filter  * */
                $chunkFilter = new PHPExcel_ChunkReadFilter();
                $chunkSize = 1000;
                /**  Tell the Reader  that we want to use the Read Filter that we've Instantiated * */
                $objReader->setReadFilter( $chunkFilter);
                $objReader->setReadDataOnly(true);
                //echo("Reading file " . $file . PHP_EOL . "<br>");
                $totalRows = $spreadsheetInfo[0]['totalRows'];
                //echo("Total rows in file " . $totalRows . " " . PHP_EOL . "<br>");

                for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
                    // echo("Loading WorkSheet for rows " . $startRow . " to " . ($startRow + $chunkSize - 1) . PHP_EOL . "<br>");

                    /**  Tell the Read Filter, the limits on which rows we want to read this iteration  * */
                    $chunkFilter->setRows($startRow, $chunkSize);

                    /**  Load only the rows that match our filter from $inputFileName to a PHPExcel Object  * */
                    $objReader->setReadFilter($chunkFilter);
                    $objPHPExcel = $objReader->load($file);
                    $sheetData = $objPHPExcel->getActiveSheet()->rangeToArray(
                        'A'.$startRow.':'.$objPHPExcel->getActiveSheet()->getHighestColumn().($startRow+$chunkSize-1),
                        null,
                        true,
                        true,
                        true
                    );
                    $sql = "INSERT INTO `external_users` (`app_group_id`, `email`, `firstName`, `lastName`, `phoneNumber`, `file_id`, `createdDate`, `company`) VALUES";
                    foreach ($sheetData as $element) {
                        if (!empty($element['E']))
                            $sql .= " ('" . $element['B'] . "','" . $element['E'] . "','" . $element['C'] . "','" . $element['D'] . "','" . $element['F'] . "','" . $file_id . "','" . $date . "', '" . $element['G'] . "'),";
                    }
                    $sql = rtrim($sql, ',');
                    $this->db->query($sql);
                    //echo $this->db->last_query();die;
                }
                $objPHPExcel->disconnectWorksheets();
                unset($objPHPExcel, $sheetData);
                /* update the contact_file_table for that entry */
                $save = array('status' => 1, "file_id" => $file_id);
                $filesData = $this->contact_model->updateContactFileTableEntry($save);
                //send email to user to tell that file is uploaded
                $email_template = $this->email_model->getoneemail('contactFileUploaded');
                //// MESSAGE OF EMAIL
                $messages = $email_template->message;
                $hurree_image = base_url() . 'assets/img/Graph-icon-white-grey.png';
                //// replace strings from message
                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{Username}', ucfirst($senderData->firstname), $messages);

                $httpClient = new \Http\Adapter\Guzzle6\Client(new Client());
                $sparky = new SparkPost($httpClient, ['key' => SPARKPOSTKEYSUB]);
                $promise = $sparky->transmissions->post([
                    'content' => [
                        'from' => [
                            'name' => 'Hurree',
                            'email' => 'hello@marketmyapp.co',
                        ],
                        'subject' => $email_template->subject,
                        'html' => $messages,
                        'text' => '',
                    ],
                    'recipients' => [
                        [
                            'address' => [
                                'name' => $senderData->firstname,
                                'email' => $senderData->email,
                            ],
                        ],
                    ],
                ]);
                $promise = $sparky->transmissions->get();
                try {
                    $response = $promise->wait();
                    //echo $response->getStatusCode() . "\n";
                    //print_r($response->getBody()) . "\n";
                } catch (\Exception $e) {
                    //echo $e->getCode() . "\n";
                    //echo $e->getMessage() . "\n";
                }


                updateCronTime('uploadContactsFromFile.txt');
            }
        }
    }

    /*
    * function used to validate the uploaded contacts. Invalid contacts are removed from the external_users.
    */
    public function validateContacts() {
        /* ge the files whose contacts are inserted into external user table but uploaded data is not validated */

        $this->load->helper('cron');

        $isCronActive = isCronActive('validateContacts.txt',10);
        if($isCronActive == true){
            return false;
        }

        updateCronTime('validateContacts.txt');

        $filesData = $this->contact_model->getContactFiles(1);

        /* Find the app groups of uploaded file */ if (count($filesData) > 0) {
            foreach ($filesData as $fileData) {
                $businessIdOfFile = $fileData['businessId'];
                $file_id = $fileData['file_id'];
                $groupsIdArray = $this->groupapp_model->getGroupsIdsOnly($businessIdOfFile);
                $groupsIdArrayOneDimensionalArray = array_map('current', $groupsIdArray);
                /* Delete records of a file having other app_group_id */ $responseDeleteFirstCase = $this->contact_model->deleteContactsUsingAppGroupIds($groupsIdArrayOneDimensionalArray, $file_id);
                /* Delete records of a file having other app_group_id */

                $responseDeleteFirstCase = $this->contact_model->deleteContactsUsingAppGroupIds($groupsIdArrayOneDimensionalArray, $file_id);
                // echo $this->db->last_query(); die;
                /* Delete records of a file having other app_group_id */

                foreach ($groupsIdArrayOneDimensionalArray as $appGroupId) {

                    /*$sql = "DELETE n1 FROM external_users n1, external_users n2 WHERE n1.external_user_id > n2.external_user_id AND n1.email = n2.email AND n1.file_id IS NOT NULL AND (n1.app_group_id='" . $appGroupId . "' || n2.app_group_id='" . $appGroupId . "')";*/

                    $sql = "Update  external_users x join external_users z on x.email = z.email set x.isDelete = 1 where (x.external_user_id > z.external_user_id ) AND (x.app_group_id='" . $appGroupId . "' && z.app_group_id='" . $appGroupId . "') AND (x.isDelete='0'&& z.isDelete='0')";
                    $this->db->query($sql);
                    //  echo $this->db->last_query(); die;

                }
                /*Delete the invalid emails of uploaded file*/
                $sqlForUpdateInvalidEmail = "Update `external_users` SET isDelete = 1 WHERE `email` NOT REGEXP '^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$' AND isDelete = 0 AND file_id = '" . $file_id . "'";
                $this->db->query($sqlForUpdateInvalidEmail);

                /* update contact_file_table and set status 2 this means operation on file is complete */
                $save = array('status' => 2, "file_id" => $file_id);
                $filesData = $this->contact_model->updateContactFileTableEntry($save);
                updateCronTime('validateContacts.txt');
            }
        }

    }

    function getTimeZone($timezone)
    {
        if (!empty($timezone)) {
            $timezone = $timezone;
            $timezone = explode(" ", $timezone);
            $hours = 0;
            $minutes = 0;
            if ($timezone[0] == "GMT") {
                $hours = substr($timezone[1], 0, 3);
                $minutes = substr($timezone[1], 3, 5);
            }
            return $hours.' hours '. $minutes. ' minutes ';
        }
    }

    function createWorkflowEntries(){// echo "jfhjkf"; exit;
        $this->load->helper('cron');
/*
        $isCronActive = isCronActive('createWorkflowEntries.txt',2);
        if($isCronActive == true){
            //echo "cron active";
            return false;
        }
        updateCronTime('createWorkflowEntries.txt');*/

        $date = date('Y-m-d');
        $chunckSize = 100;
        $workflowHistoryTime = $this->workflow_model->getWorkflowHistoryTime($date);
        //echo "<pre>"; print_r($workflowHistoryTime); die;
        $records = $newWorkflow = array();
        if(count($workflowHistoryTime) > 0){
            foreach($workflowHistoryTime as $workflowHistoryTimeRow){
                if($workflowHistoryTimeRow->wrkflow_notificationType == "email")
                    $this->putBrandInfoEntries($workflowHistoryTimeRow, $chunckSize);
                else if($workflowHistoryTimeRow->wrkflow_notificationType == "in-app")
                    $this->putInAppNotification($workflowHistoryTimeRow, $chunckSize);
                else if($workflowHistoryTimeRow->wrkflow_notificationType == "push")
                    $this->pushNotificationEntry($workflowHistoryTimeRow, $chunckSize);
                else if(strtolower($workflowHistoryTimeRow->wrkflow_notificationType) == "persona")
                    $this->saveWorkflowPersona($workflowHistoryTimeRow, $chunckSize);
            } //End foreach
        } //End Count
    }

    /*
     * function is used to make entry in brand_campaign_info table to send email according to time delayed
     */
    function putBrandInfoEntries($workflowHistory = null, $chunckSize)
    {
        unset($records);
        $wrkflow_history_timeId = $workflowHistory->wrkflow_history_timeId;
        $wrkflow_time = $workflowHistory->wrkflow_time;

        $where['wrkflow_historyId'] = $workflowHistory->wrkflow_historyId;
        $where['wrkflow_id'] = $workflowHistory->wrkflow_id;
        $getHistory = $this->workflow_model->getWorkflowHistory($where);
        $newWorkflow = array();
        //echo '<pre>';
        //print_r($getHistory); die;
        if(count($getHistory) > 0){
            $wrkflow_id = $getHistory->wrkflow_id;
            $externalUsersIds = explode(",",$getHistory->external_userid_text);
            //  echo '<pre>';
            //print_r($externalUsersIds); die;
            //$wrkflow_id = 1;
            //echo count($externalUsersIds); die;

            $newWorkflow = $this->workflow_model->getWorkflowEmailCampaignId($wrkflow_id, $workflowHistory->wrkflow_delay_id, 'email');
        }
        if(count($newWorkflow) > 0){
            $campaignId = $newWorkflow->wrkflow_emailNotification;
            //echo $campaignId.'<br>';
            $emailCampaign = $this->campaign_model->getCampaign($campaignId);
            //echo '<pre>';
            //print_r($emailCampaign); die;


            $app_group_id = $emailCampaign->app_group_id;
            $platform = $emailCampaign->platform;
            $campaignName = $emailCampaign->campaignName;
            $push_title = $emailCampaign->push_title;
            $push_message = $emailCampaign->push_message;
            $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

            $appGroup = $this->brand_model->getAppGroupRow($emailCampaign->app_group_id);
            $businessId = $appGroup->businessId;

            if(!empty($emailCampaign->displayName)) {
                $displayName = $emailCampaign->displayName;
            }else {
                $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
            }

            if(!empty($emailCampaign->fromAddress)) {
                $fromAddress = $emailCampaign->fromAddress;
            }else {
                $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
            }

            if(!empty($emailCampaign->replyToAddress)) {
                $replyToAddress = $emailCampaign->replyToAddress;
            }else {
                $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
            }
            $countUsers = count($externalUsersIds);
            if($countUsers > $chunckSize)
            {
                $beginCounter = intval($countUsers / $chunckSize);
                for ($count = 0; $count < $beginCounter; $count++) {
                    unset($records);
                    for ($i = 0; $i < $chunckSize; $i++) {
                        $indexer = $i + ($count * $chunckSize);
                        $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                        $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';
                        if($externalUser != null) {
                            if ($workflowHistory->type == 'DT'){
                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $wrkflow_time,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );
                            }
                            $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                            if($workflowHistory->type == 'ID')
                            {

                                if($workflowHistory->days == '' && $workflowHistory->time_between == '' ){
                                    $records[] = array(
                                        'email_id' => $externalUser->email,
                                        'hurree_version' => '3.1',
                                        'userid' => $externalUser->external_user_id,
                                        'campaignId' => $campaignId,
                                        'workflow_id' => $wrkflow_id,
                                        'app_group_apps_id' => $externalUser->app_group_apps_id,
                                        //'active_device_id' => $externalUser->active_device_id,
                                        'from_email' => $fromAddress,
                                        'subject' => $push_title,
                                        'message' => $push_message,
                                        'emailSentOn' => $dateString,
                                        'emailSentByUser' => $businessId,
                                        'groupid' => '',
                                        'opened' => 0,
                                        'openTime' => '',
                                        'active' => '1',
                                        'created_on' => date('Y-m-d H:i:s')
                                    );
                                }
                                if($workflowHistory->days != '' && $workflowHistory->time_between == ''){ //die("1");
                                    if(strpos($workflowHistory->days, "days")!== false)
                                    {
                                        $increase = trim(str_replace("days", "", $workflowHistory->days));
                                        $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($dateString)));
                                    }
                                    elseif (strpos($workflowHistory->days, "month") !== false)
                                    {
                                        $increase = trim(str_replace("month", "", $workflowHistory->days));
                                        $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($dateString)));
                                    }

                                    $records[] = array(
                                        'email_id' => $externalUser->email,
                                        'hurree_version' => '3.1',
                                        'userid' => $externalUser->external_user_id,
                                        'campaignId' => $campaignId,
                                        'workflow_id' => $wrkflow_id,
                                        'app_group_apps_id' => $externalUser->app_group_apps_id,
                                        //'active_device_id' => $externalUser->active_device_id,
                                        'from_email' => $fromAddress,
                                        'subject' => $push_title,
                                        'message' => $push_message,
                                        'emailSentOn' => $dateString,
                                        'emailSentByUser' => $businessId,
                                        'groupid' => '',
                                        'opened' => 0,
                                        'openTime' => '',
                                        'active' => '1',
                                        'created_on' => date('Y-m-d H:i:s')
                                    );


                                    $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                    $records[] = array(
                                        'email_id' => $externalUser->email,
                                        'hurree_version' => '3.1',
                                        'userid' => $externalUser->external_user_id,
                                        'campaignId' => $campaignId,
                                        'workflow_id' => $wrkflow_id,
                                        'app_group_apps_id' => $externalUser->app_group_apps_id,
                                        //'active_device_id' => $externalUser->active_device_id,
                                        'from_email' => $fromAddress,
                                        'subject' => $push_title,
                                        'message' => $push_message,
                                        'emailSentOn' => $dateString2,
                                        'emailSentByUser' => $businessId,
                                        'groupid' => '',
                                        'opened' => 0,
                                        'openTime' => '',
                                        'active' => '1',
                                        'created_on' => date('Y-m-d H:i:s')
                                    );

                                }
                                if($workflowHistory->days == '' && $workflowHistory->time_between != '' )
                                {
                                    $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                    $timeDelay = explode('-',$workflowHistory->time_between);
                                    $timeDelay = $timeDelay[0];
                                    $dateString = $dateString.' '.$timeDelay.':00';
                                    if(strtotime("now ". $usertimezone) < strtotime($dateString .' '.$usertimezone))
                                    {
                                        $hourse = (strtotime($dateString." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                        if($hourse < 1)
                                            $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                        else
                                            $dateString = date("Y-m-d H:i:s", strtotime($dateString ." ".$usertimezone));
                                    }
                                    else{
                                        $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                    }
                                    $records[] = array(
                                        'email_id' => $externalUser->email,
                                        'hurree_version' => '3.1',
                                        'userid' => $externalUser->external_user_id,
                                        'campaignId' => $campaignId,
                                        'workflow_id' => $wrkflow_id,
                                        'app_group_apps_id' => $externalUser->app_group_apps_id,
                                        //'active_device_id' => $externalUser->active_device_id,
                                        'from_email' => $fromAddress,
                                        'subject' => $push_title,
                                        'message' => $push_message,
                                        'emailSentOn' => $dateString,
                                        'emailSentByUser' => $businessId,
                                        'groupid' => '',
                                        'opened' => 0,
                                        'openTime' => '',
                                        'active' => '1',
                                        'created_on' => date('Y-m-d H:i:s')
                                    );

                                }
                                if($workflowHistory->days != '' && $workflowHistory->time_between != '' )
                                {
                                    $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                    $timeDelay = explode('-',$workflowHistory->time_between);
                                    $timeDelay = $timeDelay[0];
                                    $dateString1 = $dateString.' '.$timeDelay.':00';
                                    $timeDelay1 = $timeDelay[1];
                                    $dateString2 = $dateString.' '.$timeDelay1.':00';
                                    if(strtotime("now ". $usertimezone) < strtotime($dateString2 .' '.$usertimezone))
                                    {
                                        $hourse = (strtotime($dateString1." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                        if($hourse < 1)
                                            $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                        else
                                            $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 ." ".$usertimezone));
                                    }
                                    else{
                                        $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                    }

                                    $records[] = array(
                                        'email_id' => $externalUser->email,
                                        'hurree_version' => '3.1',
                                        'userid' => $externalUser->external_user_id,
                                        'campaignId' => $campaignId,
                                        'workflow_id' => $wrkflow_id,
                                        'app_group_apps_id' => $externalUser->app_group_apps_id,
                                        //'active_device_id' => $externalUser->active_device_id,
                                        'from_email' => $fromAddress,
                                        'subject' => $push_title,
                                        'message' => $push_message,
                                        'emailSentOn' => $dateString1,
                                        'emailSentByUser' => $businessId,
                                        'groupid' => '',
                                        'opened' => 0,
                                        'openTime' => '',
                                        'active' => '1',
                                        'created_on' => date('Y-m-d H:i:s')
                                    );



                                    $workflowhistoryDate =  date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                    if(strpos($workflowHistory->days, "days")!== false)
                                    {
                                        $increase = trim(str_replace("days", "", $workflowHistory->days));
                                        $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($workflowhistoryDate)));
                                    }
                                    elseif (strpos($workflowHistory->days, "month") !== false)
                                    {
                                        $increase = trim(str_replace("month", "", $workflowHistory->days));
                                        $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($workflowhistoryDate)));
                                    }






                                    $records[] = array(
                                        'email_id' => $externalUser->email,
                                        'hurree_version' => '3.1',
                                        'userid' => $externalUser->external_user_id,
                                        'campaignId' => $campaignId,
                                        'workflow_id' => $wrkflow_id,
                                        'app_group_apps_id' => $externalUser->app_group_apps_id,
                                        //'active_device_id' => $externalUser->active_device_id,
                                        'from_email' => $fromAddress,
                                        'subject' => $push_title,
                                        'message' => $push_message,
                                        'emailSentOn' => $workflowhistoryDate,
                                        'emailSentByUser' => $businessId,
                                        'groupid' => '',
                                        'opened' => 0,
                                        'openTime' => '',
                                        'active' => '1',
                                        'created_on' => date('Y-m-d H:i:s')
                                    );




                                }
                            }
                        }
                    }
                    if(isset($records) && !empty($records))
                        $this->workflow_model->insertBrandEmailCampaignInfo($records);

                    updateCronTime('createWorkflowEntries.txt');
                }

                $lastCounter = $countUsers % $chunckSize;
                $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';

                unset($records);
                for ($count = 0; $count < $lastCounter; $count++) {
                    $indexer = $count + ($beginCounter * $chunckSize);
                    $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                    $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';

                    if($externalUser != null) {
                        if ($workflowHistory->type == 'DT'){
                            $records[] = array(
                                'email_id' => $externalUser->email,
                                'hurree_version' => '3.1',
                                'userid' => $externalUser->external_user_id,
                                'campaignId' => $campaignId,
                                'workflow_id' => $wrkflow_id,
                                'app_group_apps_id' => $externalUser->app_group_apps_id,
                                //'active_device_id' => $externalUser->active_device_id,
                                'from_email' => $fromAddress,
                                'subject' => $push_title,
                                'message' => $push_message,
                                'emailSentOn' => $wrkflow_time,
                                'emailSentByUser' => $businessId,
                                'groupid' => '',
                                'opened' => 0,
                                'openTime' => '',
                                'active' => '1',
                                'created_on' => date('Y-m-d H:i:s')
                            );
                        }
                        $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                        if($workflowHistory->type == 'ID')
                        {

                            if($workflowHistory->days == '' && $workflowHistory->time_between == '' ){
                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );
                            }
                            if($workflowHistory->days != '' && $workflowHistory->time_between == ''){ //die("1");
                                if(strpos($workflowHistory->days, "days")!== false)
                                {
                                    $increase = trim(str_replace("days", "", $workflowHistory->days));
                                    $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($dateString)));
                                }
                                elseif (strpos($workflowHistory->days, "month") !== false)
                                {
                                    $increase = trim(str_replace("month", "", $workflowHistory->days));
                                    $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($dateString)));
                                }

                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );


                                $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString2,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );

                            }
                            if($workflowHistory->days == '' && $workflowHistory->time_between != '' )
                            {
                                $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                $timeDelay = explode('-',$workflowHistory->time_between);
                                $timeDelay = $timeDelay[0];
                                $dateString = $dateString.' '.$timeDelay.':00';
                                if(strtotime("now ". $usertimezone) < strtotime($dateString .' '.$usertimezone))
                                {
                                    $hourse = (strtotime($dateString." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                    if($hourse < 1)
                                        $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                    else
                                        $dateString = date("Y-m-d H:i:s", strtotime($dateString ." ".$usertimezone));
                                }
                                else{
                                    $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                }
                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );

                            }
                            if($workflowHistory->days != '' && $workflowHistory->time_between != '' )
                            {
                                $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                $timeDelay = explode('-',$workflowHistory->time_between);
                                $timeDelay = $timeDelay[0];
                                $dateString1 = $dateString.' '.$timeDelay.':00';
                                $timeDelay1 = $timeDelay[1];
                                $dateString2 = $dateString.' '.$timeDelay1.':00';
                                if(strtotime("now ". $usertimezone) < strtotime($dateString2 .' '.$usertimezone))
                                {
                                    $hourse = (strtotime($dateString1." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                    if($hourse < 1)
                                        $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                    else
                                        $timeDelay1 = $timeDelay[1];
                                    $dateString1 = $dateString.' '.$timeDelay1.':00';
                                    $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 ." ".$usertimezone));
                                }
                                else{
                                    $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                }

                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString1,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );



                                $workflowhistoryDate =  date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                if(strpos($workflowHistory->days, "days")!== false)
                                {
                                    $increase = trim(str_replace("days", "", $workflowHistory->days));
                                    $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($workflowhistoryDate)));
                                }
                                elseif (strpos($workflowHistory->days, "month") !== false)
                                {
                                    $increase = trim(str_replace("month", "", $workflowHistory->days));
                                    $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($workflowhistoryDate)));
                                }






                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $workflowhistoryDate,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );




                            }
                        }
                    }

                }
                if(isset($records) && !empty($records))
                    $this->workflow_model->insertBrandEmailCampaignInfo($records);
            }
            else
            {
                for ($count = 0; $count < $countUsers; $count++) {
                    $indexer = $count;
                    $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);


                    $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';
                    if($externalUser != null) {
                        if ($workflowHistory->type == 'DT'){
                            $records[] = array(
                                'email_id' => $externalUser->email,
                                'hurree_version' => '3.1',
                                'userid' => $externalUser->external_user_id,
                                'campaignId' => $campaignId,
                                'workflow_id' => $wrkflow_id,
                                'app_group_apps_id' => $externalUser->app_group_apps_id,
                                //'active_device_id' => $externalUser->active_device_id,
                                'from_email' => $fromAddress,
                                'subject' => $push_title,
                                'message' => $push_message,
                                'emailSentOn' => $wrkflow_time,
                                'emailSentByUser' => $businessId,
                                'groupid' => '',
                                'opened' => 0,
                                'openTime' => '',
                                'active' => '1',
                                'created_on' => date('Y-m-d H:i:s')
                            );
                        }
                        $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                        if($workflowHistory->type == 'ID')
                        {

                            if($workflowHistory->days == '' && $workflowHistory->time_between == '' ){
                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );
                            }
                            if($workflowHistory->days != '' && $workflowHistory->time_between == ''){ //die("1");
                                if(strpos($workflowHistory->days, "days")!== false)
                                {
                                    $increase = trim(str_replace("days", "", $workflowHistory->days));
                                    $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($dateString)));
                                }
                                elseif (strpos($workflowHistory->days, "month") !== false)
                                {
                                    $increase = trim(str_replace("month", "", $workflowHistory->days));
                                    $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($dateString)));
                                }

                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );


                                $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString2,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );

                            }
                            if($workflowHistory->days == '' && $workflowHistory->time_between != '' )
                            {
                                $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                $timeDelay = explode('-',$workflowHistory->time_between);
                                $timeDelay = $timeDelay[0];
                                $dateString = $dateString.' '.$timeDelay.':00';
                                if(strtotime("now ". $usertimezone) < strtotime($dateString .' '.$usertimezone))
                                {
                                    $hourse = (strtotime($dateString." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                    if($hourse < 1)
                                        $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                    else
                                        $dateString = date("Y-m-d H:i:s", strtotime($dateString ." ".$usertimezone));
                                }
                                else{
                                    $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                }
                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );

                            }
                            if($workflowHistory->days != '' && $workflowHistory->time_between != '' )
                            {
                                $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                $timeDelay = explode('-',$workflowHistory->time_between);
                                $timeDelay = $timeDelay[0];
                                $dateString1 = $dateString.' '.$timeDelay.':00';
                                $timeDelay1 = $timeDelay[1];
                                $dateString2 = $dateString.' '.$timeDelay1.':00';
                                if(strtotime("now ". $usertimezone) < strtotime($dateString2 .' '.$usertimezone))
                                {
                                    $hourse = (strtotime($dateString1." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                    if($hourse < 1)
                                        $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                    else
                                        $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 ." ".$usertimezone));
                                }else{
                                    $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                }

                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $dateString1,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );



                                $workflowhistoryDate =  date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                if(strpos($workflowHistory->days, "days")!== false)
                                {
                                    $increase = trim(str_replace("days", "", $workflowHistory->days));
                                    $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($workflowhistoryDate)));
                                }
                                elseif (strpos($workflowHistory->days, "month") !== false)
                                {
                                    $increase = trim(str_replace("month", "", $workflowHistory->days));
                                    $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($workflowhistoryDate)));
                                }






                                $records[] = array(
                                    'email_id' => $externalUser->email,
                                    'hurree_version' => '3.1',
                                    'userid' => $externalUser->external_user_id,
                                    'campaignId' => $campaignId,
                                    'workflow_id' => $wrkflow_id,
                                    'app_group_apps_id' => $externalUser->app_group_apps_id,
                                    //'active_device_id' => $externalUser->active_device_id,
                                    'from_email' => $fromAddress,
                                    'subject' => $push_title,
                                    'message' => $push_message,
                                    'emailSentOn' => $workflowhistoryDate,
                                    'emailSentByUser' => $businessId,
                                    'groupid' => '',
                                    'opened' => 0,
                                    'openTime' => '',
                                    'active' => '1',
                                    'created_on' => date('Y-m-d H:i:s')
                                );




                            }
                        }
                    }
                }//loop end
                // echo "<pre>"; print_r($records); die;

                if(isset($records) && !empty($records))
                    $this->workflow_model->insertBrandEmailCampaignInfo($records);
            }

            $update['wrkflow_history_timeId'] = $wrkflow_history_timeId;
            $update['isProcess'] = 1;
            $this->workflow_model->updateWorkflowHistoryTime($update);

        }
    }

    /*
    * function is used to make entry in inapp_notification_send_history table to send In app notification.
    */
    function putInAppNotification($workflowHistory = null, $chunckSize)
    {
        if($workflowHistory == null)
            return false;
        $records = array();
        unset($records);
        $wrkflow_history_timeId = $workflowHistory->wrkflow_history_timeId;
        $wrkflow_time = $workflowHistory->wrkflow_time;
        $wrkflow_id = $workflowHistory->wrkflow_id;
        $where['wrkflow_historyId'] = $workflowHistory->wrkflow_historyId;
        $where['wrkflow_id'] = $workflowHistory->wrkflow_id;
        $getHistory = $this->workflow_model->getWorkflowHistory($where);
        if(count($getHistory) > 0){
            $wrkflow_id = $getHistory->wrkflow_id;
            $externalUsersIds = explode(",",$getHistory->external_userid_text);

            $newWorkflow = $this->workflow_model->getWorkflowEmailCampaignId($wrkflow_id, $workflowHistory->wrkflow_delay_id, 'in-app');
        }
        if(count($newWorkflow) > 0){
            $wrkflow_inAppNotification = $newWorkflow->wrkflow_inAppNotification;
            $inAppRow = $this->inapp_model->getCampaign($wrkflow_inAppNotification);
            $countUsers = count($externalUsersIds);
            if($countUsers > $chunckSize)
            {
                $beginCounter = intval($countUsers / $chunckSize);
                for ($count = 0; $count < $beginCounter; $count++) {
                    unset($records);
                    for ($i = 0; $i < $chunckSize; $i++) {
                        $indexer = $i + ($count * $chunckSize);
                        $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                        $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';
                        if($externalUser != null) {
                            $inappNotificationResult = $this->workflow_model->getAllExternalUsersDevicesSingle($externalUsersIds[$indexer]);
                            foreach($inappNotificationResult as $inappNotification) {
                                if ($workflowHistory->type == 'DT' && $inappNotification != null) {
                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $wrkflow_time,
                                        'notification_timezone_send_time' => $wrkflow_time,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                                if ($workflowHistory->type == 'ID' && $inappNotification != null) {

                                    if ($workflowHistory->days == '' && $workflowHistory->time_between == '') {
                                        $records[] = array(
                                            'message_id' => $wrkflow_inAppNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $inappNotification->external_user_id,
                                            'user_id' => $inappNotification->businessId,
                                            'platform' => $inAppRow->device_type,
                                            'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                            'active_device_id' => $inappNotification->active_device_id,
                                            'deviceToken' => $inappNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );
                                    }
                                    if ($workflowHistory->days != '' && $workflowHistory->time_between == '') { //die("1");
                                        if (strpos($workflowHistory->days, "days") !== false) {
                                            $increase = trim(str_replace("days", "", $workflowHistory->days));
                                            $dateString = date('Y-m-d H:i:s', strtotime("+" . $increase . " day", strtotime($dateString)));
                                        } elseif (strpos($workflowHistory->days, "month") !== false) {
                                            $increase = trim(str_replace("month", "", $workflowHistory->days));
                                            $dateString = date('Y-m-d H:i:s', strtotime("+" . $increase . " month", strtotime($dateString)));
                                        }

                                        $records[] = array(
                                            'message_id' => $wrkflow_inAppNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $inappNotification->external_user_id,
                                            'user_id' => $inappNotification->businessId,
                                            'platform' => $inAppRow->device_type,
                                            'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                            'active_device_id' => $inappNotification->active_device_id,
                                            'deviceToken' => $inappNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );


                                        $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                        $records[] = array(
                                            'message_id' => $wrkflow_inAppNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $inappNotification->external_user_id,
                                            'user_id' => $inappNotification->businessId,
                                            'platform' => $inAppRow->device_type,
                                            'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                            'active_device_id' => $inappNotification->active_device_id,
                                            'deviceToken' => $inappNotification->push_notification_token,
                                            'notification_send_time' => $dateString2,
                                            'notification_timezone_send_time' => $dateString2,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                    if ($workflowHistory->days == '' && $workflowHistory->time_between != '') {
                                        $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                        $timeDelay = explode('-', $workflowHistory->time_between);
                                        $timeDelay = $timeDelay[0];
                                        $dateString = $dateString . ' ' . $timeDelay . ':00';
                                        if (strtotime("now " . $usertimezone) < strtotime($dateString . ' ' . $usertimezone)) {
                                            $hourse = (strtotime($dateString . " " . $usertimezone) - strtotime("now " . $usertimezone)) / 3600;
                                            if ($hourse < 1)
                                                $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                            else
                                                $dateString = date("Y-m-d H:i:s", strtotime($dateString . " " . $usertimezone));
                                        } else {
                                            $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                        }
                                        $records[] = array(
                                            'message_id' => $wrkflow_inAppNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $inappNotification->external_user_id,
                                            'user_id' => $inappNotification->businessId,
                                            'platform' => $inAppRow->device_type,
                                            'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                            'active_device_id' => $inappNotification->active_device_id,
                                            'deviceToken' => $inappNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                    if ($workflowHistory->days != '' && $workflowHistory->time_between != '') {
                                        $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                        $timeDelay = explode('-', $workflowHistory->time_between);
                                        $timeDelay = $timeDelay[0];
                                        $dateString1 = $dateString . ' ' . $timeDelay . ':00';
                                        $timeDelay1 = $timeDelay[1];
                                        $dateString2 = $dateString . ' ' . $timeDelay1 . ':00';
                                        if (strtotime("now " . $usertimezone) < strtotime($dateString2 . ' ' . $usertimezone)) {
                                            $hourse = (strtotime($dateString1 . " " . $usertimezone) - strtotime("now " . $usertimezone)) / 3600;
                                            if ($hourse < 1)
                                                $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                            else
                                                $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 . " " . $usertimezone));
                                        } else {
                                            $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                        }

                                        $records[] = array(
                                            'message_id' => $wrkflow_inAppNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $inappNotification->external_user_id,
                                            'user_id' => $inappNotification->businessId,
                                            'platform' => $inAppRow->device_type,
                                            'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                            'active_device_id' => $inappNotification->active_device_id,
                                            'deviceToken' => $inappNotification->push_notification_token,
                                            'notification_send_time' => $dateString1,
                                            'notification_timezone_send_time' => $dateString1,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );


                                        $workflowhistoryDate = date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                        if (strpos($workflowHistory->days, "days") !== false) {
                                            $increase = trim(str_replace("days", "", $workflowHistory->days));
                                            $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+" . $increase . " day", strtotime($workflowhistoryDate)));
                                        } elseif (strpos($workflowHistory->days, "month") !== false) {
                                            $increase = trim(str_replace("month", "", $workflowHistory->days));
                                            $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+" . $increase . " month", strtotime($workflowhistoryDate)));
                                        }


                                        $records[] = array(
                                            'message_id' => $wrkflow_inAppNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $inappNotification->external_user_id,
                                            'user_id' => $inappNotification->businessId,
                                            'platform' => $inAppRow->device_type,
                                            'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                            'active_device_id' => $inappNotification->active_device_id,
                                            'deviceToken' => $inappNotification->push_notification_token,
                                            'notification_send_time' => $workflowhistoryDate,
                                            'notification_timezone_send_time' => $workflowhistoryDate,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );


                                    }
                                }
                            }
                        }
                    }
                    if(isset($records) && !empty($records))
                        $this->workflow_model->insertInAppCampaignInfo($records);

                    updateCronTime('createWorkflowEntries.txt');
                }

                $lastCounter = $countUsers % $chunckSize;
                $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';

                unset($records);
                for ($count = 0; $count < $lastCounter; $count++) {
                    $indexer = $count + ($beginCounter * $chunckSize);
                    $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                    $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';

                    if($externalUser != null) {
                        $inappNotificationResult = $this->workflow_model->getAllExternalUsersDevicesSingle($externalUsersIds[$indexer]);
                        foreach($inappNotificationResult as $inappNotification) {
                            if ($workflowHistory->type == 'DT' && $inappNotification != null) {
                                $records[] = array(
                                    'message_id' => $wrkflow_inAppNotification,
                                    'workflow_id' => $wrkflow_id,
                                    'external_user_id' => $inappNotification->external_user_id,
                                    'user_id' => $inappNotification->businessId,
                                    'platform' => $inAppRow->device_type,
                                    'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                    'active_device_id' => $inappNotification->active_device_id,
                                    'deviceToken' => $inappNotification->push_notification_token,
                                    'notification_send_time' => $wrkflow_time,
                                    'notification_timezone_send_time' => $wrkflow_time,
                                    'is_send' => 0,
                                    'createdDate' => date('Y-m-d H:i:s')
                                );
                            }
                            $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                            if ($workflowHistory->type == 'ID' && $inappNotification != null) {

                                if ($workflowHistory->days == '' && $workflowHistory->time_between == '') {
                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString,
                                        'notification_timezone_send_time' => $dateString,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                if ($workflowHistory->days != '' && $workflowHistory->time_between == '') { //die("1");
                                    if (strpos($workflowHistory->days, "days") !== false) {
                                        $increase = trim(str_replace("days", "", $workflowHistory->days));
                                        $dateString = date('Y-m-d H:i:s', strtotime("+" . $increase . " day", strtotime($dateString)));
                                    } elseif (strpos($workflowHistory->days, "month") !== false) {
                                        $increase = trim(str_replace("month", "", $workflowHistory->days));
                                        $dateString = date('Y-m-d H:i:s', strtotime("+" . $increase . " month", strtotime($dateString)));
                                    }

                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString,
                                        'notification_timezone_send_time' => $dateString,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );


                                    $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString2,
                                        'notification_timezone_send_time' => $dateString2,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );

                                }
                                if ($workflowHistory->days == '' && $workflowHistory->time_between != '') {
                                    $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                    $timeDelay = explode('-', $workflowHistory->time_between);
                                    $timeDelay = $timeDelay[0];
                                    $dateString = $dateString . ' ' . $timeDelay . ':00';
                                    if (strtotime("now " . $usertimezone) < strtotime($dateString . ' ' . $usertimezone)) {
                                        $hourse = (strtotime($dateString . " " . $usertimezone) - strtotime("now " . $usertimezone)) / 3600;
                                        if ($hourse < 1)
                                            $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                        else
                                            $dateString = date("Y-m-d H:i:s", strtotime($dateString . " " . $usertimezone));
                                    } else {
                                        $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                    }
                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString,
                                        'notification_timezone_send_time' => $dateString,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );

                                }
                                if ($workflowHistory->days != '' && $workflowHistory->time_between != '') {
                                    $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                    $timeDelay = explode('-', $workflowHistory->time_between);
                                    $timeDelay = $timeDelay[0];
                                    $dateString1 = $dateString . ' ' . $timeDelay . ':00';
                                    $timeDelay1 = $timeDelay[1];
                                    $dateString2 = $dateString . ' ' . $timeDelay1 . ':00';
                                    if (strtotime("now " . $usertimezone) < strtotime($dateString2 . ' ' . $usertimezone)) {
                                        $hourse = (strtotime($dateString1 . " " . $usertimezone) - strtotime("now " . $usertimezone)) / 3600;
                                        if ($hourse < 1)
                                            $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                        else
                                            $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 . " " . $usertimezone));
                                    } else {
                                        $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                    }

                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString1,
                                        'notification_timezone_send_time' => $dateString1,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );


                                    $workflowhistoryDate = date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                    if (strpos($workflowHistory->days, "days") !== false) {
                                        $increase = trim(str_replace("days", "", $workflowHistory->days));
                                        $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+" . $increase . " day", strtotime($workflowhistoryDate)));
                                    } elseif (strpos($workflowHistory->days, "month") !== false) {
                                        $increase = trim(str_replace("month", "", $workflowHistory->days));
                                        $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+" . $increase . " month", strtotime($workflowhistoryDate)));
                                    }


                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $workflowhistoryDate,
                                        'notification_timezone_send_time' => $workflowhistoryDate,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );


                                }
                            }
                        }
                    }

                }
                if(isset($records) && !empty($records))
                    $this->workflow_model->insertInAppCampaignInfo($records);
                updateCronTime('createWorkflowEntries.txt');
            }
            else
            {
                for ($count = 0; $count < $countUsers; $count++) {
                    $indexer = $count;
                    $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                    $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';
                    if($externalUser != null) {
                        $inappNotificationResult = $this->workflow_model->getAllExternalUsersDevicesSingle($externalUsersIds[$indexer]);
                        foreach($inappNotificationResult as $inappNotification) {
                            if ($workflowHistory->type == 'DT' && $inappNotification != null) {
                                $records[] = array(
                                    'message_id' => $wrkflow_inAppNotification,
                                    'workflow_id' => $wrkflow_id,
                                    'external_user_id' => $inappNotification->external_user_id,
                                    'user_id' => $inappNotification->businessId,
                                    'platform' => $inAppRow->device_type,
                                    'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                    'active_device_id' => $inappNotification->active_device_id,
                                    'deviceToken' => $inappNotification->push_notification_token,
                                    'notification_send_time' => $wrkflow_time,
                                    'notification_timezone_send_time' => $wrkflow_time,
                                    'is_send' => 0,
                                    'createdDate' => date('Y-m-d H:i:s')
                                );
                            }
                            $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                            if ($workflowHistory->type == 'ID' && $inappNotification != null) {

                                if ($workflowHistory->days == '' && $workflowHistory->time_between == '') {
                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString,
                                        'notification_timezone_send_time' => $dateString,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                if ($workflowHistory->days != '' && $workflowHistory->time_between == '') { //die("1");
                                    if (strpos($workflowHistory->days, "days") !== false) {
                                        $increase = trim(str_replace("days", "", $workflowHistory->days));
                                        $dateString = date('Y-m-d H:i:s', strtotime("+" . $increase . " day", strtotime($dateString)));
                                    } elseif (strpos($workflowHistory->days, "month") !== false) {
                                        $increase = trim(str_replace("month", "", $workflowHistory->days));
                                        $dateString = date('Y-m-d H:i:s', strtotime("+" . $increase . " month", strtotime($dateString)));
                                    }

                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString,
                                        'notification_timezone_send_time' => $dateString,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );


                                    $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString2,
                                        'notification_timezone_send_time' => $dateString2,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );

                                }
                                if ($workflowHistory->days == '' && $workflowHistory->time_between != '') {
                                    $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                    $timeDelay = explode('-', $workflowHistory->time_between);
                                    $timeDelay = $timeDelay[0];
                                    $dateString = $dateString . ' ' . $timeDelay . ':00';
                                    if (strtotime("now " . $usertimezone) < strtotime($dateString . ' ' . $usertimezone)) {
                                        $hourse = (strtotime($dateString . " " . $usertimezone) - strtotime("now " . $usertimezone)) / 3600;
                                        if ($hourse < 1)
                                            $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                        else
                                            $dateString = date("Y-m-d H:i:s", strtotime($dateString . " " . $usertimezone));
                                    } else {
                                        $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                    }
                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString,
                                        'notification_timezone_send_time' => $dateString,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );

                                }
                                if ($workflowHistory->days != '' && $workflowHistory->time_between != '') {
                                    $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                    $timeDelay = explode('-', $workflowHistory->time_between);
                                    $timeDelay = $timeDelay[0];
                                    $dateString1 = $dateString . ' ' . $timeDelay . ':00';
                                    $timeDelay1 = $timeDelay[1];
                                    $dateString2 = $dateString . ' ' . $timeDelay1 . ':00';
                                    if (strtotime("now " . $usertimezone) < strtotime($dateString2 . ' ' . $usertimezone)) {
                                        $hourse = (strtotime($dateString1 . " " . $usertimezone) - strtotime("now " . $usertimezone)) / 3600;
                                        if ($hourse < 1)
                                            $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                        else
                                            $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 . " " . $usertimezone));
                                    } else {
                                        $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                    }

                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $dateString1,
                                        'notification_timezone_send_time' => $dateString1,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );


                                    $workflowhistoryDate = date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                    if (strpos($workflowHistory->days, "days") !== false) {
                                        $increase = trim(str_replace("days", "", $workflowHistory->days));
                                        $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+" . $increase . " day", strtotime($workflowhistoryDate)));
                                    } elseif (strpos($workflowHistory->days, "month") !== false) {
                                        $increase = trim(str_replace("month", "", $workflowHistory->days));
                                        $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+" . $increase . " month", strtotime($workflowhistoryDate)));
                                    }


                                    $records[] = array(
                                        'message_id' => $wrkflow_inAppNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $inappNotification->external_user_id,
                                        'user_id' => $inappNotification->businessId,
                                        'platform' => $inAppRow->device_type,
                                        'app_group_apps_id' => $inappNotification->app_group_apps_id,
                                        'active_device_id' => $inappNotification->active_device_id,
                                        'deviceToken' => $inappNotification->push_notification_token,
                                        'notification_send_time' => $workflowhistoryDate,
                                        'notification_timezone_send_time' => $workflowhistoryDate,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );


                                }
                            }
                        }
                    }
                }

                if(isset($records) && !empty($records))
                    $this->workflow_model->insertInAppCampaignInfo($records);
                updateCronTime('createWorkflowEntries.txt');
            }

            $update['wrkflow_history_timeId'] = $wrkflow_history_timeId;
            $update['isProcess'] = 1;
            $this->workflow_model->updateWorkflowHistoryTime($update);

        }
    }

    /*
    * function used for notification_send_history table entry.
    */
    function pushNotificationEntry($workflowHistory = null, $chunckSize)
    {
        if($workflowHistory == null)
            return false;
        $records = array();
        unset($records);
        $wrkflow_history_timeId = $workflowHistory->wrkflow_history_timeId;
        $wrkflow_time = $workflowHistory->wrkflow_time;
        $wrkflow_id = $workflowHistory->wrkflow_id;
        $where['wrkflow_historyId'] = $workflowHistory->wrkflow_historyId;
        $where['wrkflow_id'] = $workflowHistory->wrkflow_id;
        $getHistory = $this->workflow_model->getWorkflowHistory($where);
        if(count($getHistory) > 0){
            $wrkflow_id = $getHistory->wrkflow_id;
            $externalUsersIds = explode(",",$getHistory->external_userid_text);

            $newWorkflow = $this->workflow_model->getWorkflowEmailCampaignId($wrkflow_id, $workflowHistory->wrkflow_delay_id, 'push');
        }

        if(count($newWorkflow) > 0){
            if(!empty($newWorkflow->wrkflow_iOSNotification)){
                $wrkflow_pushNotification = $newWorkflow->wrkflow_iOSNotification;
            }else{
                $wrkflow_pushNotification = $newWorkflow->wrkflow_androidNotification;
            }
            $pushRow = $this->campaign_model->getCampaign($wrkflow_pushNotification);
            $countUsers = count($externalUsersIds);
            if($countUsers > $chunckSize)
            {
                $beginCounter = intval($countUsers / $chunckSize);
                for ($count = 0; $count < $beginCounter; $count++) {
                    unset($records);
                    for ($i = 0; $i < $chunckSize; $i++) {
                        $indexer = $i + ($count * $chunckSize);
                        $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                        $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';
                        if($externalUser != null) {
                            $pushNotificationResult = $this->workflow_model->getAllExternalUsersDevicesSingle($externalUsersIds[$indexer]);
                            if(count($pushNotificationResult) > 0){
                                foreach($pushNotificationResult as $pushNotification){
                                    if ($workflowHistory->type == 'DT' && $pushNotification != null){
                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $wrkflow_time,
                                            'notification_timezone_send_time' => $wrkflow_time,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );
                                    }
                                    $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                                    if($workflowHistory->type == 'ID' && $pushNotification != null)
                                    {

                                        if($workflowHistory->days == '' && $workflowHistory->time_between == '' ){
                                            $records[] = array(
                                                'campaign_id' => $wrkflow_pushNotification,
                                                'workflow_id' => $wrkflow_id,
                                                'external_user_id' => $pushNotification->external_user_id,
                                                'user_id' => $pushNotification->businessId,
                                                'platform' => $pushRow->platform,
                                                'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                                'active_device_id' => $pushNotification->active_device_id,
                                                'deviceToken' => $pushNotification->push_notification_token,
                                                'notification_send_time' => $dateString,
                                                'notification_timezone_send_time' => $dateString,
                                                'is_send' => 0,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );
                                        }
                                        if($workflowHistory->days != '' && $workflowHistory->time_between == ''){ //die("1");
                                            if(strpos($workflowHistory->days, "days")!== false)
                                            {
                                                $increase = trim(str_replace("days", "", $workflowHistory->days));
                                                $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($dateString)));
                                            }
                                            elseif (strpos($workflowHistory->days, "month") !== false)
                                            {
                                                $increase = trim(str_replace("month", "", $workflowHistory->days));
                                                $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($dateString)));
                                            }

                                            $records[] = array(
                                                'campaign_id' => $wrkflow_pushNotification,
                                                'workflow_id' => $wrkflow_id,
                                                'external_user_id' => $pushNotification->external_user_id,
                                                'user_id' => $pushNotification->businessId,
                                                'platform' => $pushRow->platform,
                                                'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                                'active_device_id' => $pushNotification->active_device_id,
                                                'deviceToken' => $pushNotification->push_notification_token,
                                                'notification_send_time' => $dateString,
                                                'notification_timezone_send_time' => $dateString,
                                                'is_send' => 0,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );


                                            $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                            $records[] = array(
                                                'campaign_id' => $wrkflow_pushNotification,
                                                'workflow_id' => $wrkflow_id,
                                                'external_user_id' => $pushNotification->external_user_id,
                                                'user_id' => $pushNotification->businessId,
                                                'platform' => $pushRow->platform,
                                                'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                                'active_device_id' => $pushNotification->active_device_id,
                                                'deviceToken' => $pushNotification->push_notification_token,
                                                'notification_send_time' => $dateString2,
                                                'notification_timezone_send_time' => $dateString2,
                                                'is_send' => 0,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );

                                        }
                                        if($workflowHistory->days == '' && $workflowHistory->time_between != '' )
                                        {
                                            $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                            $timeDelay = explode('-',$workflowHistory->time_between);
                                            $timeDelay = $timeDelay[0];
                                            $dateString = $dateString.' '.$timeDelay.':00';
                                            if(strtotime("now ". $usertimezone) < strtotime($dateString .' '.$usertimezone))
                                            {
                                                $hourse = (strtotime($dateString." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                                if($hourse < 1)
                                                    $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                                else
                                                    $dateString = date("Y-m-d H:i:s", strtotime($dateString ." ".$usertimezone));
                                            }
                                            else{
                                                $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                            }
                                            $records[] = array(
                                                'campaign_id' => $wrkflow_pushNotification,
                                                'workflow_id' => $wrkflow_id,
                                                'external_user_id' => $pushNotification->external_user_id,
                                                'user_id' => $pushNotification->businessId,
                                                'platform' => $pushRow->platform,
                                                'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                                'active_device_id' => $pushNotification->active_device_id,
                                                'deviceToken' => $pushNotification->push_notification_token,
                                                'notification_send_time' => $dateString,
                                                'notification_timezone_send_time' => $dateString,
                                                'is_send' => 0,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );

                                        }
                                        if($workflowHistory->days != '' && $workflowHistory->time_between != '' )
                                        {
                                            $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                            $timeDelay = explode('-',$workflowHistory->time_between);
                                            $timeDelay = $timeDelay[0];
                                            $dateString1 = $dateString.' '.$timeDelay.':00';
                                            $timeDelay1 = $timeDelay[1];
                                            $dateString2 = $dateString.' '.$timeDelay1.':00';
                                            if(strtotime("now ". $usertimezone) < strtotime($dateString2 .' '.$usertimezone))
                                            {
                                                $hourse = (strtotime($dateString1." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                                if($hourse < 1)
                                                    $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                                else
                                                    $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 ." ".$usertimezone));
                                            }else{
                                                $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                            }

                                            $records[] = array(
                                                'campaign_id' => $wrkflow_pushNotification,
                                                'workflow_id' => $wrkflow_id,
                                                'external_user_id' => $pushNotification->external_user_id,
                                                'user_id' => $pushNotification->businessId,
                                                'platform' => $pushRow->platform,
                                                'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                                'active_device_id' => $pushNotification->active_device_id,
                                                'deviceToken' => $pushNotification->push_notification_token,
                                                'notification_send_time' => $dateString1,
                                                'notification_timezone_send_time' => $dateString1,
                                                'is_send' => 0,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );

                                            $workflowhistoryDate =  date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                            if(strpos($workflowHistory->days, "days")!== false)
                                            {
                                                $increase = trim(str_replace("days", "", $workflowHistory->days));
                                                $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($workflowhistoryDate)));
                                            }
                                            elseif (strpos($workflowHistory->days, "month") !== false)
                                            {
                                                $increase = trim(str_replace("month", "", $workflowHistory->days));
                                                $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($workflowhistoryDate)));
                                            }


                                            $records[] = array(
                                                'campaign_id' => $wrkflow_pushNotification,
                                                'workflow_id' => $wrkflow_id,
                                                'external_user_id' => $pushNotification->external_user_id,
                                                'user_id' => $pushNotification->businessId,
                                                'platform' => $pushRow->platform,
                                                'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                                'active_device_id' => $pushNotification->active_device_id,
                                                'deviceToken' => $pushNotification->push_notification_token,
                                                'notification_send_time' => $workflowhistoryDate,
                                                'notification_timezone_send_time' => $workflowhistoryDate,
                                                'is_send' => 0,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );

                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(isset($records) && !empty($records))
                        $this->brand_model->saveNotificationHistory($records);

                    updateCronTime('createWorkflowEntries.txt');
                }

                $lastCounter = $countUsers % $chunckSize;
                $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';

                unset($records);
                for ($count = 0; $count < $lastCounter; $count++) {
                    $indexer = $count + ($beginCounter * $chunckSize);
                    $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                    $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';

                    if($externalUser != null) {
                        $pushNotification = $this->workflow_model->getAllExternalUsersDevicesSingle($externalUsersIds[$indexer]);
                        if(count($pushNotificationResult) > 0){
                            foreach($pushNotificationResult as $pushNotification){
                                if ($workflowHistory->type == 'DT' && $pushNotification != null){
                                    $records[] = array(
                                        'campaign_id' => $wrkflow_pushNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $pushNotification->external_user_id,
                                        'user_id' => $pushNotification->businessId,
                                        'platform' => $pushRow->platform,
                                        'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                        'active_device_id' => $pushNotification->active_device_id,
                                        'deviceToken' => $pushNotification->push_notification_token,
                                        'notification_send_time' => $wrkflow_time,
                                        'notification_timezone_send_time' => $wrkflow_time,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                                if($workflowHistory->type == 'ID' && $pushNotification != null)
                                {

                                    if($workflowHistory->days == '' && $workflowHistory->time_between == '' ){
                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );
                                    }
                                    if($workflowHistory->days != '' && $workflowHistory->time_between == ''){ //die("1");
                                        if(strpos($workflowHistory->days, "days")!== false)
                                        {
                                            $increase = trim(str_replace("days", "", $workflowHistory->days));
                                            $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($dateString)));
                                        }
                                        elseif (strpos($workflowHistory->days, "month") !== false)
                                        {
                                            $increase = trim(str_replace("month", "", $workflowHistory->days));
                                            $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($dateString)));
                                        }

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );


                                        $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString2,
                                            'notification_timezone_send_time' => $dateString2,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                    if($workflowHistory->days == '' && $workflowHistory->time_between != '' )
                                    {
                                        $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                        $timeDelay = explode('-',$workflowHistory->time_between);
                                        $timeDelay = $timeDelay[0];
                                        $dateString = $dateString.' '.$timeDelay.':00';
                                        if(strtotime("now ". $usertimezone) < strtotime($dateString .' '.$usertimezone))
                                        {
                                            $hourse = (strtotime($dateString." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                            if($hourse < 1)
                                                $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                            else
                                                $dateString = date("Y-m-d H:i:s", strtotime($dateString ." ".$usertimezone));
                                        }
                                        else{
                                            $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                        }
                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                    if($workflowHistory->days != '' && $workflowHistory->time_between != '' )
                                    {
                                        $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                        $timeDelay = explode('-',$workflowHistory->time_between);
                                        $timeDelay = $timeDelay[0];
                                        $dateString1 = $dateString.' '.$timeDelay.':00';
                                        $timeDelay1 = $timeDelay[1];
                                        $dateString2 = $dateString.' '.$timeDelay1.':00';
                                        if(strtotime("now ". $usertimezone) < strtotime($dateString2 .' '.$usertimezone))
                                        {
                                            $hourse = (strtotime($dateString1." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                            if($hourse < 1)
                                                $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                            else
                                                $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 ." ".$usertimezone));
                                        }else{
                                            $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                        }

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString1,
                                            'notification_timezone_send_time' => $dateString1,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                        $workflowhistoryDate =  date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                        if(strpos($workflowHistory->days, "days")!== false)
                                        {
                                            $increase = trim(str_replace("days", "", $workflowHistory->days));
                                            $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($workflowhistoryDate)));
                                        }
                                        elseif (strpos($workflowHistory->days, "month") !== false)
                                        {
                                            $increase = trim(str_replace("month", "", $workflowHistory->days));
                                            $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($workflowhistoryDate)));
                                        }

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $workflowhistoryDate,
                                            'notification_timezone_send_time' => $workflowhistoryDate,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                }
                            }
                        }
                    }

                }
                if(isset($records) && !empty($records))
                    $this->brand_model->saveNotificationHistory($records);
                updateCronTime('createWorkflowEntries.txt');
            }
            else
            {
                for ($count = 0; $count < $countUsers; $count++) {
                    $indexer = $count;
                    $externalUser = $this->workflow_model->getExternalUser($externalUsersIds[$indexer]);
                    $usertimezone = isset($externalUser->timezone) && !empty($externalUser->timezone)? $this->getTimeZone($externalUser->timezone):'UTC';
                    if($externalUser != null) {
                        $pushNotificationResult = $this->workflow_model->getAllExternalUsersDevicesSingle($externalUsersIds[$indexer]);
                        if(count($pushNotificationResult) > 0){
                            foreach($pushNotificationResult as $pushNotification){
                                if ($workflowHistory->type == 'DT' && $pushNotification != null){
                                    $records[] = array(
                                        'campaign_id' => $wrkflow_pushNotification,
                                        'workflow_id' => $wrkflow_id,
                                        'external_user_id' => $pushNotification->external_user_id,
                                        'user_id' => $pushNotification->businessId,
                                        'platform' => $pushRow->platform,
                                        'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                        'active_device_id' => $pushNotification->active_device_id,
                                        'deviceToken' => $pushNotification->push_notification_token,
                                        'notification_send_time' => $wrkflow_time,
                                        'notification_timezone_send_time' => $wrkflow_time,
                                        'is_send' => 0,
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                                }
                                $dateString = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));
                                if($workflowHistory->type == 'ID' && $pushNotification != null)
                                {

                                    if($workflowHistory->days == '' && $workflowHistory->time_between == '' ){
                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );
                                    }
                                    if($workflowHistory->days != '' && $workflowHistory->time_between == ''){ //die("1");
                                        if(strpos($workflowHistory->days, "days")!== false)
                                        {
                                            $increase = trim(str_replace("days", "", $workflowHistory->days));
                                            $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($dateString)));
                                        }
                                        elseif (strpos($workflowHistory->days, "month") !== false)
                                        {
                                            $increase = trim(str_replace("month", "", $workflowHistory->days));
                                            $dateString = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($dateString)));
                                        }

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );


                                        $dateString2 = date('Y-m-d H:i:s', strtotime($workflowHistory->wrkflow_time));

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString2,
                                            'notification_timezone_send_time' => $dateString2,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                    if($workflowHistory->days == '' && $workflowHistory->time_between != '' )
                                    {
                                        $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                        $timeDelay = explode('-',$workflowHistory->time_between);
                                        $timeDelay = $timeDelay[0];
                                        $dateString = $dateString.' '.$timeDelay.':00';
                                        if(strtotime("now ". $usertimezone) < strtotime($dateString .' '.$usertimezone))
                                        {
                                            $hourse = (strtotime($dateString." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                            if($hourse < 1)
                                                $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                            else
                                                $dateString = date("Y-m-d H:i:s", strtotime($dateString ." ".$usertimezone));
                                        }
                                        else{
                                            $dateString = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString)));
                                        }
                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString,
                                            'notification_timezone_send_time' => $dateString,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                    if($workflowHistory->days != '' && $workflowHistory->time_between != '' )
                                    {
                                        $dateString = date('Y-m-d', strtotime($workflowHistory->wrkflow_time));
                                        $timeDelay = explode('-',$workflowHistory->time_between);
                                        $timeDelay = $timeDelay[0];
                                        $dateString1 = $dateString.' '.$timeDelay.':00';
                                        $timeDelay1 = $timeDelay[1];
                                        $dateString2 = $dateString.' '.$timeDelay1.':00';
                                        if(strtotime("now ". $usertimezone) < strtotime($dateString2 .' '.$usertimezone))
                                        {
                                            $hourse = (strtotime($dateString1." ".$usertimezone) - strtotime("now ". $usertimezone))/3600;
                                            if($hourse < 1)
                                                $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                            else
                                                $dateString1 = date("Y-m-d H:i:s", strtotime($dateString1 ." ".$usertimezone));
                                        }else{
                                            $dateString1 = date('Y-m-d H:i:s', strtotime("+1 day", strtotime($dateString1)));
                                        }

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $dateString1,
                                            'notification_timezone_send_time' => $dateString1,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                        $workflowhistoryDate =  date("Y-m-d", strtotime($workflowHistory->wrkflow_time));

                                        if(strpos($workflowHistory->days, "days")!== false)
                                        {
                                            $increase = trim(str_replace("days", "", $workflowHistory->days));
                                            $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." day", strtotime($workflowhistoryDate)));
                                        }
                                        elseif (strpos($workflowHistory->days, "month") !== false)
                                        {
                                            $increase = trim(str_replace("month", "", $workflowHistory->days));
                                            $workflowhistoryDate = date('Y-m-d H:i:s', strtotime("+".$increase." month", strtotime($workflowhistoryDate)));
                                        }

                                        $records[] = array(
                                            'campaign_id' => $wrkflow_pushNotification,
                                            'workflow_id' => $wrkflow_id,
                                            'external_user_id' => $pushNotification->external_user_id,
                                            'user_id' => $pushNotification->businessId,
                                            'platform' => $pushRow->platform,
                                            'app_group_apps_id' => $pushNotification->app_group_apps_id,
                                            'active_device_id' => $pushNotification->active_device_id,
                                            'deviceToken' => $pushNotification->push_notification_token,
                                            'notification_send_time' => $workflowhistoryDate,
                                            'notification_timezone_send_time' => $workflowhistoryDate,
                                            'is_send' => 0,
                                            'createdDate' => date('Y-m-d H:i:s')
                                        );

                                    }
                                }
                            }
                        }
                    }
                }

                if(isset($records) && !empty($records))
                    $this->brand_model->saveNotificationHistory($records);
                updateCronTime('createWorkflowEntries.txt');
            }

            $update['wrkflow_history_timeId'] = $wrkflow_history_timeId;
            $update['isProcess'] = 1;
            $this->workflow_model->updateWorkflowHistoryTime($update);

        }
    }

    /*
    * callback function used by sparkpost. Function update the colums of brand_email_campaigns_info according to events happens
     * to a email.
    */
    public function sparkpostWebhook(){
        $raw_post_data = file_get_contents('php://input');

        $insert['json'] = $raw_post_data;
        $insert['createdDate'] = date('Y-m-d H:i:s');
        $this->brand_model->saveSendGridHistory($insert);

        $sendGridNotificationsArray = json_decode($raw_post_data);
        $sparkPostArray = array_chunk($sendGridNotificationsArray, 100);
        //echo '<pre>';
        //print_r($sparkPostArray); die;
        $i=0;
        foreach($sparkPostArray as $sparkArray){
            $processedEmail = array();
            $deliveredEmail = array();
            $deferredEmail = array();
            $openEmail = array();
            $clickEmail = array();
            $bounceEmail = array();
            $bounceEmailArray = array();
            $droppedEmail = array();
            $spamreportEmail = array();
            $unsubscribeEmail = array();
            $groupUnsubscribeEmail = array();
            $groupResubscribeEmail = array();
            $spamArray = array();
            $generationRejection = array();
            $link_unsubscribe = array();
            $linkUnsubsribeArray = array();
            $list_unsubscribe = array();
            $listUnsubsribeArray = array();
            $hardBounceArray = array();
            $suspensionArray = array();
            $generationFailure = array();
            $generationFailureArray = array();
            $outOfBand = array();
            $outOfBandArray = array();
            //echo '<pre>';
            //echo count($sparkArray);
            //print_r($sparkArray); die;
            //print_r($sparkArray[$i]->msys->message_event->type); die;
            for($j=0;$j<=count($sparkArray);$j++){

                if (isset($sparkArray[$j]->msys->message_event->type) && $sparkArray[$j]->msys->message_event->type == 'bounce') {
                    //$bounceEmail[] = $sendGridNotification->msys->message_event->rcpt_to;
                    $bounceEmail[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->message_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->message_event->rcpt_to,
                        'is_bounced' => 1
                    );
                     $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->message_event->transmission_id,$sparkArray[$j]->msys->message_event->rcpt_to);
                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);
                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->message_event->rcpt_to);
                        $this->db->where('type','bounced');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();
                        if(count($result) == 0){
                            $bounceEmailArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->message_event->rcpt_to,
                                'unsubscribe' => 1,
                                'businessId' => $rowBusinessId->businessId,
                                'type' => 'bounced',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }

                    }
                }
                elseif(isset($sparkArray[$j]->msys->message_event->type) && $sparkArray[$j]->msys->message_event->type == 'out_of_band'){
                    $outOfBand[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->message_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->message_event->rcpt_to,
                        'out_of_band' => 1
                    );

                    $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->message_event->transmission_id,$sparkArray[$j]->msys->message_event->rcpt_to);
                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);
                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->message_event->rcpt_to);
                        $this->db->where('type','out_of_band');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();
                        if(count($result) == 0){
                            $outOfBandArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->message_event->rcpt_to,
                                'unsubscribe' => 1,
                                'businessId' => $rowBusinessId->businessId,
                                'type' => 'out_of_band',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }

                    }
                }
                elseif(isset($sparkArray[$j]->msys->gen_event->type) && $sparkArray[$j]->msys->gen_event->type == 'generation_rejection'){
                    $generationRejection[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->gen_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->gen_event->rcpt_to,
                        'is_hard_bounced' => 1
                    );

                    $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->gen_event->transmission_id,$sparkArray[$j]->msys->gen_event->rcpt_to);

                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);

                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->gen_event->rcpt_to);
                        $this->db->where('type','hard_bounced');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();
                        if(count($result) == 0){
                            $hardBounceArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->gen_event->rcpt_to,
                                'unsubscribe' => 1,
                                'businessId' => $rowBusinessId->businessId,
                                'type' => 'hard_bounced',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }

                    }
                }
                elseif(isset($sparkArray[$j]->msys->gen_event->type) && $sparkArray[$j]->msys->gen_event->type == 'generation_failure'){
                    $generationFailure[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->gen_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->gen_event->rcpt_to,
                        'generation_failure' => 1
                    );

                    $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->gen_event->transmission_id,$sparkArray[$j]->msys->gen_event->rcpt_to);

                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);

                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->gen_event->rcpt_to);
                        $this->db->where('type','generation_failure');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();
                        if(count($result) == 0){
                            $generationFailureArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->gen_event->rcpt_to,
                                'unsubscribe' => 1,
                                'businessId' => $rowBusinessId->businessId,
                                'type' => 'generation_failure',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }

                    }

                }
                elseif(isset($sparkArray[$j]->msys->message_event->type) && $sparkArray[$j]->msys->message_event->type == 'delivery'){
                    $deliveredEmail[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->message_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->message_event->rcpt_to,
                        'is_received' => 1
                    );
                }
                elseif(isset($sparkArray[$j]->msys->track_event->type) && $sparkArray[$j]->msys->track_event->type == 'open'){
                    $openEmail[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->track_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->track_event->rcpt_to,
                        'is_opened' => 1
                    );
                }
                elseif (isset($sparkArray[$j]->msys->track_event->type) && $sparkArray[$j]->msys->track_event->type == 'click') {
                    $clickEmail[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->track_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->track_event->rcpt_to,
                        'is_clicked' => 1
                    );
                }
                elseif(isset($sparkArray[$j]->msys->message_event->type) && $sparkArray[$j]->msys->message_event->type == 'spam_complaint') {
                    $spamreportEmail[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->message_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->message_event->rcpt_to,
                        'is_spamreport' => 1
                    );

                    $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->message_event->transmission_id,$sparkArray[$j]->msys->message_event->rcpt_to);
                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);

                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->message_event->rcpt_to);
                        $this->db->where('type','spam');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();

                        if(count($result) == 0){
                            $spamArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->message_event->rcpt_to,
                                'businessId' => $rowBusinessId->businessId,
                                'unsubscribe' => 1,
                                'type' => 'spam',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }


                    }
                }
                elseif(isset($sparkArray[$j]->msys->unsubscribe_event->type) && $sparkArray[$j]->msys->unsubscribe_event->type == 'link_unsubscribe'){
                    $link_unsubscribe[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->unsubscribe_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->unsubscribe_event->rcpt_to,
                        'link_unsubscribe' => 1
                    );

                    $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->unsubscribe_event->transmission_id,$sparkArray[$j]->msys->unsubscribe_event->rcpt_to);
                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);

                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->unsubscribe_event->rcpt_to);
                        $this->db->where('type','link_unsubscribe');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();

                        if(count($result) == 0){
                            $linkUnsubsribeArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->unsubscribe_event->rcpt_to,
                                'businessId' => $rowBusinessId->businessId,
                                'unsubscribe' => 1,
                                'type' => 'link_unsubscribe',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }

                    }
                }
                elseif(isset($sparkArray[$j]->msys->unsubscribe_event->type) && $sparkArray[$j]->msys->unsubscribe_event->type == 'list_unsubscribe'){
                    $list_unsubscribe[] = array(
                        'sendgrid_message_id' => $sparkArray[$j]->msys->unsubscribe_event->transmission_id,
                        'email_id' => $sparkArray[$j]->msys->unsubscribe_event->rcpt_to,
                        'list_unsubscribe' => 1
                    );

                    $row = $this->brand_model->getBusinessIdBySendGridMsgId($sparkArray[$j]->msys->unsubscribe_event->transmission_id,$sparkArray[$j]->msys->unsubscribe_event->rcpt_to);
                    if(count($row) > 0){
                        $rowBusinessId = $this->brand_model->getBusinessIdbyCampaignId($row->campaignId);

                        $this->db->select('*');
                        $this->db->where('from_email',$sparkArray[$j]->msys->unsubscribe_event->rcpt_to);
                        $this->db->where('type','list_unsubscribe');
                        $this->db->where('app_group_id',$rowBusinessId->app_group_id);
                        $result = $this->db->get('unsubscribe_emails');
                        $result = $result->row_array();
                        if(count($result) == 0){
                            $listUnsubsribeArray[] = array(
                                'from_email' => $sparkArray[$j]->msys->unsubscribe_event->rcpt_to,
                                'businessId' => $rowBusinessId->businessId,
                                'unsubscribe' => 1,
                                'type' => 'list_unsubscribe',
                                'app_group_id' => $rowBusinessId->app_group_id,
                                'createdDate' => date('YmdHis')
                            );
                        }

                    }
                }


            }
            //echo '<pre>';
            //print_r($bounceEmail); die;
            if(count($bounceEmail) > 0){
                $this->brand_model->updateSendGridEvents($bounceEmail, 'is_bounced');
            }
            if(count($outOfBand) > 0){
                $this->brand_model->updateSendGridEvents($bounceEmail, 'out_of_band');
            }
            if(count($generationRejection)>0){
                $this->brand_model->updateSendGridEvents($generationRejection, 'is_hard_bounced');
            }
            if(count($generationFailure)>0){
                $this->brand_model->updateSendGridEvents($generationFailure, 'generation_failure');
            }
            if(count($deliveredEmail)>0){
                $this->brand_model->updateSendGridEvents($deliveredEmail,'is_received');
            }
            if(count($openEmail)>0){
                $this->brand_model->updateSendGridEvents($openEmail,'is_opened');
            }
            if(count($clickEmail)>0){
                $this->brand_model->updateSendGridEvents($clickEmail,'is_clicked');
            }
            if(count($spamreportEmail) > 0){
                $this->brand_model->updateSendGridEvents($spamreportEmail, 'is_spamreport');
            }
            if(count($link_unsubscribe) > 0){
                $this->brand_model->updateSendGridEvents($link_unsubscribe, 'link_unsubscribe');
            }
            if(count($list_unsubscribe) > 0){
                $this->brand_model->updateSendGridEvents($list_unsubscribe, 'list_unsubscribe');
            }

            // Start insert batch in unsubscribe_emails table
            if(count($bounceEmailArray) > 0){
                $this->brand_model->insertSpam($bounceEmailArray);
            }

            if(count($outOfBandArray) > 0){
                $this->brand_model->insertSpam($outOfBandArray);
            }

            if(count($spamArray) > 0){
                $this->brand_model->insertSpam($spamArray);
            }

            if(count($hardBounceArray) > 0){
                $this->brand_model->insertSpam($hardBounceArray);
            }

            if(count($generationFailureArray) > 0){
                $this->brand_model->insertSpam($generationFailureArray);
            }

            if(count($linkUnsubsribeArray) > 0){
                $this->brand_model->insertSpam($linkUnsubsribeArray);
            }

            if(count($listUnsubsribeArray) > 0){
                $this->brand_model->insertSpam($listUnsubsribeArray);
            }
            //Update query

            $i++;
        }
        //echo '<pre>';
        //print_r($bounceEmail);

        echo 'updated';

    }

    /*
    * function used to add spam, bounced emails in suspension list of sparkpost.
    */
    function updateSparkpostSuspensionList()
    {
        $this->load->helper('cron');

        $isCronActive = isCronActive('updateSparkpostSuspensionList.txt',5);
        if($isCronActive == true){
            return false;
        }
        updateCronTime('updateSparkpostSuspensionList.txt');
        $unsubscibelist = $this->brand_model->getUnsubscibeSparkPostList();
        //echo '<pre>';
        if($unsubscibelist != null)
        {
            $sparkPostArray = array_chunk($unsubscibelist, 100);
            foreach ($sparkPostArray as $unsubscibe) {
                foreach ($unsubscibe as $row) {
                    $postArray = array();
                    $batchUpdate = array();
                    $emails = explode(',', $row->emails);
                    foreach ($emails as $email) {
                        $postArray[] = array(
                            "recipient" => $email,
                            "type" => "non_transactional",
                            "description" => "User requested to not receive any non_transactional emails."
                        );
                        $batchUpdate[] = array('email' => $email, 'app_group_id' => $row->app_group_id);

                    }
                    $sparkPostKey = $row->sparkpost_key;
                    if(!is_null($sparkPostKey)) {
                        $sparkPostHistoryId = addToSparkpostSuspensionList($sparkPostKey, $postArray);
                        if ($sparkPostHistoryId)
                            $this->brand_model->updateUnsubscibeSparkPostList($batchUpdate);
                    }

                }
                updateCronTime('updateSparkpostSuspensionList.txt');
            }

        }
    }


    /*code to remove Unverified sending after 2 weeks after their creation*/
    function reSetDomainNonVerified()
    {
        $this->workflow_model->reSetDomainNonVerified();
    }

    /*
    * function used to assign persona to a workflow.
    */

    public function saveWorkflowPersona($wrkflowHistoryTimeRow = NULL, $chunckSize){

        //$wrkflow_historyId = 3;
        $wrkflow_history_timeId = $wrkflowHistoryTimeRow->wrkflow_history_timeId;
        $wrkflow_historyId = $wrkflowHistoryTimeRow->wrkflow_historyId;

        $wrkflow_delay_id = $wrkflowHistoryTimeRow->wrkflow_delay_id;
        //echo $wrkflow_historyId; die;
        $workflowHistory = $this->workflow_model->getWorkflowHistoryRow($wrkflow_historyId);
        //echo $this->db->last_query();
        //echo '<pre>';
        //print_r($workflowHistory); die;

        if(count($workflowHistory)>0){
            $wrkflow_id = $workflowHistory->wrkflow_id;
            //echo $wrkflow_id; die;
            $externalUsers = explode(',',$workflowHistory->external_userid_text);
            //echo '<pre>';
            //print_r(array_chunk($externalUsers, 2)); die;
            $externalUsersArray = array_chunk($externalUsers, $chunckSize);

            $where['wrkflow_id'] = $wrkflow_id;
            $where['wrkflow_historyId'] = $wrkflow_historyId;
            $where['wrkflow_delay_id'] = $wrkflow_delay_id;
            //$where['wrkflow_time'] = '2017-03-22'; //date('Y-m-d');
            $workflowHistoryTime = $this->workflow_model->getWorkflowHistoryTimePersona($where);
            //echo $this->db->last_query();
            //echo '<pre>';
            //print_r($workflowHistoryTime); die;
            $wrkflow_history_timeId = $workflowHistoryTime->wrkflow_history_timeId;
            if(count($workflowHistoryTime) > 0){

                $timedelays = $this->workflow_model->getWorkflowTimeDelayRow($wrkflow_delay_id);
                //echo '<pre>';
                //print_r($timedelays->wrkflow_personaNotification); die;
                $personaId = $timedelays->wrkflow_personaNotification;

                //echo count($externalUsersArray); die;
                $j = 0;
                foreach($externalUsersArray as $users){

                    //print_r($users); die;
                    $count = count($users);
                    //echo $count;
                    $i = 1;
                    callbegin:

                    $updateArray = array();
                    $assignPersonaContact = array();
                    foreach($users as $user){

                        //echo $user;
                        $externalUser = $this->workflow_model->getExternalUser($user);
                        //print_r(explode(',',$externalUser->persona_ids)); die;

                        if( strpos( $externalUser->persona_ids, $personaId ) !== false ) {
                            //echo "exists";
                            $assignPersona = $externalUser->persona_ids;
                        }else{
                            if(empty($externalUser->persona_ids)){
                                $assignPersona = $personaId;
                            }else{
                                $assignPersona = $externalUser->persona_ids.','.$personaId;
                            }

                        }

                        $updateArray[] = array(
                            'external_user_id'=>$user,
                            'persona_ids' => $assignPersona
                        );

                        $check['external_user_id'] = $user;
                        $check['persona_user_id'] = $personaId;
                        $assignPersonaEntries = $this->workflow_model->getPersonaAssignContactEntries($check);

                        //echo '<pre>';
                        //print_r($assignPersonaEntries); die;
                        if(count($assignPersonaEntries) == 0){
                            $assignPersonaContact[] = array(
                                'persona_user_id'=>$personaId,
                                'external_user_id' => $user,
                                'isDelete' => 0,
                                'createdDate' => date('Y-m-d H:i:s')
                            );
                        }

                        //echo $assignPersona; die;
                        //echo $this->db->last_query();
                        //echo '<pre>';
                        //print_r($externalUser); die;

                        $i++;
                    }

                    if($i < $count){
                        goto callbegin;
                    }
                    //echo '<pre>';
                    //print_r($updateArray);
                    //print_r($assignPersonaContact); //die;
                    if(count($updateArray) > 0){
                        //Update query
                        $this->workflow_model->updateExternalUsers($updateArray);
                    }

                    if(count($assignPersonaContact) > 0){
                        //Insert query
                        $this->workflow_model->insertAssignPersonaContacts($assignPersonaContact);
                    }

                    $j++;
                }
                //echo 'j:'.$j;
                //echo count($externalUsersArray);
                if($j == count($externalUsersArray)){

                    $update['wrkflow_history_timeId'] = $wrkflow_history_timeId;
                    $update['isProcess'] = 1;
                    $this->workflow_model->updateWorkflowHistoryTime($update);
                }
            }

        }

    }


    // function created for insert cross campaigns at launch ///
    public function insertCrossCampaignsAtLaunch() {

        $this->load->helper('cron');
        $isCronActive = isCronActive('insertCrossCampaignsAtLaunch.txt',5);
        if($isCronActive == true){
            return false;
        }
        updateCronTime('insertCrossCampaignsAtLaunch.txt');

        $results = $this->brand_model->getAllCrossCampaignsAtLaunch();  //echo '<pre>'; print_r($results); //exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $app_group_id = $result->app_group_id;
                $platform = $result->platform;
                $campaignName = $result->campaignName;
                $push_title = $result->push_title;
                $push_message = $result->push_message;
                $campaign_id = $result->id;
                $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

                if (!empty($result->displayName)) {
                    $displayName = $result->displayName;
                } else {
                    $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
                }

                if (!empty($result->fromAddress)) {
                    $fromAddress = $result->fromAddress;
                } else {
                    $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
                }

                if (!empty($result->replyToAddress)) {
                    $replyToAddress = $result->replyToAddress;
                } else {
                    $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
                }

                $notification_arr = array(
                    'campaign_id' => $campaign_id,
                    'app_group_id' => $app_group_id,
                    'platform' => $platform,
                    'notification_alert_count' => 1,
                    'createdDate' => date('Y-m-d H:i:s'),
                    'modifiedDate' => date('Y-m-d H:i:s')
                );
                $notificationRow = $this->brand_model->getNotificationByCampaignId($result->id);
                $status = "continue";

                $platformIds = '';
                $campaign_send_date_time = '';
                $platformApps = array();
                $platformIds = $this->groupapp_model->getAllApps($app_group_id);
                if (count($platformIds) > 0) {
                    $campaign_send_date_time = $platformIds[0]->createdDate;
                    foreach ($platformIds as $platformId) {
                        array_push($platformApps, $platformId->app_group_apps_id);
                    }
                    $platformIds = implode(',', $platformApps);
                    $platformIds = rtrim($platformIds, ',');
                }
                $personaAssignContactArr = array();
                $listContactArr = array();
                if (!empty($result->persona_user_id)) {
                    $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                    if (count($personaAssignContacts) > 0) {
                        foreach ($personaAssignContacts as $personaAssignContact) {
                            array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                        }
                    }
                }
                if (!empty($result->list_id)) {
                    $listContacts = getContactsByListId($result->list_id, $result->businessId);
                    if (!empty($listContacts)) {
                        $listContactArr = explode(',', $listContacts);
                    }
                }
                $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                $addtional_users = array_merge($add_int_users, $add_diff_users);
                $recent_push_device = $limit_ipad_device = $limit_ipod_iphone_device = 0;

                if ($result->delivery_type == 1) {
                    if ($result->time_based_scheduling == 1) {
                        $next_notification_send_date = $result->createdDate;
                        if (count($notificationRow) == 0) {
                            if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                                $status = "complete";
                            }
                            $notification_arr = array_merge($notification_arr, array('notification_type' => 'launch', 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                        } else {
                            $notification_arr = array_merge($notification_arr, array('notification_id' => $notificationRow[0]->notification_id, 'notification_type' => 'launch', 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                        }
                        $this->brand_model->saveNotificationDetails($notification_arr);
                    } else {
                        if ($result->time_based_scheduling == 2) {
                            $notification_type = $result->send;
                        }
                        if ($result->time_based_scheduling == 3) {
                            $notification_type = $result->intelligent_send;
                        }

                        if (count($notificationRow) == 0) {
                            $notification_send_date = $result->notification_send_date;
                            $pos = strpos($notification_send_date, "AM");
                            if ($pos !== false) {
                                $notification_send_date = explode("AM", $notification_send_date);
                                $notification_send_date = date('Y-m-d H:i:s', strtotime($notification_send_date[0]));
                            }
                            $pos = strpos($notification_send_date, "PM");
                            if ($pos !== false) {
                                $notification_send_date = explode("PM", $notification_send_date);
                                $notification_send_date = date('Y-m-d H:i:s', strtotime($notification_send_date[0] . "+12 hours"));
                            }
                            $next_notification_send_date = $notification_send_date;
                            $notification_arr = array_merge($notification_arr, array('notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date, 'status' => $status));

                            $this->brand_model->saveNotificationDetails($notification_arr);
                        }
                    }
                }

                if ($result->time_based_scheduling == 1) {
                    $segments = $result->segments;
                    $offset = 0;
                    $limit = 1000;
                    $i = 0;
                    $totalReceiveCampaignsUsers = 0;
                    $countIndex = array();
                    if (isset($segments)) {
                        $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 2);
                    }
                    $filters = $result->filters;
                    if (!empty($filters)) {
                        $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 2);
                    } //echo "<pre>"; print_r($users); //sssexit;

                    $userIds = $emailIds = array();
                    $deviceIds = $send_notitfication_history = array();
                    $flag = 0;//exit;

                    callbegin:
                    unset($send_email_notitfication_history);
                    $send_email_notitfication_history = array();
                    unset($send_notitfication_history);
                    $send_notitfication_history = array();
                    if (count($users) > 0) {
                        foreach ($users as $key => $device) {

                            if (isset($result->receiveCampaignType)) {
                                $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                                if ($result->receiveCampaignType == 1) {
                                    $i = $i + 1;
                                    if ($i > $totalReceiveCampaignsUsers) {
                                        $flag = 1; break;
                                    }
                                } else if ($result->receiveCampaignType == 2) {
                                    $totalSendCampaignsUsers = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                                    if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                        $flag = 1; break;
                                    }
                                }
                            }

                            $send_email_notitfication_history[] = array(
                                'email_id' => $device->email,
                                'hurree_version' => '3.1',
                                'userid' => $device->external_user_id,
                                'campaignId' => $campaign_id,
                                'app_group_apps_id' => $device->app_group_apps_id,
                                'active_device_id' => $device->active_device_id,
                                'from_email' => $fromAddress,
                                'replyToAddress' => $replyToAddress,
                                'subject' => $push_title,
                                'message' => $push_message,
                                'emailSentOn' => $next_notification_send_date,
                                'emailSentByUser' => $device->businessId,
                                'groupid' => '',
                                'opened' => 0,
                                'openTime' => '',
                                'active' => '1',
                                'created_on' => date('Y-m-d H:i:s')
                            );

                            if (!empty($device->push_notification_token)) {
				                          $send_notitfication_history[] = array(
                                      'campaign_id' => $campaign_id,
                                      'user_id' => $device->businessId,
                                      'platform' => $platform,
                                      'app_group_apps_id' => $device->app_group_apps_id,
                                      'active_device_id' => $device->active_device_id,
                                      'deviceToken' => $device->push_notification_token,
                                      'external_user_id' => $device->external_user_id,
                                      'notification_send_time' => $next_notification_send_date,
                                      'notification_timezone_send_time' => $next_notification_send_date,
                                      'is_send' => '0',
                                      'createdDate' => date('Y-m-d H:i:s')
                                  );
	                          }
                            $countIndex[] = $key;
                        } // END FOREACH
                    } // END IF

                    if (count($send_email_notitfication_history) > 0) {
                        $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                    }
		                if (count($send_notitfication_history) > 0) {
                        $notification_id = $this->brand_model->saveNotificationHistory($send_notitfication_history);
                    }
                    if(count($send_email_notitfication_history) > 0 || count($send_notitfication_history) > 0){
                        updateCronTime('insertCrossCampaignsAtLaunch.txt');
                    }
                    if($flag == 0){
                        $offset = count($countIndex);
                        $limit = 1000;
                        if (isset($segments)) {
                            $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 2);
                        }
                        $filters = $result->filters;
                        if (!empty($filters)) {
                            $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 2);
                        }
                        if (count($users) > 0) { //echo $offset.':'.$limit.',';
                            goto callbegin;
                        }
                    }
                    $push_campaign_arr = array('id' => $campaign_id, 'send' => 1);
                    $this->brand_model->sendCampaigns($push_campaign_arr);
                } // END IF
            }
        }
    }

    // function created for insert cross campaigns users at time ////

    public function insertCrossCampaignsAtTime() {

        $this->load->helper('cron');
        $isCronActive = isCronActive('insertCrossCampaignsAtTime.txt',5);
        if($isCronActive == true){
            return false;
        }
        updateCronTime('insertCrossCampaignsAtTime.txt');

        $results = $this->brand_model->getAllActiveCrossEmailCampaigns(); //echo '<pre>'; print_r($results); exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $currentDate = $result->notification_send_datetime;
                $campaign_id = $result->campaign_id;
                $notification_id = $result->notification_id;
                $notification_type = $result->notification_type;
                $status = 'continue';
                $next_notification_send_date = $result->notification_send_datetime;
                $notification_alert_count = $result->notification_alert_count + 1;
                $createdDate = $result->createdDate;
                if ($result->notification_type == 'launch') {
                    if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                        $status = 'complete';
                    } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 1) {
                        $status = 'complete';
                    } else {
                        if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                        }
                    }
                } else if ($result->notification_type == 'once') {
                    if ($result->reEligible_to_receive_campaign == 0 && $result->notification_alert_count == 1) {
                        $status = 'complete';
                    } else if ($result->reEligible_to_receive_campaign == 1 && $result->notification_alert_count == 1) {
                        $status = 'complete';
                    } else {
                        if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                        }
                    }
                }

                if ($result->delivery_type == 1) {
                    $ending_on_the_date = '';
                    if ($result->time_based_scheduling == 2) {
                        if ($result->ending == 'never' || $result->ending == 'after') {
                            if ($result->ending_on_the_date != '0000-00-00') {
                                $ending_on_the_date = $result->ending_on_the_date;
                            }
                        }
                    } else if ($result->time_based_scheduling == 3) {
                        if ($result->ending == 'never' || $result->ending == 'after') {
                            if ($result->intelligent_ending_on_the_date != '0000-00-00') {
                                $ending_on_the_date = $result->intelligent_ending_on_the_date;
                            }
                        }
                    }
                }

                $app_group_id = $result->app_group_id;
                $platform = $result->platform;
                $campaignName = $result->campaignName;
                $push_title = $result->push_title;
                $push_message = $result->push_message;

                $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

                if (isset($result->displayName)) {
                    $displayName = $result->displayName;
                } else {
                    $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
                }

                if (isset($result->fromAddress)) {
                    $fromAddress = $result->fromAddress;
                } else {
                    $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
                }

                if (isset($result->replyToAddress)) {
                    $replyToAddress = $result->replyToAddress;
                } else {
                    $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
                }

                $notification_arr = array(
                    'notification_id' => $notification_id,
                    'campaign_id' => $campaign_id,
                    'app_group_id' => $app_group_id,
                    'platform' => $platform,
                    'notification_type' => $notification_type,
                    'notification_alert_count' => $notification_alert_count,
                    'createdDate' => $createdDate,
                    'modifiedDate' => date('Y-m-d H:i:s')
                );

                if ($result->delivery_type == 1) {
                    if ($result->send == 'once') {
                        $next_notification_send_date = $next_notification_send_date;
                    }

                    if ($result->time_based_scheduling == 2 && $result->notification_alert_count > 0) {
                        if ($result->send = 'daily') {
                            if (!empty($result->everyDay)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyDay days"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'weekly') {
                            if (!empty($result->weekday)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->weekday"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'monthly') {
                            if (!empty($result->everyMonth)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyMonth Months"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        }
                    } else if ($result->time_based_scheduling == 3 && $result->notification_alert_count > 0) {
                        if ($result->send == 'daily') {
                            if (!empty($result->intelligent_everyDay)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyDay days"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'weekly') {
                            if (!empty($result->intelligent_weekday)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_weekday"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        } else if ($result->send == 'monthly') {
                            if (!empty($result->intelligent_weekday)) {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->intelligent_everyMonth Months"));
                            } else {
                                $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                            }
                        }
                    }
                    if ($currentDate <= $ending_on_the_date) {
                        if ($result->reEligible_to_receive_campaign == 0) {
                            $status = "complete"; //continue;
                        }
                    } elseif ($currentDate >= $ending_on_the_date && $result->reEligible_to_receive_campaign == 1) {
                        $reElegible_date = date('Y-m-d', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                        if ($reElegible_date <= $currentDate) {
                            $status = "complete"; //continue;
                        } else if ($reElegible_date >= $ending_on_the_date && $ending_on_the_date != '') {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                            $status = "complete"; //continue;
                        }
                    }
                }
                $save_notification = array_merge($notification_arr, array('status' => $status, 'notification_send_date' => $next_notification_send_date));
                $this->brand_model->saveNotificationDetails($save_notification);

                $platformIds = '';
                $campaign_send_date_time = '';
                $platformApps = array();
                $platformIds = $this->groupapp_model->getAllApps($app_group_id);
                $campaign_send_date_time = $platformIds[0]->createdDate;
                foreach ($platformIds as $platformId) {
                    array_push($platformApps, $platformId->app_group_apps_id);
                }
                $platformIds = implode(',', $platformApps);
                $platformIds = rtrim($platformIds, ',');
                $personaAssignContactArr = array();
                $listContactArr = array();
                if (!empty($result->persona_user_id)) {
                    $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                    if (count($personaAssignContacts) > 0) {
                        foreach ($personaAssignContacts as $personaAssignContact) {
                            array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                        }
                    }
                }
                if (!empty($result->list_id)) {
                    $listContacts = getContactsByListId($result->list_id, $result->businessId);
                    if (!empty($listContacts)) {
                        $listContactArr = explode(',', $listContacts);
                    }
                }
                $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                $addtional_users = array_merge($add_int_users, $add_diff_users);
                $recent_push_device = 0;
                $limit_ipad_device = 0;
                $limit_ipod_iphone_device = 0;

                $segments = $result->segments;
                $flag = 0;
                $offset = 0;
                $limit = 1000;
                $i = 0;
                $totalReceiveCampaignsUsers = 0;
                $index = array();
                if (isset($segments)) {
                    $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 2);
                }

                $filters = $result->filters;
                if (!empty($filters)) {
                    $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 2);
                }  //echo "<pre>"; print_r($users); exit;
                $userIds = $emailIds = array();
                $deviceIds = $send_notitfication_history = array();

                callbegin:
                unset($send_email_notitfication_history);
                $send_email_notitfication_history = array();
                unset($send_notitfication_history);
                $send_notitfication_history = array();
                if (count($users) > 0) {
                    foreach ($users as $key => $device) {

                        if (isset($result->receiveCampaignType)) {
                            $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                            if ($result->receiveCampaignType == 1) {
                                $i = $i + 1;
                                if ($i > $totalReceiveCampaignsUsers) {
                                    $flag = 1; break;
                                }
                            } else if ($result->receiveCampaignType == 2) {
                                $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                    $flag = 1; break;
                                }
                            }
                        }

                        $notification_timezone_send_time = $next_notification_send_date;

                        if ($result->time_based_scheduling == 2) {
                            if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                $timezone = $device->timezone;
                                $timezone = explode(" ", $timezone);
                                $hours = 0;
                                $minutes = 0;
                                if ($timezone[0] == "GMT") {
                                    $hours = substr($timezone[1], 0, 3);
                                    $minutes = substr($timezone[1], 3, 5);
                                }
                                date_default_timezone_set('GMT');
                                $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                            }
                        }

                        $send_email_notitfication_history[] = array(
                            'email_id' => $device->email,
                            'hurree_version' => '3.1',
                            'userid' => $device->external_user_id,
                            'campaignId' => $campaign_id,
                            'from_email' => $fromAddress,
                            'replyToAddress' => $replyToAddress,
                            'subject' => $push_title,
                            'message' => $push_message,
                            'emailSentOn' => $next_notification_send_date,
                            'emailSentByUser' => $device->businessId,
                            'groupid' => '',
                            'opened' => 0,
                            'openTime' => '',
                            'active' => '1',
                            'created_on' => date('Y-m-d H:i:s')
                        );
                        if (!empty($device->push_notification_token)) {
		                          $send_notitfication_history[] = array(
                                  'campaign_id' => $campaign_id,
                                  'user_id' => $device->businessId,
                                  'platform' => $platform,
                                  'app_group_apps_id' => $device->app_group_apps_id,
                                  'active_device_id' => $device->active_device_id,
                                  'deviceToken' => $device->push_notification_token,
                                  'external_user_id' => $device->external_user_id,
                                  'notification_send_time' => $next_notification_send_date,
                                  'notification_timezone_send_time' => $next_notification_send_date,
                                  'is_send' => '0',
                                  'createdDate' => date('Y-m-d H:i:s')
                              );
	                      }
                        $index[] = $key;
                    } // END FOREACH
                } // END IF

                if (count($send_email_notitfication_history) > 0) {
                    $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                }
                if (count($send_notitfication_history) > 0) {
                    $notification_id = $this->brand_model->saveNotificationHistory($send_notitfication_history);
                }
                if(count($send_email_notitfication_history) > 0 || count($send_notitfication_history) > 0){
                    updateCronTime('insertCrossCampaignsAtTime.txt');
                }
                if($flag == 0){
                    $offset = count($index);
                    $limit = 1000;
                    if (isset($segments)) {
                        $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 2);
                    }
                    $filters = $result->filters;
                    if (!empty($filters)) {
                        $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 2);
                    }
                    if (count($users) > 0) {
                        goto callbegin; //echo "<pre>"; print_r($users); exit;
                    }
                }
            } // END FOREACH
        } // END IF
    }

    // function created for insert cross campaign users on action triggers //
    public function insertCrossCampaignsAtActionTrigger(){
        $this->load->helper('cron');
        $isCronActive = isCronActive('insertCrossCampaignsAtActionTrigger.txt',5);
        if($isCronActive == true){
              return false;
        }
        updateCronTime('insertCrossCampaignsAtActionTrigger.txt');

        $results = $this->brand_model->getAllCrossActionTriggerCampaigns(); //echo '<pre>'; print_r($results); exit;
        $personaAssignContactArr = array();
        $listContactArr = array();
        if (count($results) > 0) {
            foreach ($results as $result) {
                $app_group_id = $result->app_group_id;
                $platform     = $result->platform;
                $campaignName = $result->campaignName;
                $notification_alert_count = $result->notification_alert_count + 1;
                $campaign_id  = $result->id;
                $notification_type = "action-based";
                $push_title = $result->push_title;
                $push_message = $result->push_message;
                $notification_arr = array(
                    'campaign_id' => $campaign_id,
                    'app_group_id' => $app_group_id,
                    'platform' => $platform,
                    'notification_alert_count' => $notification_alert_count,
                    'createdDate' => date('Y-m-d H:i:s'),
                    'modifiedDate' => date('Y-m-d H:i:s')
                );
                $notificationRow = $this->brand_model->getNotificationByCampaignId($campaign_id);

                $email_settings = $this->brand_model->getAppGroupEmailSettings($app_group_id);

                if (isset($result->displayName)) {
                    $displayName = $result->displayName;
                } else {
                    $displayName = isset($email_settings->displayName) ? $email_settings->displayName : '';
                }

                if (isset($result->fromAddress)) {
                    $fromAddress = $result->fromAddress;
                } else {
                    $fromAddress = isset($email_settings->displayEmail) ? $email_settings->displayEmail : '';
                }

                if (isset($result->replyToAddress)) {
                    $replyToAddress = $result->replyToAddress;
                } else {
                    $replyToAddress = isset($email_settings->reply_email) ? $email_settings->reply_email : '';
                }

                $status = "continue";
                $start_date = '';
                $end_date = '';
                $triggerAction = $result->triggerAction;
                $triggerAction = explode(',',$triggerAction);
                if(!empty($result->campaignDuration_startTime_date)){
                    $notification_start_date = $result->notification_send_date;
                    $pos = strpos($notification_start_date, "AM");
                    if ($pos !== false) {
                        $notification_start_date = explode("AM", $notification_start_date);
                        $notification_start_date = date('Y-m-d H:i:s', strtotime($notification_start_date[0]));
                    }
                    $pos = strpos($notification_start_date, "PM");
                    if ($pos !== false) {
                        $notification_start_date = explode("PM", $notification_start_date);
                        $notification_start_date = date('Y-m-d H:i:s', strtotime($notification_start_date[0] . "+12 hours"));
                    }
                    $start_date = $notification_start_date;
                }
                if(!empty($result->campaignDuration_endTime_date)){
                    if($result->campaignDuration_endTime_date != '0000-00-00'){
                        $notification_end_date = $result->campaignDuration_endTime_date .' '.$result->campaignDuration_endTime_hours.':'.$result->campaignDuration_endTime_mins.' '.$result->campaignDuration_endTime_am;
                        $pos = strpos($notification_end_date, "AM");
                        if ($pos !== false) {
                            $notification_end_date = explode("AM", $notification_end_date);
                            $notification_end_date = date('Y-m-d H:i:s', strtotime($notification_end_date[0]));
                        }
                        $pos = strpos($notification_end_date, "PM");
                        if ($pos !== false) {
                            $notification_end_date = explode("PM", $notification_end_date);
                            $notification_end_date = date('Y-m-d H:i:s', strtotime($notification_end_date[0] . "+12 hours"));
                        }
                        $end_date = $notification_end_date;
                    }else{
                        $end_date = "";
                    }//echo $end_date; exit;
                }

                if (!empty($result->persona_user_id)) {
                    $personaAssignContacts = $this->brand_model->getAssignContactsByPersonaId($result->persona_user_id);
                    if (count($personaAssignContacts) > 0) {
                        foreach ($personaAssignContacts as $personaAssignContact) {
                            array_push($personaAssignContactArr, $personaAssignContact->external_user_id);
                        }
                    }
                }
                if (!empty($result->list_id)) {
                    $listContacts = getContactsByListId($result->list_id,$result->businessId);
                    if (!empty($listContacts)) {
                        $listContactArr = explode(',',$listContacts);
                    }
                }
                $add_int_users = array_intersect($personaAssignContactArr,$listContactArr);
                $add_diff_users = array_diff($personaAssignContactArr,$listContactArr);
                $addtional_users = array_merge($add_int_users,$add_diff_users);
                $next_notification_send_date = $result->createdDate;
                if (count($notificationRow) == 0) {
                    if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                        $status = "continue";
                    }
                    $date = date('Y-m-d H:i:s');
                    if ($end_date > $date) {
                        $status = "complete";
                    }
                    $notification_arr = array_merge($notification_arr,array('notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date,'status' => $status));
                } else {
                    $notification_arr = array_merge($notification_arr,array('notification_id' => $notificationRow[0]->notification_id,'notification_type' => $notification_type,'notification_send_date' => $next_notification_send_date,'status' => $status));
                }
                $this->brand_model->saveNotificationDetails($notification_arr);

                $segments  = $result->segments;
                $filters = $result->filters;
                $recent_push_device = 0;
                $limit_ipad_device = 0;
                $limit_ipod_iphone_device = 0;
                $campaign_send_date_time = '';
                $platformIds = '';
                $platformApps = array();
                $platformIds = $this->groupapp_model->getAllApps($app_group_id);
                if(count($platformIds) > 0){
          		        $campaign_send_date_time = $platformIds[0]->createdDate;
          		        foreach ($platformIds as $platformId) {
          		            array_push($platformApps, $platformId->app_group_apps_id);
          		        }
          		        $platformIds = implode(',', $platformApps);
          		        $platformIds = rtrim($platformIds, ',');
          		  }
                if ($result->send_push_to_recently_used_device == 1) {
                    $recent_push_device = 1;
                }
                if ($result->limit_this_push_to_iPad_devices == 1) {
                    $limit_ipad_device = 1;
                }
                if ($result->limit_this_push_to_iphone_and_ipod_devices == 1) {
                    $limit_ipod_iphone_device = 1;
                }
                $offset = 0;
                $limit = 250;
                $totalReceiveCampaignsUsers = 0;
                $index = array();
                if (isset($segments)) {
                    $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 2);
                }
                if (!empty($filters)) {
                    $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 2);
                }
                $triggerUsers = array();
                if(count($triggerAction) > 0){
                    foreach($triggerAction as $key => $action){
                        $users_arr = $this->brand_model->getTriggerActionUsers($action,$platformIds,$start_date,$end_date,$campaign_id);
                        if(!empty($users_arr['external_user_id'])){ // print_r($users_arr);
                            array_push($triggerUsers,$users_arr['external_user_id']);
                        }
                    }
                }
                if(count($triggerUsers) > 0){
                    $triggerUsers = implode(',',$triggerUsers);
                    $triggerUsers = explode(',',$triggerUsers);
                    $triggerUsers = array_unique($triggerUsers);
                    $receviedUsers = array();
                    $campaignReceivedUsers = $this->brand_model->getActionTriggerReceivedUsers($campaign_id,"push");
                    if(!empty($campaignReceivedUsers['external_user_id'])){
                        $receviedUsers = explode(',',$campaignReceivedUsers['external_user_id']);
                        $triggerUsers = array_diff($triggerUsers,$receviedUsers);
                    }
                }
                $userIds = $emailIds = array();
                $deviceIds = $send_notitfication_history = $send_email_notitfication_history = array();

                callbegin:
                $flag = 0;
                unset($send_notitfication_history);
                $send_notitfication_history = array();
                unset($send_email_notitfication_history);
                $send_email_notitfication_history = array();
                if(count($triggerUsers) > 0){
                    if (count($users) > 0) {
                        foreach ($users as $key => $device) {
                            if(in_array($device->external_user_id, $triggerUsers)) {
                                if (isset($result->receiveCampaignType)) {
                                    $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;
                                    if ($result->receiveCampaignType == 1) {
                                        $i = isset($i) ? $i : $i = 0;
                                        $i = $i + 1;
                                        if ($i > $totalReceiveCampaignsUsers) {
                                            $flag = 1; break;
                                        }
                                    } else if ($result->receiveCampaignType == 2) {
                                        $totalSendCampaignsUsers = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                        if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                            $flag = 1; break;
                                        }
                                    }
                                }

                                $notification_timezone_send_time = $next_notification_send_date;

                                if(!empty($result->scheduleDelay_afterTime)){
                                    $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$result->scheduleDelay_afterTime $result->scheduleDelay_afterTimeInterval"));
                                }

                                $send_email_notitfication_history[] = array(
                                                          'email_id' => $device->email,
                                                          'hurree_version' => '3.1',
                                                          'userid' => $device->external_user_id,
                                                          'campaignId' => $campaign_id,
                                                          'from_email' => $fromAddress,
                                                          'replyToAddress' => $replyToAddress,
                                                          'subject' => $push_title,
                                                          'message' => $push_message,
                                                          'emailSentOn' => $next_notification_send_date,
                                                          'emailSentByUser' => $device->businessId,
                                                          'groupid' => '',
                                                          'opened' => 0,
                                                          'openTime' => '',
                                                          'active' => '1',
                                                          'created_on' => date('Y-m-d H:i:s')
                                                      );
                                if (!empty($device->push_notification_token)) {
                                    $send_notitfication_history[] = array(
                                        'campaign_id' => $campaign_id,
                                        'user_id' => $device->businessId,
                                        'platform' => $platform,
                                        'app_group_apps_id' => $device->app_group_apps_id,
                                        'active_device_id' => $device->active_device_id,
                                        'deviceToken' => $device->push_notification_token,
                                        'external_user_id' => $device->external_user_id,
                                        'notification_send_time' => $next_notification_send_date,
                                        'notification_timezone_send_time' => $next_notification_send_date,
                                        'is_send' => '0',
                                        'createdDate' => date('Y-m-d H:i:s')
                                    );
                               }
                            } // END IF
                            $index[] = $key;
                        } // END FOREACH
                    } // END IF COUNT users
                } // END IF COUNT trigger users
                if($flag == 0){
                    $offset = count($index);
                    $limit  = 250;
                    if(count($send_notitfication_history) > 0){
                        $notification_id = $this->brand_model->saveNotificationHistory($send_notitfication_history);
                    }
                    if (count($send_email_notitfication_history) > 0) {
                        $notification_id = $this->brand_model->saveEmailNotificationHistory($send_email_notitfication_history);
                    }
                    if(count($send_email_notitfication_history) > 0 || count($send_notitfication_history) > 0){
                       updateCronTime('insertCrossCampaignsAtActionTrigger.txt');
                    }
                    if($offset > 0){
                        if (isset($segments)) {
                            $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit, $forEmail = 2);
                        }
                        $filters = $result->filters;
                        if (!empty($filters)) {
                            $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit, $forEmail = 2);
                        }
                        if(count($users) > 0){
                            goto callbegin;
                        }
                    } // END IF Offset
                } // END IF FLAG
          } // END Main FOREACH block
       } // END main IF block
    }

}
