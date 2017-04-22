<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class LoadCampaign extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree','cookie', 'salesforce_helper', 'permission_helper', 'permission'));

        $this->load->library(array('form_validation', 'pagination'));

        $this->load->model(array('user_model', 'brand_model', 'payment_model', 'administrator_model', 'groupapp_model', 'notification_model', 'country_model', 'permission_model', 'location_model', 'email_model', 'campaign_model', 'reward_model', 'businessstore_model','offer_model','geofence_model','role_model','contact_model','hubSpot_model','crosschannel_model'));
//		    $header['allPermision'] = $this->_getpermission();
        emailConfig();
    }

  public function campaignData($campaignId) {
     $pushCampaignRow = $this->brand_model->getPushCampaignByCampaignId($campaignId);
      
       if ($pushCampaignRow->platform == 'email') {
            $countSendCampaigns = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaignId);
            if (count($countSendCampaigns) > 0) {
                $countSendCampaigns = count($countSendCampaigns);
            } else {
                $countSendCampaigns = 0;
            }

            $sendCampaigns = $this->brand_model->getEmailCampaignSendHistoryByCampaignId($campaignId);
            $send_time_arr = array();
            $send_users_arr = array();
            if (count($sendCampaigns) > 0) {
               
                foreach ($sendCampaigns as $user) {
                    $sendTime = date('Y-m-d', strtotime($user->emailSentOn)); //echo  $createdDate;
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
        } else {
            $countSendCampaigns = $this->brand_model->countCampaignSendHistoryByCampaignId($campaignId); ///print_r($countSendCampaigns); exit;
            if (count($countSendCampaigns) > 0) {
                $countSendCampaigns = count($countSendCampaigns);
            } else {
                $countSendCampaigns = 0;
            }

            $sendCampaigns = $this->brand_model->getCampaignSendHistoryByCampaignId($campaignId); //print_r($ssendCampaigns); exit;
            $send_time_arr = array();
            $send_users_arr = array();
            if (count($sendCampaigns) > 0) {
                //$send_arr = array();
                foreach ($sendCampaigns as $user) {
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
        }
        if ($pushCampaignRow->platform == 'email') {
            $countViewCampaigns = $this->brand_model->countEmailCampaignViewHistoryByCampaignId($campaignId);
            if (count($countViewCampaigns) > 0) {
                $countViewCampaigns = count($countViewCampaigns);
            } else {
                $countViewCampaigns = 0;
            }

            $viewCampaigns = $this->brand_model->getEmailCampaignViewHistoryByCampaignId($campaignId);
            foreach ($viewCampaigns as $i => $v) {
    if ($v->openTime == "0000-00-00 00:00:00") {
        unset($viewCampaigns[$i]);
    }
}
            $view_time_arr = array();
            $view_users_arr = array();
            if (count($viewCampaigns) > 0) {
                
                foreach ($viewCampaigns as $user) {
                    $viewTime = date('Y-m-d', strtotime($user->openTime));
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
        } else {
            $countViewCampaigns = $this->brand_model->countCampaignViewHistoryByCampaignId($campaignId);
            if (count($countViewCampaigns) > 0) {
                $countViewCampaigns = count($countViewCampaigns);
            } else {
                $countViewCampaigns = 0;
            }

            $viewCampaigns = $this->brand_model->getCampaignViewHistoryByCampaignId($campaignId);
            $view_time_arr = array();
            $view_users_arr = array();
            if (count($viewCampaigns) > 0) {
              
                foreach ($viewCampaigns as $user) {
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
        $result['countSendCampaigns']= $countSendCampaigns;
        $result['countViewCampaigns']= $countViewCampaigns;
        $result['campaignName']= $pushCampaignRow->campaignName;
        $result['platform']= $pushCampaignRow->platform;
        $result['push_title']= $pushCampaignRow->push_title;
        
      
      return $result;
  }
  
  
    public function insightsData() {
		$login = $this->administrator_model->front_login_session();
		if ($login->active != 0) {
			$header['page'] = 'insightsPage';
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
      //print_r(  $data['user']); exit;
      /* Customer Profiling */
      $businessId = $login->businessId;

      if(!empty($appGroupId)){
        $allUsers = $this->brand_model->getUsersByAppGroupId($businessId,$appGroupId);
      }else{
        $allUsers = $this->brand_model->getAllAppGroupsUsersByUserId($businessId);
      }

      $totalHours = 0;
      $dailyActiveUsersDateTime = array();
      $countdailyActiveUsers = 0;
      $dailySessionsUsersDateTime = array();
      $countdailySessionsUsers = 0;

    //  echo '<pre>'; echo count($allUsers); print_r($allUsers ); exit;
      $singleScanUsers = array();
      $multipleScanUsers = array();
      $countSingleUserIds = array();
      $countMultipleUserIds = array();
      $data['singleScanUsers'] = array();
      $data['multipleScanUsers'] = array();
      $data['countSingleUserIds'] = array();
      $data['countMultipleUserIds'] = array();
      $multipleUserIds = array();
      $singleUserIds = array();
      $vipUserIds = array();
      $countForVip = array();

      $data['notRedeemUsers'] = array();
      $data['appGroupId'] = 'all';
      if(!empty($appGroupId)){
        $data['appGroupId'] = $appGroupId;
      }

      if(count($allUsers)>0){
        $i =0;
        foreach ($allUsers as $user) {
          $userid = $user->external_user_id;
          $results = $this->brand_model->countLoggedInTimeUsers($userid);
          //echo '<pre>'; print_r($results); exit;
          if (count($results) > 1) {
            if(!in_array($user->external_user_id,$multipleUserIds)){
              if(count($countForVip) < 10){
                array_push($countForVip,count($results));
                array_push($vipUserIds,$userid);
              }
              array_push($multipleUserIds,$userid);
              array_push($countMultipleUserIds,$userid);
              if(count($countMultipleUserIds) < 20){
                $multipleScanUsers[] = $user;
              }
            }

          } else {
            //array_push($countForVip,count($results));
            //array_push($vipUserIds,$user->user_Id);

            if(!in_array($user->external_user_id,$singleUserIds)){
              array_push($countSingleUserIds,$userid);
              array_push($singleUserIds,$userid);
              if(count($countSingleUserIds) < 20){
                $singleScanUsers[] = $user;
              }
            }
          }

          $todayDate = date('Y-m-d',strtotime('-24 hours'));
          $userLoginTime = date('Y-m-d',strtotime($user->datetime));
          if($userLoginTime == $todayDate) { //strtotime($user->datetime) < strtotime($todayDate)
            //echo "<br />check$i:  ".$user->external_user_id.'   '.$user->datetime .'           '.$user->logoutTime; //exit;
            if(empty($user->logoutTime)){
             $time = date('Y-m-d H:i:s');
            }else{
              $time = $user->logoutTime;
            }
            $hourdiff = round((strtotime($time) - strtotime($user->datetime))/3600, 1);
            //echo "logintime: $user->datetime, logouttime: $time, hourdiff: $hourdiff".'<br />';
            $totalHours = $totalHours + $hourdiff;
            $logindate = date('Y-m-d',strtotime($user->datetime));

            if(!in_array($logindate,$dailyActiveUsersDateTime)){
              array_push($dailyActiveUsersDateTime,$logindate);
              $countdailyActiveUsers++;
            }

            if(!in_array($user->datetime,$dailySessionsUsersDateTime)){
              array_push($dailySessionsUsersDateTime,$user->datetime);
              $countdailySessionsUsers++;
            }

          }
          $data['timeOnAppUsers'] = $totalHours*30/100;
          $data['dailyActiveUsers'] = $countdailyActiveUsers*30/100;
          $data['dailySessionsUsers'] = $countdailySessionsUsers*30/100;
        }
        $freqs = array_count_values($vipUserIds);
        $vipUserIds = array_keys($freqs);

        $data['vipUserIds'] = $vipUserIds;
        $data['singleUserIds'] = $singleUserIds;
        $data['multipleUserIds'] = $multipleUserIds;

        $data['countForVip'] = count($countForVip);

        $data['singleScanUsers'] = $singleScanUsers;
        $data['multipleScanUsers'] = $multipleScanUsers;

        $data['countSingleUserIds'] = count($countSingleUserIds);
        $data['countMultipleUserIds'] = count($countMultipleUserIds);

        if(empty($appGroupId)){
          $push_campaigns = $this->brand_model->getAllPushCampaigns($login->businessId);
        }else{
          $push_campaigns = $this->brand_model->getAllPushCampaignsByAppGroupId($login->businessId,$appGroupId);
        }

        $data['pushCampaigns'] = $push_campaigns;

      }else{
        $data['vipUserIds'] = array();
        $data['singleUserIds'] = array();
        $data['multipleUserIds'] = array();
        $data['countForVip'] = 0;
        $data['countSingleUserIds'] = 0;
        $data['countMultipleUserIds'] = 0;
        $data['singleScanUsers'] = array();
        $data['multipleScanUsers'] = array();
        $data['appGroupId'] = 'all';
        $data['timeOnAppUsers'] = array();
        $data['dailyActiveUsers'] = array();
        $data['dailySessionsUsers'] = array();
        $data['pushCampaigns'] = array();
      }
      /* End Gender Stats and Age Stats*/
      // end

      $data['notRedeemUsers'] = $this->user_model->getAppUsers();
      //end
      $data['campaignPermission'] = $header['campaignPermission'];

      if($login->usertype == 9) {

      	$header['allPermision'] = getAssignPermission($login->user_id);
      	$data['allPermision'] = $header['allPermision'];
      }
//      echo "</pre>";
//      print_r($data);
//      echo "</pre>"; die;
      
            return $data;
			
		} 

	}
}