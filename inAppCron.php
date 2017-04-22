<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class inAppCron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('user_model', 'brand_model', 'inapp_model', 'groupapp_model', 'webhook_model'));
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    /*
     * this function is used to make entry in inapp_notification_send_history
      table to prepare inApp sending data
     */

     /* This function is used for insert inapp messaging users at launch delivery. */
     public function deliverInAppMessagingAtLaunch() {
         $this->load->helper('cron');
         $isCronActive = isCronActive('deliverInAppMessagingAtLaunch.txt', 5);
         if ($isCronActive == true) {
             return false;
         }
         updateCronTime('deliverInAppMessagingAtLaunch.txt');
         $results = $this->inapp_model->getAllInAppMessagingAtLaunch(); //echo count($results); echo '<pre>'; print_r($results); exit;
         if (count($results) > 0) {
             foreach ($results as $result) {
                 $app_group_id = $result->app_group_id;
                 $platform = $result->device_type;
                 $message_id = $result->id;

                 $notification_arr = array(
                     'message_id' => $message_id,
                     'app_group_id' => $app_group_id,
                     'platform' => $platform,
                     'notification_alert_count' => 1,
                     'createdDate' => date('Y-m-d H:i:s'),
                     'modifiedDate' => date('Y-m-d H:i:s')
                 );
                 $notificationRow = $this->inapp_model->getNotificationByMessageId($result->id); // GET ROW FROM inapp_notification_send_details
                 $status = "continue";

                 $platformIds = '';
                 $send_date_time = '';
                 $platformApps = array();
                 $platformIOSIds = $this->groupapp_model->getIOSApps($app_group_id);
                 $platformAndroidIds = $this->groupapp_model->getAndroidApps($app_group_id);
                 $platformIds = array_merge($platformIOSIds, $platformAndroidIds);
                 if (count($platformIds) > 0) {
                     $send_date_time = $platformIds[0]->createdDate;
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
                 $addtional_users = array_merge($add_int_users, $add_diff_users); // list and persona contacts users
                 $recent_push_device = 0;
                 $limit_ipad_device = 0;
                 $limit_ipod_iphone_device = 0;

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
                         } // save record in inapp_notification_send_details table
                         $this->inapp_model->saveNotificationDetails($notification_arr);
                     } else {
                         if ($result->time_based_scheduling == 2) { // designated time
                             $notification_type = $result->send;
                         }
                         if ($result->time_based_scheduling == 3) { // intelligent time
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

                             $this->inapp_model->saveNotificationDetails($notification_arr); // save data in inapp_notification_send_details
                         }
                     }
                 }

                 if ($result->time_based_scheduling == 1) { // for launch
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
                                         $i = $i + 1; // use for count user
                                         if ($i > $totalReceiveCampaignsUsers) {
                                             $flag = 1;
                                             break;
                                         }
                                     } else if ($result->receiveCampaignType == 2) {
                                         $totalSendCampaignsUsers = $this->inapp_model->countCampaignSendHistoryByCampaignId($message_id);
                                         if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                             $flag = 1;
                                             break;
                                         }
                                     }
                                 }
                                 $notification_timezone_send_time = $next_notification_send_date;
                                 $send_notitfication_history[] = array(
                                     'message_id' => $message_id,
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
                                 $countIndex[] = $key;
                             } // END IF
                         } // END foreach
                     } // END IF
                     if (count($send_notitfication_history) > 0) {
                         $notification_id = $this->inapp_model->saveNotificationHistory($send_notitfication_history);
                         updateCronTime('deliverInAppMessagingAtLaunch.txt');
                     }
                     if ($flag == 0) {
                         $offset = count($countIndex);
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
                     $inapp_arr = array('id' => $message_id, 'send' => 1); // for launch in-app launch event done.
                     $this->inapp_model->sendInAppMessage($inapp_arr);
                 } // END IF
             } // END FOREACH
         } // END IF
     }

     /* This function is used for insert inapp messaging users at schedule based delivery in designated time and intelligent delivery. */
     public function deliverAllInAppMessagingAtTime() {
         $this->load->helper('cron');
         $isCronActive = isCronActive('deliverAllInAppMessagingAtTime.txt', 5);
         if ($isCronActive == true) {
             return false;
         }
         updateCronTime('deliverAllInAppMessagingAtTime.txt');
         $results = $this->inapp_model->getAllActiveInAppMessaging(); //echo '<pre>'; print_r($results); //exit;
         if (count($results) > 0) {
             foreach ($results as $result) {
                 $currentDate = $result->notification_send_datetime;
                 $message_id = $result->message_id; // work as inapp id
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
                 } else if ($result->notification_type == 'once') { // for once
                     if ($result->reEligible_to_receive_campaign == 0 && $result->$notification_alert_count == 1) {
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
                 $platform = $result->device_type;
                 $campaignName = $result->campaignName;

                 $notification_arr = array(
                     'notification_id' => $notification_id,
                     'message_id' => $message_id,
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
                         if ($result->send = 'daily') { // daily date
                             if (!empty($result->everyDay)) {
                                 $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->everyDay days"));
                             } else {
                                 $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                             }
                         } else if ($result->send == 'weekly') {  // weekly date
                             if (!empty($result->weekday)) {
                                 $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . "+$result->weekday"));
                             } else {
                                 $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime));
                             }
                         } else if ($result->send == 'monthly') {  // monthly date
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
                         } else if ($result->send == 'monthly') { // monthly
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
                 $this->inapp_model->saveNotificationDetails($save_notification); // save inapp_notification_send_details new date with status // print_r( $save_notification ); exit;

                 $platformIds = '';
                 $send_date_time = '';
                 $platformApps = array();
                 $platformIOSIds = $this->groupapp_model->getIOSApps($app_group_id);
                 $platformAndroidIds = $this->groupapp_model->getAndroidApps($app_group_id);
                 $platformIds = array_merge($platformIOSIds, $platformAndroidIds);
                 if (count($platformIds) > 0) {
                     $send_date_time = $platformIds[0]->createdDate;
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
                 $addtional_users = array_merge($add_int_users, $add_diff_users); // used for concat list and persona contacts.
                 $recent_push_device = 0;
                 $limit_ipad_device = 0;
                 $limit_ipod_iphone_device = 0;

                 $flag = 0;
                 $offset = 0;
                 $limit = 1000;
                 $i = 0;
                 $totalReceiveCampaignsUsers = 0;
                 $index = array();
                 $segments = $result->segments; //1;//
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
                                 $totalReceiveInAppUsers = $result->no_of_users_who_receive_campaigns;

                                 if ($result->receiveCampaignType == 1) {
                                     $i = $i + 1; // use for count element
                                     if ($i > $totalReceiveInAppUsers) {
                                         $flag = 1;
                                         break;
                                     }
                                 } else if ($result->receiveCampaignType == 2) {
                                     $totalSendInAppUsers = $this->inapp_model->countCampaignSendHistoryByCampaignId($message_id);
                                     if ($totalReceiveInAppUsers < $totalSendInAppUsers) {
                                         $flag = 1;
                                         break;
                                     }
                                 }
                             }

                             $notification_timezone_send_time = $next_notification_send_date;
 			    // used for create user timezone
                             if ($result->time_based_scheduling == 2) {
                                 if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
                                     $timezone = $device->timezone;
                                     $timezone = explode(" ", $timezone);
                                     $hours = 0;
                                     $minutes = 0;
                                     if ($timezone[0] == "GMT") {
                                         $hours = substr($timezone[1], 0, 3);
                                         $minutes = substr($timezone[1], 3, 5);
                                         //echo "$hours";	 //echo "$minutes";
                                     }
                                     date_default_timezone_set('GMT');
                                     $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$hours hours $minutes minutes"));
                                 }
                             }

                             $send_notitfication_history[] = array(
                                 'message_id' => $message_id,
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
                     $notification_id = $this->inapp_model->saveNotificationHistory($send_notitfication_history);
                     updateCronTime('deliverAllInAppMessagingAtTime.txt');
                 }

                 if ($flag == 0) {
                     $offset = count($index);
                     $limit = 1000;
                     if (isset($segments)) {
                         $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                     }
                     $filters = $result->filters;
                     if (!empty($filters)) {
                         $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                     }
                     if (count($users) > 0) {// print_r($users); exit;//$c = 0; echo $c++;
                         goto callbegin;
                     }
                 }
             } // END foreach
         } // END IF
     }

     /* This function is used for send inapp messaging to users devices from inapp_notification_send_history table. */
         public function deliverInAppMessagingNotificationHistory() {
             $this->load->helper('cron');
             $isCronActive = isCronActive('deliverInAppMessagingNotificationHistory.txt', 5);
             if ($isCronActive == true) {
                 return false;
             }
             updateCronTime('deliverInAppMessagingNotificationHistory.txt');
             $limit = $chunckSize = 200;

             $totalCompains = $this->inapp_model->countActiveHistoryCampaigns(); // echo "<pre>";
             if ($totalCompains == null)
                 return false;
             foreach ($totalCompains as $total) {
                 $tatalCampains = $total->counter;
                 $message_id = $total->message_id; // used for inapp id
                 //updateCronTime('deliverNotificationHistory.txt');
                 if ($chunckSize < $tatalCampains) {
                     $offset = 0;
                     $total_loop = (int) $tatalCampains / $chunckSize;
                     $extraloop = (int) $tatalCampains % $chunckSize;
                     for ($counter = 0; $counter < $total_loop; $counter++) {
                         $offset = $counter * $limit;
                         $results = $this->inapp_model->getAllActiveHistoryCampaigns($limit, $offset, $message_id);
                         if ($results != null)
                             $this->innerCallPushNotification($results);
                     }
                     $offset = $total_loop * $limit;
                     $results = $this->inapp_model->getAllActiveHistoryCampaigns($extraloop, $offset, $message_id);
                     if ($results != null)
                         $this->innerCallPushNotification($results);
                 } else {
                     $results = $this->inapp_model->getAllActiveHistoryCampaigns($limit, 0, $message_id);
                     if ($results != null)
                         $this->innerCallPushNotification($results);
                 }
             }
         }

    /*
     * calling this function inner from deliverNotificationHistory() function and passed set of record
     *
     */

    public function innerCallPushNotification($results) {
        foreach ($results as $result) {
            $appsGroupAppRow = $this->brand_model->getAppsGroupAppRowByAppId($result->app_group_apps_id);
            $deviceToken = $result->deviceToken;
            $app_group_id = $result->app_group_id;
            $deviceType = $result->platform;
            $notification_id = $result->notification_id;
            $message_id = $result->id;
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

            $push_title = $result->header;

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

            if ($appsGroupAppRow->platform == 'iOS') {
                $certificateType = $result->certificateType; // 1 for production , 2 for developement
                $certificateName = $result->fileName; // certifivcate file name

                $sound = 'default';
                if (!empty($result->fileName)) {
                    $msgpayload = array(
                        'aps' => array(
                            'alert' => $push_title,
                            "app_group_id" => $app_group_id,
                            "inAppMessagingId" => $message_id,
                            "notification_id" => "$notification_id",
                            "campaignName" => $campaignName,
                            "type" => 'inAppNotification',
                            "sound" => $sound,
                        )
                    );
                    $response = $this->sendIosPush($deviceToken, $msgpayload, $certificateType, $certificateName); //print_r($result ); exit;
                }
            } else if ($appsGroupAppRow->platform == 'Android') {
                $google_api_key = "AIzaSyBhuz3MFoUyNX3MNLZtma1l89sauNDGT7U"; // defalut key if user have no GCM key
                $notification = array(
                    'alert' => $push_title,
                    "app_group_id" => $app_group_id,
                    "inAppMessagingId" => $message_id,
                    "notification_id" => "$notification_id",
                    "campaignName" => $campaignName,
                    'type' => 'inAppNotification',
                );
                $message = json_encode($notification);
                if (!empty($appsGroupAppRow->GCM)) {
                    $google_api_key = $appsGroupAppRow->GCM;
                }
                $response = false;
                $response = $this->androidPush($google_api_key, $deviceToken, $message);
                $response = json_decode($response); // print_r(	$response); //exit;
                if (isset($response) && $response->failure == "0") {
                    $response = true;
                }
            }
            if (isset($response) && $response == "1") {
                $this->inapp_model->sendNotificationSendHistory($notification_id);// update inapp_notifivcation_send_hitory row status for send or not.

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
        $results = $this->inapp_model->getAllActiveHistoryInAppMessaging();
        if (count($results) > 0) {
            foreach ($results as $result) {
                $appsGroupAppRow = $this->brand_model->getAppsGroupAppRowByAppId($result->app_group_apps_id);
                $deviceToken = $result->deviceToken;
                $app_group_id = $result->app_group_id;
                $deviceType = $result->platform;
                $notification_id = $result->notification_id;
                $inAppMessagingId = $result->id;
                $campaignName = $result->campaignName;

                if ($appsGroupAppRow->platform == 'iOS') {
                    $certificateType = $appsGroupAppRow->certificateType;
                    $certificateName = $appsGroupAppRow->fileName;
                    $push_message = $result->header;
                    $sound = 'default';
                    if (!empty($appsGroupAppRow->fileName)) {
                        $msgpayload = array(
                            'aps' => array(
                                "alert" => $push_message,
                                "app_group_id" => $app_group_id,
                                "inAppMessagingId" => $inAppMessagingId,
                                "notification_id" => "$notification_id",
                                "campaignName" => $campaignName,
                                "type" => 'inAppNotification',
                                "sound" => $sound,
                            )
                        );
                        $this->sendIosPush($deviceToken, $msgpayload, $certificateType, $certificateName);
                    }
                } else if ($appsGroupAppRow->platform == 'Android') {
                    $notification = array(
                        'alert' => $push_message,
                        "app_group_id" => $app_group_id,
                        "inAppMessagingId" => $inAppMessagingId,
                        "notification_id" => "$notification_id",
                        "campaignName" => $campaignName,
                        'type' => 'inAppNotification',
                    );
                    $message = json_encode($notification);
                    if (!empty($appsGroupAppRow->GCM)) {
                        $google_api_key = $appsGroupAppRow->GCM;
                    } else {
                        $google_api_key = "AIzaSyBhuz3MFoUyNX3MNLZtma1l89sauNDGT7U";
                    }
                    $response = $this->androidPush($google_api_key, $deviceToken, $message);
                }
                $this->inapp_model->sendNotificationSendHistory($notification_id);
            }
        }
    }

    /* This url is used for insert inapp campaigns users at action based delivery  */
    public function deliverInAppMessagingAtActionTrigger() {
        $this->load->helper('cron');
        $isCronActive = isCronActive('deliverInAppMessagingAtActionTrigger.txt', 5);
        if ($isCronActive == true) {
            return false;
        }
        updateCronTime('deliverInAppMessagingAtActionTrigger.txt');
        $results = $this->inapp_model->getAllActionTriggerCampaigns(); //echo '<pre>'; print_r($results); exit;
        $personaAssignContactArr = array();
        $listContactArr = array();
        if (count($results) > 0) {
            foreach ($results as $result) {
                $app_group_id = $result->app_group_id;
                $platform = $result->device_type;
                $campaignName = $result->campaignName;
                $notification_alert_count = $result->notification_alert_count + 1; // increment in notification_alert_count every time
                $campaign_id = $result->id;
                $notification_type = "action-based";
                // $push_title = $result->push_title;
                // $push_message = $result->push_message;
                $notification_arr = array(
                    'message_id' => $campaign_id,
                    'app_group_id' => $app_group_id,
                    'platform' => $platform,
                    'notification_alert_count' => $notification_alert_count,
                    'createdDate' => date('Y-m-d H:i:s'),
                    'modifiedDate' => date('Y-m-d H:i:s')
                );
                $notificationRow = $this->inapp_model->getNotificationByMessageId($campaign_id); // get row from inapp_notification_send_details

                $status = "continue";
                $start_date = '';
                $end_date = '';
                $triggerAction = $result->triggerAction; // used for triggers
                $triggerAction = explode(',', $triggerAction);
                if (!empty($result->campaignDuration_startTime_date)) {  // used for startdate
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
                if (!empty($result->campaignDuration_endTime_date)) {  // used for enddate
                    if ($result->campaignDuration_endTime_date != '0000-00-00') {
                        $notification_end_date = $result->campaignDuration_endTime_date . ' ' . $result->campaignDuration_endTime_hours . ':' . $result->campaignDuration_endTime_mins . ' ' . $result->campaignDuration_endTime_am;
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
                    } else {
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
                    $listContacts = getContactsByListId($result->list_id, $result->businessId);
                    if (!empty($listContacts)) {
                        $listContactArr = explode(',', $listContacts);
                    }
                }
                $add_int_users = array_intersect($personaAssignContactArr, $listContactArr);
                $add_diff_users = array_diff($personaAssignContactArr, $listContactArr);
                $addtional_users = array_merge($add_int_users, $add_diff_users); // list and persona contacts
                $next_notification_send_date = $result->createdDate;
                if (count($notificationRow) == 0) { // use for reeleigibe inapp
                    if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                        $status = "continue";
                    }
                    $date = date('Y-m-d H:i:s');
                    if ($end_date > $date) {
                        $status = "complete";
                    }
                    $notification_arr = array_merge($notification_arr, array('notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                } else {
                    $notification_arr = array_merge($notification_arr, array('notification_id' => $notificationRow[0]->notification_id, 'notification_type' => $notification_type, 'notification_send_date' => $next_notification_send_date, 'status' => $status));
                }
                $this->inapp_model->saveNotificationDetails($notification_arr); // insert inapp_notification_send_details

                $segments = $result->segments;
                $filters = $result->filters;
                $recent_push_device = 0;
                $limit_ipad_device = 0;
                $limit_ipod_iphone_device = 0;
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
                $offset = 0;
                $limit = 250;
                $totalReceiveCampaignsUsers = 0;
                $index = array();
                if (isset($segments)) { // Target users segments.
                    $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                }
                if (!empty($filters)) { // Target users filters.
                    $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                }
                $triggerUsers = array();
                if (count($triggerAction) > 0) { // used for selected trigger action
                    foreach ($triggerAction as $key => $action) {
                        $users_arr = $this->brand_model->getTriggerActionUsers($action, $platformIds, $start_date, $end_date, $campaign_id);
                        if (!empty($users_arr['external_user_id'])) { // print_r($users_arr);
                            array_push($triggerUsers, $users_arr['external_user_id']);
                        }
                    }
                }
                if (count($triggerUsers) > 0) {
                    $triggerUsers = implode(',', $triggerUsers);
                    $triggerUsers = explode(',', $triggerUsers);
                    $triggerUsers = array_unique($triggerUsers);
                    $receviedUsers = array();
                    $campaignReceivedUsers = $this->brand_model->getActionTriggerReceivedUsers($campaign_id, "inapp");
                    if (!empty($campaignReceivedUsers['external_user_id'])) {
                        $receviedUsers = explode(',', $campaignReceivedUsers['external_user_id']);
                        $triggerUsers = array_diff($triggerUsers, $receviedUsers);
                    }
                }
                $userIds = $deviceIds = array();

                callPushbegin:
                $flag = 0;
                unset($send_notitfication_history);
                $send_notitfication_history = array();
                if (count($triggerUsers) > 0) {
                    if (count($users) > 0) {
                        foreach ($users as $key => $device) {
                            if (in_array($device->external_user_id, $triggerUsers)) {
                                if (isset($result->receiveCampaignType)) {
                                    $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;
                                    if ($result->receiveCampaignType == 1) {
                                        $i = $i + 1; // used for count users
                                        if ($i > $totalReceiveCampaignsUsers) {
                                            $flag = 1;
                                            break;
                                        }
                                    } else if ($result->receiveCampaignType == 2) {
                                        $totalSendCampaignsUsers = $this->inapp_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                        if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                            $flag = 1;
                                            break;
                                        }
                                    }
                                }

                                $notification_timezone_send_time = $next_notification_send_date;
                                /* if ($result->send_campaign_to_users_in_their_local_time_zone == 1) {
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
                                  } */
                                if (!empty($result->scheduleDelay_afterTime)) {
                                    $notification_timezone_send_time = date('Y-m-d H:i:s', strtotime($next_notification_send_date . "$result->scheduleDelay_afterTime $result->scheduleDelay_afterTimeInterval"));
                                }
                                $send_notitfication_history[] = array(
                                    'message_id' => $campaign_id,
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
                    if ($flag == 0) {
                        $offset = count($index);
                        $limit = 250;
                        if (count($send_notitfication_history) > 0) {
                            $notification_id = $this->inapp_model->saveNotificationHistory($send_notitfication_history);
                            updateCronTime('deliverInAppMessagingAtActionTrigger.txt');
                        }
                        if ($offset > 0) {
                            if (isset($segments)) {
                                $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $addtional_users, $offset, $limit);
                            }
                            $filters = $result->filters;
                            if (!empty($filters)) {
                                $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $limit_ipad_device, $limit_ipod_iphone_device, $filters, $addtional_users, $campaign_send_date_time, $offset, $limit);
                            }
                            if (count($users) > 0) {
                                goto callPushbegin;
                            }
                        }
                    }
                }
            } // END FOREACH
        } // END IF
    }
/* This function is used for send notification on android device token.*/
    public function androidPush($google_api_key, $deviceToken, $message) {
        $this->load->library('Gcm');
        $gcm = new GCM();
        $result = $gcm->sendPushNotification($google_api_key, $deviceToken, $message);
        return $result;
    }
/* This function is used for send notification on iOS device token.*/
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
            return false; //echo "false";die;
        }
        $apnsMessage = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload;
        if (fwrite($apnsConnection, $apnsMessage)) {
            return true; //   echo "Done";die;
        }
        //fclose($apnsConnection);
        //die();
    }

    public function deliverAllWebhooksAtLaunch() {
        $results = $this->webhook_model->getAllWebhooksAtLaunch(); //echo count($results); echo '<pre>'; print_r($results); exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $app_group_id = $result->app_group_id;
                $platform = $result->platform;
                $campaignName = $result->campaignName;
                $webhook_id = $result->id;

                $notificationRow = $this->webhook_model->getNotificationByWebhookId($result->id);
                if ($result->time_based_scheduling == 1) {
                    $next_notification_send_date = $result->createdDate;
                    if (count($notificationRow) == 0) {
                        $notification_arr = array(
                            'webhook_id' => $webhook_id,
                            'app_group_id' => $app_group_id,
                            'platform' => $platform,
                            'notification_send_date' => $next_notification_send_date,
                            'notification_type' => 'launch',
                            'notification_alert_count' => 0,
                            'status' => 'continue',
                            'createdDate' => date('Y-m-d H:i:s'),
                            'modifiedDate' => date('Y-m-d H:i:s')
                        );
                        $this->webhook_model->saveNotificationDetails($notification_arr);
                    } else {
                        if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                            $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->createdDate . "+$result->reEligibleTime $result->reEligibleTimeInterval"));
                            $notification_arr = array(
                                'notification_id' => $notificationRow[0]->notification_id,
                                'webhook_id' => $webhook_id,
                                'app_group_id' => $app_group_id,
                                'platform' => $platform,
                                'notification_send_date' => $next_notification_send_date,
                                'notification_type' => 'launch',
                                'notification_alert_count' => 0,
                                'status' => 'continue',
                                'createdDate' => date('Y-m-d H:i:s'),
                                'modifiedDate' => date('Y-m-d H:i:s')
                            );
                            $this->webhook_model->saveNotificationDetails($notification_arr);
                        }
                    }
                } else {
                    if ($result->time_based_scheduling == 2) {
                        $notification_type = $result->send;
                    }
                    if ($result->time_based_scheduling == 3) {
                        $notification_type = $result->intelligent_send;
                    }
                    if ($result->delivery_type == 2) {
                        $notification_type = "action-based";
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
                        $next_notification_send_date = $notification_send_date; //date('Y-m-d H:i:s', strtotime($result->createdDate . " + $result->reEligibleTime $result->reEligibleTimeInterval"));
                        $notification_arr = array(
                            'webhook_id' => $webhook_id,
                            'app_group_id' => $app_group_id,
                            'platform' => $platform,
                            'notification_send_date' => $next_notification_send_date,
                            'notification_type' => $notification_type,
                            'notification_alert_count' => 0,
                            'status' => 'continue',
                            'createdDate' => date('Y-m-d H:i:s'),
                            'modifiedDate' => date('Y-m-d H:i:s')
                        );
                        $this->webhook_model->saveNotificationDetails($notification_arr);
                    }
                }

                if ($result->time_based_scheduling == 1) {

                    $push_webhook_arr = array('id' => $webhook_id, 'send' => 1);
                    $this->webhook_model->sendWebhooks($push_webhook_arr);

                    $platformIds = '';
                    $send_date_time = '';
                    $platformApps = array();
                    $platformIOSIds = $this->groupapp_model->getIOSApps($app_group_id);
                    $platformAndroidIds = $this->groupapp_model->getAndroidApps($app_group_id);
                    $platformIds = array_merge($platformIOSIds, $platformAndroidIds);
                    if (count($platformIds) > 0) {
                        $send_date_time = $platformIds[0]->createdDate;
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
                    $recent_push_device = 0;

                    $segments = $result->segments;
                    if (isset($segments)) {
                        $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $addtional_users);
                    }
                    $filters = $result->filters;
                    if (!empty($filters)) {
                        $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $filters, $addtional_users, $campaign_send_date_time);
                    }
                    //echo "<pre>"; echo "recent_push_device"; print_r($users); exit;
                    $i = 0;
                    $totalReceiveCampaignsUsers = 0;
                    $userIds = $deviceIds = array();

                    if (count($users) > 0) {
                        foreach ($users as $device) {
                            if (!in_array($device->external_user_id, $userIds)) {
                                array_push($userIds, $device->external_user_id);

                                if (isset($result->receiveCampaignType)) {
                                    $totalReceiveCampaignsUsers = $result->no_of_users_who_receive_campaigns;

                                    if ($result->receiveCampaignType == 1) {
                                        $i = $i + 1;
                                        if ($i > $totalReceiveCampaignsUsers) {
                                            continue;
                                        }
                                    } else if ($result->receiveCampaignType == 2) {
                                        $totalSendCampaignsUsers = $this->webhook_model->countWebhookSendHistoryByWebhookId($webhook_id);
                                        if ($totalReceiveCampaignsUsers < $totalSendCampaignsUsers) {
                                            continue;
                                        }
                                    }
                                }
                            }
                            if (!in_array($device->push_notification_token, $deviceIds)) {
                                array_push($deviceIds, $device->push_notification_token);

                                $send_notitfication_history = array(
                                    'webhook_id' => $webhook_id,
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
                                $this->webhook_model->saveNotificationHistory($send_notitfication_history);
                            } // END IF
                        } // END foreach
                    } // END count
                } // END IF
            }  // End foreach
        } // end If
    }

    public function deliverAllWebhooksAtTime() {
        $results = $this->webhook_model->getAllActiveWebhooks(); // echo '<pre>'; print_r($results); exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $currentDate = $result->notification_send_datetime;
                $webhook_id = $result->webhook_id;
                $notification_id = $result->notification_id;
                $notification_type = $result->notification_type;
                $status = 'continue';
                $next_notification_send_date = $result->notification_send_datetime;
                $notification_alert_count = $result->notification_alert_count + 1;
                $createdDate = $result->createdDate;
                if ($result->notification_type == 'launch') {
                    if ($result->reEligible_to_receive_campaign == 0 || $result->notification_alert_count == 2) {
                        $status = 'complete';
                    }
                } else if ($result->notification_type == 'once') {
                    if ($result->reEligible_to_receive_campaign == 0 || $result->notification_alert_count == 2) {
                        $status = 'complete';
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
                } else if ($result->delivery_type == 2) {
                    $triggerAction = $result->triggerAction;
                    $scheduleDelay = $result->scheduleDelay;
                    $endtime = $result->campaignDuration_endTime_date . ' ' . $result->campaignDuration_endTime_hours . ':' . $result->campaignDuration_endTime_mins . ':' . '00';
                    $campaignDuration_startTime_date = $result->notification_send_datetime;
                    if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 0) {
                        $status = 'complete';
                    } else if ($result->reEligible_to_receive_campaign == 0 || $result->notification_alert_count == 2) {
                        $status = 'complete';
                    }
                    if ($campaignDuration_startTime_date < $endtime) {
                        $status = 'complete'; //continue;
                    }
                }

                $app_group_id = $result->app_group_id;
                $platform = $result->platform;
                $campaignName = $result->campaignName;

                $notification_arr = array(
                    'notification_id' => $notification_id,
                    'webhook_id' => $webhook_id,
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
                } else if ($result->delivery_type == 2) {
                    if (isset($result->reEligible_to_receive_campaign) && $result->reEligible_to_receive_campaign == 1) {
                        $next_notification_send_date = date('Y-m-d H:i:s', strtotime($result->notification_send_datetime . " + $result->reEligibleTime $result->reEligibleTimeInterval"));
                    }
                }
                $save_notification = array_merge($notification_arr, array('status' => $status, 'notification_send_date' => $next_notification_send_date));
                //print_r($save_notification); exit;
                $this->webhook_model->saveNotificationDetails($save_notification);

                $platformIds = '';
                $send_date_time = '';
                $platformApps = array();
                $platformIOSIds = $this->groupapp_model->getIOSApps($app_group_id);
                $platformAndroidIds = $this->groupapp_model->getAndroidApps($app_group_id);
                $platformIds = array_merge($platformIOSIds, $platformAndroidIds);
                if (count($platformIds) > 0) {
                    $send_date_time = $platformIds[0]->createdDate;
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
                $users = $this->brand_model->getAppUsersByAppGroupId($app_group_id, $platformIds, $addtional_users);
                $recent_push_device = 0;

                $segments = $result->segments;
                if (isset($segments)) {
                    $users = $this->brand_model->getExternalUsersBySegments($app_group_id, $platformIds, $segments, $recent_push_device, $addtional_users);
                }

                $filters = $result->filters;
                if (!empty($filters)) {
                    $users = $this->brand_model->getExternalUsersByFilters($app_group_id, $platformIds, $segments, $recent_push_device, $filters, $addtional_users, $campaign_send_date_time);
                }

                $i = 0;
                $totalReceiveInAppUsers = 0;
                $userIds = $deviceIds = array();

                if (count($users) > 0) {
                    foreach ($users as $device) {
                        if (!in_array($device->external_user_id, $userIds)) {
                            array_push($userIds, $device->external_user_id);

                            if (isset($result->receiveCampaignType)) {
                                $totalReceiveInAppUsers = $result->no_of_users_who_receive_campaigns;

                                if ($result->receiveCampaignType == 1) {
                                    $i = $i + 1;
                                    if ($i > $totalReceiveInAppUsers) {
                                        continue;
                                    }
                                } else if ($result->receiveCampaignType == 2) {
                                    $totalSendInAppUsers = $this->webhook_model->countWebhookSendHistoryByWebhookId($webhook_id);
                                    if ($totalReceiveInAppUsers < $totalSendInAppUsers) {
                                        continue;
                                    }
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

                        if (!in_array($device->active_device_id, $deviceIds)) {
                            array_push($deviceIds, $device->active_device_id);

                            $send_notitfication_history = array(
                                'webhook_id' => $webhook_id,
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

                            $this->webhook_model->saveNotificationHistory($send_notitfication_history);
                        } // END IF
                    } // END FOREACH
                } // END ELSE
            } // END foreach
        } // END IF
    }

    public function deliverWebhookNotificationHistory() {
        $results = $this->webhook_model->getAllActiveWebhooksHistory(); //echo "<pre>"; print_r($results); //exit;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $app_group_id = $result->app_group_id;
                $notification_id = $result->notification_id;
                $campaignName = $result->campaignName;
                $headers = $result->requestHeadersPairs;
                $request_body = $result->jsonkeyValuePairs;
                if (!empty($headers)) {
                    $headers = json_decode($result->requestHeadersPairs, true);
                }
                if (!empty($headers)) {
                    $request_body = json_decode($result->jsonkeyValuePairs, true);
                }
                $webhook_url = $result->webhook_url;
                $dataType = $result->request_body;
                $http_request = $result->http_request;
                $plaintext = $result->plaintext;

                /* Replace content is webhook */
                $userRow = $this->brand_model->getExternalUserByUserId($result->external_user_id);
                if (count($userRow) > 0) {
                    $date_of_birth = $userRow->date_of_birth;
                    $email_address = $userRow->email;
                    $first_name = $userRow->firstName;
                    $last_name = $userRow->lastName;
                    $gender = $userRow->gender;
                    $phone_number = $userRow->phoneNumber;
                    $time_zone = $userRow->timezone;
                    $userRow = $this->brand_model->getExternalUserActiveRow($userRow->external_user_id);
                    if (count($userRow) > 0) {
                        $last_used_app_date = $userRow->dateTime;
                        $most_recent_app_version = $userRow->sdk_version;
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
                    $last_used_app_date = '';
                    $most_recent_app_version = '';
                    $phone_number = '';
                    $time_zone = '';
                    $username = '';
                }
                $set_user_to_unsubscribed_url = base_url() . "hurreeEmail/unsubscribe/" . base64_encode($email_address);
                $set_user_to_subscribed_url = base_url() . "hurreeEmail/subscribe/" . base64_encode($email_address);

                $request_body = str_ireplace('{{${date_of_birth}}}', $date_of_birth, $request_body);
                $request_body = str_ireplace('{{${email_address}}}', $email_address, $request_body);
                $request_body = str_ireplace('{{${first_name}}}', $first_name, $request_body);
                $request_body = str_ireplace('{{${last_name}}}', $last_name, $request_body);
                $request_body = str_ireplace('{{${gender}}}', $gender, $request_body);
                $request_body = str_ireplace('{{${last_used_app_date}}}', $last_used_app_date, $request_body);
                $request_body = str_ireplace('{{${most_recent_app_version}}}', $most_recent_app_version, $request_body);
                $request_body = str_ireplace('{{${phone_number}}}', $phone_number, $request_body);
                $request_body = str_ireplace('{{${time_zone}}}', $time_zone, $request_body);
                $request_body = str_ireplace('{{${username}}}', $username, $request_body);
                $request_body = str_ireplace('{{campaign.${name}}}', $campaignName, $request_body);
                $request_body = str_ireplace('{{${set_user_to_unsubscribed_url}}}', $set_user_to_unsubscribed_url, $request_body);
                $request_body = str_ireplace('{{${set_user_to_subscribed_url}}}', $set_user_to_subscribed_url, $request_body);

                $response = $this->httpRequest($webhook_url, $http_request, $request_body, $headers);

                //print_r($response); exit;
                /*                 * ***************************************** */
                $update = array('is_send' => '1', 'id' => $notification_id, 'http_response' => $response);
                $this->webhook_model->sendNotificationSendHistory($update);
            }
        }
    }

    function httpRequest($request_url, $method_name, $request_body, $headers = false) {
        /* Set the Request Url (without Parameters) here */
        $webhook_url = $request_url;

        /* Which Request Method do I want to use DELETE, GET, POST or PUT */
        $http_request = $method_name;

        /* all Request Parameters () */
        $api_request_parameters = $request_body;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($http_request == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($api_request_parameters));
        }

        if ($http_request == 'GET') {
            $webhook_url .= '?' . http_build_query($api_request_parameters);
        }

        if ($http_request == 'POST') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($api_request_parameters));
        }

        if ($http_request == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($api_request_parameters));
        }

        /* Here you can set the Response Content Type you prefer to get :
          application/json, application/xml, text/html, text/plain, etc */ //array('Accept: application/json')
        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        } else {
            /*  Yes we want to get the Response Header (it will be mixed with the response body but we'll separate that after) */
            curl_setopt($ch, CURLOPT_HEADER, false);
        }
        /*  Let's give the Request Url to Curl */
        curl_setopt($ch, CURLOPT_URL, $webhook_url);

        /*  Allows Curl to connect to an API server through HTTPS */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //The maximum number of seconds to allow cURL functions to execute.
        /*  Let's get the Response ! */
        $api_response = curl_exec($ch);

        /* We need to get Curl infos for the header_size and the http_code */
        $api_response_info = curl_getinfo($ch);

        /* close Curl */
        curl_close($ch);

        /* Here we separate the Response Header from the Response Body */
        //$api_response_header = trim(substr($api_response, 0, $api_response_info['header_size']));
        //$api_response_body = substr($api_response, $api_response_info['header_size']);
        // Response HTTP Status Code
        $output = '';
        if ($api_response_info['http_code'] == 200) {
            $output = 'success';
        } else {
            $output = $api_response_info['http_code'];
        }
        return $output;
        /* echo $api_response_info['http_code'];

          // Response Header
          echo $api_response_header;

          // Response Body
          echo $api_response_body; */
    }

    function httpGet($url, $params, $headers = false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch,CURLOPT_HEADER, false);
        $output = curl_exec($ch);
        if ($output === false) {
            $output = 0;
        } else {
            $output = 1;
        }
        curl_close($ch);
        return $output;
    }

    function httpPost($url, $params, $headers = false) {
        $postData = '';
        //create name value pairs seperated by &
        foreach ($params as $k => $v) {
            $postData .= $k . '=' . $v . '&';
        }
        $postData = rtrim($postData, '&');
        //print_r($postData);exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $output = curl_exec($ch);
        if ($output === false) {
            $output = 0;
        } else {
            $output = 1;
        }
        curl_close($ch);
        return $output;
    }

}
