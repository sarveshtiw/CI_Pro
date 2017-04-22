<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once('vendor/autoload.php');

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AppUser extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree', 'cookie', 'salesforce_helper', 'permission_helper', 'permission', 'hubspot_helper'));

        $this->load->library(array('form_validation', 'pagination'));

        $this->load->model(array('user_model', 'webhook_model', 'brand_model', 'payment_model', 'administrator_model', 'groupapp_model', 'notification_model', 'country_model', 'permission_model', 'location_model', 'email_model', 'campaign_model', 'reward_model', 'businessstore_model', 'offer_model', 'geofence_model', 'role_model', 'contact_model', 'hubSpot_model', 'crosschannel_model', 'hurreebrand_model', 'workflow_model', 'inapp_model', 'referfriend_model', 'paypalprofile_model','social_model','lists_model'));
        $header['allPermision'] = $this->_getpermission();
        emailConfig();

           $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    public function _getpermission() {
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

    public function index() {
        // Define Variables and Array
        $data['roleName'] = array();
        $header['allPermision'] = array();

        $login = $this->administrator_model->front_login_session();
        // echo '<pre>'; print_r($login ); die;
        if ($login->active != 0) {
            $header['page'] = 'account';
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

            $header['cookie_group'] = ''; //$cookie_group;
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

            $data['userDetails'] = $this->user_model->getOneUser($login->user_id);
            $data['masterData'] = $this->user_model->getMasterUserData($login->businessId);

            if ($login->usertype == 8) {
                $data['roleName'] = 'App Admin';
            } elseif ($login->usertype == 10) {
                $data['roleName'] = 'Developer';
            } else {
                $roleName = $this->permission_model->userAppGroupRoleNames($login->user_id);
                //echo '<pre>';
                //print_r($roleName); die;
                if (count($roleName) > 0) {
                    $userRoles = '';
                    foreach ($roleName as $role) {
                        $userRoles[] = $role->roleName;
                    }
                    $data['roleName'] = $userRoles;
                }
            }

            $data['businessName'] = $login->businessName;
            // get all notifications start
            $select = "*";
            $arr_notification['pushMsg.active'] = 1;
            $arr_notification['pushMsg.isDelete'] = 0;
            $arr_notification['user_id'] = $login->user_id;
            $data['records'] = $this->notification_model->getNotificationCreatedByAdmin($arr_notification, $row = 1, $select = '*', '', '', $totalrecords = 1);   //// Get Total No of Records in Database
            //print_r($data['records']); exit;
            $perpage = 20;

            $config['base_url'] = base_url() . 'index.php/appUser/notificationfull/';
            $config['total_rows'] = count($data['records']);
            $config['per_page'] = $perpage;
            $config['uri_segment'] = 3;
            $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $limit = $config['per_page'];
            $order_by['createdDate'] = 'DESC';
            $select = ''; //' CONCAT("@", UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username , firstname, lastname, image, user_Id, image , actionString , action,  notification.createdDate as postedDate, notification.*, ( case when (usertype = 1 or usertype = 4) THEN  CONCAT_WS( " ", users.firstname, users.lastname ) ELSE  users.businessName END ) as name, challenge.game_id';
            $Notificationrecords = $this->notification_model->getNotificationCreatedByAdmin($arr_notification, $row = 1, $select, $page, $limit);

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
            }
            //print_r(  $data['userDetails'] ); exit;
            $data['per_page'] = 1;
            $data['notifications'] = $Notificationrecords;
            $data['countries'] = $this->country_model->get_countries();
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/editProfileBrandUser', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect('home/signup');
        }
    }

    public function saveProfile() {

        $login = $this->administrator_model->front_login_session();
        $arr['user_Id'] = $login->user_id;
        $usertype = $_POST['usertype'];
        $arr['firstname'] = $_POST['firstname'];
        $arr['lastname'] = $_POST['lastname'];
        if (!empty($_POST['newPassword'])) {
            $arr['password'] = md5($_POST['newPassword']);
        }
        $arr['contactNumber'] = $_POST['contactNumber'];
        $arr['bio'] = $_POST['bio'];

        $arr['email'] = $_POST['email'];
        if ($usertype == 8) {
            $arr['country'] = $_POST['country'];
        }
        $result = $this->user_model->updateProfile($arr, $login->businessId);
        //echo $this->db->last_query(); die;
        echo 1;
        exit;
    }

    function savedeveloperLogo() {

        $ime = $_POST['pic'];
        if ($ime != '') {
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
            //$thumbnailpath = 'upload/profile/thumbnail/' . $filename;
            //$mediumpath = 'upload/profile/medium/' . $filename;
            $fullpath = 'upload/profile/developerlogo/' . $filename;


            // code for upload image in thumbnail folder
            imagealphablending($im, false);
            imagesavealpha($im, true);

            // code for upload image in medium folder
            imagealphablending($im, false);
            imagesavealpha($im, true);

            // code for upload image in full folder
            imagealphablending($im, false);
            imagesavealpha($im, true);


            if (in_array($extnsn, $valid_exts)) {
                $quality = 0;
                if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                    $quality = round((100 - $quality) * 0.09);
                    //$resp = imagejpeg($im, $thumbnailpath,$quality);
                    //$resp = imagejpeg($im, $mediumpath,$quality);
                    $resp = imagejpeg($im, $fullpath, $quality);
                } else if ($extnsn == 'png') {
                    //$resp = imagepng($im, $thumbnailpath);
                    //$resp = imagepng($im, $mediumpath);
                    $resp = imagepng($im, $fullpath);
                } else if ($extnsn == 'gif') {
                    //$resp = imagegif($im, $thumbnailpath);
                    //$resp = imagegif($im, $mediumpath);
                    $resp = imagegif($im, $fullpath);
                }
            }
            // code for update user image
            $login = $this->administrator_model->front_login_session();



            $userid = $login->user_id;
            $businessId = $login->businessId;
            $update = array(
                'user_Id' => $userid,
                'developerLogo' => $filename,
            );
            $this->user_model->updateProfile($update, $businessId);
        } else {
            $login = $this->administrator_model->front_login_session();

            $userid = $login->user_id;
            $businessId = $login->businessId;
            $update = array(
                'user_Id' => $userid,
                'developerLogo' => '',
            );
            $this->user_model->updateProfile($update, $businessId);
            $resp = 1;
        }
        return $resp;
    }

    public function permission() {
        $login = $this->administrator_model->front_login_session();
         if ($login->active != 0){
          $header['page'] = 'permission';
        $header['userid'] = $login->user_id;
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $header['usertype'] = $login->usertype;
        $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

        //App Groups list in menu
        $header['groups'] = $this->groupapp_model->getGroups($login->businessId);

        $cookies = $this->input->cookie('group', true);
        if (!empty($cookies)) {
            $cookie = $this->input->cookie('group', true);
            $cookie_group = explode(",", $cookie);
        } else {
            $cookie_group = '';
        }

        $header['cookie_group'] = $cookie_group;

        $arr['roles.businessId'] = $login->businessId;
        $arr['roles.isDelete'] = 0;

        $roles = $this->role_model->getRoles($arr);
        //echo '<pre>'; print_r($roles); exit;
        foreach ($roles as $role) {
            $permissionIds = $role->permissionId;
            $permissionIds = explode(',', $permissionIds);
            $permissions = $this->permission_model->getPermissionName($permissionIds);

            $newpermission = array();
            foreach ($permissions as $permission) {
                $newpermission[] = trim($permission->permissionName);
            }
            $newpermission1 = implode(', ', $newpermission);

            $role->permissions = $newpermission1;

            // Get role is assign to any app sub suer or not
            $select = 'id';
            $where['roleid'] = $role->role_id;
            $assignedRoleToUser = $this->groupapp_model->getAssignedRols($select, $where);
            if (count($assignedRoleToUser) > 0) {
                $role->canDelete = 0;
            } else {
                $role->canDelete = 1;
            }
        }

        $data['roles'] = $roles;

        $this->load->view('3.1/inner_headerBrandUser', $header);
        $this->load->view('3.1/permissionListing', $data);
        $this->load->view('3.1/inner_footerBrandUser');
         }else{redirect('home/signup');}


    }

    public function createRole() {
        $where['isDelete'] = 0;
        $where['version'] = "3.1";
        $data['permissions'] = $this->permission_model->getPermissions($where);
        //echo $this->db->last_query(); die;
        $this->load->view('3.1/addPermission', $data);
    }

    public function saveRole() {

        $login = $this->administrator_model->front_login_session();
        $createdBy = $login->user_id;
        $permissions = $_POST['permissions'];
        $permissions = implode(',', $permissions);

        $roleName = $_POST['roleName'];
        $permissionIds = $permissions;
        $businessId = $login->businessId;
        $result = $this->role_model->saveRole($roleName, $permissionIds, $createdBy, $businessId);
        if ($result) {
            echo '1';
        } else {
            echo '0';
        }
    }

    // get only one permission to edit
    public function editRole($roleId = false, $createdBy = false) {

        if (!empty($_POST)) {

            $login = $this->administrator_model->front_login_session();
            $createdBy = $login->user_id;
            $permissions = $_POST['permissions'];
            $roleId = $_POST['roleId'];
            $permissions = implode(',', $permissions);

            $roleName = $_POST['roleName'];
            $permissionIds = $permissions;

            $result = $this->role_model->editRole($roleName, $permissionIds, $createdBy, $roleId);
            if ($result) {
                echo '1';
            } else {
                echo '0';
            }
        } else {

            $arr['permissionRoleConnection.roleId'] = $roleId;
            $arr['permissionRoleConnection.createdBy'] = $createdBy;
            $roleDetails = $this->role_model->getRole($arr);

            $permissionIds = $roleDetails->permissionId;
            $permissionIds = explode(',', $permissionIds);
            $permission = $this->permission_model->getPermission($permissionIds);
            $roleDetails->permission = $permission;

            $data['roleDetails'] = $roleDetails;
            $where['isDelete'] = 0;
            $where['version'] = '3.1';
            $data['permissions'] = $this->permission_model->getPermissions($where);
//     		/echo $this->db->last_query(); die;
            $this->load->view('3.1/addPermission', $data);
        }
    }

    public function deleteRole($roleId = false) {
        if (!empty($_POST)) {
            $where['role_id'] = $_POST['id'];
            $result = $this->role_model->deleteRole($where);
            echo $result;
            exit;
        } else {
            $data['deleteable'] = $_GET['canDelete'];

            $data['roleId'] = $roleId;
            $this->load->view('3.1/deletePermission', $data);
        }
    }

    function deleteRoleError() {
        $this->load->view('3.1/roleDeleteError');
    }

    public function userListing() {
        $login = $this->administrator_model->front_login_session();
        ///echo $login->businessId; exit;
        if ($login->active != 0) {
            $header['page'] = 'userlisting';
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

            $where['users.businessId'] = $login->businessId;
            $where['users.createdBy'] = $login->user_id;
            $where['users.isDelete'] = 0;
            //$where['business_branch.active'] = 1;
            $data['users'] = $this->user_model->getUsers($where);
            //echo '<pre>'; print_r($data['users']); die;

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/userListing', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect('home/signup');
        }
    }

    public function addUserPopUp() {

        $login = $this->administrator_model->front_login_session();

        $userid = $login->user_id;
        $businessId = $login->businessId;

        $group['businessId'] = $businessId;
        $group['isDelete'] = 0;
        $data['group'] = $this->groupapp_model->getGroupDetails($group, "*", "1");
// 		echo '<pre>';
//     	print_r($data['group']); die;

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);
        //print_r($data['permissions']); die;
        $this->load->view('3.1/add_user', $data);
    }

    public function addmoreGroup() {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;

        $data['counter'] = $_POST['counter'];

        $group['businessId'] = $businessId;
        $group['isDelete'] = 0;
        $data['group'] = $this->groupapp_model->getGroupDetails($group, "*", "1");

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);

        echo $this->load->view('3.1/addMoreGroup', $data);
    }

    public function RandomStringCreateUsername() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function saveUser() {

        $login = $this->administrator_model->front_login_session();
        //print_r($login);

        $masterAdmin = $login->user_id;
        $businessId = $login->businessId;
        $businessName = $login->businessName;
        $CreatedBy = $this->user_model->getOneUser($masterAdmin);
//     	print_r($CreatedBy);
        $masterAdminName = $CreatedBy->firstname . " " . $CreatedBy->lastname;

        $businessUserFirstName = $_POST['firstname'];
        $businessUserEmail = trim($_POST['email']);

        //print_r($_POST); die;

        $rand = $this->RandomStringCreateUsername();

        generateReferalCode:
        //generate referal code
        $referralCode =  $this->generateReferralCode();
        //check referal code in database
         $referralCount = $this->user_model->checkReferralCodeExist($referralCode);
         if($referralCount >0){
          goto generateReferalCode;
         }

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
        $save['referral_code']= $referralCode;
//     	print_r($save); die;
        $last_insertId = $this->user_model->insertsignup($save);
        //echo $this->db->last_query(); die;



        if ($_POST['usertype'] == 9) {

            $groups = $_POST['group'];
            $i = 0;
            $j = 0;
            foreach ($groups as $group) {

                //print_r($location);
                //echo $entry-1;;
                //die;
                $entry = count($group);
                for ($i = 1; $i <= $entry - 1; $i++) {

                    $insert['userid'] = $last_insertId;
                    $insert['app_group_id'] = $group[0];
                    $insert['roleid'] = $group[$i];

                    //print_r($insert);

                    $this->groupapp_model->assignGroup($insert);
                    $j++;
                }
            }
        }

        $link = base_url() . 'appUser/createUsername/' . $rand;

        $url = '<a style="color:rgb(43,170,223)" href="' . $link . '" target="_blank">Create account here</a>';


        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('app_user_signup');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;

        //// replace strings from message
        $messages = str_replace('{BusinessUserFirstName}', ucfirst($businessUserFirstName), $messages);
        $messages = str_replace('{MasterAdminName}', $masterAdminName, $messages);
        $messages = str_replace('{BusinessUserEmail}', $businessUserEmail, $messages);
        $messages = str_replace('{createUsername}', $url, $messages);

        //// FROM EMAIL
        //$this->email->from($email_template->from_email, 'Hurree');
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($businessUserEmail);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
        //	echo $this->email->print_debugger();
        echo 1;
    }

    public function createUsername() {

        $usernameToken = $this->uri->segment(3);

        $data['username_create_token'] = $usernameToken;

        $arr_user['username_create_token'] = $usernameToken;
        $arr_user['active'] = 1;
        $user = $this->user_model->getOneUserDetails($arr_user, '*');

        if (count($user) > 0) {
            $_SESSION['hurree-business'] = 'access';
            $this->load->view('3.1/create_username', $data);
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
        $userDetails = $this->user_model->getUser($session_details->user_Id);

        if (count($userDetails) > 0) {
            if (isset($userDetails->active) && $userDetails->active == 1) {
                $this->session->set_userdata("logged_in", $userDetails);
                $_SESSION['hurree-business'] = 'access';
                echo 1;
                exit;
                //$sess = $this->session->userdata('logged_in');
            } else {
                echo "failed";
                exit;
            }
        } else {
            echo "failed";
            exit;
        }

        //echo $session_details->user_Id; print_r($loginStatus); exit;
        //
      //  print_r($userDetails); exit;
        //}
        //unset($_SESSION['hurree-business']);
    }

    public function userListingResponse() {
        $login = $this->administrator_model->front_login_session();
//     	$login = (object) array(
//     			"active" => 1,
//     			"usertype" => 8,
//     			"user_id" => 434,
//     			"businessId" => 434
//     	);
        if ($login->active != 0) {

            $where['users.businessId'] = $login->businessId;
            $where['users.createdBy'] = $login->user_id;
            $where['users.isDelete'] = 0;
            $data2['users'] = $this->groupapp_model->getUsers($where, $login->usertype);
            //	print_r($data2['users']); die;
            $data1 = array();

            for ($i = 0; $i < count($data2['users']); $i++) {

                $result = $this->user_model->getOneUser($data2['users'][$i]['user_Id']);
                //print_r($result ); die;

                if ($data2['users'][$i]['usertype'] == 8) {
                    $data2['users'][$i]['type'] = 'App Admin';
                }
                if ($data2['users'][$i]['usertype'] == 9) {
                    $data2['users'][$i]['type'] = 'App Sub User';
                }
                if ($login->usertype == 8 && !empty($result->createdBy)) {
                    $data2['users'][$i]['action'] = $data2['users'][$i]['action'];
                } else if ($login->usertype == 8 && empty($result->createdBy)) {
                    $data2['users'][$i]['action'] = '';
                } else if ($login->usertype == 9) {
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
                // print_r($data1); exit;
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

    public function editUserPopUp($userid) {

        $data['user'] = $this->user_model->getOneUser($userid);

        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;

        $group['businessId'] = $businessId;
        $group['isDelete'] = 0;
        $data['group'] = $this->groupapp_model->getGroupDetails($group, "*", "1");

        $where['businessId'] = $businessId;
        //$where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);

        $data['UserGroup'] = $this->groupapp_model->getUserGroup($userid);
        // echo $businessId; exit;
        //print_r($data['UserGroup']);
        //  print_r($data['permissions']); exit;

        $this->load->helper('permission');

        $this->load->view('3.1/edit_user', $data);
    }

    public function editUser() {

        $login = $this->administrator_model->front_login_session();
        $modifiedBy = $login->user_id;
        //print_r($_POST);
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
        $this->groupapp_model->deleteGroup($_POST['userid']);
        if ($_POST['usertype'] == 9) {

            $groups = $_POST['group'];
            $i = 0;
            $j = 0;
            foreach ($groups as $group) {

                $entry = count($group);
                for ($i = 1; $i <= $entry - 1; $i++) {

                    $insert['userid'] = $last_insertId;
                    $insert['app_group_id'] = $group[0];
                    $insert['roleid'] = $group[$i];
                    //print_r($insert);

                    $this->groupapp_model->assignGroup($insert);
                    $j++;
                }
            }
        }

        echo 1;
    }

    public function editAddmoregroup() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $businessId = $login->businessId;

        $data['counter'] = $_POST['counter'];

        $group['businessId'] = $businessId;
        $group['isDelete'] = 0;
        $data['group'] = $this->groupapp_model->getGroupDetails($group, "*", "1");

        $where['businessId'] = $businessId;
        $where['isDelete'] = 0;
        $data['permissions'] = $this->permission_model->getPermissionsForAddUser($where);

        echo $this->load->view('3.1/editaddmoreGroups', $data);
    }

    public function deleteUserPopUp($userid) {

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

    public function emailConfig() {
        $this->load->library('email');   //// LOAD LIBRARY
          $config['protocol'] = 'smtp';
          $config['smtp_host'] = 'smtp.sparkpostmail.com';//auth.smtp.1and1.co.uk
          $config['smtp_port'] = 587;
          $config['smtp_user'] = 'SMTP_Injection';//support@hurree.co.uk
          $config['smtp_pass'] = SPARKPOSTKEYSUB;
          $config['charset'] = 'utf-8';
          $config['newline'] = "\r\n";
          $config['mailtype'] = 'html'; // or html

          $this->email->initialize($config);
    }

    /* ================================================================= */

    function campaigns($groupId = false) {

        $login = $this->administrator_model->front_login_session();

        $this->session->unset_userdata("pushCampaignPagination");

        if ($login->active != 0) {
            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);

                $businessGroups = $this->groupapp_model->getGroups($login->businessId);
                if (count($businessGroups) > 0) {
                    $app_g = $app_g2 = $app_g3 = array();
                    foreach ($businessGroups as $businessGroup) {
                        $data['groupApps'] = $this->groupapp_model->getGroupsWithAndroid($login->businessId);
                        //echo '<pre>'; print_r($data['groupApps']); exit;
                        if (count($data['groupApps']) > 0) {
                            foreach ($data['groupApps'] as $groups) {
                                if (!in_array($groups->app_group_id, $app_g)) {
                                    $app_name = $groups->app_group_name;
                                    if (!empty($groups->app_image)) {
                                        $image = base_url() . "upload/apps/" . $groups->app_image;
                                    } else {
                                        $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
                                    }
                                    array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
                                    if (!empty($groups->app_image)) {
                                        $app_name = $groups->app_group_name;
                                        if (!empty($groups->app_image)) {
                                            $image = base_url() . "upload/apps/" . $groups->app_image;
                                        } else {
                                            $image = base_url() . "assets/template/frontend/img/1466507439.png";
                                        }
                                        array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
                                        //echo $app_name; exit;
                                        //  $app_g = $app_g;
                                    }
                                }
                            }
                            $app_g3 = $app_g + $app_g2;
                            $app_g4 = $app_g5 = array();
                            foreach ($app_g3 as $groups) {
                                if (!in_array($groups['app_group_id'], $app_g5)) {
                                    $app_name = $groups['app_group_name'];
                                    $image = $groups['image'];
                                    //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
                                    array_push($app_g5, $groups['app_group_id']);
                                    array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
                                }
                            }
                            $data['groupApps'] = $app_g4;
                            //echo '<pre>'; print_r($data['groupApps']); exit;
                        }
                        //echo '<pre>';
                        //print_r($data['groupApps']); die;
                    }
                }
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

                $data['groupApps'] = $this->groupapp_model->getAppUserGroupsWithImages($groupArray);
                $app_g = $app_g2 = $app_g3 = array();
                if (count($data['groupApps']) > 0) {
                    foreach ($data['groupApps'] as $groups) {
                        if (!in_array($groups->app_group_id, $app_g)) {
                            $app_name = $groups->app_group_name;
                            if (!empty($groups->app_image)) {
                                $image = base_url() . "upload/apps/" . $groups->app_image;
                            } else {
                                $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
                            }
                            array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
                            if (!empty($groups->app_image)) {
                                $app_name = $groups->app_group_name;
                                if (!empty($groups->app_image)) {
                                    $image = base_url() . "upload/apps/" . $groups->app_image;
                                } else {
                                    $image = base_url() . "assets/template/frontend/img/1466507439.png";
                                }
                                array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
                                //echo $app_name; exit;
                                //  $app_g = $app_g;
                            }
                        }
                    }
                    $app_g3 = $app_g + $app_g2;
                    $app_g4 = $app_g5 = array();
                    foreach ($app_g3 as $groups) {
                        if (!in_array($groups['app_group_id'], $app_g5)) {
                            $app_name = $groups['app_group_name'];
                            $image = $groups['image'];
                            //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
                            array_push($app_g5, $groups['app_group_id']);
                            array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
                        }
                    }
                    $data['groupApps'] = $app_g4;
                    //echo '<pre>'; print_r($data['groupApps']); exit;
                }
            }

            //if(!empty($groupId)){
            //  $push_campaigns = $this->brand_model->getCampaignsByAppGroupId($login->businessId,$groupId);
            //}else{
            //
      //}
            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->brand_model->getPushCampaignsByBusinessId($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/campaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;


                $data['push_campaigns'] = $this->brand_model->getPushCampaignsByBusinessId($login->businessId, $data['page'], $config['per_page']);
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->campaign_model->getPushCampaigns($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/campaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $data['push_campaigns'] = $this->campaign_model->getPushCampaigns($AppUserCampaigns, $data['page'], $config['per_page']);
            }
            //print_r($data['push_campaigns']); die;
            $data['statuscount'] = count($data['push_campaigns']);
            $data['noofcampaigns'] = $config['per_page'];
            $data["businessId"] = $businessId;
            //$push_campaigns = $this->brand_model->getPushCampaignsByBusinessId($login->businessId);

            $data['groupId'] = $groupId;
            //$data['push_campaigns'] = $push_campaigns;//	$header['groups']

            /* if($groupId != ''){
              $data['campaign'] = $this->campaign_model->getCampaign($groupId);
              //echo '<pre>';
              //print_r($campaign); die;
              }else{
              $data['campaign'] = $this->campaign_model->getCampaign('');
              } */

            //Check User have default Campaign package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);

            if (count($userPackage) > 0) {
                $data['defaultAndroidCampaign'] = $userPackage->androidCampaign;
                $data['defaultIosCampaign'] = $userPackage->iOSCampaign;
            } else {
                $data['defaultAndroidCampaign'] = 0;    //countTotalCampaign
                $data['defaultIosCampaign'] = 0;
            }

            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

            if (count($extraPackage) > 0) {
                $data['extraAndroidCampaign'] = $extraPackage->androidCampaign;
                $data['extraIosCampaign'] = $extraPackage->iOSCampaign;
            } else {
                $data['extraAndroidCampaign'] = 0;
                $data['extraIosCampaign'] = 0;   //extraCampaignQuantity
            }

            if ($data['extraAndroidCampaign'] === 'unlimited') {
                $data['totalAndroidCampaign'] = 'unlimited';
            } else {
                $data['totalAndroidCampaign'] = $data['defaultAndroidCampaign'] + $data['extraAndroidCampaign'];
            }

            if ($data['extraIosCampaign'] === 'unlimited') {
                $data['totaliOSCampaign'] = 'unlimited';
            } else {
                $data['totaliOSCampaign'] = $data['defaultIosCampaign'] + $data['extraIosCampaign'];
            }


            if (isset($cookie_group[0])) {
                $iOSApp = $this->campaign_model->getIosAppImage($cookie_group[0]);
                if (count($iOSApp) > 0) {
                    if($iOSApp->app_image != ''){
                        $data['iOSAppImage'] = base_url() . "upload/apps/" . $iOSApp->app_image;
                    }else{
                        $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
                    }

                } else {
                    $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
                }
            } else {
                $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
            }
            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }

            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }

            if(isset($cookie_group[0])){
                $androidCredentials = $this->groupapp_model->getAppWithPushNotification($cookie_group[0]);
                $data['androidCredentials'] = count($androidCredentials);

                $iosCredentials = $this->groupapp_model->getAppWithPushNotificationIOS($cookie_group[0]);
                $data['iosCredentials'] = count($iosCredentials);
            }


            $data['additional_profit'] = $header['loggedInUser']->additional_profit;

            $data['usertype'] = $login->usertype;
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/campaigns', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function pushCampaignListPagination() {


        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];

            $newStatusCount = $_POST["newStatusCount"];

            $this->session->set_userdata("pushCampaignPagination", $newStatusCount);

            $max_status_id = @$_POST['status_id'];

            $businessId = $_POST['businessId'];

            $start = $statuscount;
            if ($header['login']->usertype == 8) {
                $data['push_campaigns'] = $this->brand_model->getPushCampaignsByBusinessId($businessId, $start, $noofStatus);
            }
            if ($header['login']->usertype == 9) {

                $groups = $this->campaign_model->getUserGroups($header['login']->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $data['push_campaigns'] = $this->campaign_model->getPushCampaigns($AppUserCampaigns, $start, $noofStatus);
            }

            if (count($data['push_campaigns']) > 0) {
                $this->load->view('3.1/addmorecampaigns', $data);
            }
        } else {

            redirect(base_url());
        }
    }

    function emailCampaignListPagination() {

        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];

            $newStatusCount = $_POST["newStatusCount"];

            $this->session->set_userdata("emailCampaignPagination", $newStatusCount);

            $max_status_id = @$_POST['status_id'];

            $businessId = $_POST['businessId'];

            $start = $statuscount;
            if ($header['login']->usertype == 8) {
                $data['email_campaigns'] = $this->campaign_model->getAllEmailCampaigns($businessId, $start, $noofStatus);
            }
            if ($header['login']->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($header['login']->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $data['email_campaigns'] = $this->campaign_model->getEmailCampaigns($AppUserCampaigns, $start, $noofStatus);
            }
            if (count($data['email_campaigns']) > 0) {
                $this->load->view('3.1/addmore_emailcampaigns', $data);
            }
        } else {

            redirect(base_url());
        }
    }

    function editCampaigns($campaignId = false) {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
                $groupArray = NULL;
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

            foreach ($header['groups'] as $group) {
                $groups[] = $group->app_group_id;
            }

            //if(!empty($groupId)){
            //  $push_campaigns = $this->brand_model->getCampaignsByAppGroupId($login->businessId,$groupId);
            //}else{
            //
			//}
            //$push_campaigns = $this->brand_model->getPushCampaignsByBusinessId($login->businessId);
            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->brand_model->getPushCampaignsByBusinessId($login->businessId, '', '');   //// Get Total No of Records in Database

                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editCampaigns/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("pushCampaignPagination")) {
                    $config['per_page'] = $this->session->userdata("pushCampaignPagination");
                } else {
                    $config['per_page'] = '6';
                }

                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;
                $data['push_campaigns'] = $this->brand_model->getPushCampaignsByBusinessId($login->businessId, $data['page'], $config['per_page']);
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->campaign_model->getPushCampaigns($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editCampaigns/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("pushCampaignPagination")) {
                    $config['per_page'] = $this->session->userdata("pushCampaignPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;

                $data['push_campaigns'] = $this->campaign_model->getPushCampaigns($AppUserCampaigns, $data['page'], $config['per_page']);
            }

            $data['statuscount'] = count($data['push_campaigns']);
            $data['noofcampaigns'] = $config['per_page'];
            $data["businessId"] = $businessId;


            $data['groupId'] = $campaignId;
            //$data['push_campaigns'] = $push_campaigns;//	$header['groups']

            if ($campaignId != '') {
                $data['campaign'] = $this->campaign_model->getCampaign($campaignId, $groupArray);
                //echo '<pre>';
                //print_r($data['campaign']); die;
            } else {
                $data['campaign'] = $this->campaign_model->getCampaign('');
            }

            $data['app_groupId'] = $data['campaign']->app_group_id;

            if (count($data['campaign']) > 0) {
                $iOSApp = $this->campaign_model->getIosAppImage($data['campaign']->app_group_id);
                if (count($iOSApp) > 0) {
                    if($iOSApp->app_image != ''){
                       $data['iOSAppImage'] = base_url() . "upload/apps/" . $iOSApp->app_image;
                   }else{
                        $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
                   }

                } else {
                    $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
                }
            } else {
                $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
            }

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }

            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            $data['usertype'] = $login->usertype;

            if($data['campaign']->persona_user_id != 0){
                $suggestion = $this->getPersonaSuggestionMsg($data['campaign']->persona_user_id);
                $responce = json_decode($suggestion);
                $data['suggestion'] = $responce->statusMessage;

                $persona = $this->contact_model->getPersonaUser($data['campaign']->persona_user_id);
                $personaName = $persona->name;

                $data['twitterSearchKeyword'] = $this->getTwitterSearchResultsInApp($personaName);
                $data['googleSearchKeyword'] = $this->getGoogleTrendInApp($personaName);


            }else{
                $data['suggestion'] = 'DUMMY DATA: 56% of this persona clicked through on an Offer.';
                $data['twitterSearchKeyword'] = '';
                $data['googleSearchKeyword'] = '';
            }

            if (count($data['campaign']) > 0) {
                $this->load->view('3.1/inner_headerBrandUser', $header);
                $this->load->view('3.1/edit_campaign', $data);
                $this->load->view('3.1/inner_footerBrandUser');
            } else {
                redirect(base_url() . "appUser");
            }
        } else {
            redirect(base_url());
        }
    }

    function getPersonaSuggestionMsg($persona_user_id){
                if(isset($persona_user_id)){
                      //$persona_user_id = $_POST['persona_user_id'];
                        $contactUsers = $this->brand_model->getAssignContactsByPersonaId($persona_user_id);
                        $contactUserIdsArr = array();

                        if(count($contactUsers) > 0){
                             foreach($contactUsers as $users){
                                 if(!in_array($users->external_user_id,$contactUserIdsArr)){
                                    $contactUserIdsArr[] = $users->external_user_id;
                                 }
                             }
                             $contactUserIds = implode(',',$contactUserIdsArr);

                             $maximumViewCampaignName = '';
                             $totalViewUsers = 0;
                             $maximumViewCampaign = $this->brand_model->countPushViewExternalUsersById($contactUserIds);
               //echo "<pre>"; print_r($maximumViewCampaign); exit;
                             if(count($maximumViewCampaign) > 0){
                                 foreach($maximumViewCampaign as $key => $campaign){
                                     if($key == 0){
                                          $platform = $campaign->platform;
                                          $campaign_id = $campaign->campaign_id;
                                            $campaignName = $campaign->campaignName;
                                            $totalViewUsers = $campaign->totalCampaignUsers;
                                     }
                                 }
                                 $countPushSendUserIds = 0;
                                 if($platform == "android" || $platform == "iOS"){
                                      $countPushSendUserIds = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                 }else if($platform == "email"){
                                      $countPushSendUserIds = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                                 }
                                 if(count($countPushSendUserIds) > 0){
                                     $countPushSendUserIds = count($countPushSendUserIds);
                                 }
                                 $totalViewUsers = sprintf("%.2f",$totalViewUsers*100 / $countPushSendUserIds);
                                 $maximumViewCampaignName = $campaignName;
                             }
                         }else{
                             $totalViewUsers = 0;
                             $maximumViewCampaignName = '';
                         }

                        $success = 'success';
                        if(!empty($maximumViewCampaignName)){
                            $maximumViewCampaignName = " for $maximumViewCampaignName.";
                        }else{
                                $maximumViewCampaignName = '.';
                        }
                        $statusMessage = "$totalViewUsers% of this persona clicked through on an offer$maximumViewCampaignName <br /><br /><strong>Why not try sending a similar offer?</strong>";

                        $response = array(

                                        "status" => $success,
                                        "statusMessage" => $statusMessage

                        );
                 }else{
                        $success = 'error';
                        $statusMessage = "Error occoured. Please try again.";

                        $response = array(

                                        "status" => $success,
                                        "statusMessage" => $statusMessage

                        );
                }
            return json_encode($response);
        }

    function editEmailCampaign($campaignId = false) {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
                $groupArray = NULL;
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

            foreach ($header['groups'] as $group) {
                $groups[] = $group->app_group_id;
            }


            //if(!empty($groupId)){
            //  $push_campaigns = $this->brand_model->getCampaignsByAppGroupId($login->businessId,$groupId);
            //}else{
            //
			//}
            $push_campaigns = $this->brand_model->getPushCampaignsByBusinessId($login->businessId);

            $data['groupId'] = $campaignId;
            $data['push_campaigns'] = $push_campaigns; //	$header['groups']

            if ($campaignId != '') {
                $data['campaign'] = $this->campaign_model->getCampaign($campaignId, $groupArray);
            } else {
                $data['campaign'] = $this->campaign_model->getCampaign('');
            }

            $data['app_groupId'] = $data['campaign']->app_group_id;

            $groupData = $this->groupapp_model->getOneGroup($data['campaign']->app_group_id);
            $data['groupData'] = $groupData;

            $data['emailSettings'] = $this->campaign_model->getEmailSettings($data['campaign']->app_group_id);

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['usertype'] = $login->usertype;

            //$email_campaigns = $this->campaign_model->getAllEmailCampaigns($login->businessId);
            //$data['email_campaigns'] = $email_campaigns;

            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->campaign_model->getAllEmailCampaigns($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editEmailCampaign/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("emailCampaignPagination")) {
                    $config['per_page'] = $this->session->userdata("emailCampaignPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;
                $data['email_campaigns'] = $this->campaign_model->getAllEmailCampaigns($login->businessId, $data['page'], $config['per_page']);
                $data['statuscount'] = count($data['email_campaigns']);
                $data['noofcampaigns'] = $config['per_page'];
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->campaign_model->getEmailCampaigns($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editEmailCampaign/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("emailCampaignPagination")) {
                    $config['per_page'] = $this->session->userdata("emailCampaignPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;

                $data['email_campaigns'] = $this->campaign_model->getEmailCampaigns($AppUserCampaigns, $data['page'], $config['per_page']);

                $data['statuscount'] = count($data['email_campaigns']);
                $data['noofcampaigns'] = $config['per_page'];
            }
            $data["businessId"] = $businessId;

            //print_r($data['email_campaigns']); die;
            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }
            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            if($data['campaign']->persona_user_id != 0){
                $suggestion = $this->getPersonaSuggestionMsg($data['campaign']->persona_user_id);
                $responce = json_decode($suggestion);
                $data['suggestion'] = $responce->statusMessage;

                $persona = $this->contact_model->getPersonaUser($data['campaign']->persona_user_id);
                $personaName = $persona->name;

                $data['twitterSearchKeyword'] = $this->getTwitterSearchResultsInApp($personaName);
                $data['googleSearchKeyword'] = $this->getGoogleTrendInApp($personaName);


            }else{
                $data['suggestion'] = 'DUMMY DATA: 56% of this persona clicked through on an Offer.';
                $data['twitterSearchKeyword'] = '';
                $data['googleSearchKeyword'] = '';
            }
            if (count($data['campaign']) > 0) {
                $this->load->view('3.1/inner_headerBrandUser', $header);
                $this->load->view('3.1/edit_emailCampaign', $data);
                $this->load->view('3.1/inner_footerBrandUser');
            } else {
                redirect(base_url() . "appUser");
            }
        } else {
            redirect(base_url());
        }
    }

    function campaignError() {
        $this->load->view('3.1/errorcampaign');
    }

    public function preview() {
        $this->load->view('3.1/preview');
    }

    public function insightsPage($appGroupId = false) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'insightsPage';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $country_id = '2';
            $timezone = '';
            if (!empty($header['loggedInUser']->country)) {
                $country_id = $header['loggedInUser']->country;
                $row = $this->country_model->getTimezonebyCountryId($country_id);
                $timezone = $row->timezone;
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
            $businessId = $login->businessId;
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
            /* Customer Profiling */
            $businessId = $login->businessId;

            $totalHours = 0;
            $dailyActiveUsersDateTime = array();
            $countdailyActiveUsers = 0;
            $dailySessionsUsersDateTime = array();
            $countdailySessionsUsers = 0;
      	    $countSingleUserIds = 0;
      	    $countMultipleUserIds = 0;
      	    $countForVip = 0;

            $data['pushCampaigns'] = array();
            $data['inAppMessaging'] = array();
            $data['workflows'] = array();
            $data['webhooks'] = array();
            $data['appGroupId'] = 'all';

            if (!empty($appGroupId)) {
                $data['appGroupId'] = $appGroupId;
            }

            //if (count($allUsers) > 0) {
                //callbegin:
                /*$i = 0;
                foreach ($allUsers as $key => $user) {

                    $todayDate = date('Y-m-d', strtotime('-24 hours'));
                    $userLoginTime = date('Y-m-d', strtotime($user->datetime));
                    if ($userLoginTime == $todayDate) {
                        if (empty($user->logoutTime)) {
                            $time = date('Y-m-d H:i:s');
                        } else {
                            $time = $user->logoutTime;
                        }
                        $hourdiff = round((strtotime($time) - strtotime($user->datetime)) / 3600, 1);
                        $totalHours = $totalHours + $hourdiff;
                        $logindate = date('Y-m-d', strtotime($user->datetime));

                        if (!in_array($logindate, $dailyActiveUsersDateTime)) {
                            array_push($dailyActiveUsersDateTime, $logindate);
                            $countdailyActiveUsers++;
                        }

                        if (!in_array($user->datetime, $dailySessionsUsersDateTime)) {
                            array_push($dailySessionsUsersDateTime, $user->datetime);
                            $countdailySessionsUsers++;
                        }
                    }
                    $data['timeOnAppUsers'] = $totalHours * 30 / 100;
                    $data['dailyActiveUsers'] = $countdailyActiveUsers * 30 / 100;
                    $data['dailySessionsUsers'] = $countdailySessionsUsers * 30 / 100;
                    $index[] = $key;
                }*/


            	$data['timeOnAppUsers'] = array();
            	$data['dailyActiveUsers'] = array();
            	$data['dailySessionsUsers'] = array();

              if (empty($appGroupId)) {
          		    $countSingleUserIds = $this->brand_model->countAllAppGroupsUsersBybusinessId($businessId, $type='new');
          		    if(isset($countSingleUserIds[0]->TotalUser) && $countSingleUserIds[0]->TotalUser >= 0){
          		      $countSingleUserIds = $countSingleUserIds[0]->TotalUser;
          		    }
          		    $countMultipleUserIds = $this->brand_model->countAllAppGroupsUsersBybusinessId($businessId, $type='returning');
          	 	    if(isset($countMultipleUserIds[0]->TotalReturning) && $countMultipleUserIds[0]->TotalReturning >= 0){
                    //echo $countMultipleUserIds[0]->TotalReturning; exit;
          		      $countMultipleUserIds = $countMultipleUserIds[0]->TotalReturning;
          		    }
          		    $countForVip = $this->brand_model->countAllAppGroupsUsersBybusinessId($businessId, $type='VIP');
          		    if(isset($countForVip[0]->TotalVIP) && $countForVip[0]->TotalVIP >= 0){ // echo $countForVip[0]->TotalVIP; exit;
          		       $countForVip = $countForVip[0]->TotalVIP;
          		    }
                  $appUsers = $this->brand_model->countAllAppGroupsUsersBybusinessId($businessId, $type='app');
                  if(isset($appUsers[0]->TotalReturning) && $appUsers[0]->TotalReturning >= 0){
                    $timeOnAppUsers = $appUsers[0]->TotalReturning / 86400; //print_r($timeOnAppUsers); exit;
                    $dailyActiveUsers = $appUsers[0]->TotalReturning * 100 / $countSingleUserIds;
                    $dailySessionsUsers = $appUsers[0]->TotalReturning * 100 / 24 * $countSingleUserIds;
                  }
                  $push_campaigns = $this->brand_model->getAllPushCampaigns($login->businessId, 10);
                  $inAppMessaging = $this->inapp_model->getAllInAppMessaging($login->businessId, 0, 10);
                  $webhooks = $this->webhook_model->getAllWebhooks($login->businessId, 0, 10);
              } else {
          		    $countSingleUserIds = $this->brand_model->countUsersByAppGroupId($businessId, $appGroupId, $type='new');
          		    if(isset($countSingleUserIds[0]->TotalUser) && $countSingleUserIds[0]->TotalUser >= 0){
          		      $countSingleUserIds = $countSingleUserIds[0]->TotalUser;
          		    }
          		    $countMultipleUserIds = $this->brand_model->countUsersByAppGroupId($businessId, $appGroupId, $type='returning');
          	 	    if(isset($countMultipleUserIds[0]->TotalReturning) && $countMultipleUserIds[0]->TotalReturning >= 0){
          		      $countMultipleUserIds = $countMultipleUserIds[0]->TotalReturning;
          		    }
          		    $countForVip = $this->brand_model->countUsersByAppGroupId($businessId, $appGroupId, $type='VIP');
          		    if(isset($countForVip[0]->TotalVIP) && $countForVip[0]->TotalVIP >= 0){
          		       $countForVip = $countForVip[0]->TotalVIP;
		              }
                  $appUsers = $this->brand_model->countAllAppGroupsUsersBybusinessId($businessId, $appGroupId, $type='app');
                  if(isset($appUsers[0]->TotalReturning) && $appUsers[0]->TotalReturning >= 0){
                    $timeOnAppUsers = $appUsers[0]->TotalReturning / 86400; //print_r($timeOnAppUsers); exit;
                    $dailyActiveUsers = $appUsers[0]->TotalReturning * 100 / $countSingleUserIds;
                    $dailySessionsUsers = $appUsers[0]->TotalReturning * 100 / 24 * $countSingleUserIds;
                  }
                  $push_campaigns = $this->brand_model->getAllPushCampaignsByAppGroupId($login->businessId, $appGroupId, 10);
                  $inAppMessaging = $this->inapp_model->getAllInAppMessagingByAppGroupId($login->businessId, $appGroupId, 10);
                  $webhooks = $this->webhook_model->getAllWebhooksByAppGroupId($login->businessId, $appGroupId, 10);
              }
              $workflows = $this->workflow_model->getInsightsWorkflow($businessId);
          		$data['countSingleUserIds'] = $countSingleUserIds;
          		$data['countMultipleUserIds'] = $countMultipleUserIds;
          		$data['countForVip'] = $countForVip;
              $data['pushCampaigns'] = $push_campaigns;
              $data['workflows'] = $workflows;
              $data['inAppMessaging'] = $inAppMessaging;
              $data['webhooks'] = $webhooks;

            //end
            $data['campaignPermission'] = $header['campaignPermission'];

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/insights', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function getUsersList($type = false, $appGroupId = false) {
        $header['login'] = $this->administrator_model->front_login_session();
        $businessId = $header['login']->businessId;
        if (!empty($appGroupId) && $appGroupId != 'all') {
            if ($type == 2) {
                $allUsers = $this->brand_model->getUsersByAppGroupId($businessId, $appGroupId, $type='new');
            } else if ($type == 3) {
               $allUsers = $this->brand_model->getUsersByAppGroupId($businessId, $appGroupId, $type='returning');
            } else if ($type == 4) {
               $allUsers = $this->brand_model->getUsersByAppGroupId($businessId, $appGroupId, $type='VIP');
            }
        } else {
            if ($type == 2) {
                $allUsers = $this->brand_model->getAllAppGroupsUsersByUserId($businessId, $type='new');
            } else if ($type == 3) {
               $allUsers = $this->brand_model->getAllAppGroupsUsersByUserId($businessId, $type='returning');
            } else if ($type == 4) {
               $allUsers = $this->brand_model->getAllAppGroupsUsersByUserId($businessId, $type='VIP');
            }
        }
        //echo "<pre>"; print_r($allUsers); exit;
        $data['users'] = $allUsers;
        $this->load->view('3.1/getUsersList', $data);
    }

    public function timeline($userId = NULL) {
        // define variable and array
        $data = array();
        $login = $this->administrator_model->front_login_session();
        if (!empty($userId)) {
            $userRow = $this->brand_model->getUserRowByUserId($userId);
            if ($userRow->businessId != $login->businessId) {
                redirect('home/signup');
            }
        }
        //echo '<pre>'; print_r($login ); die;
        if ($login->active != 0) {
            $header['page'] = 'account';
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

            // Get UserDetails all events
            $data['eventDetails'] = $this->contact_model->getUserEvents($userId);

            // Get User Details
            $where['external_user_id'] = $userId;
            $select = "external_user_id, firstName, lastName, phoneNumber, email, createdDate";
            $data['userDetails'] = $this->contact_model->getConatctDetails($select, $where);
            $data['appAdminEmail'] = $login->email;
            $data['loginUserDetials'] = $login;

//                        echo "<pre>";
//			print_r($data);
//                        echo "</pre>";
//                        die;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/timeline', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect('home/signup');
        }
    }

    public function setCookies($login) {
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

        return $header;
    }

    function crmPage() {
        $data = array();
        $header = array();
        $login = $this->administrator_model->front_login_session();
        $data['hubSpotlogin'] = 0;

        $userHubId = $this->session->userdata('hubPortalId');

        if ($login->active != 0) {

            $header = $this->setCookies($login);
            // Get HubSpot Details From URL
            if ($_GET) {
                $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';
                $refresh_token = isset($_GET['refresh_token']) ? $_GET['refresh_token'] : '';
                $expires_in = isset($_GET['expires_in']) ? $_GET['expires_in'] : '';

                if (!empty($accessToken) && isset($refresh_token) && isset($expires_in)) {

                    $userHubId = $this->session->userdata('userHubId');
                    $update['userHubSpotId'] = $userHubId;
                    $update['userid'] = $login->user_id;
                    $update['accress_token'] = $accessToken;
                    $update['refresh_token'] = $refresh_token;
                    $last_id = $this->hubSpot_model->save($update);
                    $data['hubSpotlogin'] = 1;
                } else {

                    $data['hubSpotlogin'] = 0;
                }
                //echo $this->db->last_query(); die;
            } else if ($userHubId != '') {
                $data['hubSpotlogin'] = 1;
            }
            //Check User enable ON or OFF for Hubspot
            $hubspot = $this->brand_model->getHubSpotDetails($login->user_id);
            if (count($hubspot) > 0) {
                $data['on_off'] = $hubspot->on_off;
            } else {
                $data['on_off'] = 0;
            }


            //Check User enable ON/OFF for Salesforce
            $salesforce = $this->brand_model->getSalesforceDetails($login->user_id);
            if (count($salesforce) > 0) {
                $data['salesforce_on'] = $salesforce->on_off;
            } else {
                $data['salesforce_on'] = 0;
            }


            $header['page'] = 'crm';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            //	echo '<pre>'; print_r($data); die;
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/crmPage', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect('home/signup');
        }
    }

    /**
     * Load a view page for enter HubId of Hubspot CRM
     */
    function connectHubspot() {
        $this->load->view('3.1/hubIdForm');
    }

    function disconnectHubspot() {

        $this->session->unset_userdata('hubPortalId');
        redirect('appUser/crmPage');
    }

    /**
     * Authorize user
     * @param unknown $portalId
     * @return unknown
     */
    function hubspotAuthenticaion($portalId, $url = NULL) {
        if ($url == '') {
            $url = base_url() . 'appUser';
        }

        $endpoint = 'https://app.hubspot.com/auth/authenticate?client_id=' . HUBCLIENTID . '&portalId=' . $portalId . '&redirect_uri=' . $url . '&scope=offline';

        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = @curl_exec($ch);
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch);
        @curl_close($ch);
        // 		echo "curl Errors: " . $curl_errors;
        // 		echo "\nStatus code: " . $status_code;
        // 		echo "\nResponse: " . $response;
        return $status_code;
    }

    function oauthHubSpot($hubID = NULL) {
        $login = $this->administrator_model->front_login_session();
        $save['userHubSpotId'] = '';

        if ($hubID != '') {
            $portalId = $hubID;
            $currentUrl = $_GET['url'];
        } else {
            $portalId = $_POST['hubid'];
            $currentUrl = $_POST['currenturl'];
        }
        //  print_r($_POST);

        $scopeArray = array(
            "offline",
            "contacts-rw",
            "blog-rw",
            "events-rw",
            "keyword-rw"
        );

        $where ['portalId'] = $portalId;
        $where['userid'] = $login->user_id;
        $where ['isActive'] = 1;
        $select = 'userHubSpotId, accress_token';
        $hubDetails = $this->hubSpot_model->getHubSpotDetails($select, $where);


        if (count($hubDetails) > 0) {
            $save['userHubSpotId'] = $hubDetails->userHubSpotId;
            $save['modifiedDate'] = date('Ymdhis');
        } else {
            $save['createdDate'] = date('Ymdhis');
        }

        $url = base_url() . 'appUser/oauthHubSpot';
        $status = $this->hubspotAuthenticaion($portalId, $url);
        if ($status == '302') {
            $save['userid'] = $login->user_id;
            $save['portalId'] = $portalId;
            $save['isActive'] = 1;

            $last_id = $this->hubSpot_model->save($save);

            $this->session->set_userdata('hubPortalId', $portalId);
            $this->session->set_userdata('userHubId', $last_id);

            $scopeStringWithPlusSigns = implode("+", $scopeArray);


            header("Location: https://app.hubspot.com/auth/authenticate?client_id=" . HUBCLIENTID . "&portalId=$portalId&redirect_uri=$currentUrl&scope=$scopeStringWithPlusSigns");
        } else {
            echo 'some error occurs, try again';
        }
    }

    function hubspotAutoSync() {

        $login = $this->administrator_model->front_login_session();

        $update['userid'] = $login->user_id;
        $update['on_off'] = $_POST['on_off'];
        $this->brand_model->updateHubSpotDetails($update);

        echo 1;
    }

    function salesforceAutoSync() {

        $login = $this->administrator_model->front_login_session();

        $update['userid'] = $login->user_id;
        $update['on_off'] = $_POST['on_off'];
        $this->brand_model->updateSalesforceDetails($update);

        echo 1;
    }

    public function store() {
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            $data['allPermision'] = array();
            $usertype = $header['login']->usertype;
            if ($usertype == 8 || $usertype == 9) {

                $this->load->helper('convertlink');

                $userid = $header['login']->user_id;
                $data['loginuser'] = $header['login']->user_id;
                $data['user'] = $this->user_model->getOneUser($userid);
                $data['viewPage'] = 'Brand Store';
                $data['campaigns'] = $this->campaign_model->getAllCampaigns($header['login']->user_id);

                $data['packages'] = $this->brand_model->getAllpackages();

                if ($usertype == 9) {

                    $data['allPermision'] = getAssignPermission($userid);
                    //  print_r($data['allPermision']); die;
                }

                $data['usertype'] = $usertype;

                $this->load->view('3.1/brand_store', $data);
            } else {
                redirect(base_url());
            }
        } else {
            $this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
            redirect(base_url());
        }
    }

    // This function is used for open checkout page from brand store popup via pay now button //
     public function checkout() {
         $brandStoreId = $this->uri->segment('3');
         $data['package'] = $this->brand_model->getOnePackage($brandStoreId);
         $data['countries'] = $this->country_model->get_countries();
         $arr_card_type['active'] = 1; // $row is not required only used for pass default values
         $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);
         $login = $this->administrator_model->front_login_session();
         $userid = $login->user_id;
         $businessId = $login->businessId;
         $data['recurringPackage'] = array();
         if ($brandStoreId == 5) { // $brandStoreId is used for check purchase product is recurring type
             $data['recurringPackage'] = $this->brand_model->checkRecurringPackageExist($brandStoreId, $businessId);
         }
         //echo '<pre>'; print_r($data); exit;
         $this->load->view('3.1/checkout', $data);
     }

     // This function is used for open brand signup payment popup //
      public function brandsignup() {
          $login = $this->administrator_model->front_login_session();
          //print_r($login); exit;
          $userid = $login->user_id;
          $data['package'] = array('paymentMode' => 'recurring', 'price' => '99.99', 'currency_type' => 'USD');
          $data['countries'] = $this->country_model->get_countries();
          $arr_card_type['active'] = 1; // $row is not required only used for pass default values
          $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);
          $this->load->view('3.1/brand_signup', $data);
      }

      // This function is used for save brand signup payment from popup via pay now button //
     public function brandUserSignupPayment() {
         $login = $this->administrator_model->front_login_session();
         $userid = $login->user_id;
         $email = $login->email;
         $username = $login->username;
         $businessId = $login->businessId;
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
         $desc = "Payment of Brand User Signup";

         //// CREATE AN STRING for send request to paypal
         $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                 "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
                 "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country" .
                 "&CURRENCYCODE=$currencyID&PROFILESTARTDATE=$profileStartDate&MAXFAILEDPAYMENTS=3" .
                 "&DESC=$desc&BILLINGPERIOD=Month" .
                 "&BILLINGFREQUENCY=1";

         //SEND REQUEST TO PAYPAL for create recurring payment profile id
         $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);
         //echo '<pre>'; print_r($httpParsedResponseAr ); exit;
         if ($httpParsedResponseAr['ACK'] == 'Success') {

             $user['user_Id'] = $userid;
             $user['paypal_profileid'] = $httpParsedResponseAr['PROFILEID']; //date('YmdHis');
             $user['paypal_response'] = json_encode($httpParsedResponseAr);
             $user['accountType'] = 'paid';
             $user['modifiedDate'] = date('YmdHis');

             $last_id = $this->user_model->insertsignup($user);

             /* code start save billing info details */
             $userBillingInfo = $this->brand_model->getBillingInfo($businessId);
             if (count($userBillingInfo) > 0) {
                 $billingInfoArr = array(
                     'id' => $userBillingInfo->id,
                     'businessId' => $businessId,
                     'address' => $address,
                     'city' => $city,
                     'state' => $state,
                     'Country' => $country,
                     'pincode' => $zip,
                     'card_firstname' => $firstName,
                     'card_lastname' => $lastName,
                     'card_number' => $creditCardNumber,
                     'expiry_date' => $padDateMonth . '/' . $expDateYear,
                     'isDelete' => 0,
                     'createdDate' => date('Y-m-d H:i:s')
                 );

                 $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
             } else {
                 $billingInfoArr = array(
                     'businessId' => $businessId,
                     'address' => $address,
                     'city' => $city,
                     'state' => $state,
                     'Country' => $country,
                     'pincode' => $zip,
                     'card_firstname' => $firstName,
                     'card_lastname' => $lastName,
                     'card_number' => $creditCardNumber,
                     'expiry_date' => $padDateMonth . '/' . $expDateYear,
                     'isDelete' => 0,
                     'createdDate' => date('Y-m-d H:i:s')
                 );

                 $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
             }
             /*********************************************** */

             //// SEND  EMAIL START for SEND USER
             $this->emailConfig();   //Get configuration of email
             //// GET EMAIL FROM DATABASE

             $email_template = $this->email_model->getoneemail('purchase_account');

             //// MESSAGE OF EMAIL    $payerId = $httpParsedResponseAr['PAYERID'];
             $messages = $email_template->message;

             $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
             $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
             $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

             //// replace strings from message
             $messages = str_replace('{Username}', ucfirst($username), $messages);
             $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
             $messages = str_replace('{amount}', '$' . $amount, $messages);

             //// FROM EMAIL
             $this->email->from("hello@marketmyapp.co", 'Hurree');
             $this->email->to($email);
             $this->email->subject($email_template->subject);
             $this->email->message($messages);
             $this->email->send();    ////  EMAIL SEND

 	    //// SEND  EMAIL START for ADMIN USER
             $this->emailConfig();   //Get configuration of email
             //// GET EMAIL FROM DATABASE

             $email_template = $this->email_model->getoneemail('admin_mail_user_signup');

             //// MESSAGE OF EMAIL
             $messages = $email_template->message;

             //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
             $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
             $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
             $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

             //// replace strings from message
             $messages = str_replace('{firstname}', ucfirst($firstname), $messages);
             $messages = str_replace('{lastname}', ucfirst($lastname), $messages);
             $messages = str_replace('{email}', $email, $messages);
             $messages = str_replace('{username}', ucfirst($username), $messages);
             $messages = str_replace('{amount}', '$' . $amount, $messages);

             //// FROM EMAIL
             $this->email->from('hello@marketmyapp.co', 'Hurree');
             $this->email->to('Business@hurree.co');
             $this->email->subject($email_template->subject);
             $this->email->message($messages);
             $this->email->send();


             // Set user as customer in Hubspot

             $portal = HUBPORTALID;
             $url = base_url() . 'appUser/brandUserSignupPayment';

             $status = $this->hubspotAuthenticaion($portal, $url);

             if ($status == 302) {
                 $responce_code = $this->upldatecontactToHubspot($login->email);
                 if ($responce_code != 204) {
                     $responcecode = $this->upldatecontactToHubspot($login->email);
                     if ($responcecode != 204) {
                         $responcecode = $this->upldatecontactToHubspot($login->email);
                     }
                 }
             }

             echo 'Success';
         } else {
             echo 'Failure';
         }
     }

     // function used for send request on paypal url with paypal credentails and requried parameters
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
          $version = urlencode('109.0');
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
      // This function is used for save brand store payment from popup via pay now button //
         public function brandUserStorePayment() {
             $login = $this->administrator_model->front_login_session();
             $userid = $login->user_id;
             $businessId = $login->businessId;
             $email = $login->email;
             $username = $login->username;
             $total_appgroup = 0;

             $packageid = $_POST['packageid'];
             $paymentMode = $_POST['paymentMode'];

             $firstname = $_POST['firstname'];
             $lastname = $_POST['lastname'];
             $card_type = $_POST['card_type'];
             $card_no = $_POST['card'];
             $exp_month = $_POST['expire_month'];
             $exp_year = $_POST['expire_year'];
             $cvv2 = $_POST['cvv'];
             $total_appgroup = $_POST['total_appgroup'];
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

             if ($paymentMode == "oneOff") { // oneOff payment used for only one time payment

                 //// CREATE AN STRING SEND REQUEST TO PAYPAL
                 $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                         "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$emails" .
                         "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";

                 //// SEND REQUEST TO PAYPAL for return
                 $httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);
                 //echo '<pre>'; print_r($httpParsedResponseAr); exit;
                 if ($httpParsedResponseAr['ACK'] == 'Success') {
                     /* code start save billing info details */
                     $userBillingInfo = $this->brand_model->getBillingInfo($businessId);
                     if (count($userBillingInfo) > 0) {
                         $billingInfoArr = array(
                             'id' => $userBillingInfo->id,
                             'businessId' => $businessId,
                             'address' => $address,
                             'city' => $city,
                             'state' => $state,
                             'Country' => $country,
                             'pincode' => $zip_code,
                             'card_firstname' => $firstName,
                             'card_lastname' => $lastName,
                             'card_number' => $creditCardNumber,
                             'expiry_date' => $padDateMonth . '/' . $expDateYear,
                             'isDelete' => 0,
                             'createdDate' => date('Y-m-d H:i:s')
                         );

                         $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                     } else {
                         $billingInfoArr = array(
                             'businessId' => $businessId,
                             'address' => $address,
                             'city' => $city,
                             'state' => $state,
                             'Country' => $country,
                             'pincode' => $zip_code,
                             'card_firstname' => $firstName,
                             'card_lastname' => $lastName,
                             'card_number' => $creditCardNumber,
                             'expiry_date' => $padDateMonth . '/' . $expDateYear,
                             'isDelete' => 0,
                             'createdDate' => date('Y-m-d H:i:s')
                         );

                         $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                     }
                     /*                 * ********************************************* */

                     $payment['brand_store_payment_id'] = '';
                     $payment['user_id'] = $userid;
                     $payment['package_id'] = $packageid;
                     $payment['purchasedOn'] = date('YmdHis');
                     $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
                     $payment['currency'] = $currency;
                     $payment['transaction_id'] = $httpParsedResponseAr['TRANSACTIONID'];
                     $payment['payment_response'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '|CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
                     $payment['isActive'] = 1;
                     $payment['isDelete'] = 0;
                     $payment['createdDate'] = date('YmdHis');

                     $last_payment_id = $this->brand_model->savepayment($payment);

                     $package = $this->brand_model->getOnePackage($packageid);
                     $description = $package->desription;
                     $quantity = $package->quantity;

                     //If user don't have any entry in user_profile_info table
                     $packageInfo = $this->brand_model->getPackagesInfo($businessId);
                     if (count($packageInfo) == 0) {
                         //Insert
                         $date = date('YmdHis');
                         $insert['user_pro_id'] = '';
                         $insert['user_id'] = $userid;
                         $insert['businessId'] = $businessId;
                         $insert['totalIosApps'] = 0;
                         $insert['totalAndroidApps'] = 0;
                         $insert['totalCampaigns'] = 0;
                         $insert['totalAppGroup'] = 0;
                         $insert['androidCampaign'] = 0;
                         $insert['iOSCampaign'] = 0;
                         $insert['emailCampaign'] = 0;
                         $insert['crossChannel'] = 0;
                         $insert['inAppMessaging'] = 0;
                         $insert['webhook'] = 0;
                         $insert['createdDate'] = $date;
                         $last_insert_id = $this->brand_model->savePackage($insert);
                     }

                     $extraPackage['businessId'] = $businessId;
                     $extraPackage['packageid'] = $packageid;
                     $userExtraPackage = $this->brand_model->getExtraPackage($extraPackage);

                     if (count($userExtraPackage) == 0) {
                         //Insert
                         if ($package->desription == 'Unlimited Campaigns') {
                             $quantity = 'unlimited';
                         } else {
                             $quantity = $package->quantity;
                         }
                         if ($packageid == 6 && $total_appgroup > 1) {
                             $quantity = $total_appgroup;
                         }
                         if ($packageid == 1) {
                             $userInsert['androidCampaign'] = 5;
                             $userInsert['iOSCampaign'] = 5;
                             $userInsert['emailCampaign'] = 25;
                         } else if ($packageid == 2) {
                             $userInsert['androidCampaign'] = 10;
                             $userInsert['iOSCampaign'] = 10;
                             $userInsert['emailCampaign'] = 50;
                         } else if ($packageid == 3) {
                             $userInsert['androidCampaign'] = 15;
                             $userInsert['iOSCampaign'] = 15;
                             $userInsert['emailCampaign'] = 75;
                         } else if ($packageid == 4) {
                             $userInsert['androidCampaign'] = 'unlimited';
                             $userInsert['iOSCampaign'] = 'unlimited';
                             $userInsert['emailCampaign'] = 'unlimited';
                         } else if ($packageid == 7) {
                             $userInsert['crossChannel'] = $quantity;
                             $quantity = 0;
                         } else if ($packageid == 8) {
                             $userInsert['inAppMessaging'] = $quantity;
                             $quantity = 0;
                         }
                         else if ($packageid == 9) {
                             $userInsert['webhook'] = $quantity;
                             $quantity = 0;
                         }

                         $current_date = date('YmdHis');
                         $userInsert['user_extra_packages_id'] = '';
                         $userInsert['userid'] = $userid;
                         $userInsert['businessId'] = $businessId;
                         $userInsert['packageid'] = $packageid;
                         $userInsert['quantity'] = $quantity;
                         //$userInsert['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                         $userInsert['createdDate'] = $current_date;

                         $this->brand_model->saveExtraPackage($userInsert);
                     } else {
                         //Update
                         if ($package->desription == 'Unlimited Campaigns') {
                             $quantity = 'unlimited';
                         } else {
                             $quantity = $package->quantity;
                         }
                         if ($packageid == 6 && $total_appgroup > 1) {
                             $quantity = $total_appgroup;
                         }
                         if ($packageid == 1) {
                             $update['androidCampaign'] = $userExtraPackage->androidCampaign + 5;
                             $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 5;
                             $update['emailCampaign'] = $userExtraPackage->emailCampaign + 25;
                         } else if ($packageid == 2) {
                             $update['androidCampaign'] = $userExtraPackage->androidCampaign + 10;
                             $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 10;
                             $update['emailCampaign'] = $userExtraPackage->emailCampaign + 50;
                         } else if ($packageid == 3) {
                             $update['androidCampaign'] = $userExtraPackage->androidCampaign + 15;
                             $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 15;
                             $update['emailCampaign'] = $userExtraPackage->emailCampaign + 75;
                         } else if ($packageid == 4) {
                             $update['androidCampaign'] = 'unlimited';
                             $update['iOSCampaign'] = 'unlimited';
                             $update['emailCampaign'] = 'unlimited';
                         } else if ($packageid == 7) {
                             $update['crossChannel'] = $userExtraPackage->crossChannel + 5;
                             $quantity = 0;
                         } else if ($packageid == 8) {
                             $update['inAppMessaging'] = $userExtraPackage->inAppMessaging + 5;
                             $quantity = 0;
                         }
                         else if ($packageid == 9) {
                             $update['webhook'] = $userExtraPackage->webhook + 5;
                             $quantity = 0;
                         }

                         $current_date = date('YmdHis');
                         $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;
                         $update['quantity'] = $quantity;
                         //$update['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                         $update['createdDate'] = $current_date;

                         $this->brand_model->updateExtraPackage($update);

                         //// SEND  EMAIL START FOR SEND USER
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
                             $packageName = $quantity . " " . $package->desription;
                         }
                         $price = "$" . $amount;
                         //// replace strings from message
                         $messages = str_replace('{Username}', ucfirst($username), $messages);
                         $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                         $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                         $messages = str_replace('{price}', $price, $messages);

                         //// FROM EMAIL
                         $this->email->from('hello@marketmyapp.co', 'Hurree');
                         $this->email->to($email);
                         $this->email->subject($email_template->subject);
                         $this->email->message($messages);
                         $this->email->send();    ////  EMAIL SEND
                         //Email send to Admin

     		    //// SEND  EMAIL START for ADMIN USER
                         $this->emailConfig();   //Get configuration of email
                         //// GET EMAIL FROM DATABASE

                         $email_template = $this->email_model->getoneemail('admin_user_buy_extra_package');

                         //// MESSAGE OF EMAIL
                         $messages = $email_template->message;

                         //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
                         $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                         $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                         $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                         if ($package->quantity == 0) {
                             $packageName = $package->desription;
                         } else {
                             $packageName = $quantity . " " . $package->desription;
                         }
                         $price = "$" . $amount;
                         //// replace strings from message
                         $messages = str_replace('{Username}', ucfirst($username), $messages);
                         $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                         $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                         $messages = str_replace('{price}', $price, $messages);

                         //// FROM EMAIL
                         $this->email->from('hello@marketmyapp.co', 'Hurree');
                         $this->email->to('Business@hurree.co');
                         $this->email->subject($email_template->subject);
                         $this->email->message($messages);
                         $this->email->send();
                     }

                     echo 'Success';
                 } else {
                     echo 'Failure';
                 }
             } else if ($paymentMode == 'recurring') { // recurring payment for buy package from brand store

                 $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z")); // recurring profile start date
                 $desc = "Payment of Hurree store";

                 //// CREATE AN RECURRING STRING for send request to paypal
                 $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                         "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
                         "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country" .
                         "&CURRENCYCODE=$currencyID&PROFILESTARTDATE=$profileStartDate&MAXFAILEDPAYMENTS=3" .
                         "&DESC=$desc&BILLINGPERIOD=Month" .
                         "&BILLINGFREQUENCY=1";

                 //SEND REQUEST TO PAYPAL recurring payment for generate user paypal profile id
                 $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);

                 if ($httpParsedResponseAr['ACK'] == 'Success') {
                     /* code start save billing info details */
                     $userBillingInfo = $this->brand_model->getBillingInfo($businessId);
                     if (count($userBillingInfo) > 0) {
                         $billingInfoArr = array(
                             'id' => $userBillingInfo->id,
                             'businessId' => $businessId,
                             'address' => $address,
                             'city' => $city,
                             'state' => $state,
                             'Country' => $country,
                             'pincode' => $zip_code,
                             'card_firstname' => $firstName,
                             'card_lastname' => $lastName,
                             'card_number' => $creditCardNumber,
                             'expiry_date' => $padDateMonth . '/' . $expDateYear,
                             'isDelete' => 0,
                             'createdDate' => date('Y-m-d H:i:s')
                         );

                         $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                     } else {
                         $billingInfoArr = array(
                             'businessId' => $businessId,
                             'address' => $address,
                             'city' => $city,
                             'state' => $state,
                             'Country' => $country,
                             'pincode' => $zip_code,
                             'card_firstname' => $firstName,
                             'card_lastname' => $lastName,
                             'card_number' => $creditCardNumber,
                             'expiry_date' => $padDateMonth . '/' . $expDateYear,
                             'isDelete' => 0,
                             'createdDate' => date('Y-m-d H:i:s')
                         );

                         $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                     }
                     /*                 * ********************************************* */

                     $recurringPayment['brand_store_payment_id'] = '';
                     $recurringPayment['user_id'] = $userid;
                     $recurringPayment['package_id'] = $packageid;
                     $recurringPayment['purchasedOn'] = date('YmdHis');
                     $recurringPayment['amount'] = $amount;
                     $recurringPayment['currency'] = $currency;
                     $recurringPayment['profile_id'] = $httpParsedResponseAr['PROFILEID'];
                     $recurringPayment['payment_response'] = json_encode($httpParsedResponseAr);
                     $recurringPayment['isActive'] = 1;
                     $recurringPayment['isDelete'] = 0;
                     $recurringPayment['createdDate'] = date('YmdHis');

                     $last_payment_id = $this->brand_model->savepayment($recurringPayment);

                     $package = $this->brand_model->getOnePackage($packageid);
                     $description = $package->desription;
                     $quantity = $package->quantity;

                     //If user don't have any entry in user_profile_info table
                     $packageInfo = $this->brand_model->getPackagesInfo($businessId);
                     if (count($packageInfo) == 0) {
                         //Insert
                         $date = date('YmdHis');
                         $recurringInsert['user_pro_id'] = '';
                         $recurringInsert['user_id'] = $userid;
                         $recurringInsert['businessId'] = $businessId;
                         $recurringInsert['totalCampaigns'] = 0;
                         $recurringInsert['totalAppGroup'] = 0;
                         $recurringInsert['androidCampaign'] = 0;
                         $recurringInsert['iOSCampaign'] = 0;
                         $recurringInsert['emailCampaign'] = 0;
                         $recurringInsert['createdDate'] = $date;
                         $last_insert_id = $this->brand_model->savePackage($recurringInsert);
                     }

                     $extraPackage['businessId'] = $businessId;
                     $extraPackage['packageid'] = $packageid;
                     $userExtraPackage = $this->brand_model->getExtraPackage($extraPackage);

                     if (count($userExtraPackage) == 0) {
                         //Insert
                         if ($package->desription == '') {
                             $quantity = '';
                         } else {
                             $quantity = $package->quantity;
                         }
                         if ($packageid == 6 && $total_appgroup > 1) {
                             $quantity = $total_appgroup;
                         }
                         $current_date = date('YmdHis');
                         $userInsert['user_extra_packages_id'] = '';
                         $userInsert['userid'] = $userid;
                         $userInsert['businessId'] = $businessId;
                         $userInsert['packageid'] = $packageid;
                         $userInsert['quantity'] = $quantity;
                         $userInsert['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                         $userInsert['createdDate'] = $current_date;

                         $this->brand_model->saveExtraPackage($userInsert);
                     } else {
                         //Update
                         if ($package->desription == '') {
                             $quantity = '';
                         } else {
                             $quantity = $package->quantity;
                         }
                         if ($packageid == 6 && $total_appgroup > 1) {
                             $quantity = $total_appgroup;
                         }
                         $current_date = date('YmdHis');
                         $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;
                         $update['quantity'] = $quantity;
                         $update['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                         $update['createdDate'] = $current_date;

                         $this->brand_model->updateExtraPackage($update);

                         //// SEND  EMAIL START PURCHASE USER
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
                             $packageName = $quantity . " " . $package->desription;
                         }
                         $price = "$" . $amount;
                         //// replace strings from message
                         $messages = str_replace('{Username}', ucfirst($username), $messages);
                         $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                         $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                         $messages = str_replace('{price}', $price, $messages);

                         //// FROM EMAIL
                         $this->email->from('hello@marketmyapp.co', 'Hurree');
                         $this->email->to($email);
                         $this->email->subject($email_template->subject);
                         $this->email->message($messages);
                         $this->email->send();    ////  EMAIL SEND
                         //Email send to Admin

     		    //// SEND  EMAIL START for ADMIN USER
                         $this->emailConfig();   //Get configuration of email
                         //// GET EMAIL FROM DATABASE

                         $email_template = $this->email_model->getoneemail('admin_user_buy_extra_package');

                         //// MESSAGE OF EMAIL
                         $messages = $email_template->message;

                         //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
                         $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                         $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                         $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                         if ($package->quantity == 0) {
                             $packageName = $package->desription;
                         } else {
                             $packageName = $quantity . " " . $package->desription;
                         }
                         $price = "$" . $amount;
                         //// replace strings from message
                         $messages = str_replace('{Username}', ucfirst($username), $messages);
                         $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                         $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                         $messages = str_replace('{price}', $price, $messages);

                         //// FROM EMAIL
                         $this->email->from('hello@marketmyapp.co', 'Hurree');
                         $this->email->to('Business@hurree.co');
                         $this->email->subject($email_template->subject);
                         $this->email->message($messages);
                         $this->email->send();
                     }

                     echo 'Success';
                 } else {
                     echo 'Failure';
                 }
             } else {
                 echo 'Failure';
             }
         }

    function savePushNotificationCampaign() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
            if ($param->selectedPlatform != 'email') {
                if ($param->push_icon != '') {

                    $ime = $param->push_icon;

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

                    $fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['push_icon'] = $filename;
                } else {
                    if ($param->campaignId == '') {
                        $save['push_icon'] = $param->push_icon;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['push_icon'] = $dataCampaign->push_icon;
                    }
                }

                if ($param->push_img_url != '') {
                    $save['push_icon'] = '';
                    $save['push_img_url'] = $param->push_img_url;
                } else {
                    if ($param->campaignId == '') {
                        $save['push_img_url'] = '';
                    } else {
                        if ($save['push_icon'] != '') {
                            $save['push_img_url'] = '';
                        } else {
                            $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                            $save['push_img_url'] = $dataCampaign->push_img_url;
                        }
                    }
                }

                if ($param->expandedImage != '') {

                    $imexpanded = $param->expandedImage;

                    $imageExpanded = explode(';base64,', $imexpanded);
                    $size = getimagesize($imexpanded);
                    $type = $size['mime'];
                    $typea = explode('/', $type);
                    $extnsn = $typea[1];
                    $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                    $img_cont = str_replace(' ', '+', $imageExpanded[1]);
                    //$img_cont=$image[1];
                    $data = base64_decode($img_cont);
                    $im = imagecreatefromstring($data);
                    $filename1 = time() . '.' . $extnsn;

                    $fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['expandedImage'] = $filename1;
                } else {

                    if ($param->campaignId == '') {
                        $save['expandedImage'] = $param->expandedImage;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['expandedImage'] = $dataCampaign->expandedImage;
                    }
                }

                if ($param->expanded_img_url != '') {
                    $save['expandedImage'] = '';
                    $save['expanded_img_url'] = $param->expanded_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['expanded_img_url'] = '';
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        if ($save['expandedImage'] != '') {
                            $save['expanded_img_url'] = '';
                        } else {
                            $save['expanded_img_url'] = $dataCampaign->expanded_img_url;
                        }
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
            $save['push_notification_image'] = $param->push_notification_image;

            if ($param->selectedPlatform == 'android') {
                $save['push_title'] = $param->push_title;
                $save['push_message'] = $param->push_message;
                $save['summery_text'] = $param->summery_text;
            } else {
                $save['push_message'] = $param->push_message;
            }
            if ($param->selectedPlatform == 'email') {
                $save['push_title'] = $param->push_title;
                $message = str_replace("&lbrace;","{",$param->push_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                //$save['push_message'] = $message;
                $save['push_message'] = '<html><head><style>img.fr-dib { margin: 5px auto; display: block; float: none; vertical-align: top; }img.fr-dib.fr-fil {margin-left: 0;}img.fr-dib.fr-fir {margin-right: 0;}</style></head><body>'.$message.'</body></html>';
                $save['displayName'] = $param->displayName;
                $save['fromAddress'] = $param->fromAddress;
                $save['replyToAddress'] = $param->replyToAddress;
            }
            if ($param->selectedPlatform != 'email') {
                $save["custom_url"] = $param->custom_url;
                $save["redirect_url"] = $param->redirect_url;
                //$save["deep_link"] = $param->deep_link;

                if ($param->selectedPlatform == 'android') {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                } else {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                    $save["limit_this_push_to_iPad_devices"] = $param->limit_this_push_to_iPad_devices;
                    $save["limit_this_push_to_iphone_and_ipod_devices"] = $param->limit_this_push_to_iphone_and_ipod_devices;
                }
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
            //$save["type"] = $param->type;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');
            $campaignId = $param->campaignId;
        }//End foreach

        if ($campaignId == '') {
            $id = $this->campaign_model->savePushNotificationCampaign($save);

            if ($param->copy_push != '' && $param->selectedPlatform != 'email') {

                if ($param->selectedPlatform == 'android') {

                    $save['push_title'] = '';
                    $save['platform'] = 'iOS';
                }
                if ($param->selectedPlatform == 'iOS') {
                    $save['platform'] = 'android';
                    $save['push_title'] = $param->copy_title;
                }
                $this->campaign_model->savePushNotificationCampaign($save);
            }

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

            if ($param->selectedPlatform == 'android') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                        if ($extraPackage->androidCampaign != 'unlimited') {
                            $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                        } else {
                            $update['androidCampaign'] = $extraPackage->androidCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $androidCampaign = $userPackage->androidCampaign;
                        $updateAndroidCampaign = $androidCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'androidCampaign' => $updateAndroidCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }

                if ($param->copy_push != '') {

                    if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                        if ($extraPackage->iOSCampaign != 'unlimited') {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                        } else {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $iOSCampaign = $userPackage->iOSCampaign;
                        $updateiOSCampaign = $iOSCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'iOSCampaign' => $updateiOSCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }

            if ($param->selectedPlatform == 'iOS') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                        if ($extraPackage->iOSCampaign != 'unlimited') {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                        } else {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $iOSCampaign = $userPackage->iOSCampaign;
                        $updateiOSCampaign = $iOSCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'iOSCampaign' => $updateiOSCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }

                if ($param->copy_push != '') {

                    if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                        if ($extraPackage->androidCampaign != 'unlimited') {
                            $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                        } else {
                            $update['androidCampaign'] = $extraPackage->androidCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $androidCampaign = $userPackage->androidCampaign;
                        $updateAndroidCampaign = $androidCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'androidCampaign' => $updateAndroidCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }

            if ($param->selectedPlatform == 'email') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                        if ($extraPackage->emailCampaign != 'unlimited') {
                            $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                        } else {
                            $update['emailCampaign'] = $extraPackage->emailCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $emailCampaign = $userPackage->emailCampaign;
                        $updateEmailCampaign = $emailCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'emailCampaign' => $updateEmailCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }
        } else {
            $save["id"] = $campaignId;
            $save["isDraft"] = 0;
            $save["modifiedDate"] = date('YmdHis');
            $this->campaign_model->updatePushNotificationCampaign($save);
            $id = $campaignId;
        }
        echo $id;
    }

    // function used for store data in php session.
     public function brandPaymentByPaypal() {
         //Add vat and amount in email
         $total_appgroup = 0;
         $amount = $this->input->post('amount');
         $brand_checkout = $this->input->post('brand_checkout');
         $total_appgroup = $this->input->post('total_appgroup');
         $packageid = $this->input->post('packageid');
         $currency = 'USD';
         $paypal_details = array('amount' => $amount, 'currency' => $currency, 'brand_checkout' => $brand_checkout, 'packageid' => $packageid, 'total_appgroup' => $total_appgroup);
         $this->session->set_userdata('paypal_details', $paypal_details);
         echo 'Success';
     }

   // function used for redirect paypal login page with package description, pay amount and currency etc.
     public function paypalTokenAuthorize() {
         $paypal_details = $this->session->userdata('paypal_details');
         $amount = $paypal_details['amount'];
         $currencyID = $paypal_details['currency'];
         $brand_checkout = $paypal_details['brand_checkout'];
         $packageid = $paypal_details['packageid'];
         $paymentType = 'Sale';
         $token = '';

         $returnUrl = base_url() . 'appUser/paypalRecurringToken'; //base_url(). // paypal return url
         $cancelUrl = base_url() . 'appUser/'; // paypal cancel url

         if ($brand_checkout == "brand_signup_checkout") {
             $desc = 'Payment of purchase account'; // used for singup payment description
         } else {
             $desc = 'Payment of hurree store'; // used for store payment description
         }

         if ($packageid == 1 || $packageid == 2 || $packageid == 3 || $packageid == 4 || $packageid == 7 || $packageid == 8 || $packageid == 9) { // used for oneOff payment
             $nvpStr = "&PAYMENTREQUEST_0_PAYMENTACTION=$paymentType&PAYMENTREQUEST_0_AMT=$amount&PAYMENTREQUEST_0_CURRENCYCODE=$currencyID&RETURNURL=$returnUrl&CANCELURL=$cancelUrl";
         } else { // used for recurring payment request
             $nvpStr = "&L_BILLINGTYPE0=RecurringPayments&L_BILLINGAGREEMENTDESCRIPTION0=$desc&RETURNURL=$returnUrl&CANCELURL=$cancelUrl&PAYMENTREQUEST_n_AMT=$amount&PAYMENTREQUEST_0_PAYMENTACTION=$paymentType&PAYMENTREQUEST_0_CURRENCYCODE=$currencyID";
         }
 	//When a customer initiates a check out, call SetExpressCheckout to specify the payment action, amount of payment, return URL, and cancel URL.
         $httpParsedResponseAr = $this->PPHttpPost('SetExpressCheckout', $nvpStr);
         $token = $httpParsedResponseAr['TOKEN'];
         if ($_SERVER['HTTP_HOST'] == 'hurree.local' || $_SERVER['HTTP_HOST'] == 'hurree1.local' || $_SERVER['HTTP_HOST'] == 'stage.hurree.co' || $_SERVER['HTTP_HOST'] == 'localhost'|| $_SERVER['HTTP_HOST'] == 'test.hurree.co') { // used for redirect on sandbox paypal page
             redirect("https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=$token");
         } else { // used for redirect on production paypal page
             redirect("https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=$token");
         }
     }


    // function used for paypal return url, after paypal success response.
     public function paypalRecurringToken() {
         $token = $_GET['token']; //EC-0S431878A2959822T; // paypal return id
         $paypal_details = $this->session->userdata('paypal_details');
         $amount = $paypal_details['amount'];
         $currencyID = $paypal_details['currency'];
         $brand_checkout = $paypal_details['brand_checkout'];
         $packageid = $paypal_details['packageid'];
         $total_appgroup = $paypal_details['total_appgroup'];

         $paymentType = 'Sale';

         $login = $this->administrator_model->front_login_session();
         $userid = $login->user_id;
         $businessId = $login->businessId;
         $email = $login->email;
         $username = $login->username;
         $firstname = $login->firstname;
         $lastname = $login->lastname;

         if ($brand_checkout == "brand_signup_checkout") {
             $desc = 'Payment of purchase account'; // used for paypal signup payment description
         } else {
             $desc = 'Payment of hurree store'; // used for paypal store payment description
         }
         $payment_trail = 'paid'; // used for trail user to paid user

         if (isset($token)) {
             $nvpStr = "&TOKEN=$token";
 		// send request for get paypal payment user all details related Name, address
             $httpParsedResponseAr = $this->PPHttpPost('GetExpressCheckoutDetails', $nvpStr);
           //  echo '<pre>'; print_r($httpParsedResponseAr); die;
             if ($httpParsedResponseAr['ACK'] == 'Success') {

                 $firstname = isset($httpParsedResponseAr['FIRSTNAME']) ? urldecode($httpParsedResponseAr['FIRSTNAME']) : '';
                 $lastname = isset($httpParsedResponseAr['LASTNAME']) ? urldecode($httpParsedResponseAr['LASTNAME']) : '';
                 $address = isset($httpParsedResponseAr['SHIPTOCITY']) ? urldecode($httpParsedResponseAr['SHIPTOCITY']) . ', ' : '';
                 $address .= isset($httpParsedResponseAr['SHIPTOSTATE']) ? urldecode($httpParsedResponseAr['SHIPTOSTATE']) . ', ' : '';
                 $address .= isset($httpParsedResponseAr['SHIPTOCOUNTRYNAME']) ? urldecode($httpParsedResponseAr['SHIPTOCOUNTRYNAME']) : '';
                 $city = isset($httpParsedResponseAr['SHIPTOCITY']) ? urldecode($httpParsedResponseAr['SHIPTOCITY']) : '';
                 $state = isset($httpParsedResponseAr['SHIPTOSTATE']) ? urldecode($httpParsedResponseAr['SHIPTOSTATE']) : '';
                 $country = isset($httpParsedResponseAr['SHIPTOCOUNTRYNAME']) ? urldecode($httpParsedResponseAr['SHIPTOCOUNTRYNAME']) : '';
                 $pincode = isset($httpParsedResponseAr['SHIPTOZIP']) ? urldecode($httpParsedResponseAr['SHIPTOZIP']) : '';
                 $email = isset($httpParsedResponseAr['EMAIL']) ? urldecode($httpParsedResponseAr['EMAIL']) : '';

                 $currDate = date('YmdHis');
                 $amount = urlencode($paypal_details['amount']);
                 $currencyID = urlencode($paypal_details['currency']);
                 $paymentType = urlencode($paymentType);

                 $payerId = $httpParsedResponseAr['PAYERID'];
 		// send request for get paypal payment agreement details
                 $nvpStr = "&TOKEN=$token&PAYERID=$payerId&PAYMENTREQUEST_0_AMT=$amount&PAYMENTREQUEST_0_PAYMENTACTION=$paymentType&PAYMENTREQUEST_0_CURRENCYCODE=$currencyID";

                 $httpParsedResponseAr = $this->PPHttpPost('DoExpressCheckoutPayment', $nvpStr);

                 $expressCheckoutResponse = $httpParsedResponseAr;

               //  echo '<pre>'; print_r($httpParsedResponseAr); exit;

                 if ($brand_checkout == "brand_store_checkout" && ($packageid == 1 || $packageid == 2 || $packageid == 3 || $packageid == 4 || $packageid == 7 || $packageid == 8 || $packageid == 9)) {
                     if ($httpParsedResponseAr['ACK'] == 'Success') {
                         $billingInfoArr = array(
                             'firstname' => $firstname,
                             'lastname' => $lastname,
                             'address' => $address,
                             'city' => $city,
                             'state' => $state,
                             'country' => $country,
                             'pincode' => $pincode,
                             'email' => $email
                         );
                         $oneOffArr = array('userid' => $userid, 'businessId' => $businessId, 'packageid' => $packageid, 'payerId' => $payerId, 'httpResponse' => $httpParsedResponseAr, 'currency' => $currencyID, 'billingInfo' => $billingInfoArr);
                         $this->brandOneOffPaymentByPaypal($oneOffArr);
                         exit;
                     } else {
                         redirect('appUser');
                         exit;
                     }
                 }

                 //$profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z")); //
                 /* for live */
                 $billingPeried = 'Month';
                 /* for staging */
                // $billingPeried = 'Day';

                 $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z")); // recurring profile start date

                 $nvpStr = "&TOKEN=$token&PAYERID=$payerId&PROFILESTARTDATE=$profileStartDate&DESC=$desc&BILLINGPERIOD=$billingPeried&BILLINGFREQUENCY=1&AMT=$amount&COUNTRYCODE=US&CURRENCYCODE=$currencyID&&MAXFAILEDPAYMENTS=3";
 		// send request for send request for create paypal recurring profile id
                 $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);

                 //echo '<pre>'; print_r($httpParsedResponseAr); exit;
                 if ($httpParsedResponseAr['ACK'] == 'Success') {
                     if ($brand_checkout == "brand_signup_checkout") {
                         $date = date('YmdHis');
                         $user['user_Id'] = $userid;
                         $user['accountType'] = 'paid';
                         $user['modifiedDate'] = $date;
                         $user['paypal_profileid'] = $httpParsedResponseAr['PROFILEID']; //added for recurring payment
                         $user['paypal_response'] = json_encode($httpParsedResponseAr); //added for recurring payment

                         $last_id = $this->user_model->insertsignup($user);

                         /* code start save billing info details */
                         $userBillingInfo = $this->brand_model->getBillingInfo($businessId);
                         if (count($userBillingInfo) > 0) {
                             $billingInfoArr = array(
                                 'id' => $userBillingInfo->id,
                                 'businessId' => $businessId,
                                 'address' => $address,
                                 'city' => $city,
                                 'state' => $state,
                                 'Country' => $country,
                                 'pincode' => $pincode,
                                 'card_firstname' => $firstname,
                                 'card_lastname' => $lastname,
                                 'card_number' => '',
                                 'expiry_date' => '',
                                 'email' => $email,
                                 'isDelete' => 0,
                                 'createdDate' => date('Y-m-d H:i:s')
                             );

                             $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                         } else {
                             $billingInfoArr = array(
                                 'businessId' => $businessId,
                                 'address' => $address,
                                 'city' => $city,
                                 'state' => $state,
                                 'Country' => $country,
                                 'pincode' => $pincode,
                                 'card_firstname' => $firstname,
                                 'card_lastname' => $lastname,
                                 'card_number' => '',
                                 'expiry_date' => '',
                                 'email' => $email,
                                 'isDelete' => 0,
                                 'createdDate' => date('Y-m-d H:i:s')
                             );

                             $last_id = $this->brand_model->addBillingInfo($billingInfoArr);


                         }


                         /*  ************* insert data in payment_status table * ********************************************* */
      $responseJson = json_encode($expressCheckoutResponse);
 	// save paypal json response in payal_status table
      $data = array('user_id'=> $userid, "payment_status"=> urldecode($expressCheckoutResponse['PAYMENTINFO_0_PAYMENTSTATUS']),"amount"=>urldecode($expressCheckoutResponse['PAYMENTINFO_0_AMT']), "currency"=>urldecode($expressCheckoutResponse['PAYMENTINFO_0_CURRENCYCODE']),"transationId"=>urldecode($expressCheckoutResponse['PAYMENTINFO_0_TRANSACTIONID']),"txn_type"=>"recurring_payment","paymentInfo"=>$responseJson,"IsDelete"=>0, 'isActive'=>1, 'createdDate'=>date('YmdHis'));


        $payment = array();
        $insertedPaymentId = $this->payment_model->savepayment($data);

         /*  ************* End insert data in payment_status table * ********************************************* */

                         //// SEND  EMAIL START for purchase user
                         $this->emailConfig();   //Get configuration of email
                         //// GET EMAIL FROM DATABASE

                         $email_template = $this->email_model->getoneemail('purchase_account');

                         //// MESSAGE OF EMAIL
                         $messages = $email_template->message;

                         $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                         $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                         $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                         //// replace strings from message
                         $messages = str_replace('{Username}', ucfirst($username), $messages);
                         $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                         $messages = str_replace('{amount}', '$' . $amount, $messages);

                         //// FROM EMAIL
                         $this->email->from('hello@marketmyapp.co', 'Hurree');
                         $this->email->to($email);
                         $this->email->subject($email_template->subject);
                         $this->email->message($messages);
                         $this->email->send();    ////  EMAIL SEND

 			// used for send EMAIL Admin USER
                         $this->emailConfig();   //Get configuration of email
                         //// GET EMAIL FROM DATABASE

                         $email_template = $this->email_model->getoneemail('admin_mail_user_signup');

                         //// MESSAGE OF EMAIL
                         $messages = $email_template->message;

                         //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
                         $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                         $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                         $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                         //// replace strings from message
                         $messages = str_replace('{firstname}', ucfirst($firstname), $messages);
                         $messages = str_replace('{lastname}', ucfirst($lastname), $messages);
                         $messages = str_replace('{email}', $email, $messages);
                         $messages = str_replace('{username}', ucfirst($username), $messages);
                         $messages = str_replace('{amount}', '$' . $amount, $messages);

                         //// FROM EMAIL
                         $this->email->from('hello@marketmyapp.co', 'Hurree');
                         $this->email->to('Business@hurree.co');
                         $this->email->subject($email_template->subject);
                         $this->email->message($messages);
                         $this->email->send();

                         $this->session->unset_userdata('paypal_details');
                         $this->session->unset_userdata('paypalToken');

                         redirect('appUser');  // exit;
                     } else {
                         $recurringPayment['brand_store_payment_id'] = '';
                         $recurringPayment['user_id'] = $userid;
                         $recurringPayment['package_id'] = $packageid;
                         $recurringPayment['purchasedOn'] = date('YmdHis');
                         $recurringPayment['amount'] = $amount;
                         $recurringPayment['currency'] = $currencyID;
                         $recurringPayment['profile_id'] = $httpParsedResponseAr['PROFILEID'];
                         $recurringPayment['payment_response'] = json_encode($httpParsedResponseAr);
                         $recurringPayment['isActive'] = 1;
                         $recurringPayment['isDelete'] = 0;
                         $recurringPayment['createdDate'] = date('YmdHis');

                         $last_payment_id = $this->brand_model->savepayment($recurringPayment);

                         $package = $this->brand_model->getOnePackage($packageid);
                         $description = $package->desription;
                         $quantity = $package->quantity;

                         //If user don't have any entry in user_profile_info table
                         $packageInfo = $this->brand_model->getPackagesInfo($businessId);
                         if (count($packageInfo) == 0) {
                             //Insert
                             $date = date('YmdHis');
                             $recurringInsert['user_pro_id'] = '';
                             $recurringInsert['user_id'] = $userid;
                             $recurringInsert['totalCampaigns'] = 0;
                             $recurringInsert['totalAppGroup'] = 0;
                             $recurringInsert['androidCampaign'] = 0;
                             $recurringInsert['iOSCampaign'] = 0;
                             $recurringInsert['emailCampaign'] = 0;
                             $recurringInsert['crossChannel'] = 0;
                             $recurringInsert['inAppMessaging'] = 0;
                             $recurringInsert['webhook'] = 0;
                             $recurringInsert['createdDate'] = $date;
                             $last_insert_id = $this->brand_model->savePackage($recurringInsert);
                         }

                         $extraPackage['businessId'] = $businessId;
                         $extraPackage['packageid'] = $packageid;
                         $userExtraPackage = $this->brand_model->getExtraPackage($extraPackage);

                         if (count($userExtraPackage) == 0) {
                             //Insert
                             if ($package->desription == '') {
                                 $quantity = '';
                             } else {
                                 $quantity = $package->quantity;
                             }
                             if ($packageid == 6 && $total_appgroup >= 1) {
                                 $quantity = $total_appgroup;
                             }
                             if ($packageid == 1) {
                                 $userInsert['androidCampaign'] = 5;
                                 $userInsert['iOSCampaign'] = 5;
                                 $userInsert['emailCampaign'] = 25;
                             } else if ($packageid == 2) {
                                 $userInsert['androidCampaign'] = 10;
                                 $userInsert['iOSCampaign'] = 10;
                                 $userInsert['emailCampaign'] = 50;
                             } else if ($packageid == 3) {
                                 $userInsert['androidCampaign'] = 15;
                                 $userInsert['iOSCampaign'] = 15;
                                 $userInsert['emailCampaign'] = 75;
                             } else if ($packageid == 4) {
                                 $userInsert['androidCampaign'] = 'Unlimited';
                                 $userInsert['iOSCampaign'] = 'Unlimited';
                                 $userInsert['emailCampaign'] = 'Unlimited';
                             } else if ($packageid == 7) {
                                 $userInsert['crossChannel'] = $quantity;
                                 $quantity = 0;
                             } else if ($packageid == 8) {
                                 $userInsert['inAppMessaging'] = $quantity;
                                 $quantity = 0;
                             }
                             else if ($packageid == 9) {
                                 $userInsert['webhook'] = $quantity;
                                 $quantity = 0;
                             }
                             $current_date = date('YmdHis');
                             $userInsert['user_extra_packages_id'] = '';
                             $userInsert['userid'] = $userid;
                             $userInsert['packageid'] = $packageid;
                             $userInsert['quantity'] = $quantity;
                             $userInsert['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                             $userInsert['createdDate'] = $current_date;

                             $this->brand_model->saveExtraPackage($userInsert);
                         } else {
                             //Update
                             if ($package->desription == '') {
                                 $quantity = '';
                             } else {
                                 $quantity = $package->quantity;
                             }
                             if ($packageid == 6 && $total_appgroup >= 1) {
                                 $quantity1 = $userExtraPackage->quantity + $total_appgroup;
                                 $quantity = $total_appgroup;
                             }

                             if ($packageid == 1) {
                                 $update['androidCampaign'] = $userExtraPackage->androidCampaign + 5;
                                 $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 5;
                                 $update['emailCampaign'] = $userExtraPackage->emailCampaign + 25;
                             } else if ($packageid == 2) {
                                 $update['androidCampaign'] = $userExtraPackage->androidCampaign + 10;
                                 $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 10;
                                 $update['emailCampaign'] = $userExtraPackage->emailCampaign + 50;
                             } else if ($packageid == 3) {
                                 $update['androidCampaign'] = $userExtraPackage->androidCampaign + 15;
                                 $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 15;
                                 $update['emailCampaign'] = $userExtraPackage->emailCampaign + 75;
                             } else if ($packageid == 4) {
                                 $update['androidCampaign'] = 'Unlimited';
                                 $update['iOSCampaign'] = 'Unlimited';
                                 $update['emailCampaign'] = 'Unlimited';
                             } else if ($packageid == 7) {
                                 $update['crossChannel'] = $userExtraPackage->crossChannel + 5;
                                 $quantity1 = 0;
                             } else if ($packageid == 8) {
                                 $update['inAppMessaging'] = $userExtraPackage->inAppMessaging + 5;
                                 $quantity1 = 0;
                             }
                             else if ($packageid == 9) {
                                 $update['webhook'] = $userExtraPackage->webhook + 5;
                                 $quantity1 = 0;
                             }
                             $current_date = date('YmdHis');
                             $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;
                             $update['quantity'] = $quantity1;
                             $update['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
                             $update['createdDate'] = $current_date;

                             $this->brand_model->updateExtraPackage($update);
                         }
                             /* code start save billing info details */
                             $userBillingInfo = $this->brand_model->getBillingInfo($businessId);
                             if (count($userBillingInfo) > 0) {
                                 $billingInfoArr = array(
                                     'id' => $userBillingInfo->id,
                                     'businessId' => $businessId,
                                     'address' => $address,
                                     'city' => $city,
                                     'state' => $state,
                                     'Country' => $country,
                                     'pincode' => $pincode,
                                     'card_firstname' => $firstname,
                                     'card_lastname' => $lastname,
                                     'card_number' => '',
                                     'expiry_date' => '',
                                     'email' => $email,
                                     'isDelete' => 0,
                                     'createdDate' => date('Y-m-d H:i:s')
                                 );

                                 $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                             } else {
                                 $billingInfoArr = array(
                                     'businessId' => $businessId,
                                     'address' => $address,
                                     'city' => $city,
                                     'state' => $state,
                                     'Country' => $country,
                                     'pincode' => $pincode,
                                     'card_firstname' => $firstname,
                                     'card_lastname' => $lastname,
                                     'card_number' => '',
                                     'expiry_date' => '',
                                     'email' => $email,
                                     'isDelete' => 0,
                                     'createdDate' => date('Y-m-d H:i:s')
                                 );

                                 $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
                             }
                             /*                             * ********************************************* */

                             //// SEND  EMAIL START PURCHASE USER
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
                                 $packageName = $quantity . " " . $package->desription;
                             }
                             $price = "$" . $amount;
                             //// replace strings from message
                             $messages = str_replace('{Username}', ucfirst($username), $messages);
                             $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                             $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                             $messages = str_replace('{price}', $price, $messages);

                             //// FROM EMAIL
                             $this->email->from('hello@marketmyapp.co', 'Hurree');
                             $this->email->to($email);
                             $this->email->subject($email_template->subject);
                             $this->email->message($messages);
                             $this->email->send();    ////  EMAIL SEND
                             //Email send to Admin

 			    //// SEND EMAIL START ADMIN USER
                             $this->emailConfig();   //Get configuration of email
                             //// GET EMAIL FROM DATABASE

                             $email_template = $this->email_model->getoneemail('admin_user_buy_extra_package');

                             //// MESSAGE OF EMAIL
                             $messages = $email_template->message;

                             //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
                             $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                             $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                             $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                             if ($package->quantity == 0) {
                                 $packageName = $package->desription;
                             } else {
                                 $packageName = $quantity . " " . $package->desription;
                             }
                             $price = "$" . $amount;
                             //// replace strings from message
                             $messages = str_replace('{Username}', ucfirst($username), $messages);
                             $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                             $messages = str_replace('{What_user_purchased}', $packageName, $messages);
                             $messages = str_replace('{price}', $price, $messages);

                             //// FROM EMAIL
                             $this->email->from('hello@marketmyapp.co', 'Hurree');
                             $this->email->to('Business@hurree.co');
                             $this->email->subject($email_template->subject);
                             $this->email->message($messages);
                             $this->email->send();
                             redirect('appUser');
                         }

                 } else {
                     echo $httpParsedResponseAr['L_LONGMESSAGE0'];
                 }
             }
         } else {
             redirect(base_url());
         }
     }

     // this function is used for save oneOff payment data in database via pay via paypal button
       public function brandOneOffPaymentByPaypal($data) {
           $userid = $data['userid'];
           $businessId = $data['businessId'];
           $packageid = $data['packageid'];
           $currency = $data['currency'];
           $httpParsedResponseAr['AMT'] = urlencode($data['httpResponse']['PAYMENTINFO_0_AMT']);
           $httpParsedResponseAr['TRANSACTIONID'] = $data['httpResponse']['TOKEN'];
           $httpParsedResponseAr['ACK'] = $data['httpResponse']['ACK'];
           $httpParsedResponseAr['CORRELATIONID'] = $data['httpResponse']['CORRELATIONID'];

           $payment['brand_store_payment_id'] = '';
           $payment['user_id'] = $userid;
           $payment['package_id'] = $packageid;
           $payment['purchasedOn'] = date('YmdHis');
           $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
           $payment['currency'] = $currency;
           $payment['transaction_id'] = $httpParsedResponseAr['TRANSACTIONID'];
           $payment['payment_response'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '|CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
           $payment['isActive'] = 1;
           $payment['isDelete'] = 0;
           $payment['createdDate'] = date('YmdHis');

           $last_payment_id = $this->brand_model->savepayment($payment);

           /* code start save billing info details */
           $userBillingInfo = $this->brand_model->getBillingInfo($businessId);
           if (count($userBillingInfo) > 0) {
               $billingInfoArr = array(
                   'id' => $userBillingInfo->id,
                   'businessId' => $businessId,
                   'address' => $data['billingInfo']['address'],
                   'city' => $data['billingInfo']['city'],
                   'state' => $data['billingInfo']['state'],
                   'Country' => $data['billingInfo']['country'],
                   'pincode' => $data['billingInfo']['pincode'],
                   'card_firstname' => $data['billingInfo']['firstname'],
                   'card_lastname' => $data['billingInfo']['lastname'],
                   'card_number' => '',
                   'expiry_date' => '',
                   'email' => $data['billingInfo']['email'],
                   'isDelete' => 0,
                   'createdDate' => date('Y-m-d H:i:s')
               );

               $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
           } else {
               $address = $data['billingInfo']['address'];
               $city = $data['billingInfo']['city'];
               $state = $data['billingInfo']['state'];
               $country = $data['billingInfo']['country'];
               $pincode = $data['billingInfo']['pincode'];
               $firstName = $data['billingInfo']['firstname'];
               $lastName = $data['billingInfo']['lastname'];
               $email = $data['billingInfo']['email'];
               $billingInfoArr = array(
                   'businessId' => $businessId,
                   'address' => $address,
                   'city' => $city,
                   'state' => $state,
                   'Country' => $country,
                   'pincode' => $pincode,
                   'card_firstname' => $firstName,
                   'card_lastname' => $lastName,
                   'card_number' => '',
                   'expiry_date' => $email,
                   'isDelete' => 0,
                   'createdDate' => date('Y-m-d H:i:s')
               );

               $last_id = $this->brand_model->addBillingInfo($billingInfoArr);
           }
           /************************************************/

           $package = $this->brand_model->getOnePackage($packageid);
           $description = $package->desription;
           $quantity = $package->quantity;

           //If user don't have any entry in user_profile_info table
           $packageInfo = $this->brand_model->getPackagesInfo($businessId);
           if (count($packageInfo) == 0) {
               //Insert
               $date = date('YmdHis');
               $insert['user_pro_id'] = '';
               $insert['user_id'] = $userid;
               $insert['totalIosApps'] = 0;
               $insert['totalAndroidApps'] = 0;
               $insert['totalCampaigns'] = 0;
               $insert['totalAppGroup'] = 0;
               $insert['androidCampaign'] = 0;
               $insert['iOSCampaign'] = 0;
               $insert['emailCampaign'] = 0;
               $insert['crossChannel'] = 0;
               $insert['inAppMessaging'] = 0;
               $insert['webhook'] = 0;
               $insert['createdDate'] = $date;
               $last_insert_id = $this->brand_model->savePackage($insert);
           }

           $extraPackage['businessId'] = $businessId;
           $extraPackage['packageid'] = $packageid;
           $userExtraPackage = $this->brand_model->getExtraPackage($extraPackage);

           if (count($userExtraPackage) == 0) {
               //Insert
               if ($package->desription == 'Unlimited Campaigns') {
                   $quantity = 'unlimited';
               } else {
                   $quantity = $package->quantity;
               }
               if ($packageid == 1) {
                   $userInsert['androidCampaign'] = 5;
                   $userInsert['iOSCampaign'] = 5;
                   $userInsert['emailCampaign'] = 25;
               } else if ($packageid == 2) {
                   $userInsert['androidCampaign'] = 10;
                   $userInsert['iOSCampaign'] = 10;
                   $userInsert['emailCampaign'] = 50;
               } else if ($packageid == 3) {
                   $userInsert['androidCampaign'] = 15;
                   $userInsert['iOSCampaign'] = 15;
                   $userInsert['emailCampaign'] = 75;
               } else if ($packageid == 4) {
                   $userInsert['androidCampaign'] = 'Unlimited';
                   $userInsert['iOSCampaign'] = 'Unlimited';
                   $userInsert['emailCampaign'] = 'Unlimited';
               } else if ($packageid == 7) {
                   $userInsert['crossChannel'] = 1;
               } else if ($packageid == 8) {
                   $userInsert['inAppMessaging'] = 5;
               }
               else if ($packageid == 9) {
                   $userInsert['webhook'] = 5;
               }

               $current_date = date('YmdHis');
               $userInsert['user_extra_packages_id'] = '';
               $userInsert['userid'] = $userid;
               $userInsert['packageid'] = $packageid;
               $userInsert['quantity'] = $quantity;
               $userInsert['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
               $userInsert['createdDate'] = $current_date;

               $this->brand_model->saveExtraPackage($userInsert);
           } else {
               //Update
               if ($package->desription == 'Unlimited Campaigns') {
                   $quantity = 'unlimited';
               } else {
                   $quantity = $package->quantity;
               }
               if ($packageid == 1) {
                   $update['androidCampaign'] = $userExtraPackage->androidCampaign + 5;
                   $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 5;
                   $update['emailCampaign'] = $userExtraPackage->emailCampaign + 25;
               } else if ($packageid == 2) {
                   $update['androidCampaign'] = $userExtraPackage->androidCampaign + 10;
                   $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 10;
                   $update['emailCampaign'] = $userExtraPackage->emailCampaign + 50;
               } else if ($packageid == 3) {
                   $update['androidCampaign'] = $userExtraPackage->androidCampaign + 15;
                   $update['iOSCampaign'] = $userExtraPackage->iOSCampaign + 15;
                   $update['emailCampaign'] = $userExtraPackage->emailCampaign + 75;
               } else if ($packageid == 4) {
                   $update['androidCampaign'] = 'Unlimited';
                   $update['iOSCampaign'] = 'Unlimited';
                   $update['emailCampaign'] = 'Unlimited';
               }

               $current_date = date('YmdHis');
               $update['user_extra_packages_id'] = $userExtraPackage->user_extra_packages_id;
               $update['quantity'] = $quantity;
               $update['expiry_date'] = date('Y-m-d', strtotime("+30 days"));
               $update['createdDate'] = $current_date;

               $this->brand_model->updateExtraPackage($update);

               //// SEND  EMAIL START PURCHASE USER
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
               $price = "$" . $amount;
               //// replace strings from message
               $messages = str_replace('{Username}', ucfirst($username), $messages);
               $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
               $messages = str_replace('{What_user_purchased}', $packageName, $messages);
               $messages = str_replace('{price}', $price, $messages);

               //// FROM EMAIL
               $this->email->from('hello@marketmyapp.co', 'Hurree');
               $this->email->to($email);
               $this->email->subject($email_template->subject);
               $this->email->message($messages);
               $this->email->send();    ////  EMAIL SEND

               //Email send to Admin
               $this->emailConfig();   //Get configuration of email
               //// GET EMAIL FROM DATABASE

               $email_template = $this->email_model->getoneemail('admin_user_buy_extra_package');

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
               $price = "$" . $amount;
               //// replace strings from message
               $messages = str_replace('{Username}', ucfirst($username), $messages);
               $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
               $messages = str_replace('{What_user_purchased}', $packageName, $messages);
               $messages = str_replace('{price}', $price, $messages);

               //// FROM EMAIL
               $this->email->from('hello@marketmyapp.co', 'Hurree');
               $this->email->to('Business@hurree.co');
               $this->email->subject($email_template->subject);
               $this->email->message($messages);
               $this->email->send();
           }

           redirect('appUser');
       }
   }

    function upldatecontactToHubspot($email) {
        //$email = 'nagender@qsstechnosoft.com';
        $oneConatctData = $this->getOneContactHubSpot($email);

        if ($oneConatctData['status'] == 200) {
            $vid = $oneConatctData['vid'];

            $arr = array(
                'properties' => array(
                    array(
                        'property' => 'lifecyclestage',
                        'value' => 'customer'
                    )
                )
            );

            $endpoint = 'https://api.hubapi.com/contacts/v1/contact/vid/' . $vid . '/profile?hapikey=' . HAPIKEY;

            $post_json = json_encode($arr);
            $ch = @curl_init();
            @curl_setopt($ch, CURLOPT_POST, true);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
            @curl_setopt($ch, CURLOPT_URL, $endpoint);
            @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = @curl_exec($ch);
            $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errors = curl_error($ch);
            @curl_close($ch);
//     		     	echo "curl Errors: " . $curl_errors;
//     		echo "\nStatus code: " . $status_code;
//     		     	echo "\nResponse: " . $response
            //;
            return $status_code;
        }

        return $oneConatctData['status'];
    }

    function getOneContactHubSpot($email) {

        $endpoint = 'https://api.hubapi.com/contacts/v1/contact/email/' . $email . '/profile?hapikey=' . HAPIKEY;

        $ch = @curl_init();
        //@curl_setopt($ch, CURLOPT_POST, true);
        //@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = @curl_exec($ch);
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch);
        @curl_close($ch);
        //     	echo "curl Errors: " . $curl_errors;
        //echo "\nStatus code: " . $status_code;
        //     	echo "\nResponse: " . $response;
        if ($status_code == 200) {
            $getssss = json_decode($response);
            $vid = $getssss->vid;
        } else {
            $vid = '';
        }

        $arr_staus = array(
            'status' => $status_code,
            'vid' => $vid
        );

        return $arr_staus;
    }

    function authorizeSales() {
        //echo oauthSalesforce();
    }

    function oauthSaleforcece() {

        $login = $this->administrator_model->front_login_session();
        $where['userId'] = $login->user_id;
        $select = '';
        $userDetails = $this->hubSpot_model->getSalesUserDetails($select, $where);

        $code = $_GET['code'];
        if (count($userDetails) == 0) {
            $save['usersaleId'] = '';
            $save['userId'] = $login->user_id;
            $save['code'] = $code;
            $save['cretaedDate'] = date('YmdHis');
            $last_id = $this->hubSpot_model->saveSalesUser($save);
        } else {
            $update['usersaleId'] = $userDetails->usersaleId;
            $update['code'] = $code;
            $update['modifiedDate'] = date('YmdHis');
            $last_id = $this->hubSpot_model->saveSalesUser($update);
        }


        if ($last_id != '') {
            // redirect for access token

            $status = getAccessTokenSalesForece($code, $login->user_id, $last_id);

            if ($status != '') {
                $this->session->set_userdata('salesCode', $code);
                redirect('appUser/crmPage');
            }
        }
    }

    /**
     * Disconnect saleforce account
     */
    function disconnectSalesForce() {
        $login = $this->administrator_model->front_login_session();


        $where['userId'] = $login->user_id;
        $select = 'usersaleId';
        $userDetails = $this->hubSpot_model->getSalesUserDetails($select, $where);
        //print_r($userDetails); die;
        $save['usersaleId'] = $userDetails->usersaleId;
        //$save['userId'] = $login->user_id;
        $save['code'] = '';
        $save['access_token'] = '';
        $save['refresh_token'] = '';
        $save['signature'] = '';
        $save['id_token'] = '';
        $save['instance_url'] = '';
        $save['id'] = '';
        $save['token_type'] = '';
        $save['issued_at'] = '';
        $save['modifiedDate'] = date('YmdHis');

        $id = $this->hubSpot_model->saveSalesUser($save);
        //	echo $this->db->last_query(); die;

        $this->session->unset_userdata('salesCode');
        redirect('appUser/crmPage');
    }

    function selectExportSite() {
        $this->load->view('3.1/selectExportSite');
    }

    function saveComposeAsDraft() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        //print_r($json); die;
        $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
            if ($param->selectedPlatform != 'email') {
                if ($param->push_icon != '') {

                    $ime = $param->push_icon;

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

                    $fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['push_icon'] = $filename;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_icon'] = $param->push_icon;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['push_icon'] = $dataCampaign->push_icon;
                    }
                }

                if ($param->push_img_url != '') {
                    $save['push_img_url'] = $param->push_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_img_url'] = '';
                    } else {
                        if ($save['push_icon'] != '') {
                            $save['push_img_url'] = '';
                        } else {
                            $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                            $save['push_img_url'] = $dataCampaign->push_img_url;
                        }
                    }
                }

                if ($param->expandedImage != '') {

                    $imexpanded = $param->expandedImage;

                    $imageExpanded = explode(';base64,', $imexpanded);
                    $size = getimagesize($imexpanded);
                    $type = $size['mime'];
                    $typea = explode('/', $type);
                    $extnsn = $typea[1];
                    $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                    $img_cont = str_replace(' ', '+', $imageExpanded[1]);
                    //$img_cont=$image[1];
                    $data = base64_decode($img_cont);
                    $im = imagecreatefromstring($data);
                    $filename1 = time() . '.' . $extnsn;

                    $fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['expandedImage'] = $filename1;
                } else {

                    if ($param->campaignId == '') {
                        $save['expandedImage'] = $param->expandedImage;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['expandedImage'] = $dataCampaign->expandedImage;
                    }
                }

                if ($param->expanded_img_url != '') {
                    $save['expanded_img_url'] = $param->expanded_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['expanded_img_url'] = '';
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        if ($save['expandedImage'] != '') {
                            $save['expanded_img_url'] = '';
                        } else {
                            $save['expanded_img_url'] = $dataCampaign->expanded_img_url;
                        }
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
            $save['push_notification_image'] = $param->push_notification_image;

            if ($param->selectedPlatform == 'android') {
                $save['push_title'] = $param->push_title;
                $save['push_message'] = $param->push_message;
                $save['summery_text'] = $param->summery_text;
            } else {
                $save['push_message'] = $param->push_message;
            }
            if ($param->selectedPlatform == 'email') {
                $save['push_title'] = $param->push_title;
                $message = str_replace("&lbrace;","{",$param->push_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                //$save['push_message'] = $message;
                $save['push_message'] = '<html><head><style>img.fr-dib { margin: 5px auto; display: block; float: none; vertical-align: top; }img.fr-dib.fr-fil {margin-left: 0;}img.fr-dib.fr-fir {margin-right: 0;}</style></head><body>'.$message.'</body></html>';
                $save['displayName'] = $param->displayName;
                $save['fromAddress'] = $param->fromAddress;
                $save['replyToAddress'] = $param->replyToAddress;
            }

            if ($param->selectedPlatform != 'email') {
                $save["custom_url"] = $param->custom_url;
                $save["redirect_url"] = $param->redirect_url;
                //$save["deep_link"] = $param->deep_link;
                if ($param->selectedPlatform == 'android') {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                } else {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                    $save["limit_this_push_to_iPad_devices"] = $param->limit_this_push_to_iPad_devices;
                    $save["limit_this_push_to_iphone_and_ipod_devices"] = $param->limit_this_push_to_iphone_and_ipod_devices;
                }
            }
        }
            if ($param->campaignId == '') {
                $save["isActive"] = 0;
                $save["isDraft"] = 1;

                //$save["type"] = $param->type;
                $save["createdDate"] = date('YmdHis');
                $save["modifiedDate"] = date('YmdHis');
                $this->campaign_model->savePushNotificationCampaign($save);

                $login = $this->administrator_model->front_login_session();
                $businessId = $login->businessId;

                $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

                if ($param->selectedPlatform == 'android') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                            if ($extraPackage->androidCampaign != 'unlimited') {
                                $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                            } else {
                                $update['androidCampaign'] = $extraPackage->androidCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $androidCampaign = $userPackage->androidCampaign;
                            $updateAndroidCampaign = $androidCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'androidCampaign' => $updateAndroidCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($param->selectedPlatform == 'iOS') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                            if ($extraPackage->iOSCampaign != 'unlimited') {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                            } else {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $iOSCampaign = $userPackage->iOSCampaign;
                            $updateiOSCampaign = $iOSCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'iOSCampaign' => $updateiOSCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($param->selectedPlatform == 'email') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                            if ($extraPackage->emailCampaign != 'unlimited') {
                                $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                            } else {
                                $update['emailCampaign'] = $extraPackage->emailCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $emailCampaign = $userPackage->emailCampaign;
                            $updateEmailCampaign = $emailCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'emailCampaign' => $updateEmailCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }
            } else {
                $save['id'] = $param->campaignId;
                $save["modifiedDate"] = date('YmdHis');
                $this->campaign_model->updatePushNotificationCampaign($save);
            }
            echo 1;
        //}
    }

    function saveDeliveryAsDraft() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        $params = json_decode($json);
        foreach ($params as $param) {
            if ($param->selectedPlatform != 'email') {
                if ($param->push_icon != '') {

                    $ime = $param->push_icon;

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

                    $fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['push_icon'] = $filename;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_icon'] = $param->push_icon;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['push_icon'] = $dataCampaign->push_icon;
                    }
                }

                if ($param->push_img_url != '') {
                    $save['push_img_url'] = $param->push_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_img_url'] = '';
                    } else {
                        if ($save['push_icon'] != '') {
                            $save['push_img_url'] = '';
                        } else {
                            $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                            $save['push_img_url'] = $dataCampaign->push_img_url;
                        }
                    }
                }

                if ($param->expandedImage != '') {

                    $imexpanded = $param->expandedImage;

                    $imageExpanded = explode(';base64,', $imexpanded);
                    $size = getimagesize($imexpanded);
                    $type = $size['mime'];
                    $typea = explode('/', $type);
                    $extnsn = $typea[1];
                    $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                    $img_cont = str_replace(' ', '+', $imageExpanded[1]);
                    //$img_cont=$image[1];
                    $data = base64_decode($img_cont);
                    $im = imagecreatefromstring($data);
                    $filename1 = time() . '.' . $extnsn;

                    $fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['expandedImage'] = $filename1;
                } else {

                    if ($param->campaignId == '') {
                        $save['expandedImage'] = $param->expandedImage;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['expandedImage'] = $dataCampaign->expandedImage;
                    }
                }

                if ($param->expanded_img_url != '') {
                    $save['expanded_img_url'] = $param->expanded_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['expanded_img_url'] = '';
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        if ($save['expandedImage'] != '') {
                            $save['expanded_img_url'] = '';
                        } else {
                            $save['expanded_img_url'] = $dataCampaign->expanded_img_url;
                        }
                    }
                }
            }

            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['list_id'] = $param->campaignList;
            $save['push_notification_image'] = $param->push_notification_image;

            if ($param->selectedPlatform == 'android') {
                $save['push_title'] = $param->push_title;
                $save['push_message'] = $param->push_message;
                $save['summery_text'] = $param->summery_text;
            } else {
                $save['push_message'] = $param->push_message;
            }

            if ($param->selectedPlatform == 'email') {
                $save['push_title'] = $param->push_title;
                $message = str_replace("&lbrace;","{",$param->push_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['push_message'] = $message;
                $save['displayName'] = $param->displayName;
                $save['fromAddress'] = $param->fromAddress;
                $save['replyToAddress'] = $param->replyToAddress;
            }
            if ($param->selectedPlatform != 'email') {
                $save["custom_url"] = $param->custom_url;
                $save["redirect_url"] = $param->redirect_url;
                //$save["deep_link"] = $param->deep_link;
                if ($param->selectedPlatform == 'android') {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                } else {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                    $save["limit_this_push_to_iPad_devices"] = $param->limit_this_push_to_iPad_devices;
                    $save["limit_this_push_to_iphone_and_ipod_devices"] = $param->limit_this_push_to_iphone_and_ipod_devices;
                }
            }

            $save["isActive"] = 0;
            $save["isDraft"] = 1;
            //$save["type"] = $param->type;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');
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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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


                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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
            } elseif ($param->deliveryType == 2) {


                if (isset($param->triggerAction)) {
                    if (is_array($param->triggerAction)) {
                        $triggerAction = implode(",", $param->triggerAction);
                    } else {
                        $triggerAction = '';
                    }
                } else {
                    $triggerAction = '';
                }

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

                $save["campaignDuration_endTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_endTime_date));

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
            //$this->campaign_model->savePushNotificationCampaign($save);

            if ($param->campaignId == '') {
                $this->campaign_model->savePushNotificationCampaign($save);

                $login = $this->administrator_model->front_login_session();
                $businessId = $login->businessId;

                $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

                if ($param->selectedPlatform == 'android') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                            if ($extraPackage->androidCampaign != 'unlimited') {
                                $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                            } else {
                                $update['androidCampaign'] = $extraPackage->androidCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $androidCampaign = $userPackage->androidCampaign;
                            $updateAndroidCampaign = $androidCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'androidCampaign' => $updateAndroidCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($param->selectedPlatform == 'iOS') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                            if ($extraPackage->iOSCampaign != 'unlimited') {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                            } else {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $iOSCampaign = $userPackage->iOSCampaign;
                            $updateiOSCampaign = $iOSCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'iOSCampaign' => $updateiOSCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($param->selectedPlatform == 'email') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                            if ($extraPackage->emailCampaign != 'unlimited') {
                                $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                            } else {
                                $update['emailCampaign'] = $extraPackage->emailCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $emailCampaign = $userPackage->emailCampaign;
                            $updateEmailCampaign = $emailCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'emailCampaign' => $updateEmailCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }
            } else {
                $save['id'] = $param->campaignId;
                $save["modifiedDate"] = date('YmdHis');
                $this->campaign_model->updatePushNotificationCampaign($save);
            }
            echo 1;
        } //End foreach
    }

    function saveTargetAsDraft() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        $params = json_decode($json);
        foreach ($params as $param) {
            if ($param->selectedPlatform != 'email') {
                if ($param->push_icon != '') {

                    $ime = $param->push_icon;

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

                    $fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['push_icon'] = $filename;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_icon'] = $param->push_icon;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['push_icon'] = $dataCampaign->push_icon;
                    }
                }

                if ($param->push_img_url != '') {
                    $save['push_img_url'] = $param->push_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_img_url'] = '';
                    } else {
                        if ($save['push_icon'] != '') {
                            $save['push_img_url'] = '';
                        } else {
                            $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                            $save['push_img_url'] = $dataCampaign->push_img_url;
                        }
                    }
                }

                if ($param->expandedImage != '') {

                    $imexpanded = $param->expandedImage;

                    $imageExpanded = explode(';base64,', $imexpanded);
                    $size = getimagesize($imexpanded);
                    $type = $size['mime'];
                    $typea = explode('/', $type);
                    $extnsn = $typea[1];
                    $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                    $img_cont = str_replace(' ', '+', $imageExpanded[1]);
                    //$img_cont=$image[1];
                    $data = base64_decode($img_cont);
                    $im = imagecreatefromstring($data);
                    $filename1 = time() . '.' . $extnsn;

                    $fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['expandedImage'] = $filename1;
                } else {

                    if ($param->campaignId == '') {
                        $save['expandedImage'] = $param->expandedImage;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['expandedImage'] = $dataCampaign->expandedImage;
                    }
                }

                if ($param->expanded_img_url != '') {
                    $save['expanded_img_url'] = $param->expanded_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['expanded_img_url'] = '';
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        if ($save['expandedImage'] != '') {
                            $save['expanded_img_url'] = '';
                        } else {
                            $save['expanded_img_url'] = $dataCampaign->expanded_img_url;
                        }
                    }
                }
            }

            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['list_id'] = $param->campaignList;
            $save['push_notification_image'] = $param->push_notification_image;

            if ($param->selectedPlatform == 'android') {
                $save['push_title'] = $param->push_title;
                $save['push_message'] = $param->push_message;
                $save['summery_text'] = $param->summery_text;
            } else {
                $save['push_message'] = $param->push_message;
            }

            if ($param->selectedPlatform == 'email') {
                $save['push_title'] = $param->push_title;
                $message = str_replace("&lbrace;","{",$param->push_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['push_message'] = $message;
                $save['displayName'] = $param->displayName;
                $save['fromAddress'] = $param->fromAddress;
                $save['replyToAddress'] = $param->replyToAddress;
            }
            if ($param->selectedPlatform != 'email') {
                $save["custom_url"] = $param->custom_url;
                $save["redirect_url"] = $param->redirect_url;
                //$save["deep_link"] = $param->deep_link;
                if ($param->selectedPlatform == 'android') {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                } else {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                    $save["limit_this_push_to_iPad_devices"] = $param->limit_this_push_to_iPad_devices;
                    $save["limit_this_push_to_iphone_and_ipod_devices"] = $param->limit_this_push_to_iphone_and_ipod_devices;
                }
            }

            $save["isActive"] = 0;
            $save["isDraft"] = 1;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');
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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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


                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                $save["campaignDuration_endTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_endTime_date));

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

            if (isset($param->segments)) {
                if (is_array($param->segments)) {
                    foreach ($param->segments as $segment) {
                        $seg[] = $segment[0];
                    }

                    $segments = implode(",", $seg);
                } else {
                    $segments = '';
                }
            } else {
                $segments = '';
            }

            if (isset($param->filters)) {
                if (is_array($param->filters)) {
                    foreach ($param->filters as $filter) {
                        $fil[] = $filter[0];
                    }
                    $filters = implode(",", $fil);
                } else {
                    $filters = '';
                }
            } else {
                $filters = '';
            }
            /* if($param->filters != 'filters'){
              foreach($param->filters as $filter){
              $fil[] = $filter[0];
              }
              $filters = implode(",",$fil);
              }else{
              $filters = '';
              } */
            /* if(is_array($param->filters)){
              foreach($param->filters as $filter){
              $fil[] = $filter[0];
              }
              $filters = implode(",",$fil);
              }else{
              $filters = '';
              } */



            /* if(isset($param->filters)){
              foreach($param->filters as $filter){
              $fil[] = $filter[0];
              }
              $filters = implode(",",$fil);
              }else{
              $filters = '';
              } */
            $save["segments"] = $segments;
            $save["filters"] = $filters;
            $save["send_to_users"] = $param->send_to_users;
            $save["receiveCampaignType"] = $param->receiveCampaignType;
            $save["no_of_users_who_receive_campaigns"] = $param->no_of_users_who_receive_campaigns;
            $save["messages_per_minute"] = $param->messages_per_minute;
        } //End foreach
        //$this->campaign_model->savePushNotificationCampaign($save);
        if ($param->campaignId == '') {
            $this->campaign_model->savePushNotificationCampaign($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);
            if ($param->selectedPlatform == 'android') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                        if ($extraPackage->androidCampaign != 'unlimited') {
                            $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                        } else {
                            $update['androidCampaign'] = $extraPackage->androidCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $androidCampaign = $userPackage->androidCampaign;
                        $updateAndroidCampaign = $androidCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'androidCampaign' => $updateAndroidCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }

            if ($param->selectedPlatform == 'iOS') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                        if ($extraPackage->iOSCampaign != 'unlimited') {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                        } else {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $iOSCampaign = $userPackage->iOSCampaign;
                        $updateiOSCampaign = $iOSCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'iOSCampaign' => $updateiOSCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }

            if ($param->selectedPlatform == 'email') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                        if ($extraPackage->emailCampaign != 'unlimited') {
                            $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                        } else {
                            $update['emailCampaign'] = $extraPackage->emailCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $emailCampaign = $userPackage->emailCampaign;
                        $updateEmailCampaign = $emailCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'emailCampaign' => $updateEmailCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }
        } else {
            $save['id'] = $param->campaignId;
            $save["modifiedDate"] = date('YmdHis');
            $this->campaign_model->updatePushNotificationCampaign($save);
        }
        echo 1;
    }

    function savePushNotificationCampaignDraft() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
            if ($param->selectedPlatform != 'email') {
                if ($param->push_icon != '') {

                    $ime = $param->push_icon;

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

                    $fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['push_icon'] = $filename;
                } else {
                    if ($param->campaignId == '') {
                        $save['push_icon'] = $param->push_icon;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['push_icon'] = $dataCampaign->push_icon;
                    }
                }

                if ($param->push_img_url != '') {
                    $save['push_img_url'] = $param->push_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_img_url'] = '';
                    } else {
                        if ($save['push_icon'] != '') {
                            $save['push_img_url'] = '';
                        } else {
                            $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                            $save['push_img_url'] = $dataCampaign->push_img_url;
                        }
                    }
                }

                if ($param->expandedImage != '') {

                    $imexpanded = $param->expandedImage;

                    $imageExpanded = explode(';base64,', $imexpanded);
                    $size = getimagesize($imexpanded);
                    $type = $size['mime'];
                    $typea = explode('/', $type);
                    $extnsn = $typea[1];
                    $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                    $img_cont = str_replace(' ', '+', $imageExpanded[1]);
                    //$img_cont=$image[1];
                    $data = base64_decode($img_cont);
                    $im = imagecreatefromstring($data);
                    $filename1 = time() . '.' . $extnsn;

                    $fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['expandedImage'] = $filename1;
                } else {
                    if ($param->campaignId == '') {
                        $save['expandedImage'] = $param->expandedImage;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['expandedImage'] = $dataCampaign->expandedImage;
                    }
                }

                if ($param->expanded_img_url != '') {
                    $save['expanded_img_url'] = $param->expanded_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['expanded_img_url'] = '';
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        if ($save['expandedImage'] != '') {
                            $save['expanded_img_url'] = '';
                        } else {
                            $save['expanded_img_url'] = $dataCampaign->expanded_img_url;
                        }
                    }
                }
            }

            $save['app_group_id'] = $param->groupId;
            $save['platform'] = $param->selectedPlatform;
            $save['campaignName'] = $param->campaignName;
            $save['message_category'] = $param->message_category;
            $save['automation'] = $param->automation;
            $save['persona_user_id'] = $param->campaignPersonaUser;
            $save['list_id'] = $param->campaignList;
            $save['push_notification_image'] = $param->push_notification_image;

            if ($param->selectedPlatform == 'android') {
                $save['push_title'] = $param->push_title;
                $save['push_message'] = $param->push_message;
                $save['summery_text'] = $param->summery_text;
            } else {
                $save['push_message'] = $param->push_message;
            }

            if ($param->selectedPlatform == 'email') {
                $save['push_title'] = $param->push_title;
                $message = str_replace("&lbrace;","{",$param->push_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['push_message'] = $message;
                $save['displayName'] = $param->displayName;
                $save['fromAddress'] = $param->fromAddress;
                $save['replyToAddress'] = $param->replyToAddress;
            }

            if ($param->selectedPlatform != 'email') {
                $save["custom_url"] = $param->custom_url;
                $save["redirect_url"] = $param->redirect_url;
                //$save["deep_link"] = $param->deep_link;
                if ($param->selectedPlatform == 'android') {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                } else {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                    $save["limit_this_push_to_iPad_devices"] = $param->limit_this_push_to_iPad_devices;
                    $save["limit_this_push_to_iphone_and_ipod_devices"] = $param->limit_this_push_to_iphone_and_ipod_devices;
                }
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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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

                        $save["ending_on_the_date"] = date('Y-m-d', strtotime($param->ending_on_the_date));

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


                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                        $save["intelligent_ending_on_the_date"] = date('Y-m-d', strtotime($param->intelligent_ending_on_the_date));

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

                $save["campaignDuration_endTime_date"] = date('Y-m-d', strtotime($param->campaignDuration_endTime_date));

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
            //$save["type"] = $param->type;
            $save["createdDate"] = date('YmdHis');
            $save["modifiedDate"] = date('YmdHis');
        }//End foreach
        //$this->campaign_model->savePushNotificationCampaign($save);
        if ($param->campaignId == '') {

            $this->campaign_model->savePushNotificationCampaign($save);

            $login = $this->administrator_model->front_login_session();
            $businessId = $login->businessId;

            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);
            if ($param->selectedPlatform == 'android' || $param->selectedPlatform == 'iOS') {
              $notificationRow = $this->brand_model->getNotificationByCampaignId($campaignId);
              if (count($notificationRow) > 0) {
                  $notification_arr = array(
                                          'notification_id' => $notificationRow[0]->notification_id,
                                          'isDelete' => '1',
                                          'modifiedDate' => date('Y-m-d H:i:s')
                                      );
                  $this->brand_model->saveNotificationDetails($notification_arr);
              }
              $notificationRow = $this->brand_model->getPushNotificationHistoryByCampaignId($campaignId);
              if (count($notificationRow) > 0) {
                  $notification_arr = array(
                                          'campaign_id' => $campaignId,
                                          'isDelete' => '1'
                                      );
                  $this->brand_model->updatePushNotificationSendHistory($notification_arr);
              }
            }
            if ($param->selectedPlatform == 'email') {
              $notificationRow = $this->brand_model->getNotificationByCampaignId($campaignId);
              if (count($notificationRow) > 0) {
                  $notification_arr = array(
                                          'notification_id' => $notificationRow[0]->notification_id,
                                          'isDelete' => '1',
                                          'modifiedDate' => date('Y-m-d H:i:s')
                                      );
                  $this->brand_model->saveNotificationDetails($notification_arr);
              }
              $notificationRow = $this->brand_model->getEmailNotificationHistoryByCampaignId($campaignId);
              if (count($notificationRow) > 0) {
                  $notification_arr = array(
                                          'campaignId' => $campaignId,
                                          'isDelete' => '1'
                                      );
                  $this->brand_model->updateEmailNotificationSendHistory($notification_arr);
              }
            }
            if ($param->selectedPlatform == 'android') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                        if ($extraPackage->androidCampaign != 'unlimited') {
                            $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                        } else {
                            $update['androidCampaign'] = $extraPackage->androidCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $androidCampaign = $userPackage->androidCampaign;
                        $updateAndroidCampaign = $androidCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'androidCampaign' => $updateAndroidCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }

            }

            if ($param->selectedPlatform == 'iOS') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                        if ($extraPackage->iOSCampaign != 'unlimited') {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                        } else {
                            $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $iOSCampaign = $userPackage->iOSCampaign;
                        $updateiOSCampaign = $iOSCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'iOSCampaign' => $updateiOSCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }

            if ($param->selectedPlatform == 'email') {
                if ($additional_profit != 1) {
                    if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                        if ($extraPackage->emailCampaign != 'unlimited') {
                            $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                        } else {
                            $update['emailCampaign'] = $extraPackage->emailCampaign;
                        }

                        $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                        $this->campaign_model->updateBrandUserExtraPackage($update);
                    } else {
                        //Update total campaigns
                        $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                        $emailCampaign = $userPackage->emailCampaign;
                        $updateEmailCampaign = $emailCampaign - 1;

                        $update = array(
                            'user_pro_id' => $userPackage->user_pro_id,
                            'emailCampaign' => $updateEmailCampaign
                        );
                        $this->campaign_model->updateBrandUserTotalCampaigns($update);
                    }
                }
            }
        } else {
            $save['id'] = $param->campaignId;
            $save["modifiedDate"] = date('YmdHis');
            $this->campaign_model->updatePushNotificationCampaign($save);
        }
        echo 1;
    }

    public function createInsightsCampaign($type = false) {
        if ($type == 2 || $type == 3) {
            echo "<center><h5>No customer exists in this category.</h4></center>";
        } else if ($type == 4) {
            echo "<center><h5>0 customer in VIP list.</h5></center>";
        } else if ($type == 5) {
            echo "<center><h5>No customer exists in this category.</h5></center>";
        } else if ($type == 6) {
            echo "<center><h5>No customer exists in this category.</h5></center>";
        } else if ($type == 7) {
            echo "<center><h5>No customer exists in this category.</h5></center>";
        }
    }

    public function getUsersByInsightsPage($campaigns_type, $app_group, $total) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $businessId = $login->businessId;
            $allUsers = array();

            if (isset($app_group) && ($app_group == 'all')) {
                $allUsers = $this->brand_model->getAllAppGroupsUsersByUserId($businessId);
            } else {
                $allUsers = $this->brand_model->getUsersByAppGroupId($businessId, $app_group);
            }

            if (count($allUsers) == 0) {
                return $allUsers;
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

            if ($campaigns_type == 'new') {
                foreach ($allUsers as $user) {
                    $userid = $user->external_user_id;
                    $results = $this->brand_model->countLoggedInTimeUsers($userid);
                    //echo '<pre>'; print_r($results); exit;
                    if (count($results) == 1) {
                        if (!in_array($user->external_user_id, $singleUserIds)) {
                            array_push($countSingleUserIds, $userid);
                            array_push($singleUserIds, $userid);
                        }
                    }
                }
                $allUsers = $singleUserIds;
                return $allUsers;
            } else if ($campaigns_type == 'returning') {
                foreach ($allUsers as $user) {
                    $userid = $user->external_user_id;
                    $results = $this->brand_model->countLoggedInTimeUsers($userid);
                    //echo '<pre>'; print_r($results); exit;
                    if (count($results) > 1) {
                        if (!in_array($user->external_user_id, $multipleUserIds)) {
                            array_push($multipleUserIds, $userid);
                            array_push($countMultipleUserIds, $userid);
                        }
                    }
                }
                $allUsers = $multipleUserIds;
                return $allUsers;
            } else if ($campaigns_type == 'vip') {
                foreach ($allUsers as $user) {
                    $userid = $user->external_user_id;
                    $results = $this->brand_model->countLoggedInTimeUsers($userid);
                    //echo '<pre>'; print_r($results); exit;
                    if (count($results) > 1) {
                        if (!in_array($user->external_user_id, $multipleUserIds)) {
                            if (count($countForVip) < 10) {
                                array_push($countForVip, $userid);
                                array_push($vipUserIds, $userid);
                            }
                        }
                    }
                }
                $allUsers = $vipUserIds;
                return $allUsers;
            } else if ($campaigns_type == 'best') {
                foreach ($allUsers as $user) {
                    $userid = $user->external_user_id;
                    $results = $this->brand_model->countLoggedInTimeUsers($userid);
                    //echo '<pre>'; print_r($results); exit;
                    if (count($results) > 1) {
                        if (!in_array($user->external_user_id, $multipleUserIds)) {
                            if (count($countForVip) < 10) {
                                array_push($countForVip, $userid);
                                array_push($vipUserIds, $userid);
                            }
                        }
                    }
                }
                $allUsers = $vipUserIds;
                return $allUsers;
            } else {
                return $allUsers;
            }
        } else {
            redirect(base_url());
        }
    }

    public function emailCampaigns($groupId = false) {

        $login = $this->administrator_model->front_login_session();
        //print_r($login); exit;
        $this->session->unset_userdata("emailCampaignPagination");

        if ($login->active != 0) {

            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            $groupData = $this->groupapp_model->getOneGroup($cookie_group[0]);
            $data['groupData'] = $groupData;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);

                $businessGroups = $this->groupapp_model->getGroups($login->businessId);
                //echo '<pre>';print_r($businessGroups); die;
                if (count($businessGroups) > 0) {
                    $app_g = $app_g2 = $app_g3 = array();
                    $data['groupApps'] = $businessGroups;
//                    foreach ($businessGroups as $businessGroup) {
//
//
//                        $data['groupApps'] = $this->groupapp_model->getGroupsWithAndroid($login->businessId);
//                        //echo '<pre>'; print_r($data['groupApps']); exit;
//                        if (count($data['groupApps']) > 0) {
//                            foreach ($data['groupApps'] as $groups) {
//                                if (!in_array($groups->app_group_id, $app_g)) {
//                                    $app_name = $groups->app_group_name;
//                                    if (!empty($groups->app_image)) {
//                                        $image = base_url() . "upload/apps/" . $groups->app_image;
//                                    } else {
//                                        $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                                    }
//                                    array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                    if (!empty($groups->app_image)) {
//                                        $app_name = $groups->app_group_name;
//                                        if (!empty($groups->app_image)) {
//                                            $image = base_url() . "upload/apps/" . $groups->app_image;
//                                        } else {
//                                            $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                        }
//                                        array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                        //echo $app_name; exit;
//                                        //  $app_g = $app_g;
//                                    }
//                                }
//                            }
//                            $app_g3 = $app_g + $app_g2;
//                            $app_g4 = $app_g5 = array();
//                            foreach ($app_g3 as $groups) {
//                                if (!in_array($groups['app_group_id'], $app_g5)) {
//                                    $app_name = $groups['app_group_name'];
//                                    $image = $groups['image'];
//                                    //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                                    array_push($app_g5, $groups['app_group_id']);
//                                    array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                                }
//                            }
//                            $data['groupApps'] = $app_g4;
//                            //echo '<pre>'; print_r($data['groupApps']); exit;
//                        }
//                        //echo '<pre>';
//                        //print_r($data['groupApps']); die;
//                    }
                }
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
                //echo '<pre>'; print_r($header['groups']); die;
                $groupApps = $this->groupapp_model->getUserGroupData($groupArray);
                //$data['groupApps'] = $this->groupapp_model->getAppUserGroupsWithImages($groupArray);
                $app_g = $app_g2 = $app_g3 = array();
                if (count($groupApps) > 0) {
//                    foreach ($data['groupApps'] as $groups) {
//                        if (!in_array($groups->app_group_id, $app_g)) {
//                            $app_name = $groups->app_group_name;
//                            if (!empty($groups->app_image)) {
//                                $image = base_url() . "upload/apps/" . $groups->app_image;
//                            } else {
//                                $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                            }
//                            array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                            if (!empty($groups->app_image)) {
//                                $app_name = $groups->app_group_name;
//                                if (!empty($groups->app_image)) {
//                                    $image = base_url() . "upload/apps/" . $groups->app_image;
//                                } else {
//                                    $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                }
//                                array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                //echo $app_name; exit;
//                                //  $app_g = $app_g;
//                            }
//                        }
//                    }
//                    $app_g3 = $app_g + $app_g2;
//                    $app_g4 = $app_g5 = array();
//                    foreach ($app_g3 as $groups) {
//                        if (!in_array($groups['app_group_id'], $app_g5)) {
//                            $app_name = $groups['app_group_name'];
//                            $image = $groups['image'];
//                            //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                            array_push($app_g5, $groups['app_group_id']);
//                            array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                        }
//                    }
                    $data['groupApps'] = $groupApps;
                    //echo '<pre>'; print_r($data['groupApps']); exit;
                }
            }

            //$email_campaigns = $this->campaign_model->getAllEmailCampaigns($login->businessId);
            //$data['email_campaigns'] = $email_campaigns;
            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->campaign_model->getAllEmailCampaigns($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/emailCampaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;
                $data['email_campaigns'] = $this->campaign_model->getAllEmailCampaigns($login->businessId, $data['page'], $config['per_page']);
                $data['statuscount'] = count($data['email_campaigns']);
                $data['noofcampaigns'] = $config['per_page'];
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->campaign_model->getEmailCampaigns($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/campaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $data['email_campaigns'] = $this->campaign_model->getEmailCampaigns($AppUserCampaigns, $data['page'], $config['per_page']);

                $data['statuscount'] = count($data['email_campaigns']);
                $data['noofcampaigns'] = $config['per_page'];
            }
            $data["businessId"] = $businessId;

            $data['groupId'] = $groupId;

            //Check user have email settings
            if(isset($cookie_group[0])){
                $data['emailSettings'] = $this->campaign_model->getEmailSettings($cookie_group[0]);
            }else{
                $data['emailSettings'] = '';
            }


            //Check User have default Campaign package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);

            if (count($userPackage) > 0) {
                $data['defaultemailCampaign'] = $userPackage->emailCampaign;
            } else {
                $data['defaultemailCampaign'] = 0;    //countTotalCampaign
            }

            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

            if (count($extraPackage) > 0) {
                $data['extraemailCampaign'] = $extraPackage->emailCampaign;
            } else {
                $data['extraemailCampaign'] = 0; //extraCampaignQuantity
            }

            if ($data['extraemailCampaign'] === 'unlimited') {
                $data['totalEmailCampaign'] = 'unlimited';
            } else {
                $data['totalEmailCampaign'] = $data['defaultemailCampaign'] + $data['extraemailCampaign'];
            }

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['usertype'] = $login->usertype;

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }
            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            $data['additional_profit'] = $header['loggedInUser']->additional_profit;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/email_campaign', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function campaignsList($appGroupId = false) {
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
            if (empty($appGroupId)) {
                $push_campaigns = $this->brand_model->getAllPushCampaigns($login->businessId);
            } else {
                $push_campaigns = $this->brand_model->getAllPushCampaignsByAppGroupId($login->businessId, $appGroupId);
            }

            $data['pushCampaigns'] = $push_campaigns;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/campaignsList', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function launchCampaignSuccessPopUp() {
        $this->load->view('3.1/launchCampaignSuccess');
    }

    public function campaignsPerformance($campaignId = false) {
        // $countViewCampaigns = 0;
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'campaignsList';
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

            //$groups = $this->groupapp_model->getUserGroupData($groupArray);
            if (count($header['groups']) > 0) {
                $groupId = $header['groups'][0]->app_group_id;
            } else {
                $groupId = '';
            }

            if (empty($campaignId)) {
                redirect(base_url().'appUser');
            }
            $pushCampaignRow = $this->brand_model->getPushCampaignByCampaignId($campaignId); //echo "<pre>"; print_r($pushCampaignRow); exit;
            if (count($pushCampaignRow) == 0) {
                redirect(base_url().'appUser');
            }
            $app_group_row = $this->brand_model->getAppGroupRow($pushCampaignRow->app_group_id);
            //echo $app_group_row->businessId.' = '. $login->businessId; exit;
            if($app_group_row->businessId != $login->businessId){
              redirect(base_url().'appUser');
            }
            //print_r($app_group_row); exit;
            $data['campaignName'] = $pushCampaignRow->campaignName;
            $data['push_title'] = $pushCampaignRow->push_title;

            $data['group'] = $this->groupapp_model->getOneGroup($groupId); //echo "<pre>"; print_r($pushCampaignRow); exit;
            $data['groupId'] = $groupId;
            $data['campaignId'] = $campaignId;
            $data['platform'] = $pushCampaignRow->platform;
            $data['user'] = $header['loggedInUser'];

            $push_campaigns = $this->brand_model->getAllPushCampaigns($login->businessId, $limit = 10);
            if ($pushCampaignRow->platform == 'email') { //echo "called"; exit;
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
                    //$send_arr = array();
                    foreach ($sendCampaigns as $user) {
                        $sendTime = date('Y-m-d', strtotime($user->emailSentOn)); //echo  $createdDate;
                        if (!in_array($sendTime, $send_time_arr)) {
                            $y = date('Y', strtotime($sendTime));
                            $m = date('m', strtotime($sendTime));
                            $d = date('d', strtotime($sendTime));
                            $m = $m - 1;

                            //array_push($send_arr,$user->id);
                            //array_push($send_time_arr,$user->notification_timezone_send_time);
                            array_push($send_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                        }
                    }
                    $send_users_arr = implode(',', $send_users_arr);
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

                            //array_push($send_arr,$user->id);
                            //array_push($send_time_arr,$user->notification_timezone_send_time);
                            array_push($send_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                        }
                    }
                    $send_users_arr = implode(',', $send_users_arr);
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
               // echo '<pre>'; print_r($view_users_arr); exit;
                if (count($viewCampaigns) > 0) {
                    //$view_arr = array();
                    foreach ($viewCampaigns as $user) {
                        $viewTime = date('Y-m-d', strtotime($user->openTime));
                         if (!in_array($viewTime, $view_time_arr)) {
                            $y = date('Y', strtotime($viewTime));
                            $m = date('m', strtotime($viewTime));
                            $m = $m - 1;
                            $d = date('d', strtotime($viewTime));

                            //array_push($view_arr,$user->totalrecord);
                            //array_push($view_time_arr,$user->notification_timezone_view_time);
                            array_push($view_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                        }
                         }
                    $view_users_arr = implode(',', $view_users_arr);
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
                    //$view_arr = array();
                    foreach ($viewCampaigns as $user) {
                        $viewTime = date('Y-m-d', strtotime($user->notification_timezone_view_time));
                        if (!in_array($viewTime, $view_time_arr)) {
                            $y = date('Y', strtotime($viewTime));
                            $m = date('m', strtotime($viewTime));
                            $m = $m - 1;
                            $d = date('d', strtotime($viewTime));

                            //array_push($view_arr,$user->totalrecord);
                            //array_push($view_time_arr,$user->notification_timezone_view_time);
                            array_push($view_users_arr, "[Date.UTC($y,$m,$d),$user->totalrecord]");
                        }
                    }
                    $view_users_arr = implode(',', $view_users_arr);
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

            $data['push_campaigns'] = $push_campaigns;
            $data['sendCampaigns'] = $send_users_arr;
            $data['viewCampaigns'] = $view_users_arr;
            $data['countSendCampaigns'] = $countSendCampaigns;
            $data['countViewCampaigns'] = $countViewCampaigns;
            $data['highChartScript'] = TRUE;
            $data['highChartScriptAjax'] = TRUE;
            $data['currentCampaign'] = $campaignId;
            //  echo '<pre>'; print_r($data); exit;
            //$header['groups']

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/campaignsPerformance', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function campaignsDelete($campaignId = false) {
        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;
        $businessId = $login->businessId;
        if (isset($_POST['campaignId'])) {
            $campaignId = $_POST['campaignId'];
            $campaignRow = $this->brand_model->getPushCampaignByCampaignId($campaignId);
            if (count($campaignRow) > 0) {
                $app_group_row = $this->brand_model->getAppGroupRow($campaignRow->app_group_id);
                $update = array('id' => $campaignRow->id, 'isDelete' => 1);
                $this->brand_model->DeleteCampaigns($update);

                $notificationSendRow = $this->brand_model->DeleteNotificationSend($campaignRow->id);

                $notificationHistoryRows = $this->brand_model->DeleteNotificationSendHistory($campaignRow->id);

                $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);
                if ($campaignRow->platform == 'android') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                            if ($extraPackage->androidCampaign != 'unlimited') {
                                $update['androidCampaign'] = $extraPackage->androidCampaign + 1;
                            } else {
                                $update['androidCampaign'] = $extraPackage->androidCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $androidCampaign = $userPackage->androidCampaign;
                            $updateAndroidCampaign = $androidCampaign + 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'androidCampaign' => $updateAndroidCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($campaignRow->platform == 'iOS') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                            if ($extraPackage->iOSCampaign != 'unlimited') {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign + 1;
                            } else {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $iOSCampaign = $userPackage->iOSCampaign;
                            $updateiOSCampaign = $iOSCampaign + 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'iOSCampaign' => $updateiOSCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($campaignRow->platform == 'email') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                            if ($extraPackage->emailCampaign != 'unlimited') {
                                $update['emailCampaign'] = $extraPackage->emailCampaign + 1;
                            } else {
                                $update['emailCampaign'] = $extraPackage->emailCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $emailCampaign = $userPackage->emailCampaign;
                            $updateEmailCampaign = $emailCampaign + 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'emailCampaign' => $updateEmailCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                echo 1;
            } else {
                echo 'false';  exit;
            }
        } else {
            $data['campaignId'] = $campaignId;
            $this->load->view('3.1/delete_campaigns', $data);
        }
    }

    public function campaignsClone($campaignId = false) {
        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;
        $businessId = $login->businessId;
        if (isset($_POST['campaignId'])) {
            $campaignId = $_POST['campaignId'];
            $campaignRow = $this->brand_model->getPushCampaignRowId($campaignId);
            if ($campaignRow > 0) {
                $lastRow = $this->brand_model->getPushCampaignByCampaignId($campaignRow);
                if (count($lastRow) > 0) {
                    $update = array('id' => $lastRow->id, 'isDraft' => 1, 'isActive' => 0, 'createdDate' => date('Y-m-d H:i:s'));
                    $this->brand_model->updateCampaigns($update);

                    $campaignRow = $this->brand_model->getPushCampaignByCampaignId($campaignId);
                    $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);
                    if ($campaignRow->platform == 'android') {
                        if ($additional_profit != 1) {
                            if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                                if ($extraPackage->androidCampaign != 'unlimited') {
                                    $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                                } else {
                                    $update['androidCampaign'] = $extraPackage->androidCampaign;
                                }

                                $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                                $this->campaign_model->updateBrandUserExtraPackage($update);
                            } else {
                                //Update total campaigns
                                $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                                $androidCampaign = $userPackage->androidCampaign;
                                $updateAndroidCampaign = $androidCampaign - 1;

                                $update = array(
                                    'user_pro_id' => $userPackage->user_pro_id,
                                    'androidCampaign' => $updateAndroidCampaign
                                );
                                $this->campaign_model->updateBrandUserTotalCampaigns($update);
                            }
                        }
                    }

                    if ($campaignRow->platform == 'iOS') {
                        if ($additional_profit != 1) {
                            if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                                if ($extraPackage->iOSCampaign != 'unlimited') {
                                    $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                                } else {
                                    $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                                }

                                $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                                $this->campaign_model->updateBrandUserExtraPackage($update);
                            } else {
                                //Update total campaigns
                                $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                                $iOSCampaign = $userPackage->iOSCampaign;
                                $updateiOSCampaign = $iOSCampaign - 1;

                                $update = array(
                                    'user_pro_id' => $userPackage->user_pro_id,
                                    'iOSCampaign' => $updateiOSCampaign
                                );
                                $this->campaign_model->updateBrandUserTotalCampaigns($update);
                            }
                        }
                    }

                    if ($campaignRow->platform == 'email') {
                        if ($additional_profit != 1) {
                            if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                                if ($extraPackage->emailCampaign != 'unlimited') {
                                    $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                                } else {
                                    $update['emailCampaign'] = $extraPackage->emailCampaign;
                                }

                                $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                                $this->campaign_model->updateBrandUserExtraPackage($update);
                            } else {
                                //Update total campaigns
                                $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                                $emailCampaign = $userPackage->emailCampaign;
                                $updateEmailCampaign = $emailCampaign - 1;

                                $update = array(
                                    'user_pro_id' => $userPackage->user_pro_id,
                                    'emailCampaign' => $updateEmailCampaign
                                );
                                $this->campaign_model->updateBrandUserTotalCampaigns($update);
                            }
                        }
                    }

                    echo $lastRow->id; //exit;
                } else {
                    echo 'false'; exit;
                }
            } else {
                echo 'false'; exit;
            }
        } else {
            $data['campaignId'] = $campaignId;
            $campaignRow = $this->brand_model->getPushCampaignByCampaignId($campaignId);
            //Check User have default Campaign package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

            if ($campaignRow->platform == 'android') {
              if (count($userPackage) > 0 || count($extraPackage) > 0) {
                  $data['countTotalCampaign'] = $userPackage->androidCampaign;
              } else {
                  $data['countTotalCampaign'] = 0;
              }
            }

            if ($campaignRow->platform == 'iOS') {
              if (count($userPackage) > 0 || count($extraPackage) > 0) {
                  $data['countTotalCampaign'] = $userPackage->iOSCampaign;
              } else {
                  $data['countTotalCampaign'] = 0;
              }
            }

            if ($campaignRow->platform == 'email') {
              if (count($userPackage) > 0 || count($extraPackage) > 0) {
                  $data['countTotalCampaign'] = $userPackage->emailCampaign;
              } else {
                  $data['countTotalCampaign'] = 0;
              }
            }
            $this->load->view('3.1/clone_campaigns', $data);
        }
    }

    public function cloneSuccess($campaignId = false) {
        $campaignRow = $this->brand_model->getPushCampaignByCampaignId($campaignId);
        //print_r($campaignRow); exit;
        if (count($campaignRow) > 0) {
            if ($campaignRow->platform == 'android' || $campaignRow->platform == 'iOS') {
                redirect('appUser/editCampaigns/' . $campaignId);
            } else {
                redirect('appUser/editEmailCampaign/' . $campaignId);
            }
        } else {
            redirect('appUser/insightsPage');
        }
    }

    function emailPreview() {
        $this->load->view('3.1/email_preview');
    }

    function deleteCampaignPopUp($campaignId) {
        $data['campaignId'] = $campaignId;
        $campaign = $this->brand_model->getPushCampaignByCampaignId($campaignId);
        $data['type'] = $campaign->platform;
        $this->load->view('3.1/delete_campaign', $data);
    }

    function deleteCampaign() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;
        $campaignId = $_POST['campaignId'];

        $param = $this->campaign_model->getCampaign($campaignId);

        $update['id'] = $_POST['campaignId'];
        $update['isDelete'] = 1;
        $this->campaign_model->updatePushNotificationCampaign($update);

        $login = $this->administrator_model->front_login_session();
        $businessId = $login->businessId;

        $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);
        if($param->platform == 'android') {
            if ($additional_profit != 1) {
                if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                    if ($extraPackage->androidCampaign != 'unlimited') {
                        $update1['androidCampaign'] = $extraPackage->androidCampaign + 1;
                    } else {
                        $update1['androidCampaign'] = $extraPackage->androidCampaign;
                    }

                    $update1['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                    $this->campaign_model->updateBrandUserExtraPackage($update1);
                } else {
                    //Update total campaigns
                    $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                    $androidCampaign = $userPackage->androidCampaign;
                    $updateAndroidCampaign = $androidCampaign + 1;

                    $update1 = array(
                        'user_pro_id' => $userPackage->user_pro_id,
                        'androidCampaign' => $updateAndroidCampaign
                    );
                    $this->campaign_model->updateBrandUserTotalCampaigns($update1);
                }
            }
        }

        if ($param->platform == 'iOS') {
            if ($additional_profit != 1) {
                if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                    if ($extraPackage->iOSCampaign != 'unlimited') {
                        $update1['iOSCampaign'] = $extraPackage->iOSCampaign + 1;
                    } else {
                        $update1['iOSCampaign'] = $extraPackage->iOSCampaign;
                    }

                    $update1['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                    $this->campaign_model->updateBrandUserExtraPackage($update1);
                } else {
                    //Update total campaigns
                    $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                    $iOSCampaign = $userPackage->iOSCampaign;
                    $updateiOSCampaign = $iOSCampaign + 1;

                    $update1 = array(
                        'user_pro_id' => $userPackage->user_pro_id,
                        'iOSCampaign' => $updateiOSCampaign
                    );
                    $this->campaign_model->updateBrandUserTotalCampaigns($update1);
                }
            }
        }

        if ($param->platform == 'email') {
            if ($additional_profit != 1) {
                if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                    if ($extraPackage->emailCampaign != 'unlimited') {
                        $update1['emailCampaign'] = $extraPackage->emailCampaign + 1;
                    } else {
                        $update1['emailCampaign'] = $extraPackage->emailCampaign;
                    }

                    $update1['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                    $this->campaign_model->updateBrandUserExtraPackage($update1);
                } else {
                    //Update total campaigns
                    $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                    $emailCampaign = $userPackage->emailCampaign;
                    $updateEmailCampaign = $emailCampaign + 1;

                    $update1 = array(
                        'user_pro_id' => $userPackage->user_pro_id,
                        'emailCampaign' => $updateEmailCampaign
                    );
                    $this->campaign_model->updateBrandUserTotalCampaigns($update1);
                }
            }
        }

        echo 1;
    }

    function supportedAttributes() {
        $this->load->view('3.1/supported_attributes');
    }

    function androidSupportedAttributes() {
        $this->load->view('3.1/android_supported_attributes');
    }

    function iosSupportedAttributes() {
        $this->load->view('3.1/ios_supported_attributes');
    }

    public function postcampaign() {
        $campaignId = $_POST['campaignId'];
        $this->load->model('social_model');
        $campaignData = $this->brand_model->getPushCampaignByCampaignId($campaignId);
        $login = $this->administrator_model->front_login_session();
        if (!empty($campaignData)) {
            $app_group_id = $campaignData->app_group_id;
            $platformIds = '';
            $platformApps = array();
            if ($campaignData->platform == 'android') {
                $platformIds = $this->groupapp_model->getAndroidApps($app_group_id);
                foreach ($platformIds as $platformId) {
                    array_push($platformApps, $platformId->app_group_apps_id);
                }
            } else if ($campaignData->platform == 'iOS') {
                $platformIds = $this->groupapp_model->getIOSApps($app_group_id);
                foreach ($platformIds as $platformId) {
                    array_push($platformApps, $platformId->app_group_apps_id);
                }
            }
            $platformIds = implode(',', $platformApps);
            $platformIds = rtrim($platformIds, ',');
            $appGroupAppRow = $this->brand_model->getAppsGroupAppRowByAppId($platformIds);
            if (!empty($appGroupAppRow->app_download_url)) {
                $app_download_url = $appGroupAppRow->app_download_url;
            } else {
                if ($campaignData->platform == 'android') {
                    $app_download_url = "https://play.google.com/store/apps";
                } else if ($campaignData->platform == 'iOS') {
                    $app_download_url = "https://itunes.apple.com/en/genre/ios/id36?mt=8";
                }
            }

            $campaignOwner = $login->user_id;
            $campaignImage = ''; //host_ip_url . 'upload/status_image/full/' . $campaignData->expanded_img_url;

            $campaignText = $campaignData->push_message;
            $campaignUrl = $app_download_url;
            //campaign user data
            $campaignUserData = $this->user_model->getOneUser($login->user_id);
            $campaignOwnerName = $login->firstname . ' ' . $login->lastname;
            //socail table data of campain user
            $socialData = $this->social_model->getAllSocialAccountsOfCampaingUser($campaignOwner);

            foreach ($socialData as $socialAccount) {
                switch ($socialAccount->source) {
                    case "twitter":
                        require_once APPPATH . "/third_party/twitter/twitter-autopost.php";
                        $Twitauth = new Twitauth(Tw_CONSUMER_KEY, Tw_CONSUMER_SECRET);
                        $arr = array('oauth_token' => $socialAccount->access_token, 'oauth_token_secret' => $socialAccount->oauth_token_secret, 'oauth_verifier' => $socialAccount->refresh_token, 'text' => $campaignText, 'url' => $campaignUrl, 'image' => $campaignImage);
                        $result = $Twitauth->postStatus($arr);
                        //print_r($result);
                        break;

                    case "google":
                        try {
                            $accesstoken = '';
                            $client_id = google_sconnect_client_id;
                            $client_secret = google_sconnect_client_secret;
                            $redirect_uri = base_url() . '/social/auth';
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
                            if (isset($response->access_token)) {
                                $accesstoken = $response->access_token;
                            }
                            //code to post a new post on google plus api
                            $curl1 = curl_init();
                            $campaignImage = $campaignData->expanded_img_url;
                            if (!empty($campaignData->notification_image)) {
                                $data = '{"object":{"originalContent":"' . $campaignText . '   ' . $campaignUrl . '","attachments": [{"url": "' . $campaignImage . '","objectType": "article"}]},"access":{"kind":"plus#acl","items":[{"type" : "domain"}],"domainRestricted":true},"key":"' . $client_id . '"}';
                            } else {
                                $data = '{"object":{"originalContent":"' . $campaignText . '   ' . $campaignUrl . '"},"access":{"kind":"plus#acl","items":[{"type" : "domain"}],"domainRestricted":true},"key":"' . $client_id . '"}';
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
        } else {
            echo 'false';
        }
    }

    public function fbPostOffer($id = false) {
        $data = array();
        if (!empty($id)) {
            $data['id'] = $id;
        }
        $campaignData = $this->brand_model->getPushCampaignByCampaignId($id);
        $login = $this->administrator_model->front_login_session();
        $app_group_id = $campaignData->app_group_id;
        $platformIds = '';
        $platformApps = array();
        if ($campaignData->platform == 'android') {
            $platformIds = $this->groupapp_model->getAndroidApps($app_group_id);
            foreach ($platformIds as $platformId) {
                array_push($platformApps, $platformId->app_group_apps_id);
            }
        } else if ($campaignData->platform == 'iOS') {
            $platformIds = $this->groupapp_model->getIOSApps($app_group_id);
            foreach ($platformIds as $platformId) {
                array_push($platformApps, $platformId->app_group_apps_id);
            }
        }
        $platformIds = implode(',', $platformApps);
        $platformIds = rtrim($platformIds, ',');
        $appGroupAppRow = $this->brand_model->getAppsGroupAppRowByAppId($platformIds);
        if (!empty($appGroupAppRow->app_download_url)) {
            $app_download_url = $appGroupAppRow->app_download_url;
        } else {
            if ($campaignData->platform == 'android') {
                $app_download_url = "https://play.google.com/store/apps";
            } else if ($campaignData->platform == 'iOS') {
                $app_download_url = "https://itunes.apple.com/en/genre/ios/id36?mt=8";
            }
        }
        $image = ''; // stagehost_ip_url.'upload/amazing_offer1.png';
        //print_r($campaignData); exit;
        $appId = "1773145859677039"; //$this->config->item('appId');
        $caption = $login->username . ' has created an campaign!';
        $fbpostName = $login->username;
        $fbpostUrl = $app_download_url;
        $message = $campaignData->push_message . '  ' . $app_download_url;
        $fbPostdescription = $campaignData->push_message;
        $fbRedirectUrl = "";
        $data['postUrl'] = "https://www.facebook.com/dialog/share?app_id=$appId&amp;display=popup&amp;caption=$caption&amp;name=$fbpostName&amp;link=$fbpostUrl&amp;description=$fbPostdescription&amp;picture=$image&amp;href=$fbpostUrl&amp;message=$message&amp;redirect_uri=$fbRedirectUrl";
        $this->load->view('fbPostOffer', $data);
    }

    function uploadEditorImage() {

        // Allowed extentions.
        $allowedExts = array("gif", "jpeg", "jpg", "png", "blob");

        // Get filename.
        $temp = explode(".", $_FILES["file"]["name"]);

        // Get extension.
        $extension = end($temp);

        // An image check is being done in the editor but it is best to
        // check that again on the server side.
        // Do not use $_FILES["file"]["type"] as it can be easily forged.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["file"]["tmp_name"]);

        if ((($mime == "image/gif") || ($mime == "image/jpeg") || ($mime == "image/pjpeg") || ($mime == "image/x-png") || ($mime == "image/png")) && in_array(strtolower($extension), $allowedExts)) {
            // Generate new random name.
            $name = sha1(microtime()) . "." . $extension;

            // Save file in the uploads folder.
            move_uploaded_file($_FILES["file"]["tmp_name"], getcwd() . "/upload/editor/" . $name);

            // Generate response.
            $response = new StdClass;
            $response->link = base_url() . "upload/editor/" . $name;
            echo stripslashes(json_encode($response));
        }
    }

    function loadEditorImages() {
        $response = array();

        // Image types.
        $image_types = array(
            "image/gif",
            "image/jpeg",
            "image/pjpeg",
            "image/jpeg",
            "image/pjpeg",
            "image/png",
            "image/x-png"
        );

        // Filenames in the uploads folder.
        $fnames = scandir("upload/editor");

        // Check if folder exists.
        if ($fnames) {
            // Go through all the filenames in the folder.
            foreach ($fnames as $name) {
                // Filename must not be a folder.
                if (!is_dir($name)) {
                    // Check if file is an image.
                    if (in_array(mime_content_type(getcwd() . "/upload/editor/" . $name), $image_types)) {
                        // Build the image.
                        $img = new StdClass;
                        $img->url = base_url() . "upload/editor/" . $name;
                        $img->thumb = base_url() . "upload/editor/" . $name;
                        $img->name = $name;

                        // Add to the array of image.
                        array_push($response, $img);
                    }
                }
            }
        }

        // Folder does not exist, respond with a JSON to throw error.
        else {
            $response = new StdClass;
            $response->error = "Images folder does not exist!";
        }

        $response = json_encode($response);

        // Send response.
        echo stripslashes($response);
    }

    function deleteEditorImage() {
        // Get src.
        $src = $_POST["src"];

        // Check if file exists.
        if (file_exists(getcwd() . $src)) {
            // Delete file.
            unlink(getcwd() . $src);
        }
    }

    public function sendTestEmail() {

        $json = file_get_contents('php://input');
        $params = json_decode($json);
        foreach ($params as $param) {
            $receiverEmail = $param->receiverEmail;
            $displayName = $param->displayName;
            $fromAddress = $param->fromAddress;
            $subject = $param->subject;
            $editor = $param->editor;
            $campaignName = $param->campaignName;
            $groupId = $param->groupId;

            $messages = $editor;
            $emailIds = array();
            $userEmailIds = array();

            $unsubscribeUsers = $this->brand_model->getUnsubscribeEmails();
            if (count($unsubscribeUsers) > 0) {
                foreach ($unsubscribeUsers as $user) {
                    $userEmailIds[] = $user->from_email;
                }
                $emailIds = $userEmailIds;
                //echo $receiverEmail;
                //print_r($userEmailIds); exit;
                if (in_array($receiverEmail, $emailIds)) {
                    $success = 'error';
                    $statusMessage = 'Unsubscribe Email, Please fill another email.';

                    $response = array(
                        "data" => array(
                            "status" => $success,
                            "statusMessage" => $statusMessage,
                            "errortype" => "unsubscribe"
                        )
                    );

                    echo json_encode($response);
                    exit();
                }
            }

            $userRow = $this->brand_model->getUserByEmailId($receiverEmail);
            if (count($userRow) > 0) {
                $date_of_birth = $userRow->date_of_birth;
                $email_address = $userRow->email;
                $first_name = $userRow->firstname;
                $last_name = $userRow->lastname;
                $gender = $userRow->gender;
                $last_used_app_date = '';
                $most_recent_app_version = '';
                $phone_number = $userRow->contactNumber;
                $time_zone = '';
                $username = $userRow->username;
                $businessId = $userRow->businessId;
                $company = '';
                $notice = '';
            } else {
                $userRow = $this->brand_model->getExternalUserByEmailAndGroupId($receiverEmail,$groupId);

                if (count($userRow) > 0) {
                    $date_of_birth = $userRow->date_of_birth;
                    $email_address = $userRow->email;
                    $first_name = $userRow->firstName;
                    $last_name = $userRow->lastName;
                    $gender = $userRow->gender;
                    $phone_number = $userRow->phoneNumber;
                    $time_zone = $userRow->timezone;
                    $company = $userRow->company;
                    $notice = '';
                    //Get businessId for external user
                    $appGroup = $this->groupapp_model->getOneGroup($groupId);
                    $businessId = $appGroup->businessId;

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
                    $date_of_birth = '1985-02-02';
                    $email_address = 'johnmike@mallinator.com';
                    $first_name = 'John';
                    $last_name = 'Mike';
                    $gender = 'male';
                    $last_used_app_date = '2017-02-13 04:54:17';
                    $most_recent_app_version = '1.0';
                    $phone_number = '1234567890';
                    $time_zone = 'GMT +0530';
                    $username = 'Username';
                    $businessId = 1;
                    $company = 'Demo Company';
                    $notice = 'Above user details are dummy details.';
                }
            }
            $set_user_to_unsubscribed_url = 'Unsubscribed link: '.base_url() . "hurreeEmail/unsubscribe/" . base64_encode($email_address)."/".$businessId."/".$groupId;
            $set_user_to_subscribed_url = base_url() . "hurreeEmail/subscribe/" . base64_encode($email_address);

            $subject = str_ireplace('{{${date_of_birth}}}', $date_of_birth, $subject);
            $subject = str_replace('{{${email_address}}}', $email_address, $subject);
            $subject = str_ireplace('{{${first_name}}}', $first_name, $subject);
            $subject = str_ireplace('{{${last_name}}}', $last_name, $subject);
            $subject = str_ireplace('{{${gender}}}', $gender, $subject);
            $subject = str_ireplace('{{${last_used_app_date}}}', $last_used_app_date, $subject);
            $subject = str_ireplace('{{${most_recent_app_version}}}', $most_recent_app_version, $subject);
            $subject = str_ireplace('{{${phone_number}}}', $phone_number, $subject);
            $subject = str_ireplace('{{${time_zone}}}', $time_zone, $subject);
            $subject = str_ireplace('{{${username}}}', $username, $subject);
            $subject = str_ireplace('{{campaign.${name}}}', $campaignName, $subject);
            $subject = str_ireplace('{{${company}}}', $company, $subject);
            $subject = str_ireplace('{{${set_user_to_unsubscribed_url}}}', $set_user_to_unsubscribed_url, $subject);
            $subject = str_ireplace('{{${set_user_to_subscribed_url}}}', $set_user_to_subscribed_url, $subject);

            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;date_of_birth&rbrace;&rbrace;&rbrace;', $date_of_birth, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;email_address&rbrace;&rbrace;&rbrace;', $email_address, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;first_name&rbrace;&rbrace;&rbrace;', $first_name, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;last_name&rbrace;&rbrace;&rbrace;', $last_name, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;gender&rbrace;&rbrace;&rbrace;', $gender, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;last_used_app_date&rbrace;&rbrace;&rbrace;', $last_used_app_date, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;most_recent_app_version&rbrace;&rbrace;&rbrace;', $most_recent_app_version, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;phone_number&rbrace;&rbrace;&rbrace;', $phone_number, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;time_zone&rbrace;&rbrace;&rbrace;', $time_zone, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;username&rbrace;&rbrace;&rbrace;', $username, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;campaign.&dollar;&lbrace;name&rbrace;&rbrace;&rbrace;', $campaignName, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;company&rbrace;&rbrace;&rbrace;', $company, $messages);
            $messages = str_ireplace('&lbrace;&lbrace;&dollar;&lbrace;set_user_to_unsubscribed_url&rbrace;&rbrace;&rbrace;', $set_user_to_unsubscribed_url, $messages);

            if($notice != ''){
                $messages .= '<br><br>'.$notice;
            }

            //// SEND  EMAIL START
            //$this->emailConfig();   //Get configuration of email
            //// GET EMAIL FROM DATABASE

            if (empty($subject)) {
                $subject = "[ Hurree Test ] Email Subject";
            } else {
                $subject = "[ Hurree Test ] " . $subject;
            }

            if (empty($fromAddress)) {
                $fromAddress = "hello@marketmyapp.co";
            }

            $httpClient = new \Http\Adapter\Guzzle6\Client(new Client());
                $sparky = new SparkPost($httpClient, ['key' => SPARKPOSTKEYSUB]);
                $promise = $sparky->transmissions->post([
                     'content' => [
                        'from' => [
                            'name' => 'No Reply',
                            'email' => $fromAddress,
                        ],
                        'subject' => $subject,
                        'html' => $messages,
                        'text' => '',
                    ],
                    'recipients' => [
                        [
                            'address' => [
                                'name' => '',
                                'email' => $receiverEmail,
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

              //echo $response->getStatusCode();

//            $this->email->from($fromAddress, 'No Reply');
//            //// FROM EMAIL
//            $this->email->to($receiverEmail);
//            $this->email->subject($subject);
//            $this->email->message($messages);
            if ($response->getStatusCode() == 200) {   ////  EMAIL SEND
                //$emailResponse = $this->email->print_email_debugger();
                // print_r( $response);  exit;
                $success = 'success';
                $statusMessage = 'Email send successfully.';

                $response = array(
                    "data" => array(
                        "status" => $success,
                        "statusMessage" => $statusMessage,
                    )
                );
            } else {
                $success = 'error';
                $statusMessage = "Server Busy. Please try again.";

                $response = array(
                    "data" => array(
                        "status" => $success,
                        "statusMessage" => $statusMessage
                    )
                );
            }

            echo json_encode($response);
            exit();
        }
    }

    function billinginfo() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'billinginfo';
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

            //Monthly package

            $package = $this->brand_model->getpackage();
            $data['packagePrice'] = $package->price_usd;

            //Get active groups
            $activeGroups = $this->campaign_model->getActiveAppGroups($login->businessId);

            $app_group_id = '';
            foreach ($activeGroups as $group) {
                $app_group_id[] = $group->app_group_id;
            }

            //Get Contacts count
            $data['contacts'] = $this->brand_model->getExternalUsersCount($app_group_id);

            //Get how many Android and iOS Campaigns are left
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($login->businessId);

            if (count($userPackage) > 0) {

                    $data['defaultAppGroups'] = $userPackage->totalAppGroup;

                if ($userPackage->androidCampaign == 0 || $userPackage->androidCampaign < 0) {
                    $data['defaultAndroidCampaign'] = 0;
                } else {
                    $data['defaultAndroidCampaign'] = $userPackage->androidCampaign;
                }
                if ($userPackage->iOSCampaign == 0 || $userPackage->iOSCampaign < 0) {
                    $data['defaultIosCampaign'] = 0;
                } else {
                    $data['defaultIosCampaign'] = $userPackage->iOSCampaign;
                }
                if ($userPackage->emailCampaign == 0 || $userPackage->emailCampaign < 0) {
                    $data['defaultEmailCampaign'] = 0;
                } else {
                    $data['defaultEmailCampaign'] = $userPackage->emailCampaign;
                }
            } else {

                $data['defaultAppGroups'] = 0;
                $data['defaultAndroidCampaign'] = 0;    //countTotalCampaign
                $data['defaultIosCampaign'] = 0;
                $data['defaultEmailCampaign'] = 0;
            }

            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraPackage($login->businessId);

            if (count($extraPackage) > 0) {
                if ($extraPackage->androidCampaign == '0' || $extraPackage->androidCampaign < '0') {
                    $data['extraAndroidCampaign'] = 0;
                } else {
                    $data['extraAndroidCampaign'] = $extraPackage->androidCampaign;
                }
                if ($extraPackage->iOSCampaign == '0' || $extraPackage->iOSCampaign < '0') {
                    $data['extraIosCampaign'] = 0;
                } else {
                    $data['extraIosCampaign'] = $extraPackage->iOSCampaign;
                }

                if ($extraPackage->emailCampaign == '0' || $extraPackage->emailCampaign < '0') {
                    $data['extraEmailCampaign'] = 0;
                } else {
                    $data['extraEmailCampaign'] = $extraPackage->emailCampaign;
                }
            } else {
                $data['extraAndroidCampaign'] = 0;
                $data['extraIosCampaign'] = 0;   //extraCampaignQuantity
                $data['extraEmailCampaign'] = 0;
            }

            if ($data['extraAndroidCampaign'] === 'unlimited') {
                $data['totalAndroidCampaign'] = 'Unlimited';
            } else {
                $data['totalAndroidCampaign'] = $data['defaultAndroidCampaign'] + $data['extraAndroidCampaign'];
            }


            if ($data['extraIosCampaign'] === 'unlimited') {
                $data['totaliOSCampaign'] = 'Unlimited';
            } else {
                $data['totaliOSCampaign'] = $data['defaultIosCampaign'] + $data['extraIosCampaign'];
            }

            if ($data['extraEmailCampaign'] === 'unlimited') {
                $data['totalEmailCampaign'] = 'Unlimited';
            } else {
                $data['totalEmailCampaign'] = $data['defaultEmailCampaign'] + $data['extraEmailCampaign'];
            }

            $data['additional_profit'] = $header['loggedInUser']->additional_profit;
            $data['billingInfo'] = $this->brand_model->getBillingInfo($login->businessId);
            if (!empty($data['billingInfo'])) {
                $countryData = $this->country_model->getcountry("country_code", $data['billingInfo']->Country);
                if(count($countryData) > 0){
                    $data['billingInfo']->Country = $countryData[0]->country_name;
                }else{
                    $data['billingInfo']->Country = '';
                }

            }

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/billinginfo', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function calendar() {

        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'calendar';
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

            $where['users.businessId'] = $login->businessId;
            $where['users.createdBy'] = $login->user_id;
            $where['users.isDelete'] = 0;
            //$where['business_branch.active'] = 1;
            $data['users'] = $this->user_model->getUsers($where);
            //echo '<pre>'; print_r($data['users']); die;

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            //$data['userDetails'] = $this->user_model->getOneUser($login->user_id);
            //$data['masterData'] = $this->user_model->getMasterUserData($login->businessId);
            //$data['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $data['businessName'] = $login->businessName;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/calender', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function sdkIntegratePopup() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $data['businessId'] = $login->businessId;
            $data['email'] = $login->email;

            $this->load->view('3.1/hurree_sdk', $data);
        } else {
            redirect(base_url());
        }
    }

    public function sdkIntegrate() {
        $businessId = $this->input->post('businessId');
        $show_message = $this->input->post('message');
        $userRow = $this->brand_model->getAppUserSettings($businessId);
        if (count($userRow) > 0) {
            $appSettingArr = array(
                'id' => $userRow->id,
                'businessId' => $userRow->businessId,
                'is_sdk_integrate' => 1,
                "is_dont_show_subscribe" => $show_message,
                'modifiedDate' => date('Y-m-d H:i:s')
            );
            $last_id = $this->brand_model->addSdkIntegrationInfo($appSettingArr);
        } else {
            $appSettingArr = array(
                'businessId' => $businessId,
                'is_sdk_integrate' => 1,
                "is_dont_show_subscribe" => $show_message,
                'createdDate' => date('Y-m-d H:i:s')
            );
            $last_id = $this->brand_model->addSdkIntegrationInfo($appSettingArr);
        }
        $this->session->unset_userdata('sdkAlertPopup', 0);
        echo 1;
        exit();
    }

    public function sdkIntegrateNeedHelp() {
        $businessId = $this->input->post('businessId');
        $show_message = $this->input->post('show_message');
        $email = $this->input->post('email');
        $userRow = $this->brand_model->getAppUserSettings($businessId);
        if (count($userRow) > 0) {
            $appSettingArr = array(
                'id' => $userRow->id,
                'businessId' => $userRow->businessId,
                'is_sdk_integrate' => $userRow->is_sdk_integrate,
                "is_dont_show_subscribe" => $show_message,
                'modifiedDate' => date('Y-m-d H:i:s')
            );
            $last_id = $this->brand_model->addSdkIntegrationInfo($appSettingArr);
        } else {
            $appSettingArr = array(
                'businessId' => $businessId,
                'is_sdk_integrate' => 1,
                "is_dont_show_subscribe" => $show_message,
                'createdDate' => date('Y-m-d H:i:s')
            );
            $last_id = $this->brand_model->addSdkIntegrationInfo($appSettingArr);
        }
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('hurree_sdk_installation');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;

        //$hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
        $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
        $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
        $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

        if (empty($email)) {
            $email = "hello@marketmyapp.co";
        }
        //// replace strings from message
        $messages = str_replace('{email}', ucfirst($email), $messages);

        //// FROM EMAIL
        $this->email->from($email, 'Hurree');
        $this->email->to("support@hurree.co"); //
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        if ($this->email->send()) {   ////  EMAIL SEND
            echo 1;
            exit();
        } else {
            echo 'failed';
            exit();
        }
    }

    public function sdkIntegrateNotshow() {
        $this->session->unset_userdata('sdkAlertPopup', 0);
        echo 1;
        exit();
    }

    function crossChannel($groupId = false) {

        $login = $this->administrator_model->front_login_session();

        $this->session->unset_userdata("crossChannelPagination");

        if ($login->active != 0) {

            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            $groupData = $this->groupapp_model->getOneGroup($cookie_group[0]);
            $data['groupData'] = $groupData;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);

                $businessGroups = $this->groupapp_model->getGroups($login->businessId);
                if (count($businessGroups) > 0) {
                    $app_g = $app_g2 = $app_g3 = array();
                    $data['groupApps'] = $businessGroups;
//                    foreach ($businessGroups as $businessGroup) {
//                        $data['groupApps'] = $this->groupapp_model->getGroupsWithAndroid($login->businessId);
//                        //echo '<pre>'; print_r($data['groupApps']); //exit;
//                        if (count($data['groupApps']) > 0) {
//                            foreach ($data['groupApps'] as $groups) {
//                                if (!in_array($groups->app_group_id, $app_g)) {
//                                    $app_name = $groups->app_group_name;
//                                    if (!empty($groups->app_image)) {
//                                        $image = base_url() . "upload/apps/" . $groups->app_image;
//                                    } else {
//                                        $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                                    }
//                                    array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                    if (!empty($groups->app_image)) {
//                                        $app_name = $groups->app_group_name;
//                                        if (!empty($groups->app_image)) {
//                                            $image = base_url() . "upload/apps/" . $groups->app_image;
//                                        } else {
//                                            $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                        }
//                                        array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                        //echo $app_name; exit;
//                                        //  $app_g = $app_g;
//                                    }
//                                }
//                            }
//                            $app_g3 = $app_g + $app_g2;
//                            $app_g4 = $app_g5 = array();
//                            foreach ($app_g3 as $groups) {
//                                if (!in_array($groups['app_group_id'], $app_g5)) {
//                                    $app_name = $groups['app_group_name'];
//                                    $image = $groups['image'];
//                                    //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                                    array_push($app_g5, $groups['app_group_id']);
//                                    array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                                }
//                            }
//                            $data['groupApps'] = $app_g4;
//                            //echo '<pre>'; print_r($data['groupApps']); exit;
//                        }
//                        //echo '<pre>';
//                        //print_r($data['groupApps']); die;
//                    }
                }else{
                    $data['groupApps'] = '';
                }
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
                $groupApps = $this->groupapp_model->getUserGroupData($groupArray);
                //$data['groupApps'] = $this->groupapp_model->getAppUserGroupsWithImages($groupArray);
                $app_g = $app_g2 = $app_g3 = array();
                if (count($groupApps) > 0) {
//                    foreach ($data['groupApps'] as $groups) {
//                        if (!in_array($groups->app_group_id, $app_g)) {
//                            $app_name = $groups->app_group_name;
//                            if (!empty($groups->app_image)) {
//                                $image = base_url() . "upload/apps/" . $groups->app_image;
//                            } else {
//                                $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                            }
//                            array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                            if (!empty($groups->app_image)) {
//                                $app_name = $groups->app_group_name;
//                                if (!empty($groups->app_image)) {
//                                    $image = base_url() . "upload/apps/" . $groups->app_image;
//                                } else {
//                                    $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                }
//                                array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                //echo $app_name; exit;
//                                //  $app_g = $app_g;
//                            }
//                        }
//                    }
//                    $app_g3 = $app_g + $app_g2;
//                    $app_g4 = $app_g5 = array();
//                    foreach ($app_g3 as $groups) {
//                        if (!in_array($groups['app_group_id'], $app_g5)) {
//                            $app_name = $groups['app_group_name'];
//                            $image = $groups['image'];
//                            //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                            array_push($app_g5, $groups['app_group_id']);
//                            array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                        }
//                    }
                    $data['groupApps'] = $groupApps;
                    //echo '<pre>'; print_r($data['groupApps']); exit;
                }else{
                   $data['groupApps'] = $groupApps;
                }
            }

            //Check user have email settings
            if(isset($cookie_group[0])){
                $data['emailSettings'] = $this->campaign_model->getEmailSettings($cookie_group[0]);
            }else{
                $data['emailSettings'] = '';
            }

            //Check User have default Cross Channel package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);

            if (count($userPackage) > 0) {
                $data['defaultcrossChannel'] = $userPackage->crossChannel;
            } else {
                $data['defaultcrossChannel'] = 0;
            }

            //Check User have extra Cross Channel package
            $extraPackage = $this->crosschannel_model->getBrandUserExtraPackage($businessId);

            if (count($extraPackage) > 0) {
                $data['extracrossChannel'] = $extraPackage->crossChannel;
            } else {
                $data['extracrossChannel'] = 0;
            }

            $data['totalCrossChannel'] = $data['defaultcrossChannel'] + $data['extracrossChannel'];


            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->crosschannel_model->getCrossChannelByBusinessId($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/crossChannel/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;


                $data['push_campaigns'] = $this->crosschannel_model->getCrossChannelByBusinessId($login->businessId, $data['page'], $config['per_page']);
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->crosschannel_model->getCrossChannel($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/crossChannel/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $data['push_campaigns'] = $this->crosschannel_model->getCrossChannel($AppUserCampaigns, $data['page'], $config['per_page']);
            }

            //print_r($data['push_campaigns']); die;
            $data['statuscount'] = count($data['push_campaigns']);
            $data['noofcampaigns'] = $config['per_page'];
            $data["businessId"] = $businessId;

            //$push_campaigns = $this->brand_model->getPushCampaignsByBusinessId($login->businessId);

            $data['groupId'] = $groupId;
            //$data['push_campaigns'] = $push_campaigns;//	$header['groups']

            /* if($groupId != ''){
              $data['campaign'] = $this->campaign_model->getCampaign($groupId);
              //echo '<pre>';
              //print_r($campaign); die;
              }else{
              $data['campaign'] = $this->campaign_model->getCampaign('');
              } */

            if (isset($cookie_group[0])) {
                $iOSApp = $this->campaign_model->getIosAppImage($cookie_group[0]);

                if (count($iOSApp) > 0) {
                    $data['iOSAppImage'] = base_url() . "upload/apps/" . $iOSApp->app_image;
                } else {
                    $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
                }
            } else {
                $data['iOSAppImage'] = base_url() . 'assets/template/frontend/img/ios.png';
            }


            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $data['usertype'] = $login->usertype;

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }
            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            $data['additional_profit'] = $header['loggedInUser']->additional_profit;
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/crosschannel', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function editCrossChannel($campaignId = false) {

        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {

            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

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

            foreach ($header['groups'] as $group) {
                $groups[] = $group->app_group_id;
            }

            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->crosschannel_model->getCrossChannelByBusinessId($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editCrossChannel/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("crossChannelPagination")) {
                    $config['per_page'] = $this->session->userdata("crossChannelPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;


                $data['push_campaigns'] = $this->crosschannel_model->getCrossChannelByBusinessId($login->businessId, $data['page'], $config['per_page']);
                $AppUserCampaigns = NULL;
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }
                //print_r($AppUserCampaigns); die;

                $records = $this->crosschannel_model->getCrossChannel($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editCrossChannel/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("crossChannelPagination")) {
                    $config['per_page'] = $this->session->userdata("crossChannelPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;

                $data['push_campaigns'] = $this->crosschannel_model->getCrossChannel($AppUserCampaigns, $data['page'], $config['per_page']);
            }

            $data['statuscount'] = count($data['push_campaigns']);
            $data['noofcampaigns'] = $config['per_page'];
            $data["businessId"] = $businessId;

            $data['groupId'] = $campaignId;

            if ($campaignId != '') {
                $data['campaign'] = $this->campaign_model->getCampaign($campaignId, $AppUserCampaigns);
            } else {
                $data['campaign'] = $this->campaign_model->getCampaign('');
            }

            $data['app_groupId'] = $data['campaign']->app_group_id;

            $groupData = $this->groupapp_model->getOneGroup($data['campaign']->app_group_id);
            $data['groupData'] = $groupData;

            $data['emailSettings'] = $this->campaign_model->getEmailSettings($data['campaign']->app_group_id);

            if (count($data['campaign']) > 0) {
                $iOSApp = $this->campaign_model->getIosAppImage($data['campaign']->app_group_id);
                if (count($iOSApp) > 0) {
                    if($iOSApp->app_image != ''){
                        $data['iOSAppImage'] = $iOSApp->app_image;
                    }else{
                        $data['iOSAppImage'] = '';
                    }

                } else {
                    $data['iOSAppImage'] = '';
                }
            } else {
                $data['iOSAppImage'] = '';
            }


            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $data['usertype'] = $login->usertype;

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }
            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            if($data['campaign']->persona_user_id != 0){
                $suggestion = $this->getPersonaSuggestionMsg($data['campaign']->persona_user_id);
                $responce = json_decode($suggestion);
                $data['suggestion'] = $responce->statusMessage;

                $persona = $this->contact_model->getPersonaUser($data['campaign']->persona_user_id);
                $personaName = $persona->name;

                $data['twitterSearchKeyword'] = $this->getTwitterSearchResultsInApp($personaName);
                $data['googleSearchKeyword'] = $this->getGoogleTrendInApp($personaName);


            }else{
                $data['suggestion'] = 'DUMMY DATA: 56% of this persona clicked through on an Offer.';
                $data['twitterSearchKeyword'] = '';
                $data['googleSearchKeyword'] = '';
            }
            if (count($data['campaign']) > 0) {
                $this->load->view('3.1/inner_headerBrandUser', $header);
                $this->load->view('3.1/edit_crosschannel', $data);
                $this->load->view('3.1/inner_footerBrandUser');
            } else {
                redirect(base_url() . "appUser");
            }
        } else {
            redirect(base_url());
        }
    }

    function getGifImages() {

        $type = $_POST["type"];
        $data["type"] = $type;

        echo $this->load->view('3.1/gifimages', $data);
    }

    function getGifImagesIos(){
        $type = $_POST["type"];
        $data["type"] = $type;

        echo $this->load->view('3.1/gifimagesios', $data);
    }

    public function addNote($externalUserId = null) {
        $data = array();
        $login = $this->administrator_model->front_login_session();
        $data['loginUserDetails'] = $login;
        $where = array('external_user_id' => $externalUserId);
        $externalUserDetials = $this->contact_model->getConatctDetails('', $where, $row = NULL, NULL);
        $data['externalUserDetials'] = $externalUserDetials;
        $this->load->view('3.1/add_note', $data);
    }

    public function saveNote() {
        $login = $this->administrator_model->front_login_session();
        $contactUserId = $_POST['contactUserId'];
        $loginUserId = $_POST['loginUserId'];
        $noteArea = $_POST['noteArea'];
        $event = array('external_user_id' => $contactUserId, 'sender_id' => $loginUserId, 'app_group_apps_id' => 0, 'active_device_id' => 0, 'screenName' => 'Contact Note', 'eventName' => 'Contact Note', 'eventDate' => date('YmdHis'), 'eventType' => 'contactNote', 'isExportHubspot' => 0, 'createdDate' => date('YmdHis'), 'noteText' => $noteArea);
        $insertId = $this->hurreebrand_model->saveEvent($event);

        $hubspot = $this->brand_model->getHubSpotDetails($login->user_id);
        if ($hubspot->on_off == 1) {
            if ($this->session->userdata('userHubId') != '') {

                $userRow = $this->brand_model->getExternalUserById($contactUserId);
                $email = $userRow->email;
                //get hubspot details
                //$hubDetails = $this->gethubspotDetails($login);
                $access_token = $hubspot->accress_token;
                $portalId = $hubspot->portalId;

                // Get Contact Vid form hubspot
                $vid = getOneContactDetailsHibspot($email, $access_token, $portalId);
                if ($vid != 'false') {

                    $request = array
                        (
                        "engagement" => array(
                            "active" => true,
                            "type" => "NOTE"
                        ),
                        "associations" => array
                            (
                            "contactIds" => Array
                                (
                                "0" => $vid
                            ),
                            "companyIds" => Array
                            (
                            ),
                            "dealIds" => Array
                            (
                            )
                        ),
                        "metadata" => array
                            (
                            "body" => $userRow->firstName . ' ' . $userRow->lastName . ' Contact Note: ' . $noteArea
                        )
                    );
                    $eResponce = createEnganementHubspot($vid, $request, $access_token, $portalId);

                    if ($eResponce == true) {
                        $save['eventId'] = $insertId;
                        $save['isExportHubspot'] = 1;
                        $this->contact_model->saveEvent($save);
                    }
                }
            }
        }

        if ($insertId > 0) {
            $response = array('success' => TRUE);
        } else {
            $response = array('success' => FALSE);
        }
        print json_encode($response);
        die;
    }

    function gethubspotDetails($login) {
        $portalId = $this->session->userdata('hubPortalId');

        $hwhere ['userid'] = $login->user_id;
        $hwhere['portalId'] = $portalId;
        $hwhere ['isActive'] = 1;
        $select = 'userHubSpotId, refresh_token, portalId, accress_token';
        $hubDetails = $this->hubSpot_model->getHubSpotDetails($select, $hwhere);
        return $hubDetails;
    }

    public function editNote($eventId) {
        $data = array();
        $where = array('eventId' => $eventId);
        $event = $this->contact_model->getOneUserEvent(NULL, $where, NULL);
        $data['event'] = $event;

        $this->load->view('3.1/edit_note', $data);
    }

    public function comparePopup($campaignId) {
        $data = array();
        $login = $this->administrator_model->front_login_session();
        $push_campaigns = $this->brand_model->getAllPushCampaigns($login->businessId, NULL);
        $data['push_campaigns'] = $push_campaigns;
        $data['currentCampaignId'] = $campaignId;

        $this->load->view('3.1/comparePopup', $data);
    }

    public function deleteNote() {
        $eventId = $_POST['eventId'];
        $data = array('isDelete' => 1);
        $this->db->where('eventId', $eventId);
        $this->db->update('events', $data);
        $resutl = $this->db->affected_rows();

        if ($resutl > 0) {
            $response = array('success' => TRUE);
        } else {
            $response = array('success' => FALSE);
        }
        print json_encode($response);
        die;
    }

    public function saveEditNote() {
        $eventId = $_POST['eventId'];
        $noteArea = $_POST['noteArea'];
        $data = array('noteText' => $noteArea);
        $this->db->where('eventId', $eventId);
        $this->db->update('events', $data);
        $resutl = $this->db->affected_rows();
        if ($resutl > 0) {
            $response = array('success' => TRUE);
        } else {
            $response = array('success' => FALSE);
        }
        print json_encode($response);
        die;
    }

    public function findCampaign() {
        $output = "";
        $login = $this->administrator_model->front_login_session();
        $campaignName = $_POST['campaignText'];

        if ($campaignName == "") {
            $push_campaigns = $this->brand_model->getAllPushCampaigns($login->businessId, NULL);
        } else {
            $push_campaigns = $this->brand_model->getAllPushCampaignsByName($login->businessId, $campaignName);
        }



        foreach ($push_campaigns as $campaign) {
            $output .= '<li id="compareLi-' . $campaign->id . '"><span class="bullet" title="Published"></span><span title="' . $campaign->campaignName . '">' . $campaign->campaignName . '</span><button name="compareButton-' . $campaign->id . '" id="compareButton-' . $campaign->id . '" role="button" class="compare-button">Select</button></li>';
        }

        echo $output;
        die;
    }

    public function compairCampaign() {
        $oldCampaign = $_POST['currentCampaign'];
        $campaignId = $_POST['selectedId'];
        $prevViewCampaign = $_POST['prevViewCampaign'];
        $prevSentCampaign = $_POST['prevSentCampaign'];
        $login = $this->administrator_model->front_login_session();
        $this->load->library('LoadCampaign');
        $result = $this->loadcampaign->campaignData($campaignId);
        $resultOldCampaign = $this->loadcampaign->campaignData($oldCampaign);
        $send_users_arr_new = $result['send_users_arr'];
        $view_users_arr_new = $result['view_users_arr'];
        $send_users_arr_old = $resultOldCampaign['send_users_arr'];
        $view_users_arr_old = $resultOldCampaign['view_users_arr'];

        $newCampaignData = array();
        $newCampaignData['countSendCampaigns'] = $result['countSendCampaigns'];
        $newCampaignData['countViewCampaigns'] = $result['countViewCampaigns'];
        $newCampaignData['campaignName'] = $result['campaignName'];
        $newCampaignData['push_title'] = ($result['push_title'] =='')? "NA" : $result['push_title'];
        $oldCampaignData = array();
        $oldCampaignData['countSendCampaigns'] = $resultOldCampaign['countSendCampaigns'];
        $oldCampaignData['countViewCampaigns'] = $resultOldCampaign['countViewCampaigns'];
        $oldCampaignData['campaignName'] = $resultOldCampaign['campaignName'];
        $oldCampaignData['push_title'] = ($resultOldCampaign['push_title'] =='')? "NA" : $resultOldCampaign['push_title'];
        $rows1 = array();
        $rows1['name'] = $result['campaignName'] . '- Sent count';
        $rows1['data'] = $send_users_arr_new;
        $rows2 = array();
        $rows2['name'] = $result['campaignName'] . '- View count';
        $rows2['data'] = $view_users_arr_new;
        $rows3 = array();
        $rows3['name'] = $resultOldCampaign['campaignName'] . '- View count';
        $rows3['data'] = $view_users_arr_old;
        $rows4 = array();
        $rows4['name'] = $resultOldCampaign['campaignName'] . '- Sent count';
        $rows4['data'] = $send_users_arr_old;
        $result = array();
        array_push($result, $rows1);
        array_push($result, $rows2);
        array_push($result, $rows3);
        array_push($result, $rows4);
        $data['graph'] = $result;
        $data ['newCampaignData'] = $newCampaignData;
        $data ["oldCampaignData"] = $oldCampaignData;
        print json_encode($data, JSON_NUMERIC_CHECK);

        die;
    }

    function uploadLogo() {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {

            $header['page'] = 'uploadLogo';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            if ($header['loggedInUser']->additional_profit == 1) {
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
                    //echo 'Hassan';die;
                    $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
                }
                $data['userDetails'] = $this->user_model->getOneUser($login->user_id);
                $this->load->view('3.1/inner_headerBrandUser', $header);
                $this->load->view('3.1/upload_logo', $data);
                $this->load->view('3.1/inner_footerBrandUser');
            } else {
                redirect(base_url() . "appUser");
            }
        } else {
            redirect(base_url());
        }
    }

    function inAppMessaging($groupId = false) {
        $login = $this->administrator_model->front_login_session();

        $this->session->unset_userdata("inAppMessagingPagination");

        if ($login->active != 0) {

            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);

                $businessGroups = $this->groupapp_model->getGroups($login->businessId);
                if (count($businessGroups) > 0) {
                    $app_g = $app_g2 = $app_g3 = array();
                    $data['groupApps'] = $businessGroups;
//                    foreach ($businessGroups as $businessGroup) {
//                        $data['groupApps'] = $this->groupapp_model->getGroupsWithAndroid($login->businessId);
//                        //echo $this->db->last_query();
//                        //echo '<pre>'; print_r($data['groupApps']); exit;
//                        if (count($data['groupApps']) > 0) {
//                            foreach ($data['groupApps'] as $groups) {
//                                if (!in_array($groups->app_group_id, $app_g)) {
//                                    $app_name = $groups->app_group_name;
//                                    if (!empty($groups->app_image)) {
//                                        $image = base_url() . "upload/apps/" . $groups->app_image;
//                                    } else {
//                                        $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                                    }
//                                    array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                    if (!empty($groups->app_image)) {
//                                        $app_name = $groups->app_group_name;
//                                        if (!empty($groups->app_image)) {
//                                            $image = base_url() . "upload/apps/" . $groups->app_image;
//                                        } else {
//                                            $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                        }
//                                        array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                        //echo $app_name; exit;
//                                        //  $app_g = $app_g;
//                                    }
//                                }
//                            }
//                            $app_g3 = $app_g + $app_g2;
//                            $app_g4 = $app_g5 = array();
//                            foreach ($app_g3 as $groups) {
//                                if (!in_array($groups['app_group_id'], $app_g5)) {
//                                    $app_name = $groups['app_group_name'];
//                                    $image = $groups['image'];
//                                    //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                                    array_push($app_g5, $groups['app_group_id']);
//                                    array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                                }
//                            }
//                            $data['groupApps'] = $app_g4;
//                            //echo '<pre>'; print_r($data['groupApps']); exit;
//                        }
//                        //echo '<pre>';
//                        //print_r($data['groupApps']); die;
//                    }
                }else{
                    $data['groupApps'] = '';
                }
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
                $groupApps = $this->groupapp_model->getUserGroupData($groupArray);
                //$data['groupApps'] = $this->groupapp_model->getAppUserGroupsWithImages($groupArray);
                $app_g = $app_g2 = $app_g3 = array();
                if (count($groupApps) > 0) {
//                    foreach ($data['groupApps'] as $groups) {
//                        if (!in_array($groups->app_group_id, $app_g)) {
//                            $app_name = $groups->app_group_name;
//                            if (!empty($groups->app_image)) {
//                                $image = base_url() . "upload/apps/" . $groups->app_image;
//                            } else {
//                                $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                            }
//                            array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                            if (!empty($groups->app_image)) {
//                                $app_name = $groups->app_group_name;
//                                if (!empty($groups->app_image)) {
//                                    $image = base_url() . "upload/apps/" . $groups->app_image;
//                                } else {
//                                    $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                }
//                                array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                //echo $app_name; exit;
//                                //  $app_g = $app_g;
//                            }
//                        }
//                    }
//                    $app_g3 = $app_g + $app_g2;
//                    $app_g4 = $app_g5 = array();
//                    foreach ($app_g3 as $groups) {
//                        if (!in_array($groups['app_group_id'], $app_g5)) {
//                            $app_name = $groups['app_group_name'];
//                            $image = $groups['image'];
//                            //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                            array_push($app_g5, $groups['app_group_id']);
//                            array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                        }
//                    }
                    $data['groupApps'] = $groupApps;
                    //echo '<pre>'; print_r($data['groupApps']); exit;
                }else{
                    $data['groupApps'] = '';
                }
            }

            //$email_campaigns = $this->campaign_model->getAllEmailCampaigns($login->businessId);
            //$data['email_campaigns'] = $email_campaigns;
            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->inapp_model->getAllInAppMessaging($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/emailCampaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;
                $data['inapp_messaging'] = $this->inapp_model->getAllInAppMessaging($login->businessId, $data['page'], $config['per_page']);
                $data['statuscount'] = count($data['inapp_messaging']);
                $data['noofcampaigns'] = $config['per_page'];
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->inapp_model->getInAppMessaging($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/campaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $data['inapp_messaging'] = $this->inapp_model->getInAppMessaging($AppUserCampaigns, $data['page'], $config['per_page']);

                $data['statuscount'] = count($data['inapp_messaging']);
                $data['noofcampaigns'] = $config['per_page'];
            }
            $data["businessId"] = $businessId;

            $data['groupId'] = $groupId;

            //Check User have default Campaign package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);

            if (count($userPackage) > 0) {
                $data['inAppMessaging'] = $userPackage->inAppMessaging;
                $defaultInAppMessaging = $data['inAppMessaging'];
            } else {
                $data['inAppMessaging'] = 0;    //countTotalCampaign
                $defaultInAppMessaging = $data['inAppMessaging'];
            }

            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraInAppMessaging($businessId);

            if (count($extraPackage) > 0) {
                $data['inAppMessaging'] = $extraPackage->inAppMessaging;
                $extraInAppMessaging = $data['inAppMessaging'];
            } else {
                $data['inAppMessaging'] = 0; //extraCampaignQuantity
                $extraInAppMessaging = $data['inAppMessaging'];
            }

            $data['totalinAppMessaging'] = $defaultInAppMessaging + $extraInAppMessaging;

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['usertype'] = $login->usertype;

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            $data['additional_profit'] = $header['loggedInUser']->additional_profit;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/inapp_messaging', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function editInAppMessaging($campaignId = false) {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {

            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
                $groupArray = NULL;
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

            foreach ($header['groups'] as $group) {
                $groups[] = $group->app_group_id;
            }

            if ($login->usertype == 8) {
                //Pagination starts
                $records = $this->inapp_model->getAllInAppMessaging($login->businessId, '', '');
                //Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/inAppMessaging/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("inAppMessagingPagination")) {
                    $config['per_page'] = $this->session->userdata("inAppMessagingPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;


                $data['inapp_messaging'] = $this->inapp_model->getAllInAppMessaging($login->businessId, $data['page'], $config['per_page']);
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->inapp_model->getInAppMessaging($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/inAppMessaging/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("crossChannelPagination")) {
                    $config['per_page'] = $this->session->userdata("inAppMessagingPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;

                $data['inapp_messaging'] = $this->inapp_model->getInAppMessaging($AppUserCampaigns, $data['page'], $config['per_page']);
            }

            $data['statuscount'] = count($data['inapp_messaging']);
            $data['noofcampaigns'] = $config['per_page'];
            $data["businessId"] = $businessId;

            $data['groupId'] = $campaignId;

            if ($campaignId != '') {
                $data['campaign'] = $this->inapp_model->getCampaign($campaignId,$groupArray);
            } else {
                $data['campaign'] = $this->inapp_model->getCampaign('');
            }

            $data['app_groupId'] = $data['campaign']->app_group_id;

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }

            $data['usertype'] = $login->usertype;
            $data['campaignId'] = $campaignId;

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }
            if($data['campaign']->persona_user_id != 0){
                $suggestion = $this->getPersonaSuggestionMsg($data['campaign']->persona_user_id);
                $responce = json_decode($suggestion);
                $data['suggestion'] = $responce->statusMessage;

                $persona = $this->contact_model->getPersonaUser($data['campaign']->persona_user_id);
                $personaName = $persona->name;

                $data['twitterSearchKeyword'] = $this->getTwitterSearchResultsInApp($personaName);
                $data['googleSearchKeyword'] = $this->getGoogleTrendInApp($personaName);


            }else{
                $data['suggestion'] = 'DUMMY DATA: 56% of this persona clicked through on an Offer.';
                $data['twitterSearchKeyword'] = '';
                $data['googleSearchKeyword'] = '';
            }
            if (count($data['campaign']) > 0) {
                $this->load->view('3.1/inner_headerBrandUser', $header);
                $this->load->view('3.1/edit_inapp_messaging', $data);
                $this->load->view('3.1/inner_footerBrandUser');
            } else {
                redirect(base_url() . "appUser");
            }
        } else {
            redirect(base_url());
        }
    }

    function pdf($campaignId) {

        $this->load->library('LoadCampaign');
        $result = $this->loadcampaign->campaignData($campaignId);

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
         <h3 style="background: none; border-bottom: 1px solid #ddd;  box-sizing: border-box; color: #424141; font-family: "proxima-nova",sans-serif; font-size: 14px; font-weight: 700; line-height: 1.1; margin: 0; margin-bottom: 10px; margin-top: 20px; padding: 15px;">Campaign Performance</h3>
         <div class="col-xs-12" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 100%;">
          <div class="row performance" style="box-sizing: border-box; margin-left: -15px; margin-right: -15px;">
           <div class="col-xs-6" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 50%;">
            <div class="innerPerformace" style="border-radius: 4px; box-sizing: border-box; float: left; margin: 10px 0; padding: 12px; text-align: left; width: 100%;"><strong style="box-sizing: border-box; font-weight: bold;">Campaign Name :</strong>' . $result['campaignName'] . '</div>
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
             <p style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; margin: 0; padding: 0;"><strong style="box-sizing: border-box; font-weight: bold;">' . $result['countViewCampaigns'] . '</strong></p>
             <h4 style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; font-size: 14px; font-weight: 300 !important; line-height: 1.1; margin: 0; margin-bottom: 10px; margin-top: 10px; padding: 0; text-transform: uppercase;">Views</h4>
            </div>
           </div>
           <div class="col-xs-6" style="box-sizing: border-box; float: left; min-height: 1px; padding-left: 15px; padding-right: 15px; position: relative; width: 50%;">
            <div class="inner purpleBg" style="background: #686767; border-radius: 4px; box-sizing: border-box; float: left; margin: 20px 0; padding: 15px; text-align: center; width: 100%;">
             <p style="box-sizing: border-box; color: #fff; font-family: "proxima-nova",sans-serif; margin: 0; padding: 0;"><strong style="box-sizing: border-box; font-weight: bold;">' . $result['countSendCampaigns'] . '</strong></p>
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
'); // $html can be a url or html content.
        $wkhtmltopdf->output(Wkhtmltopdf::MODE_DOWNLOAD, "file.pdf");
    }

    function insightsPagePdf() {

        $this->load->library('LoadCampaign');
        $result = $this->loadcampaign->insightsData();

        (count($result['dailySessionsUsers']) > 0 ) ? $dailySessionsUsers = $result['dailySessionsUsers'] : $dailySessionsUsers = 0;

        (count($result['dailyActiveUsers']) > 0 ) ? $dailyActiveUsers = $result['dailySessionsUsers'] : $dailyActiveUsers = 0;
        $timeOnAppUsers = $result['timeOnAppUsers'];

        if (!empty($timeOnAppUsers)) {
            $timeOnAppUsers = explode('.', $timeOnAppUsers);
            if (isset($timeOnAppUsers[0]) && isset($timeOnAppUsers[1])) {
                $timeOnAppUsers = $timeOnAppUsers[0] . ' hours' . '&nbsp;' . $timeOnAppUsers[1] . ' minutes';
            } else {
                $timeOnAppUsers = $timeOnAppUsers[0];
            }
        } else {
            $timeOnAppUsers = 0;
        }

        $countForVip = $result['countForVip'];
        $countForVipPercentage = $countForVip * 5 / 100;


        $countMultipleUserIds = $result['countMultipleUserIds'];
        $countMultipleUserIdsPercentage = $countMultipleUserIds * 5 / 100;

        $countSingleUserIds = $result['countSingleUserIds'];

        $countSingleUserIdsPercentage = $countSingleUserIds * 5 / 100;

//         echo "<pre>";
//         print_r($result['dailySessionsUsers']);
//         echo "</pre>"; die;


        $this->load->library('wkhtmltopdf');
        $array['path'] = '/tmp';
        $wkhtmltopdf = new Wkhtmltopdf;
        $wkhtmltopdf->make($array);
        $wkhtmltopdf->setTitle("New Title");
        $wkhtmltopdf->setHtml('<!DOCTYPE html>
<html lang="en">
<head style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
<meta charset="utf-8" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
<meta name="HandheldFriendly" content="True" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
<meta name="MobileOptimized" content="300" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
<meta name="format-detection" content="telephone=no" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">

<title style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">Hurree</title>

</head>
<body >

<div class="pageStarts" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;padding: 70px 0 0px;">
  <div class="container-fluid" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;padding-right: 15px;padding-left: 15px;margin-right: auto;margin-left: auto;min-width: inherit;">
    <div class="col-xs-12" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 100%;">

      <div class="pageContent" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">
        <div class="stats" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;border-radius: 4px;box-shadow: 0 0 3px rgba(0,0,0,.3);float: left;width: 100%;">
          <div class="col-xs-12" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 100%;">
            <div class="row  performance profiling" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin-right: -15px;margin-left: -15px;">
              <h3 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;page-break-after: avoid;font-weight: 700;line-height: 1.1;color: #424141;margin-top: 20px;margin-bottom: 10px;font-size: 14px;margin: 0;padding: 15px;background: none;border-bottom: 1px solid #ddd;border-top: 1px solid #ddd;">User Profiling</h3>
              <div class="col-xs-12" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 100%;">
                <div class="row" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin-right: -15px;margin-left: -15px;">
                  <div class="col-xs-4" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 33.33333333%;">
                    <div class="inner greenBg" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #424141;float: left;width: 100%;margin: 20px 0;border-radius: 4px;padding: 15px;text-align: center;">
                                                                   <h4 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: 300 !important;line-height: 1.1;color: #fff;margin-top: 10px;margin-bottom: 10px;font-size: 14px;margin: 0;padding: 0;text-transform: uppercase;">  New Users </h4>
                                              <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;"><strong style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: bold;">' . $countSingleUserIds . '</strong></p>
                                              <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;">(' . $countSingleUserIdsPercentage . '/ 100%)</p>

                                            </div>
                  </div>
                  <div class="col-xs-4" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 33.33333333%;">
                    <div class="inner purpleBg" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #686767;float: left;width: 100%;margin: 20px 0;border-radius: 4px;padding: 15px;text-align: center;">
                                                                  <h4 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: 300 !important;line-height: 1.1;color: #fff;margin-top: 10px;margin-bottom: 10px;font-size: 14px;margin: 0;padding: 0;text-transform: uppercase;">  Returning Users </h4>
                                            <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;"><strong style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: bold;">' . $countMultipleUserIds . '</strong></p>
                                            <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;">(' . $countMultipleUserIdsPercentage . '/ 100%)</p>

                                          </div>
                  </div>
                  <div class="col-xs-4" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 33.33333333%;">
                    <div class="inner blueBg" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #8e8d8d;float: left;width: 100%;margin: 20px 0;border-radius: 4px;padding: 15px;text-align: center;">
                                                                   <h4 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: 300 !important;line-height: 1.1;color: #fff;margin-top: 10px;margin-bottom: 10px;font-size: 14px;margin: 0;padding: 0;text-transform: uppercase;"> VIP</h4>
                                              <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;"><strong style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: bold;">' . $countForVip . '</strong></p>
                                              <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;">(' . $countForVipPercentage . '/ 100%)</p>

                                            </div>
                  </div>
                </div>
              </div>

            </div>

            <div class="row app-usage performance" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin-right: -15px;margin-left: -15px;background: #f3f3f3;">
              <h3 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;page-break-after: avoid;font-weight: 700;line-height: 1.1;color: #424141;margin-top: 20px;margin-bottom: 10px;font-size: 14px;margin: 0;padding: 15px;background: none;border-bottom: 1px solid #ddd;border-top: 1px solid #ddd;">App Usage</h3>
              <div class="col-xs-12" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 100%;">
              <div class="row" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;margin-right: -15px;margin-left: -15px;">
                  <div class="col-xs-4" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 33.33333333%;">
                    <div class="inner greenBg" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #424141;float: left;width: 100%;margin: 20px 0;border-radius: 4px;padding: 25px 15px;text-align: center;display: table-cell;vertical-align: middle;">
                      <h4 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: 300 !important;line-height: 1.1;color: #fff;margin-top: 10px;margin-bottom: 10px;font-size: 14px;margin: 0px !important;padding: 0;text-transform: uppercase;">Time on App (Average)</h4>
                      <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;">' . $timeOnAppUsers . '</p>
                    </div>
                  </div>
                  <div class="col-xs-4" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 33.33333333%;">
                    <div class="inner purpleBg" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #686767;float: left;width: 100%;margin: 20px 0;border-radius: 4px;padding: 25px 15px;text-align: center;display: table-cell;vertical-align: middle;">
                      <h4 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: 300 !important;line-height: 1.1;color: #fff;margin-top: 10px;margin-bottom: 10px;font-size: 14px;margin: 0px !important;padding: 0;text-transform: uppercase;">daily active users (Average)</h4>
                      <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;">' . $dailyActiveUsers . '</p>
                    </div>
                  </div>
                  <div class="col-xs-4" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;position: relative;min-height: 1px;padding-right: 15px;padding-left: 15px;float: left;width: 33.33333333%;">
                    <div class="inner blueBg" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;background: #8e8d8d;float: left;width: 100%;margin: 20px 0;border-radius: 4px;padding: 25px 15px;text-align: center;display: table-cell;vertical-align: middle;">
                      <h4 style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;font-weight: 300 !important;line-height: 1.1;color: #fff;margin-top: 10px;margin-bottom: 10px;font-size: 14px;margin: 0px !important;padding: 0;text-transform: uppercase;">daily sessions (Average)</h4>
                      <p style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;orphans: 3;widows: 3;margin: 0;padding: 0;color: #fff;">' . $dailySessionsUsers . '</p>
                    </div>
                  </div>
              </div>
              </div>
            </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>'); // $html can be a url or html content.
        $wkhtmltopdf->output(Wkhtmltopdf::MODE_DOWNLOAD, "file.pdf");
    }

    function checkUnicode() {
        $unicode = $_POST['unicode'];

        $productFile = file_get_contents(base_url() . 'assets/template/frontend/css/font-awesome.min.css');

        echo strchr($productFile, $unicode);
    }

    function referFriend() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $loginUserData = $this->user_model->getOneUser($login->user_id);

            //echo "<pre>"; print_r($loginUserData); die;
            if ($loginUserData->accountType == "trail") {
            redirect('appUser');
        }
            $header['page'] = 'referFriend';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $loginUserData;
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

            $data = array();

            $data['userData'] = $this->user_model->getOneUser($login->user_id);

            $data['referralFriends'] = $this->referfriend_model->getAllReferralFriendOfLoginUser($login->user_id);

//               echo "<pre>";
//                print_r($data);
//                echo "</pre>"; die;


            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/referFriend', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function saveReferFriend() {

        $result = 0;
        $date = date('YmdHis');
        $login = $this->administrator_model->front_login_session();
        $arr['userId'] = $login->user_id;
        $arr['email'] = $_POST['email'];
        $arr['status'] = 'Remind';
        $arr["createdDate"] = $date;
        // check for email already exist in users table.
        $resultEmailCountUserTable = $this->user_model->check_email(trim($_POST['email']));
        if ($resultEmailCountUserTable >0) {
            $response = array('success' => FALSE, 'message' => "Email is already registered with Hurree");
            print json_encode($response);
            exit;
        }

        //check for email in referFriend already exist or not.
        $emailExistCount = $this->referfriend_model->checkReferralFriendEmailExist($_POST['email']);
        //check only 3 email id's per month is allowed.
        $emailPerMonthCount = $this->referfriend_model->getCountReferralFriendPerMonth($login->user_id);
        //  echo $emailPerMonthCount ; die;

        if ($emailPerMonthCount >= 3) {
            $response = array('success' => FALSE, 'message' => "You already sent 3 invites of this month.");
            print json_encode($response);
            exit;
        }

        if ($emailExistCount == 0) {
            $result = $this->referfriend_model->insertReferralFriend($arr);
            $this->triggerReferralEmail($result);
        }

        if ($result > 0) {
            $response = array('success' => TRUE, 'message' => "Insert successfully");
        } else {
            $response = array('success' => FALSE, 'message' => "Email id already exist");
        }
        print json_encode($response);
        exit;
    }

    function change_subscription_status($profile_id, $action) {

        $api_request = 'USER=' . urlencode(PAYPAL_API_USERNAME)
                . '&PWD=' . urlencode(PAYPAL_APT_PASSWORD)
                . '&SIGNATURE=' . urlencode(PAYPAL_API_SIGNATURE)
                . '&VERSION=76.0'
                . '&METHOD=ManageRecurringPaymentsProfileStatus'
                . '&PROFILEID=' . urlencode($profile_id)
                . '&ACTION=' . urlencode($action)
                . '&NOTE=' . urlencode('Profile cancelled at store');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp'); // For live transactions, change to 'https://api-3t.paypal.com/nvp'
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Uncomment these to turn off server and peer verification
        // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        // curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API parameters for this transaction
        curl_setopt($ch, CURLOPT_POSTFIELDS, $api_request);

        // Request response from PayPal
        $response = curl_exec($ch);

        // If no response was received from PayPal there is no point parsing the response
        if (!$response)
            die('Calling PayPal to change_subscription_status failed: ' . curl_error($ch) . '(' . curl_errno($ch) . ')');

        curl_close($ch);

        // An associative array is more usable than a parameter string
        parse_str($response, $parsed_response);

        // $a = array("PROFILEID"=>"I-D73Y4E8679SP","TIMESTAMP"=>"2016-11-01T11:23:17Z","CORRELATIONID"=>"4b3db0e3cf4f4","ACK"=>"Success","VERSION"=>"76.0","BUILD"=>"24616352");

         return $parsed_response;
    }

//change_subscription_status( 'I-NARPL1C00000', 'Suspend' )
//change_subscription_status( 'I-NARPL1C00000', 'Cancel' );
//change_subscription_status( 'I-NARPL1C00000', 'Reactivate' );



    public function referralListingResponse() {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $data2['users'] = $this->referfriend_model->getAllReferralFriendOfLoginUser($login->user_id);

            // echo "<pre>"; print_r($data2['users']); die;

            $data1 = array();

            for ($i = 0; $i < count($data2['users']); $i++) {





                $data1[$i] = array(
                    $data2['users'][$i]['email'],
                    $data2['users'][$i]['status']
                );
                // print_r($data1); exit;
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

        public function triggerRemindEmail($referralId = NULL) {
        $login = $this->administrator_model->front_login_session();
        $userData = $this->user_model->getOneUser($login->user_id);
        $referralData = $this->referfriend_model->getReferralFriendDateById($referralId);
        $this->emailConfig();
        $email_template = $this->email_model->getoneemail('reminder_email');
        //// MESSAGE OF EMAIL
        $messages = $email_template->message;
        $url = base_url() . 'pages/referral/' . $userData->referral_code;
        //// replace strings from message
        $messages = str_replace('{href}', $url, $messages);
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($referralData->email);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        return $send = $this->email->send();
    }

      public function triggerReferralEmail($referralId = NULL) {
        $referralData = $this->referfriend_model->getReferralFriendDateById($referralId);
        $login = $this->administrator_model->front_login_session();
        $userData = $this->user_model->getOneUser($login->user_id);
        $this->emailConfig();
        $email_template = $this->email_model->getoneemail('referral_email');
        $messages = $email_template->message;
        $url = base_url() . 'pages/referral/' . $userData->referral_code;
        //// replace strings from message
        $messages = str_replace('{href}', $url, $messages);
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($referralData->email);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        return $send = $this->email->send();
    }

    function remindEmail() {
        $refer_id = $_POST['id'];
        $referralData = $this->referfriend_model->getReferralFriendDateById($refer_id);
        if ($this->triggerRemindEmail($refer_id)) {
            $response = array('success' => TRUE, 'message' => "Email sent successfully");
        } else {
            $response = array('success' => FALSE, 'message' => "Email not sent");
        }
        print json_encode($response);
        exit;
    }

    /* cron job function to check referral friends and deactive paypal profile */
    public function cronjobPaypalDeactivate() {
        /* for live use day = 30*/
        $days = 30;
        /* for staging use day = 1
        $days = 1;*/
        $allUsers = $this->user_model->getAllUsers();
        foreach ($allUsers as $oneUser) {

            //count the number of user having cron_status 0
             $referralFriendCount = $this->referfriend_model->getCountAllReferralFriendStatusNotProcessed($oneUser['user_Id']);
             $remainingDays = $this->calculateRemainingDays($oneUser['user_Id']);


            if ($referralFriendCount > 0 && (!is_null($oneUser['paypal_profileid']))) {
                //find entry exist already in paypal_profile table.
                $existProfile = $this->paypalprofile_model->getPaypalProfileEntry($oneUser['user_Id']);

                if (empty($existProfile)) {
                    //call the method to deactive the paypal profile
                    $response = $this->change_subscription_status(urldecode($oneUser['paypal_profileid']), 'Suspend' );
                    if($response['ACK'] == 'Success'){
                    //make entry in paypal paypal_profile_status
                    $this->paypalProfileStatusEntry(json_encode($response),$oneUser['user_Id'],$oneUser['paypal_profileid'],'Suspend');
                    }
                    //make entry in paypal profile table
                    $date = date('YmdHis');
                    $dateActivation = date('YmdHis', strtotime($date . ' + ' . (($days * $referralFriendCount) +$remainingDays) . ' days'));
                    $data = array("userId" => $oneUser['user_Id'], "paypal_profile_id" => urldecode($oneUser['paypal_profileid']), "activation_date" => $dateActivation, "deactivation_date" => $date, "current_friend_count" => $referralFriendCount, "cron_execution" => $date, "isActive" => 1);
                    $history = json_encode($data);
                    $data['history'] = $history;
                    $result = $this->paypalprofile_model->insertPaypalProfileEntry($data);
                } else {

                    if(is_null($existProfile->activation_date)){
                    $profile_id = $existProfile->profile_id;
                    //call the method to deactive the paypal profile
                    $response = $this->change_subscription_status(urldecode($oneUser['paypal_profileid']), 'Suspend' );
                    if($response['ACK'] == 'Success'){
                      //make entry in paypal paypal_profile_status
                    $this->paypalProfileStatusEntry(json_encode($response),$oneUser['user_Id'],$oneUser['paypal_profileid'],'Suspend');

                    }
                    $date = date('YmdHis');
                    $dateActivation = date('YmdHis', strtotime($date . ' + ' . (($days * $referralFriendCount)+ $remainingDays) . ' days'));
                    $currentFriendCount = $referralFriendCount;
                    $data = array('activation_date' => $dateActivation, "current_friend_count" => $currentFriendCount,"deactivation_date" => $date);
                    $previousHistory = $existProfile->history;
                    $history = json_encode($data);
                    $data['history'] = $previousHistory. '#@$%!'.$history;
                    $res = $this->paypalprofile_model->updatePaypalProfileEntry($profile_id, $data);

                    }else{
                      $activation_date = $existProfile->activation_date;
                    $current_friend_count = $existProfile->current_friend_count;
                    $profile_id = $existProfile->profile_id;
                    $activationDate = date('YmdHis', strtotime($activation_date . ' + ' . (($days * $referralFriendCount)) . ' days'));
                    $currentFriendCount = $referralFriendCount + $current_friend_count;
                    $data = array('activation_date' => $activationDate, "current_friend_count" => $currentFriendCount);
                    $previousHistory = $existProfile->history;
                    $history = json_encode($data);
                    $data['history'] = $previousHistory. '#@$%!'.$history;
                    $res = $this->paypalprofile_model->updatePaypalProfileEntry($profile_id, $data);
                    }

                }
                //update status on
                echo $countRecordProcessed = $this->referfriend_model->updateReferralFriendAfterProcessed($oneUser['user_Id']);
            }
        }
    }

    /* cron job function to check referral friends and activate paypal profile */
    public function cronjobPaypalActivate() {
        // read the current date
        $profilesToActivate = $this->paypalprofile_model->getPaypalProfileForDeactivation();
        if(!empty($profilesToActivate)){
           foreach ($profilesToActivate as $profileToActivate) {
            //call the method to deactive the paypal profile
            $response = $this->change_subscription_status($profileToActivate['paypal_profile_id'], 'Reactivate' );
            //update the paypal_profile  of after activation.
            $previousHistory = $profileToActivate['history'];
            $res = $this->paypalprofile_model->updatePaypalProfileForPaypalActivate($profileToActivate['profile_id'],$previousHistory);
            $this->paypalProfileStatusEntry(json_encode($response),$profileToActivate['userId'],$profileToActivate['paypal_profile_id'],'Reactivate');
        }
        }
    }

    function paypalProfileStatusEntry($response =NULL,$userId =NULL, $paypalProfileId=NULL,$action=NULL){
        //$response = array("PROFILEID"=>"I-D73Y4E8679SP","TIMESTAMP"=>"2016-11-01T11:23:17Z","CORRELATIONID"=>"4b3db0e3cf4f4","ACK"=>"Success","VERSION"=>"76.0","BUILD"=>"24616352");
        $response = json_encode($response);
        $data = array('userId'=> $userId, "paypal_profile_id"=> $paypalProfileId, "response"=>$response , 'isActive'=>1, 'createdDate'=>date('YmdHis'));
        $id = $this->paypalprofile_model->insertPaypalProfileStatusEntry($data);
        return $id;
    }

    function referralFriendListingResponse() {
        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {

            $referralFriends = $this->referfriend_model->getReferralFriendsListing($login->user_id);
            //	echo "<pre>";print_r($data2['users']); die;
            $data1 = array();
            $j = 1;
            for ($i = 0; $i < count($referralFriends); $i++) {
                $data1[$i] = array(
                    $j++,
                    $referralFriends[$i]['email'],
                    $referralFriends[$i]['action'],
                );
                // print_r($data1); exit;
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

    function getGoogleTrend($q=NULL){
      if($q == NULL){
            $q = $_POST['keyword'];
        }

        $string = explode(" ",$q);

        $gv = file_get_contents("https://www.google.com/trends/fetchComponent?hl=en-UK&cat=&geo=US&q=".$string[0]."&cid=TOP_QUERIES_0_0&date=today+30-d&cmpt=q&content=1&export=3");

        $data = array();
        $gv = preg_replace('/google.visualization.Query.setResponse\(/', '', $gv);
        $gv = preg_replace('/\)\;/', '', $gv);

        // Get table Headings
        preg_match('/"cols":\[{(.*)}\],"rows"/', $gv, $tables);
        $columns = array();
        if(count($tables) > 0){
        $cols = explode('},{', $tables[1]);

         foreach ($cols as $col) {
          $items = explode(",", $col);
          foreach ($items as $item) {
           $item = preg_replace('/\"/', '', $item);
           $arr = explode(':', $item);
           if ($arr[0] == 'id') {
            array_push($columns, $arr[1]);
           }
          }
         }
         // Get the rows
         preg_match('/"rows":\[{"c":(.*)\]/', $gv, $r);
         if (count($r)) {
          preg_match_all('/\[{(.*?)}\]}/', $r[1], $rows);
          for ($i = 0; $i < count($rows[1]); $i++) {
           $pairs = explode('},{', $rows[1][$i]);
           for ($p = 0; $p < count($pairs); $p++) {
            $pair = preg_replace('/\"/', '', $pairs[$p]);
            $s = preg_replace('/v:/', '', $pair);
            $data[$i][$columns[$p]] = $s;
           }
          }
         }
        if(count($data) > 0){
           echo 'Google: '.$data[0]['query'];
           //return '<br><br>Google: '.$data[0]['query'];
       }else{
           echo '';
           //return '';
       }

        }else{
            echo '';
           //return '';
        }
    }

    function getTwitterSearchResults($q=NULL){

        if($q == NULL){
            $q = $_POST['keyword'];
        }

        $word = str_replace(" ","%20", $q);

        $this->load->helper('twitterapiexchange_helper');

        $settings = array(
            'oauth_access_token' => "61417316-tqE6a3kbYc4zvhychTRrR0N51z7sC6Qvps7q2mLgs",
            'oauth_access_token_secret' => "2kZS5VTSTxJYDNRFpNQgNHbTbkJ4qfPW8oEcDrpP8uCTW",
            'consumer_key' => "CNwVtenqKyIC8mcHUP6zVUjE5",
            'consumer_secret' => "UbrCgpEJ20WERd5YuK6nMU4kNItIEGY3w8fKGARj3BECASCRMb"
        );

        $url = 'https://api.twitter.com/1.1/users/search.json';
        $getfield = '?q='.$word.'&page=1&count=1';
        $requestMethod = 'GET';
        $twitter = new TwitterAPIExchange($settings);
        $json = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
        $twitterSearch = json_decode($json);


        if(count($twitterSearch) > 0){
            echo 'Twitter: '.$twitterSearch[0]->name;
            //return '<br></br>Twitter: '.$twitterSearch[0]->name;
        }else{
            echo '';
            //return '';
        }

    }

    function getTwitterSearchResultsInApp($q=NULL){

        $word = str_replace(" ","%20", $q);

        $this->load->helper('twitterapiexchange_helper');

        $settings = array(
            'oauth_access_token' => "61417316-tqE6a3kbYc4zvhychTRrR0N51z7sC6Qvps7q2mLgs",
            'oauth_access_token_secret' => "2kZS5VTSTxJYDNRFpNQgNHbTbkJ4qfPW8oEcDrpP8uCTW",
            'consumer_key' => "CNwVtenqKyIC8mcHUP6zVUjE5",
            'consumer_secret' => "UbrCgpEJ20WERd5YuK6nMU4kNItIEGY3w8fKGARj3BECASCRMb"
        );

        $url = 'https://api.twitter.com/1.1/users/search.json';
        $getfield = '?q='.$word.'&page=1&count=1';
        $requestMethod = 'GET';
        $twitter = new TwitterAPIExchange($settings);
        $json = $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
        $twitterSearch = json_decode($json);


        if(count($twitterSearch) > 0){
            return '<br></br>Twitter: '.$twitterSearch[0]->name;
        }else{
            return '';
        }

    }

    function getGoogleTrendInApp($q=NULL){
      if($q == NULL){
            $q = $_POST['keyword'];
        }

        $string = explode(" ",$q);

        $gv = file_get_contents("https://www.google.com/trends/fetchComponent?hl=en-UK&cat=&geo=US&q=".$string[0]."&cid=TOP_QUERIES_0_0&date=today+30-d&cmpt=q&content=1&export=3");

        $data = array();
        $gv = preg_replace('/google.visualization.Query.setResponse\(/', '', $gv);
        $gv = preg_replace('/\)\;/', '', $gv);

        // Get table Headings
        preg_match('/"cols":\[{(.*)}\],"rows"/', $gv, $tables);
        $columns = array();
        if(count($tables) > 0){
        $cols = explode('},{', $tables[1]);

         foreach ($cols as $col) {
          $items = explode(",", $col);
          foreach ($items as $item) {
           $item = preg_replace('/\"/', '', $item);
           $arr = explode(':', $item);
           if ($arr[0] == 'id') {
            array_push($columns, $arr[1]);
           }
          }
         }
         // Get the rows
         preg_match('/"rows":\[{"c":(.*)\]/', $gv, $r);
         if (count($r)) {
          preg_match_all('/\[{(.*?)}\]}/', $r[1], $rows);
          for ($i = 0; $i < count($rows[1]); $i++) {
           $pairs = explode('},{', $rows[1][$i]);
           for ($p = 0; $p < count($pairs); $p++) {
            $pair = preg_replace('/\"/', '', $pairs[$p]);
            $s = preg_replace('/v:/', '', $pair);
            $data[$i][$columns[$p]] = $s;
           }
          }
         }
        if(count($data) > 0){
           return '<br><br>Google: '.$data[0]['query'];
       }else{

           return '';
       }

        }else{

           return '';
        }
    }

    function getFacebookSearchResults(){

        /*
            type = 'user', 'page', 'event', 'group', and 'place'
        */
        $login = $this->administrator_model->front_login_session();
        $social = $this->social_model->isSocialAccountExist($login->user_id,'facebook');
        if(count($social) > 0){

            $accessToken = $social[0]->access_token;
            if($accessToken != ''){

                $url = 'https://graph.facebook.com/search?q=Narendra+Modi&type=event&access_token='.$accessToken;

                $facebookAccessToken = file_get_contents($url);
                echo $facebookAccessToken;
                die;

            }else{
                echo '';
            }
        }else{
            echo '';
        }

    }

     function calculateRemainingDays($userId) {
        $userPreviousPaymentData = $this->payment_model->getRecentRecurringPaymentEntryOfUser($userId);
        if(empty($userPreviousPaymentData)){
            return  0;
        }


        $decodedJson = json_decode($userPreviousPaymentData->paymentInfo);


        if(!isset($decodedJson->next_payment_date)){
            $date=date_create($userPreviousPaymentData->createdDate);
        date_add($date,date_interval_create_from_date_string("30 days"));
              $addedDate =  date_format($date,"Y-m-d H:i:s");
              $nextPaymentDate = $addedDate;
        }else{
            $nextPaymentDate =  $decodedJson->next_payment_date;
        }

        $now = time();
        $your_date = strtotime($nextPaymentDate);
        $datediff = abs($now - $your_date);


        return    floor($datediff / (60 * 60 * 24));
    }

    function getActivationDate($userId){

        $userPreviousPaymentData = $this->paypalprofile_model->getPaypalActivationDate($userId);

        if(empty($userPreviousPaymentData)){
            return  0;
        }

        if(is_null($userPreviousPaymentData->activation_date)){
          return  0;
        }

        $now = time();
        $your_date = strtotime($userPreviousPaymentData->activation_date);
        $datediff = abs($now - $your_date);
        $days =   floor($datediff / (60 * 60 * 24));
        return $days;

    }

       function getLeftDaysUserTable($userid){

        $userData = $this->user_model->getOneUser($userid);
        if((!is_null($userData->referred_parent)) && ($userData->accountType == 'trail')){

            $date=date_create($userData->modifiedDate);
            date_add($date,date_interval_create_from_date_string("60 days"));
              $addedDate =  date_format($date,"Y-m-d H:i:s");

        $now = time();
        $your_date = strtotime($addedDate);
        $datediff = abs( $your_date -$now);
        $days =   floor($datediff / (60 * 60 * 24));
        return $days;
        }

        $date=date_create($userData->modifiedDate);
        date_add($date,date_interval_create_from_date_string("30 days"));
              $addedDate =  date_format($date,"Y-m-d H:i:s");

        $now = time();
        $your_date = strtotime($addedDate);
        $datediff = abs( $your_date -$now);
        $days =   floor($datediff / (60 * 60 * 24));
        return $days;

    }

    public function updateUserPaymentStatus(){

        $allUsers = $this->user_model->getAllPaidUsers();
        foreach ($allUsers as $oneUser) {

            $paypalProfileEntry = $this->paypalprofile_model->getPaypalProfileEntry($oneUser['user_Id']);
//

            //    case 1: paypalprofile is empty
            if(empty($paypalProfileEntry)){
                        $userPreviousPaymentData = $this->payment_model->getRecentRecurringPaymentEntryOfUser($oneUser['user_Id']);
                        if(empty($userPreviousPaymentData)){
                           $data = array ("user_Id"=>$oneUser['user_Id'], 'accountType'=> "trail");
                            $userPreviousPaymentData = $this->user_model->updatebio($data);
                            continue;
                        }

                    $decodedJson = json_decode($userPreviousPaymentData->paymentInfo);


        if(!isset($decodedJson->next_payment_date)){
            $date=date_create($userPreviousPaymentData->createdDate);
        date_add($date,date_interval_create_from_date_string("30 days"));
              $addedDate =  date_format($date,"Y-m-d H:i:s");
              $nextPaymentDate = $addedDate;
        }else{
            $nextPaymentDate =  $decodedJson->next_payment_date;
        }

        $now = time();
        $your_date = strtotime($nextPaymentDate);
        $datediff = ($your_date - $now);
       $leftDays =     floor($datediff / (60 * 60 * 24));
        if(($leftDays <=0)){
            // update user record and set payment status to trail
            $data = array ("user_Id"=>$oneUser['user_Id'], 'accountType'=> "trail");
            $userPreviousPaymentData = $this->user_model->updatebio($data);
            }
            }

            //    case 2: paypalprofile is not empty
            if(!empty($paypalProfileEntry)){

                //read the value of activation_date

                if(is_null($paypalProfileEntry->activation_date)){
                    $userPreviousPaymentData = $this->payment_model->getRecentRecurringPaymentEntryOfUser($oneUser['user_Id']);
                    if(empty($userPreviousPaymentData)){
                           $data = array ("user_Id"=>$oneUser['user_Id'], 'accountType'=> "trail");
                            $userPreviousPaymentData = $this->user_model->updatebio($data);
                            continue;
                        }
                    $decodedJson = json_decode($userPreviousPaymentData->paymentInfo);


        if(!isset($decodedJson->next_payment_date)){
            $date=date_create($userPreviousPaymentData->createdDate);
        date_add($date,date_interval_create_from_date_string("30 days"));
              $addedDate =  date_format($date,"Y-m-d H:i:s");
              $nextPaymentDate = $addedDate;
        }else{
            $nextPaymentDate =  $decodedJson->next_payment_date;  //echo "2"; die;
        }

        $now = time();
        $your_date = strtotime($nextPaymentDate);
        $datediff = ($your_date - $now);
        $leftDays =     floor($datediff / (60 * 60 * 24));


        if(($leftDays <=0)){
            // update user record and set payment status to trail
            $data = array ("user_Id"=>$oneUser['user_Id'], 'accountType'=> "trail");
            $userPreviousPaymentData = $this->user_model->updatebio($data);

            }

                }



            }



        }
    }


    function crossChannelEmailPreview() {
        $this->load->view('3.1/cross_channel_email_preview');
    }


    function searchGiphyTags(){
        if(isset($_POST)){
            $url = "http://giphy.com/ajax/tags/search/?q=".urlencode($_POST['tag']);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec ($ch);
            curl_close ($ch);
            echo $result;
            //$response = json_decode($result);
            //$obj = $response->result->objects;

        }else{
            echo '';
        }

    }


    function getGiphyImages(){

        $tag = $_POST['tag'];
        if(preg_match('/\s/',$tag) == 1){
            $search = str_replace(" ","+",$tag);
        }else{
            $search = $tag;
        }
        $endpoint = 'http://api.giphy.com/v1/gifs/search?q='.$search.'&api_key=dc6zaTOxFJmzC&limit=9';

        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = @curl_exec($ch);
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch);
        @curl_close($ch);

        echo $response;
    }

    /* Webhook created by Hassan Ali */
    function webhook($groupId = false){

        $login = $this->administrator_model->front_login_session();
        //$this->session->unset_userdata("emailCampaignPagination");

        if ($login->active != 0) {

            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);

                $businessGroups = $this->groupapp_model->getGroups($login->businessId);
                if (count($businessGroups) > 0) {
                    $app_g = $app_g2 = $app_g3 = array();
                    $data['groupApps'] = $businessGroups;
//                    foreach ($businessGroups as $businessGroup) {
//                        $data['groupApps'] = $this->groupapp_model->getGroupsWithAndroid($login->businessId);
//                        //echo '<pre>'; print_r($data['groupApps']); //exit;
//                        if (count($data['groupApps']) > 0) {
//                            foreach ($data['groupApps'] as $groups) {
//                                if (!in_array($groups->app_group_id, $app_g)) {
//                                    $app_name = $groups->app_group_name;
//                                    if (!empty($groups->app_image)) {
//                                        $image = base_url() . "upload/apps/" . $groups->app_image;
//                                    } else {
//                                        $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                                    }
//                                    array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                    if (!empty($groups->app_image)) {
//                                        $app_name = $groups->app_group_name;
//                                        if (!empty($groups->app_image)) {
//                                            $image = base_url() . "upload/apps/" . $groups->app_image;
//                                        } else {
//                                            $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                        }
//                                        array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                        //echo $app_name; exit;
//                                        //  $app_g = $app_g;
//                                    }
//                                }
//                            }
//                            $app_g3 = $app_g + $app_g2;
//                            $app_g4 = $app_g5 = array();
//                            foreach ($app_g3 as $groups) {
//                                if (!in_array($groups['app_group_id'], $app_g5)) {
//                                    $app_name = $groups['app_group_name'];
//                                    $image = $groups['image'];
//                                    //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                                    array_push($app_g5, $groups['app_group_id']);
//                                    array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                                }
//                            }
//                            $data['groupApps'] = $app_g4;
//                            //echo '<pre>'; print_r($data['groupApps']); exit;
//                        }
//                        //echo '<pre>';
//                        //print_r($data['groupApps']); die;
//                    }
                }else{
                    $data['groupApps'] = '';
                }
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
                $groupApps = $this->groupapp_model->getUserGroupData($groupArray);
                //$data['groupApps'] = $this->groupapp_model->getAppUserGroupsWithImages($groupArray);
                $app_g = $app_g2 = $app_g3 = array();
                if (count($groupApps) > 0) {
//                    foreach ($data['groupApps'] as $groups) {
//                        if (!in_array($groups->app_group_id, $app_g)) {
//                            $app_name = $groups->app_group_name;
//                            if (!empty($groups->app_image)) {
//                                $image = base_url() . "upload/apps/" . $groups->app_image;
//                            } else {
//                                $image = base_url() . "assets/template/frontend/img/1466507439.png"; //'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';
//                            }
//                            array_push($app_g2, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                            if (!empty($groups->app_image)) {
//                                $app_name = $groups->app_group_name;
//                                if (!empty($groups->app_image)) {
//                                    $image = base_url() . "upload/apps/" . $groups->app_image;
//                                } else {
//                                    $image = base_url() . "assets/template/frontend/img/1466507439.png";
//                                }
//                                array_push($app_g, array('app_group_id' => $groups->app_group_id, 'app_group_name' => $app_name, 'image' => $image));
//                                //echo $app_name; exit;
//                                //  $app_g = $app_g;
//                            }
//                        }
//                    }
//                    $app_g3 = $app_g + $app_g2;
//                    $app_g4 = $app_g5 = array();
//                    foreach ($app_g3 as $groups) {
//                        if (!in_array($groups['app_group_id'], $app_g5)) {
//                            $app_name = $groups['app_group_name'];
//                            $image = $groups['image'];
//                            //}else{ $image = 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRwF0CRVNehjyrJgx_BgDZPiQhE_GuNPwhOub29UjLN_vmwh2fSJkeuEuQ';}
//                            array_push($app_g5, $groups['app_group_id']);
//                            array_push($app_g4, array('app_group_id' => $groups['app_group_id'], 'app_group_name' => $app_name, 'image' => $image));
//                        }
//                    }
                    $data['groupApps'] = $groupApps;
                    //echo '<pre>'; print_r($data['groupApps']); exit;
                }else{
                    $data['groupApps'] = '';
                }
            }

            //$email_campaigns = $this->campaign_model->getAllEmailCampaigns($login->businessId);
            //$data['email_campaigns'] = $email_campaigns;
            if ($login->usertype == 8) {
                //Pagination starts
                $this->load->model('webhook_model');
                $records = $this->webhook_model->getAllWebhooks($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/webhook/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;
                $data['webhooks'] = $this->webhook_model->getAllWebhooks($login->businessId, $data['page'], $config['per_page']);
                $data['statuscount'] = count($data['webhooks']);
                $data['noofcampaigns'] = $config['per_page'];
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }
                $this->load->model('webhook_model');
                $records = $this->webhook_model->getWebhooks($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/campaigns/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $data['webhooks'] = $this->webhook_model->getWebhooks($AppUserCampaigns, $data['page'], $config['per_page']);

                $data['statuscount'] = count($data['webhooks']);
                $data['noofcampaigns'] = $config['per_page'];
            }
            $data["businessId"] = $businessId;

            $data['groupId'] = $groupId;

            //Check User have default Campaign package
            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);

            if (count($userPackage) > 0) {
                $data['defaultWebhook'] = $userPackage->webhook;
            } else {
                $data['defaultWebhook'] = 0;    //countTotalCampaign
            }

            //Check User have extra Campaign package
            $extraPackage = $this->campaign_model->getBrandUserExtraWebhook($businessId);

            if (count($extraPackage) > 0) {
                $data['extraWebhook'] = $extraPackage->webhook;
            } else {
                $data['extraWebhook'] = 0;
            }

            $data['totalWebhook'] = $data['defaultWebhook'] + $data['extraWebhook'];

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['usertype'] = $login->usertype;

            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }

            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }

            $data['additional_profit'] = $header['loggedInUser']->additional_profit;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/webhook', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }

    }

    function confirmationlaunch(){
        $this->load->view('3.1/confirmationlaunch');
    }

    function editconfirmationlaunch(){
        $this->load->view('3.1/editconfirmationlaunch');
    }

      function generateReferralCode(){
      $this->load->library('couponcode');
      $code = new CouponCode();
      $result = $code->generate();
      if($code->validate($result)){
        return $code->normalize($result);
      }else{
        $this->generateReferralCode();
      }
  }

    function editWebhook($campaignId = false){

        $login = $this->administrator_model->front_login_session();

        if ($login->active != 0) {
            $header['page'] = 'userlisting';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;

            $data['allPermision'] = array();
            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;
            $data['cookie_group'] = $cookie_group;

            //App Groups list in menu
            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
                $groupArray = NULL;
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

            foreach ($header['groups'] as $group) {
                $groups[] = $group->app_group_id;
            }

            //$push_campaigns = $this->brand_model->getPushCampaignsByBusinessId($login->businessId);

            $data['groupId'] = $campaignId;
            //$data['push_campaigns'] = $push_campaigns; //   $header['groups']

            $this->load->model('webhook_model');
            if ($campaignId != '') {
                $data['campaign'] = $this->webhook_model->getWebhook($campaignId, $groupArray);
            } else {
                $data['campaign'] = $this->webhook_model->getWebhook('');
            }

            $data['app_groupId'] = $data['campaign']->app_group_id;

            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['usertype'] = $login->usertype;

            //$email_campaigns = $this->campaign_model->getAllEmailCampaigns($login->businessId);
            //$data['email_campaigns'] = $email_campaigns;

            if ($login->usertype == 8) {
                //Pagination starts

                $records = $this->webhook_model->getAllWebhooks($login->businessId, '', '');   //// Get Total No of Records in Database
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editWebhook/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("webhookPagination")) {
                    $config['per_page'] = $this->session->userdata("webhookPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;
                $data['webhooks'] = $this->webhook_model->getAllWebhooks($login->businessId, $data['page'], $config['per_page']);
                $data['statuscount'] = count($data['webhooks']);
                $data['noofcampaigns'] = $config['per_page'];
            } elseif ($login->usertype == 9) {
                $groups = $this->campaign_model->getUserGroups($login->user_id);

                foreach ($groups as $group) {

                    $AppUserCampaigns[] = $group->app_group_id;
                }

                $records = $this->webhook_model->getWebhooks($AppUserCampaigns, '', '');
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'appUser/editWebhook/';
                $config['total_rows'] = $data['records'];
                if ($this->session->userdata("webhookPagination")) {
                    $config['per_page'] = $this->session->userdata("webhookPagination");
                } else {
                    $config['per_page'] = '6';
                }
                $config['uri_segment'] = 3;
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
                $data['page'] = $page;

                $data['webhooks'] = $this->webhook_model->getWebhooks($AppUserCampaigns, $data['page'], $config['per_page']);

                $data['statuscount'] = count($data['webhooks']);
                $data['noofcampaigns'] = $config['per_page'];
            }
            $data["businessId"] = $businessId;

            //print_r($data['email_campaigns']); die;
            if ($login->usertype == 9) {

                $header['allPermision'] = getAssignPermission($login->user_id);
                $data['allPermision'] = $header['allPermision'];
            }
            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }

            $data['lists'] = array();
            $lists = $this->lists_model->getAllListsOfBusinessId($login->businessId);
            if (count($lists) > 0) {
                $data['lists'] = $lists;
            }

            if($data['campaign']->persona_user_id != 0){
                $suggestion = $this->getPersonaSuggestionMsg($data['campaign']->persona_user_id);
                $responce = json_decode($suggestion);
                $data['suggestion'] = $responce->statusMessage;

                $persona = $this->contact_model->getPersonaUser($data['campaign']->persona_user_id);
                $personaName = $persona->name;

                $data['twitterSearchKeyword'] = $this->getTwitterSearchResultsInApp($personaName);
                $data['googleSearchKeyword'] = $this->getGoogleTrendInApp($personaName);


            }else{
                $data['suggestion'] = 'DUMMY DATA: 56% of this persona clicked through on an Offer.';
                $data['twitterSearchKeyword'] = '';
                $data['googleSearchKeyword'] = '';
            }
            if (count($data['campaign']) > 0) {
                $this->load->view('3.1/inner_headerBrandUser', $header);
                $this->load->view('3.1/edit_webhook', $data);
                $this->load->view('3.1/inner_footerBrandUser');
            } else {
                redirect(base_url() . "appUser");
            }
        } else {
            redirect(base_url());
        }
    }

    function saveAutomation() {

        $login = $this->administrator_model->front_login_session();
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $additional_profit = $header['loggedInUser']->additional_profit;

        $json = file_get_contents('php://input');
        //print_r($json); die;
        $params = json_decode($json);
        foreach ($params as $param) {
            //print_r($param); die;
            if ($param->selectedPlatform != 'email') {
                if ($param->push_icon != '') {

                    $ime = $param->push_icon;

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

                    $fullpath = 'upload/pushNotificationCampaigns/icon/' . $filename;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['push_icon'] = $filename;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_icon'] = $param->push_icon;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['push_icon'] = $dataCampaign->push_icon;
                    }
                }

                if ($param->push_img_url != '') {
                    $save['push_img_url'] = $param->push_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['push_img_url'] = '';
                    } else {
                        if ($save['push_icon'] != '') {
                            $save['push_img_url'] = '';
                        } else {
                            $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                            $save['push_img_url'] = $dataCampaign->push_img_url;
                        }
                    }
                }

                if ($param->expandedImage != '') {

                    $imexpanded = $param->expandedImage;

                    $imageExpanded = explode(';base64,', $imexpanded);
                    $size = getimagesize($imexpanded);
                    $type = $size['mime'];
                    $typea = explode('/', $type);
                    $extnsn = $typea[1];
                    $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                    $img_cont = str_replace(' ', '+', $imageExpanded[1]);
                    //$img_cont=$image[1];
                    $data = base64_decode($img_cont);
                    $im = imagecreatefromstring($data);
                    $filename1 = time() . '.' . $extnsn;

                    $fullpath = 'upload/pushNotificationCampaigns/expandedImage/' . $filename1;

                    // code for upload image in folder
                    imagealphablending($im, false);
                    imagesavealpha($im, true);

                    if (in_array($extnsn, $valid_exts)) {
                        $quality = 0;
                        if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                            $quality = round((100 - $quality) * 0.09);

                            $resp = imagejpeg($im, $fullpath, $quality);
                        } else if ($extnsn == 'png') {

                            $resp = imagepng($im, $fullpath);
                        } else if ($extnsn == 'gif') {

                            $resp = imagegif($im, $fullpath);
                        }
                    }
                    $save['expandedImage'] = $filename1;
                } else {

                    if ($param->campaignId == '') {
                        $save['expandedImage'] = $param->expandedImage;
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        $save['expandedImage'] = $dataCampaign->expandedImage;
                    }
                }

                if ($param->expanded_img_url != '') {
                    $save['expanded_img_url'] = $param->expanded_img_url;
                } else {

                    if ($param->campaignId == '') {
                        $save['expanded_img_url'] = '';
                    } else {
                        $dataCampaign = $this->campaign_model->getCampaign($param->campaignId);
                        if ($save['expandedImage'] != '') {
                            $save['expanded_img_url'] = '';
                        } else {
                            $save['expanded_img_url'] = $dataCampaign->expanded_img_url;
                        }
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
            $save['push_notification_image'] = $param->push_notification_image;

            if ($param->selectedPlatform == 'android') {
                $save['push_title'] = $param->push_title;
                $save['push_message'] = $param->push_message;
                $save['summery_text'] = $param->summery_text;
            } else {
                $save['push_message'] = $param->push_message;
            }
            if ($param->selectedPlatform == 'email') {
                $save['push_title'] = $param->push_title;
                $message = str_replace("&lbrace;","{",$param->push_message);
                $message = str_replace("&rbrace;","}",$message);
                $message = str_replace("&dollar;","$",$message);
                $save['push_message'] = $message;
                $save['displayName'] = $param->displayName;
                $save['fromAddress'] = $param->fromAddress;
                $save['replyToAddress'] = $param->replyToAddress;
            }

            if ($param->selectedPlatform != 'email') {
                $save["custom_url"] = $param->custom_url;
                $save["redirect_url"] = $param->redirect_url;
                //$save["deep_link"] = $param->deep_link;
                if ($param->selectedPlatform == 'android') {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                } else {
                    $save["send_push_to_recently_used_device"] = $param->send_push_to_recently_used_device;
                    $save["limit_this_push_to_iPad_devices"] = $param->limit_this_push_to_iPad_devices;
                    $save["limit_this_push_to_iphone_and_ipod_devices"] = $param->limit_this_push_to_iphone_and_ipod_devices;
                }
            }
        }
            if ($param->campaignId == '') {
                $save["isActive"] = 0;
                $save["isDraft"] = 0;

                //$save["type"] = $param->type;
                $save["createdDate"] = date('YmdHis');
                $save["modifiedDate"] = date('YmdHis');
                $this->campaign_model->savePushNotificationCampaign($save);

                $login = $this->administrator_model->front_login_session();
                $businessId = $login->businessId;

                $extraPackage = $this->campaign_model->getBrandUserExtraPackage($businessId);

                if ($param->selectedPlatform == 'android') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->androidCampaign > 0 || $extraPackage->androidCampaign == 'unlimited')) {
                            if ($extraPackage->androidCampaign != 'unlimited') {
                                $update['androidCampaign'] = $extraPackage->androidCampaign - 1;
                            } else {
                                $update['androidCampaign'] = $extraPackage->androidCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $androidCampaign = $userPackage->androidCampaign;
                            $updateAndroidCampaign = $androidCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'androidCampaign' => $updateAndroidCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($param->selectedPlatform == 'iOS') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->iOSCampaign > 0 || $extraPackage->iOSCampaign == 'unlimited')) {
                            if ($extraPackage->iOSCampaign != 'unlimited') {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign - 1;
                            } else {
                                $update['iOSCampaign'] = $extraPackage->iOSCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $iOSCampaign = $userPackage->iOSCampaign;
                            $updateiOSCampaign = $iOSCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'iOSCampaign' => $updateiOSCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }

                if ($param->selectedPlatform == 'email') {
                    if ($additional_profit != 1) {
                        if (count($extraPackage) > 0 && ($extraPackage->emailCampaign > 0 || $extraPackage->emailCampaign == 'unlimited')) {
                            if ($extraPackage->emailCampaign != 'unlimited') {
                                $update['emailCampaign'] = $extraPackage->emailCampaign - 1;
                            } else {
                                $update['emailCampaign'] = $extraPackage->emailCampaign;
                            }

                            $update['user_extra_packages_id'] = $extraPackage->user_extra_packages_id;
                            $this->campaign_model->updateBrandUserExtraPackage($update);
                        } else {
                            //Update total campaigns
                            $userPackage = $this->campaign_model->getBrandUserPackageInfo($businessId);
                            $emailCampaign = $userPackage->emailCampaign;
                            $updateEmailCampaign = $emailCampaign - 1;

                            $update = array(
                                'user_pro_id' => $userPackage->user_pro_id,
                                'emailCampaign' => $updateEmailCampaign
                            );
                            $this->campaign_model->updateBrandUserTotalCampaigns($update);
                        }
                    }
                }
            } else {
                $save['id'] = $param->campaignId;
                $save["modifiedDate"] = date('YmdHis');
                $this->campaign_model->updatePushNotificationCampaign($save);
            }
            echo 1;
        //}
    }

    function confirmAutomation(){
        $this->load->view('3.1/confirm_automation');
    }


}
