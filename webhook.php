<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Webhook extends CI_Controller {

	public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree', 'cookie', 'permission_helper', 'permission'));

        $this->load->model(array('webhook_model','user_model', 'administrator_model', 'groupapp_model', 'country_model', 'permission_model', 'email_model', 'campaign_model', 'role_model', 'brand_model'));
        $header['allPermision'] = $this->_getpermission();
        emailConfig();
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    public function _getpermission ()
    {
    	$login = $this->administrator_model->front_login_session();

      if(isset($login->user_id) && isset($login->usertype)){

      	$userid= $login->user_id;
      	$usertype= $login->usertype;

      	if($usertype == 9) {

      		$allPermision = getAssignPermission( $userid);
      	    	//  print_r($data['allPermision']); die;
      	  return $allPermision;
      	} else {
      		return false;
      	}
      } else {
      		return false;
      }
    }


    function saveWebhook(){

    $login = $this->administrator_model->front_login_session();
    $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    $additional_profit = $header['loggedInUser']->additional_profit;

    $json = file_get_contents('php://input');

    $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
        	
            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['list_id'] = $param->campaignList;
            $save['webhook_url'] = $param->webhook_url;
            $save['request_body'] = $param->request_body;
            $save['http_request'] = $param->http_request;
            $save['plaintext'] = $param->plaintext;

            if($param->jsonkeyValuePairs != ''){
            	$save['jsonkeyValuePairs'] = json_encode($param->jsonkeyValuePairs);
            }else{
            	$save['jsonkeyValuePairs'] = '';
            }
            
            if($param->requestHeadersPairs != ''){
            	$save['requestHeadersPairs'] = json_encode($param->requestHeadersPairs);
            }else{
            	$save['requestHeadersPairs'] = '';
            }
            

            $save["delivery_type"] = $param->deliveryType;
            if ($param->deliveryType == 1) {
                $save["time_based_scheduling"] = $param->time_based_scheduling;
            }

            if ($param->deliveryType == 1) {
                if ($param->time_based_scheduling == 1) {

                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;

                    if ($param->campaignId != '') {

                        $save["send"] = '';
                        $save["starting_at_hour"] = '';
                        $save["starting_at_min"] = '';
                        $save["starting_at_am_pm"] = '';
                        $save["once_date"] = '';

                        $save["everyDay"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["weekday"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["everyMonth"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["send_campaign_to_users_in_their_local_time_zone"] = '';
                    }
                }
                if ($param->time_based_scheduling == 2) {

                    $save["send"] = $param->send;
                    $save["starting_at_hour"] = $param->starting_at_hour;
                    $save["starting_at_min"] = $param->starting_at_min;
                    $save["starting_at_am_pm"] = $param->starting_at_am_pm;
                    if ($param->send == 'once') {


                        $save["once_date"] = date('Y-m-d', strtotime($param->on_date));

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->on_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {
                            $save["everyDay"] = '';
                            $save["beginning_date"] = '';
                            $save["ending"] = '';
                            $save["ending_on_the_date"] = '';
                            $save["ending_after_occurances"] = '';

                            $save["weekday"] = '';
                            $save["everyMonth"] = '';
                        }
                    } elseif ($param->send == 'daily') {

                        $save["everyDay"] = $param->everyDay;

                        $save["beginning_date"] = date('Y-m-d', strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        if ($param->ending == 'never' || $param->ending == 'after') {
                            $save["ending_on_the_date"] = '';
                        } else {
                            $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));
                        }

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->beginning_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {

                            $save["once_date"] = '';

                            $save["weekday"] = '';


                            $save["everyMonth"] = '';
                        }
                    } elseif ($param->send == 'weekly') {

                        //$save["everyWeeks"] = $param->everyWeek;
                        $weekday = implode(",", $param->weekday);
                        $save["weekday"] = $weekday;

                        $save["beginning_date"] = date('Y-m-d', strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        if ($param->ending == 'never' || $param->ending == 'after') {
                            $save["ending_on_the_date"] = '';
                        } else {
                            $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));
                        }


                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->beginning_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["everyMonth"] = '';
                        }
                    } elseif ($param->send == 'monthly') {

                        $save["everyMonth"] = $param->everyMonth;

                        $save["beginning_date"] = date('Y-m-d', strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        if ($param->ending == 'never' || $param->ending == 'after') {
                            $save["ending_on_the_date"] = '';
                        } else {
                            $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));
                        }

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->beginning_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["weekday"] = '';
                        }
                    }

                    $save["send_campaign_to_users_in_their_local_time_zone"] = $param->send_campaign_to_users_in_their_local_time_zone;
                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;
                }

                if ($param->time_based_scheduling == 3) {

                    $save["intelligent_send"] = $param->intelligent_send;

                    $save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                    $save["specific_start_hours"] = $param->specific_start_hours;
                    $save["specific_start_mins"] = $param->specific_start_mins;
                    $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                    $save["specific_end_hours"] = $param->specific_end_hours;
                    $save["specific_end_mins"] = $param->specific_end_mins;
                    $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                    if ($param->intelligent_send == 'once') {

                        $save["intelligent_on_date"] = date('Y-m-d', strtotime($param->intelligent_on_date));

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_on_date)) . " 00:00:00";

                        if ($param->campaignId != '') {

                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_beginning_date"] = '';
                            $save["intelligent_ending"] = '';
                            $save["intelligent_ending_on_the_date"] = '';
                            $save["intelligent_ending_after_occurances"] = '';

                            $save["intelligent_weekday"] = '';

                            $save["intelligent_everyMonth"] = '';
                        }
                    } else if ($param->intelligent_send == 'daily') {
                        $save["intelligent_everyDay"] = $param->intelligent_everyDay;

                        $save["intelligent_beginning_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        if ($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after') {
                            $save["intelligent_ending_on_the_date"] = '';
                        } else {
                            $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));
                        }

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date)) . " 00:00:00";

                        if ($param->campaignId != '') {

                            $save["intelligent_on_date"] = '';
                            $save["intelligent_weekday"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }
                    } else if ($param->intelligent_send == 'weekly') {
                        //$save["intelligent_everyWeek"] = $param->intelligent_everyWeek;

                        $weekday = implode(",", $param->intelligent_weekday);
                        $save["intelligent_weekday"] = $weekday;

                        $save["intelligent_beginning_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        if ($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after') {
                            $save["intelligent_ending_on_the_date"] = '';
                        } else {
                            $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));
                        }


                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date)) . " 00:00:00";

                        if ($param->campaignId != '') {
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }
                    } else if ($param->intelligent_send == 'monthly') {

                        $save["intelligent_everyMonth"] = $param->intelligent_everyMonth;


                        $save["intelligent_beginning_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        if ($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after') {
                            $save["intelligent_ending_on_the_date"] = '';
                        } else {
                            $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));
                        }

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date)) . " 00:00:00";

                        if ($param->campaignId != '') {
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_weekday"] = '';
                        }
                    }
                    if ($param->campaignId != '') {


                        $save["triggerAction"] = '';
                        $save["scheduleDelay"] = '';
                        $save["scheduleDelay_afterTime"] = '';
                        $save["scheduleDelay_afterTimeInterval"] = '';

                        $save["on_the_next_weekday"] = '';
                        $save["on_the_next_deliveryTime"] = '';
                        $save["on_the_next_hours"] = '';
                        $save["on_the_next_mins"] = '';
                        $save["on_the_next_am"] = '';

                        $save["unless_the_user"] = '';

                        $save["campaignDuration_startTime_date"] = '';
                        $save["campaignDuration_startTime_hours"] = '';
                        $save["campaignDuration_startTime_mins"] = '';
                        $save["campaignDuration_startTime_am"] = '';
                        $save["campaignDuration_endTime_date"] = '';
                        $save["campaignDuration_endTime_hours"] = '';
                        $save["campaignDuration_endTime_mins"] = '';
                        $save["campaignDuration_endTime_am"] = '';

                        $save["send_campaign_at_local_time_zone"] = '';

                        $save["sendIfDeliveryTimeFallsOutsideSpecifiedPortion"] = '';
                        $save["specific_start_hours"] = '';
                        $save["specific_start_mins"] = '';
                        $save["specific_start_am_pm"] = '';
                        $save["specific_end_hours"] = '';
                        $save["specific_end_mins"] = '';
                        $save["specific_end_am_pm"] = '';
                    }
                }
            } elseif ($param->deliveryType == 2) {

                //print_r($param);
                $triggerAction = implode(",", $param->triggerAction);
                $save["triggerAction"] = $triggerAction;
                $save["scheduleDelay"] = $param->scheduleDelay;

                $save["notification_send_date"] = date('Y-m-d', strtotime($param->campaignDuration_startTime_date)) . " " . $param->campaignDuration_startTime_hours . ":" . $param->campaignDuration_startTime_mins . ":00 " . $param->campaignDuration_startTime_am;

                if ($param->scheduleDelay == 'After') {
                    $save["scheduleDelay_afterTime"] = $param->scheduleDelay_afterTime;
                    $save["scheduleDelay_afterTimeInterval"] = $param->scheduleDelay_afterTimeInterval;

                    $time = date('Y-m-d', strtotime($param->campaignDuration_startTime_date)) . " " . $param->campaignDuration_startTime_hours . ":" . $param->campaignDuration_startTime_mins;
                    $save["notification_send_date"] = date('Y-m-d H:i:s', strtotime("+ $param->scheduleDelay_afterTime $param->scheduleDelay_afterTimeInterval", strtotime($time))) . " " . $param->campaignDuration_startTime_am;
                } elseif ($param->scheduleDelay == 'On the next') {

                    $save["on_the_next_weekday"] = $param->on_the_next_weekday;
                    $save["on_the_next_deliveryTime"] = $param->on_the_next_deliveryTime;
                    $save["on_the_next_hours"] = $param->on_the_next_hours;
                    $save["on_the_next_mins"] = $param->on_the_next_mins;
                    $save["on_the_next_am"] = $param->on_the_next_am;
                }


                if (is_array($param->unless_the_user)) {
                    $unless_the_user = implode(",", $param->unless_the_user);
                } else {
                    $unless_the_user = '';
                }

                $save["unless_the_user"] = $unless_the_user;

                $save["campaignDuration_startTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_startTime_date));

                $save["campaignDuration_startTime_hours"] = $param->campaignDuration_startTime_hours;
                $save["campaignDuration_startTime_mins"] = $param->campaignDuration_startTime_mins;
                $save["campaignDuration_startTime_am"] = $param->campaignDuration_startTime_am;

                if ($param->campaignDuration_endTime_date == '') {
                    $save["campaignDuration_endTime_date"] = '';
                } else {
                    $save["campaignDuration_endTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_endTime_date));
                }


                $save["campaignDuration_endTime_hours"] = $param->campaignDuration_endTime_hours;
                $save["campaignDuration_endTime_mins"] = $param->campaignDuration_endTime_mins;
                $save["campaignDuration_endTime_am"] = $param->campaignDuration_endTime_am;

                $save["send_campaign_at_local_time_zone"] = $param->send_campaign_at_local_time_zone;

                $save["sendIfDeliveryTimeFallsOutsideSpecifiedPortion"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                $save["specific_start_hours"] = $param->specific_start_hours;
                $save["specific_start_mins"] = $param->specific_start_mins;
                $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                $save["specific_end_hours"] = $param->specific_end_hours;
                $save["specific_end_mins"] = $param->specific_end_mins;
                $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                $save["reEligibleTime"] = $param->reEligibleTime;
                $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                if ($param->campaignId != '') {

                    $save["time_based_scheduling"] = '';

                    $save["send"] = '';
                    $save["starting_at_hour"] = '';
                    $save["starting_at_min"] = '';
                    $save["starting_at_am_pm"] = '';
                    $save["once_date"] = '';

                    $save["everyDay"] = '';
                    $save["beginning_date"] = '';
                    $save["ending"] = '';
                    $save["ending_on_the_date"] = '';
                    $save["ending_after_occurances"] = '';

                    $save["weekday"] = '';
                    $save["everyMonth"] = '';

                    $save["intelligent_send"] = '';
                    $save["intelligent_on_date"] = '';
                    $save["intelligent_everyDay"] = '';
                    $save["intelligent_beginning_date"] = '';
                    $save["intelligent_ending"] = '';
                    $save["intelligent_ending_on_the_date"] = '';
                    $save["intelligent_ending_after_occurances"] = '';

                    $save["intelligent_weekday"] = '';

                    $save["intelligent_everyMonth"] = '';
                }
            }

            if (count($param->segments)) {
                foreach ($param->segments as $segment) {
                    $seg[] = $segment[0];
                }

                $segments = implode(",", $seg);
            } else {
                $segments = '';
            }

            if (count($param->filters)) {
                foreach ($param->filters as $filter) {
                    $fil[] = $filter[0];
                }
                $filters = implode(",", $fil);
            } else {
                $filters = '';
            }
            $save["segments"] = $segments;
            $save["filters"] = $filters;
            $save["send_to_users"] = $param->send_to_users;
            $save["receiveCampaignType"] = $param->receiveCampaignType;
            $save["no_of_users_who_receive_campaigns"] = $param->no_of_users_who_receive_campaigns;
            $save["messages_per_minute"] = $param->messages_per_minute;
            $save["isActive"] = 1;
            $save["isDraft"] = 0;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');
            $campaignId = $param->campaignId;

        } //End foreach

        if ($campaignId == '') {
            $id = $this->webhook_model->saveWebhook($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                        $update['webhook'] = $extraPackage->webhook - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->webhook_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $webhook = $userPackage->webhook;
                        $updateInAppMessaging = $webhook - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

            }

        }else{

            $save["id"] = $campaignId;
            $save["isDraft"] = 0;
            $save["modifiedDate"] = date('YmdHis');
            $this->webhook_model->updateWebhook($save);
            $id = $campaignId;

        }
        echo $id;
  }

  function saveComposeAsDraft(){

    $login = $this->administrator_model->front_login_session();
    $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    $additional_profit = $header['loggedInUser']->additional_profit;

    $json = file_get_contents('php://input');

    $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
        	
            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['list_id'] = $param->campaignList;
            $save['webhook_url'] = $param->webhook_url;
            $save['request_body'] = $param->request_body;
            $save['http_request'] = $param->http_request;
            $save['plaintext'] = $param->plaintext;

            if($param->jsonkeyValuePairs != ''){
            	$save['jsonkeyValuePairs'] = json_encode($param->jsonkeyValuePairs);
            }else{
            	$save['jsonkeyValuePairs'] = '';
            }
            
            if($param->requestHeadersPairs != ''){
            	$save['requestHeadersPairs'] = json_encode($param->requestHeadersPairs);
            }else{
            	$save['requestHeadersPairs'] = '';
            }

            $campaignId = $param->campaignId;

        }

        if ($campaignId == '') {

        	$save["isActive"] = 0;
    		$save["isDraft"] = 1;
    		$save["createdDate"] = date('YmdHis');
    		$save["modifiedDate"] = date('YmdHis');

            $id = $this->webhook_model->saveWebhook($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                        $update['webhook'] = $extraPackage->webhook - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->webhook_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $webhook = $userPackage->webhook;
                        $updateInAppMessaging = $webhook - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

            }

        }else{
            $save["id"] = $campaignId;
            //$save["isDraft"] = 0;
            $save["modifiedDate"] = date('YmdHis');
            $this->webhook_model->updateWebhook($save);
            $id = $campaignId;
        }
        echo 1;
  }

  function saveDeliveryAsDraft(){

  	$login = $this->administrator_model->front_login_session();
    $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    $additional_profit = $header['loggedInUser']->additional_profit;

    $json = file_get_contents('php://input');

    $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
        	
            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['list_id'] = $param->campaignList;
            $save['webhook_url'] = $param->webhook_url;
            $save['request_body'] = $param->request_body;
            $save['http_request'] = $param->http_request;
            $save['plaintext'] = $param->plaintext;

            if($param->jsonkeyValuePairs != ''){
            	$save['jsonkeyValuePairs'] = json_encode($param->jsonkeyValuePairs);
            }else{
            	$save['jsonkeyValuePairs'] = '';
            }
            
            if($param->requestHeadersPairs != ''){
            	$save['requestHeadersPairs'] = json_encode($param->requestHeadersPairs);
            }else{
            	$save['requestHeadersPairs'] = '';
            }

            $save["delivery_type"] = $param->deliveryType;
            if($param->deliveryType == 1){
                $save["time_based_scheduling"] = $param->time_based_scheduling;
            }

            if($param->deliveryType == 1){
                if($param->time_based_scheduling == 1){

                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;

                    if($param->campaignId != ''){

                        $save["send"] = '';
                        $save["starting_at_hour"] = '';
                        $save["starting_at_min"] = '';
                        $save["starting_at_am_pm"] = '';
                        $save["once_date"] = '';

                        $save["everyDay"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["weekday"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["everyMonth"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["send_campaign_to_users_in_their_local_time_zone"] = '';

                    }


                }
                if($param->time_based_scheduling == 2){

                    $save["send"] = $param->send;
                    $save["starting_at_hour"] = $param->starting_at_hour;
                    $save["starting_at_min"] = $param->starting_at_min;
                    $save["starting_at_am_pm"] = $param->starting_at_am_pm;
                    if($param->send == 'once'){

                        $save["once_date"] = date('Y-m-d',strtotime($param->on_date));

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->on_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){
                            $save["everyDay"] = '';
                            $save["beginning_date"] = '';
                            $save["ending"] = '';
                            $save["ending_on_the_date"] = '';
                            $save["ending_after_occurances"] = '';

                            $save["weekday"] = '';
                            $save["everyMonth"] = '';

                        }

                    }
                    elseif($param->send == 'daily'){

                        $save["everyDay"] = $param->everyDay;

                        $save["beginning_date"] = date('Y-m-d',strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        $save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->beginning_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){

                            $save["once_date"] = '';

                            $save["weekday"] = '';


                            $save["everyMonth"] = '';

                        }

                    }

                    elseif($param->send == 'weekly'){

                        //$save["everyWeeks"] = $param->everyWeek;
                        $weekday = implode(",",$param->weekday);
                        $save["weekday"] = $weekday;
                        $save["beginning_date"] = date('Y-m-d',strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        $save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->beginning_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["everyMonth"] = '';

                        }

                    }

                    elseif($param->send == 'monthly'){

                        $save["everyMonth"] = $param->everyMonth;

                        $save["beginning_date"] = date('Y-m-d',strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        $save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->beginning_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["weekday"] = '';

                        }

                    }

                    $save["send_campaign_to_users_in_their_local_time_zone"] = $param->send_campaign_to_users_in_their_local_time_zone;
                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                }

                if($param->time_based_scheduling == 3){

                    $save["intelligent_send"] = $param->intelligent_send;

                    if($param->intelligent_send == 'once'){

                        $save["intelligent_on_date"] = date('Y-m-d',strtotime($param->intelligent_on_date));

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_on_date)). " 00:00:00";

                        if($param->campaignId != ''){

                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_beginning_date"] = '';
                            $save["intelligent_ending"] = '';
                            $save["intelligent_ending_on_the_date"] = '';
                            $save["intelligent_ending_after_occurances"] = '';

                            $save["intelligent_weekday"] = '';

                            $save["intelligent_everyMonth"] = '';

                        }
                    }
                    else if($param->intelligent_send == 'daily'){

                        $save["intelligent_everyDay"] = $param->intelligent_everyDay;

                        $save["intelligent_beginning_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;


                        $save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date)). " 00:00:00";

                        if($param->campaignId != ''){

                            $save["intelligent_on_date"] = '';
                            $save["intelligent_weekday"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }

                    }
                    else if($param->intelligent_send == 'weekly'){
                        //$save["intelligent_everyWeek"] = $param->intelligent_everyWeek;

                        $weekday = implode(",",$param->intelligent_weekday);
                        $save["intelligent_weekday"] = $weekday;

                        $save["intelligent_beginning_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date)). " 00:00:00";

                        if($param->campaignId != ''){
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }

                    }
                    else if($param->intelligent_send == 'monthly'){

                        $save["intelligent_everyMonth"] = $param->intelligent_everyMonth;


                        $save["intelligent_beginning_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date)). " 00:00:00";

                        if($param->campaignId != ''){
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_weekday"] = '';
                        }


                    }

                    if($param->campaignId != ''){


                        $save["triggerAction"] = '';
                        $save["scheduleDelay"] = '';
                        $save["scheduleDelay_afterTime"] = '';
                        $save["scheduleDelay_afterTimeInterval"] = '';

                        $save["on_the_next_weekday"] = '';
                        $save["on_the_next_deliveryTime"] = '';
                        $save["on_the_next_hours"] = '';
                        $save["on_the_next_mins"] = '';
                        $save["on_the_next_am"] = '';

                        $save["unless_the_user"] = '';

                        $save["campaignDuration_startTime_date"] = '';
                        $save["campaignDuration_startTime_hours"] = '';
                        $save["campaignDuration_startTime_mins"] = '';
                        $save["campaignDuration_startTime_am"] = '';
                        $save["campaignDuration_endTime_date"] = '';
                        $save["campaignDuration_endTime_hours"] = '';
                        $save["campaignDuration_endTime_mins"] = '';
                        $save["campaignDuration_endTime_am"] = '';

                        $save["send_campaign_at_local_time_zone"] = '';

                        $save["sendIfDeliveryTimeFallsOutsideSpecifiedPortion"] = '';
                        $save["specific_start_hours"] = '';
                        $save["specific_start_mins"] = '';
                        $save["specific_start_am_pm"] = '';
                        $save["specific_end_hours"] = '';
                        $save["specific_end_mins"] = '';
                        $save["specific_end_am_pm"] = '';
                    }

                    $save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                    $save["specific_start_hours"] = $param->specific_start_hours;
                    $save["specific_start_mins"] = $param->specific_start_mins;
                    $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                    $save["specific_end_hours"] = $param->specific_end_hours;
                    $save["specific_end_mins"] = $param->specific_end_mins;
                    $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;


                }

            }elseif($param->deliveryType == 2){


                //print_r($param);
                $triggerAction = implode(",", $param->triggerAction);
                $save["triggerAction"] = $triggerAction;
                $save["scheduleDelay"] = $param->scheduleDelay;

                $save["notification_send_date"] = date('Y-m-d',strtotime($param->campaignDuration_startTime_date)). " ".$param->campaignDuration_startTime_hours.":".$param->campaignDuration_startTime_mins.":00 ".$param->campaignDuration_startTime_am;

                if($param->scheduleDelay == 'After'){
                    $save["scheduleDelay_afterTime"] = $param->scheduleDelay_afterTime;
                    $save["scheduleDelay_afterTimeInterval"] = $param->scheduleDelay_afterTimeInterval;

                    $time = date('Y-m-d',strtotime($param->campaignDuration_startTime_date)). " ".$param->campaignDuration_startTime_hours.":".$param->campaignDuration_startTime_mins;
                    $save["notification_send_date"] = date('Y-m-d H:i:s', strtotime("+ $param->scheduleDelay_afterTime $param->scheduleDelay_afterTimeInterval", strtotime($time)))." " .$param->campaignDuration_startTime_am;
                }
                elseif($param->scheduleDelay == 'On the next'){

                    $save["on_the_next_weekday"] = $param->on_the_next_weekday;
                    $save["on_the_next_deliveryTime"] = $param->on_the_next_deliveryTime;
                    $save["on_the_next_hours"] = $param->on_the_next_hours;
                    $save["on_the_next_mins"] = $param->on_the_next_mins;
                    $save["on_the_next_am"] = $param->on_the_next_am;
                }


                if(is_array($param->unless_the_user)){
                    $unless_the_user = implode(",",$param->unless_the_user);
                }else{
                    $unless_the_user = '';
                }

                $save["unless_the_user"] = $unless_the_user;

                $save["campaignDuration_startTime_date"] = date('Y-m-d',strtotime($param->campaignDuration_startTime_date));

                $save["campaignDuration_startTime_hours"] = $param->campaignDuration_startTime_hours;
                $save["campaignDuration_startTime_mins"] = $param->campaignDuration_startTime_mins;
                $save["campaignDuration_startTime_am"] = $param->campaignDuration_startTime_am;

                $save["campaignDuration_endTime_date"] = date('Y-m-d',strtotime($param->campaignDuration_endTime_date));

                $save["campaignDuration_endTime_hours"] = $param->campaignDuration_endTime_hours;
                $save["campaignDuration_endTime_mins"] = $param->campaignDuration_endTime_mins;
                $save["campaignDuration_endTime_am"] = $param->campaignDuration_endTime_am;

                $save["send_campaign_at_local_time_zone"] = $param->send_campaign_at_local_time_zone;

                $save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                $save["specific_start_hours"] = $param->specific_start_hours;
                $save["specific_start_mins"] = $param->specific_start_mins;
                $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                $save["specific_end_hours"] = $param->specific_end_hours;
                $save["specific_end_mins"] = $param->specific_end_mins;
                $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                $save["reEligibleTime"] = $param->reEligibleTime;
                $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                if($param->campaignId != ''){

                    $save["time_based_scheduling"] = '';

                    $save["send"] = '';
                    $save["starting_at_hour"] = '';
                    $save["starting_at_min"] = '';
                    $save["starting_at_am_pm"] = '';
                    $save["once_date"] = '';

                    $save["everyDay"] = '';
                    $save["beginning_date"] = '';
                    $save["ending"] = '';
                    $save["ending_on_the_date"] = '';
                    $save["ending_after_occurances"] = '';

                    $save["weekday"] = '';
                    $save["everyMonth"] = '';

                    $save["intelligent_send"] = '';
                    $save["intelligent_on_date"] = '';
                    $save["intelligent_everyDay"] = '';
                    $save["intelligent_beginning_date"] = '';
                    $save["intelligent_ending"] = '';
                    $save["intelligent_ending_on_the_date"] = '';
                    $save["intelligent_ending_after_occurances"] = '';

                    $save["intelligent_weekday"] = '';

                    $save["intelligent_everyMonth"] = '';


                }

            }


            $save["isActive"] = 0;
            $save["isDraft"] = 1;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');

            $campaignId = $param->campaignId;

        } //End foreach

        if ($campaignId == '') {

            $id = $this->webhook_model->saveWebhook($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                        $update['webhook'] = $extraPackage->webhook - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->webhook_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $webhook = $userPackage->webhook;
                        $updateInAppMessaging = $webhook - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

            }

        }else{

            $save["id"] = $campaignId;
            $save["modifiedDate"] = date('YmdHis');
            $this->webhook_model->updateWebhook($save);
            $id = $campaignId;

        }
        echo 1;
  }

  function saveTargetAsDraft(){

  	$login = $this->administrator_model->front_login_session();
    $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    $additional_profit = $header['loggedInUser']->additional_profit;

    $json = file_get_contents('php://input');

    $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
        	
            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['list_id'] = $param->campaignList;
            $save['webhook_url'] = $param->webhook_url;
            $save['request_body'] = $param->request_body;
            $save['http_request'] = $param->http_request;
            $save['plaintext'] = $param->plaintext;

            if($param->jsonkeyValuePairs != ''){
            	$save['jsonkeyValuePairs'] = json_encode($param->jsonkeyValuePairs);
            }else{
            	$save['jsonkeyValuePairs'] = '';
            }
            
            if($param->requestHeadersPairs != ''){
            	$save['requestHeadersPairs'] = json_encode($param->requestHeadersPairs);
            }else{
            	$save['requestHeadersPairs'] = '';
            }

            $save["delivery_type"] = $param->deliveryType;
            if($param->deliveryType == 1){
                $save["time_based_scheduling"] = $param->time_based_scheduling;
            }

            if($param->deliveryType == 1){
                if($param->time_based_scheduling == 1){

                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;

                    if($param->campaignId != ''){

                        $save["send"] = '';
                        $save["starting_at_hour"] = '';
                        $save["starting_at_min"] = '';
                        $save["starting_at_am_pm"] = '';
                        $save["once_date"] = '';

                        $save["everyDay"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["weekday"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["everyMonth"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["send_campaign_to_users_in_their_local_time_zone"] = '';

                    }


                }
                if($param->time_based_scheduling == 2){

                    $save["send"] = $param->send;
                    $save["starting_at_hour"] = $param->starting_at_hour;
                    $save["starting_at_min"] = $param->starting_at_min;
                    $save["starting_at_am_pm"] = $param->starting_at_am_pm;
                    if($param->send == 'once'){

                        $save["once_date"] = date('Y-m-d',strtotime($param->on_date));

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->on_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){
                            $save["everyDay"] = '';
                            $save["beginning_date"] = '';
                            $save["ending"] = '';
                            $save["ending_on_the_date"] = '';
                            $save["ending_after_occurances"] = '';

                            $save["weekday"] = '';
                            $save["everyMonth"] = '';

                        }

                    }
                    elseif($param->send == 'daily'){

                        $save["everyDay"] = $param->everyDay;

                        $save["beginning_date"] = date('Y-m-d',strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        $save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->beginning_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){

                            $save["once_date"] = '';

                            $save["weekday"] = '';


                            $save["everyMonth"] = '';

                        }

                    }

                    elseif($param->send == 'weekly'){

                        //$save["everyWeeks"] = $param->everyWeek;
                        $weekday = implode(",",$param->weekday);
                        $save["weekday"] = $weekday;
                        $save["beginning_date"] = date('Y-m-d',strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        $save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->beginning_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["everyMonth"] = '';

                        }

                    }

                    elseif($param->send == 'monthly'){

                        $save["everyMonth"] = $param->everyMonth;

                        $save["beginning_date"] = date('Y-m-d',strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        $save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->beginning_date)). " ".$param->starting_at_hour.":".$param->starting_at_min.":00"." ".$param->starting_at_am_pm;

                        if($param->campaignId != ''){

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["weekday"] = '';

                        }

                    }

                    $save["send_campaign_to_users_in_their_local_time_zone"] = $param->send_campaign_to_users_in_their_local_time_zone;
                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                }

                if($param->time_based_scheduling == 3){

                    $save["intelligent_send"] = $param->intelligent_send;

                    if($param->intelligent_send == 'once'){

                        $save["intelligent_on_date"] = date('Y-m-d',strtotime($param->intelligent_on_date));

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_on_date)). " 00:00:00";

                        if($param->campaignId != ''){

                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_beginning_date"] = '';
                            $save["intelligent_ending"] = '';
                            $save["intelligent_ending_on_the_date"] = '';
                            $save["intelligent_ending_after_occurances"] = '';

                            $save["intelligent_weekday"] = '';

                            $save["intelligent_everyMonth"] = '';

                        }
                    }
                    else if($param->intelligent_send == 'daily'){

                        $save["intelligent_everyDay"] = $param->intelligent_everyDay;

                        $save["intelligent_beginning_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;


                        $save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date)). " 00:00:00";

                        if($param->campaignId != ''){

                            $save["intelligent_on_date"] = '';
                            $save["intelligent_weekday"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }

                    }
                    else if($param->intelligent_send == 'weekly'){
                        //$save["intelligent_everyWeek"] = $param->intelligent_everyWeek;

                        $weekday = implode(",",$param->intelligent_weekday);
                        $save["intelligent_weekday"] = $weekday;

                        $save["intelligent_beginning_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date)). " 00:00:00";

                        if($param->campaignId != ''){
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }

                    }
                    else if($param->intelligent_send == 'monthly'){

                        $save["intelligent_everyMonth"] = $param->intelligent_everyMonth;


                        $save["intelligent_beginning_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d',strtotime($param->intelligent_beginning_date)). " 00:00:00";

                        if($param->campaignId != ''){
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_weekday"] = '';
                        }


                    }

                    if($param->campaignId != ''){


                        $save["triggerAction"] = '';
                        $save["scheduleDelay"] = '';
                        $save["scheduleDelay_afterTime"] = '';
                        $save["scheduleDelay_afterTimeInterval"] = '';

                        $save["on_the_next_weekday"] = '';
                        $save["on_the_next_deliveryTime"] = '';
                        $save["on_the_next_hours"] = '';
                        $save["on_the_next_mins"] = '';
                        $save["on_the_next_am"] = '';

                        $save["unless_the_user"] = '';

                        $save["campaignDuration_startTime_date"] = '';
                        $save["campaignDuration_startTime_hours"] = '';
                        $save["campaignDuration_startTime_mins"] = '';
                        $save["campaignDuration_startTime_am"] = '';
                        $save["campaignDuration_endTime_date"] = '';
                        $save["campaignDuration_endTime_hours"] = '';
                        $save["campaignDuration_endTime_mins"] = '';
                        $save["campaignDuration_endTime_am"] = '';

                        $save["send_campaign_at_local_time_zone"] = '';

                        $save["sendIfDeliveryTimeFallsOutsideSpecifiedPortion"] = '';
                        $save["specific_start_hours"] = '';
                        $save["specific_start_mins"] = '';
                        $save["specific_start_am_pm"] = '';
                        $save["specific_end_hours"] = '';
                        $save["specific_end_mins"] = '';
                        $save["specific_end_am_pm"] = '';
                    }

                    $save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                    $save["specific_start_hours"] = $param->specific_start_hours;
                    $save["specific_start_mins"] = $param->specific_start_mins;
                    $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                    $save["specific_end_hours"] = $param->specific_end_hours;
                    $save["specific_end_mins"] = $param->specific_end_mins;
                    $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;


                }

            }elseif($param->deliveryType == 2){


                //print_r($param);
                $triggerAction = implode(",", $param->triggerAction);
                $save["triggerAction"] = $triggerAction;
                $save["scheduleDelay"] = $param->scheduleDelay;

                $save["notification_send_date"] = date('Y-m-d',strtotime($param->campaignDuration_startTime_date)). " ".$param->campaignDuration_startTime_hours.":".$param->campaignDuration_startTime_mins.":00 ".$param->campaignDuration_startTime_am;

                if($param->scheduleDelay == 'After'){
                    $save["scheduleDelay_afterTime"] = $param->scheduleDelay_afterTime;
                    $save["scheduleDelay_afterTimeInterval"] = $param->scheduleDelay_afterTimeInterval;

                    $time = date('Y-m-d',strtotime($param->campaignDuration_startTime_date)). " ".$param->campaignDuration_startTime_hours.":".$param->campaignDuration_startTime_mins;
                    $save["notification_send_date"] = date('Y-m-d H:i:s', strtotime("+ $param->scheduleDelay_afterTime $param->scheduleDelay_afterTimeInterval", strtotime($time)))." " .$param->campaignDuration_startTime_am;
                }
                elseif($param->scheduleDelay == 'On the next'){

                    $save["on_the_next_weekday"] = $param->on_the_next_weekday;
                    $save["on_the_next_deliveryTime"] = $param->on_the_next_deliveryTime;
                    $save["on_the_next_hours"] = $param->on_the_next_hours;
                    $save["on_the_next_mins"] = $param->on_the_next_mins;
                    $save["on_the_next_am"] = $param->on_the_next_am;
                }


                if(is_array($param->unless_the_user)){
                    $unless_the_user = implode(",",$param->unless_the_user);
                }else{
                    $unless_the_user = '';
                }

                $save["unless_the_user"] = $unless_the_user;

                $save["campaignDuration_startTime_date"] = date('Y-m-d',strtotime($param->campaignDuration_startTime_date));

                $save["campaignDuration_startTime_hours"] = $param->campaignDuration_startTime_hours;
                $save["campaignDuration_startTime_mins"] = $param->campaignDuration_startTime_mins;
                $save["campaignDuration_startTime_am"] = $param->campaignDuration_startTime_am;

                $save["campaignDuration_endTime_date"] = date('Y-m-d',strtotime($param->campaignDuration_endTime_date));

                $save["campaignDuration_endTime_hours"] = $param->campaignDuration_endTime_hours;
                $save["campaignDuration_endTime_mins"] = $param->campaignDuration_endTime_mins;
                $save["campaignDuration_endTime_am"] = $param->campaignDuration_endTime_am;

                $save["send_campaign_at_local_time_zone"] = $param->send_campaign_at_local_time_zone;

                $save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                $save["specific_start_hours"] = $param->specific_start_hours;
                $save["specific_start_mins"] = $param->specific_start_mins;
                $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                $save["specific_end_hours"] = $param->specific_end_hours;
                $save["specific_end_mins"] = $param->specific_end_mins;
                $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                $save["reEligibleTime"] = $param->reEligibleTime;
                $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                if($param->campaignId != ''){

                    $save["time_based_scheduling"] = '';

                    $save["send"] = '';
                    $save["starting_at_hour"] = '';
                    $save["starting_at_min"] = '';
                    $save["starting_at_am_pm"] = '';
                    $save["once_date"] = '';

                    $save["everyDay"] = '';
                    $save["beginning_date"] = '';
                    $save["ending"] = '';
                    $save["ending_on_the_date"] = '';
                    $save["ending_after_occurances"] = '';

                    $save["weekday"] = '';
                    $save["everyMonth"] = '';

                    $save["intelligent_send"] = '';
                    $save["intelligent_on_date"] = '';
                    $save["intelligent_everyDay"] = '';
                    $save["intelligent_beginning_date"] = '';
                    $save["intelligent_ending"] = '';
                    $save["intelligent_ending_on_the_date"] = '';
                    $save["intelligent_ending_after_occurances"] = '';

                    $save["intelligent_weekday"] = '';

                    $save["intelligent_everyMonth"] = '';


                }

            }

            //Start Target Section
            if(isset($param->segments)){
                if(is_array($param->segments)){
                    foreach($param->segments as $segment){
                        $seg[] = $segment[0];
                    }

                    $segments = implode(",",$seg);
                }else{
                    $segments = '';
                }
            }else{
                $segments = '';
            }

            if(isset($param->filters)){
                if(is_array($param->filters)){
                    foreach($param->filters as $filter){
                        $fil[] = $filter[0];
                    }
                    $filters = implode(",",$fil);
                }else{
                    $filters = '';
                }
            }else{
                $filters = '';
            }
            $save["segments"] = $segments;
            $save["filters"] = $filters;
            $save["send_to_users"] = $param->send_to_users;
            $save["receiveCampaignType"] = $param->receiveCampaignType;
            $save["no_of_users_who_receive_campaigns"] = $param->no_of_users_who_receive_campaigns;
            $save["messages_per_minute"] = $param->messages_per_minute;


            $save["isActive"] = 0;
            $save["isDraft"] = 1;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');

            $campaignId = $param->campaignId;

        } //End foreach

        if ($campaignId == '') {

            $id = $this->webhook_model->saveWebhook($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                        $update['webhook'] = $extraPackage->webhook - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->webhook_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $webhook = $userPackage->webhook;
                        $updateInAppMessaging = $webhook - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

            }

        }else{

            $save["id"] = $campaignId;
            $save["modifiedDate"] = date('YmdHis');
            $this->webhook_model->updateWebhook($save);
            $id = $campaignId;

        }
        echo 1;


  }

  function saveWebhookAsDraft(){

    $login = $this->administrator_model->front_login_session();
    $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    $additional_profit = $header['loggedInUser']->additional_profit;

    $json = file_get_contents('php://input');

    $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
        	
            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['list_id'] = $param->campaignList;
            $save['webhook_url'] = $param->webhook_url;
            $save['request_body'] = $param->request_body;
            $save['http_request'] = $param->http_request;
            $save['plaintext'] = $param->plaintext;

            if($param->jsonkeyValuePairs != ''){
            	$save['jsonkeyValuePairs'] = json_encode($param->jsonkeyValuePairs);
            }else{
            	$save['jsonkeyValuePairs'] = '';
            }
            
            if($param->requestHeadersPairs != ''){
            	$save['requestHeadersPairs'] = json_encode($param->requestHeadersPairs);
            }else{
            	$save['requestHeadersPairs'] = '';
            }
            

            $save["delivery_type"] = $param->deliveryType;
            if ($param->deliveryType == 1) {
                $save["time_based_scheduling"] = $param->time_based_scheduling;
            }

            if ($param->deliveryType == 1) {
                if ($param->time_based_scheduling == 1) {

                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;

                    if ($param->campaignId != '') {

                        $save["send"] = '';
                        $save["starting_at_hour"] = '';
                        $save["starting_at_min"] = '';
                        $save["starting_at_am_pm"] = '';
                        $save["once_date"] = '';

                        $save["everyDay"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["weekday"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["everyMonth"] = '';
                        $save["beginning_date"] = '';
                        $save["ending"] = '';
                        $save["ending_on_the_date"] = '';
                        $save["ending_after_occurances"] = '';

                        $save["send_campaign_to_users_in_their_local_time_zone"] = '';
                    }
                }
                if ($param->time_based_scheduling == 2) {

                    $save["send"] = $param->send;
                    $save["starting_at_hour"] = $param->starting_at_hour;
                    $save["starting_at_min"] = $param->starting_at_min;
                    $save["starting_at_am_pm"] = $param->starting_at_am_pm;
                    if ($param->send == 'once') {


                        $save["once_date"] = date('Y-m-d', strtotime($param->on_date));

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->on_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {
                            $save["everyDay"] = '';
                            $save["beginning_date"] = '';
                            $save["ending"] = '';
                            $save["ending_on_the_date"] = '';
                            $save["ending_after_occurances"] = '';

                            $save["weekday"] = '';
                            $save["everyMonth"] = '';
                        }
                    } elseif ($param->send == 'daily') {

                        $save["everyDay"] = $param->everyDay;

                        $save["beginning_date"] = date('Y-m-d', strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        if ($param->ending == 'never' || $param->ending == 'after') {
                            $save["ending_on_the_date"] = '';
                        } else {
                            $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));
                        }

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->beginning_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {

                            $save["once_date"] = '';

                            $save["weekday"] = '';


                            $save["everyMonth"] = '';
                        }
                    } elseif ($param->send == 'weekly') {

                        //$save["everyWeeks"] = $param->everyWeek;
                        $weekday = implode(",", $param->weekday);
                        $save["weekday"] = $weekday;

                        $save["beginning_date"] = date('Y-m-d', strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        if ($param->ending == 'never' || $param->ending == 'after') {
                            $save["ending_on_the_date"] = '';
                        } else {
                            $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));
                        }


                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->beginning_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["everyMonth"] = '';
                        }
                    } elseif ($param->send == 'monthly') {

                        $save["everyMonth"] = $param->everyMonth;

                        $save["beginning_date"] = date('Y-m-d', strtotime($param->beginning_date));

                        $save["ending"] = $param->ending;

                        if ($param->ending == 'never' || $param->ending == 'after') {
                            $save["ending_on_the_date"] = '';
                        } else {
                            $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));
                        }

                        $save["ending_after_occurances"] = $param->ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->beginning_date)) . " " . $param->starting_at_hour . ":" . $param->starting_at_min . ":00" . " " . $param->starting_at_am_pm;

                        if ($param->campaignId != '') {

                            $save["once_date"] = '';

                            $save["everyDay"] = '';


                            $save["weekday"] = '';
                        }
                    }

                    $save["send_campaign_to_users_in_their_local_time_zone"] = $param->send_campaign_to_users_in_their_local_time_zone;
                    $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                    $save["reEligibleTime"] = $param->reEligibleTime;
                    $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                    $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;
                }

                if ($param->time_based_scheduling == 3) {

                    $save["intelligent_send"] = $param->intelligent_send;

                    $save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                    $save["specific_start_hours"] = $param->specific_start_hours;
                    $save["specific_start_mins"] = $param->specific_start_mins;
                    $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                    $save["specific_end_hours"] = $param->specific_end_hours;
                    $save["specific_end_mins"] = $param->specific_end_mins;
                    $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                    if ($param->intelligent_send == 'once') {

                        $save["intelligent_on_date"] = date('Y-m-d', strtotime($param->intelligent_on_date));

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_on_date)) . " 00:00:00";

                        if ($param->campaignId != '') {

                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_beginning_date"] = '';
                            $save["intelligent_ending"] = '';
                            $save["intelligent_ending_on_the_date"] = '';
                            $save["intelligent_ending_after_occurances"] = '';

                            $save["intelligent_weekday"] = '';

                            $save["intelligent_everyMonth"] = '';
                        }
                    } else if ($param->intelligent_send == 'daily') {
                        $save["intelligent_everyDay"] = $param->intelligent_everyDay;

                        $save["intelligent_beginning_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        if ($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after') {
                            $save["intelligent_ending_on_the_date"] = '';
                        } else {
                            $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));
                        }

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date)) . " 00:00:00";

                        if ($param->campaignId != '') {

                            $save["intelligent_on_date"] = '';
                            $save["intelligent_weekday"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }
                    } else if ($param->intelligent_send == 'weekly') {
                        //$save["intelligent_everyWeek"] = $param->intelligent_everyWeek;

                        $weekday = implode(",", $param->intelligent_weekday);
                        $save["intelligent_weekday"] = $weekday;

                        $save["intelligent_beginning_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        if ($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after') {
                            $save["intelligent_ending_on_the_date"] = '';
                        } else {
                            $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));
                        }


                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date)) . " 00:00:00";

                        if ($param->campaignId != '') {
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_everyMonth"] = '';
                        }
                    } else if ($param->intelligent_send == 'monthly') {

                        $save["intelligent_everyMonth"] = $param->intelligent_everyMonth;


                        $save["intelligent_beginning_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date));

                        $save["intelligent_ending"] = $param->intelligent_ending;

                        if ($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after') {
                            $save["intelligent_ending_on_the_date"] = '';
                        } else {
                            $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));
                        }

                        $save["intelligent_ending_after_occurances"] = $param->intelligent_ending_after_occurances;

                        $save["notification_send_date"] = date('Y-m-d', strtotime($param->intelligent_beginning_date)) . " 00:00:00";

                        if ($param->campaignId != '') {
                            $save["intelligent_on_date"] = '';
                            $save["intelligent_everyDay"] = '';
                            $save["intelligent_weekday"] = '';
                        }
                    }
                    if ($param->campaignId != '') {


                        $save["triggerAction"] = '';
                        $save["scheduleDelay"] = '';
                        $save["scheduleDelay_afterTime"] = '';
                        $save["scheduleDelay_afterTimeInterval"] = '';

                        $save["on_the_next_weekday"] = '';
                        $save["on_the_next_deliveryTime"] = '';
                        $save["on_the_next_hours"] = '';
                        $save["on_the_next_mins"] = '';
                        $save["on_the_next_am"] = '';

                        $save["unless_the_user"] = '';

                        $save["campaignDuration_startTime_date"] = '';
                        $save["campaignDuration_startTime_hours"] = '';
                        $save["campaignDuration_startTime_mins"] = '';
                        $save["campaignDuration_startTime_am"] = '';
                        $save["campaignDuration_endTime_date"] = '';
                        $save["campaignDuration_endTime_hours"] = '';
                        $save["campaignDuration_endTime_mins"] = '';
                        $save["campaignDuration_endTime_am"] = '';

                        $save["send_campaign_at_local_time_zone"] = '';

                        $save["sendIfDeliveryTimeFallsOutsideSpecifiedPortion"] = '';
                        $save["specific_start_hours"] = '';
                        $save["specific_start_mins"] = '';
                        $save["specific_start_am_pm"] = '';
                        $save["specific_end_hours"] = '';
                        $save["specific_end_mins"] = '';
                        $save["specific_end_am_pm"] = '';
                    }
                }
            } elseif ($param->deliveryType == 2) {

                //print_r($param);
                $triggerAction = implode(",", $param->triggerAction);
                $save["triggerAction"] = $triggerAction;
                $save["scheduleDelay"] = $param->scheduleDelay;

                $save["notification_send_date"] = date('Y-m-d', strtotime($param->campaignDuration_startTime_date)) . " " . $param->campaignDuration_startTime_hours . ":" . $param->campaignDuration_startTime_mins . ":00 " . $param->campaignDuration_startTime_am;

                if ($param->scheduleDelay == 'After') {
                    $save["scheduleDelay_afterTime"] = $param->scheduleDelay_afterTime;
                    $save["scheduleDelay_afterTimeInterval"] = $param->scheduleDelay_afterTimeInterval;

                    $time = date('Y-m-d', strtotime($param->campaignDuration_startTime_date)) . " " . $param->campaignDuration_startTime_hours . ":" . $param->campaignDuration_startTime_mins;
                    $save["notification_send_date"] = date('Y-m-d H:i:s', strtotime("+ $param->scheduleDelay_afterTime $param->scheduleDelay_afterTimeInterval", strtotime($time))) . " " . $param->campaignDuration_startTime_am;
                } elseif ($param->scheduleDelay == 'On the next') {

                    $save["on_the_next_weekday"] = $param->on_the_next_weekday;
                    $save["on_the_next_deliveryTime"] = $param->on_the_next_deliveryTime;
                    $save["on_the_next_hours"] = $param->on_the_next_hours;
                    $save["on_the_next_mins"] = $param->on_the_next_mins;
                    $save["on_the_next_am"] = $param->on_the_next_am;
                }


                if (is_array($param->unless_the_user)) {
                    $unless_the_user = implode(",", $param->unless_the_user);
                } else {
                    $unless_the_user = '';
                }

                $save["unless_the_user"] = $unless_the_user;

                $save["campaignDuration_startTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_startTime_date));

                $save["campaignDuration_startTime_hours"] = $param->campaignDuration_startTime_hours;
                $save["campaignDuration_startTime_mins"] = $param->campaignDuration_startTime_mins;
                $save["campaignDuration_startTime_am"] = $param->campaignDuration_startTime_am;

                if ($param->campaignDuration_endTime_date == '') {
                    $save["campaignDuration_endTime_date"] = '';
                } else {
                    $save["campaignDuration_endTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_endTime_date));
                }


                $save["campaignDuration_endTime_hours"] = $param->campaignDuration_endTime_hours;
                $save["campaignDuration_endTime_mins"] = $param->campaignDuration_endTime_mins;
                $save["campaignDuration_endTime_am"] = $param->campaignDuration_endTime_am;

                $save["send_campaign_at_local_time_zone"] = $param->send_campaign_at_local_time_zone;

                $save["sendIfDeliveryTimeFallsOutsideSpecifiedPortion"] = $param->send_this_campaign_during_a_specific_portion_of_day;
                $save["specific_start_hours"] = $param->specific_start_hours;
                $save["specific_start_mins"] = $param->specific_start_mins;
                $save["specific_start_am_pm"] = $param->specific_start_am_pm;
                $save["specific_end_hours"] = $param->specific_end_hours;
                $save["specific_end_mins"] = $param->specific_end_mins;
                $save["specific_end_am_pm"] = $param->specific_end_am_pm;

                $save["reEligibleTime"] = $param->reEligibleTime;
                $save["reEligibleTimeInterval"] = $param->reEligibleTimeInterval;
                $save["reEligible_to_receive_campaign"] = $param->reEligible_to_receive_campaign;
                $save["ignore_frequency_capping_settings"] = $param->ignore_frequency_capping_settings;

                if ($param->campaignId != '') {

                    $save["time_based_scheduling"] = '';

                    $save["send"] = '';
                    $save["starting_at_hour"] = '';
                    $save["starting_at_min"] = '';
                    $save["starting_at_am_pm"] = '';
                    $save["once_date"] = '';

                    $save["everyDay"] = '';
                    $save["beginning_date"] = '';
                    $save["ending"] = '';
                    $save["ending_on_the_date"] = '';
                    $save["ending_after_occurances"] = '';

                    $save["weekday"] = '';
                    $save["everyMonth"] = '';

                    $save["intelligent_send"] = '';
                    $save["intelligent_on_date"] = '';
                    $save["intelligent_everyDay"] = '';
                    $save["intelligent_beginning_date"] = '';
                    $save["intelligent_ending"] = '';
                    $save["intelligent_ending_on_the_date"] = '';
                    $save["intelligent_ending_after_occurances"] = '';

                    $save["intelligent_weekday"] = '';

                    $save["intelligent_everyMonth"] = '';
                }
            }

            if (count($param->segments)) {
                foreach ($param->segments as $segment) {
                    $seg[] = $segment[0];
                }

                $segments = implode(",", $seg);
            } else {
                $segments = '';
            }

            if (count($param->filters)) {
                foreach ($param->filters as $filter) {
                    $fil[] = $filter[0];
                }
                $filters = implode(",", $fil);
            } else {
                $filters = '';
            }
            $save["segments"] = $segments;
            $save["filters"] = $filters;
            $save["send_to_users"] = $param->send_to_users;
            $save["receiveCampaignType"] = $param->receiveCampaignType;
            $save["no_of_users_who_receive_campaigns"] = $param->no_of_users_who_receive_campaigns;
            $save["messages_per_minute"] = $param->messages_per_minute;
            $save["isActive"] = 0;
            $save["isDraft"] = 1;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');
            $campaignId = $param->campaignId;

        } //End foreach

        if ($campaignId == '') {
            $id = $this->webhook_model->saveWebhook($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                        $update['webhook'] = $extraPackage->webhook - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->webhook_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $webhook = $userPackage->webhook;
                        $updateInAppMessaging = $webhook - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

            }

        }else{
            $save["id"] = $campaignId;
            $save["modifiedDate"] = date('YmdHis');
            $this->webhook_model->updateWebhook($save);
            $id = $campaignId;
        }
        echo 1;
  }

  function webhookListPagination() {

        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];

            $newStatusCount = $_POST["newStatusCount"];

            $this->session->set_userdata("webhookPagination", $newStatusCount);

            $max_status_id = @$_POST['status_id'];

            $businessId = $_POST['businessId'];

            $start = $statuscount;
            if ($header['login']->usertype == 8) {
                $data['webhooks'] = $this->webhook_model->getAllWebhooks($businessId, $start, $noofStatus);
            }
            if ($header['login']->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($header['login']->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $data['webhooks'] = $this->webhook_model->getWebhooks($AppUserCampaigns, $start, $noofStatus);
            }
            if (count($data['webhooks']) > 0) {
                $this->load->view('3.1/addmore_webhooks', $data);
            }
        } else {

            redirect(base_url());
        }
    }

    function deleteWebhookPopUp($campaignId){
        $data['campaignId'] = $campaignId;
        $this->load->view('3.1/delete_webhook', $data);
    }

    function deleteWebhook() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $webhookId = $_POST['webhookId'];
        $update['id'] = $_POST['webhookId'];
        $update['isDelete'] = 1;
        $this->webhook_model->updateWebhook($update);

        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;

        $param = $this->webhook_model->getDeletedWebhook($webhookId);

        $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);
        
        if ($param->platform == 'webhook') {
            if ($additional_profit != 1) {
                if (count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                    $updateExtraPackage['webhook'] = $extraPackage->webhook + 1;

                    $updateExtraPackage['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                    $this->webhook_model->updateBrandUserExtraPackage($updateExtraPackage);
                } else {
                    //Update total campaigns
                    $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                    $webhook = $userPackage->webhook;
                    $updateWebhook = $webhook + 1;

                    $update = array(
                        'user_pro_id' => $userPackage->user_pro_id,
                        'webhook' => $updateWebhook
                    );
                    $this->campaign_model->updateBrandUserTotalCampaigns($update);
                }
            }
        }

        echo 1;
    }
    
    function confirmationlaunch(){
        $this->load->view('3.1/webhookconfirmationlaunch');
    }

    function editconfirmationlaunch(){
        $this->load->view('3.1/editwebhookconfirmationlaunch');
    }

    function saveAutomation(){
        $login = $this->administrator_model->front_login_session();
    $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    $additional_profit = $header['loggedInUser']->additional_profit;

    $json = file_get_contents('php://input');

    $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
        	
            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['list_id'] = $param->campaignList;
            $save['webhook_url'] = $param->webhook_url;
            $save['request_body'] = $param->request_body;
            $save['http_request'] = $param->http_request;
            $save['plaintext'] = $param->plaintext;

            if($param->jsonkeyValuePairs != ''){
            	$save['jsonkeyValuePairs'] = json_encode($param->jsonkeyValuePairs);
            }else{
            	$save['jsonkeyValuePairs'] = '';
            }
            
            if($param->requestHeadersPairs != ''){
            	$save['requestHeadersPairs'] = json_encode($param->requestHeadersPairs);
            }else{
            	$save['requestHeadersPairs'] = '';
            }

            $campaignId = $param->campaignId;

        }

        if ($campaignId == '') {

        	$save["isActive"] = 0;
    		$save["isDraft"] = 0;
    		$save["createdDate"] = date('YmdHis');
    		$save["modifiedDate"] = date('YmdHis');

            $id = $this->webhook_model->saveWebhook($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->webhook > 0)) {
                        $update['webhook'] = $extraPackage->webhook - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->webhook_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $webhook = $userPackage->webhook;
                        $updateInAppMessaging = $webhook - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

            }

        }else{
            $save["id"] = $campaignId;
            //$save["isDraft"] = 0;
            $save["modifiedDate"] = date('YmdHis');
            $this->webhook_model->updateWebhook($save);
            $id = $campaignId;
        }
        echo 1;
    }
    
}