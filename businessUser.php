<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BusinessUser extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree'));
        $this->load->library(array('form_validation', 'pagination'));
        $this->load->model(array('user_model', 'administrator_model', 'notification_model', 'country_model', 'permission_model', 'location_model', 'email_model', 'campaign_model', 'reward_model', 'businessstore_model','offer_model','geofence_model'));
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active == 0) {
          redirect(base_url());
        }
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    function account() {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'account';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $data['userDetails'] = $this->user_model->getOneUser($login->user_id);
            $data['masterData'] = $this->user_model->getMasterUserData($login->businessId);
			if($login->usertype == 8 || $login->usertype == 9){
            	redirect("appUser");
            }
            if($login->usertype == 6){
            	$data['roleName'] = 'Master Admin';
            }else{
            	$roleName = $this->permission_model->userRoleNames($login->user_id);
            	//echo '<pre>';
            	//print_r($roleName); die;
            	$userRoles = '';
            	foreach ($roleName as $role){
            		$userRoles[] = $role->roleName;
            	}
            	$data['roleName'] = $userRoles;
            }

            $data['businessName'] = $login->businessName;
            // get all notifications start
            $select = "*";
            $arr_notification['actionTo'] = $login->user_id;
            $arr_notification['notification.active'] = 1;
            $arr_notification['notification.isDelete'] = 0;
            $arr_notification['actionFrom !='] = $login->user_id;
            //$arr_notification['notification.is_new']=1;
            $data['records'] = $this->notification_model->getnotification($arr_notification, $row = 1, $select = '*', '', '', $totalrecords = 1);   //// Get Total No of Records in Database

            $perpage = 6;

            $config['base_url'] = base_url() . 'index.php/businessUser/notificationfull/';
            $config['total_rows'] = count($data['records']);
            $config['per_page'] = $perpage;
            $config['uri_segment'] = 3;
            $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            //$data['page'] = $page;
            $limit = $config['per_page'];
            $order_by['order_by'] = 'distance';
            $order_by['sequence'] = 'DESC';
            $select = ' CONCAT("@", UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username , firstname, lastname, image, user_Id, image , actionString , action,  notification.createdDate as postedDate, notification.*, ( case when (usertype = 1 or usertype = 4) THEN  CONCAT_WS( " ", users.firstname, users.lastname ) ELSE  users.businessName END ) as name, challenge.game_id';
            $Notificationrecords = $this->notification_model->getnotification($arr_notification, $row = 1, $select, $page, $limit);

            $arr_update['is_new'] = 0;
            $notWhere['where'] = 'actionTo';
            $notWhere['val'] = $login->user_id;
            $this->notification_model->updateNotification($arr_update, $notWhere);

            // end
            $data['per_page'] = 1;
            $data['notifications'] = $Notificationrecords;
            $data['countries'] = $this->country_model->get_countries();
            $this->load->view('inner_header3.0', $header);
            $this->load->view('editProfile', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect('home/signup');
        }
    }

    function notificationfullPagination() {
        //$totalrecord = $_POST['totalrecord'];
        $notificationCount = $_POST['notificationCount'];
        $per_page = $_POST['per_page'];

        $start = $notificationCount;

        $baseurl = base_url();
        $data['viewPage'] = 'timeline';
        $login = $this->administrator_model->front_login_session();
        //$data['user'] = $login;

        $userid = $login->user_id;

        /* echo '<pre>'; print_r($login); die; */
        $select = "*";
        $arr_notification['actionTo'] = $login->user_id;
        $arr_notification['notification.active'] = 1;
        $arr_notification['notification.isDelete'] = 0;
        $arr_notification['actionFrom !='] = $login->user_id;
        //$arr_notification['notification.is_new']=1;

        $limit = $per_page;
        $order_by['order_by'] = 'distance';
        $order_by['sequence'] = 'DESC';
        $select = ' CONCAT("@", UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username , firstname, lastname, image, user_Id, image , actionString , action,  notification.createdDate as postedDate, notification.*, ( case when (usertype = 1 or usertype = 4) THEN  CONCAT_WS( " ", users.firstname, users.lastname ) ELSE  users.businessName END ) as name, challenge.game_id';
        $notifications = $this->notification_model->getnotification($arr_notification, $row = 1, $select, $start, $limit);

        $arr_update['is_new'] = 0;
        $notWhere['where'] = 'actionTo';
        $notWhere['val'] = $login->user_id;
        $this->notification_model->updateNotification($arr_update, $notWhere);

        if (isset($_POST['per_page'])) {
            $pagination['notifications'] = $notifications;
            $this->load->view('notificationPagination', $pagination);
        } else {
            return $notifications;
        }
    }

    function saveProfile() {

        $login = $this->administrator_model->front_login_session();
        $arr['user_Id'] = $login->user_id;
        $arr['firstname'] = $_POST['firstname'];
        $arr['lastname'] = $_POST['lastname'];
        if (!empty($_POST['newPassword'])) {
            $arr['password'] = md5($_POST['newPassword']);
        }
        $arr['contactNumber'] = $_POST['contactNumber'];
        $arr['bio'] = $_POST['bio'];

        $arr['email'] = $_POST['email'];
        $arr['country'] = $_POST['country'];
        $result = $this->user_model->updateProfile($arr,$login->businessId);
        echo 1;
        exit;
    }

    function campaignPage($locationId=NULL) {

        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'campaign';
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['userid'] = $login->user_id;
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $businessId = $login->businessId;

            $usertype = $header['loggedInUser']->usertype;

            if($usertype == 6){
              	$location['businessId'] = $businessId;
              	$location['active'] = 1;
             	$location['isDelete'] = 0;
            	$data['locations'] = $this->location_model->getLocations($location);
            }
            if($usertype == 7){
            	$locations = $this->location_model->getUserLocations($login->user_id);

            	$data['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            	$header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            	foreach($locations as $userlocation){

            		$location['branch_id'] = $userlocation->locationid;

            		//$locationArray[]= $this->location_model->getLocations($location)[0];
            		$locationArray[] = $userlocation->locationid;

            	}
            	$data['locations'] = $this->location_model->getUserlocationBranch($locationArray);
            }

            //Get Live Campaigns
        	if($usertype == 6){
        		$activeCampaigns = $this->campaign_model->getActiveCampaigns($businessId);

        		$campaignId = '';
        		foreach($activeCampaigns as $campaign){
        			$campaignId[] = $campaign->campaignId;
        		}

        		$where['businessId'] = $businessId;
        		$where['campaign_id'] = $campaignId;
        		$data['live_campaigns'] = $this->campaign_model->getBusinessAdminLiveCampaigns($where);

        	}

        	if($usertype == 7){
        		$locations = $this->location_model->getUserLocations($login->user_id);
        		foreach($locations as $userlocation){

        			$BusinessUserCampaigns[] = $userlocation->locationid;

        		}

        		$getCampaigns = $this->campaign_model->getcampaignlocationmap($BusinessUserCampaigns);
				if(count($getCampaigns)>0){
        		foreach($getCampaigns as $getCampaign){
        			$campaignslive[] = $getCampaign->campaignId;
        		}
				}else{
					$campaignslive = '';
				}
        		$data['live_campaigns'] = $this->campaign_model->getLiveCampaigns($campaignslive);

        	}

        	//Get Best & worst Campaigns
        	if($usertype == 6){
        		$activeCampaigns = $this->campaign_model->getActiveCampaigns($businessId);
        		$campaignId = '';
        		foreach($activeCampaigns as $campaign){
        			$campaignId[] = $campaign->campaignId;
        		}

        		$where['businessId'] = $businessId;
        		$where['campaign_id'] = $campaignId;

        		$data['best_campaigns'] = $this->campaign_model->getBusinessAdminBestCampaigns($where);
            //echo '<pre>'; print_r(	$data['best_campaigns']); exit;
        	}if($usertype == 7){

        		$data['best_campaigns'] = $this->campaign_model->getBusinessUserBestCampaigns($campaignslive);
        	}

        	//For particular location
        	if($locationId != ''){

        		//Live Campaigns
        		if($usertype == 6){
        			$getCampaigns = $this->campaign_model->getcampaignlocationmap($locationId);
        			if(count($getCampaigns)>0){
        			foreach($getCampaigns as $getCampaign){
        				$campaignslive[] = $getCampaign->campaignId;
        			}
        			}else{
        				$campaignslive = '';
        			}
        			$data['live_campaigns'] = $this->campaign_model->getLiveCampaigns($campaignslive);
        			/* $where['businessId'] = $businessId;
        			$where['locationId'] = $locationId; */
        			//$data['live_campaigns'] = $this->campaign_model->getBusinessAdminLocationLiveCampaigns($campaigns);
        			/* echo '<pre>';
        			print_r($data['live_campaigns']); die; */
        		}

        		if($usertype == 7){
        			/* $locations = $this->location_model->getUserLocations($login->user_id);
        			foreach($locations as $userlocation){

        				$BusinessUserCampaigns[] = $userlocation->locationid;

        			} */
        			$getCampaigns = $this->campaign_model->getcampaignlocationmap($locationId);
        			foreach($getCampaigns as $getCampaign){
        				$campaignslive[] = $getCampaign->campaignId;
        			}

        			$data['live_campaigns'] = $this->campaign_model->getLiveCampaigns($campaignslive);

        		}


        		//Get Best & worst Campaigns
        		if($usertype == 6){

        			$getCampaigns = $this->campaign_model->getcampaignlocationmap($locationId);
        			if(count($getCampaigns) > 0){
        			foreach($getCampaigns as $getCampaign){
        				$campaigns[] = $getCampaign->campaignId;

        			}
        			}else{
        				$campaigns = '';
        			}

        			//$where['businessId'] = $businessId;
        			//$where['locationId'] = $locationId;
        			$data['best_campaigns'] = $this->campaign_model->getBusinessAdminLocationBestCampaigns($campaigns);
        			/* echo '<pre>';
        			print_r($data['best_campaigns']);die; */

        		}if($usertype == 7){
        			$getCampaigns = $this->campaign_model->getcampaignlocationmap($locationId);
					if(count($getCampaigns) > 0){
        			foreach($getCampaigns as $getCampaign){
        				$campaigns[] = $getCampaign->campaignId;
        			}
					}else{
						$campaigns = '';
					}
        			$data['best_campaigns'] = $this->campaign_model->getBusinessUserBestCampaigns($campaigns);

        		}
        	}

            $this->load->view('inner_header3.0', $header);
            $this->load->view('campaignPage', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect(base_url());
        }
    }

    function performance($campaignId) {
        $login      = $this->administrator_model->front_login_session();
        $user_id    = $login->user_id;
        $businessId = $login->businessId;
        $type       = 'campaigns';

      	$data['campaignId'] = $campaignId;
        $data['genderUsers'] = $this->geofence_model->getPerformances($type,$campaignId,$user_id);
        $campaignNotiPer = $this->geofence_model->getPerformancesByDate($type,$campaignId,$user_id);
        //echo '<pre>';
        //print_r($data['genderUsers']); exit;
        $data['campaignNotiPer'] = $campaignNotiPer;
        //print_r($data['campaignNotiPer']); exit;

      	$this->load->view('campaign_performance',$data);
    }

    function location($campaignId) {

        $getLocations = $this->campaign_model->getCampaignLocations($campaignId);

        foreach ($getLocations as $getLocation) {
            $locations[] = $getLocation->locationId;
        }

        $data['campaignlocations'] = $this->campaign_model->getCampaignLocation($locations);

        $this->load->view('campaign_location', $data);
    }

    function insightsPage($branchId=false) {
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->active != 0) {
            $header['page'] = 'insight';
            $header['userid'] = $header['login']->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($header['login']->user_id);
            $header['usertype'] = $header['login']->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($header['login']->user_id);
            // added code
            $userid = $header['login']->user_id;
            $usertype = $header['login']->usertype;
            $data['loginuser'] = $header['login']->user_id;
            $data['user'] = $header['loggedInUser']; //$this->user_model->getOneUser($userid);
            $data['viewPage'] = 'insights';

            /* Customer Profiling  */
            $businessId = $header['login']->businessId;

            if(!empty($branchId)){
               $allUsers = $this->user_model->scannedUsersByLocation($businessId,$branchId);
            }else{
               $allUsers = $this->user_model->scannedUsers($businessId);
            }

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
            $data['segment1Arr'] =  array();
            $data['segment2Arr'] =  array();
            $data['segment3Arr'] =  array();
            $data['segment1Users'] =  array();
            $data['segment2Users'] =  array();
            $data['segment3Users'] =  array();

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
                        if(count($countForVip) < 10){
                          array_push($countForVip,count($results));
                          array_push($vipUserIds,$user->user_Id);
                        }
                        array_push($multipleUserIds,$user->user_Id);
                        array_push($countMultipleUserIds,$user->user_Id);
                        if(count($countMultipleUserIds) < 20){
                           $multipleScanUsers[] = $user;
                        }
                      }

                    } else {
                       //array_push($countForVip,count($results));
                       //array_push($vipUserIds,$user->user_Id);

                       if(!in_array($user->user_Id,$singleUserIds)){
                           array_push($countSingleUserIds,$user->user_Id);
                           array_push($singleUserIds,$user->user_Id);
                           if(count($countSingleUserIds) < 20){
                             $singleScanUsers[] = $user;
                           }
                       }
                    }
                }
                $freqs = array_count_values($vipUserIds);
                $vipUserIds = array_keys($freqs);

                $data['vipUserIds'] = $vipUserIds;
                $data['singleUserIds'] = $singleUserIds;
                $data['multipleUserIds'] = $multipleUserIds;

                // find best performing group
                $genderuser = array();
                foreach($allUsers as $singleuser){
                   $genderuser[] = $singleuser->user_Id;
                }

                // $genders = $this->user_model->getGender($genderuser);
                if(count($genderuser)>0){
                  $newGender = array();
                  $male = array();
                  $female = array();
                  for($i =0; $i< count($genderuser); $i++){

                     $genders = $this->user_model->getGender($genderuser[$i]);

                     $newGender[] =  $genders->gender;
                     if($genders->gender == 'male'){
                         if(!in_array($genders->user_Id,$male)){
                           array_push($male,$genders->user_Id);
                         }
                     }else{
                         if(!in_array($genders->user_Id,$female)){
                            array_push($female,$genders->user_Id);
                         }
                     }
                  }

                  $c = array_count_values($newGender);
                  $val = array_search(max($c), $c);
                  $minval = array_search(min($c), $c);
                  if($val == $minval){
                    if($minval ==  'female'){
                      $minval = 'male';
                    }
                  }
                  //print_r($newGender); echo $val,$minval;
                  if($val == 'female'){
                        $gender = 'F';
                        $genderwiseuserids = $female;
                  }else{
                       $gender = 'M';
                       $genderwiseuserids = $male;
                  }

                  // worst performing group

                  if($minval == 'female'){
                        $mingender = 'F';
                        $mingenderwiseuserids = $female;
                  }else{
                       $mingender = 'M';
                       $mingenderwiseuserids = $male;
                  }

                  $data['bestGroup']['gender'] = $gender;
                  $data['worstGroup']['gender'] = $mingender;
                  $genderUsers = $this->user_model->getAge($businessId);
                  //print_r($data['bestGroup']['gender']); exit;
                  $ageSixteen = array();
                  $ageTwentyFive = array();
                  $ageThirtyFive = array();
                  $agemax = array();

                  $ageArray = array(); $bestAgeUserIds = array(); $WorstAgeUserIds = array();
                  foreach($genderUsers as $user){
                      $birthday_timestamp = strtotime($user->date_of_birth);
                      $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                      if($age>=16 && $age <=25 )
                      {
                              array_push($ageArray,$age);
                              array_push($ageSixteen,$user->user_Id);
                      }else if($age>=25 && $age <=35 )
                      {
                              array_push($ageArray,$age);
                              array_push($ageTwentyFive,$user->user_Id);
                      }else if($age>=35 && $age <=50 )
                      {
                              array_push($ageArray,$age);
                              array_push($ageThirtyFive,$user->user_Id);
                      }else{
                              array_push($ageArray,$age);
                              array_push($agemax,$user->user_Id);
                      }

                     $bestAgeArray = max($ageArray);
                     $worstAgeArray = min($ageArray);
                     $data['BestAgeFactor'] = $bestAgeArray;
                     $data['WorstAgeFactor'] = $worstAgeArray;

                  }
                  //print_r($ageArray);
                  //print_r( $data['WorstAgeFactor']); exit;
                  if($data['BestAgeFactor']>=16 && $data['BestAgeFactor'] <=25 )
                  {
                          array_push($bestAgeUserIds,$ageSixteen);
                  }
                  else if($data['BestAgeFactor']>=25 && $data['BestAgeFactor'] <=35 )
                  {
                          array_push($bestAgeUserIds,$ageTwentyFive);
                  }
                  else if($data['BestAgeFactor']>=35 && $data['BestAgeFactor'] <=50 )
                  {
                          array_push($bestAgeUserIds,$ageThirtyFive);
                  }else{
                          array_push($bestAgeUserIds,$agemax);
                  }
                   // end best performing group
                  $data['bestAgeUserIds'] = $bestAgeUserIds[0];

                  if($data['WorstAgeFactor']>=16 && $data['WorstAgeFactor'] <=25 )
                  {
                      if($ageSixteen === $bestAgeUserIds){
                          array_push($WorstAgeUserIds,$ageSixteen);
                      }
                  }
                  else if($data['WorstAgeFactor']>=25 && $data['WorstAgeFactor'] <=35 )
                  {
                      if($ageTwentyFive === $bestAgeUserIds){
                          array_push($WorstAgeUserIds,$ageTwentyFive);
                      }
                  }
                  else if($data['WorstAgeFactor']>=35 && $data['WorstAgeFactor'] <=50 )
                  {
                      if($ageThirtyFive === $bestAgeUserIds){
                          array_push($WorstAgeUserIds,$ageThirtyFive);
                      }
                  }else{
                      if($agemax === $bestAgeUserIds){
                          array_push($WorstAgeUserIds,$agemax);
                      }
                  }

                  if(count($WorstAgeUserIds) > 0){
                    $data['WorstAgeUserIds'] = $WorstAgeUserIds[0];
                  }else{
                    $data['WorstAgeUserIds'] = $WorstAgeUserIds;
                  }
                }else{
                    $data['BestAgeFactor'] = 0;
                    $data['WorstAgeFactor'] = 0;
                    $data['bestAgeUserIds'] = '';
                    $data['bestGroup']['gender'] = 'NA';
                    $data['worstGroup']['gender'] = 'NA';
                }
                $data['countForVip'] = count($countForVip);

                $data['singleScanUsers'] = $singleScanUsers;
                $data['multipleScanUsers'] = $multipleScanUsers;

                $data['countSingleUserIds'] = count($countSingleUserIds);
                $data['countMultipleUserIds'] = count($countMultipleUserIds);

                $data['genderUsers'] = $genderUsers;

                //echo '<pre>';
                //print_r($genderUsers); exit;
                /* End Customer Profiling  */

                /* Gender Stats and Age Stats  */
                // $data['genderUsers'] = $this->user_model->getAge($businessId);
                // echo 'called'; exit;
               //$data['genderUsers'] = array(); //$this->geofence_model->getPerformances($type='campaign',$geofence_id='',$businessId);

          }else{
              $data['vipUserIds'] = array();
              $data['singleUserIds'] = array();
              $data['multipleUserIds'] = array();
              $data['BestAgeFactor'] = 0;
              $data['WorstAgeFactor'] = 0;
              $data['bestAgeUserIds'] = array();
              $data['WorstAgeUserIds'] = array();
              $data['bestGroup']['gender'] = 'NA';
              $data['worstGroup']['gender'] = 'NA';
              $data['countForVip'] = 0;
              $data['countSingleUserIds'] = 0;
              $data['countMultipleUserIds'] = 0;
              $data['singleScanUsers'] = array();
              $data['multipleScanUsers'] = array();
              $data['genderUsers'] =  array();
         }
          /* End Gender Stats and Age Stats  */
          // end
         // user did not reddeem any campaign
         // end user did not reddeem any campaign

          $data['notRedeemUsers'] = $this->user_model->getAppUsers();
          $data['segmentData'] = $this->campaign_model->getAllUserSegment($businessId);
          $data['segment1Arr'] = $this->campaign_model->getUserSegment(1,$businessId);
          $data['segment2Arr'] = $this->campaign_model->getUserSegment(2,$businessId);
          $data['segment3Arr'] = $this->campaign_model->getUserSegment(3,$businessId);
          $data['segment1Users'] = $this->getSegmentCriteriaUsers(1,$businessId);
          $data['segment2Users'] = $this->getSegmentCriteriaUsers(2,$businessId);
          $data['segment3Users'] = $this->getSegmentCriteriaUsers(3,$businessId);

          $data['locations'] = $this->user_model->getAllBranchesByUserId($businessId,$userid);//$this->offer_model->getCampignLocations();

          //end
          $data['campaignPermission'] = $header['campaignPermission'];// $this->permission_model->getCampaignPermission($header['login']->user_id);
          $this->load->view('inner_header3.0', $header);
          $this->load->view('insightPage',$data);
          if(count($data['genderUsers']) > 0){
             $this->load->view('custom_chart', $data);
          }
          $this->load->view('inner_footer3.0');
        } else {
            redirect(base_url());
        }
    }

    function getSegmentCriteriaUsers($segmentType,$businessId){
        $users = array();
        $usersIds = array();
        $segmentData = array();
        $segmentAgeArr = array();

        $userSegment = $this->campaign_model->getUserSegment($segmentType,$businessId);
        if(count($userSegment) > 0){
            if(!empty($userSegment->segmentGender) && empty($userSegment->segmentAge)){
              $segmentData = array('gender' => $userSegment->segmentGender);
              $users = $this->user_model->getSegmentUsersList($segmentData);
              if(count($users) > 0){
                foreach ($users as $user) {
                  array_push($usersIds,$user->user_Id);
                }
              }
              $users = $usersIds;
            }else if(!empty($userSegment->segmentAge) && empty($userSegment->segmentGender)){
              $users = $this->user_model->getSegmentUsersList($segmentData);
              $ageArray = array();
              $segmentAge = explode('-',$userSegment->segmentAge);
              $segmentAgeMin = $segmentAge[0];
              $segmentAgeMax = isset($segmentAge[1]) ? $segmentAge[1] : 0;;
              if($userSegment->segmentAge == 'over 50'){ $segmentAgeMin = '50'; $segmentAgeMax = '99';}
              foreach($users as $user){
                  $birthday_timestamp = strtotime($user->date_of_birth);
                  $age = date('md', $birthday_timestamp) > date('md') ? date('Y') - date('Y', $birthday_timestamp) - 1 : date('Y') - date('Y', $birthday_timestamp);

                  if($age>=$segmentAgeMin && $age <=$segmentAgeMax )
                  {
                      array_push($ageArray,$age);
                      array_push($usersIds,$user->user_Id);
                  }
              }
              $users = $usersIds;
            }else if(!empty($userSegment->segmentWhoHave) && empty($userSegment->segmentGender) && empty($userSegment->segmentAge)){
                $segmentData = array('segmentWhoHave' => $userSegment->segmentWhoHave, 'businessId'=> $businessId);
                $users = $this->user_model->getSegmentUsersList($segmentData);
                if(count($users) > 0){
                  foreach ($users as $user) {
                    array_push($usersIds,$user->user_Id);
                  }
                }
                $users = $usersIds;
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
                        array_push($addUsers,$user->user_Id);
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
                        array_push($addUsers,$user->user_Id);
                    }
                }
                $users = $addUsers;

            }
        }
        return $users;
    }

    function rewardsPage($locationId = NULL) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'rewards';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;
            $usertype = $header['loggedInUser']->usertype;
            $data['page'] = 'rewards';
            $data['user'] = $this->user_model->getOneUser($login->user_id);
            $data['rewardsPermission'] = array();
            if ($usertype == 6) {
                $location['businessId'] = $businessId;
                $location['active'] = 1;
                $location['isDelete'] = 0;
                $data['locations'] = $this->location_model->getLocations($location);
            }
            if ($usertype == 7) {
                $locations = $this->location_model->getUserLocations($login->user_id);

                foreach ($locations as $userlocation) {

                    $location['branch_id'] = $userlocation->locationid;
                    $locationArray[] = $userlocation->locationid;
                }
                $data['rewardsPermission'] = $this->permission_model->getRewardsPermission($login->user_id);
                $data['locations'] = $this->location_model->getUserlocationBranch($locationArray);
            }

            //Get Live Campaigns
            if ($usertype == 6) {

            	$activeRewards = $this->reward_model->getActiveRewards($businessId);

            	$rewardId = '';
            	foreach($activeRewards as $reward){
            		$rewardId[] = $reward->rewardId;
            	}

            	$where['businessId'] = $businessId;
            	$where['reward_id'] = $rewardId;

                $data['live_rewards'] = $this->reward_model->getBusinessAdminLiveRewards($where);
            }

            if ($usertype == 7) {
                $locations = $this->location_model->getUserLocations($login->user_id);
                foreach ($locations as $userlocation) {

                    $BusinessUserRewards[] = $userlocation->locationid;
                }

                $getRewards = $this->reward_model->getrewardslocationmap($BusinessUserRewards);
				if(count($getRewards)>0){
                foreach ($getRewards as $getReward) {
                    $rewards[] = $getReward->rewardId;
                }
				}else{
					$rewards = '';
				}

                $data['live_rewards'] = $this->reward_model->getLiveRewards($rewards);
            }

            //Get Best & worst Campaigns
            if ($usertype == 6) {

            	$activeRewards = $this->reward_model->getActiveRewards($businessId);

            	$rewardId = '';
            	foreach($activeRewards as $reward){
            		$rewardId[] = $reward->rewardId;
            	}

            	$where['businessId'] = $businessId;
            	$where['reward_id'] = $rewardId;

                $data['best_rewards'] = $this->reward_model->getBusinessAdminBestRewards($where);
            }if ($usertype == 7) {

                $data['best_rewards'] = $this->reward_model->getBusinessUserBestRewards($rewards);
            }

            //For particular location
            if ($locationId != '') {

                //Live Campaigns
                if ($usertype == 6) {
                    $getRewards = $this->reward_model->getrewardslocationmap($locationId);
                    if (count($getRewards) > 0) {
                        foreach ($getRewards as $getReward) {
                            $rewardslive[] = $getReward->rewardId;
                        }
                    } else {
                        $rewardslive = '';
                    }

                    $data['live_rewards'] = $this->reward_model->getLiveRewards($rewardslive);
                }

                if ($usertype == 7) {

                    $getRewards = $this->reward_model->getrewardslocationmap($locationId);
                    if(count($getRewards)>0){
                    foreach ($getRewards as $getReward) {
                        $rewardslive[] = $getReward->rewardId;
                    }
                    }else{
                    	$rewardslive = '';
                    }

                    $data['live_rewards'] = $this->reward_model->getLiveRewards($rewardslive);
                }


                //Get Best & worst Campaigns
                if ($usertype == 6) {

                    $getRewards = $this->reward_model->getrewardslocationmap($locationId);
                    if (count($getRewards) > 0) {
                        foreach ($getRewards as $getReward) {
                            $rewards[] = $getReward->rewardId;
                        }
                    } else {
                        $rewards = '';
                    }
                    $data['best_rewards'] = $this->reward_model->getBusinessAdminLocationBestRewards($rewards);
                }
                if ($usertype == 7) {
                    $getRewards = $this->reward_model->getrewardslocationmap($locationId);

                    foreach ($getRewards as $getReward) {
                        $rewards[] = $getReward->rewardId;
                    }
                    $data['best_rewards'] = $this->reward_model->getBusinessUserBestRewards($rewards);
                }
            }

            $this->load->view('inner_header3.0', $header);
            $this->load->view('rewardsPage', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect(base_url());
        }
    }



    function locationPage() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'location';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $header['usertype'] = $login->usertype;

            $data['businessUser'] = $this->user_model->getAllBranchesByUserId($login->businessId, $login->user_id);
            //echo '<pre>'; print_r($data['businessUser']); exit;

            $this->load->view('inner_header3.0', $header);
            $this->load->view('locationPage', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect(base_url());
        }
    }

    function addLocation($id = false) {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;

        $userPackage = $this->campaign_model->getUserLocationPackageInfo($userid);
        //print_r($userPackage ); exit;
        if (count($userPackage) > 0) {
            $data['countTotalLocations'] = $userPackage->totalLocations;
        } else {
            $data['countTotalLocations'] = 0;
        }
        //Extra Locations
        $extraPackage = $this->campaign_model->getBusinessLocationPackage($businessId);
        //print_r($extraPackage); exit;
        if (count($extraPackage) > 0) {
            $data['extraLocationsQuantity'] = $extraPackage->quantity;
        } else {
            $data['extraLocationsQuantity'] = 0;
        }
        $data['result'] = array();
        if (!empty($id)) {
            $data['result'] = $this->user_model->getBranchByBranchId($id);
        }
        $this->load->view('addLocation', $data);
    }

    function getLocationPackages() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'crm';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $data['packages'] = $this->location_model->get_packages();

            $this->load->view('inner_header3.0', $header);
            $this->load->view('getLocationPackages', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect('home/signup');
        }
    }

    function businesAddNewLocation() {
        $login = $this->administrator_model->front_login_session();
        $complete_address = $_POST['business_address'] . ',' . $_POST['business_town'] . ',' . $complete_address = $_POST['business_postcode'];
        $complete_address = urlencode($complete_address);
        $latitude = '';
        $longitude = '';
        $response = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $complete_address . '&sensor=false');
        $json = json_decode($response);
        foreach ($json->results as $res) {
            $geo = $res->geometry;
            $location = $geo->location;

            $latitude = $location->lat;
            $longitude = $location->lng;
        }
        $business_name = $_POST['business_name'];
        $business_email = $_POST['business_email'];
        $business_address = $_POST['business_address'];
        $business_town = $_POST['business_town'];
        $business_postcode = $_POST['business_postcode'];

        $businessId = $login->businessId;
        $branch['branch_id'] = '';
        $branch['userid'] = $login->user_id;
        $branch['email'] = $business_email;
        $branch['businessId'] = $businessId;
        $branch['businessCategory'] = '';
        $branch['store_name'] = $business_name;
        $branch['country'] = '';
        $branch['address'] = $business_address;
        $branch['address2'] = '';
        $branch['latitude'] = $latitude;
        $branch['longitude'] = $longitude;
        $branch['town'] = $business_town;
        $branch['postcode'] = $business_postcode;
        $branch['phone'] = '';
        $branch['website'] = '';
        $branch['peopleVisit'] = '';
        $branch['description'] = '';
        $branch['main_branch'] = '';
        $branch['active'] = 1;
        $branch['coinDate'] = date('Y-m-d H:i:s');
        $branch['createdDate'] = date('Y-m-d H:i:s');
        if (!empty($_POST['branch_id'])) {
            $branch['branch_id'] = $_POST['branch_id'];
            $branchid = $this->user_model->savebusinessbranch($branch);
        } else {
            $branchid = $this->user_model->savebusinessbranch($branch);
            $userid = $login->user_id;
            $extraPackage = $this->campaign_model->getBusinessLocationPackage($businessId);
            if (count($extraPackage) > 0 && ($extraPackage->quantity > 0)) {
                if ($extraPackage->quantity != '0') {
                    $update['quantity'] = $extraPackage->quantity - 1;
                } else {
                    $update['quantity'] = $extraPackage->quantity;
                }

                $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                $this->campaign_model->updateExtraPackage($update);
            } else {
                //Update total campaigns
                $userPackage = $this->campaign_model->getBusinessLocationPackageInfo($businessId);
                $totalLocations = $userPackage->totalLocations;
                $updateTotalLocations = $totalLocations - 1;

                $update = array(
                    'businessId' => $businessId,
                    'totalLocations' => $updateTotalLocations
                );
                $this->campaign_model->updateTotalLocations($update);
            }
        }
        echo "Success";
        exit;
    }

    public function buyLocationPackage($id = false) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'location package';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);

            $businessStoreId = $this->uri->segment('3');
            $data['package'] = $this->location_model->getOnePackage($id);
            $data['countries'] = $this->country_model->get_countries();
            $arr_card_type['active'] = 1;
            $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);
            $data['recurringPackage'] = array();
            if ($id == 1 || $id == 2 || $id == 3 || $id == 4) {
                $data['recurringPackage'] = $this->businessstore_model->checkRecurringLocationPackageExist($id, $login->user_id);
            }
            $this->load->view('location_checkout', $data);
        } else {
            redirect('home/signup');
        }
    }

    public function deleteLocation($id = false) {
        if (!empty($_POST)) {
            $branchRow = $this->user_model->getBranchByBranchId($_POST['branch_id']);
            $result = $this->location_model->checkLocationIsAssigned($_POST['branch_id']);
            $business_branch = $this->user_model->getAllBranchesByUserId($branchRow->businessId, $branchRow->user_Id);
            //print_r($business_branch); exit;
            if (count($business_branch) == 1) {
                echo 'You cannot delete this location. we need atleast one location for business.';
            } else if (count($result) > 0) {
                echo 'You cannot delete this location. this location is already assigned another user.';
            } else {
                $where['branch_id'] = $_POST['branch_id'];
                $where['active'] = 0;
                $result = $this->location_model->deleteBranch($where);

                $login = $this->administrator_model->front_login_session();
                $userid = $login->user_id;
                $businessId = $login->businessId;
                $extraPackage = $this->campaign_model->getBusinessLocationPackage($businessId);
                if (count($extraPackage) > 0 && ($extraPackage->quantity > 0)) {
                    if ($extraPackage->quantity != '0') {
                        $update['quantity'] = $extraPackage->quantity + 1;
                    } else {
                        $update['quantity'] = $extraPackage->quantity;
                    }

                    $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                    $this->campaign_model->updateExtraPackage($update);
                } else {
                    //Update total campaigns
                    $userPackage = $this->campaign_model->getBusinessLocationPackageInfo($businessId);
                    $totalLocations = 0;
                    if (isset($userPackage->totalLocations)) {
                        $totalLocations = $userPackage->totalLocations;
                    }

                    $updateTotalLocations = $totalLocations + 1;
                    //print_r($userPackage); exit;
                    $update = array(
                        'businessId' => $businessId,
                        'totalLocations' => $updateTotalLocations
                    );
                    $this->campaign_model->updateTotalLocations($update);
                }
                echo '1';
                exit;
            }
        } else {
            $data['branch_id'] = $id;
            $this->load->view('deleteLocation', $data);
        }
    }

    function saveCheckoutPayment() {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $email = $login->email;
        $username = $login->username;
        $businessId = $login->businessId;
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
        $amount = $_POST['amount'];
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
        $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z"));
        $desc = "Payment of hurree store";

        //// CREATE AN STRING
        $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
                "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country" .
                "&CURRENCYCODE=$currencyID&PROFILESTARTDATE=$profileStartDate&MAXFAILEDPAYMENTS=3" .
                "&DESC=$desc&BILLINGPERIOD=Month" .
                "&BILLINGFREQUENCY=1";

        //SEND REQUEST TO PAYPAL reoccuring payment
        $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);
        //echo '<pre>'; print_r($httpParsedResponseAr ); exit; //Success
        if ($httpParsedResponseAr['ACK'] == 'Success') {

            $recurringPayment['business_store_payment_id'] = '';
            $recurringPayment['user_id'] = $userid;
            $recurringPayment['locationPackageId'] = $packageid;
            $recurringPayment['purchasedOn'] = date('YmdHis');
            $recurringPayment['amount'] = $amount;
            $recurringPayment['currency'] = $currency;
            $recurringPayment['profile_id'] = $httpParsedResponseAr['PROFILEID'];
            $recurringPayment['payment_response'] = json_encode($httpParsedResponseAr);
            $recurringPayment['isActive'] = 1;
            $recurringPayment['isDelete'] = 0;
            $recurringPayment['createdDate'] = date('YmdHis');

            $last_payment_id = $this->businessstore_model->savepayment($recurringPayment);

            $package = $this->location_model->getOnePackage($packageid);
            //print_r($package); exit;
            $description = $package->package_description;
            $quantity = $package->num_of_locations;

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
                $recurringInsert['totalLocations'] = 0;
                $recurringInsert['createdDate'] = $date;
                $recurringInsert['modifiedDate'] = $date;
                $last_insert_id = $this->user_model->savePackage($recurringInsert);
            }

            $extraPackage['businessId'] = $businessId;
            $extraPackage['locationPackageId'] = $packageid;
            $userExtraPackage = $this->campaign_model->getBusinessLocationExtraPackage($extraPackage);

            if (count($userExtraPackage) == 0) {
                //Insert
                if ($package->package_description == '') {
                    $num_of_locations = '';
                } else {
                    $num_of_locations = $package->num_of_locations;
                }
                $current_date = date('YmdHis');
                $userInsert['user_extra_packages_id'] = '';
                $userInsert['userid'] = $userid;
                $userInsert['businessId'] = $businessId;
                $userInsert['locationPackageId'] = $packageid;
                $userInsert['quantity'] = $num_of_locations;
                $userInsert['expiry_date'] = date('Y-m-d', strtotime("+2 years"));
                $userInsert['createdDate'] = $current_date;

                $this->campaign_model->saveExtraPackage($userInsert);
            } else {
                //Update
                if ($package->package_description == '') {
                    $num_of_locations = '';
                } else {
                    $num_of_locations = $package->num_of_locations;
                }
                $current_date = date('YmdHis');
                $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;

                $userInsert['locationPackageId'] = $packageid;
                $update['quantity'] = $num_of_locations;
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

                if ($package->num_of_locations == 0) {
                    $packageName = $package->package_description;
                } else {
                    $packageName = $package->num_of_locations . " " . $package->package_description;
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
    }

    function PPHttpPost($methodName_, $nvpStr_) {
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

        return $httpParsedResponseAr;
    }

    function crmPage() {
    	$data = array();
    	$login = $this->administrator_model->front_login_session();
        $data['hubSpotlogin'] = 0;

//         if($_GET)
//         {
//         	if(!isset($_GET['status']))
//         	{
//         		$accressToken = $_GET['access_token'];
// 	        	$refresh_token= $_GET['refresh_token'];
// 	        	$expires_in = $_GET['expires_in'];

// 	        	$userHubId = $this->session->userdata('userHubId');

// 	        	$update['userHubSpotId'] = $userHubId;
// 	        	$update['userid'] = $login->user_id;
// 	        	$update['accress_token'] = $accressToken;
// 	        	$update['refresh_token'] = $refresh_token;
// 	        	$last_id = $this->hubSpot_model->save($update);
//         	}
//         	$data['hubSpotlogin'] =  1;
   //     }

        if ($login->active != 0) {
            $header['page'] = 'crm';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

           // print_r($data); die;
            $this->load->view('inner_header3.0', $header);
            $this->load->view('crmPage', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect('home/signup');
        }
    }

    function userListing() {
        $login = $this->administrator_model->front_login_session();
        ///echo $login->businessId; exit;
        if ($login->active != 0) {
            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $where['users.businessId'] = $login->businessId;
            $where['users.createdBy'] = $login->user_id;
            $where['users.isDelete'] = 0;
            //$where['business_branch.active'] = 1;
            $data['users'] = $this->user_model->getUsers($where);
            $this->load->view('inner_header3.0', $header);
            $this->load->view('userListing', $data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect('home/signup');
        }
    }

    function userListingResponse() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {

            $where['users.businessId'] = $login->businessId;
            $where['users.createdBy'] = $login->user_id;
            $where['users.isDelete'] = 0;
            $data2['users'] = $this->user_model->getUsers($where);

            $data1 = array();

            for ($i = 0; $i < count($data2['users']); $i++) {

                   $result = $this->user_model->getOneUser($data2['users'][$i]['user_Id']);

                    if ($data2['users'][$i]['usertype'] == 7) {
                        $data2['users'][$i]['store_names'] = $this->user_model->getUserLocation($data2['users'][$i]['user_Id']);
                    }
                    if ($data2['users'][$i]['usertype'] == 6) {
                        $data2['users'][$i]['type'] = 'Master admin';
                    }
                    if ($data2['users'][$i]['usertype'] == 7) {
                        $data2['users'][$i]['type'] = 'Business User';
                    }
                    if ($login->usertype == 6 && !empty($result->createdBy)) {
                        $data2['users'][$i]['action'] = $data2['users'][$i]['action'];
                    }
                     else if ($login->usertype == 6 && empty($result->createdBy)) {
                        $data2['users'][$i]['action'] = '';
                    }else if ($login->usertype == 7) {
                        $data2['users'][$i]['action'] = '';
                    }

                    $data1[$i] = array(
                        $data2['users'][$i]['userimage'],
                        ucfirst($data2['users'][$i]['firstname']) . ' ' . ucfirst($data2['users'][$i]['lastname']),
                        $data2['users'][$i]['email'],
                        $data2['users'][$i]['store_names'],
                        $data2['users'][$i]['type'],
                        $data2['users'][$i]['action'],
                    );

            }

            $response = array(
                "data" => $data1
            );

            echo json_encode($response);
            exit;
        } else {
            redirect(base_url());
        }
    }

    function addUserPopUp() {

        $login = $this->administrator_model->front_login_session();

        $userid = $login->user_id;
        $businessId = $login->businessId;

        $location['businessId'] = $businessId;
        $location['isDelete'] = 0;
        $location['active'] = 1;
        $data['locations'] = $this->location_model->getLocations($location);

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);
        //print_r($data['permissions']); die;
        $this->load->view('add_user', $data);
    }

    function addmorelocations() {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;

        $data['counter'] = $_POST['counter'];

        $location['businessId'] = $businessId;
        $location['isDelete'] = 0;
        $location['active'] = 1;
        $data['locations'] = $this->location_model->getLocations($location);

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);

        echo $this->load->view('addmorelocations', $data);
    }

    function editAddmorelocations() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;

        $data['counter'] = $_POST['counter'];

        $location['businessId'] = $businessId;
        $location['isDelete'] = 0;
        $location['active'] = 1;
        $data['locations'] = $this->location_model->getLocations($location);

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);

        echo $this->load->view('editaddmorelocations', $data);
    }

    function saveUser() {

        $login = $this->administrator_model->front_login_session();
        $masterAdmin = $login->user_id;
        $businessId = $login->businessId;
        $businessName = $login->businessName;
        $CreatedBy = $this->user_model->getOneUser($masterAdmin);

        $masterAdminName = $CreatedBy->firstname . " " . $CreatedBy->lastname;

        $businessUserFirstName = $_POST['firstname'];
        $businessUserEmail = trim($_POST['email']);

        //print_r($_POST); die;

        $rand = $this->RandomStringCreateUsername();

        $save = array();
        $save['user_Id'] = '';
        $save['businessId'] = $businessId;
        $save['businessName'] = $businessName;
        $save['createdBy'] = $masterAdmin;
        $save['firstname'] = $_POST['firstname'];
        $save['lastname'] = $_POST['lastname'];
        $save['username'] = '{Username}';
        $save['email'] = $businessUserEmail;
        $save['usertype'] = $_POST['usertype'];
        $save['image'] = 'user.png';
        $save['loginSource'] = 'normal';
        $save['username_create_token'] = $rand;
        $save['createdDate'] = date('YmdHis');

        $last_insertId = $this->user_model->insertsignup($save);




        if ($_POST['usertype'] == 7) {

            $locations = $_POST['location'];
            $i = 0;
            $j = 0;
            foreach ($locations as $location) {

                //print_r($location);
                //echo $entry-1;;
                //die;
                $entry = count($location);
                for ($i = 1; $i <= $entry - 1; $i++) {

                    $insert['userid'] = $last_insertId;
                    $insert['locationid'] = $location[0];
                    $insert['roleid'] = $location[$i];

                    //print_r($insert);

                    $this->location_model->assignLocations($insert);
                    $j++;
                }
            }
        }

        $link = base_url() . 'businessUser/createUsername/' . $rand;

        $url = '<a style="color:rgb(43,170,223)" href="' . $link . '" target="_blank">log in here</a>';


        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('business_user_signup');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;

        //// replace strings from message
        $messages = str_replace('{BusinessUserFirstName}', ucfirst($businessUserFirstName), $messages);
        $messages = str_replace('{MasterAdminName}', $masterAdminName, $messages);
        $messages = str_replace('{BusinessUserEmail}', $businessUserEmail, $messages);
        $messages = str_replace('{createUsername}', $url, $messages);

        //// FROM EMAIL
        $this->email->from($email_template->from_email, 'Hurree');
        $this->email->to($businessUserEmail);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND

        echo 1;
    }

    function emailConfig() {

        $this->load->library('email');   //// LOAD LIBRARY

        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://email-smtp.eu-west-1.amazonaws.com';//auth.smtp.1and1.co.uk
        $config['smtp_port'] = 465;
        $config['smtp_user'] = 'AKIAJUJGM2OYDQR4TSWA';//support@hurree.co.uk
        $config['smtp_pass'] = 'AkINVk1QbB5FLbvbu43cduRlx4be3zFGmvMqmu99Aw2t';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html

        $this->email->initialize($config);
    }

    function editUserPopUp($userid) {
        $data['user'] = $this->user_model->getOneUser($userid);

        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;

        $location['businessId'] = $businessId;
        $location['isDelete'] = 0;
        $location['active'] = 1;
        $data['locations'] = $this->location_model->getLocations($location);

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);

        $data['Userlocations'] = $this->location_model->getUserLocations($userid);

        $this->load->helper('permission');

        $this->load->view('edit_user', $data);
    }

    function editUser() {

        $login = $this->administrator_model->front_login_session();
        $modifiedBy = $login->user_id;

        $save = array();
        $save['user_Id'] = $_POST['userid'];
        $save['modifiedBy'] = $modifiedBy;
        $save['firstname'] = $_POST['firstname'];
        $save['lastname'] = $_POST['lastname'];
        $save['email'] = $_POST['email'];
        $save['usertype'] = $_POST['usertype'];
        $save['modifiedDate'] = date('YmdHis');

        $last_insertId = $this->user_model->insertsignup($save);

        //Delete previous locations and permissions
        $this->location_model->deleteLocations($_POST['userid']);

        if ($_POST['usertype'] == 7) {

            $locations = $_POST['location'];
            $i = 0;
            $j = 0;
            foreach ($locations as $location) {

                $entry = count($location);
                for ($i = 1; $i <= $entry - 1; $i++) {

                    $insert['userid'] = $last_insertId;
                    $insert['locationid'] = $location[0];
                    $insert['roleid'] = $location[$i];

                    //print_r($insert);

                    $this->location_model->assignLocations($insert);
                    $j++;
                }
            }
        }

        echo 1;
    }

    function createUsername() {

        $usernameToken = $this->uri->segment(3);
        $data['username_create_token'] = $usernameToken;

        $arr_user['username_create_token'] = $usernameToken;
        $arr_user['active'] = 1;
        $user = $this->user_model->getOneUserDetails($arr_user, '*');

        if (count($user) > 0) {
        	  $_SESSION['hurree-business'] = 'access';
            $this->load->view('create_username', $data);
        } else {
            redirect(base_url());
        }
    }

    function saveUsername() {

        $arr_user['username_create_token'] = $this->input->post('username_create_token');
        $arr_user['active'] = 1;
        $user = $this->user_model->getOneUserDetails($arr_user, '*');

        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $update = array(
            'user_Id' => $user->user_Id,
            'username' => $username,
            'password' => md5($password),
            'username_create_token' => ''
        );
        $this->user_model->reset_password($update);

        $session_details = $this->user_model->check_username($username, $password);
        $where['userid'] = $session_details->user_Id;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);
        if (count($loginStatus) == 0) {
        	$this->session->set_userdata('logged_in', $session_details);
        	$sess = $this->session->userdata('logged_in');
        }
        //unset($_SESSION['hurree-business']);
        redirect('account');
    }

    function RandomStringCreateUsername() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    function demo() {

        require_once APPPATH . 'third_party/vendor/PHP-Curler/Curler.class.php';
        require_once APPPATH . 'third_party/vendor/PHP-MetaParser/MetaParser.class.php';

        // curling
        $curler = (new Curler());
        $url = 'http://toi.in/whW92a';
        $body = $curler->get($url);
        $parser = (new MetaParser($body, $url));
        echo '<pre>';
        print_r($parser->getDetails());
    }

    function deleteUserPopUp($userid) {

        $data['userid'] = $userid;
        $this->load->view('deleteuser', $data);
    }

    function deleteUser() {
        $login = $this->administrator_model->front_login_session();
        $modifiedBy = $login->user_id;

        $delete['user_Id'] = $_POST['userid'];
        $delete['active'] = 0;
        $delete['isDelete'] = 1;
        $delete['modifiedBy'] = $modifiedBy;
        $save['modifiedDate'] = date('YmdHis');

        $this->user_model->insertsignup($delete);
        echo 1;
    }

    function rewardPerformance($rewardId) {

        $data['reward'] = $this->reward_model->getRewardPerformance($rewardId);
        $this->load->view('reward_performance', $data);
    }

    function rewardLocations($rewardId) {
        $getLocations = $this->reward_model->getRewardLocations($rewardId);

        foreach ($getLocations as $getLocation) {
            $locations[] = $getLocation->locationId;
        }

        $data['campaignlocations'] = $this->campaign_model->getCampaignLocation($locations);

        $this->load->view('campaign_location', $data);
    }
    /* function added by sarvesh for calender */
    public function calender(){

      $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'account';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $data['userDetails'] = $this->user_model->getOneUser($login->user_id);
            $data['masterData'] = $this->user_model->getMasterUserData($login->businessId);

            $data['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $data['businessName'] = $login->businessName;

            $this->load->view('inner_header3.0', $header);
            $this->load->view('calender', $data);
            $this->load->view('inner_footer3.0');
        }else{
      	  redirect(base_url());
        }
    }

    function locationPageSearch(){
           $keyword = trim($_POST['keyword']);
           $login = $this->administrator_model->front_login_session();

           $data['businessUser'] = $this->user_model->getAllBranchesForSearch($login->businessId, $keyword);
           echo $this->load->view('locationPageSearch',$data);

    }

    function sendmail(){
              $this->load->library('email');
               $config['mailtype']     = 'html';
               $config['useragent']    = 'Post Title';
               $config['protocol']     = 'smtp';
               $config['smtp_host']    = 'tls://email-smtp.eu-west-1.amazonaws.com';
               $config['smtp_user']    = 'AKIAJUJGM2OYDQR4TSWA';
               $config['smtp_pass']    = 'AkINVk1QbB5FLbvbu43cduRlx4be3zFGmvMqmu99Aw2t';
               $config['smtp_port']    = '465';
               $config['wordwrap']     = TRUE;
               $config['newline']      = "\r\n";

               $this->email->initialize($config);
               $to = 'sarveshp09@gmail.com';
               $subject='test';
               $message = 'test mail';
               $this->email->from('sarvesh@qsstechnosoft.com', 'hurree');
               $this->email->to($to);
               $this->email->subject($subject);
               $this->email->message($message);


               if($this->email->send()):
                       echo 'success mail';
               else:
                    echo    $this->email->print_debugger();
               endif;
          exit;

    }

    function testSQS(){
      $this->load->library('aws_sdk');
      $aws_sdk = new Aws_sdk();

          // Create the queue
          $queue_options = array(
              'QueueName' => 'our_queue'
          );
          $aws_sdk->createQueue($queue_options);
          echo '<pre>';
          print_r(  $aws_sdk );

           exit;
      try {
            $sqs_credentials = array(
                'region' => '[[YOUR_AWS_REGION]]',
                'version' => 'latest',
                'credentials' => array(
                    'key'    => '[[YOUR_AWS_ACCESS_KEY_ID]]',
                    'secret' => '[[YOUR_AWS_SECRET_ACCESS_KEY]]',
                )
            );

            // Instantiate the client
            $sqs_client = new SqsClient($sqs_credentials);

            // Create the queue
            $queue_options = array(
                'QueueName' => 'our_queue'
            );
            $sqs_client->createQueue($queue_options);
        } catch (Exception $e) {
            die('Error creating new queue ' . $e->getMessage());
        }

    }


}
