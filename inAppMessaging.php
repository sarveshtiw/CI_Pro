<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class InAppMessaging extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('inapp_model','user_model', 'administrator_model', 'groupapp_model', 'country_model', 'permission_model', 'email_model', 'campaign_model', 'role_model', 'brand_model'));
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
    
    function inAppSupportedAttributes(){
        $this->load->view('3.1/inapp_supported_attributes');
    }

    function saveInApp(){

    	$login = $this->administrator_model->front_login_session();
  		$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
  		$additional_profit = $header['loggedInUser']->additional_profit;

  		$json = file_get_contents('php://input');
    	$params = json_decode($json);

    		foreach($params as $param){
    		//print_r($param); die;

    			if($param->image != ''){
    				$ime = $param->image;
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

    				$fullpath = 'upload/in_app/' . $filename;

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
    				$save['image'] = $filename;
    			}else{
    				if($param->campaignId == ''){
    					$save['image'] = $param->image;
    				}else{
                                    $groups = NULL;
    					$dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
    					$save['image'] = $dataCampaign->image;

    				}
    			}

    			if($param->image_url != ''){
    				$save['image_url'] = $param->image_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['image_url'] = '';
    				}else{
    					if($save['image'] != ''){
    						$save['image_url'] = '';
    					}else{
                                            $groups = NULL;
                                            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                                            $save['image_url'] = $dataCampaign->image_url;
    					}
    				}
    			}

    		$save['app_group_id'] = $param->groupId;
    		$save['campaignName'] = $param->campaignName;
                $save['persona_user_id'] = $param->campaignPersonaUser;
                $save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['message_type'] = $param->message_type;
    		$save['device_orientation'] = $param->device_orientation;
    		$save['device_type'] = $param->device_type;
    		$save['layout'] = $param->layout;
    		$save['header'] = $param->header;
    		$save['header_text_color'] = $param->header_text_color;
    		$save['header_text_opacity'] = $param->header_text_opacity;
    		$save['text_alignment'] = $param->text_alignment;
    		$save['closing_button_background_color'] = $param->closing_button_background_color;
    		$save["closing_button_background_color_opacity"] = $param->closing_button_background_color_opacity;
    		$save["body"] = $param->body;
    		$save["body_text_color"] = $param->body_text_color;
    		$save["body_text_opacity"] = $param->body_text_opacity;
    		$save["background_color"] = $param->background_color;
    		$save["background_color_opacity"] = $param->background_color_opacity;
    		$save["message_close"] = $param->message_close;
    		$save["button1_text"] = $param->button1_text;
    		$save["button2_text"] = $param->button2_text;
    		$save["button1_customUrl"] = $param->button1_customUrl;
    		$save["button1_redirectUrl"] = $param->button1_redirectUrl;
    		$save["button1_background_color"] = $param->button1_background_color;
    		$save["button1_background_color_opacity"] = $param->button1_background_color_opacity;
    		$save["button1_text_color"] = $param->button1_text_color;
    		$save["button1_text_color_opacity"] = $param->button1_text_color_opacity;
    		$save["button2_customUrl"] = $param->button2_customUrl;
    		$save["button2_redirectUrl"] = $param->button2_redirectUrl;
    		$save["button2_background_color"] = $param->button2_background_color;
    		$save["button2_background_color_opacity"] = $param->button2_background_color_opacity;
    		$save["button2_text_color"] = $param->button2_text_color;
    		$save["button2_text_color_opacity"] = $param->button2_text_color_opacity;
    		$save["frame_color"] = $param->frame_color;
    		$save["frame_color_opacity"] = $param->frame_color_opacity;
    		$save["on_click_behavior"] = $param->on_click_behavior;
    		$save["slide_up_position"] = $param->slide_up_position;
    		$save["chevron_color"] = $param->chevron_color;
    		$save["chevron_color_opacity"] = $param->chevron_color_opacity;

            if($param->custom_html != ''){
                    $milliSec = round(microtime(true)*1000);
                    $data = $param->custom_html;
                    $customHtmlFile = $milliSec.'.html';
                    $ret = file_put_contents('upload/customhtml/'.$customHtmlFile, $data, FILE_APPEND | LOCK_EX);
                    /*if($ret === false) {
                        die('There was an error writing this file');
                    }
                    else {
                        echo "$ret bytes written to file";
                    }*/
                }else{
                    $customHtmlFile = '';
                }

    		$save["custom_html"] = $customHtmlFile;
    		$save["image_type"] = $param->image_type;
        if($param->fontawesome_icon_img != '' && $param->fontawesome_icon_img !== 'data:,' && $param->fontawesome_icon_img !== '#'){
          $ime = $param->fontawesome_icon_img;
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

          $fullpath = 'upload/in_app/fontIcons/' . $filename;

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
          $save['fontawesome_icon_img'] = $filename;
        }else{
          if($param->campaignId == ''){
            $save['fontawesome_icon_img'] = $param->fontawesome_icon_img;
          }else{
              $groups = NULL;
            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
            $save['fontawesome_icon_img'] = $dataCampaign->fontawesome_icon_img;

          }
        }
    		$save["fontawesome_icon"] = $param->fontawesome_icon;

    		$save["fontawesome_background_color"] = $param->fontawesome_background_color;
    		$save["fontawesome_background_opacity"] = $param->fontawesome_background_opacity;
    		$save["fontawesome_icon_color"] = $param->fontawesome_icon_color;
    		$save["fontawesome_icon_color_opacity"] = $param->fontawesome_icon_color_opacity;

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
    		$save["isActive"] = 1;
    		$save["isDraft"] = 0;
    		$save["createdDate"] = date('YmdHis');
    		$save["modifiedDate"] = date('YmdHis');

    	}//End foreach

    	if($param->campaignId == ''){
    		$id = $this->inapp_model->saveInAppMessaging($save);

    		$login = $this->administrator_model->front_login_session();
    		$businessId = $login->businessId;

    		$extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


    			if($additional_profit != 1){
		    		if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
		    			$update['inAppMessaging'] = $extraPackage->inAppMessaging - 1;
		    			$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
		    			$this->inapp_model->updateBrandUserExtraPackage($update);
		    		} else {
		    			//Update total campaigns
		    			$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
		    			$inAppMessaging = $userPackage->inAppMessaging;
		    			$updateInAppMessaging = $inAppMessaging - 1;

		    			$update = array(
		    					'user_pro_id' => $userPackage->user_pro_id,
		    					'inAppMessaging' => $updateInAppMessaging
		    			);
		    			$this->campaign_model->updateBrandUserTotalCampaigns($update);

		    		}

    			}



    	}else{
    		$save["id"] = $param->campaignId;
    		$save["isDraft"] = 0;
    		$save["modifiedDate"] = date('YmdHis');
    		$this->inapp_model->updateInAppMessaging($save);
    		$id = $param->campaignId;
    	}
    	echo $id;
    }


    function saveComposeAsDraft(){

    	$login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    			if($param->image != ''){
    				$ime = $param->image;
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

    				$fullpath = 'upload/in_app/' . $filename;

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
    				$save['image'] = $filename;
    			}else{
    				if($param->campaignId == ''){
    					$save['image'] = $param->image;
    				}else{
                                    $groups = NULL;	
                                    $dataCampaign = $this->inapp_model->getCampaign($param->campaignId, $groups);
                                    $save['image'] = $dataCampaign->image;

    				}
    			}

    			if($param->image_url != ''){
    				$save['image_url'] = $param->image_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['image_url'] = '';
    				}else{
    					if($save['image'] != ''){
    						$save['image_url'] = '';
    					}else{
                                            $groups = NULL;
                                            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId, $groups);
                                            $save['image_url'] = $dataCampaign->image_url;
    					}
    				}
    			}




    		$save['app_group_id'] = $param->groupId;
    		$save['campaignName'] = $param->campaignName;
                $save['persona_user_id'] = $param->campaignPersonaUser;
                $save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['message_type'] = $param->message_type;
    		$save['device_orientation'] = $param->device_orientation;
    		$save['device_type'] = $param->device_type;
    		$save['layout'] = $param->layout;
    		$save['header'] = $param->header;
    		$save['header_text_color'] = $param->header_text_color;
    		$save['header_text_opacity'] = $param->header_text_opacity;
    		$save['text_alignment'] = $param->text_alignment;
    		$save['closing_button_background_color'] = $param->closing_button_background_color;
    		$save["closing_button_background_color_opacity"] = $param->closing_button_background_color_opacity;
    		$save["body"] = $param->body;
    		$save["body_text_color"] = $param->body_text_color;
    		$save["body_text_opacity"] = $param->body_text_opacity;
    		$save["background_color"] = $param->background_color;
    		$save["background_color_opacity"] = $param->background_color_opacity;
    		$save["message_close"] = $param->message_close;
    		$save["button1_text"] = $param->button1_text;
    		$save["button2_text"] = $param->button2_text;
    		$save["button1_customUrl"] = $param->button1_customUrl;
    		$save["button1_redirectUrl"] = $param->button1_redirectUrl;
    		$save["button1_background_color"] = $param->button1_background_color;
    		$save["button1_background_color_opacity"] = $param->button1_background_color_opacity;
    		$save["button1_text_color"] = $param->button1_text_color;
    		$save["button1_text_color_opacity"] = $param->button1_text_color_opacity;
    		$save["button2_customUrl"] = $param->button2_customUrl;
    		$save["button2_redirectUrl"] = $param->button2_redirectUrl;
    		$save["button2_background_color"] = $param->button2_background_color;
    		$save["button2_background_color_opacity"] = $param->button2_background_color_opacity;
    		$save["button2_text_color"] = $param->button2_text_color;
    		$save["button2_text_color_opacity"] = $param->button2_text_color_opacity;
    		$save["frame_color"] = $param->frame_color;
    		$save["frame_color_opacity"] = $param->frame_color_opacity;
    		$save["on_click_behavior"] = $param->on_click_behavior;
    		$save["slide_up_position"] = $param->slide_up_position;
    		$save["chevron_color"] = $param->chevron_color;
    		$save["chevron_color_opacity"] = $param->chevron_color_opacity;

            if($param->custom_html != ''){
                    $milliSec = round(microtime(true)*1000);
                    $data = $param->custom_html;
                    $customHtmlFile = $milliSec.'.html';
                    $ret = file_put_contents('upload/customhtml/'.$customHtmlFile, $data, FILE_APPEND | LOCK_EX);
                    /*if($ret === false) {
                        die('There was an error writing this file');
                    }
                    else {
                        echo "$ret bytes written to file";
                    }*/
                }else{
                    $customHtmlFile = '';
                }

    		$save["custom_html"] = $customHtmlFile;
    		$save["image_type"] = $param->image_type;
        if($param->fontawesome_icon_img != '' && $param->fontawesome_icon_img !== 'data:,'){
          $ime = $param->fontawesome_icon_img;
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

          $fullpath = 'upload/in_app/fontIcons/' . $filename;

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
          $save['fontawesome_icon_img'] = $filename;
        }else{
          if($param->campaignId == ''){
            $save['fontawesome_icon_img'] = $param->fontawesome_icon_img;
          }else{
              $groups = NULL;
            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
            $save['fontawesome_icon_img'] = $dataCampaign->fontawesome_icon_img;

          }
        }
    		$save["fontawesome_icon"] = $param->fontawesome_icon;
    		$save["fontawesome_background_color"] = $param->fontawesome_background_color;
    		$save["fontawesome_background_opacity"] = $param->fontawesome_background_opacity;
    		$save["fontawesome_icon_color"] = $param->fontawesome_icon_color;
    		$save["fontawesome_icon_color_opacity"] = $param->fontawesome_icon_color_opacity;


    		if($param->campaignId == ''){
    			$save["isActive"] = 0;
    			$save["isDraft"] = 1;

    			//$save["type"] = $param->type;
    			$save["createdDate"] = date('YmdHis');
    			$save["modifiedDate"] = date('YmdHis');
    			$this->inapp_model->saveInAppMessaging($save);

    			$login = $this->administrator_model->front_login_session();
    			$businessId = $login->businessId;

    			$extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


    			if($additional_profit != 1){
		    		if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
		    			$update['inAppMessaging'] = $extraPackage->inAppMessaging - 1;
		    			$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
		    			$this->inapp_model->updateBrandUserExtraPackage($update);
		    		} else {
		    			//Update total campaigns
		    			$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
		    			$inAppMessaging = $userPackage->inAppMessaging;
		    			$updateInAppMessaging = $inAppMessaging - 1;

		    			$update = array(
		    					'user_pro_id' => $userPackage->user_pro_id,
		    					'inAppMessaging' => $updateInAppMessaging
		    			);
		    			$this->campaign_model->updateBrandUserTotalCampaigns($update);

		    		}

    			}

    		}else{
    			$save['id'] = $param->campaignId;
    			$save["modifiedDate"] = date('YmdHis');
    			$this->inapp_model->updateInAppMessaging($save);
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
            //print_r($param); die;

                if($param->image != ''){
                    $ime = $param->image;
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

                    $fullpath = 'upload/in_app/' . $filename;

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
                    $save['image'] = $filename;
                }else{
                    if($param->campaignId == ''){
                        $save['image'] = $param->image;
                    }else{
                        $groups = NULL;
                        $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                        $save['image'] = $dataCampaign->image;

                    }
                }

                if($param->image_url != ''){
                    $save['image_url'] = $param->image_url;
                }else{

                    if($param->campaignId == ''){
                        $save['image_url'] = '';
                    }else{
                        if($save['image'] != ''){
                            $save['image_url'] = '';
                        }else{
                            $groups = NULL;
                            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                            $save['image_url'] = $dataCampaign->image_url;
                        }
                    }
                }


            $save['app_group_id'] = $param->groupId;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['list_id'] = $param->campaignList;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['message_type'] = $param->message_type;
            $save['device_orientation'] = $param->device_orientation;
            $save['device_type'] = $param->device_type;
            $save['layout'] = $param->layout;
            $save['header'] = $param->header;
            $save['header_text_color'] = $param->header_text_color;
            $save['header_text_opacity'] = $param->header_text_opacity;
            $save['text_alignment'] = $param->text_alignment;
            $save['closing_button_background_color'] = $param->closing_button_background_color;
            $save["closing_button_background_color_opacity"] = $param->closing_button_background_color_opacity;
            $save["body"] = $param->body;
            $save["body_text_color"] = $param->body_text_color;
            $save["body_text_opacity"] = $param->body_text_opacity;
            $save["background_color"] = $param->background_color;
            $save["background_color_opacity"] = $param->background_color_opacity;
            $save["message_close"] = $param->message_close;
            $save["button1_text"] = $param->button1_text;
            $save["button2_text"] = $param->button2_text;
            $save["button1_customUrl"] = $param->button1_customUrl;
            $save["button1_redirectUrl"] = $param->button1_redirectUrl;
            $save["button1_background_color"] = $param->button1_background_color;
            $save["button1_background_color_opacity"] = $param->button1_background_color_opacity;
            $save["button1_text_color"] = $param->button1_text_color;
            $save["button1_text_color_opacity"] = $param->button1_text_color_opacity;
            $save["button2_customUrl"] = $param->button2_customUrl;
            $save["button2_redirectUrl"] = $param->button2_redirectUrl;
            $save["button2_background_color"] = $param->button2_background_color;
            $save["button2_background_color_opacity"] = $param->button2_background_color_opacity;
            $save["button2_text_color"] = $param->button2_text_color;
            $save["button2_text_color_opacity"] = $param->button2_text_color_opacity;
            $save["frame_color"] = $param->frame_color;
            $save["frame_color_opacity"] = $param->frame_color_opacity;
            $save["on_click_behavior"] = $param->on_click_behavior;
            $save["slide_up_position"] = $param->slide_up_position;
            $save["chevron_color"] = $param->chevron_color;
            $save["chevron_color_opacity"] = $param->chevron_color_opacity;

            if($param->custom_html != ''){
                    $milliSec = round(microtime(true)*1000);
                    $data = $param->custom_html;
                    $customHtmlFile = $milliSec.'.html';
                    $ret = file_put_contents('upload/customhtml/'.$customHtmlFile, $data, FILE_APPEND | LOCK_EX);
                    /*if($ret === false) {
                        die('There was an error writing this file');
                    }
                    else {
                        echo "$ret bytes written to file";
                    }*/
                }else{
                    $customHtmlFile = '';
                }

            $save["custom_html"] = $customHtmlFile;
            $save["image_type"] = $param->image_type;
            if($param->fontawesome_icon_img != '' && $param->fontawesome_icon_img !== 'data:,'){
              $ime = $param->fontawesome_icon_img;
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

              $fullpath = 'upload/in_app/fontIcons/' . $filename;

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
              $save['fontawesome_icon_img'] = $filename;
            }else{
              if($param->campaignId == ''){
                $save['fontawesome_icon_img'] = $param->fontawesome_icon_img;
              }else{
                $groups = NULL;
                $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                $save['fontawesome_icon_img'] = $dataCampaign->fontawesome_icon_img;

              }
            }
            $save["fontawesome_icon"] = $param->fontawesome_icon;
            $save["fontawesome_background_color"] = $param->fontawesome_background_color;
            $save["fontawesome_background_opacity"] = $param->fontawesome_background_opacity;
            $save["fontawesome_icon_color"] = $param->fontawesome_icon_color;
            $save["fontawesome_icon_color_opacity"] = $param->fontawesome_icon_color_opacity;

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

        }//End foreach

        if($param->campaignId == ''){
            $id = $this->inapp_model->saveInAppMessaging($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


                if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
                        $update['inAppMessaging'] = $extraPackage->inAppMessaging - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->inapp_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $inAppMessaging = $userPackage->inAppMessaging;
                        $updateInAppMessaging = $inAppMessaging - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

                }



        }else{

                $save['id'] = $param->campaignId;
                $save["modifiedDate"] = date('YmdHis');
                $this->inapp_model->updateInAppMessaging($save);

        }

            echo 1;
}

function saveTargetAsDraft(){

    $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        $params = json_decode($json);

            foreach($params as $param){
            //print_r($param); die;

                if($param->image != ''){
                    $ime = $param->image;
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

                    $fullpath = 'upload/in_app/' . $filename;

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
                    $save['image'] = $filename;
                }else{
                    if($param->campaignId == ''){
                        $save['image'] = $param->image;
                    }else{
                        $groups = NULL;
                        $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                        $save['image'] = $dataCampaign->image;

                    }
                }

                if($param->image_url != ''){
                    $save['image_url'] = $param->image_url;
                }else{

                    if($param->campaignId == ''){
                        $save['image_url'] = '';
                    }else{
                        if($save['image'] != ''){
                            $save['image_url'] = '';
                        }else{
                            $groups = NULL;
                            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                            $save['image_url'] = $dataCampaign->image_url;
                        }
                    }
                }


            $save['app_group_id'] = $param->groupId;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['list_id'] = $param->campaignList;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['message_type'] = $param->message_type;
            $save['device_orientation'] = $param->device_orientation;
            $save['device_type'] = $param->device_type;
            $save['layout'] = $param->layout;
            $save['header'] = $param->header;
            $save['header_text_color'] = $param->header_text_color;
            $save['header_text_opacity'] = $param->header_text_opacity;
            $save['text_alignment'] = $param->text_alignment;
            $save['closing_button_background_color'] = $param->closing_button_background_color;
            $save["closing_button_background_color_opacity"] = $param->closing_button_background_color_opacity;
            $save["body"] = $param->body;
            $save["body_text_color"] = $param->body_text_color;
            $save["body_text_opacity"] = $param->body_text_opacity;
            $save["background_color"] = $param->background_color;
            $save["background_color_opacity"] = $param->background_color_opacity;
            $save["message_close"] = $param->message_close;
            $save["button1_text"] = $param->button1_text;
            $save["button2_text"] = $param->button2_text;
            $save["button1_customUrl"] = $param->button1_customUrl;
            $save["button1_redirectUrl"] = $param->button1_redirectUrl;
            $save["button1_background_color"] = $param->button1_background_color;
            $save["button1_background_color_opacity"] = $param->button1_background_color_opacity;
            $save["button1_text_color"] = $param->button1_text_color;
            $save["button1_text_color_opacity"] = $param->button1_text_color_opacity;
            $save["button2_customUrl"] = $param->button2_customUrl;
            $save["button2_redirectUrl"] = $param->button2_redirectUrl;
            $save["button2_background_color"] = $param->button2_background_color;
            $save["button2_background_color_opacity"] = $param->button2_background_color_opacity;
            $save["button2_text_color"] = $param->button2_text_color;
            $save["button2_text_color_opacity"] = $param->button2_text_color_opacity;
            $save["frame_color"] = $param->frame_color;
            $save["frame_color_opacity"] = $param->frame_color_opacity;
            $save["on_click_behavior"] = $param->on_click_behavior;
            $save["slide_up_position"] = $param->slide_up_position;
            $save["chevron_color"] = $param->chevron_color;
            $save["chevron_color_opacity"] = $param->chevron_color_opacity;

            if($param->custom_html != ''){
                    $milliSec = round(microtime(true)*1000);
                    $data = $param->custom_html;
                    $customHtmlFile = $milliSec.'.html';
                    $ret = file_put_contents('upload/customhtml/'.$customHtmlFile, $data, FILE_APPEND | LOCK_EX);
                    /*if($ret === false) {
                        die('There was an error writing this file');
                    }
                    else {
                        echo "$ret bytes written to file";
                    }*/
                }else{
                    $customHtmlFile = '';
                }

            $save["custom_html"] = $customHtmlFile;
            $save["image_type"] = $param->image_type;
            if($param->fontawesome_icon_img != '' && $param->fontawesome_icon_img !== 'data:,'){
              $ime = $param->fontawesome_icon_img;
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

              $fullpath = 'upload/in_app/fontIcons/' . $filename;

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
              $save['fontawesome_icon_img'] = $filename;
            }else{
              if($param->campaignId == ''){
                $save['fontawesome_icon_img'] = $param->fontawesome_icon_img;
              }else{
                $groups = NULL;
                $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                $save['fontawesome_icon_img'] = $dataCampaign->fontawesome_icon_img;

              }
            }
            $save["fontawesome_icon"] = $param->fontawesome_icon;
            $save["fontawesome_background_color"] = $param->fontawesome_background_color;
            $save["fontawesome_background_opacity"] = $param->fontawesome_background_opacity;
            $save["fontawesome_icon_color"] = $param->fontawesome_icon_color;
            $save["fontawesome_icon_color_opacity"] = $param->fontawesome_icon_color_opacity;

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

        }//End foreach

        if($param->campaignId == ''){
            $id = $this->inapp_model->saveInAppMessaging($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


                if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
                        $update['inAppMessaging'] = $extraPackage->inAppMessaging - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->inapp_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $inAppMessaging = $userPackage->inAppMessaging;
                        $updateInAppMessaging = $inAppMessaging - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

                }



        }else{

                $save['id'] = $param->campaignId;
                $save["modifiedDate"] = date('YmdHis');
                $this->inapp_model->updateInAppMessaging($save);

        }

            echo 1;
}

function saveInAppMessagingDraft(){

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        $params = json_decode($json);

            foreach($params as $param){
            //print_r($param); die;

                if($param->image != ''){
                    $ime = $param->image;
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

                    $fullpath = 'upload/in_app/' . $filename;

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
                    $save['image'] = $filename;
                }else{
                    if($param->campaignId == ''){
                        $save['image'] = $param->image;
                    }else{
                        $groups = NULL;
                        $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                        $save['image'] = $dataCampaign->image;

                    }
                }

                if($param->image_url != ''){
                    $save['image_url'] = $param->image_url;
                }else{

                    if($param->campaignId == ''){
                        $save['image_url'] = '';
                    }else{
                        if($save['image'] != ''){
                            $save['image_url'] = '';
                        }else{
                            $groups = NULL;
                            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                            $save['image_url'] = $dataCampaign->image_url;
                        }
                    }
                }


            $save['app_group_id'] = $param->groupId;
            $save['campaignName'] = $param->campaignName;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['list_id'] = $param->campaignList;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['message_type'] = $param->message_type;
            $save['device_orientation'] = $param->device_orientation;
            $save['device_type'] = $param->device_type;
            $save['layout'] = $param->layout;
            $save['header'] = $param->header;
            $save['header_text_color'] = $param->header_text_color;
            $save['header_text_opacity'] = $param->header_text_opacity;
            $save['text_alignment'] = $param->text_alignment;
            $save['closing_button_background_color'] = $param->closing_button_background_color;
            $save["closing_button_background_color_opacity"] = $param->closing_button_background_color_opacity;
            $save["body"] = $param->body;
            $save["body_text_color"] = $param->body_text_color;
            $save["body_text_opacity"] = $param->body_text_opacity;
            $save["background_color"] = $param->background_color;
            $save["background_color_opacity"] = $param->background_color_opacity;
            $save["message_close"] = $param->message_close;
            $save["button1_text"] = $param->button1_text;
            $save["button2_text"] = $param->button2_text;
            $save["button1_customUrl"] = $param->button1_customUrl;
            $save["button1_redirectUrl"] = $param->button1_redirectUrl;
            $save["button1_background_color"] = $param->button1_background_color;
            $save["button1_background_color_opacity"] = $param->button1_background_color_opacity;
            $save["button1_text_color"] = $param->button1_text_color;
            $save["button1_text_color_opacity"] = $param->button1_text_color_opacity;
            $save["button2_customUrl"] = $param->button2_customUrl;
            $save["button2_redirectUrl"] = $param->button2_redirectUrl;
            $save["button2_background_color"] = $param->button2_background_color;
            $save["button2_background_color_opacity"] = $param->button2_background_color_opacity;
            $save["button2_text_color"] = $param->button2_text_color;
            $save["button2_text_color_opacity"] = $param->button2_text_color_opacity;
            $save["frame_color"] = $param->frame_color;
            $save["frame_color_opacity"] = $param->frame_color_opacity;
            $save["on_click_behavior"] = $param->on_click_behavior;
            $save["slide_up_position"] = $param->slide_up_position;
            $save["chevron_color"] = $param->chevron_color;
            $save["chevron_color_opacity"] = $param->chevron_color_opacity;

            if($param->custom_html != ''){
                    $milliSec = round(microtime(true)*1000);
                    $data = $param->custom_html;
                    $customHtmlFile = $milliSec.'.html';
                    $ret = file_put_contents('upload/customhtml/'.$customHtmlFile, $data, FILE_APPEND | LOCK_EX);
                    /*if($ret === false) {
                        die('There was an error writing this file');
                    }
                    else {
                        echo "$ret bytes written to file";
                    }*/
            }else{
                    $customHtmlFile = '';
            }

            $save["custom_html"] = $customHtmlFile;
            $save["image_type"] = $param->image_type;
            if($param->fontawesome_icon_img != '' && $param->fontawesome_icon_img !== 'data:,'){
              $ime = $param->fontawesome_icon_img;
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

              $fullpath = 'upload/in_app/fontIcons/' . $filename;

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
              $save['fontawesome_icon_img'] = $filename;
            }else{
              if($param->campaignId == ''){
                $save['fontawesome_icon_img'] = $param->fontawesome_icon_img;
              }else{
                $groups = NULL;
                $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
                $save['fontawesome_icon_img'] = $dataCampaign->fontawesome_icon_img;

              }
            }
            $save["fontawesome_icon"] = $param->fontawesome_icon;
            $save["fontawesome_background_color"] = $param->fontawesome_background_color;
            $save["fontawesome_background_opacity"] = $param->fontawesome_background_opacity;
            $save["fontawesome_icon_color"] = $param->fontawesome_icon_color;
            $save["fontawesome_icon_color_opacity"] = $param->fontawesome_icon_color_opacity;

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
            $id = $this->inapp_model->saveInAppMessaging($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


                if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
                        $update['inAppMessaging'] = $extraPackage->inAppMessaging - 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->inapp_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $inAppMessaging = $userPackage->inAppMessaging;
                        $updateInAppMessaging = $inAppMessaging - 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

                }



        }else{
            $save["id"] = $param->campaignId;
            $save["isDraft"] = 1;
            $save["modifiedDate"] = date('YmdHis');
            $this->inapp_model->updateInAppMessaging($save);
            $id = $param->campaignId;
        }
        echo 1;
    }

    function inAppListPagination() {


        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];

            $newStatusCount = $_POST["newStatusCount"];

            $this->session->set_userdata("inAppMessagingPagination",$newStatusCount);

            $max_status_id = @$_POST['status_id'];

            $businessId = $_POST['businessId'];

            $start = $statuscount;
            if($header['login']->usertype == 8){
                $data['push_campaigns'] = $this->inapp_model->getInAppMessagingByBusinessId($businessId, $start, $noofStatus);
            }
            if($header['login']->usertype == 9){

                $groups = $this->campaign_model->getUserGroups($header['login']->user_id);

                foreach($groups as $group){

                    $AppUserCampaigns[] = $group->app_group_id;

                }

                $data['push_campaigns'] = $this->inapp_model->getInAppMessaging($AppUserCampaigns, $start, $noofStatus);
            }

            if (count($data['push_campaigns']) > 0) {
                $this->load->view('3.1/addmore_inappmessaging', $data);
            }
        } else {

            redirect(base_url());
        }
    }

    function deleteInAppPopUp($id){
        $data['id'] = $id;
        $this->load->view('3.1/delete_inapp',$data);
    }

    function deleteInApp(){

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


                if($additional_profit != 1){
                    if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
                        $update['inAppMessaging'] = $extraPackage->inAppMessaging + 1;
                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->inapp_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $inAppMessaging = $userPackage->inAppMessaging;
                        $updateInAppMessaging = $inAppMessaging + 1;

                        $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'inAppMessaging' => $updateInAppMessaging
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);

                    }

                }

        $updateInApp['id'] = $_POST['id'];
        $updateInApp['isDelete'] = 1;
        $this->inapp_model->updateInAppMessaging($updateInApp);
        echo 1;
    }

    public function performance($inAppMessagingId = false) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'inAppMessagingList';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $groups = $this->groupapp_model->getUserGroups($login->user_id);
                if (count($groups) > 0) {
                    foreach ($groups as $group) {

                        $groupArray[] = $group->app_group_id;
                    }
                } else {
                    $groupArray = '';
                }

                $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
            }

            if (count($header['groups']) > 0) {
                $groupId = $header['groups'][0]->app_group_id;
            } else {
                $groupId = '';
            }

            if (empty($inAppMessagingId)) {
                redirect(base_url().'appUser');
            }
            $groups = NULL;
            $inAppRow = $this->inapp_model->getCampaign($inAppMessagingId,$groups);
            if (count($inAppRow) == 0) {
                redirect(base_url().'appUser');
            }
            $app_group_row = $this->brand_model->getAppGroupRow($inAppRow->app_group_id);
            if($app_group_row->businessId != $login->businessId){
                redirect(base_url().'appUser');
            }
            $data['campaignName'] = $inAppRow->campaignName;

            $data['group'] = $this->groupapp_model->getOneGroup($groupId);
            $data['groupId'] = $groupId;
            $data['inAppMessagingId'] = $inAppMessagingId;
            $data['platform'] = $inAppRow->device_type;
            $data['user'] = $header['loggedInUser'];

            $inAppMessages = $this->inapp_model->getAllInAppMessaging($login->businessId, $limit = 10);
            $countSendInApp = $this->inapp_model->countInAppSendByInAppId($inAppMessagingId);
            if (count($countSendInApp) > 0) {
                $countSendInApp = count($countSendInApp);
            } else {
                $countSendInApp = 0;
            }
            $sendInApps = $this->inapp_model->getInAppSendHistoryByInAppId($inAppMessagingId);
            $send_time_arr = array();
            $send_users_arr = array();
            if (count($sendInApps) > 0) {
                foreach ($sendInApps as $user) {
                    $sendTime = date('Y-m-d', strtotime($user->notification_timezone_send_time));
                    if (!in_array($sendTime, $send_time_arr)) {
                        $y = date('Y', strtotime($sendTime));
                        $m = date('m', strtotime($sendTime));
                        $d = date('d', strtotime($sendTime));
                        $m = $m - 1;
                        array_push($send_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                    }
                }
               $send_users_arr = implode(',', $send_users_arr);
            }

            $countViewInApp = $this->inapp_model->countInAppViewByInAppId($inAppMessagingId);
            if (count($countViewInApp) > 0) {
                $countViewInApp = count($countViewInApp);
            } else {
                $countViewInApp = 0;
            }

            $viewInApps = $this->inapp_model->getInAppViewHistoryByInAppId($inAppMessagingId);
            $view_time_arr = array();
            $view_users_arr = array();
            if (count($viewInApps) > 0) {
                foreach ($viewInApps as $user) {
                    $viewTime = date('Y-m-d', strtotime($user->notification_timezone_view_time));
                    if (!in_array($viewTime, $view_time_arr)) {
                        $y = date('Y', strtotime($viewTime));
                        $m = date('m', strtotime($viewTime));
                        $m = $m - 1;
                        $d = date('d', strtotime($viewTime));

                        array_push($view_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                    }
                }
                $view_users_arr = implode(',', $view_users_arr);
            }

            if (empty($view_users_arr)) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $currentMonth = $currentMonth - 1;
                $currentDate = date('d');
                $view_users_arr = "[Date.UTC($currentYear,$currentMonth,$currentDate),0]";
            }

            if (empty($send_users_arr)) {
                $currentYear = date('Y');
                $currentMonth = date('m');
                $currentMonth = $currentMonth - 1;
                $currentDate = date('d');
                $send_users_arr = "[Date.UTC($currentYear,$currentMonth,$currentDate),0]";
            }

            $data['inAppMessages'] = $inAppMessages;
            $data['sendInAppUsers'] = $send_users_arr;
            $data['viewInAppUsers'] = $view_users_arr;
            $data['countSendInApp'] = $countSendInApp;
            $data['countViewInApp'] = $countViewInApp;
            $data['highChartScript'] = TRUE;
            $data['highChartScriptAjax'] = TRUE;
            $data['currentInApp'] = $inAppMessagingId;
            // echo '<pre>'; print_r($data); exit;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/inAppMessagingPerformance', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function lists($appGroupId = false) {
      $login = $this->administrator_model->front_login_session();
      if ($login->active != 0) {
         $header['page'] = 'campaignsList';
         $header['userid'] = $login->user_id;
         $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
         $country_id = '2';
         $timezone  = '';
        if(!empty($header['loggedInUser']->country)){
           $country_id = $header['loggedInUser']->country;
           $row = $this->country_model->getTimezonebyCountryId($country_id);
           $timezone = $row->timezone;
           //echo $row->timezone; exit;
         }
         $data['userTimezone'] = $timezone;
         $header['usertype'] = $login->usertype;
         $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

         $cookies = $this->input->cookie ('group',true);
         if(!empty($cookies))
         {
           $cookie = $this->input->cookie('group',true);
           $cookie_group = explode(",", $cookie);

         }else{
          $cookie_group = '';
         }

         $header['cookie_group'] = $cookie_group;

        //App Groups list in menu
        if($login->usertype == 8){
          $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
        }else{
          $groups = $this->groupapp_model->getUserGroups($login->user_id);
          if(count($groups) > 0){
            foreach($groups as $group){

              $groupArray[] = $group->app_group_id;

            }
          }else{
            $groupArray = '';
          }

          $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
        }

         //$groups = $this->groupapp_model->getUserGroupData($groupArray);
         if(count($header['groups']) > 0){
           $groupId = $header['groups'][0]->app_group_id;
         }else{
           $groupId = '';
         }

         $data['group'] = $this->groupapp_model->getOneGroup($groupId);
         $data['groupId'] = $groupId;

         $data['user'] = $header['loggedInUser'];

         $businessId = $login->businessId;
         $businessId = $login->businessId;
         if (empty($appGroupId)) {
              $inAppMessaging = $this->inapp_model->getInAppMessagingByBusinessId($businessId);
         } else {
              $inAppMessaging = $this->inapp_model->getAllInAppMessagingByAppGroupId($businessId, $appGroupId);
         }

         $data['inAppMessaging'] = $inAppMessaging;

         $this->load->view('3.1/inner_headerBrandUser', $header);
         $this->load->view('3.1/inAppMessagingList',$data);
         $this->load->view('3.1/inner_footerBrandUser');
       }else{
         redirect(base_url());
       }
    //echo "<h1>Coming Soon..</h1>"; exit();
    }

    public function pdf($inAppId){
        $result = $this->InAppData($inAppId);

        $this->load->library('wkhtmltopdf');
        $array['path'] = '/tmp';

        $wkhtmltopdf = new Wkhtmltopdf;
        $wkhtmltopdf->make($array);
        $wkhtmltopdf->setTitle("New Title");
        $wkhtmltopdf->setHtml('<!DOCTYPE html>
          <html lang="en" style="background: #fff; box-sizing: border-box; color: #424141; font-family: "proxima-nova",sans-serif; font-size: 10px; margin: 0; padding: 0;">
           <head style="box-sizing: border-box;">
            <meta charset="utf-8" style="box-sizing: border-box;" />
            <meta content="True" name="HandheldFriendly" style="box-sizing: border-box;" />
            <meta content="300" name="MobileOptimized" style="box-sizing: border-box;" />
            <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" style="box-sizing: border-box;" />
            <meta content="telephone=no" name="format-detection" style="box-sizing: border-box;" />
            <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible" style="box-sizing: border-box;" />

            <title style="box-sizing: border-box;">Hurree</title>

           </head>
           <body style="background: #fff; background-color: #fff; box-sizing: border-box; color: #424141; font-family: "proxima-nova",sans-serif; font-size: 14px; line-height: 1.42857143; margin: 0; overflow-y: auto; padding: 0;">
            <div class="pageStarts" style="box-sizing: border-box; padding: 70px 0 0px;">
             <div class="container-fluid" style="box-sizing: border-box; margin-left: auto; margin-right: auto; min-width: inherit; padding-left: 15px; padding-right: 15px;">
              <div class="col-xs-12" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 100%;">
               <div class="pageContent" style="box-sizing: border-box;">
                <div class="stats" style="border-radius: 4px; box-shadow: 0 0 3px rgba(0,0,0,.3); box-sizing: border-box; float: left; width: 100%;">
                 <div class="col-xs-12" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 100%;">
                  <div class="row  performance profiling" style="box-sizing: border-box; margin-left: -15px; margin-right: -15px;">
                   <h3 style="background: none; border-bottom: 1px solid #ddd;  box-sizing: border-box; color: #424141; font-family: "proxima-nova",sans-serif; font-size: 14px; font-weight: 700; line-height: 1.1; margin: 0; margin-bottom: 10px; margin-top: 20px; padding: 15px;"> In-App messaging Performance</h3>
                   <div class="col-xs-12" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 100%;">
                    <div class="row performance" style="box-sizing: border-box; margin-left: -15px; margin-right: -15px;">
                     <div class="col-xs-6" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 50%;">
                      <div class="innerPerformace" style="border-radius: 4px; box-sizing: border-box; float: left; margin: 10px 0; padding: 12px; text-align: left; width: 100%;"><strong style="box-sizing: border-box; font-weight: bold;">Campaign Name :</strong> ' . $result['campaignName'] . '</div>
                     </div>
                     <div class="col-xs-6" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 50%;">
                      <div class="innerPerformace" style="border-radius: 4px; box-sizing: border-box; float: left; margin: 10px 0; padding: 12px; text-align: left; width: 100%;"><strong style="box-sizing: border-box; font-weight: bold;">Push Title :</strong> ' . $result['push_title'] . '</div>
                     </div>
                    </div>
                   </div>
                   <div class="col-xs-12" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 100%;">
                    <div class="row" style="box-sizing: border-box; margin-left: -15px; margin-right: -15px;">
                     <div class="col-xs-6" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 50%;">
                      <div class="inner purpleBg" style="background: #686767; border-radius: 4px; box-sizing: border-box; float: left; margin: 20px 0; padding: 15px; text-align: center; width: 100%;">
                       <p style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; margin: 0; padding: 0;"><strong style="box-sizing: border-box; font-weight: bold;">' . $result['countViewInApp'] . '</strong></p>
                       <h4 style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; font-size: 14px; font-weight: 300 !important; line-height: 1.1; margin: 0; margin-bottom: 10px; margin-top: 10px; padding: 0; text-transform: uppercase;">Views</h4>
                      </div>
                     </div>
                     <div class="col-xs-6" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 50%;">
                      <div class="inner purpleBg" style="background: #686767; border-radius: 4px; box-sizing: border-box; float: left; margin: 20px 0; padding: 15px; text-align: center; width: 100%;">
                       <p style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; margin: 0; padding: 0;"><strong style="box-sizing: border-box; font-weight: bold;">' . $result['countSendInApp'] . '</strong></p>
                       <h4 style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; font-size: 14px; font-weight: 300 !important; line-height: 1.1; margin: 0; margin-bottom: 10px; margin-top: 10px; padding: 0; text-transform: uppercase;">Sent</h4>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div>
                  <div id="insert-after" style="box-sizing: border-box;">
                  </div>

                 </div>
                </div>
               </div>
              </div>
             </div>
            </div>
           </body>
          </html>
          ');
        // $html can be a url or html content.
         $wkhtmltopdf->output(Wkhtmltopdf::MODE_DOWNLOAD, "inAppMessagingPerformance.pdf");
    }

    public function InAppData($inAppId) {
        $groups = NULL;
        $inAppRow = $this->inapp_model->getCampaign($inAppId,$groups);
        $countSendInApp = $this->inapp_model->countInAppSendByInAppId($inAppId); ///print_r($countSendCampaigns); exit;
        if (count($countSendInApp) > 0) {
            $countSendInApp = count($countSendInApp);
        } else {
            $countSendInApp = 0;
        }

        $sendInApp = $this->inapp_model->getInAppSendHistoryByInAppId($inAppId); //print_r($ssendCampaigns); exit;
        $send_time_arr = array();
        $send_users_arr = array();
        if (count($sendInApp) > 0) {
            foreach ($sendInApp as $user) {
                $sendTime = date('Y-m-d', strtotime($user->notification_timezone_send_time)); //echo  $createdDate;
                if (!in_array($sendTime, $send_time_arr)) {
                    $y = date('Y', strtotime($sendTime));
                    $m = date('m', strtotime($sendTime));
                    $d = date('d', strtotime($sendTime));
                    $m = $m - 1;
                    $date = new DateTime(NULL, new DateTimeZone('UTC'));
                    $date->setDate($y,$m,$d);
                    $ts = $date->getTimestamp();
                    $send_users_arr[] = [$ts,$user->totalrecord];
                }
            }
        }

        $countViewInApp = $this->inapp_model->countInAppViewByInAppId($inAppId);
        if (count($countViewInApp) > 0) {
            $countViewInApp = count($countViewInApp);
        } else {
            $countViewInApp = 0;
        }

        $viewInApp = $this->inapp_model->getInAppViewHistoryByInAppId($inAppId);
        $view_time_arr = array();
        $view_users_arr = array();
        if (count($viewInApp) > 0) {
            foreach ($viewInApp as $user) {
                $viewTime = date('Y-m-d', strtotime($user->notification_timezone_view_time));
                if (!in_array($viewTime, $view_time_arr)) {
                    $y = date('Y', strtotime($viewTime));
                    $m = date('m', strtotime($viewTime));
                    $m = $m - 1;
                    $d = date('d', strtotime($viewTime));
                    $date = new DateTime(NULL, new DateTimeZone('UTC'));
                    $date->setDate($y,$m,$d);
                    $ts = $date->getTimestamp();
                    $view_users_arr[] = [$ts,$user->totalrecord];
                }
            }
        }

        if (empty($view_users_arr)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $currentMonth = $currentMonth - 1;
            $currentDate = date('d');
            $date = new DateTime(NULL, new DateTimeZone('UTC'));
            $date->setDate($currentYear,$currentMonth,$currentDate);
            $ts = $date->getTimestamp();
            $view_users_arr[] = [$ts,0];
        }

        if (empty($send_users_arr)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $currentMonth = $currentMonth - 1;
            $currentDate = date('d');
            $date = new DateTime(NULL, new DateTimeZone('UTC'));
            $date->setDate($currentYear,$currentMonth,$currentDate);
            $ts = $date->getTimestamp();
            $send_users_arr[] = [$ts,0];
        }

        $result = array();
        $result['view_users_arr']= $view_users_arr;
        $result['send_users_arr']= $send_users_arr;
        $result['countSendInApp']= $countSendInApp;
        $result['countViewInApp']= $countViewInApp;
        $result['campaignName']= $inAppRow->campaignName;
        //$result['platform']= $pushCampaignRow->platform;
        $result['push_title']= '';//$pushCampaignRow->push_title;

        return $result;
    }

    public function cloneInApp($inAppId = false) {
        $login = $this->administrator_model->front_login_session();
        if (isset($_POST['inAppId'])) {
            $inAppId = $_POST['inAppId'];
            $inAppCloneRow = $this->inapp_model->getCloneInAppRowId($inAppId);
            if ($inAppCloneRow > 0) {
                $groups = NULL;
                $lastRow = $this->inapp_model->getCampaign($inAppCloneRow,$groups);
                if (count($lastRow) > 0) {
                    $update = array('id' => $lastRow->id, 'isDraft' => 1, 'isActive' => 0, 'createdDate' => date('Y-m-d H:i:s'));
                    $this->inapp_model->updateInAppMessaging($update);
                    //echo $lastRow->id; //exit;
                    $this->session->set_flashdata('inAppCloneSuccess', 'In-App clone added successfully!');

    								$success = 'success';
    								$statusMessage = 'In-App clone added successfully!';

    								$response = array(
    										"data" => array(
    												"status" => $success,
    												"statusMessage" => $statusMessage,
    												"inAppId" => "$lastRow->id"
    										)
    								);
                } else {
                  $success = 'error';
                  $statusMessage = "Error occoured. Please try again.";

                  $response = array(
                      "data" => array(
                          "status" => $success,
                          "statusMessage" => $statusMessage
                      )
                  );

                }
            } else {
              $success = 'error';
              $statusMessage = "Error occoured. Please try again.";

              $response = array(
                  "data" => array(
                      "status" => $success,
                      "statusMessage" => $statusMessage
                  )
              );
            }
          echo json_encode($response); exit();
        } else {
            $data['inAppId'] = $inAppId;
            $businessId = $login->businessId;
            //Check User have default Campaign package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
            if (count($userPackage) > 0) {
                $data['countTotalInApp'] = $userPackage->inAppMessaging;
            } else {
                $data['countTotalInApp'] = 0;
            }

            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);
            if (count($extraPackage) > 0) {
                $data['extraInAppQuantity'] = $extraPackage->quantity;
            } else {
                $data['extraInAppQuantity'] = 0;
            }
            $this->load->view('3.1/clone_inapp_messaging', $data);
        }
    }

    public function delete($inAppId=false){
       if(isset($_POST['inAppId'])){
           $inAppId = $_POST['inAppId'];
           $groups = NULL;
           $inAppRow = $this->inapp_model->getCampaign($inAppId,$groups);
           if(count($inAppRow) > 0){
               $update = array('id' => $inAppRow->id, 'isDelete' => 1,'modifiedDate' => date('Y-m-d H:i:s'));
               $this->inapp_model->updateInAppMessaging($update);
               //echo $lastRow->id; //exit;

               $success = 'success';
               $statusMessage = 'In-App Messaging deleted successfully!';

               $response = array(
                   "data" => array(
                       "status" => $success,
                       "statusMessage" => $statusMessage
                   )
               );
            }else{
               $success = 'error';
               $statusMessage = "Error occoured. Please try again.";

               $response = array(
                   "data" => array(
                       "status" => $success,
                       "statusMessage" => $statusMessage
                   )
               );
           }
          echo json_encode($response); exit();
        }else{
           $data['inAppId'] = $inAppId;
           $this->load->view('3.1/delete_in-app_messaging',$data);
        }
    }

    public function comparePopup($inAppId) {
       $data = array();
       $login = $this->administrator_model->front_login_session();
       $inAppMessaging = $this->inapp_model->getAllInAppMessaging($login->businessId, NULL);
       $data['inAppMessaging'] = $inAppMessaging;
       $data['currentInAppId'] = $inAppId;

       $this->load->view('3.1/in_app_comparePopup', $data);
    }

    public function compareInApp() {
       $oldInApp = $_POST['currentInApp'];
       $inAppId = $_POST['selectedId'];
       $prevViewInApp = $_POST['prevViewInApp'];
       $prevSentInApp = $_POST['prevSentInApp'];
       $login = $this->administrator_model->front_login_session();
       $result = $this->InAppData($inAppId);

       $resultOldInApp = $this->InAppData($oldInApp);

       $send_users_arr_new = $result['send_users_arr'];
       $view_users_arr_new = $result['view_users_arr'];

       $send_users_arr_old = $resultOldInApp['send_users_arr'];
       $view_users_arr_old = $resultOldInApp['view_users_arr'];

       $newInAppData = array();

       $newInAppnData['countSendInApp'] = $result['countSendInApp'];
       $newInAppData['countViewInApp'] = $result['countViewInApp'];

       $newInAppData['campaignName'] = $result['campaignName'];
       $newInAppData['push_title'] = '';//$result['push_title'];

       $oldInAppData = array();

       $oldInAppData['countSendInApp'] = $resultOldInApp['countSendInApp'];
       $oldInAppData['countViewInApp'] = $resultOldInApp['countViewInApp'];

       $oldInAppData['campaignName'] = $resultOldInApp['campaignName'];
       $oldInAppData['push_title'] = '';//$resultOldCampaign['push_title'];

       $rows1 = array();
       $rows1['name'] = $result['campaignName'] . '- Sent count';
       $rows1['data'] = $send_users_arr_new;

       $rows2 = array();
       $rows2['name'] = $result['campaignName'] . '- View count';
       $rows2['data'] = $view_users_arr_new;


       $rows3 = array();
       $rows3['name'] = $resultOldInApp['campaignName'] . '- View count';
       $rows2['data'] = $view_users_arr_old;

       $rows4 = array();
       $rows4['name'] = $resultOldInApp['campaignName'] . '- Sent count';
       $rows4['data'] = $send_users_arr_old;

       $result = array();
       array_push($result, $rows1);
       array_push($result, $rows2);
       array_push($result, $rows3);
       array_push($result, $rows4);
       $data['graph'] = $result;
       $data ['newInAppData'] = $newInAppData;
       $data ["oldInAppData"] = $oldInAppData;
       print json_encode($data, JSON_NUMERIC_CHECK);  exit();
   }

   function saveAutomation(){
       $login = $this->administrator_model->front_login_session();
    	$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
    	$additional_profit = $header['loggedInUser']->additional_profit;

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
    	foreach($params as $param){

    			if($param->image != ''){
    				$ime = $param->image;
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

    				$fullpath = 'upload/in_app/' . $filename;

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
    				$save['image'] = $filename;
    			}else{
    				if($param->campaignId == ''){
    					$save['image'] = $param->image;
    				}else{
                                    $groups = NULL;	
                                    $dataCampaign = $this->inapp_model->getCampaign($param->campaignId, $groups);
                                    $save['image'] = $dataCampaign->image;

    				}
    			}

    			if($param->image_url != ''){
    				$save['image_url'] = $param->image_url;
    			}else{

    				if($param->campaignId == ''){
    					$save['image_url'] = '';
    				}else{
    					if($save['image'] != ''){
    						$save['image_url'] = '';
    					}else{
                                            $groups = NULL;
                                            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId, $groups);
                                            $save['image_url'] = $dataCampaign->image_url;
    					}
    				}
    			}




    		$save['app_group_id'] = $param->groupId;
    		$save['campaignName'] = $param->campaignName;
                $save['persona_user_id'] = $param->campaignPersonaUser;
                $save['list_id'] = $param->campaignList;
                $save['message_category'] = $param->message_category;
                $save['automation'] = $param->automation;
    		$save['message_type'] = $param->message_type;
    		$save['device_orientation'] = $param->device_orientation;
    		$save['device_type'] = $param->device_type;
    		$save['layout'] = $param->layout;
    		$save['header'] = $param->header;
    		$save['header_text_color'] = $param->header_text_color;
    		$save['header_text_opacity'] = $param->header_text_opacity;
    		$save['text_alignment'] = $param->text_alignment;
    		$save['closing_button_background_color'] = $param->closing_button_background_color;
    		$save["closing_button_background_color_opacity"] = $param->closing_button_background_color_opacity;
    		$save["body"] = $param->body;
    		$save["body_text_color"] = $param->body_text_color;
    		$save["body_text_opacity"] = $param->body_text_opacity;
    		$save["background_color"] = $param->background_color;
    		$save["background_color_opacity"] = $param->background_color_opacity;
    		$save["message_close"] = $param->message_close;
    		$save["button1_text"] = $param->button1_text;
    		$save["button2_text"] = $param->button2_text;
    		$save["button1_customUrl"] = $param->button1_customUrl;
    		$save["button1_redirectUrl"] = $param->button1_redirectUrl;
    		$save["button1_background_color"] = $param->button1_background_color;
    		$save["button1_background_color_opacity"] = $param->button1_background_color_opacity;
    		$save["button1_text_color"] = $param->button1_text_color;
    		$save["button1_text_color_opacity"] = $param->button1_text_color_opacity;
    		$save["button2_customUrl"] = $param->button2_customUrl;
    		$save["button2_redirectUrl"] = $param->button2_redirectUrl;
    		$save["button2_background_color"] = $param->button2_background_color;
    		$save["button2_background_color_opacity"] = $param->button2_background_color_opacity;
    		$save["button2_text_color"] = $param->button2_text_color;
    		$save["button2_text_color_opacity"] = $param->button2_text_color_opacity;
    		$save["frame_color"] = $param->frame_color;
    		$save["frame_color_opacity"] = $param->frame_color_opacity;
    		$save["on_click_behavior"] = $param->on_click_behavior;
    		$save["slide_up_position"] = $param->slide_up_position;
    		$save["chevron_color"] = $param->chevron_color;
    		$save["chevron_color_opacity"] = $param->chevron_color_opacity;

            if($param->custom_html != ''){
                    $milliSec = round(microtime(true)*1000);
                    $data = $param->custom_html;
                    $customHtmlFile = $milliSec.'.html';
                    $ret = file_put_contents('upload/customhtml/'.$customHtmlFile, $data, FILE_APPEND | LOCK_EX);
                    /*if($ret === false) {
                        die('There was an error writing this file');
                    }
                    else {
                        echo "$ret bytes written to file";
                    }*/
                }else{
                    $customHtmlFile = '';
                }

    		$save["custom_html"] = $customHtmlFile;
    		$save["image_type"] = $param->image_type;
        if($param->fontawesome_icon_img != '' && $param->fontawesome_icon_img !== 'data:,'){
          $ime = $param->fontawesome_icon_img;
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

          $fullpath = 'upload/in_app/fontIcons/' . $filename;

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
          $save['fontawesome_icon_img'] = $filename;
        }else{
          if($param->campaignId == ''){
            $save['fontawesome_icon_img'] = $param->fontawesome_icon_img;
          }else{
              $groups = NULL;
            $dataCampaign = $this->inapp_model->getCampaign($param->campaignId,$groups);
            $save['fontawesome_icon_img'] = $dataCampaign->fontawesome_icon_img;

          }
        }
    		$save["fontawesome_icon"] = $param->fontawesome_icon;
    		$save["fontawesome_background_color"] = $param->fontawesome_background_color;
    		$save["fontawesome_background_opacity"] = $param->fontawesome_background_opacity;
    		$save["fontawesome_icon_color"] = $param->fontawesome_icon_color;
    		$save["fontawesome_icon_color_opacity"] = $param->fontawesome_icon_color_opacity;


    		if($param->campaignId == ''){
    			$save["isActive"] = 0;
    			$save["isDraft"] = 0;

    			//$save["type"] = $param->type;
    			$save["createdDate"] = date('YmdHis');
    			$save["modifiedDate"] = date('YmdHis');
    			$this->inapp_model->saveInAppMessaging($save);

    			$login = $this->administrator_model->front_login_session();
    			$businessId = $login->businessId;

    			$extraPackage = $this->inapp_model->getBrandUserExtraPackage($businessId);


    			if($additional_profit != 1){
		    		if(count($extraPackage) > 0 && ($extraPackage->inAppMessaging > 0)) {
		    			$update['inAppMessaging'] = $extraPackage->inAppMessaging - 1;
		    			$update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
		    			$this->inapp_model->updateBrandUserExtraPackage($update);
		    		} else {
		    			//Update total campaigns
		    			$userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
		    			$inAppMessaging = $userPackage->inAppMessaging;
		    			$updateInAppMessaging = $inAppMessaging - 1;

		    			$update = array(
		    					'user_pro_id' => $userPackage->user_pro_id,
		    					'inAppMessaging' => $updateInAppMessaging
		    			);
		    			$this->campaign_model->updateBrandUserTotalCampaigns($update);

		    		}

    			}

    		}else{
    			$save['id'] = $param->campaignId;
    			$save["modifiedDate"] = date('YmdHis');
    			$this->inapp_model->updateInAppMessaging($save);
    		}
    		echo 1;


    	}
   }
}
