<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once('vendor/autoload.php');

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

class GroupApp extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree', 'cookie', 'salesforce_helper', 'permission_helper', 'permission'));
        $this->load->model(array('administrator_model', 'brand_model', 'user_model', 'groupapp_model', 'permission_model', 'email_model'));
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active == 0) {
            redirect(base_url());
        }
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    function _getpermission() {
        $login = $this->administrator_model->front_login_session();

        if (isset($login->user_id) && isset($login->usertype)) {

            $userid = $login->user_id;
            $usertype = $login->usertype;

            if ($usertype == 9) {

                $allPermision = getAssignPermission($userid);
                //  print_r($data['allPermision']); die;
                return $allPermision;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function addgroup_popup() {
        /* added by sarvesh */
        $login = $this->administrator_model->front_login_session();
        $userPackage = $this->brand_model->getPackagesInfo($login->businessId);
        if (count($userPackage) > 0) {
            $data['countTotalAppGroup'] = $userPackage->totalAppGroup;
        } else {
            $data['countTotalAppGroup'] = 0;
        }
        //Extra AppGroup
        $extraPackage = $this->brand_model->getUserPackageAppGroup($login->businessId);
        if (count($extraPackage) > 0) {
            $data['extraAppGroupQuantity'] = $extraPackage->quantity;
        } else {
            $data['extraAppGroupQuantity'] = 0;
        }
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $data['additional_profit'] = $header['loggedInUser']->additional_profit;
        //print_r($data); exit;
        /*  End added by sarvesh */
        $this->load->view('3.1/addgroup_popup', $data);
    }

    function addGroup() {

        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'groupApp';
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['userid'] = $login->user_id;
            $header['usertype'] = $login->usertype;
            //$header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $businessId = $login->businessId;

            $usertype = $header['loggedInUser']->usertype;
            
            $dataArray = array(
                'domain' => $_POST['domain_name'],
                'tracking_domain' => 'tracking.hurree.co',
                'generate_dkim' => true,
                'shared_with_subaccounts' => true
            );

            $jsonVar = json_encode($dataArray);

            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/sending-domains');
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . SPARKPOSTKEYMAIN));
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $jsonVar);
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
            $response = curl_exec($this->curl);
            //$response = '{"results":{"message":"Successfully Created domain.","dkim":{"public":"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDZNyrnnO\/ysDABuaThPmGtgB4qeF92Bv2GOBP1K4yviD2C3pHr3iEuOLVUyu9iyHGVVW243LuWRq5XizsH9DlhaQMQJdZtGsJWquXb5yxrlHTBnl2MpbkM\/NBgZQ9mW8OKXz7aYE2h84l7E3VFgCF16T7I4RdwtWFS7VMoh9zEdQIDAQAB","selector":"scph0317","headers":"from:to:subject:date","signing_domain":"uber.com"},"domain":"uber.com"}}';
            $responseSparkPostArray = json_decode($response, true); 
            reset($responseSparkPostArray);
                    $first_key = key($responseSparkPostArray); 
                    if ($first_key == 'errors') {
                     echo  0; exit;  
                        
                    }
            
            
            
            
            $updateGroup['app_group_id'] = $insert_id;
            $updateGroup['sparkpost_response'] = $response;
            $this->groupapp_model->updateGroup($updateGroup);


            $arr = json_decode($response);
            $domain = $arr->results->dkim->signing_domain;
            $public = $arr->results->dkim->public;
            $selector = $arr->results->dkim->selector;

            $hostname = $selector . '._domainkey.' . $domain;
            $type = 'TXT';
            $value = 'v=DKIM1; k=rsa; h=sha256;p=' . $public;
            
            
            
            

            $save['app_group_name'] = $_POST['group_name'];
            $save['domain'] = $_POST['domain_name'];
            $save['businessId'] = $businessId;
            $save['domain_createdDate'] = date('YmdHis');
            $save['createdDate'] = date('YmdHis');

            $insert_id = $this->groupapp_model->saveGroup($save);

            $where['app_group_id'] = $insert_id;
            $group = $this->groupapp_model->getGroupDetails($where, '*', '');
            //echo $header['loggedInUser']->additional_profit; die;

            if ($header['loggedInUser']->additional_profit != 1) {
                /* added by sarvesh */
                $userid = $login->user_id;
                $extraPackage = $this->brand_model->getUserPackage($userid);
                if (count($extraPackage) > 0) {
                    if ($extraPackage->quantity != 0) {
                        $update['quantity'] = $extraPackage->quantity - 1;
                    } else {
                        $update['quantity'] = $extraPackage->quantity;
                    }
                    $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                    $this->brand_model->updateExtraPackage($update);
                } else {
                    //Update total campaigns
                    $userPackage = $this->brand_model->getPackagesInfo($businessId);
                    $totalAppGroup = $userPackage->totalAppGroup;
                    if ($totalAppGroup == 0) {
                        $updateTotalAppGroups = $totalAppGroup;
                    } else {
                        $updateTotalAppGroups = $totalAppGroup - 1;
                    }

                    $update = array(
                        'businessId' => $businessId,
                        'totalAppGroup' => $updateTotalAppGroups
                    );
                    $this->brand_model->updateTotalAppGroups($update);
                }
                /* end by sarvesh */
            }

            $cookie = array(
                'name' => 'group',
                'value' => $group->app_group_id . "," . $group->app_group_name,
                'expire' => '86500',
            );
            $this->input->set_cookie($cookie);

            

            $name = ucfirst($login->firstname);
            $this->emailConfig();   //Get configuration of email

            $email_template = $this->email_model->getoneemail('verify_domain');

            $subject = $email_template->subject;
            $subject = str_ireplace('{domain}', $domain, $subject);
            //// MESSAGE OF EMAIL
            $messages = $email_template->message;
            $messages = str_ireplace('{Name}', $name, $messages);
            $messages = str_ireplace('{domain}', $domain, $messages);
            $messages = str_ireplace('{hostname}', $hostname, $messages);
            $messages = str_ireplace('{Type}', $type, $messages);
            $messages = str_ireplace('{Value}', $value, $messages);

            //$message = $hostname .'<br>'.$type.'<br>'.$value;

            $email = $login->email;

            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to($email);
            $this->email->subject($subject);
            $this->email->message($messages);
            $this->email->send();

            echo $insert_id;
        } else {
            redirect(base_url());
        }
    }

    function checkGroupName() {

        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;

        $groupName = $_POST['group_name'];

        $data['businessId'] = $businessId;
        $data['app_group_name'] = $groupName;
        $group = $this->groupapp_model->checkGroupName($data);

        if (count($group) > 0) {

            $exist = 1;
        } else {
            $exist = 0;
        }

        echo $exist;
    }

    function appGroups($groupId = NULL) {

        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'account';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $data['userDetails'] = $this->user_model->getOneUser($login->user_id);
            //delete_cookie('group');
            //echo $this->input->cookie('group',true);
            //die;
            if ($login->usertype == 8) {

                $cookies = $this->input->cookie('group', true);
                if (!empty($cookies)) {
                    $cookie = $this->input->cookie('group', true);
                    $cookie_group = explode(",", $cookie);
                } else {
                    $cookie_group = '';
                }

                //App Groups list in menu
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
                $header['cookie_group'] = $cookie_group;
                $data['groups'] = $this->groupapp_model->getGroups($login->businessId);

                if ($groupId != '') {
                    $data['group'] = $this->groupapp_model->getOneGroup($groupId);
                    $data['groupId'] = $groupId;
                    $data['emailSettings'] = $this->groupapp_model->checkEmailSettinsExist($groupId);

                    $data['androidApps'] = $this->groupapp_model->getAndroidApps($groupId);
                    $data['iosApps'] = $this->groupapp_model->getIOSApps($groupId);

                    $data['countApps'] = count($data['androidApps']) + count($data['iosApps']);

                    $data['dkimData'] = $this->groupapp_model->getOneGroup($groupId);
                } else {
                    $data['groups'] = $this->groupapp_model->getGroups($login->businessId);

                    $groups = $this->groupapp_model->getGroups($login->businessId);
                    if (count($groups) > 0) {
                        $groupId = $groups[0]->app_group_id;
                    } else {
                        $groupId = '';
                    }

                    $data['group'] = $this->groupapp_model->getOneGroup($groupId);
                    $data['groupId'] = $groupId;
                    $data['emailSettings'] = $this->groupapp_model->checkEmailSettinsExist($groupId);
                    $data['androidApps'] = $this->groupapp_model->getAndroidApps($groupId);
                    $data['iosApps'] = $this->groupapp_model->getIOSApps($groupId);

                    $data['countApps'] = count($data['androidApps']) + count($data['iosApps']);
                    $data['dkimData'] = $this->groupapp_model->getOneGroup($groupId);
                }
            } else {

                $groups = $this->groupapp_model->getUserGroups($login->user_id);

                //$data['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
                //$header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
                if (count($groups) > 0) {
                    foreach ($groups as $group) {

                        $groupArray[] = $group->app_group_id;
                    }
                } else {
                    $groupArray = '';
                }
                //App Groups list in menu
                $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);

                $data['groups'] = $this->groupapp_model->getUserGroupData($groupArray);

                if ($groupId != '') {

                    $data['group'] = $this->groupapp_model->getOneGroup($groupId);
                    $data['groupId'] = $groupId;
                    $data['emailSettings'] = $this->groupapp_model->checkEmailSettinsExist($groupId);
                    $data['androidApps'] = $this->groupapp_model->getAndroidApps($groupId);
                    $data['iosApps'] = $this->groupapp_model->getIOSApps($groupId);
                    $data['countApps'] = count($data['androidApps']) + count($data['iosApps']);
                    $data['dkimData'] = $this->groupapp_model->getOneGroup($groupId);
                } else {

                    $data['groups'] = $this->groupapp_model->getUserGroupData($groupArray);

                    $groups = $this->groupapp_model->getUserGroupData($groupArray);
                    if (count($groups) > 0) {
                        $groupId = $groups[0]->app_group_id;
                    } else {
                        $groupId = '';
                    }

                    $data['group'] = $this->groupapp_model->getOneGroup($groupId);
                    $data['groupId'] = $groupId;
                    $data['emailSettings'] = $this->groupapp_model->checkEmailSettinsExist($groupId);
                    $data['androidApps'] = $this->groupapp_model->getAndroidApps($groupId);
                    $data['iosApps'] = $this->groupapp_model->getIOSApps($groupId);
                    $data['countApps'] = count($data['androidApps']) + count($data['iosApps']);
                    $data['dkimData'] = $this->groupapp_model->getOneGroup($groupId);
                }
            }

            //echo '<pre>';
            //print_r($data['group']);

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/app_groups', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function addApp_popup($groupId) {

        $data['group_id'] = $groupId;
        $this->load->view('3.1/add_app', $data);
    }

    function checkAppName() {

        $data['groupId'] = isset($_POST['group_id']) ? $_POST['group_id'] : '';
        $data['appName'] = $_POST['app_name'];
        $app = $this->groupapp_model->checkAppName($data);

        if (count($app) > 0) {

            $exist = 1;
        } else {
            $exist = 0;
        }

        echo $exist;
    }

    function addApp() {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'groupApp';
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['userid'] = $login->user_id;
            $header['usertype'] = $login->usertype;
            $appGroup = 0;
            //$header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $businessId = $login->businessId;

            $usertype = $header['loggedInUser']->usertype;
            $platform = $_POST['platform'];

            if ($platform == 'Android') {
                $appGroup = $this->groupapp_model->getAndroidApps($_POST['group_id']);
            } else if ($platform == 'iOS') {
                $appGroup = $this->groupapp_model->getIOSApps($_POST['group_id']);
            }

            if (count($appGroup) > 0) {
                echo "Only one $platform app allowed in one app group.";
                exit;
            } else {
                $save['app_name'] = $_POST['app_name'];
                if ($_POST['platform'] == 'Android') {
                    $save['app_image'] = '';
                } else {
                    $save['app_image'] = '';
                }
                $save['app_group_id'] = $_POST['group_id'];
                $save['platform'] = $_POST['platform'];
                $save['createdDate'] = date('YmdHis');

                $insert_id = $this->groupapp_model->saveApp($save);

                //echo $insert_id;
                echo "success";
            }
        } else {
            redirect(base_url());
        }
    }

    function saveEmailSettings() {

        $groupId = $_POST['groupId'];
        $feedback_email = $_POST['feedback_email'];
        $displayName = $_POST['displayName'];
        $email_fromEmail = $_POST['email_fromEmail'];
        $email_replyTo = $_POST['email_replyTo'];

        $settingsExist = $this->groupapp_model->checkEmailSettinsExist($groupId);
        if (count($settingsExist) > 0) {
            //Update
            $update['email_settings_id'] = $settingsExist->email_settings_id;
            $update['app_group_id'] = $groupId;
            $update['feedback_email'] = $feedback_email;
            $update['display_name'] = $displayName;
            $update['display_email'] = $email_fromEmail;
            $update['reply_email'] = $email_replyTo;
            $this->groupapp_model->saveEmailSettings($update);
        } else {
            //Insert
            $insert['email_settings_id'] = '';
            $insert['app_group_id'] = $groupId;
            $insert['feedback_email'] = $feedback_email;
            $insert['display_name'] = $displayName;
            $insert['display_email'] = $email_fromEmail;
            $insert['reply_email'] = $email_replyTo;
            $insert['createdDate'] = date('YmdHis');

            $this->groupapp_model->saveEmailSettings($insert);
        }

        echo 'success';
    }

    function editgroup($groupId) {

        $data['group'] = $this->groupapp_model->getOneGroup($groupId);
        $data['groupId'] = $groupId;
        $this->load->view('3.1/editgroup_popup', $data);
    }

    function updateGroup() {
        $login = $this->administrator_model->front_login_session();
         if ($_POST['existDomain'] == 1) { 
        $update['app_group_id'] = $_POST['group_id'];
        $update['app_group_name'] = $_POST['group_name'];
        $update['domain'] = $_POST['domain_name'];
        $this->groupapp_model->updateGroup($update);
         echo 'Success'; exit;
         }
        $cookie = array(
            'name' => 'group',
            'value' => $_POST['group_id'] . "," . $_POST['group_name'],
            'expire' => '86500',
        );
        $this->input->set_cookie($cookie);
        if ($_POST['existDomain'] == 0) {

            $dataArray = array(
                'domain' => $_POST['domain_name'],
                'tracking_domain' => 'tracking.hurree.co',
                'generate_dkim' => true,
                'shared_with_subaccounts' => true
            );

            $jsonVar = json_encode($dataArray);

            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/sending-domains');
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . SPARKPOSTKEYMAIN));
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $jsonVar);
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
            $response = curl_exec($this->curl);
            
            $responseSparkPostArray = json_decode($response, true);
            
            reset($responseSparkPostArray);
                    $first_key = key($responseSparkPostArray);
                    if ($first_key == 'errors') {
                     echo  0; exit;  
                        
                    }

            //$response = '{"results":{"message":"Successfully Created domain.","dkim":{"public":"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDZNyrnnO\/ysDABuaThPmGtgB4qeF92Bv2GOBP1K4yviD2C3pHr3iEuOLVUyu9iyHGVVW243LuWRq5XizsH9DlhaQMQJdZtGsJWquXb5yxrlHTBnl2MpbkM\/NBgZQ9mW8OKXz7aYE2h84l7E3VFgCF16T7I4RdwtWFS7VMoh9zEdQIDAQAB","selector":"scph0317","headers":"from:to:subject:date","signing_domain":"uber.com"},"domain":"uber.com"}}';

            $updateGroup['app_group_id'] = $_POST['group_id'];
            $updateGroup['domain'] = $_POST['domain_name'];
            $updateGroup['sparkpost_response'] = $response;
            $updateGroup['domain_createdDate'] = date('YmdHis');
            $this->groupapp_model->updateGroup($updateGroup);

            $arr = json_decode($response);
            $domain = $arr->results->dkim->signing_domain;
            $public = $arr->results->dkim->public;
            $selector = $arr->results->dkim->selector;

            $hostname = $selector . '._domainkey.' . $domain;
            $type = 'TXT';
            $value = 'v=DKIM1; k=rsa; h=sha256;p=' . $public;

            $name = ucfirst($login->firstname);
            $this->emailConfig();   //Get configuration of email

            $email_template = $this->email_model->getoneemail('verify_domain');

            $subject = $email_template->subject;
            $subject = str_ireplace('{domain}', $domain, $subject);
            //// MESSAGE OF EMAIL
            $messages = $email_template->message;
            $messages = str_ireplace('{Name}', $name, $messages);
            $messages = str_ireplace('{domain}', $domain, $messages);
            $messages = str_ireplace('{hostname}', $hostname, $messages);
            $messages = str_ireplace('{Type}', $type, $messages);
            $messages = str_ireplace('{Value}', $value, $messages);

            //$message = $hostname .'<br>'.$type.'<br>'.$value;

            $email = $login->email;

            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to($email);
            $this->email->subject($subject);
            $this->email->message($messages);
            $this->email->send();
        }
        echo 'Success';
    }

    function deletegroup_popup($groupId) {

        $data['groupId'] = $groupId;
        $this->load->view('3.1/delete_group', $data);
    }

    function deleteGroup() {

        $groupId = $_POST['groupId'];

        $update['app_group_id'] = $groupId;
        $update['isDelete'] = 1;
        $this->groupapp_model->updateGroup($update);

        /* added by sarvesh */
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;
        $extraPackage = $this->brand_model->getUserPackage($userid);

        if (count($extraPackage) > 0) {
            $updatePackage['quantity'] = $extraPackage->quantity + 1;
            $updatePackage['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
            $this->brand_model->updateExtraPackage($updatePackage);
        } else {
            //Update total App groups
            $userPackage = $this->brand_model->getPackagesInfo($businessId);
            $totalAppGroup = $userPackage->totalAppGroup;
            $updateTotalAppGroups = $totalAppGroup + 1;

            $updatePackage = array(
                'businessId' => $businessId,
                'totalAppGroup' => $updateTotalAppGroups
            );
            $this->brand_model->updateTotalAppGroups($updatePackage);
        }
        /* end by sarvesh */
        echo 1;
    }

    function getApp() {

        $appId = $_POST['appId'];
        $app = $this->groupapp_model->getApp($appId);
        echo json_encode($app);
    }

    function saveAndroidImage() {

        $ime = $_POST['pic'];
        $androidAppId = $_POST['androidAppId'];

        if ($size = @getimagesize($ime)) {
            $responseImage = 1;
        } else {
            $responseImage = 0;
        }

        if ($responseImage == 1) {


            $image = explode(';base64,', $ime);
            $size = getimagesize($ime);
            $type = $size['mime'];
            $typea = explode('/', $type);
            $extnsn = $typea[1];
            $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

            $img_cont = str_replace(' ', '+', $image[1]);
            //$img_cont=$image[1];
            $data = base64_decode($img_cont);
            $im = imagecreatefromstring($data);
            $filename = time() . '.' . $extnsn;
            //echo $im; exit;
            /* $thumbnailpath = 'upload/apps/' . $filename;
              $mediumpath = 'upload/apps/' . $filename; */
            $fullpath = 'upload/apps/' . $filename;


            // code for upload image in thumbnail folder
            /* imagealphablending($im, false);
              imagesavealpha($im, true); */

            // code for upload image in medium folder
            /* imagealphablending($im, false);
              imagesavealpha($im, true); */

            // code for upload image in full folder
            imagealphablending($im, false);
            imagesavealpha($im, true);


            if (in_array($extnsn, $valid_exts)) {
                $quality = 0;
                if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                    $quality = round((100 - $quality) * 0.09);
                    /* $resp = imagejpeg($im, $thumbnailpath,$quality);
                      $resp = imagejpeg($im, $mediumpath,$quality); */
                    $resp = imagejpeg($im, $fullpath, $quality);
                } else if ($extnsn == 'png') {
                    /* $resp = imagepng($im, $thumbnailpath);
                      $resp = imagepng($im, $mediumpath); */
                    $resp = imagepng($im, $fullpath);
                } else if ($extnsn == 'gif') {
                    /* $resp = imagegif($im, $thumbnailpath);
                      $resp = imagegif($im, $mediumpath); */
                    $resp = imagegif($im, $fullpath);
                }
            }

            // code for update user image
            $login = $this->administrator_model->front_login_session();



            $userid = $login->user_id;
            $businessId = $login->businessId;

            $update['app_group_apps_id'] = $androidAppId;
            $update['app_image'] = $filename;

            $resp = $this->groupapp_model->updateAndroidApp($update);
            echo $resp;
        } else {
            echo 'Please upload another image';
        }
    }

    function saveAndroidApp() {

        $update['app_group_apps_id'] = $_POST['androidAppId'];
        $update['app_name'] = $_POST['android_app_name'];
        $update['package_name'] = $_POST['android_package_name'];
        $update['GCM'] = $_POST['android_gcm'];
        $update['app_download_url'] = $_POST['android_app_download_url'];
        $this->groupapp_model->updateAndroidApp($update);

        echo 'Success';
    }

    function saveIOSApp() {

        if (@$_FILES['upload']['size'] > 0) {
            //print_r($_FILES);
            $uploads_dir = 'upload/apps/files';

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $tmp_name = $_FILES["upload"]["tmp_name"];
            $name = mktime() . $_FILES["upload"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");
            $ext = strtolower(pathinfo($_FILES["upload"]["name"], PATHINFO_EXTENSION));

            $app_image = $name;
        } else {
            $app_image = '';
        }

        $update['app_group_apps_id'] = $_POST['iosAppId'];
        $update['app_name'] = $_POST['ios_app_name'];
        $update['certificateType'] = $_POST['certificateType'];
        $update['app_download_url'] = $_POST['ios_app_download_url'];

        if ($app_image != '') {
            $update['fileName'] = $app_image;
        }
        $this->groupapp_model->updateAndroidApp($update);

        echo 'Success';
    }

    function setGroup() {

        $groupId = $_POST['groupId'];
        $where['app_group_id'] = $groupId;
        $group = $this->groupapp_model->getGroupDetails($where, '*', '');

        $cookie = array(
            'name' => 'group',
            'value' => $group->app_group_id . "," . $group->app_group_name,
            'expire' => '86500',
        );
        $this->input->set_cookie($cookie);

        echo 1;
    }

    function choose_pushPlatform() {

        $this->load->view('3.1/choose_pushPlatform');
    }

    function getOneGroup() {
        $groupId = $_POST['groupId'];
        $app = $this->groupapp_model->getAppWithPushNotification($groupId);
        if (count($app) > 0) {
            echo 1;
        } else {
            echo 0;
        }
    }

    function getOneGroupIOS() {
        $groupId = $_POST['groupId'];
        $app = $this->groupapp_model->getAppWithPushNotificationIOS($groupId);
        if (count($app) > 0) {
            echo 1;
        } else {
            echo 0;
        }
    }

    function deleteapp_popup($app_group_apps_id) {

        $data['app_group_apps_id'] = $app_group_apps_id;
        $this->load->view('3.1/delete_app', $data);
    }

    function deleteApp() {

        $app_group_apps_id = $_POST['app_group_apps_id'];

        $update['app_group_apps_id'] = $app_group_apps_id;
        $update['isDelete'] = 1;

        $this->groupapp_model->updateApp($update);
        echo 1;
    }

    function getGroupKey() {

        $groupId = $_POST['groupId'];
        $group = $this->groupapp_model->getOneGroup($groupId);
        echo $group->app_group_key;
    }

    function updateSuccessAppPopUp() {
        $this->load->view('3.1/update_success_app');
    }

    public function emailConfig() {
        $this->load->library('email');   //// LOAD LIBRARY
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'smtp.sparkpostmail.com'; //auth.smtp.1and1.co.uk
        $config['smtp_port'] = 587;
        $config['smtp_user'] = 'SMTP_Injection'; //support@hurree.co.uk
        $config['smtp_pass'] = SPARKPOSTKEYSUB;
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html

        $this->email->initialize($config);
    }

    function verifyDomain() {
        $str = '';
        $status = '';
        $groupId = $_POST['groupId'];
        $group = $this->groupapp_model->getOneGroup($groupId);
        $verifyResponse = $group->verify_response;
        $domain = $group->domain;
        $dataArray = array(
            'dkim_verify' => true,
            'postmaster_at_verify' => true,
            'abuse_at_verify' => true
        );
        $jsonVar = json_encode($dataArray);
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/sending-domains/' . $domain . '/verify');
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . SPARKPOSTKEYMAIN));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $jsonVar);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($this->curl);
        //$response = '{"results":{"ownership_verified":1,"spf_status":"unverified","dns":{"dkim_error":"DNS DKIM query error: dns lookup failed for scph0317._domainkey.qsstechnosoft.com: NXDOMAIN"},"dkim_status":"invalid","compliance_status":"pending","verification_mailbox_status":"unverified","abuse_at_status":"unverified","postmaster_at_status":"unverified"}}';
        //$response = '{"errors":[{"message":"Exceed Sending Limit (hourly)","code":"2101"}]}';
        $str .= $verifyResponse;
        $str .= $response;

        $updateGroup['app_group_id'] = $groupId;
        $updateGroup['verify_response'] = $str;
        $this->groupapp_model->updateGroup($updateGroup);
        $result = json_decode($response, true);
        reset($result);
        $first_key = key($result);
        switch ($first_key) {
            case "results":
                if ($result['results']['ownership_verified']) {
                    $sparkPostArray = array(
                        "name" => $domain,
                        "key_label" => "API Key for $domain",
                        "key_grants" => array(
                            "0" => 'smtp/inject',
                            "1" => 'sending_domains/manage',
                            "2" => 'message_events/view',
                            "3" => 'suppression_lists/manage',
                            "4" => 'tracking_domains/view',
                            "5" => 'tracking_domains/manage',
                        ),
                        "key_valid_ips" => array(
                        ),
                        "ip_pool" => ''
                    );
                    $sparkPostJson = json_encode($sparkPostArray);
                    //$sparkpostJson ='{"name":"Sparkle Ponies","key_label":"API Key for Sparkle Ponies Subaccount","key_grants":["smtp/inject","sending_domains/manage","message_events/view","suppression_lists/manage","tracking_domains/view","tracking_domains/manage"],"key_valid_ips":[],"ip_pool":""}';

                    $ch = curl_init();
                    $url = 'https://api.sparkpost.com/api/v1/subaccounts';
                    $ch = curl_init($url);
                    # Setup request to send json via POST.
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $sparkPostJson);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: ' . SPARKPOSTKEYMAIN . '', 'Content-Type:application/json'));
                    # Return response instead of printing.
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    # Send request.
                    $responseSparkPost = curl_exec($ch);
                    curl_close($ch);
                    # Print response.
                    //$responseSparkPost = '{"results":{"key":"c2239d2aafda9211a2aad1a2e6261c22d3374c75","short_key":"c223","label":"API Key for Sparkle Ponies Subaccount","subaccount_id":2}}';
                    $responseSparkPostArray = json_decode($responseSparkPost, true);
                    reset($responseSparkPostArray);
                    $first_key_subaccount = key($responseSparkPostArray);
                    if ($first_key_subaccount == 'results') {
                        $update['app_group_id'] = $groupId;
                        $update['domain_verify'] = 1;
                        $update['sparkpost_key'] = $responseSparkPostArray['results']['key'];
                        $update['sparkpost_key_json'] = $responseSparkPost;
                        $this->groupapp_model->updateGroup($update);
                        $status = 1;
                    } else {

                        $status = 0;
                    }
                }
                break;
            case "errors":
                $status = 0;
                break;
            default:
                $status = 0;
                break;
        }
        echo $status;
    }

    function checkDomain() {
        $domain = $_POST['domain'];
        $groupDomain = $this->groupapp_model->checkDomain($domain);
        if (count($groupDomain) > 0) {
            echo 1;
        } else {
            echo 0;
        }
    }

}
