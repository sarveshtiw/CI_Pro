<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Social extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // To use site_url and redirect on this controller.
        $this->load->helper(array('url', 'hurree'));
        //$this->load->helper('cookie');
        $this->load->model(array('user_model', 'brand_model', 'email_model', 'score_model', 'administrator_model', 'social_model'));
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    //oauth for gmail
    public function auth() {

        try {
            $accesstoken = '';
            $client_id = google_auth_client_id;
            $client_secret = google_auth_client_secret;
            $redirect_uri = base_url() . 'social/auth';
            $max_results = 1000;

            //die("i am in 1");
            if (!(isset($_GET["code"]))) {

                $this->session->set_flashdata('error_message', 'Some error occured or You deny contacts import');
                redirect("timeline");
            }

            $auth_code = $_GET["code"];
            $fields = array(
                'code' => urlencode($auth_code),
                'client_id' => urlencode($client_id),
                'client_secret' => urlencode($client_secret),
                'redirect_uri' => urlencode($redirect_uri),
                'grant_type' => urlencode('authorization_code')
            );
            $post = '';
            foreach ($fields as $key => $value) {
                $post .= $key . '=' . $value . '&';
            }
            $post = rtrim($post, '&');
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
            curl_setopt($curl, CURLOPT_POST, 5);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $result = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($result);
            $accesstoken = $response->access_token;
            $url = 'https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $max_results . '&alt=json&v=3.0&oauth_token=' . $accesstoken;
            $xmlresponse = $this->curl_file_get_contents1($url);
            $temp = json_decode($xmlresponse, true);

            $emails = array();
            if (count($temp['feed']['entry'])) {
                foreach ($temp['feed']['entry'] as $cnt) {

                    if (!empty($cnt['gd$email']['0']['address'])) {
                        $emails[] = $cnt['gd$email']['0']['address'];
                    }
                }
            }
            $friendsEmails = implode(",", $emails);
            $date = date('Y-m-d H:i:s');
            $login = $this->administrator_model->front_login_session();
            $userid = $login->user_id;
            $data = array(
                'email' => $friendsEmails,
                'user_id' => $userid,
                'isActive' => '1',
                'isDelete' => '0',
                'createdDate' => $date,
                'modifiedDate' => $date
            );
            $this->db->insert('friends_import', $data);
            $id = $this->db->insert_id();
            redirect('timeline?import=' . $id);
        } catch (Exception $e) {
            die("i am in catch");
            //  Yii::$app->session->setFlash('error', 'Some problem in importing contacts plz try again.');
        }
    }

    //fetch contacts from hotmail or outlook or email
    function curl_file_get_contents1($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    //oauth for hotmail
    public function hotmailoauth() {
        ini_set('memory_limit', '512M');
        $client_id = '000000004818EB51';
        $client_secret = 'cmd3lGb1x2WeYB2S-PuEcTGkbaq4Liai';
        $redirect_uri1 = 'http://stage.hurree.co/social/hotmailoauth';
        $auth_code = $_GET["code"];

        $fields = array(
            'code' => urlencode($auth_code),
            'client_id' => urlencode($client_id),
            'client_secret' => urlencode($client_secret),
            'redirect_uri' => urlencode($redirect_uri1),
            'grant_type' => urlencode('authorization_code')
        );
        $post = '';
        foreach ($fields as $key => $value) {
            $post .= $key . '=' . $value . '&';
        }
        $post = rtrim($post, '&');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://login.live.com/oauth20_token.srf');
        curl_setopt($curl, CURLOPT_POST, 5);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($result);
        $accesstoken = $response->access_token;
        $url = 'https://apis.live.net/v5.0/me/contacts?access_token=' . $accesstoken . '&limit=10000';
        $xmlresponse = $this->curl_file_get_contents1($url);
        $xml = json_decode($xmlresponse, true);

        $emails = array();
        if (count($xml['data'])) {
            foreach ($xml['data'] as $cnt) {
                if (!empty($cnt['emails']['preferred'])) {
                    $emails[] = $cnt['emails']['preferred'];
                }
            }
        }

        $friendsEmails = implode(",", $emails);
        if (empty($friendsEmails)) {
            $this->session->set_flashdata('error_message', 'No contacts found or deny the contact import');
            redirect("timeline");
        }

        // var_dump($friendsEmails);die;
        $date = date('Y-m-d H:i:s');
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $data = array(
            'email' => $friendsEmails,
            'user_id' => $userid,
            'isActive' => '1',
            'isDelete' => '0',
            'createdDate' => $date,
            'modifiedDate' => $date
        );
        $this->db->insert('friends_import', $data);
        $id = $this->db->insert_id();
        redirect('timeline?import=' . $id);
    }

    public function googleConnect() {
        $google_sconnect_client_id = google_sconnect_client_id;
        $google_sconnect_redirect_uri = base_url() . 'social/google';
        $url = "https://accounts.google.com/o/oauth2/auth?client_id=$google_sconnect_client_id&redirect_uri=$google_sconnect_redirect_uri&scope=https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/plus.stream.read https://www.googleapis.com/auth/plus.stream.write https://www.googleapis.com/auth/plus.circles.read https://www.googleapis.com/auth/plus.circles.write https://www.googleapis.com/auth/userinfo.profile&response_type=code&access_type=offline";
        redirect($url);
    }

    public function google() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        try {
            $accesstoken = '';
            $client_id = google_sconnect_client_id;
            $client_secret = google_sconnect_client_secret;
            $redirect_uri = base_url() . 'social/google';
            $auth_code = $_GET["code"];
            if (!empty($auth_code)) {
                //code to find access & refresh token of google
                $fields = array(
                    'code' => urlencode($auth_code),
                    'client_id' => urlencode($client_id),
                    'client_secret' => urlencode($client_secret),
                    'redirect_uri' => urlencode($redirect_uri),
                    'grant_type' => urlencode('authorization_code'),
                );
                $post = '';
                foreach ($fields as $key => $value) {
                    $post .= $key . '=' . $value . '&';
                }
                $post = rtrim($post, '&');
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
                curl_setopt($curl, CURLOPT_POST, 5);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                $result = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($result);
                $accesstoken = $response->access_token;
                if (isset($response->refresh_token)) {
                    $refreshtoken = $response->refresh_token;
                }
                //access & refresh token code end here and we get the access & refresh token
                //code to find user data by google
                $urlForUserData = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=' . $accesstoken;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlForUserData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //execute post
                $userDataJson = curl_exec($ch);
                //close connection
                curl_close($ch);

                $userData = json_decode($userDataJson);

                $date = date('Y-m-d H:i:s');
                $data = array(
                    'emails' => NULL,
                    'active' => '1',
                    'isDelete' => '0',
                    'createdDate' => $date,
                    'modifiedDate' => $date,
                    'user_id' => $userid,
                    'source' => 'google',
                    'source_id' => $userData->id,
                    'access_token' => NULL,
                    'refresh_token' => $refreshtoken,
                    'oauth_token_secret' => NULL,
                    'token_type' => NULL,
                    'expires_in' => NULL
                );
                $login = $this->administrator_model->front_login_session();
                $userid = $login->user_id;
                $userRow = $this->social_model->isSocialAccountExist($userid, "google");

                $login = $this->administrator_model->front_login_session();
                $userid = $login->user_id;
                $userRow = $this->social_model->isSocialAccountExist($userid, "google");

                if (count($userRow) == 0) {
                    $this->social_model->save($data);
                } else {
                    $this->social_model->update($userid, "google", $data);
                }
            }
            if ($login->usertype == 8 || $login->usertype == 9) {
                redirect('appUser/crmPage');
            } else {
                redirect('businessUser/crmPage');
            }
        } catch (Exception $e) {
            die("i am in catch");
        }
    }

    public function fblogin($type = false) {
        $this->load->library('facebook'); // Automatically picks appId and secret from config
        $data['user_profile'] = array();

        if ($type == "login") {
            $this->session->set_userdata("fblogin", 1);
        } elseif ($type == "signup") {
            $this->session->set_userdata("fbsignup", 1);
        }

        $user = $this->facebook->getUser();
        if ($user) {
            $facebook = $this->facebook->api('/me?fields=name,first_name,last_name,email,birthday,education,gender,id');

            if (!isset($facebook['email'])) {
                $facebook['email'] = '';
            }
            $arr_user['fbid'] = $facebook['id'];

            $oneuser = $this->user_model->getOneUserDetails($arr_user, "*");
            //print_r($oneuser); exit;
            if (count($oneuser) > 0) {
                $userDetails = $this->user_model->getUser($oneuser->user_Id);

                if (isset($userDetails->active) && $userDetails->active == 1) {
                    $this->session->set_userdata("logged_in", $userDetails);
                    $_SESSION['hurree-business'] = 'access';
                    redirect('appUser');
                    exit();
                } else {
                    $this->session->set_userdata("account_inactive", "Your Account is not active. Please Contact to Administrator.");
                    redirect(base_url());
                    exit();
                }
            } else {

                $arr['email'] = $facebook['email'];
                $arr['active'] = 1;
                $arr['isDelete'] = 0;
                $userRow = $this->user_model->getUserEmailExist($arr, "*");
                if (count($userRow) > 0) {
                    if ($userRow->loginSource == "google") {
                        $this->session->set_flashdata("email_id_exist", "You are already signup with google plus.");
                        redirect(base_url());
                        exit();
                    } else {
                        $this->session->set_flashdata("email_id_exist", "This email is already exists. Please try another account.");
                        redirect(base_url());
                        exit();
                    }
                }
                //echo 'New User';
                /* $last_insert_user_id = $this->user_model->getMaxUserRow();
                  $last_insert_id = rand();
                  if(isset($last_insert_user_id->user_Id)){
                  $last_insert_id = $last_insert_user_id->user_Id + 1;
                  } */
                $username = str_replace(' ', '', $facebook['name']);
                $username = lcfirst($username);
                //$variUsername='';
                $generatePass = RandomStringGenerate();

                $variUsername = checkusernameExist($username);

                $first_name = '';
                $last_name = '';
                if (isset($facebook['first_name']) || isset($facebook['last_name'])) {
                    $first_name = $facebook['first_name'];
                    $last_name = $facebook['last_name'];
                } else {
                    $name = explode(' ', $facebook['name']);
                    $first_name = $name[0];
                    $last_name = $name[1];
                }

                //$variUsername==''?$variUsername=$username:$variUsername=$variUsername.mktime();   //// Line to be deteted after correct code.
                if (!isset($facebook['email'])) {
                    $facebook['email'] = '';
                }
                generateReferalCode:
                //generate referal code
                $referralCode = $this->generateReferralCode();
                //check referal code in database
                $referralCount = $this->user_model->checkReferralCodeExist($referralCode);
                if ($referralCount > 0) {
                    goto generateReferalCode;
                }

                
                $arr_userDetails['user_Id'] = '';
                $arr_userDetails['email'] = $facebook['email'];
                $arr_userDetails['businessId'] = 0;
                $arr_userDetails['username'] = $variUsername;
                $arr_userDetails['accountType'] = 'trail';
                $arr_userDetails['createdDate'] = date('YmdHis');
                $arr_userDetails['active'] = 1;
                $arr_userDetails['loginSource'] = 'Facebook';
                $arr_userDetails['image'] = 'user.png';
                $arr_userDetails['header_image'] = 'profileBG.jpg';
                $arr_userDetails['usertype'] = 8;
                $arr_userDetails['password'] = md5($generatePass);
                $arr_userDetails['firstname'] = $first_name;
                $arr_userDetails['lastname'] = $last_name;
                $arr_userDetails['fbid'] = $facebook['id'];
                $arr_userDetails['date_of_birth'] = date('Y-d-m', strtotime(str_replace('/', '-', $facebook['birthday'])));
                $arr_userDetails['editable'] = 1;
                $arr_userDetails['referral_code'] = $referralCode;
                $last_id = $this->user_model->insertsignup($arr_userDetails);

                $arr_userDetails1['businessId'] = $last_id;
                $this->db->where('user_Id', $last_id);
                $this->db->update("users", $arr_userDetails1);

                $business_arr = array(
                    'businessId' => $last_id,
                    'businessName' => "",
                    'country' => ""
                );

                $this->user_model->saveBusinessId($business_arr);

                $brand_arr = array(
                    'user_id' => $last_id,
                    'businessId' => $last_id,
                    'totalIosApps' => 1,
                    'totalAndroidApps' => 1,
                    'totalCampaigns' => 1,
                    'totalAppGroup' => 1,
                    'androidCampaign' => 1,
                    'iOSCampaign' => 1,
                    'emailCampaign' => 5,
                    'createdDate' => date('YmdHis')
                );

                $this->brand_model->savePackage($brand_arr);

                //insert the record in email_signup table for sending email after signup through cron
                $data = array('user_id' => $last_id, "sent" => 0, 'modifiedDate' => date('YmdHis'));
                $this->db->insert('email_signup', $data);
                //end insert code of email_signup
                // send data to hubspot
                $portal = HUBPORTALID;
                $status = $this->hubspotAuthenticaion($portal);
                if ($status == 302) {
                    $responce_code = $this->savecontactToHubspot($facebook['email'], $first_name, $last_name);
                    if ($responce_code != 200) {
                        $responcecode = $this->savecontactToHubspot($facebook['email'], $first_name, $last_name);
                        if ($responcecode != 200) {
                            $responcecode = $this->savecontactToHubspot($facebook['email'], $first_name, $last_name);
                        }
                    }
                }
                /* end hubspot data */

                $userDetails = $this->user_model->getUser($last_id);
                $this->session->set_userdata('logged_in', $userDetails);

                $_SESSION['hurree-business'] = 'access';

                redirect('appUser');
                exit();
            }
        } else {
            if (isset($_GET['error'])) {
                redirect(base_url());
            } else {
                $data['login_url'] = $this->facebook->getLoginUrl(array(
                    'redirect_uri' => site_url('social/fblogin'),
                    'scope' => array("email") // permissions here
                ));
                redirect($data['login_url']);
            }
        }
    }

    public function googlelogin($flag = false, $type = false) {
        if ($type == "login") {
            $this->session->set_userdata("googlelogin", 1);
        } elseif ($type == "signup") {
            $this->session->set_userdata("googlesignup", 1);
        }
        $client_id = google_auth_client_id;
        $client_secret = google_auth_client_secret;
        $redirect_uri = base_url() . 'social/googlelogin';
        if (isset($flag) && $flag == 'flag') {
            $url = "https://accounts.google.com/o/oauth2/auth?client_id=$client_id&redirect_uri=$redirect_uri&scope=https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email&response_type=code&access_type=offline";
            redirect($url);
        } else if (isset($_GET["code"])) {
            $auth_code = $_GET["code"];
            $fields = array(
                'code' => urlencode($auth_code),
                'client_id' => urlencode($client_id),
                'client_secret' => urlencode($client_secret),
                'redirect_uri' => urlencode($redirect_uri),
                'grant_type' => urlencode('authorization_code')
            );
            $post = '';
            foreach ($fields as $key => $value) {
                $post .= $key . '=' . $value . '&';
            }
            $post = rtrim($post, '&');
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
            curl_setopt($curl, CURLOPT_POST, 5);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $result = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($result);
            //print_r($response);

            $accesstoken = $response->access_token;
            if (!empty($accesstoken)) {
                $user_response = file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=$accesstoken");
                $google = json_decode($user_response);
                //print_r($google); exit;
                $google_user_Id = $google->id;
            }
            $email = '';
            if (isset($google->email)) {
                $email = $google->email;
            }
            $arr_user['socialId'] = $google_user_Id;

            $oneuser = $this->user_model->getOneUserDetails($arr_user, "*");

            if (count($oneuser) > 0) {
                $userDetails = $this->user_model->getUser($oneuser->user_Id);
                // print_r($userDetails); exit;
                if (isset($userDetails->active) && $userDetails->active == 1) {
                    $this->session->set_userdata("logged_in", $userDetails);
                    $_SESSION['hurree-business'] = 'access';
                    redirect('appUser');
                    exit();
                } else {
                    $this->session->set_userdata("account_inactive", "Your Account is not active. Please Contact to Administrator.");
                    redirect(base_url());
                    exit();
                }
            } else {

                $arr['email'] = $email;
                $arr['active'] = 1;
                $arr['isDelete'] = 0;
                $userRow = $this->user_model->getUserEmailExist($arr, "*");
                if (count($userRow) > 0) {
                    if ($userRow->loginSource == "Facebook") {
                        $this->session->set_flashdata("email_id_exist", "You are already signup with facebook.");
                        redirect(base_url());
                        exit();
                    } else {
                        $this->session->set_flashdata("email_id_exist", "This email is already exists. Please try another account.");
                        redirect(base_url());
                        exit();
                    }
                }
                //echo 'New User';
                /* $last_insert_user_id = $this->user_model->getMaxUserRow();
                  $last_insert_id = rand();
                  if(isset($last_insert_user_id->user_Id)){
                  $last_insert_id = $last_insert_user_id->user_Id + 1;
                  } */
                $username = str_replace(' ', '', $google->name);
                $username = lcfirst($username);
                //$variUsername='';
                $generatePass = RandomStringGenerate();

                $variUsername = checkusernameExist($username);

                $first_name = '';
                $last_name = '';
                if (isset($google->name)) {
                    $name = explode(' ', $google->name);
                    $first_name = $name[0];
                    $last_name = $name[1];
                }

                //$variUsername==''?$variUsername=$username:$variUsername=$variUsername.mktime();   //// Line to be deteted after correct code.
                generateReferalCode:
                //generate referal code
                $referralCode = $this->generateReferralCode();
                //check referal code in database
                $referralCount = $this->user_model->checkReferralCodeExist($referralCode);
                if ($referralCount > 0) {
                    goto generateReferalCode;
                }

                $arr_userDetails['user_Id'] = '';
                $arr_userDetails['email'] = $email;
                $arr_userDetails['businessId'] = 0;
                $arr_userDetails['accountType'] = 'trail';
                $arr_userDetails['username'] = $variUsername;
                $arr_userDetails['createdDate'] = date('YmdHis');
                $arr_userDetails['active'] = 1;
                $arr_userDetails['loginSource'] = 'google';
                $arr_userDetails['image'] = 'user.png';
                $arr_userDetails['header_image'] = 'profileBG.jpg';
                $arr_userDetails['usertype'] = 8;
                $arr_userDetails['password'] = md5($generatePass);
                $arr_userDetails['firstname'] = $first_name;
                $arr_userDetails['lastname'] = $last_name;
                $arr_userDetails['socialId'] = $google_user_Id;
                $arr_userDetails['date_of_birth'] = date('Y-d-m', strtotime(str_replace('/', '-', $google->birthday)));
                $arr_userDetails['editable'] = 1;
                $arr_userDetails['referral_code'] = $referralCode;
                
                $last_id = $this->user_model->insertsignup($arr_userDetails);

                $arr_userDetails1['businessId'] = $last_id;
                $this->db->where('user_Id', $last_id);
                $this->db->update("users", $arr_userDetails1);

                $business_arr = array(
                    'businessId' => $last_insert_id,
                    'businessName' => "",
                    'country' => ""
                );

                $this->user_model->saveBusinessId($business_arr);

                $brand_arr = array(
                    'user_id' => $last_id,
                    'businessId' => $last_id,
                    'totalIosApps' => 1,
                    'totalAndroidApps' => 1,
                    'totalCampaigns' => 1,
                    'totalAppGroup' => 1,
                    'androidCampaign' => 1,
                    'iOSCampaign' => 1,
                    'emailCampaign' => 5,
                    'createdDate' => date('YmdHis')
                );

                $this->brand_model->savePackage($brand_arr);

                //insert the record in email_signup table for sending email after signup through cron
                $data = array('user_id' => $last_id, "sent" => 0, 'modifiedDate' => date('YmdHis'));
                $this->db->insert('email_signup', $data);
                //end insert code of email_signup
                // send data to hubspot
                $portal = HUBPORTALID;
                $status = $this->hubspotAuthenticaion($portal);
                if ($status == 302) {
                    $responce_code = $this->savecontactToHubspot($email, $first_name, $last_name);
                    if ($responce_code != 200) {
                        $responcecode = $this->savecontactToHubspot($email, $first_name, $last_name);
                        if ($responcecode != 200) {
                            $responcecode = $this->savecontactToHubspot($email, $first_name, $last_name);
                        }
                    }
                }
                /* end hubspot data */

                $userDetails = $this->user_model->getUser($last_id);
                $this->session->set_userdata('logged_in', $userDetails);

                $_SESSION['hurree-business'] = 'access';

                redirect('appUser');
                exit();
            }
        } else {
            redirect(base_url());
        }
    }

    public function testHubspot() {
        $email = "sarvesh1@qsstechnosoft.com";
        $first_name = "Sarveshp09";
        $last_name = "Tiwari";
        // send data to hubspot
        $portal = HUBPORTALID;
        $status = $this->hubspotAuthenticaion($portal);
        if ($status == 302) {
            $responce_code = $this->savecontactToHubspot($email, $first_name, $last_name);
            if ($responce_code != 200) {
                $responcecode = $this->savecontactToHubspot($email, $first_name, $last_name);
                if ($responcecode != 200) {
                    $responcecode = $this->savecontactToHubspot($email, $first_name, $last_name);
                }
            }
        }
        /* end hubspot data */
    }

    function hubspotAuthenticaion($portalId) {
        $endpoint = 'https://app.hubspot.com/auth/authenticate?client_id=' . HUBCLIENTID . '&portalId=' . $portalId . '&redirect_uri=' . base_url() . 'home/hubspotAuthenticaion&scope=offline';

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

    function savecontactToHubspot($email, $firstname, $lastname) {

        $arr = array(
            'properties' => array(
                array(
                    'property' => 'email',
                    'value' => $email
                ),
                array(
                    'property' => 'firstname',
                    'value' => $firstname
                ),
                array(
                    'property' => 'lastname',
                    'value' => $lastname
                ),
                array(
                    'property' => 'lifecyclestage',
                    'value' => 'salesqualifiedlead'
                )
            )
        );

        $post_json = json_encode($arr);
        //echo $post_json; die;
        //$hapikey = HUBAPIKEY;
        //$endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch?hapikey=' . $hapikey;
        $endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . HAPIKEY;
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
        //echo "curl Errors: " . $curl_errors;
        //echo "\nStatus code: " . $status_code;
        //  			echo "\nResponse: " . $response;


        return $status_code;
    }

    public function logout() {
        $this->load->library('facebook');
        // Logs off session from website
        $this->facebook->destroySession();
        // Make sure you destory website session as well.
        redirect('home');
    }

    function twlogin() {
        if (isset($_REQUEST['oauth_token'])) {
            require_once APPPATH . "/third_party/twitter/getTwitterData.php";
        } else {
            require_once APPPATH . "/third_party/twitter/login-twitter.php";
        }
    }

    function fbPageConnect() {
        $this->load->library('facebook'); // Automatically picks appId and secret from config
        $data['user_profile'] = array();
        $login = $this->administrator_model->front_login_session();

        $user = $this->facebook->getUser();
        if ($user) {
            $facebook = $this->facebook->api('/me?fields=name,first_name,last_name,email,birthday,education,gender,id');
            $access_token = $this->facebook->getAccessToken(); //echo "caleld"; exit;
            //print_r($_POST);  print_r($_REQUEST);   print_r($_GET); exit;
            if (isset($_GET['code'])) {
                $userid = $login->user_id;
                $userRow = $this->social_model->isSocialAccountExist($userid, "facebook");
                $date = date('Y-m-d H:i:s');
                $source_id = NULL;
                if (count($userRow) != 0) {
                    $source_id = $userRow[0]->source_id;
                }
                $data = array(
                    'emails' => NULL,
                    'active' => '0',
                    'isDelete' => '0',
                    'createdDate' => $date,
                    'modifiedDate' => $date,
                    'user_id' => $userid,
                    'source' => 'facebook',
                    'source_id' => $source_id,
                    'access_token' => $access_token,
                    'refresh_token' => NULL,
                    'oauth_token_secret' => NULL,
                    'token_type' => NULL,
                    'expires_in' => NULL
                );

                if (count($userRow) == 0) {
                    $this->social_model->save($data);
                } else {
                    $this->social_model->update($userid, "facebook", $data);
                }
                $cookie = array(
                    'name' => 'social_fb_token',
                    'value' => $access_token,
                    'expire' => time() + 3600
                );
                $this->input->set_cookie($cookie);
            }
        }
        if ($login->usertype == 8 || $login->usertype == 9) {
            redirect('appUser/crmPage');
        } else {
            redirect('businessUser/crmPage');
        }
    }

    function fbConnect() {
        $this->load->library('facebook'); // Automatically picks appId and secret from config

        $data['login_url'] = $this->facebook->getLoginUrl(array(
            'redirect_uri' => site_url('social/fbPageConnect'),
            'scope' => array('email', 'user_about_me', 'publish_actions', 'manage_pages', 'pages_show_list') // permissions here
        ));
        redirect($data['login_url']);
    }

    public function fbpages() {
        $this->load->helper('cookie');
        $access_token = $this->input->cookie('social_fb_token', true);
        $data['pages'] = array();
        if (!empty($access_token)) {
            $graph_url_pages = "https://graph.facebook.com/me/accounts?access_token=" . $access_token;
            $data['pages'] = json_decode(file_get_contents($graph_url_pages));
        }
        delete_cookie('social_fb_token'); // get all pages information from above url.
        $this->load->view('facebook_connect_pages', $data);
    }

    public function header_req($url) {
        $channel = curl_init();
        curl_setopt($channel, CURLOPT_URL, $url);
        curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($channel, CURLOPT_TIMEOUT, 10);
        curl_setopt($channel, CURLOPT_HEADER, true);
        curl_setopt($channel, CURLOPT_NOBODY, true);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($channel, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201');
        curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($channel, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($channel, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_exec($channel);
        $httpCode = curl_getinfo($channel, CURLINFO_HTTP_CODE);
        curl_close($channel);
        return $httpCode;
    }

    public function userPostFbPage() {
        $fbpage = $this->input->post('fbpage');
        $fbpageUrl = $this->input->post('fbpageUrl');
        $flag = '';
        if (!empty($fbpageUrl)) {
            // 1 add http if not found in URL
            if (stripos($fbpageUrl, "http") !== 0)
                $fbpageUrl = "http://" . $fbpageUrl;

            // 2 get facebook.com from URL
            $host = parse_url($fbpageUrl, PHP_URL_HOST);

            // 3 if host is indeed facebook.com then continue
            if (stripos($host, "facebook.com")) {
                $response = $this->header_req($fbpageUrl);

                if ($response === 200 || $response === 302) {
                    $fbpageUrl = explode('/', $fbpageUrl);
                    $fbpage = end($fbpageUrl);
                    $login = $this->administrator_model->front_login_session();
                    $userid = $login->user_id;
                    $this->load->model('social_model');
                    $userRow = $this->social_model->isSocialAccountExist($userid, "facebook");
                    $date = date('Y-m-d H:i:s');

                    $appId = $this->config->item('appId');

                    $this->load->library('facebook'); // Automatically picks appId and secret from config
                    $data['user_profile'] = array();

                    $data = array(
                        'modifiedDate' => $date,
                        'user_id' => $userid,
                        'source' => 'facebook',
                        'source_id' => $fbpage,
                        'active' => '1'
                    );

                    if (count($userRow) == 0) {
                        $this->social_model->save($data);
                    } else {
                        $this->social_model->update($userid, "facebook", $data);
                    }
                    $flag = "success";
                } else {
                    $flag = "Page Not Found";
                }
            } else {
                $flag = "Invalid facebook page Url";
            }
        } else {
            $login = $this->administrator_model->front_login_session();
            $userid = $login->user_id;
            $this->load->model('social_model');
            $userRow = $this->social_model->isSocialAccountExist($userid, "facebook");
            $date = date('Y-m-d H:i:s');

            $data = array(
                'modifiedDate' => $date,
                'user_id' => $userid,
                'source' => 'facebook',
                'source_id' => $fbpage,
                'active' => '1'
            );

            if (count($userRow) == 0) {
                $this->social_model->save($data);
            } else {
                $this->social_model->update($userid, "facebook", $data);
            }
            $flag = "success";
        }
        echo $flag;
        exit;
    }

    public function twConnect() {
        require_once APPPATH . "/third_party/twitter/twitteroauth.php";
        $login = $this->administrator_model->front_login_session();

        if (isset($_GET['oauth_token'])) {
            require_once APPPATH . "/third_party/twitter/twitter-autopost.php";
            $oauth_token = $this->session->userdata("oauth_token");
            $oauth_token_secret = $this->session->userdata("oauth_token_secret");
            $oauth_verifier = $_GET['oauth_verifier'];

            $Twitauth = new Twitauth(Tw_CONSUMER_KEY, Tw_CONSUMER_SECRET);
            $arr = array('oauth_token' => $oauth_token, 'oauth_token_secret' => $oauth_token_secret, 'oauth_verifier' => $oauth_verifier);
            $result = $Twitauth->getAutoPostToken($arr);

            $userid = $login->user_id;
            $userRow = $this->social_model->isSocialAccountExist($userid, "twitter");
            $date = date('Y-m-d H:i:s');
            $source_id = NULL;
            if (count($userRow) != 0) {
                $source_id = $userRow[0]->source_id;
            }
            $data = array(
                'emails' => NULL,
                'active' => '1',
                'isDelete' => '0',
                'createdDate' => $date,
                'modifiedDate' => $date,
                'user_id' => $userid,
                'source' => 'twitter',
                'source_id' => $result['user_id'],
                'access_token' => $result['oauth_token'],
                'refresh_token' => $oauth_verifier,
                'oauth_token_secret' => $result['oauth_token_secret'],
                'token_type' => NULL,
                'expires_in' => NULL
            );

            if (count($userRow) == 0) {
                $this->social_model->save($data);
            } else {
                $this->social_model->update($userid, "twitter", $data);
            }

            if ($login->usertype == 8 || $login->usertype == 9) {
                redirect('appUser/crmPage');
            } else {
                redirect('businessUser/crmPage');
            }
        } else {

            $twitteroauth = new TwitterOAuth(Tw_CONSUMER_KEY, Tw_CONSUMER_SECRET);
            $request_token = $twitteroauth->getRequestToken();

            $twitterAuthorizeURL = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);

            $this->session->set_userdata(['oauth_token' => $request_token['oauth_token']]);
            $this->session->set_userdata(['oauth_token_secret' => $request_token['oauth_token_secret']]);
            //echo '<pre>'; print_r($token); print_r($tumblr); exit;
            redirect($twitterAuthorizeURL);
        }
    }

    public function inConnect() {
        require_once APPPATH . "/third_party/instagram/instagram.class.php";
        $login = $this->administrator_model->front_login_session();

        //$instagramInfo   = Yii::$app->params['instagram'];
        $instagram = new Instagram(array(
            'apiKey' => instagram_client_id,
            'apiSecret' => instagram_client_secret,
            'apiCallback' => base_url() . 'social/inConnect'
        ));
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            $in_data = $instagram->getOAuthToken($code);
            $url = 'https://api.instagram.com/v1/users/' . $in_data->user->id . '?access_token=' . $in_data->access_token;
            //echo $url; exit;
            $api_response = file_get_contents($url);
            $record = json_decode($api_response);
            $userid = $login->user_id;
            $userRow = $this->social_model->isSocialAccountExist($userid, "instagram");
            $date = date('Y-m-d H:i:s');
            $source_id = $in_data->user->id;
            if (count($userRow) != 0) {
                $source_id = $userRow[0]->source_id;
            }

            $data = array(
                'emails' => NULL,
                'active' => '1',
                'isDelete' => '0',
                'createdDate' => $date,
                'modifiedDate' => $date,
                'user_id' => $userid,
                'source' => 'instagram',
                'source_id' => $source_id,
                'access_token' => $in_data->access_token,
                'refresh_token' => $code,
                'oauth_token_secret' => NULL,
                'token_type' => NULL,
                'expires_in' => NULL
            );

            if (count($userRow) == 0) {
                $this->social_model->save($data);
            } else {
                $this->social_model->update($userid, "instagram", $data);
            }

            if ($login->usertype == 8 || $login->usertype == 9) {
                redirect('appUser/crmPage');
            } else {
                redirect('businessUser/crmPage');
            }
        } else {
            $inloginUrl = $instagram->getLoginUrl();
            redirect($inloginUrl);
        }
    }

    public function tblrConnect() {
        require_once APPPATH . "/third_party/tumblr/lib/tumblrPHP.php";

        // Create a new instance of the Tumblr Class with your Conumser and Secret when you create your app.
        $tumblr = new Tumblr(tumblr_consumer_key, tumblr_secret_key);

        // Get the request tokens based on your consumer and secret and store them in $token
        $token = $tumblr->getRequestToken();

        $tumblrAuthorizeURL = $tumblr->getAuthorizeURL($token['oauth_token']);

        $this->session->set_userdata(['oauth_token' => $token['oauth_token']]);
        $this->session->set_userdata(['oauth_token_secret' => $token['oauth_token_secret']]);
        //echo '<pre>'; print_r($token);	print_r($tumblr); exit;
        redirect($tumblrAuthorizeURL);
    }

    public function tblrUpdateToken() {
        require_once APPPATH . "/third_party/tumblr/lib/tumblrPHP.php";
        $login = $this->administrator_model->front_login_session();

        if (isset($_GET['oauth_token'])) {
            $tumblr_token = $this->session->userdata("oauth_token");
            $tumblr_token_secret = $this->session->userdata("oauth_token_secret");

            $tumblr = new Tumblr(tumblr_consumer_key, tumblr_secret_key, $tumblr_token, $tumblr_token_secret);

            $tumblrToken = $tumblr->getAccessToken($_GET['oauth_verifier']);
            $tumblr_api_key = tumblr_api_key;
            $usersJsonFromTumblr = $tumblr->oauth_get("/user/info?api-key=$tumblr_api_key");
            //echo '<pre>'; print_r($tumblrToken); print_r($usersJsonFromTumblr); exit;
            $userid = $login->user_id;
            $userRow = $this->social_model->isSocialAccountExist($userid, "tumblr");

            $date = date('Y-m-d H:i:s');
            $source_id = NULL;
            $data = array(
                'emails' => NULL,
                'active' => '1',
                'isDelete' => '0',
                'createdDate' => $date,
                'modifiedDate' => $date,
                'user_id' => $userid,
                'source' => 'tumblr',
                'source_id' => $usersJsonFromTumblr->response->user->blogs[0]->uuid,
                'access_token' => $tumblrToken['oauth_token'],
                'refresh_token' => NULL,
                'oauth_token_secret' => $tumblrToken['oauth_token_secret'],
                'token_type' => NULL,
                'expires_in' => NULL
            );

            if (count($userRow) == 0) {
                $this->social_model->save($data);
            } else {
                $this->social_model->update($userid, "tumblr", $data);
            }
        }
        if ($login->usertype == 8 || $login->usertype == 9) {
            redirect('appUser/crmPage');
        } else {
            redirect('businessUser/crmPage');
        }
    }

    function confirmDisconnect($source) {
        $data['source'] = $source;
        $this->load->view('disconnectFromSocial', $data);
    }

    // diconnect user from social

    function disconnect() {
        $userid = $_POST['userid'];
        $social_type = $_POST['social_type'];
        $arr['user_id'] = $userid;
        $arr['source'] = $social_type;

        $result = $this->social_model->disconnect($arr);
        echo $result;
    }

    function generateReferralCode() {
        $this->load->library('couponcode');
        $code = new CouponCode();
        $result = $code->generate();
        if ($code->validate($result)) {
            return $code->normalize($result);
        } else {
            $this->generateReferralCode();
        }
    }

}
