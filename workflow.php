<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Workflow extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model(array('workflow_model', 'inapp_model', 'administrator_model', 'contact_model', 'permission_model', 'groupapp_model', 'brand_model', 'country_model', 'lists_model'));
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active == 0) {
            redirect(base_url());
        }
    }

    function getFirstName() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getFirstName($businessId);
    }

    function getLastName() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getLastName($businessId);
    }

    function getTimeZone() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getTimeZone($businessId);
    }

    function getPersonas() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getPersonas($businessId);
    }

    function getMembersList() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getMembersList($businessId);
    }

    function getCampaignEmails() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getCampaignEmails($businessId);
    }
    function getCampainInterection() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $this->workflow_model->getCampainInterection($businessId);
    }

    function getAppsPages() {
        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;
        $allGroups = $this->groupapp_model->getGroups($businessId);
        $groupIds = array();
        $appIds = array();
        if (count($allGroups) > 0) {
            foreach ($allGroups as $group) {
                $groupIds[] = $group->app_group_id;
            }
        }
        if (count($groupIds) > 0) {
            $allApps = $this->groupapp_model->getAllAppsByGroupIds($groupIds);
            if (count($allApps) > 0) {
                foreach ($allApps as $app) {
                    $appIds[] = $app->app_group_apps_id;
                }
            }
        }

        if (count($appIds) > 0) {
            $this->workflow_model->getAppsPages($appIds);
        }
    }

    /* public function getCampaignsOptions() {
      $login = $this->administrator_model->front_login_session();
      $campaignArr = array();
      if (isset($login->active) && $login->active != 0) {
      $businessId = $login->businessId;
      if (isset($_GET['type'])) {
      $campaignType = $_GET['type'];
      print_r($_POST);
      if ($campaignType == "push") {
      $campaigns = $this->brand_model->getPushCampaignsByBusinessId($businessId);
      if (count($campaigns) > 0) {
      foreach ($campaigns as $campaign) {
      $campaignArr[] = array('id' => $campaign->id, 'value' => $campaign->campaignName);
      }
      }
      } else if ($campaignType == "email") {
      $campaigns = $this->brand_model->getEmailCampaignsByBusinessId($businessId);
      if (count($campaigns) > 0) {
      foreach ($campaigns as $campaign) {
      $campaignArr[] = array('id' => $campaign->id, 'value' => $campaign->campaignName);
      }
      }
      }

      $response = array(
      "status" => $success,
      "statusMessage" => $statusMessage,
      "items" => $campaignArr
      );
      }

      echo json_encode($response);
      exit();
      }
      } */

    public function index() {
        $login = $this->administrator_model->front_login_session();
        $data['contactsFirstName'] = array();
        $data['contactsLastName'] = array();
        $data['personaUsers'] = array();
        $data['emailCampaigns'] = array();
        $data['appsPages'] = array();
        $data['androidPushCampaigns'] = array();
        $data['iosPushCampaigns'] = array();
        $data['inAppMessaging'] = array();

        if (isset($login->active) && $login->active != 0) {
            $businessId = $login->businessId;
            $header['page'] = 'contact';
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

            /* $data['contactsFirstName'] = $this->workflow_model->getAllContactsFirstNameByBusinessId($businessId);
              $data['contactsLastName'] = $this->workflow_model->getAllContactsLastNameByBusinessId($businessId); */
            $data['personaUsers'] = $this->brand_model->getAllPersonasByBusinessId($businessId);
            $data['emailCampaigns'] = $this->brand_model->getEmailCampaignsByBusinessId($businessId);
            $pushCampaigns = $this->brand_model->getPushCampaignsByBusinessId($businessId); //print_r($pushCampaigns);exit;
            $androidPushCampaigns = array();
            $iosPushCampaigns = array();
            if (count($pushCampaigns) > 0) {
                foreach ($pushCampaigns as $campaign) {
                    if ($campaign->platform == 'iOS') {
                        $iosPushCampaigns[] = array('campaign_id' => $campaign->id, "campaignName" => $campaign->campaignName);
                    } else if ($campaign->platform == 'android') {
                        $androidPushCampaigns[] = array('campaign_id' => $campaign->id, "campaignName" => $campaign->campaignName);
                    }
                }
            }
            $allGroups = $this->groupapp_model->getGroups($businessId);
            $groupIds = array();
            $appIds = array();
            if (count($allGroups) > 0) {
                foreach ($allGroups as $group) {
                    $groupIds[] = $group->app_group_id;
                }
            }
            if (count($groupIds) > 0) {
                $allApps = $this->groupapp_model->getAllAppsByGroupIds($groupIds);
                if (count($allApps) > 0) {
                    foreach ($allApps as $app) {
                        $appIds[] = $app->app_group_apps_id;
                    }
                }
            }

            /* if (count($appIds) > 0) {
              $data['appsPages'] = $this->workflow_model->getAllAppScreenByAppGroups($appIds);
              } */
            $inAppMessaging = '';
            $inAppMessages = $this->inapp_model->getAllInAppMessaging($businessId);
            if (count($inAppMessages) > 0) {
                foreach ($inAppMessages as $inapp) {
                    $inAppMessaging[] = array('inapp_id' => $inapp->id, "campaignName" => $inapp->campaignName);
                }
            }
            $data['androidPushCampaigns'] = $androidPushCampaigns;
            $data['iosPushCampaigns'] = $iosPushCampaigns;
            $data['inAppMessaging'] = $inAppMessaging;
            /*  $data['countries'] = $this->country_model->getTimezones();
              $data['lists'] = $this->lists_model->getAllListsOfBusinessId($login->businessId); */
            $data['workflows'] = $this->workflow_model->getAllWorkflows($login->businessId);
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/workflow_view', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function getCampaigns() {
        $login = $this->administrator_model->front_login_session();
        $campaignArr = array();
        if (isset($login->active) && $login->active != 0) {
            $businessId = $login->businessId;
            if (isset($_POST['type'])) {
                $campaignType = $_POST['type'];
                if ($campaignType == "push") {
                    $campaigns = $this->brand_model->getPushCampaignsByBusinessId($businessId);
                    if (count($campaigns) > 0) {
                        foreach ($campaigns as $campaign) {
                            $campaignArr[] = array('campaign_id' => $campaign->id, 'campaignName' => $campaign->campaignName);
                        }
                    }
                } else if ($campaignType == "email") {
                    $campaigns = $this->brand_model->getEmailCampaignsByBusinessId($businessId);
                    if (count($campaigns) > 0) {
                        foreach ($campaigns as $campaign) {
                            $campaignArr[] = array('campaign_id' => $campaign->id, 'campaignName' => $campaign->campaignName);
                        }
                    }
                }
                $success = 'success';
                $statusMessage = "List of $campaignType Campaigns exists.";

                $response = array(
                    "status" => $success,
                    "statusMessage" => $statusMessage,
                    "campaigns" => $campaignArr
                );
            } else {
                $success = 'error';
                $statusMessage = 'Server busy. Please try again.';

                $response = array(
                    "status" => $success,
                    "statusMessage" => $statusMessage
                );
            }

            echo json_encode($response);
            exit();
        } else {
            $success = 'error';
            $statusMessage = 'Session expired. Please login again.';

            $response = array(
                "status" => $success,
                "statusMessage" => $statusMessage
            );

            echo json_encode($response);
        }
    }

    public function liveWorkflow() {
        $login = $this->administrator_model->front_login_session();
        $workflow = $_POST['workflow']; //print_r($_POST['workflow']); exit;
        $data = array();
        if (isset($workflow)) {
            $data = array_merge($data, array('wrkflow_businessID' => $login->businessId));
            if (array_key_exists('workflow_title', $workflow)) {
                $title = $workflow['workflow_title'];
                $data = array_merge($data, array('wrkflow_title' => $title));
            }
            $data = array_merge($data, array('wrkflow_triggerpoint_json' => json_encode($workflow)));

            if ($workflow['type'] == '') {
                $data = array_merge($data, array('isDraft' => 0, 'isActive' => 1, 'createdDate' => date('Y-m-d H:i:s')));
            } else {
                $data = array_merge($data, array('isDraft' => 1, 'isActive' => 0, 'createdDate' => date('Y-m-d H:i:s')));
            }

            if (array_key_exists('existUserChk', $workflow)) {
              //  if($workflow[])
                $data = array_merge($data, array('checkEnrollUsers' => $workflow['existUserChk']));
            }

            if (array_key_exists('workflow_id', $workflow)) {
                $id = $workflow['workflow_id'];
                $data = array_merge($data, array('wrkflow_id' => $id)); //print_r($data); exit;
                $this->workflow_model->deleteTimeDelayPre($id);
            }
            $wrkflow_send_date = date('Y-m-d H:i:s');
            $insertId = $this->workflow_model->saveWorkflow($data);
            if (array_key_exists('timeDelay', $workflow)) {
                $timeDelayArr = $workflow['timeDelay'];
                $dateFlag = 1;
                foreach ($timeDelayArr as $key => $timeDelay) {
                    $json_arr = json_decode($timeDelay);

                    if ($dateFlag == 1) {
                        $start = date('Y-m-d H:i:s');
                    }

                    //print_r($timeDelay);	 //print_r( $json_arr); exit;
                    if ($json_arr->timeDelayType == "Designated-Time") {


                        $wrkflow_send_date = date('Y-m-d H:i:s', strtotime("+$json_arr->designatedDays day +$json_arr->designatedHours hour +$json_arr->designatedMinutes minutes", strtotime($wrkflow_send_date)));
                        //echo $wrkflow_send_date; exit;
                        $data1 = array('wrkflow_id' => $insertId,
                            'wrkflow_TimeDelayType' => $json_arr->timeDelayType,
                            'wrkflow_designatedMinutes' => $json_arr->designatedMinutes,
                            'wrkflow_designatedHours' => $json_arr->designatedHours,
                            'wrkflow_designatedDays' => $json_arr->designatedDays,
                            'wrkflow_notificationType' => $json_arr->notificationType,
                            'wrkflow_iOSNotification' => !empty($json_arr->iOSNotification) ? $json_arr->iOSNotificationId : '',
                            'wrkflow_androidNotification' => !empty($json_arr->androidNotification) ? $json_arr->androidNotificationId : '',
                            'wrkflow_emailNotification' => $json_arr->emailNotificationId,
                            'wrkflow_personaNotification' => $json_arr->personaNotificationId,
                            'wrkflow_inAppNotification' => $json_arr->inAppNotificationId,
                            'wrkflow_send_date' => $wrkflow_send_date,
                            'createdDate' => date('Y-m-d H:i:s'));
                    } else if ($json_arr->timeDelayType == "Intelligent-delivery") {
                        $wrkflow_send_date = date('Y-m-d H:i:s', strtotime("+$json_arr->IntelligentDeliveryDays day +$json_arr->IntelligentDeliveryHours hour +$json_arr->IntelligentDeliveryMinutes minutes", strtotime($wrkflow_send_date)));
                        $data1 = array('wrkflow_id' => $insertId,
                            'wrkflow_TimeDelayType' => $json_arr->timeDelayType,
                            'wrkflow_intelligentDeliveryMinutes' => $json_arr->IntelligentDeliveryMinutes,
                            'wrkflow_intelligentDeliveryHours' => $json_arr->IntelligentDeliveryHours,
                            'wrkflow_intelligentDeliveryDays' => $json_arr->IntelligentDeliveryDays,
                            'wrkflow_intelligentDeliveryBetweenTime' => $json_arr->InteDelBetweenCheck,
                            'wrkflow_intelligentDeliveryfrMinutes' => $json_arr->IntDelBetweenfromMinutes,
                            'wrkflow_intelligentDeliverytoMinutes' => $json_arr->IntDelBetweentoMinutes,
                            'wrkflow_intelligentDeliveryReEligible' => $json_arr->InteDelReEligibleTimeCheckbox,
                            'wrkflow_ReEligibleDate' => $json_arr->InteDelReEligibleDate,
                            'wrkflow_ReEligibleDays' => $json_arr->InteDelReEligibleDays,
                            'wrkflow_notificationType' => $json_arr->notificationType,
                            'wrkflow_iOSNotification' => !empty($json_arr->iOSNotification) ? $json_arr->iOSNotificationId : '',
                            'wrkflow_androidNotification' => !empty($json_arr->androidNotification) ? $json_arr->androidNotificationId : '',
                            'wrkflow_emailNotification' => $json_arr->emailNotificationId,
                            'wrkflow_personaNotification' => $json_arr->personaNotificationId,
                            'wrkflow_inAppNotification' => $json_arr->inAppNotificationId,
                            'wrkflow_send_date' => $wrkflow_send_date,
                            'createdDate' => date('Y-m-d H:i:s')
                        );
                    }
                    if ($dateFlag != 1) {
                        $start = $wrkflow_send_date;
                    }
                    if (!empty($json_arr->timeDelay_workflow_id)) {
                        $data1 = array_merge($data1, array('timeDelay_workflow_id' => $json_arr->timeDelay_workflow_id));
                    }
                    $timeDelayInsertId = $this->workflow_model->saveWorkflowTimedelay($data1);
                    $dateFlag ++;
                }
                if (array_key_exists('workflow_id', $workflow)) {
                    if($workflow['workflow_id'] == '')
                        $this->saveWorkflowHistory($insertId);
                    else
                        $this->reLaunchWorkflow($insertId);
                }
            }
            $success = 'success';
            $statusMessage = 'Workflow Added successfully.';

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage,
                )
            );
            echo json_encode($response);
            exit();
        }
        //print_r($_POST); exit;
    }

    public function performance($workflowId = false) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
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

            if (empty($workflowId)) {
                redirect(base_url() . 'appUser');
            }
            $workflowRow = $this->workflow_model->getWorkflowByWorkflowId($workflowId);
            if (count($workflowRow) == 0) {
                redirect(base_url() . 'appUser');
            }
            $data['workflowName'] = $workflowRow->wrkflow_title;

            $data['group'] = $this->groupapp_model->getOneGroup($groupId);
            $data['groupId'] = $groupId;
            $data['workflowId'] = $workflowId;
            $data['user'] = $header['loggedInUser'];

            $workflowTimeDelayRow = $this->workflow_model->getWorkflowTypesByWorkflowId($workflowId);
            if (count($workflowTimeDelayRow) > 0) {
                $workflow_types = explode(',', $workflowTimeDelayRow->workflow_types);
                if (in_array('email', $workflow_types)) {
                    $countSendCampaigns = $this->workflow_model->countEmailCampaignSendHistoryByWorkflowId($workflowId);
                    if (count($countSendCampaigns) > 0) {
                        $countSendCampaigns = count($countSendCampaigns);
                    } else {
                        $countSendCampaigns = 0;
                    }

                    $sendCampaigns = $this->workflow_model->getEmailCampaignSendHistoryByWorkflowId($workflowId);
                    $send_time_arr = array();
                    $send_users_arr = array();
                    if (count($sendCampaigns) > 0) {
                        foreach ($sendCampaigns as $user) {
                            $sendTime = date('Y-m-d', strtotime($user->emailSentOn));
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
                }

                if (in_array('push', $workflow_types)) {
                    $countSendCampaigns = $this->workflow_model->countCampaignSendHistoryByWorkflowId($workflowId); ///print_r($countSendCampaigns); exit;
                    if (count($countSendCampaigns) > 0) {
                        $countSendCampaigns = count($countSendCampaigns);
                    } else {
                        $countSendCampaigns = 0;
                    }

                    $sendCampaigns = $this->workflow_model->getCampaignSendHistoryByWorkflowId($workflowId); //print_r($ssendCampaigns); exit;
                    $send_time_arr = array();
                    $send_users_arr = array();
                    if (count($sendCampaigns) > 0) {
                        foreach ($sendCampaigns as $user) {
                            $sendTime = date('Y-m-d', strtotime($user->notification_timezone_send_time)); //echo  $createdDate;
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
                }

                if (in_array('email', $workflow_types)) {
                    $countViewCampaigns = $this->workflow_model->countEmailCampaignViewHistoryByWorkflowId($workflowId);
                    if (count($countViewCampaigns) > 0) {
                        $countViewCampaigns = count($countViewCampaigns);
                    } else {
                        $countViewCampaigns = 0;
                    }

                    $viewCampaigns = $this->workflow_model->getEmailCampaignViewHistoryByWorkflowId($workflowId);
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
                                array_push($view_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                            }
                        }
                        $view_users_arr = implode(',', $view_users_arr);
                    }
                }

                if (in_array('push', $workflow_types)) {
                    $countViewCampaigns = $this->workflow_model->countCampaignViewHistoryByWorkflowId($workflowId);
                    if (count($countViewCampaigns) > 0) {
                        $countViewCampaigns = count($countViewCampaigns);
                    } else {
                        $countViewCampaigns = 0;
                    }

                    $viewCampaigns = $this->workflow_model->getCampaignViewHistoryByWorkflowId($workflowId);
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
                                array_push($view_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                            }
                        }
                        $view_users_arr = implode(',', $view_users_arr);
                    }
                }
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

            $data['workflows'] = $workflowRow;
            $data['sendCampaigns'] = $send_users_arr;
            $data['viewCampaigns'] = $view_users_arr;
            $data['countSendCampaigns'] = $countSendCampaigns;
            $data['countViewCampaigns'] = $countViewCampaigns;
            $data['highChartScript'] = TRUE;
            $data['highChartScriptAjax'] = TRUE;
            $data['currentWorkflow'] = $workflowId;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/workflowPerformance', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function workflowlist($appGroupId = false) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'campaignsList';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $country_id = '2';
            $timezone = '';
            if (!empty($header['loggedInUser']->country)) {
                $country_id = $header['loggedInUser']->country;
                $row = $this->country_model->getTimezonebyCountryId($country_id);
                $timezone = $row->timezone;
                //echo $row->timezone; exit;
            }
            $data['userTimezone'] = $timezone;
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

            //$groups = $this->groupapp_model->getUserGroupData($groupArray);
            if (count($header['groups']) > 0) {
                $groupId = $header['groups'][0]->app_group_id;
            } else {
                $groupId = '';
            }

            $data['group'] = $this->groupapp_model->getOneGroup($groupId);
            $data['groupId'] = $groupId;

            $data['user'] = $header['loggedInUser'];

            $businessId = $login->businessId;

            $workflows = $this->workflow_model->getAllWorkflows($businessId);
            $data['workflows'] = $workflows;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/workflowList', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function editWorkflow() {
        $workflow_id = $this->uri->segment(3);
        if ($this->workflow_model->checkworkflow($workflow_id)) {
            redirect("workflow");
        }
        $login = $this->administrator_model->front_login_session();
        $data['contactsFirstName'] = array();
        $data['contactsLastName'] = array();
        $data['personaUsers'] = array();
        $data['emailCampaigns'] = array();
        $data['appsPages'] = array();
        $data['androidPushCampaigns'] = array();
        $data['iosPushCampaigns'] = array();
        $data['workflow'] = array();

        if (isset($login->active) && $login->active != 0) {
            $businessId = $login->businessId;
            $header['page'] = 'contact';
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
            $data['workflow_id'] = $workflow_id;
            $workflowRow = $this->workflow_model->getWorkflowByWorkflowId($workflow_id);
            $workflowTimeDelayRows = $this->workflow_model->getTimeDelayRowsByWorkflowId($workflow_id);
            if (count($workflowRow) > 0) {
                $data['workflow'] = $workflowRow;
            }
            if (count($workflowTimeDelayRows) > 0) {
                $data['workflowTimeDelay'] = $workflowTimeDelayRows;
                $data['workflowTimeDelayJson'] = json_encode($workflowTimeDelayRows);
            }
            $data['personaUsers'] = $this->brand_model->getAllPersonasByBusinessId($businessId);
            $data['emailCampaigns'] = $this->brand_model->getEmailCampaignsByBusinessId($businessId);
            $pushCampaigns = $this->brand_model->getPushCampaignsByBusinessId($businessId); //print_r($pushCampaigns);exit;
            $androidPushCampaigns = array();
            $iosPushCampaigns = array();
            if (count($pushCampaigns) > 0) {
                foreach ($pushCampaigns as $campaign) {
                    if ($campaign->platform == 'iOS') {
                        $iosPushCampaigns[] = array('campaign_id' => $campaign->id, "campaignName" => $campaign->campaignName);
                    } else if ($campaign->platform == 'android') {
                        $androidPushCampaigns[] = array('campaign_id' => $campaign->id, "campaignName" => $campaign->campaignName);
                    }
                }
            }
            $allGroups = $this->groupapp_model->getGroups($businessId);
            $groupIds = array();
            $appIds = array();
            if (count($allGroups) > 0) {
                foreach ($allGroups as $group) {
                    $groupIds[] = $group->app_group_id;
                }
            }
            if (count($groupIds) > 0) {
                $allApps = $this->groupapp_model->getAllAppsByGroupIds($groupIds);
                if (count($allApps) > 0) {
                    foreach ($allApps as $app) {
                        $appIds[] = $app->app_group_apps_id;
                    }
                }
            }

            if (count($appIds) > 0) {
                $data['appsPages'] = $this->workflow_model->getAllAppScreenByAppGroups($appIds);
            }
            $inAppMessaging = '';
            $inAppMessages = $this->inapp_model->getAllInAppMessaging($businessId);
            if (count($inAppMessages) > 0) {
                foreach ($inAppMessages as $inapp) {
                    $inAppMessaging[] = array('inapp_id' => $inapp->id, "campaignName" => $inapp->campaignName);
                }
            }
            $data['androidPushCampaigns'] = $androidPushCampaigns;
            $data['iosPushCampaigns'] = $iosPushCampaigns;
            $data['inAppMessaging'] = $inAppMessaging;
            $data['allWorkflows'] = $this->workflow_model->getAllWorkflows($businessId);
            $data['listUsers'] = $this->lists_model->getAllListsOfBusinessId($login->businessId);


            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/edit_workflow_view', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function deleteWorkflwoLevelPopUp($levelId) {
        $data['levelId'] = $levelId;
        $this->load->view('3.1/delete_workflowlevel', $data);
    }


    function deleteWorkflowPopUp($wrkflow_id) {
        $data['wrkflow_id'] = $wrkflow_id;
        $this->load->view('3.1/delete_workflow', $data);
    }

    function deleteWorkflow() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;
        $wrkflow_id = $_POST['wrkflow_id'];

        $update['wrkflow_id'] = $wrkflow_id;
        $update['isDelete'] = 1;
        $id = $this->workflow_model->updateWorkflow($update);
        echo $id;
    }

    function saveTriggerPointAsDraft() {
        $login = $this->administrator_model->front_login_session();
        $workflow = $_POST['workflow']; //print_r($_POST['workflow']); die;
        $data = array();
        if (isset($workflow)) {
            $data = array_merge($data, array('wrkflow_businessID' => $login->businessId));

            if (array_key_exists('workflow_title', $workflow)) {
                $title = $workflow['workflow_title'];
                $data = array_merge($data, array('wrkflow_title' => $title));
            }
            $data = array_merge($data, array('wrkflow_triggerpoint_json' => json_encode($workflow)));
            if (array_key_exists('type', $workflow)) {
                $mode = json_decode($workflow['type']);
                $data = array_merge($data, array('isDraft' => 1, 'isActive' => 0, 'createdDate' => date('Y-m-d H:i:s')));
            } else {
                $data = array_merge($data, array('isDraft' => 0, 'isActive' => 1, 'createdDate' => date('Y-m-d H:i:s')));
            }

            if (array_key_exists('existUserChk', $workflow)) {
                //  if($workflow[])
                $data = array_merge($data, array('checkEnrollUsers' => $workflow['existUserChk']));
            }

            if (array_key_exists('workflow_id', $workflow)) {
                $id = $workflow['workflow_id'];
                $data = array_merge($data, array('wrkflow_id' => $id)); //print_r($data); exit;
            }

           // print_r($data); die;

            $insertId = $this->workflow_model->saveWorkflowDraft($data);
            //echo $this->db->last_query(); die;

            $success = 'success';
            $statusMessage = 'Workflow Added successfully.';

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage,
                )
            );
            echo json_encode($response);
            exit();
        }
    }

    function saveTimeDelayAsDraft() {
        $login = $this->administrator_model->front_login_session();
        $workflow = $_POST['workflow']; //print_r($_POST['workflow']); die;
        $data = array();
        if (isset($workflow)) {
            $data = array_merge($data, array('wrkflow_businessID' => $login->businessId));
            if (array_key_exists('workflow_title', $workflow)) {
                $title = $workflow['workflow_title'];
                $data = array_merge($data, array('wrkflow_title' => $title));
            }
            $data = array_merge($data, array('wrkflow_triggerpoint_json' => json_encode($workflow)));

            if (array_key_exists('type', $workflow)) {
                $mode = json_decode($workflow['type']);
                $data = array_merge($data, array('isDraft' => 1, 'isActive' => 0, 'createdDate' => date('Y-m-d H:i:s')));
            } else {
                $data = array_merge($data, array('isDraft' => 0, 'isActive' => 1, 'createdDate' => date('Y-m-d H:i:s')));
            }

            if (array_key_exists('existUserChk', $workflow)) {
                //  if($workflow[])
                $data = array_merge($data, array('checkEnrollUsers' => $workflow['existUserChk']));
            }

            if (array_key_exists('workflow_id', $workflow)) {
                $id = $workflow['workflow_id'];
                $data = array_merge($data, array('wrkflow_id' => $id)); //print_r($data); exit;
            }

            //print_r($data); die;

            $insertId = $this->workflow_model->saveWorkflowDraft($data);
            //echo $this->db->last_query(); die;

            if (array_key_exists('timeDelay', $workflow)) {
                $timeDelayArr = $workflow['timeDelay'];
                $wrkflow_send_date = date('Y-m-d H:i:s');
                foreach ($timeDelayArr as $key => $timeDelay) {
                    $json_arr = json_decode($timeDelay);
                    //print_r($timeDelay);	 //print_r( $json_arr); exit;
                    if ($json_arr->timeDelayType == "Designated-Time") {
                        //$start = date('Y-m-d H:i:s');
                        $wrkflow_send_date = date('Y-m-d H:i:s', strtotime("+$json_arr->designatedDays day +$json_arr->designatedHours hour +$json_arr->designatedMinutes minutes", strtotime($wrkflow_send_date)));
                        //echo $wrkflow_send_date; exit;
                        $data1 = array('wrkflow_id' => $insertId,
                            'wrkflow_TimeDelayType' => $json_arr->timeDelayType,
                            'wrkflow_designatedMinutes' => $json_arr->designatedMinutes,
                            'wrkflow_designatedHours' => $json_arr->designatedHours,
                            'wrkflow_designatedDays' => $json_arr->designatedDays,
                            'wrkflow_notificationType' => $json_arr->notificationType,
                            'wrkflow_iOSNotification' => $json_arr->iOSNotificationId,
                            'wrkflow_androidNotification' => $json_arr->androidNotificationId,
                            'wrkflow_emailNotification' => $json_arr->emailNotificationId,
                            'wrkflow_personaNotification' => $json_arr->personaNotificationId,
                            'wrkflow_inAppNotification' => $json_arr->inAppNotificationId,
                            'wrkflow_send_date' => $wrkflow_send_date,
                            'createdDate' => date('Y-m-d H:i:s'));
                    } else if ($json_arr->timeDelayType == "Intelligent-delivery") {
                        $wrkflow_send_date = date('Y-m-d H:i:s', strtotime("+$json_arr->IntelligentDeliveryDays day +$json_arr->IntelligentDeliveryHours hour +$json_arr->IntelligentDeliveryMinutes minutes", strtotime($wrkflow_send_date)));
                        $data1 = array('wrkflow_id' => $insertId,
                            'wrkflow_TimeDelayType' => $json_arr->timeDelayType,
                            'wrkflow_intelligentDeliveryMinutes' => $json_arr->IntelligentDeliveryMinutes,
                            'wrkflow_intelligentDeliveryHours' => $json_arr->IntelligentDeliveryHours,
                            'wrkflow_intelligentDeliveryDays' => $json_arr->IntelligentDeliveryDays,
                            'wrkflow_intelligentDeliveryBetweenTime' => $json_arr->InteDelBetweenCheck,
                            'wrkflow_intelligentDeliveryfrMinutes' => $json_arr->IntDelBetweenfromMinutes,
                            'wrkflow_intelligentDeliverytoMinutes' => $json_arr->IntDelBetweentoMinutes,
                            'wrkflow_intelligentDeliveryReEligible' => $json_arr->InteDelReEligibleTimeCheckbox,
                            'wrkflow_ReEligibleDate' => $json_arr->InteDelReEligibleDate,
                            'wrkflow_ReEligibleDays' => $json_arr->InteDelReEligibleDays,
                            'wrkflow_notificationType' => $json_arr->notificationType,
                            'wrkflow_iOSNotification' => $json_arr->iOSNotificationId,
                            'wrkflow_androidNotification' => $json_arr->androidNotificationId,
                            'wrkflow_emailNotification' => $json_arr->emailNotificationId,
                            'wrkflow_personaNotification' => $json_arr->personaNotificationId,
                            'wrkflow_inAppNotification' => $json_arr->inAppNotificationId,
                            'wrkflow_send_date' => $wrkflow_send_date,
                            'createdDate' => date('Y-m-d H:i:s')
                        );
                    }
                    if (!empty($json_arr->timeDelay_workflow_id)) {
                        $data1 = array_merge($data1, array('timeDelay_workflow_id' => $json_arr->timeDelay_workflow_id));
                    }
                    $timeDelayInsertId = $this->workflow_model->saveWorkflowTimedelay($data1);
                }
            }

            $success = 'success';
            $statusMessage = 'Workflow Added successfully.';

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage,
                )
            );
            echo json_encode($response);
            exit();
        }
    }

    function getWorkflowCount() {
        $chunkSize = 5;
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
        $lastOpenAppArr = array();
        $NumOfVisitUsers = array();
        $lastPageSeenUsers = array();
        $totalSendUsers = 0;
        $login = $this->administrator_model->front_login_session();
        $workflow = $_POST['workflow']; //print_r($_POST['workflow']); die;
        $data = array();
        $dataWorkflow = array();
        if (isset($workflow)) {
            $data = array_merge($data, array('businessId' => $login->businessId));
            if (array_key_exists('workflow_title', $workflow)) {
                $title = $workflow['workflow_title'];
                $data = array_merge($data, array('wrkflow_title' => $title));
            }
            if (array_key_exists('firstName', $workflow)) {
                $firstName = json_decode($workflow['firstname']);
                $data = array_merge($data, array('wrkflow_fname' => $firstName->firstNameArr, 'wrkflow_fnameInEx' => $firstName->fnameExcludeInc, 'wrkflow_fnameAndOr' => $firstName->fnameAndOr));
            }
            if (array_key_exists('lastname', $workflow)) {
                $lastName = json_decode($workflow['lastname']);
                $data = array_merge($data, array('wrkflow_lname' => $lastName->lastNameArr, 'wrkflow_lnameInEx' => $lastName->lnameExcludeInc, 'wrkflow_lnameAndOr' => $lastName->lnameAndOr));
            }
            if (array_key_exists('timezone', $workflow)) {
                $timezone = json_decode($workflow['timezone']);
                $data = array_merge($data, array('wrkflow_timezone' => $timezone->timezoneArr, 'wrkflow_timezoneInEx' => $timezone->timezoneExcludeInc, 'wrkflow_timezoneAndOr' => $timezone->timezoneAndOr));
            }
            if (array_key_exists('creationDate', $workflow)) {
                $creationDate = json_decode($workflow['creationDate']);
                $data = array_merge($data, array('wrkflow_creation_date' => $creationDate->creationDate, 'wrkflow_creationDateInEx' => $creationDate->creationDateExcludeInc, 'wrkflow_creationDateAndOr' => $creationDate->creationDateAndOr));
            }
            if (array_key_exists('interaction_type', $workflow)) {
                $interaction_type = json_decode($workflow['interaction_type']);
                $data = array_merge($data, array('wrkflow_interaction' => $interaction_type->interaction_type));
            }
            if (array_key_exists('interaction', $workflow)) {
                $interaction = json_decode($workflow['interaction']); // print_r(  $interaction); echo $interaction->interactionIds; exit;
                $interactionIds = rtrim($interaction->interactionIds, ',');
                $data = array_merge($data, array('wrkflow_interaction_campaigns' => $interaction->interactionIds, 'wrkflow_interCampaignInEx' => $interaction->interactionExcludeInc, 'wrkflow_interCampaignAndOr' => $interaction->interactionAndOr));
            }
            if (array_key_exists('originalSource', $workflow)) {
                $originalSource = json_decode($workflow['originalSource']);
                $data = array_merge($data, array('wrkflow_originalSource' => $originalSource->originalSourceArr, 'wrkflow_originalSourceInEx' => $originalSource->originalSourceExcludeInc, 'wrkflow_originalSourceAndOr' => $originalSource->originalSourceAndOr));
            }
            if (array_key_exists('list', $workflow)) {
                $persona = json_decode($workflow['list']);  // print_r($persona); exit;
                $listIds = rtrim($persona->listIds, ',');
                $data = array_merge($data, array('wrkflow_list' => $listIds, 'wrkflow_listInEx' => $persona->listExcludeInc, 'wrkflow_listAndOr' => $persona->listAndOr));
            }
            if (array_key_exists('persona', $workflow)) {
                $persona = json_decode($workflow['persona']);
                $personaIds = rtrim($persona->personaIds, ',');
                $data = array_merge($data, array('wrkflow_persona' => $personaIds, 'wrkflow_personaInEx' => $persona->personaExcludeInc, 'wrkflow_personaAndOr' => $persona->personaAndOr));
            }
            if (array_key_exists('receiverEmail', $workflow)) {
                $receiverEmail = json_decode($workflow['receiverEmail']);
                $receiverEmailIds = rtrim($receiverEmail->receiverEmailIds, ',');
                $data = array_merge($data, array('wrkflow_receiverEmail' => $receiverEmailIds, 'wrkflow_receiverEmailInEx' => $receiverEmail->receiverEmailExcludeInc, 'wrkflow_receiverEmailAndOr' => $receiverEmail->receiverEmailAndOr));
            }
            if (array_key_exists('openedEmail', $workflow)) {
                $openedEmail = json_decode($workflow['openedEmail']);
                $openedEmailIds = rtrim($openedEmail->openedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_openedEmail' => $openedEmailIds, 'wrkflow_openedEmailInEx' => $openedEmail->openedEmailExcludeInc, 'wrkflow_openedEmailAndOr' => $openedEmail->openedEmailAndOr));
            }
            if (array_key_exists('clickedEmail', $workflow)) {
                $clickedEmail = json_decode($workflow['clickedEmail']);
                $clickedEmailIds = rtrim($clickedEmail->clickedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_clickedEmail' => $clickedEmailIds, 'wrkflow_clickedEmailInEx' => $clickedEmail->clickedEmailExcludeInc, 'wrkflow_clickedEmailAndOr' => $clickedEmail->clickedEmailAndOr));
            }
            if (array_key_exists('unsubscribedEmail', $workflow)) {
                $unsubscribedEmail = json_decode($workflow['unsubscribedEmail']);
                $unsubscribedEmailIds = rtrim($unsubscribedEmail->unsubscribedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_unsubscribedEmail' => $unsubscribedEmailIds, 'wrkflow_unsubscribedEmailInEx' => $unsubscribedEmail->unsubscribedEmailExcludeInc, 'wrkflow_unsubscribedEmailAndOr' => $unsubscribedEmail->unsubscribedEmailAndOr));
            }
            if (array_key_exists('bouncedEmail', $workflow)) {
                $bouncedEmail = json_decode($workflow['bouncedEmail']);
                $bouncedEmailIds = rtrim($bouncedEmail->bouncedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_bouncedEmail' => $bouncedEmailIds, 'wrkflow_bouncedEmailInEx' => $bouncedEmail->bouncedEmailExcludeInc, 'wrkflow_bouncedEmailAndOr' => $bouncedEmail->bouncedEmailAndOr));
            }
            if (array_key_exists('notReceivedEmail', $workflow)) {  //print_r($workflow); exit;notReceivedEmail
                $notReceivedEmail = json_decode($workflow['notReceivedEmail']); //print_r($notReceivedEmail); exit;
                $notReceivedEmailIds = rtrim($notReceivedEmail->notReceivedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_NotReceivedEmail' => $notReceivedEmailIds, 'wrkflow_NotReceivedEmailInEx' => $notReceivedEmail->notReceivedEmailExcludeInc, 'wrkflow_NotReceivedEmailAndOr' => $notReceivedEmail->notReceivedEmailAndOr));
            }
            if (array_key_exists('notOpenedEmail', $workflow)) {
                $notOpenedEmail = json_decode($workflow['notOpenedEmail']);
                $notOpenedEmailIds = rtrim($notOpenedEmail->notOpenedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_NotOpenedEmail' => $notOpenedEmailIds, 'wrkflow_NotOpenedEmailInEx' => $notOpenedEmail->notOpenedEmailExcludeInc, 'wrkflow_NotOpenedEmailAndOr' => $notOpenedEmail->notOpenedEmailAndOr));
            }
            if (array_key_exists('notClickedEmail', $workflow)) {
                $notClickedEmail = json_decode($workflow['notClickedEmail']);
                $notClickedEmailIds = rtrim($notClickedEmail->notClickedEmailIds, ',');
                $data = array_merge($data, array('wrkflow_NotClickedEmail' => $notClickedEmailIds, 'wrkflow_NotClickedEmailInEx' => $notClickedEmail->notClickedEmailExcludeInc, 'wrkflow_NotClickedEmailAndOr' => $notClickedEmail->notClickedEmailAndOr));
            }
            if (array_key_exists('lastOpenDate', $workflow)) {
                $lastOpenDate = json_decode($workflow['lastOpenDate']);
                $data = array_merge($data, array('wrkflow_lastEmailOpenDate' => $lastOpenDate->lastOpenDate, 'wrkflow_lastEmailOpenDateInEx' => $lastOpenDate->lastOpenDateExcludeInc, 'wrkflow_lastEmailOpenDateAndOr' => $lastOpenDate->lastOpenDateAndOr));
            }
            if (array_key_exists('pageViewed', $workflow)) {
                $pageViewed = json_decode($workflow['pageViewed']);
                $data = array_merge($data, array('wrkflow_pageViewed' => $pageViewed->pageViewedArr, 'wrkflow_pageViewedInEx' => $pageViewed->pageViewedExcludeInc, 'wrkflow_pageViewedAndOr' => $pageViewed->pageViewedAndOr));
            }
            if (array_key_exists('NumofViews', $workflow)) {
                $averageView = json_decode($workflow['NumofViews']);
                $data = array_merge($data, array('wrkflow_NumOfViews' => $averageView->NumofViews, 'wrkflow_NumOfViewsInEx' => $averageView->NumOfViewsExcludeInc, 'wrkflow_NumOfViewsAndOr' => $averageView->NumOfViewsAndOr));
            }
            if (array_key_exists('lastOpenApp', $workflow)) {
                $lastOpenApp = json_decode($workflow['lastOpenApp']);
                $data = array_merge($data, array('wrkflow_lastOpenAppDate' => $lastOpenApp->lastOpenApp, 'wrkflow_lastOpenAppDateInEx' => $lastOpenApp->lastOpenAppExcludeInc, 'wrkflow_lastOpenAppDateAndOr' => $lastOpenApp->lastOpenAppAndOr));
            }
            if (array_key_exists('NumOfVisit', $workflow)) {
                $appVisit = json_decode($workflow['NumOfVisit']);
                $data = array_merge($data, array('wrkflow_NumOfVisit' => $appVisit->NumofVisit, 'wrkflow_NumOfVisitInEx' => $appVisit->NumOfVisitExcludeInc, 'wrkflow_NumOfVisitAndOr' => $appVisit->NumOfVisitAndOr));
            }
            if (array_key_exists('lastPageApp', $workflow)) {
                $lastPageApp = json_decode($workflow['lastPageApp']);
                $data = array_merge($data, array('wrkflow_lastPageSeen' => $lastPageApp->lastPageAppArr, 'wrkflow_lastPageSeenInEx' => $lastPageApp->lastPageAppExcludeInc, 'wrkflow_lastPageSeenAndOr' => $lastPageApp->lastPageAppAndOr));
            }
            if (array_key_exists('type', $workflow)) {
                $mode = json_decode($workflow['type']);
                $data = array_merge($data, array('isDraft' => 1, 'isActive' => 0, 'createdDate' => date('Y-m-d H:i:s')));
            } else {
                $data = array_merge($data, array('isDraft' => 0, 'isActive' => 1, 'createdDate' => date('Y-m-d H:i:s')));
            }
            if (array_key_exists('existUserChk', $workflow)) {
                $data = array_merge($data, array('wrkflow_existUsers' => $workflow['existUserChk']));
            }
            if (array_key_exists('newUserChk', $workflow)) {
                $data = array_merge($data, array('wrkflow_NewUsers' => $workflow['newUserChk']));
            }
            if (array_key_exists('workflow_id', $workflow)) {
                $id = $workflow['workflow_id'];
                $data = array_merge($data, array('wrkflow_id' => $id)); //print_r($data); exit;
            }
            if (array_key_exists('timeDelay', $workflow)) {
                $timeDelayArr = $workflow['timeDelay'];
                if (count($timeDelayArr) > 0) {
                    foreach ($timeDelayArr as $key => $timeDelay) {
                        $json_arr = json_decode($timeDelay);  //print_r($timeDelay);	 //print_r( $json_arr); exit;
                        if ($json_arr->timeDelayType == "Designated-Time") {
                            $start = date('Y-m-d H:i:s');
                            $wrkflow_send_date = date('Y-m-d H:i:s', strtotime("+$json_arr->designatedDays day +$json_arr->designatedHours hour +$json_arr->designatedMinutes minutes", strtotime($start)));

                            $data1 = array(
                                'wrkflow_TimeDelayType' => $json_arr->timeDelayType,
                                'wrkflow_designatedMinutes' => $json_arr->designatedMinutes,
                                'wrkflow_designatedHours' => $json_arr->designatedHours,
                                'wrkflow_designatedDays' => $json_arr->designatedDays,
                                'wrkflow_notificationType' => $json_arr->notificationType,
                                'wrkflow_iOSNotification' => $json_arr->iOSNotificationId,
                                'wrkflow_androidNotification' => $json_arr->androidNotificationId,
                                'wrkflow_emailNotification' => $json_arr->emailNotificationId,
                                'wrkflow_personaNotification' => $json_arr->personaNotificationId,
                                'wrkflow_inAppNotification' => $json_arr->inAppNotificationId,
                                'wrkflow_send_date' => $wrkflow_send_date,
                                'createdDate' => date('Y-m-d H:i:s'));
                        } else if ($json_arr->timeDelayType == "Intelligent-delivery") {
                            $start = date('Y-m-d H:i:s');
                            $wrkflow_send_date = date('Y-m-d H:i:s', strtotime("+$json_arr->IntelligentDeliveryDays day +$json_arr->IntelligentDeliveryHours hour +$json_arr->IntelligentDeliveryMinutes minutes", strtotime($start)));
                            $data1 = array(
                                'wrkflow_id' => $id,
                                'wrkflow_TimeDelayType' => $json_arr->timeDelayType,
                                'wrkflow_intelligentDeliveryMinutes' => $json_arr->IntelligentDeliveryMinutes,
                                'wrkflow_intelligentDeliveryHours' => $json_arr->IntelligentDeliveryHours,
                                'wrkflow_intelligentDeliveryDays' => $json_arr->IntelligentDeliveryDays,
                                'wrkflow_intelligentDeliveryBetweenTime' => $json_arr->InteDelBetweenCheck,
                                'wrkflow_intelligentDeliveryfrMinutes' => $json_arr->IntDelBetweenfromMinutes,
                                'wrkflow_intelligentDeliverytoMinutes' => $json_arr->IntDelBetweentoMinutes,
                                'wrkflow_intelligentDeliveryReEligible' => $json_arr->InteDelReEligibleTimeCheckbox,
                                'wrkflow_ReEligibleDate' => $json_arr->InteDelReEligibleDate,
                                'wrkflow_ReEligibleDays' => $json_arr->InteDelReEligibleDays,
                                'wrkflow_notificationType' => $json_arr->notificationType,
                                'wrkflow_iOSNotification' => $json_arr->iOSNotificationId,
                                'wrkflow_androidNotification' => $json_arr->androidNotificationId,
                                'wrkflow_emailNotification' => $json_arr->emailNotificationId,
                                'wrkflow_personaNotification' => $json_arr->personaNotificationId,
                                'wrkflow_inAppNotification' => $json_arr->inAppNotificationId,
                                'wrkflow_send_date' => $wrkflow_send_date,
                                'createdDate' => date('Y-m-d H:i:s')
                            );
                        }
                        if (!empty($json_arr->timeDelay_workflow_id)) {
                            $data1 = array_merge($data1, array('timeDelay_workflow_id' => $json_arr->timeDelay_workflow_id));
                        }

                        $delayArray = $data1;
                        break;
                    }
                }


                $workflow = array_merge($data, $delayArray);
                unset($data);
                unset($delayArray);
            }
        }
        //  echo "<pre>"; print_r($workflow); exit;
        $andOr = "";
        $app_groups = $this->brand_model->getAllAppGroupsByBusinessId($login->businessId);
        if (!empty($workflow['wrkflow_fname'])) {
            $andOr = $workflow['wrkflow_fnameAndOr'];
            $append = '';
            $names = explode(',', $workflow['wrkflow_fname']);
            foreach ($names as $name) {
                $append .= "'$name'" . ',';
            } $append = rtrim($append, ',');
            $dataWorkflow['firstName'] = array($append, $workflow['wrkflow_fnameInEx'], $workflow['wrkflow_fnameAndOr']);
        }
        if (!empty($workflow['wrkflow_lname'])) {
            $andOr = $workflow['wrkflow_lnameAndOr'];
            $append = '';
            $names = explode(',', $workflow['wrkflow_lname']);
            foreach ($names as $name) {
                $append .= "'$name'" . ',';
            } $append = rtrim($append, ',');
            $dataWorkflow['lastName'] = array($append, $workflow['wrkflow_lnameInEx'], $workflow['wrkflow_lnameAndOr']);
        }
        if (!empty($workflow['wrkflow_timezone'])) {
            $andOr = $workflow['wrkflow_timezoneAndOr'];
            $append = '';
            $timezones = explode(',', $workflow['wrkflow_timezone']);
            foreach ($timezones as $name) {
                $append .= "'$name'" . ',';
            } $append = rtrim($append, ',');
            $dataWorkflow['timezone'] = array($append, $workflow['wrkflow_timezoneInEx'], $workflow['wrkflow_timezoneAndOr']);
        }
        if (!empty($workflow['wrkflow_creation_date'])) {
            $andOr = $workflow['wrkflow_creationDateAndOr'];
            $createdDate = $workflow['wrkflow_creation_date'];
            $dataWorkflow['createdDate'] = array($createdDate, $workflow['wrkflow_creationDateInEx'], $workflow['wrkflow_creationDateAndOr']);
        }
        if (!empty($workflow['wrkflow_originalSource'])) {
            $andOr = $workflow['wrkflow_originalSourceAndOr'];
            $type = $workflow['wrkflow_originalSource'];
            $inEx = $workflow['wrkflow_originalSourceInEx'];
            $userViaApp = 0;
            if (strcmp($type, "Offline Source") == 0) {
                $userViaApp = 0;
            } else {
                if ($inEx == "exclude") {
                    $inEx = "include";
                } else {
                    $inEx = "exclude";
                }
            } //echo "called";exit;
            $dataWorkflow['app_group_apps_id'] = array($userViaApp, $inEx, $workflow['wrkflow_originalSourceAndOr']);
        }
        if (!empty($dataWorkflow)) {
            $users = $this->workflow_model->getWorkflowExternalUsers($dataWorkflow, $app_groups);
        }
        if (empty($users[0]->external_user_id)) {
            $users = array();
        }
        //echo count($users); print_r($users); exit;
        if (!empty($workflow['wrkflow_interaction'])) {
            if ($workflow['wrkflow_interaction'] == "push") {
                if (!empty($workflow['wrkflow_interaction_campaigns'])) {
                    $append = '';
                    $interactionCam = explode(',', $workflow['wrkflow_interaction_campaigns']);
                    foreach ($interactionCam as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $interCamData['campaign_id'] = array($append, $workflow['wrkflow_interCampaignInEx'], $workflow['wrkflow_interCampaignAndOr']);
                    $interCamUsers = $this->workflow_model->getPushNotificationsOpenedUsers($interCamData);
                    if (count($interCamUsers) > 0) {
                        if (!empty($interCamUsers[0]->external_user_id)) {
                            @$temp1 = array();
                            if (!empty($users[0]->external_user_id)) {
                                @$temp1 = explode(',', $users[0]->external_user_id);
                                @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                                if (strcmp($andOr, 'AND') == 0) {
                                    $users = array_intersect($temp1, $temp2);
                                } else {
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            } else {
                                @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                                $users = $temp2;
                            }
                            //$inEx = "include";
                            //$dataWorkflow['external_user_id'] = array($interCamUsers[0]->external_user_id, $inEx, $workflow['wrkflow_interCampaignAndOr']);
                        }
                    }
                    $andOr = $workflow['wrkflow_interCampaignAndOr'];
                }
            } else if ($workflow['wrkflow_interaction'] == "email") {
                if (!empty($workflow['wrkflow_interaction_campaigns'])) {
                    $append = '';
                    $interactionCam = explode(',', $workflow['wrkflow_interaction_campaigns']);
                    foreach ($interactionCam as $campaign) {
                        $append .= "'$campaign'" . ',';
                    } $append = rtrim($append, ',');
                    $interCamData['campaignId'] = array($append, $workflow['wrkflow_interCampaignInEx'], $workflow['wrkflow_interCampaignAndOr']);
                    $interCamUsers = $this->workflow_model->getEmailInteractionOpenedUsers($interCamData); //print_r($interCamUsers); exit;
                    if (count($interCamUsers) > 0) {
                        if (!empty($interCamUsers[0]->external_user_id)) {
                            @$temp1 = array();
                            if (!empty($users[0]->external_user_id)) {
                                @$temp1 = explode(',', $users[0]->external_user_id);
                                @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
                                if (strcmp($andOr, 'AND') == 0) {
                                    $users = array_intersect($temp1, $temp2);
                                } else { //echo "clled"; exit;
                                    $users = array_unique(array_merge($temp1, $temp2));
                                }
                            } else {
                                @$temp2 = explode(',', $interCamUsers[0]->external_user_id);
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

                    $andOr = $workflow['wrkflow_interCampaignAndOr'];
                }
            }
        }
        //print_r($users); exit;
        //exit;
        if (!empty($workflow['wrkflow_persona'])) {
            $append = '';
            $personas = explode(',', $workflow['wrkflow_persona']);
            foreach ($personas as $persona) {
                $append .= "'$persona'" . ',';
            }
            $append = rtrim($append, ',');
            $personaUser['persona_user_id'] = array($append, $workflow['wrkflow_personaInEx'], $workflow['wrkflow_personaAndOr']);
            $personaUsers = $this->workflow_model->getPersonaUsers($personaUser, $app_groups); //echo $personaUsers->external_user_id; print_r(  $personaUsers);exit;
            if (count($personaUsers) > 0) {
                if (!empty($personaUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $personaUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, $temp2));
                        }
                    } else {
                        @$temp2 = explode(',', $personaUsers[0]->external_user_id);
                        $users = $temp2;
                    }
                    //$dataWorkflow['external_user_id'] = array($personaUsers[0]->external_user_id, $workflow['wrkflow_personaInEx'], $workflow['wrkflow_personaAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_personaAndOr'];
        } //echo count($personaUsers); exit;
        if (!empty($workflow['wrkflow_list'])) {
            $externalUserIdsArray = array();
            $append = '';
            $lists = explode(',', $workflow['wrkflow_list']);
            foreach ($lists as $list) {
                $listContacts = getContactsByListId($list, $workflow['businessId']);
                $externalUserIdsArray = array_merge($externalUserIdsArray, explode(',', $listContacts));
            }
            $listUsers = array_unique($externalUserIdsArray); //echo count($listUsers); exit;
            $allUsers = $this->workflow_model->getAllActiveUsers($app_groups);
            if (count($listUsers) > 0) {
                @$temp1 = array();
                if (strcmp($workflow['wrkflow_listInEx'], 'include') == 0) {
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $listUsers);
                        } else {
                            $users = array_unique(array_merge($temp1, $listUsers));
                        }
                    } elseif (count($users) > 0) {
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($users, $listUsers);
                        } else {
                            $users = array_unique(array_merge($users, $listUsers));
                        }
                    } else {
                        if (strcmp($workflow['wrkflow_listInEx'], 'exclude') == 0) {
                            @$temp1 = explode(',', $allUsers[0]->external_user_id);
                            $users = array_diff($temp1, $listUsers);
                        } else {
                            $users = $listUsers;
                        }
                    }
                } else {
                    if (strcmp($andOr, 'OR') == 0) { //echo "jfhjkhjf"; exit;
                        if (!empty($users[0]->external_user_id)) {
                            @$temp1 = explode(',', $users[0]->external_user_id);
                        } elseif (count($users) > 0) {
                            $users = array_intersect($users, $listUsers);
                        }
                        $listUsersCount = count($listUsers);
                        $totalUsers = count($temp1);
                        if ($listUsersCount == 0) {
                            $users = $temp1;
                        }
                        //$users = array_unique(array_merge($temp1, $listUsers));
                        //$users = array_intersect($users, $listUsers);
                    } else {
                        @$temp1 = explode(',', $allUsers[0]->external_user_id);
                        $users = array_diff($temp1, $listUsers);
                    }
                }
            }
            $andOr = $workflow['wrkflow_listAndOr'];
        }
        // print_r($listUsers); exit;/*
        //array_unique($externalUserIdsArray);
        //echo count($externalUserIdsArray); exit;
        //$listUsers = $externalUserIdsArray;

        /* $chunkSize1 = 100;
          $externalUserIdsArray = array_chunk($externalUserIdsArray, $chunkSize1);
          echo count($externalUserIdsArray);
          foreach ($externalUserIdsArray as $externalUserId) {
          $append .= "'$externalUserId'" . ',';
          }
          $append = rtrim($append, ','); print_r($append ); exit;
          $listUser['list_id'] = array($append, $workflow['wrkflow_listInEx'], $workflow['wrkflow_listAndOr']);
          $listUsers = $this->workflow_model->k($listUser); */ //echo "<pre>"; print_r($listUsers); exit;
//echo $listUsers[0]->external_user_id; exit;
//$dataWorkflow['external_user_id'] = array($listUsers[0]->external_user_id, $workflow['wrkflow_listInEx'], $workflow['wrkflow_listAndOr']);
        $is_received = 0;
        $is_opened = 0;
        $is_clicked = 0;
        if (!empty($workflow['wrkflow_receiverEmail'])) {
            $append = '';
            $receiverEmails = explode(',', $workflow['wrkflow_receiverEmail']);
            foreach ($receiverEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_received';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_receiverEmailInEx'], $workflow['wrkflow_receiverEmailAndOr']);
            $receiverEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($receiverEmailUsers) > 0) {
                if (!empty($receiverEmailUsers[0]->external_user_id)) {
                    if (strcmp($workflow['wrkflow_receiverEmailAndOr'], 'OR') == 0) {
                        $is_received = 1;
                    }
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) { //echo "clled1"; exit;
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $receiverEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) {
                            $users = array_intersect(@$temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge(@$temp1, @$temp2));
                        }
                    } else { //echo "clled2"; echo count($users); exit;
                        if (count($users) >= 0) {
                            @$temp2 = explode(',', $receiverEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $receiverEmailUsers[0]->external_user_id);
                            $users = @$temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($receiverEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_receiverEmailAndOr']);
                }
            }// echo count($users); exit;//print_r($users); exit;
            $andOr = $workflow['wrkflow_receiverEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_openedEmail'])) {
            $append = '';
            $openedEmails = explode(',', $workflow['wrkflow_openedEmail']);
            foreach ($openedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_opened';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_openedEmailInEx'], $workflow['wrkflow_openedEmailAndOr']);
            $openedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($openedEmailUsers) > 0) {
                if (!empty($openedEmailUsers[0]->external_user_id)) {
                    if (strcmp($workflow['wrkflow_openedEmailAndOr'], 'OR') == 0) {
                        $is_opened = 1;
                    }
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $openedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, $temp2));
                        }
                    } else {
                        if (count($users) >= 0) {
                            @$temp2 = explode(',', $openedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $openedEmailUsers[0]->external_user_id);
                            $users = @$temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($openedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_openedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_openedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_clickedEmail'])) {
            $append = '';
            $clickedEmails = explode(',', $workflow['wrkflow_clickedEmail']);
            foreach ($clickedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_clicked';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_clickedEmailInEx'], $workflow['wrkflow_clickedEmailAndOr']);
            $clickedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($clickedEmailUsers) > 0) {
                if (!empty($clickedEmailUsers[0]->external_user_id)) {
                    if (strcmp($workflow['wrkflow_clickedEmailAndOr'], 'OR') == 0) {
                        $is_clicked = 1;
                    }//echo $clickedEmailUsers[0]->external_user_id; exit;
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $clickedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, @$temp2));
                        }
                    } else {
                        if (count($users) >= 0) {
                            @$temp2 = explode(',', $clickedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $clickedEmailUsers[0]->external_user_id);
                            $users = @$temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($clickedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_clickedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_clickedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_unsubscribedEmail'])) {
            $append = '';
            $unsubscribedEmails = explode(',', $workflow['wrkflow_unsubscribedEmail']);
            foreach ($unsubscribedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_unsubscribed';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_unsubscribedEmailInEx'], $workflow['wrkflow_unsubscribedEmailAndOr']);
            $unsubscribedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($unsubscribedEmailUsers) > 0) {
                if (!empty($unsubscribedEmailUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $unsubscribedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($users, @$temp2);
                        } else {
                            $users = array_unique(array_merge($users, @$temp2));
                        }
                    } else {
                        if (count($users) > 0) {
                            @$temp2 = explode(',', $unsubscribedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            if (count($users) > 0) {
                                @$temp2 = explode(',', $unsubscribedEmailUsers[0]->external_user_id);
                                if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_unique(array_merge($users, @$temp2));
                                }
                            } else {
                                @$temp2 = explode(',', $unsubscribedEmailUsers[0]->external_user_id);
                                $users = @$temp2;
                            }
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($unsubscribedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_unsubscribedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_unsubscribedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_bouncedEmail'])) {
            $append = '';
            $bouncedEmails = explode(',', $workflow['wrkflow_bouncedEmail']);
            foreach ($bouncedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_bounced';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_bouncedEmailInEx'], $workflow['wrkflow_bouncedEmailAndOr']);
            $bouncedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($bouncedEmailUsers) > 0) {
                if (!empty($bouncedEmailUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $bouncedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, @$temp2));
                        }
                    } else {
                        if (count($users) > 0) {
                            @$temp2 = explode(',', $bouncedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $bouncedEmailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($bouncedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_bouncedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_bouncedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_NotReceivedEmail'])) {
            $append = '';
            $NotReceivedEmails = explode(',', $workflow['wrkflow_NotReceivedEmail']);
            foreach ($NotReceivedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_not_received';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_NotReceivedEmailInEx'], $workflow['wrkflow_NotReceivedEmailAndOr']);
            $notReceivedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($notReceivedEmailUsers) > 0) {
                if (!empty($notReceivedEmailUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $notReceivedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, @$temp2));
                        }
                    } else { //echo count($users); print_r($users); print_r($notReceivedEmailUsers); exit;
                        if (count($users) > 0) {
                            @$temp2 = explode(',', $notReceivedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) {
                                if ($is_received == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_unique(array_merge($users, @$temp2));
                                }
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $notReceivedEmailUsers[0]->external_user_id);
                            $users = $temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($notReceivedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_NotReceivedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_NotReceivedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_NotOpenedEmail'])) {
            $append = '';
            $NotOpenedEmails = explode(',', $workflow['wrkflow_NotOpenedEmail']);
            foreach ($NotOpenedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_not_opened';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_NotOpenedEmailInEx'], $workflow['wrkflow_NotOpenedEmailAndOr']);
            $notOpenedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($notOpenedEmailUsers) > 0) {
                if (!empty($notOpenedEmailUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $notOpenedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, @$temp2));
                        }
                    } else {
                        if (count($users) > 0) {
                            @$temp2 = explode(',', $notOpenedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) {
                                if ($is_opened == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_unique(array_merge($users, @$temp2));
                                }
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $notOpenedEmailUsers[0]->external_user_id);
                            $users = @$temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($notOpenedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_NotOpenedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_NotOpenedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_NotClickedEmail'])) {
            $append = '';
            $NotClickedEmails = explode(',', $workflow['wrkflow_NotClickedEmail']);
            foreach ($NotClickedEmails as $campaign) {
                $append .= "'$campaign'" . ',';
            } $append = rtrim($append, ',');
            $type = 'is_not_clicked';
            $emailCampaign['campaignId'] = array($append, $workflow['wrkflow_NotClickedEmailInEx'], $workflow['wrkflow_NotClickedEmailAndOr']);
            $notClickedEmailUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($notClickedEmailUsers) > 0) {
                if (!empty($notClickedEmailUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $notClickedEmailUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, @$temp2));
                        }
                    } else {
                        if (count($users) > 0) {
                            @$temp2 = explode(',', $notClickedEmailUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) {
                                if ($is_clicked == 0) {
                                    $users = array_intersect($users, @$temp2);
                                } else {
                                    $users = array_unique(array_merge($users, @$temp2));
                                }
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            @$temp2 = explode(',', $notClickedEmailUsers[0]->external_user_id);
                            $users = @$temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($notClickedEmailUsers[0]->external_user_id, 'include', $workflow['wrkflow_NotClickedEmailAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_NotClickedEmailAndOr'];
        }
        if (!empty($workflow['wrkflow_lastEmailOpenDate'])) {
            $lastEmailOpenDate = $workflow['wrkflow_lastEmailOpenDate'];
            $emailCampaign['openTime'] = array($lastEmailOpenDate, $workflow['wrkflow_lastEmailOpenDateInEx'], $workflow['wrkflow_lastEmailOpenDateAndOr']);
            $type = 'is_opened';
            $lastOpenDateUsers = $this->workflow_model->getEmailNotificationsOpenedUsers($emailCampaign, $type);
            if (count($lastOpenDateUsers) > 0) {
                if (!empty($lastOpenDateUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $lastOpenDateUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, @$temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, @$temp2));
                        }
                    } else {
                        if (count($users) > 0) {
                            @$temp2 = explode(',', $lastOpenDateUsers[0]->external_user_id);
                            if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                                $users = array_intersect($users, @$temp2);
                            } else {
                                $users = array_unique(array_merge($users, @$temp2));
                            }
                        } else {
                            $allUsers = $this->workflow_model->getAllActiveUsers($app_groups);
                            @$temp1 = explode(',', $allUsers[0]->external_user_id);
                            @$temp2 = explode(',', $lastOpenDateUsers[0]->external_user_id);
                            $users = array_intersect($temp1, $temp2);
                            //@$temp2 = explode(',',$lastOpenDateUsers[0]->external_user_id);
                            //$users = $temp2;
                        }
                    }
                    //$dataWorkflow['external_user_id'] = array($lastOpenDateUsers[0]->external_user_id, 'include', $workflow['wrkflow_lastEmailOpenDateAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_lastEmailOpenDateAndOr'];
        }
        if (!empty($workflow['wrkflow_pageViewed'])) {
            $append = '';
            $appPages = explode(',', $workflow['wrkflow_pageViewed']);
            foreach ($appPages as $page) {
                $append .= "'$page'" . ',';
            } $append = rtrim($append, ',');
            $pages['screenName'] = array($append, $workflow['wrkflow_pageViewedInEx'], $workflow['wrkflow_pageViewedAndOr']);
            if (!empty($workflow['wrkflow_NumOfViews'])) {
                $numOfViews = $workflow['wrkflow_NumOfViews'];
                $pageViewedUsers = $this->workflow_model->getAllAppScreenUsers($pages, $numOfViews);
            } else {
                $pageViewedUsers = $this->workflow_model->getAllAppScreenUsers($pages);
            }
            if (count($pageViewedUsers) > 0) {
                if (!empty($pageViewedUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $pageViewedUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, $temp2));
                        }
                    } else {
                        @$temp2 = explode(',', $pageViewedUsers[0]->external_user_id);
                        $users = $temp2;
                    }
                    //$dataWorkflow['external_user_id'] = array($pageViewedUsers[0]->external_user_id, 'include', $workflow['wrkflow_pageViewedAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_pageViewedAndOr'];
        }
        if (!empty($workflow['wrkflow_lastOpenAppDate'])) {
            $lastOpenAppDate = $workflow['wrkflow_lastOpenAppDate'];
            $lastOpenAppArr['dateTime'] = array($lastOpenAppDate, $workflow['wrkflow_lastOpenAppDateInEx'], $workflow['wrkflow_lastOpenAppDateAndOr']);
            $lastOpenAppUsers = $this->workflow_model->getExternalUsersByAppOpenedDate($lastOpenAppArr);
            if (count($lastOpenAppUsers) > 0) {
                if (!empty($lastOpenAppUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $lastOpenAppUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, $temp2));
                        }
                    } else {
                        @$temp2 = explode(',', $lastOpenAppUsers[0]->external_user_id);
                        $users = $temp2;
                    }
                    //$dataWorkflow['external_user_id'] = array($lastOpenAppUsers[0]->external_user_id, 'include', $workflow['wrkflow_lastOpenAppDateAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_lastOpenAppDateAndOr'];
        }
        if (!empty($workflow['wrkflow_NumOfVisit'])) {
            $NumOfVisit = $workflow['wrkflow_NumOfVisit'];
            $NumOfVisitArr['dateTime'] = array($NumOfVisit, $workflow['wrkflow_NumOfVisitInEx'], $workflow['wrkflow_NumOfVisitAndOr']);
            $NumOfVisitUsers = $this->workflow_model->getExternalUsersByAppOpenedDate($NumOfVisitArr, $NumOfVisit);
            if (count($NumOfVisitUsers) > 0) {
                if (!empty($NumOfVisitUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $NumOfVisitUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, $temp2));
                        }
                    } else {
                        @$temp2 = explode(',', $NumOfVisitUsers[0]->external_user_id);
                        $users = $temp2;
                    }
                    //$dataWorkflow['external_user_id'] = array($NumOfVisitUsers[0]->external_user_id, 'include', $workflow['wrkflow_NumOfVisitAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_NumOfVisitAndOr'];
        }
        if (!empty($workflow['wrkflow_lastPageSeen'])) {
            $append = '';
            $appPages = explode(',', $workflow['wrkflow_lastPageSeen']);
            foreach ($appPages as $page) {
                $append .= "'$page'" . ',';
            } $append = rtrim($append, ',');
            $pages['screenName'] = array($append, $workflow['wrkflow_lastPageSeenInEx'], $workflow['wrkflow_lastPageSeenAndOr']);
            $lastPageSeenUsers = $this->workflow_model->getLastSeenPageUsers($pages);
            if (count($lastPageSeenUsers) > 0) {
                if (!empty($lastPageSeenUsers[0]->external_user_id)) {
                    @$temp1 = array();
                    if (!empty($users[0]->external_user_id)) {
                        @$temp1 = explode(',', $users[0]->external_user_id);
                        @$temp2 = explode(',', $lastPageSeenUsers[0]->external_user_id);
                        if (strcmp($andOr, 'AND') == 0) { //echo "clled"; exit;
                            $users = array_intersect($temp1, $temp2);
                        } else {
                            $users = array_unique(array_merge($temp1, $temp2));
                        }
                    } else {
                        @$temp2 = explode(',', $lastPageSeenUsers[0]->external_user_id);
                        $users = $temp2;
                    }
                    //$dataWorkflow['external_user_id'] = array($lastPageSeenUsers[0]->external_user_id, 'include', $workflow['wrkflow_lastPageSeenAndOr']);
                }
            }
            $andOr = $workflow['wrkflow_lastPageSeenAndOr'];
        }

        //$result = array_merge($users, $interCamUsers,$personaUsers, $listUsers, $receiverEmailUsers, $openedEmailUsers, $clickedEmailUsers, $unsubscribedEmailUsers, $bouncedEmailUsers, $notReceivedEmailUsers, $notOpenedEmailUsers, $notClickedEmailUsers, $lastOpenDateUsers, $pageViewedUsers, $lastOpenAppUsers, $NumOfVisitUsers, $lastPageSeenUsers);
        /* echo count($users); echo "<pre>"; print_r($users); exit;
          $external_user_ids = "";
          if(count($result) > 0){
          foreach($result as $key => $row){
          $external_user_ids .= $row->external_user_id.',';
          }
          $external_user_ids = rtrim($external_user_ids, ',');
          }
          if(!empty($external_user_ids)){
          $dataWorkflow['external_user_id'] = array($external_user_ids,'include','AND');
          } */


        $result = $users;
        $result = array_unique($result, SORT_REGULAR);
        //  echo count($users); echo "<pre>"; print_r($result); exit;//($users); print_r($listUsers); exit;

        $newUsers = '';
        if (count($result) > 0) {
            $newArray = array();
            foreach ($result as $row) { //print_r($row); exit;
                //if (!in_array($row->external_user_id, $newArray)) {
                if (!empty($row->external_user_id)) {
                    $newArray[] = $row->external_user_id;
                    $newUsers .= "$row->external_user_id" . ',';
                } else { //echo $row; exit;
                    $newArray[] = $row;
                    $newUsers .= "$row" . ',';
                }
            }
            $newUsers = rtrim($newUsers, ',');
        } //print_r($result); //exit;
        //print_r($newArray); exit;
        $date = date('Y-m-d H:i:s');
        if (!empty($workflow['wrkflow_TimeDelayType'])) {
            if ($workflow['wrkflow_TimeDelayType'] == "Designated-Time") {
                $minutes = $workflow['wrkflow_designatedMinutes'];
                $hours = $workflow['wrkflow_designatedHours'];
                $days = $workflow['wrkflow_designatedDays'];
                $datetime = date('Y-m-d H:i:s', strtotime($date . "$hours hours $minutes minutes $days days"));
            }
            if ($workflow['wrkflow_TimeDelayType'] == "Intelligent-delivery") {
                $minutes = $workflow['wrkflow_intelligentDeliveryMinutes'];
                $hours = $workflow['wrkflow_intelligentDeliveryHours'];
                $days = $workflow['wrkflow_intelligentDeliveryDays'];
                $datetime = date('Y-m-d H:i:s', strtotime($date . "$hours hours $minutes minutes $days days"));
            }
        }
        // echo "<pre>"; print_r($workflow); exit;
        if ($workflow['wrkflow_notificationType'] == "push") {
            $userIds = $newUsers;
            $arr = array();
            if (!empty($userIds)) {
                $userIdsArray = explode(',', $userIds);
                $userIdsArray = array_chunk($userIdsArray, $chunkSize);
                foreach ($userIdsArray as $userIdsItem) {
                    $pushUsers = $this->workflow_model->getAllExternalUsersDevices(implode(",", $userIdsItem)); //  echo "<pre>"; print_r($pushUsers); exit;
                    $totalSendUsers = $totalSendUsers + count($pushUsers);
                }
            }
        } else if ($workflow['wrkflow_notificationType'] == "email") {
            if (!empty($workflow['wrkflow_emailNotification'])) {
                $arr = array();
                $userIds = $newUsers;
                if (!empty($userIds)) {
                    $userIdsArray = explode(',', $userIds);  //print_r($userIdsArray); //exit;
                    $userIdsArray = array_chunk($userIdsArray, $chunkSize); //print_r($userIdsArray); exit;
                    foreach ($userIdsArray as $userIdsItem) {
                        $emailUsers = $this->workflow_model->getAllExternalUsersEmails(implode(",", $userIdsItem)); // print_r($emailUsers); exit;
                        $totalSendUsers = $totalSendUsers + count($emailUsers);
                    }
                }
            }
        } else if ($workflow['wrkflow_notificationType'] == "persona") {
            if (!empty($workflow['wrkflow_personaNotification'])) {
                $persona_user_id = $workflow['wrkflow_personaNotification'];  //print_r($result); exit;
                $totalSendUsers = count($result);
            }
        } else if ($workflow['wrkflow_notificationType'] == "in-app") {
            $userIds = $newUsers; //echo $userIds; exit;
            $arr = array();
            $pushUsers = $this->workflow_model->getAllExternalUsersDevices($userIds);  //print_r($pushUsers); exit;
            $totalSendUsers = $totalSendUsers + count($pushUsers);
        }
        echo $totalSendUsers;
        die;
    }

    function workflowClonePopUp($id) {
        $data['workflow_id'] = $id;
        //echo $data['workflow_id']; die;
        $this->load->view('3.1/workflow_clone', $data);
    }

    function workflowClone() {
        if (isset($_POST['workflowId'])) {
            $workflowId = $_POST['workflowId'];

            $workflowRow = $this->workflow_model->getWorkflowByWorkflowId($workflowId);

            $insert['wrkflow_id'] = '';
            $insert['businessId'] = $workflowRow->businessId;
            $insert['wrkflow_title'] = $workflowRow->wrkflow_title;
            $insert['wrkflow_fname'] = $workflowRow->wrkflow_fname;
            $insert['wrkflow_fnameInEx'] = $workflowRow->wrkflow_fnameInEx;
            $insert['wrkflow_fnameAndOr'] = $workflowRow->wrkflow_fnameAndOr;
            $insert['wrkflow_lname'] = $workflowRow->wrkflow_lname;
            $insert['wrkflow_lnameInEx'] = $workflowRow->wrkflow_lnameInEx;
            $insert['wrkflow_lnameAndOr'] = $workflowRow->wrkflow_lnameAndOr;
            $insert['wrkflow_timezone'] = $workflowRow->wrkflow_timezone;
            $insert['wrkflow_timezoneInEx'] = $workflowRow->wrkflow_timezoneInEx;
            $insert['wrkflow_timezoneAndOr'] = $workflowRow->wrkflow_timezoneAndOr;
            $insert['wrkflow_creation_date'] = $workflowRow->wrkflow_creation_date;
            $insert['wrkflow_creationDateInEx'] = $workflowRow->wrkflow_creationDateInEx;
            $insert['wrkflow_creationDateAndOr'] = $workflowRow->wrkflow_creationDateAndOr;
            $insert['wrkflow_interaction'] = $workflowRow->wrkflow_interaction;
            $insert['wrkflow_interaction_campaigns'] = $workflowRow->wrkflow_interaction_campaigns;
            $insert['wrkflow_interCampaignInEx'] = $workflowRow->wrkflow_interCampaignInEx;
            $insert['wrkflow_interCampaignAndOr'] = $workflowRow->wrkflow_interCampaignAndOr;
            $insert['wrkflow_originalSource'] = $workflowRow->wrkflow_originalSource;
            $insert['wrkflow_originalSourceInEx'] = $workflowRow->wrkflow_originalSourceInEx;
            $insert['wrkflow_originalSourceAndOr'] = $workflowRow->wrkflow_originalSourceAndOr;
            $insert['wrkflow_persona'] = $workflowRow->wrkflow_persona;
            $insert['wrkflow_personaInEx'] = $workflowRow->wrkflow_personaInEx;
            $insert['wrkflow_personaAndOr'] = $workflowRow->wrkflow_personaAndOr;
            $insert['wrkflow_receiverEmail'] = $workflowRow->wrkflow_receiverEmail;
            $insert['wrkflow_receiverEmailInEx'] = $workflowRow->wrkflow_receiverEmailInEx;
            $insert['wrkflow_receiverEmailAndOr'] = $workflowRow->wrkflow_receiverEmailAndOr;
            $insert['wrkflow_openedEmail'] = $workflowRow->wrkflow_openedEmail;
            $insert['wrkflow_openedEmailInEx'] = $workflowRow->wrkflow_openedEmailInEx;
            $insert['wrkflow_openedEmailAndOr'] = $workflowRow->wrkflow_openedEmailAndOr;
            $insert['wrkflow_clickedEmail'] = $workflowRow->wrkflow_clickedEmail;
            $insert['wrkflow_clickedEmailInEx'] = $workflowRow->wrkflow_clickedEmailInEx;
            $insert['wrkflow_clickedEmailAndOr'] = $workflowRow->wrkflow_clickedEmailAndOr;
            $insert['wrkflow_unsubscribedEmail'] = $workflowRow->wrkflow_unsubscribedEmail;
            $insert['wrkflow_unsubscribedEmailInEx'] = $workflowRow->wrkflow_unsubscribedEmailInEx;
            $insert['wrkflow_unsubscribedEmailAndOr'] = $workflowRow->wrkflow_unsubscribedEmailAndOr;
            $insert['wrkflow_bouncedEmail'] = $workflowRow->wrkflow_bouncedEmail;
            $insert['wrkflow_bouncedEmailInEx'] = $workflowRow->wrkflow_bouncedEmailInEx;
            $insert['wrkflow_bouncedEmailAndOr'] = $workflowRow->wrkflow_bouncedEmailAndOr;
            $insert['wrkflow_NotReceivedEmail'] = $workflowRow->wrkflow_NotReceivedEmail;
            $insert['wrkflow_NotReceivedEmailInEx'] = $workflowRow->wrkflow_NotReceivedEmailInEx;
            $insert['wrkflow_NotReceivedEmailAndOr'] = $workflowRow->wrkflow_NotReceivedEmailAndOr;
            $insert['wrkflow_NotOpenedEmail'] = $workflowRow->wrkflow_NotOpenedEmail;
            $insert['wrkflow_NotOpenedEmailInEx'] = $workflowRow->wrkflow_NotOpenedEmailInEx;
            $insert['wrkflow_NotOpenedEmailAndOr'] = $workflowRow->wrkflow_NotOpenedEmailAndOr;
            $insert['wrkflow_NotClickedEmail'] = $workflowRow->wrkflow_NotClickedEmail;
            $insert['wrkflow_NotClickedEmailInEx'] = $workflowRow->wrkflow_NotClickedEmailInEx;
            $insert['wrkflow_NotClickedEmailAndOr'] = $workflowRow->wrkflow_NotClickedEmailAndOr;
            $insert['wrkflow_lastEmailOpenDate'] = $workflowRow->wrkflow_lastEmailOpenDate;
            $insert['wrkflow_lastEmailOpenDateInEx'] = $workflowRow->wrkflow_lastEmailOpenDateInEx;
            $insert['wrkflow_lastEmailOpenDateAndOr'] = $workflowRow->wrkflow_lastEmailOpenDateAndOr;
            $insert['wrkflow_pageViewed'] = $workflowRow->wrkflow_pageViewed;
            $insert['wrkflow_pageViewedInEx'] = $workflowRow->wrkflow_pageViewedInEx;
            $insert['wrkflow_pageViewedAndOr'] = $workflowRow->wrkflow_pageViewedAndOr;
            $insert['wrkflow_NumOfViews'] = $workflowRow->wrkflow_NumOfViews;
            $insert['wrkflow_NumOfViewsInEx'] = $workflowRow->wrkflow_NumOfViewsInEx;
            $insert['wrkflow_NumOfViewsAndOr'] = $workflowRow->wrkflow_NumOfViewsAndOr;
            $insert['wrkflow_lastOpenAppDate'] = $workflowRow->wrkflow_lastOpenAppDate;
            $insert['wrkflow_lastOpenAppDateInEx'] = $workflowRow->wrkflow_lastOpenAppDateInEx;
            $insert['wrkflow_lastOpenAppDateAndOr'] = $workflowRow->wrkflow_lastOpenAppDateAndOr;
            $insert['wrkflow_NumOfVisit'] = $workflowRow->wrkflow_NumOfVisit;
            $insert['wrkflow_NumOfVisitInEx'] = $workflowRow->wrkflow_NumOfVisitInEx;
            $insert['wrkflow_NumOfVisitAndOr'] = $workflowRow->wrkflow_NumOfVisitAndOr;
            $insert['wrkflow_lastPageSeen'] = $workflowRow->wrkflow_lastPageSeen;
            $insert['wrkflow_lastPageSeenInEx'] = $workflowRow->wrkflow_lastPageSeenInEx;
            $insert['wrkflow_lastPageSeenAndOr'] = $workflowRow->wrkflow_lastPageSeenAndOr;
            $insert['wrkflow_list'] = $workflowRow->wrkflow_list;
            $insert['wrkflow_listInEx'] = $workflowRow->wrkflow_listInEx;
            $insert['wrkflow_listAndOr'] = $workflowRow->wrkflow_listAndOr;
            $insert['wrkflow_existUsers'] = $workflowRow->wrkflow_existUsers;
            $insert['wrkflow_NewUsers'] = $workflowRow->wrkflow_NewUsers;
            $insert['isDraft'] = 1;
            $insert['isActive'] = 0;
            $insert['isDelete'] = 0;
            $insert['is_send'] = $workflowRow->is_send;
            $insert['createdDate'] = date('Y-m-d H:i:s');
            $insert['modifiedDate'] = date('Y-m-d H:i:s');

            $last_insert_id = $this->workflow_model->saveWorkflowDraft($insert);

            echo $last_insert_id;
        }
    }

    public function updateInfo() {
        $this->load->library('excel');
        $file = './upload/files/emails.csv';
        //$businessIdOfFile = $fileData['businessId'];
        //$userData = $this->user_model->getOneUser($businessIdOfFile);

        /*
          $groupsIdArray = $this->groupapp_model->getGroupsIdsOnly($businessIdOfFile);
          $groupsIdArrayOneDimensionalArray = array_map('current', $groupsIdArray); */

        $objReader = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($file));
        $spreadsheetInfo = $objReader->listWorksheetInfo($file);
        /**  Create a new Instance of our Read Filter  * */
        $chunkFilter = new PHPExcel_ChunkReadFilter();
        $chunkSize = 1000;
        /**  Tell the Reader  that we want to use the Read Filter that we've Instantiated * */
        $objReader->setReadFilter($chunkFilter);
        $objReader->setReadDataOnly(true);
        //echo("Reading file " . $file . PHP_EOL . "<br>");
        $totalRows = $spreadsheetInfo[0]['totalRows'];
        $i = 0;
        //echo("Total rows in file " . $totalRows . " " . PHP_EOL . "<br>");
        for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
            $sql = '';
            // echo("Loading WorkSheet for rows " . $startRow . " to " . ($startRow + $chunkSize - 1) . PHP_EOL . "<br>");

            /**  Tell the Read Filter, the limits on which rows we want to read this iteration  * */
            $chunkFilter->setRows($startRow, $chunkSize);
            /**  Load only the rows that match our filter from $inputFileName to a PHPExcel Object  * */ $objPHPExcel = $objReader->load($file);

            //$sheetData = $objPHPExcel->getActiveSheet();
            $sheetData = $objPHPExcel->getActiveSheet()->removeRow(1, $startRow - 1)->toArray(null, true, true, true);

            $sheetData = array_map(function($sheetData) {
                return array(
                    'email_id' => $sheetData['A'],
                    'sendgrid_message_id' => $sheetData['B']
                );
            }, $sheetData);


            //echo "<pre>"; print_r($sheetData); // exit;

            /* foreach ($sheetData as $key => $csm) {
              unset($sheetData[$key]['id']);
              $sheetData[$key]['file_id'] = $file_id;
              } */

            //  print_r($sheetData); exit;

            $sql = ""; // (`app_group_id`, `email`, `firstName`, `lastName`, `phoneNumber`, `file_id`) VALUES";

            foreach ($sheetData as $element) {
                $i = $i + 1;
                $sql .= "UPDATE `brand_email_campaigns_info` SET is_send = 1, sendgrid_message_id = '" . $element['sendgrid_message_id'] . "' WHERE email_id = '" . $element['email_id'] . "';<br/>";
            }
            //  $sql = rtrim($sql, ',');
            echo $sql; //exit;
            //$this->db->query($sql);
        }
        // echo $this->db->last_query();die;
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel, $sheetData);
    }

    function getWorkflowUsersCount() {
        $chunkSize = 100;
        $users = array();
        $interCamUsers = array();
        $personaUsers = array();
        $listUsers = array();
        $pageViewedUsers = array();
        $lastPageSeenUsers = array();
        $totalSendUsers = 0;
        $login = $this->administrator_model->front_login_session();
        $workflow = $_POST['workflow']; //print_r($_POST['workflow']); die;

        $data = array();
        $dataWorkflow = array();
        if (isset($workflow)) {
            $businessId = $login->businessId;
            $andOr = "";
            $app_groups = $this->brand_model->getAllAppGroupsByBusinessId($login->businessId);
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
                $emailExists1 = 1;
                $interCamUsers = $this->workflow_model->getEmailInteractionNewOpenedUsers($workflow['interection_open_email'],$businessId); //print_r($interCamUsers); exit;
                while(key($workflow) !== "interection_open_email") next($workflow);
                $prevArray = prev($workflow); print_r($prevArray); //print_r($users[0]->external_user_id); exit;
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
                    } else if (count($users) >= 0 && $emailExists1 = 1) { //echo "called2"; exit;
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
                    $listUsers = array_flip($listUsers);
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
                        if (strcmp($prevArray['opt'], 'AND') == 0) {
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
            // $result = array_unique($result, SORT_REGULAR);
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
            }  //  echo count($newArray); exit();
            $date = date('Y-m-d H:i:s');
            if (count($workflow['timeDelay']) > 0) {
                $timeDelay = json_decode($workflow['timeDelay'][0],true); //echo "<pre>"; print_r($workflow); print_r($timeDelay); exit;
            }

            if ($timeDelay['notificationType'] == "push") {
                $arr = array();
                $userIds = $newUsers;
                if (!empty($userIds)) {
                    $userIdsArray = explode(',', $userIds);  //print_r($userIdsArray); //exit;
                    $userIdsArray = array_chunk($userIdsArray, $chunkSize); //print_r($userIdsArray); exit;
                    foreach ($userIdsArray as $userIdsItem) {
                      $pushUsers = $this->workflow_model->getAllExternalUsersDevices(implode(",", $userIdsItem)); //  echo "<pre>"; print_r($pushUsers); exit;
                      $totalSendUsers = $totalSendUsers + count($pushUsers);
                    }
                }
            } else if ($timeDelay['notificationType'] == "email") {
                if (!empty($timeDelay['emailNotification'])) {
                    $arr = array();
                    $userIds = $newUsers;
                    if (!empty($userIds)) {
                        $userIdsArray = explode(',', $userIds);  //print_r($userIdsArray); //exit;
                        $userIdsArray = array_chunk($userIdsArray, $chunkSize); //print_r($userIdsArray); exit;
                        foreach ($userIdsArray as $userIdsItem) {
                            $emailUsers = $this->workflow_model->getAllExternalUsersEmails(implode(",", $userIdsItem)); // print_r($emailUsers); exit;
                            $totalSendUsers = $totalSendUsers + count($emailUsers);
                        }
                    }
                }
            } else if ($timeDelay['notificationType'] == "persona") {
                if (!empty($timeDelay['personaNotification'])) {
                    $arr = array();
                    $userIds = $newUsers;
                    if (!empty($userIds)) {
                      $userIdsArray = explode(',', $userIds);
                      $userIdsArray = array_chunk($userIdsArray, $chunkSize);
                      foreach ($userIdsArray as $userIdsItem) {
                        $personaUsers = $this->workflow_model->getAllExternalUsersEmails(implode(",", $userIdsItem)); // print_r($emailUsers); exit;
                        $totalSendUsers =  $totalSendUsers + count($personaUsers);
                      }
                   }
                }
            } else if ($timeDelay['notificationType'] == "in-app") {
              $arr = array();
              $userIds = $newUsers;
              if (!empty($userIds)) {
                $userIdsArray = explode(',', $userIds);
                $userIdsArray = array_chunk($userIdsArray, $chunkSize);
                foreach ($userIdsArray as $userIdsItem) {
                  $pushUsers = $this->workflow_model->getAllExternalUsersDevices(implode(",", $userIdsItem));  //print_r($pushUsers); exit;
                  $totalSendUsers = $totalSendUsers + count($pushUsers);
                }
              }
            }
            echo $totalSendUsers; exit();
        }
    }

    function getWorkflow($workflowId){
       if(!empty($workflowId)){
         $totalSendUsers = 0;
         $login = $this->administrator_model->front_login_session();
         $workflowRow = $this->workflow_model->getWorkflowRow($workflowId);

         $data = array();
         $dataWorkflow = array();
         $workflow = json_decode($workflowRow['wrkflow_triggerpoint_json'],true); //echo "<pre>";print_r($workflow); exit;
         if (!empty($workflow)) {
             $businessId = $login->businessId;
             $andOr = "";
             $app_groups = $this->brand_model->getAllAppGroupsByBusinessId($login->businessId);
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


    function reLaunchWorkflow($wrkflow_id)
    {
        $wrkflow_historyId = null;
        $external_userid_text = $this->getWorkflow($wrkflow_id);
        if($external_userid_text != '')
        {
            $insert['wrkflow_id'] = $wrkflow_id;
            $insert['external_userid_text'] = $external_userid_text;
            $insert['isDraft'] = 0;
            $insert['isActive'] = 1;
            $insert['isDelete'] = 0;
            $insert['createdDate'] = date('Y-m-d H:i:s');

            $wrkflow_historyId = $this->workflow_model->saveWorkflowHistory($insert, 1);
            $this->workflow_model->updateflowHistoryTime($wrkflow_id);
            $timedelays = $this->workflow_model->getTimeDelays($wrkflow_id);
            $datestring = date('Y-m-d H:i:s');
            foreach($timedelays as $timedelay){

                $insert1['wrkflow_id'] = $wrkflow_id;
                $insert1['wrkflow_historyId'] = $wrkflow_historyId;
                $insert1['wrkflow_delay_id'] = $timedelay->timeDelay_workflow_id;
                $insert1['wrkflow_notificationType'] = $timedelay->wrkflow_notificationType;
                if($timedelay->wrkflow_TimeDelayType == 'Designated-Time'){
                    $date = date('Y-m-d H:i:s');

                    $minutes = $timedelay->wrkflow_designatedMinutes;
                    $hours = $timedelay->wrkflow_designatedHours;
                    $days = $timedelay->wrkflow_designatedDays;


                    $sendtime = explode(' ',$timedelay->wrkflow_send_date);
                    $sendtime[1];

                    $current = date('Y-m-d', strtotime($datestring . "$days days"));
                    $datestring = $current;
                    $newDate = explode(' ', $current);
                    $newDate[0];
                    $type = 'DT';
                    $religibleDays = '';
                    $time_between = '';


                }else{
                    $date = date('Y-m-d H:i:s');

                    $minutes = $timedelay->wrkflow_intelligentDeliveryMinutes;
                    $hours = $timedelay->wrkflow_intelligentDeliveryHours;
                    $days = $timedelay->wrkflow_intelligentDeliveryDays;

                    $sendtime = explode(' ',$timedelay->wrkflow_send_date);
                    $sendtime[1];

                    $current = date('Y-m-d', strtotime($datestring . "$days days"));
                    $datestring = $current;
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

                }

                $insert1['wrkflow_time'] = $newDate[0]." ".$sendtime[1];
                $insert1['type'] = $type;
                $insert1['days'] = $religibleDays;
                $insert1['time_between'] = $time_between;
                $insert1['isDraft'] = 0;
                $insert1['isActive'] = 1;
                $insert1['isDelete'] = 0;
                $insert1['createdDate'] = date('Y-m-d H:i:s');
                $insert1['modifiedDate'] = date('Y-m-d H:i:s');

                $this->workflow_model->saveWorkflowHistoryTime($insert1, 1);

            }
        }
    }

  function saveWorkflowHistory($wrkflow_id){
      $wrkflow_historyId = null;
      $external_userid_text = $this->getWorkflow($wrkflow_id);
      if($external_userid_text == "")
          return false;
      $extUsers = explode(",",$external_userid_text);
      //echo '<pre>';
      //print_r($extUsers); die;

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
            //echo $str1;f
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

      }else{
        $workflowRow = $this->workflow_model->getWorkflowByWorkflowId($wrkflow_id);
        if(isset($workflowRow->checkEnrollUsers) && $workflowRow->checkEnrollUsers == 1){
            $insert['wrkflow_id'] = $wrkflow_id;
            $insert['external_userid_text'] = $external_userid_text;
            $insert['isDraft'] = 0;
            $insert['isActive'] = 1;
            $insert['isDelete'] = 0;
            $insert['createdDate'] = date('Y-m-d H:i:s');

           $wrkflow_historyId = $this->workflow_model->saveWorkflowHistory($insert);
        }else{
              $insert['wrkflow_id'] = $wrkflow_id;
              $insert['external_userid_text'] = $external_userid_text;
              $insert['isDraft'] = 0;
              $insert['isActive'] = 1;
              $insert['isDelete'] = 1;
              $insert['createdDate'] = date('Y-m-d H:i:s');

            $wrkflow_historyId = $this->workflow_model->saveWorkflowHistory($insert);
        }
      }

      $timedelays = $this->workflow_model->getTimeDelays($wrkflow_id);
      $datestring = date('Y-m-d H:i:s');
      foreach($timedelays as $timedelay){

                $insert1['wrkflow_id'] = $wrkflow_id;
                $insert1['wrkflow_historyId'] = $wrkflow_historyId;
                $insert1['wrkflow_delay_id'] = $timedelay->timeDelay_workflow_id;
                $insert1['wrkflow_notificationType'] = $timedelay->wrkflow_notificationType;
          if($timedelay->wrkflow_TimeDelayType == 'Designated-Time'){
                $date = date('Y-m-d H:i:s');

                $minutes = $timedelay->wrkflow_designatedMinutes;
                $hours = $timedelay->wrkflow_designatedHours;
                $days = $timedelay->wrkflow_designatedDays;


                $sendtime = explode(' ',$timedelay->wrkflow_send_date);
                $sendtime[1];

                $current = date('Y-m-d', strtotime($datestring . "$days days"));
                $datestring = $current;
                $newDate = explode(' ', $current);
                $newDate[0];
                $type = 'DT';
                $religibleDays = '';
                $time_between = '';


          }else{
                $date = date('Y-m-d H:i:s');

                $minutes = $timedelay->wrkflow_intelligentDeliveryMinutes;
                $hours = $timedelay->wrkflow_intelligentDeliveryHours;
                $days = $timedelay->wrkflow_intelligentDeliveryDays;

                $sendtime = explode(' ',$timedelay->wrkflow_send_date);
                $sendtime[1];

                $current = date('Y-m-d', strtotime($datestring . "$days days"));
                $datestring = $current;
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

                $insert1['wrkflow_time'] = $newDate[0]." ".$sendtime[1];
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
      //die;
  }

  function saveWorkflowHistoryTime(){

      $wrkflow_id = 34;
      $wrkflow_historyId = 1;

      $timedelays = $this->workflow_model->getTimeDelays($wrkflow_id);

      foreach($timedelays as $timedelay){

                $insert['wrkflow_id'] = $wrkflow_id;
                $insert['wrkflow_historyId'] = $wrkflow_historyId;
                $insert['wrkflow_delay_id'] = $timedelay->timeDelay_workflow_id;
          if($timedelay->wrkflow_TimeDelayType == 'Designated-Time'){
                $date = date('Y-m-d H:i:s');

                $minutes = $timedelay->wrkflow_designatedMinutes;
                $hours = $timedelay->wrkflow_designatedHours;
                $days = $timedelay->wrkflow_designatedDays;

                $sendtime = explode(' ',$timedelay->wrkflow_send_date);
                $sendtime[1];

                $current = date('Y-m-d', strtotime($date . "$days days"));
                $newDate = explode(' ', $current);
                $newDate[0];


          }else{
                $date = date('Y-m-d H:i:s');

                $minutes = $timedelay->wrkflow_intelligentDeliveryMinutes;
                $hours = $timedelay->wrkflow_intelligentDeliveryHours;
                $days = $timedelay->wrkflow_intelligentDeliveryDays;

                $sendtime = explode(' ',$timedelay->wrkflow_send_date);
                $sendtime[1];

                $current = date('Y-m-d', strtotime($date . "$days days"));
                $newDate = explode(' ', $current);
                $newDate[0];

          }

                $insert['wrkflow_time'] = $newDate[0]." ".$sendtime[1];
                $insert['isDraft'] = 0;
                $insert['isActive'] = 1;
                $insert['isDelete'] = 0;
                $insert['createdDate'] = date('Y-m-d H:i:s');
                $insert['modifiedDate'] = date('Y-m-d H:i:s');

          //echo '<pre>';
          //print_r($insert); die;

          $this->workflow_model->saveWorkflowHistoryTime($insert);

      }
            //echo '<pre>';
            //print_r($insert); die;
  }

}
