<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Crosschannel extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->helper(array('hurree','cookie', 'salesforce_helper', 'permission_helper', 'permission'));

		$this->load->library(array('form_validation', 'pagination'));

		$this->load->model(array('user_model', 'brand_model', 'payment_model', 'administrator_model', 'groupapp_model', 'notification_model', 'country_model', 'permission_model', 'location_model', 'email_model', 'campaign_model', 'reward_model', 'businessstore_model','offer_model','geofence_model','role_model','contact_model','hubSpot_model','crosschannel_model'));
		$header['allPermision'] = $this->_getpermission();
		emailConfig();
		$login = $this->administrator_model->front_login_session();
		if (isset($login->active) && $login->active == 0) {
			redirect(base_url());
		}
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

function saveCrossChannel(){

	$login = $this->administrator_model->front_login_session();
	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    		if($param->push_icon != ''){

    			$ime = $param->push_icon;

    			$image = explode(';base64,',  $ime);
    			$size = getimagesize($ime);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $image[1]);
    			//$img_cont=$image[1];
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['push_icon'] = $filename;

    		}else{
    			if($param->campaignId == ''){
    				$save['push_icon'] = $param->push_icon;
    			}else{
    				$dataCampaign = $this->crosschannel_model->getCampaign($param->campaignId);
    				$save['push_icon'] = $dataCampaign->push_icon;

    			}
    		}

    		if($param->push_img_url != ''){
    			$save['push_icon'] = '';
    			$save['push_img_url'] = $param->push_img_url;
    		}else{
    			if($param->campaignId == ''){
    				$save['push_img_url'] = '';
    			}else{
    				if($save['push_icon'] != ''){
    					$save['push_img_url'] = '';
    				}else{
	    				$dataCampaign = $this->crosschannel_model->getCampaign($param->campaignId);
	    				$save['push_img_url'] = $dataCampaign->push_img_url;
    				}
    			}
    		}

    		if($param->expandedImage != ''){

    			$imexpanded = $param->expandedImage;

    			$imageExpanded = explode(';base64,',  $imexpanded);
    			$size = getimagesize($imexpanded);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    			//$img_cont=$image[1];
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename1 = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['expandedImage'] = $filename1;
    		}else{

    			if($param->campaignId == ''){
    				$save['expandedImage'] = $param->expandedImage;
    			}else{
    				$dataCampaign = $this->crosschannel_model->getCampaign($param->campaignId);
    				$save['expandedImage'] = $dataCampaign->expandedImage;

    			}
    		}

    		if($param->expanded_img_url != ''){
    			$save['expandedImage'] = '';
    			$save['expanded_img_url'] = $param->expanded_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['expanded_img_url'] = '';
    			}else{
    				$dataCampaign = $this->crosschannel_model->getCampaign($param->campaignId);
    				if($save['expandedImage'] != ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    				}
    			}
    		}

    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
        $save['persona_user_id'] = $param->campaignPersonaUser;
		    $save['list_id'] = $param->campaignList;
    		$save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    			$save['push_title'] = $param->push_title;
    			$save['push_message'] = $param->push_message;
    			$save['email_subject'] = $param->email_subject;
                        $message = str_replace("&lbrace;","{",$param->email_message);
                        $message = str_replace("&rbrace;","}",$message);
                        $message = str_replace("&dollar;","$",$message);
    			$save['email_message'] = $message;
    			$save['displayName'] = $param->displayName;
    			$save['fromAddress'] = $param->fromAddress;
    			$save['replyToAddress'] = $param->replyToAddress;
    			$save['summery_text'] = $param->summery_text;


    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;

    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;

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

    				if($param->ending == 'never' || $param->ending == 'after'){
    					$save["ending_on_the_date"] = '';
    				}else{
    					$save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));
    				}

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

    				if($param->ending == 'never' || $param->ending == 'after'){
    					$save["ending_on_the_date"] = '';
    				}else{
    					$save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));
    				}


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

    				if($param->ending == 'never' || $param->ending == 'after'){
    					$save["ending_on_the_date"] = '';
    				}else{
    					$save["ending_on_the_date"] = date('Y-m-d',strtotime($param->ending_on_the_date));
    				}

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
    			$save["send_this_campaign_during_a_specific_portion_of_day"] = $param->send_this_campaign_during_a_specific_portion_of_day;
    			$save["specific_start_hours"] = $param->specific_start_hours;
    			$save["specific_start_mins"] = $param->specific_start_mins;
    			$save["specific_start_am_pm"] = $param->specific_start_am_pm;
    			$save["specific_end_hours"] = $param->specific_end_hours;
    			$save["specific_end_mins"] = $param->specific_end_mins;
    			$save["specific_end_am_pm"] = $param->specific_end_am_pm;

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

    				if($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after'){
    					$save["intelligent_ending_on_the_date"] = '';
    				}else{
    					$save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));
    				}

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

    				if($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after'){
    					$save["intelligent_ending_on_the_date"] = '';
    				}else{
    					$save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));
    				}


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

    				if($param->intelligent_ending == 'never' || $param->intelligent_ending == 'after'){
    					$save["intelligent_ending_on_the_date"] = '';
    				}else{
    					$save["intelligent_ending_on_the_date"] = date('Y-m-d',strtotime($param->intelligent_ending_on_the_date));
    				}

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

    		if($param->campaignDuration_endTime_date == ''){
    			$save["campaignDuration_endTime_date"] = '';
    		}else{
    			$save["campaignDuration_endTime_date"] = date('Y-m-d',strtotime($param->campaignDuration_endTime_date));
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

    	if(count($param->segments)){
	    	foreach($param->segments as $segment){
	    		$seg[] = $segment[0];
	    	}

	    	$segments = implode(",",$seg);
    	}else{
    		$segments = '';
    	}

    	if(count($param->filters)){
    		foreach($param->filters as $filter){
    			$fil[] = $filter[0];
    		}
    		$filters = implode(",",$fil);
    	}else{
    		$filters = '';
    	}
    	$save["segments"] = $segments;
    	$save["filters"] = $filters;
    	$save["send_to_users"] = $param->send_to_users;
    	$save["receiveCampaignType"] = $param->receiveCampaignType;
    	$save["no_of_users_who_receive_campaigns"] = $param->no_of_users_who_receive_campaigns;
    	$save["messages_per_minute"] = $param->messages_per_minute;
    	$save["isActive"] = 1;
    	//$save["type"] = $param->type;
    	$save["createdDate"] = date('YmdHis');
    	$save["modifiedDate"] = date('YmdHis');
    	$campaignId = $param->campaignId;
    	}//End foreach

    	if($campaignId == ''){
    		$id = $this->campaign_model->savePushNotificationCampaign($save);

    		$login = $this->administrator_model->front_login_session();
    		$businessId = $login->businessId;

    		$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    		if($param->selectedPlatform == 'cross'){
    			if($additional_profit != 1){
		    		if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
		    			$update['crossChannel'] = $extraPackage->crossChannel - 1;
		    			$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
		    			$this->crosschannel_model->updateBrandUserExtraPackage($update);
		    		} else {
		    			//Update total campaigns
		    			$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
		    			$crossChannel = $userPackage->crossChannel;
		    			$updateCrossChannel = $crossChannel - 1;

		    			$update = array(
		    					'user_pro_id' => $userPackage->user_pro_id,
		    					'crossChannel' => $updateCrossChannel
		    			);
		    			$this->campaign_model->updateBrandUserTotalCampaigns($update);

		    		}

    			}
    		}


    	}else{
    		$save["id"] = $campaignId;
    		$save["isDraft"] = 0;
    		$save["modifiedDate"] = date('YmdHis');
    		$this->campaign_model->updatePushNotificationCampaign($save);
    		$id = $campaignId;
    	}
    	echo $id;
    }

    function crossChannelListPagination() {


    	$header['login'] = $this->administrator_model->front_login_session();

    	if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

    		//// Fetch Value From Post
    		$totalrecord = $_POST['totalrecord'];
    		$statuscount = $_POST['statuscount'];
    		$noofStatus = $_POST['noofstatus'];

    		$newStatusCount = $_POST["newStatusCount"];

    		$this->session->set_userdata("crossChannelPagination",$newStatusCount);

    		$max_status_id = @$_POST['status_id'];

    		$businessId = $_POST['businessId'];

    		$start = $statuscount;
    		if($header['login']->usertype == 8){
    			$data['push_campaigns'] = $this->crosschannel_model->getCrossChannelByBusinessId($businessId, $start, $noofStatus);
    		}
    		if($header['login']->usertype == 9){

    			$groups = $this->campaign_model->getUserGroups($header['login']->user_id);

    			foreach($groups as $group){

    				$AppUserCampaigns[] = $group->app_group_id;

    			}

    			$data['push_campaigns'] = $this->crosschannel_model->getCrossChannel($AppUserCampaigns, $start, $noofStatus);
    		}

    		if (count($data['push_campaigns']) > 0) {
    			$this->load->view('3.1/addmorecrosschannel', $data);
    		}
    	} else {

    		redirect(base_url());
    	}
    }


    function saveComposeAsDraft(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    			if($param->push_icon != ''){

    				$ime = $param->push_icon;

    				$image = explode(';base64,',  $ime);
    				$size = getimagesize($ime);
    				$type = $size['mime'];
    				$typea = explode('/', $type);
    				$extnsn = $typea[1];
    				$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    				$img_cont = str_replace(' ', '+', $image[1]);
    				$data = base64_decode($img_cont);
    				$im = imagecreatefromstring($data);
    				$filename = time() . '.'.$extnsn;

    				$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    				// code for upload image in folder
    				imagealphablending($im, false);
    				imagesavealpha($im, true);

    				if (in_array($extnsn, $valid_exts)) {
    					$quality = 0;
    					if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    						$quality = round((100 - $quality) * 0.09);

    						$resp = imagejpeg($im, $fullpath,$quality);
    					}else if($extnsn == 'png'){

    						$resp = imagepng($im, $fullpath);
    					}else if($extnsn == 'gif'){

    						$resp = imagegif($im, $fullpath);
    					}
    				}
    				$save['push_icon'] = $filename;
    			}else{

    				if($param->campaignId == ''){
    					$save['push_icon'] = $param->push_icon;
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['push_icon'] = $dataCampaign->push_icon;

    				}

    			}

    			if($param->push_img_url != ''){
    				$save['push_img_url'] = $param->push_img_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['push_img_url'] = '';
    				}else{
    					if($save['push_icon'] != ''){
    						$save['push_img_url'] = '';
    					}else{
    						$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    						$save['push_img_url'] = $dataCampaign->push_img_url;
    					}
    				}
    			}

    			if($param->expandedImage != ''){

    				$imexpanded = $param->expandedImage;

    				$imageExpanded = explode(';base64,',  $imexpanded);
    				$size = getimagesize($imexpanded);
    				$type = $size['mime'];
    				$typea = explode('/', $type);
    				$extnsn = $typea[1];
    				$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    				$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    				//$img_cont=$image[1];
    				$data = base64_decode($img_cont);
    				$im = imagecreatefromstring($data);
    				$filename1 = time() . '.'.$extnsn;

    				$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    				// code for upload image in folder
    				imagealphablending($im, false);
    				imagesavealpha($im, true);

    				if (in_array($extnsn, $valid_exts)) {
    					$quality = 0;
    					if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    						$quality = round((100 - $quality) * 0.09);

    						$resp = imagejpeg($im, $fullpath,$quality);
    					}else if($extnsn == 'png'){

    						$resp = imagepng($im, $fullpath);
    					}else if($extnsn == 'gif'){

    						$resp = imagegif($im, $fullpath);
    					}
    				}
    				$save['expandedImage'] = $filename1;
    			}else{

    				if($param->campaignId == ''){
    					$save['expandedImage'] = $param->expandedImage;
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['expandedImage'] = $dataCampaign->expandedImage;

    				}
    			}

    			if($param->expanded_img_url != ''){
    				$save['expanded_img_url'] = $param->expanded_img_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					if($save['expandedImage'] != ''){
    						$save['expanded_img_url'] = '';
    					}else{
    						$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    					}
    				}
    			}


    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
    		$save['push_title'] = $param->push_title;
    		$save['push_message'] = $param->push_message;
    		//$save['email_subject'] = $param->email_subject;
    		//$save['email_message'] = $param->email_message;
    		//$save['displayName'] = $param->displayName;
    		//$save['fromAddress'] = $param->fromAddress;
    		//$save['replyToAddress'] = $param->replyToAddress;
    		$save['summery_text'] = $param->summery_text;
                $save['persona_user_id'] = $param->campaignPersonaUser;
		$save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;
    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;


    		if($param->campaignId == ''){
    			$save["isActive"] = 0;
    			$save["isDraft"] = 1;

    			//$save["type"] = $param->type;
    			$save["createdDate"] = date('YmdHis');
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->savePushNotificationCampaign($save);

    			$login = $this->administrator_model->front_login_session();
    			$businessId = $login->businessId;

    			$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    		if($param->selectedPlatform == 'cross'){
    			if($additional_profit != 1){
		    		if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
		    			$update['crossChannel'] = $extraPackage->crossChannel - 1;
		    			$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
		    			$this->crosschannel_model->updateBrandUserExtraPackage($update);
		    		} else {
		    			//Update total campaigns
		    			$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
		    			$crossChannel = $userPackage->crossChannel;
		    			$updateCrossChannel = $crossChannel - 1;

		    			$update = array(
		    					'user_pro_id' => $userPackage->user_pro_id,
		    					'crossChannel' => $updateCrossChannel
		    			);
		    			$this->campaign_model->updateBrandUserTotalCampaigns($update);

		    		}
    			}

    		}

    		}else{
    			$save['id'] = $param->campaignId;
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->updatePushNotificationCampaign($save);
    		}
    		echo 1;


    	}
    }

    function saveEmailAsDraft(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    		if($param->push_icon != ''){

    			$ime = $param->push_icon;

    			$image = explode(';base64,',  $ime);
    			$size = getimagesize($ime);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $image[1]);
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['push_icon'] = $filename;
    		}else{

    			if($param->campaignId == ''){
    				$save['push_icon'] = $param->push_icon;
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				$save['push_icon'] = $dataCampaign->push_icon;

    			}

    		}

    		if($param->push_img_url != ''){
    			$save['push_img_url'] = $param->push_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['push_img_url'] = '';
    			}else{
    				if($save['push_icon'] != ''){
    					$save['push_img_url'] = '';
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['push_img_url'] = $dataCampaign->push_img_url;
    				}
    			}
    		}

    		if($param->expandedImage != ''){

    			$imexpanded = $param->expandedImage;

    			$imageExpanded = explode(';base64,',  $imexpanded);
    			$size = getimagesize($imexpanded);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    			//$img_cont=$image[1];
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename1 = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['expandedImage'] = $filename1;
    		}else{

    			if($param->campaignId == ''){
    				$save['expandedImage'] = $param->expandedImage;
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				$save['expandedImage'] = $dataCampaign->expandedImage;

    			}
    		}

    		if($param->expanded_img_url != ''){
    			$save['expanded_img_url'] = $param->expanded_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['expanded_img_url'] = '';
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				if($save['expandedImage'] != ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    				}
    			}
    		}


    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
    		$save['push_title'] = $param->push_title;
    		$save['push_message'] = $param->push_message;
    		$save['summery_text'] = $param->summery_text;
		$save['persona_user_id'] = $param->campaignPersonaUser;
		$save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['email_subject'] = $param->email_subject;
    		$message = str_replace("&lbrace;","{",$param->email_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['email_message'] = $message;
    		$save['displayName'] = $param->displayName;
    		$save['fromAddress'] = $param->fromAddress;
    		$save['replyToAddress'] = $param->replyToAddress;
    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;
    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;

    		if($param->campaignId == ''){
    			$save["isActive"] = 0;
    			$save["isDraft"] = 1;

    			//$save["type"] = $param->type;
    			$save["createdDate"] = date('YmdHis');
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->savePushNotificationCampaign($save);

    			$login = $this->administrator_model->front_login_session();
    			$businessId = $login->businessId;

    			$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    			if($param->selectedPlatform == 'cross'){
    				if($additional_profit != 1){
	    				if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
	    					$update['crossChannel'] = $extraPackage->crossChannel - 1;
	    					$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
	    					$this->crosschannel_model->updateBrandUserExtraPackage($update);
	    				} else {
	    					//Update total campaigns
	    					$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
	    					$crossChannel = $userPackage->crossChannel;
	    					$updateCrossChannel = $crossChannel - 1;

	    					$update = array(
	    							'user_pro_id' => $userPackage->user_pro_id,
	    							'crossChannel' => $updateCrossChannel
	    					);
	    					$this->campaign_model->updateBrandUserTotalCampaigns($update);

	    				}
    				}
    			}

    		}else{
    			$save['id'] = $param->campaignId;
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->updatePushNotificationCampaign($save);
    		}
    		echo 1;

    	}
    }

    function saveDeliveryAsDraft(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    		if($param->push_icon != ''){

    			$ime = $param->push_icon;

    			$image = explode(';base64,',  $ime);
    			$size = getimagesize($ime);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $image[1]);
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['push_icon'] = $filename;
    		}else{

    			if($param->campaignId == ''){
    				$save['push_icon'] = $param->push_icon;
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				$save['push_icon'] = $dataCampaign->push_icon;

    			}

    		}

    		if($param->push_img_url != ''){
    			$save['push_img_url'] = $param->push_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['push_img_url'] = '';
    			}else{
    				if($save['push_icon'] != ''){
    					$save['push_img_url'] = '';
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['push_img_url'] = $dataCampaign->push_img_url;
    				}
    			}
    		}

    		if($param->expandedImage != ''){

    			$imexpanded = $param->expandedImage;

    			$imageExpanded = explode(';base64,',  $imexpanded);
    			$size = getimagesize($imexpanded);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    			//$img_cont=$image[1];
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename1 = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['expandedImage'] = $filename1;
    		}else{

    			if($param->campaignId == ''){
    				$save['expandedImage'] = $param->expandedImage;
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				$save['expandedImage'] = $dataCampaign->expandedImage;

    			}
    		}

    		if($param->expanded_img_url != ''){
    			$save['expanded_img_url'] = $param->expanded_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['expanded_img_url'] = '';
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				if($save['expandedImage'] != ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    				}
    			}
    		}


    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
    		$save['push_title'] = $param->push_title;
    		$save['push_message'] = $param->push_message;
    		$save['summery_text'] = $param->summery_text;
		$save['persona_user_id'] = $param->campaignPersonaUser;
		$save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['email_subject'] = $param->email_subject;
    		$message = str_replace("&lbrace;","{",$param->email_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['email_message'] = $message;
    		$save['displayName'] = $param->displayName;
    		$save['fromAddress'] = $param->fromAddress;
    		$save['replyToAddress'] = $param->replyToAddress;
    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;
    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;

    		$save["isActive"] = 0;
    		$save["isDraft"] = 1;
    		$save["createdDate"] = date('YmdHis');
    		$save["modifiedDate"] = date('YmdHis');
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


    			if(isset($param->triggerAction)){
    				if(is_array($param->triggerAction)){
    					$triggerAction = implode(",", $param->triggerAction);
    				}else{
    					$triggerAction = '';
    				}
    			}else{
    				$triggerAction = '';
    			}

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

    		if($param->campaignId == ''){

    			$this->campaign_model->savePushNotificationCampaign($save);

    			$login = $this->administrator_model->front_login_session();
    			$businessId = $login->businessId;

    			$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    			if($param->selectedPlatform == 'cross'){
    				if($additional_profit != 1){
	    				if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
	    					$update['crossChannel'] = $extraPackage->crossChannel - 1;
	    					$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
	    					$this->crosschannel_model->updateBrandUserExtraPackage($update);
	    				} else {
	    					//Update total campaigns
	    					$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
	    					$crossChannel = $userPackage->crossChannel;
	    					$updateCrossChannel = $crossChannel - 1;

	    					$update = array(
	    							'user_pro_id' => $userPackage->user_pro_id,
	    							'crossChannel' => $updateCrossChannel
	    					);
	    					$this->campaign_model->updateBrandUserTotalCampaigns($update);

	    				}
    				}

    			}

    		}else{
    			$save['id'] = $param->campaignId;
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->updatePushNotificationCampaign($save);
    		}
    		echo 1;
    	}
    }

    function saveTargetAsDraft(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    		if($param->push_icon != ''){

    			$ime = $param->push_icon;

    			$image = explode(';base64,',  $ime);
    			$size = getimagesize($ime);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $image[1]);
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['push_icon'] = $filename;
    		}else{

    			if($param->campaignId == ''){
    				$save['push_icon'] = $param->push_icon;
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				$save['push_icon'] = $dataCampaign->push_icon;

    			}

    		}

    		if($param->push_img_url != ''){
    			$save['push_img_url'] = $param->push_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['push_img_url'] = '';
    			}else{
    				if($save['push_icon'] != ''){
    					$save['push_img_url'] = '';
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['push_img_url'] = $dataCampaign->push_img_url;
    				}
    			}
    		}

    		if($param->expandedImage != ''){

    			$imexpanded = $param->expandedImage;

    			$imageExpanded = explode(';base64,',  $imexpanded);
    			$size = getimagesize($imexpanded);
    			$type = $size['mime'];
    			$typea = explode('/', $type);
    			$extnsn = $typea[1];
    			$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    			$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    			//$img_cont=$image[1];
    			$data = base64_decode($img_cont);
    			$im = imagecreatefromstring($data);
    			$filename1 = time() . '.'.$extnsn;

    			$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    			// code for upload image in folder
    			imagealphablending($im, false);
    			imagesavealpha($im, true);

    			if (in_array($extnsn, $valid_exts)) {
    				$quality = 0;
    				if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    					$quality = round((100 - $quality) * 0.09);

    					$resp = imagejpeg($im, $fullpath,$quality);
    				}else if($extnsn == 'png'){

    					$resp = imagepng($im, $fullpath);
    				}else if($extnsn == 'gif'){

    					$resp = imagegif($im, $fullpath);
    				}
    			}
    			$save['expandedImage'] = $filename1;
    		}else{

    			if($param->campaignId == ''){
    				$save['expandedImage'] = $param->expandedImage;
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				$save['expandedImage'] = $dataCampaign->expandedImage;

    			}
    		}

    		if($param->expanded_img_url != ''){
    			$save['expanded_img_url'] = $param->expanded_img_url;
    		}else{

    			if($param->campaignId == ''){
    				$save['expanded_img_url'] = '';
    			}else{
    				$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    				if($save['expandedImage'] != ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    				}
    			}
    		}


    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
    		$save['push_title'] = $param->push_title;
    		$save['push_message'] = $param->push_message;
    		$save['summery_text'] = $param->summery_text;
		$save['persona_user_id'] = $param->campaignPersonaUser;
		$save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['email_subject'] = $param->email_subject;
                $message = str_replace("&lbrace;","{",$param->email_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
    		$save['email_message'] = $message;
    		$save['displayName'] = $param->displayName;
    		$save['fromAddress'] = $param->fromAddress;
    		$save['replyToAddress'] = $param->replyToAddress;
    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;
    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;

    		$save["isActive"] = 0;
    		$save["isDraft"] = 1;
    		$save["createdDate"] = date('YmdHis');
    		$save["modifiedDate"] = date('YmdHis');
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


    			if(isset($param->triggerAction)){
    				if(is_array($param->triggerAction)){
    					$triggerAction = implode(",", $param->triggerAction);
    				}else{
    					$triggerAction = '';
    				}
    			}else{
    				$triggerAction = '';
    			}

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
    	}//End foreach

    	if($param->campaignId == ''){
    		$this->campaign_model->savePushNotificationCampaign($save);

    		$login = $this->administrator_model->front_login_session();
    		$businessId = $login->businessId;

    		$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    		if($param->selectedPlatform == 'cross'){
    			if($additional_profit != 1){
	    			if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
	    				$update['crossChannel'] = $extraPackage->crossChannel - 1;
	    				$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
	    				$this->crosschannel_model->updateBrandUserExtraPackage($update);
	    			} else {
	    				//Update total campaigns
	    				$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
	    				$crossChannel = $userPackage->crossChannel;
	    				$updateCrossChannel = $crossChannel - 1;

	    				$update = array(
	    						'user_pro_id' => $userPackage->user_pro_id,
	    						'crossChannel' => $updateCrossChannel
	    				);
	    				$this->campaign_model->updateBrandUserTotalCampaigns($update);

	    			}
    			}

    		}
    	}else{
    		$save['id'] = $param->campaignId;
    		$save["modifiedDate"] = date('YmdHis');
    		$this->campaign_model->updatePushNotificationCampaign($save);
    	}
    	echo 1;
    }

    function saveCrossChannelDraft(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){
    		//print_r($param); die;

    			if($param->push_icon != ''){
    				$ime = $param->push_icon;
    				$image = explode(';base64,',  $ime);
    				$size = getimagesize($ime);
    				$type = $size['mime'];
    				$typea = explode('/', $type);
    				$extnsn = $typea[1];
    				$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    				$img_cont = str_replace(' ', '+', $image[1]);
    				//$img_cont=$image[1];
    				$data = base64_decode($img_cont);
    				$im = imagecreatefromstring($data);
    				$filename = time() . '.'.$extnsn;

    				$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    				// code for upload image in folder
    				imagealphablending($im, false);
    				imagesavealpha($im, true);

    				if (in_array($extnsn, $valid_exts)) {
    					$quality = 0;
    					if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    						$quality = round((100 - $quality) * 0.09);

    						$resp = imagejpeg($im, $fullpath,$quality);
    					}else if($extnsn == 'png'){

    						$resp = imagepng($im, $fullpath);
    					}else if($extnsn == 'gif'){

    						$resp = imagegif($im, $fullpath);
    					}
    				}
    				$save['push_icon'] = $filename;
    			}else{
    				if($param->campaignId == ''){
    					$save['push_icon'] = $param->push_icon;
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['push_icon'] = $dataCampaign->push_icon;

    				}
    			}

    			if($param->push_img_url != ''){
    				$save['push_img_url'] = $param->push_img_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['push_img_url'] = '';
    				}else{
    					if($save['push_icon'] != ''){
    						$save['push_img_url'] = '';
    					}else{
    						$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    						$save['push_img_url'] = $dataCampaign->push_img_url;
    					}
    				}
    			}

    			if($param->expandedImage != ''){

    				$imexpanded = $param->expandedImage;

    				$imageExpanded = explode(';base64,',  $imexpanded);
    				$size = getimagesize($imexpanded);
    				$type = $size['mime'];
    				$typea = explode('/', $type);
    				$extnsn = $typea[1];
    				$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    				$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    				//$img_cont=$image[1];
    				$data = base64_decode($img_cont);
    				$im = imagecreatefromstring($data);
    				$filename1 = time() . '.'.$extnsn;

    				$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    				// code for upload image in folder
    				imagealphablending($im, false);
    				imagesavealpha($im, true);

    				if (in_array($extnsn, $valid_exts)) {
    					$quality = 0;
    					if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    						$quality = round((100 - $quality) * 0.09);

    						$resp = imagejpeg($im, $fullpath,$quality);
    					}else if($extnsn == 'png'){

    						$resp = imagepng($im, $fullpath);
    					}else if($extnsn == 'gif'){

    						$resp = imagegif($im, $fullpath);
    					}
    				}
    				$save['expandedImage'] = $filename1;
    			}else{
    				if($param->campaignId == ''){
    					$save['expandedImage'] = $param->expandedImage;
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['expandedImage'] = $dataCampaign->expandedImage;

    				}
    			}

    			if($param->expanded_img_url != ''){
    				$save['expanded_img_url'] = $param->expanded_img_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					if($save['expandedImage'] != ''){
    						$save['expanded_img_url'] = '';
    					}else{
    						$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    					}
    				}
    			}

    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
    		$save['push_title'] = $param->push_title;
    		$save['push_message'] = $param->push_message;
    		$save['summery_text'] = $param->summery_text;
                $save['persona_user_id'] = $param->campaignPersonaUser;
		$save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['email_subject'] = $param->email_subject;
    		$message = str_replace("&lbrace;","{",$param->email_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['email_message'] = $message;
    		$save['displayName'] = $param->displayName;
    		$save['fromAddress'] = $param->fromAddress;
    		$save['replyToAddress'] = $param->replyToAddress;
    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;
    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;

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

    		if(count($param->segments)){
    			foreach($param->segments as $segment){
    				$seg[] = $segment[0];
    			}

    			$segments = implode(",",$seg);
    		}else{
    			$segments = '';
    		}

    		if(count($param->filters)){
    			foreach($param->filters as $filter){
    				$fil[] = $filter[0];
    			}
    			$filters = implode(",",$fil);
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

    	}//End foreach

    if($param->campaignId == ''){
    		$this->campaign_model->savePushNotificationCampaign($save);

    		$login = $this->administrator_model->front_login_session();
    		$businessId = $login->businessId;

    		$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    		if($param->selectedPlatform == 'cross'){
    			if($additional_profit != 1){
	    			if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
	    				$update['crossChannel'] = $extraPackage->crossChannel - 1;
	    				$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
	    				$this->crosschannel_model->updateBrandUserExtraPackage($update);
	    			} else {
	    				//Update total campaigns
	    				$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
	    				$crossChannel = $userPackage->crossChannel;
	    				$updateCrossChannel = $crossChannel - 1;

	    				$update = array(
	    						'user_pro_id' => $userPackage->user_pro_id,
	    						'crossChannel' => $updateCrossChannel
	    				);
	    				$this->campaign_model->updateBrandUserTotalCampaigns($update);

	    			}
    			}

    		}
    	}else{
    		$save['id'] = $param->campaignId;
    		$save["modifiedDate"] = date('YmdHis');
    		$this->campaign_model->updatePushNotificationCampaign($save);
    	}
    	echo 1;
    }

    function deleteCampaignPopUp($campaignId){
    	$data['campaignId'] = $campaignId;
    	$campaign = $this->brand_model->getPushCampaignByCampaignId($campaignId);
    	$data['type'] = $campaign->platform;
    	$this->load->view('3.1/delete_crosscampaign',$data);
    }

    function deleteCampaign(){
    	$campaignId = $_POST['campaignId'];
    	$update['id'] = $_POST['campaignId'];
    	$update['isDelete'] = 1;
    	$this->campaign_model->updatePushNotificationCampaign($update);

    	$login = $this->administrator_model->front_login_session();
    	$businessId = $login->businessId;

    	$param = $this->campaign_model->getCampaign($campaignId);

    	$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	if($additional_profit != 1){
    	//if($param->platform == 'android'){
    	if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
    		$update1['crossChannel'] = $extraPackage->crossChannel + 1;
    		$update1['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
    		$this->crosschannel_model->updateBrandUserExtraPackage($update1);
    	} else {
    				//Update Cross Channel
    		$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
    		$crossChannel = $userPackage->crossChannel;
    		$updateCrossChannel = $crossChannel + 1;

    		$update = array(
    				'user_pro_id' => $userPackage->user_pro_id,
    				'crossChannel' => $updateCrossChannel
    		);
    		$this->campaign_model->updateBrandUserTotalCampaigns($update);

    	}
    	//}
    	}

    	echo 1;
    }

    function getCampaignTemplate(){

    	$id = $_POST['id'];
    	$template = $this->crosschannel_model->getCampaignTemplate($id);
    	echo $template->template;
    }
    
    function saveAutomation(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    			if($param->push_icon != ''){

    				$ime = $param->push_icon;

    				$image = explode(';base64,',  $ime);
    				$size = getimagesize($ime);
    				$type = $size['mime'];
    				$typea = explode('/', $type);
    				$extnsn = $typea[1];
    				$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    				$img_cont = str_replace(' ', '+', $image[1]);
    				$data = base64_decode($img_cont);
    				$im = imagecreatefromstring($data);
    				$filename = time() . '.'.$extnsn;

    				$fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

    				// code for upload image in folder
    				imagealphablending($im, false);
    				imagesavealpha($im, true);

    				if (in_array($extnsn, $valid_exts)) {
    					$quality = 0;
    					if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    						$quality = round((100 - $quality) * 0.09);

    						$resp = imagejpeg($im, $fullpath,$quality);
    					}else if($extnsn == 'png'){

    						$resp = imagepng($im, $fullpath);
    					}else if($extnsn == 'gif'){

    						$resp = imagegif($im, $fullpath);
    					}
    				}
    				$save['push_icon'] = $filename;
    			}else{

    				if($param->campaignId == ''){
    					$save['push_icon'] = $param->push_icon;
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['push_icon'] = $dataCampaign->push_icon;

    				}

    			}

    			if($param->push_img_url != ''){
    				$save['push_img_url'] = $param->push_img_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['push_img_url'] = '';
    				}else{
    					if($save['push_icon'] != ''){
    						$save['push_img_url'] = '';
    					}else{
    						$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    						$save['push_img_url'] = $dataCampaign->push_img_url;
    					}
    				}
    			}

    			if($param->expandedImage != ''){

    				$imexpanded = $param->expandedImage;

    				$imageExpanded = explode(';base64,',  $imexpanded);
    				$size = getimagesize($imexpanded);
    				$type = $size['mime'];
    				$typea = explode('/', $type);
    				$extnsn = $typea[1];
    				$valid_exts = array('jpeg', 'jpg', 'png', 'gif');

    				$img_cont = str_replace(' ', '+', $imageExpanded[1]);
    				//$img_cont=$image[1];
    				$data = base64_decode($img_cont);
    				$im = imagecreatefromstring($data);
    				$filename1 = time() . '.'.$extnsn;

    				$fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

    				// code for upload image in folder
    				imagealphablending($im, false);
    				imagesavealpha($im, true);

    				if (in_array($extnsn, $valid_exts)) {
    					$quality = 0;
    					if($extnsn == 'jpeg' || $extnsn == 'jpg'){
    						$quality = round((100 - $quality) * 0.09);

    						$resp = imagejpeg($im, $fullpath,$quality);
    					}else if($extnsn == 'png'){

    						$resp = imagepng($im, $fullpath);
    					}else if($extnsn == 'gif'){

    						$resp = imagegif($im, $fullpath);
    					}
    				}
    				$save['expandedImage'] = $filename1;
    			}else{

    				if($param->campaignId == ''){
    					$save['expandedImage'] = $param->expandedImage;
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					$save['expandedImage'] = $dataCampaign->expandedImage;

    				}
    			}

    			if($param->expanded_img_url != ''){
    				$save['expanded_img_url'] = $param->expanded_img_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['expanded_img_url'] = '';
    				}else{
    					$dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
    					if($save['expandedImage'] != ''){
    						$save['expanded_img_url'] = '';
    					}else{
    						$save['expanded_img_url'] = $dataCampaign->expanded_img_url;
    					}
    				}
    			}


    		$save['app_group_id'] = $param->groupId;
    		$save['platform'] = $param->selectedPlatform;
    		$save['campaignName'] = $param->campaignName;
    		$save['push_title'] = $param->push_title;
    		$save['push_message'] = $param->push_message;
    		//$save['email_subject'] = $param->email_subject;
    		//$save['email_message'] = $param->email_message;
    		//$save['displayName'] = $param->displayName;
    		//$save['fromAddress'] = $param->fromAddress;
    		//$save['replyToAddress'] = $param->replyToAddress;
    		$save['summery_text'] = $param->summery_text;
                $save['persona_user_id'] = $param->campaignPersonaUser;
		$save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save["custom_url"] = $param->custom_url;
    		$save["redirect_url"] = $param->redirect_url;
    		$save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;


    		if($param->campaignId == ''){
    			$save["isActive"] = 0;
    			$save["isDraft"] = 0;

    			//$save["type"] = $param->type;
    			$save["createdDate"] = date('YmdHis');
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->savePushNotificationCampaign($save);

    			$login = $this->administrator_model->front_login_session();
    			$businessId = $login->businessId;

    			$extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

    		if($param->selectedPlatform == 'cross'){
    			if($additional_profit != 1){
		    		if(count($extraPackage) > 0 && ($extraPackage->crossChannel > 0)) {
		    			$update['crossChannel'] = $extraPackage->crossChannel - 1;
		    			$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
		    			$this->crosschannel_model->updateBrandUserExtraPackage($update);
		    		} else {
		    			//Update total campaigns
		    			$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
		    			$crossChannel = $userPackage->crossChannel;
		    			$updateCrossChannel = $crossChannel - 1;

		    			$update = array(
		    					'user_pro_id' => $userPackage->user_pro_id,
		    					'crossChannel' => $updateCrossChannel
		    			);
		    			$this->campaign_model->updateBrandUserTotalCampaigns($update);

		    		}
    			}

    		}

    		}else{
    			$save['id'] = $param->campaignId;
    			$save["modifiedDate"] = date('YmdHis');
    			$this->campaign_model->updatePushNotificationCampaign($save);
    		}
    		echo 1;


    	}
    }


}
