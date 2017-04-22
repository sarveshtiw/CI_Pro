<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once('vendor/autoload.php');

use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

class Home extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('credit_card_helper', 'time', 'hurree'));

        $this->load->library(array('form_validation', 'facebook', 'email', 'image_lib', 'user_agent', 'Mobile_Detect', 'session'));
        $this->load->model(array('user_model', 'email_model', 'country_model', 'payment_model', 'promocode_model', 'administrator_model', 'pushmessage_model', 'message_model', 'games_model', 'score_model', 'challenge_model', 'store_model', 'notification_model', 'status_model', 'qrcode_model', 'subscription_model', 'beacon_model', 'targetoffer_model', 'campaign_model', 'package_model', 'brand_model', 'referfriend_model'));
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    function loginRedirect($username, $password) {
        //// CHECK FOR USERNAME IN DATABASE
        $session_details = $this->user_model->check_username($username, $password);

        if (count($session_details) > 0) {
            //// IF USERNAME IS ALREADY REGISTERED,  WILL GO FOR CHECK PASSWORD
            if ($session_details->usertype != 3) {
                $this->session->set_userdata('logged_in', $session_details);
                $sess = $this->session->userdata('logged_in');
                redirect('businessUser/account');
            } else {
                $this->session->set_flashdata("error_message", "Invalid Username/Password");
                redirect("home/signup");
            }
        }
    }

    function checkusername($username) {
        $arr_user = array();

        $arr_user['username'] = $username;
        $arr_user['active'] = 1;

        $oneuser = $this->user_model->getOneUserDetails($arr_user, "*");
        if (count($oneuser) > 0) {
            $rand = $this->RandomString();
            //$newusername= $username.$rand;
            $newusername = $username . mktime();
            $oneuser = $this->checkusername($newusername);
            /* $username=$oneuser->username; */
            $username = $newusername;
            return $username;
        } else {
            return $arr_user['username'];
        }
    }

    function checkChallenger() {
        $arr_user = array();
        $arr_user['username'] = $_POST['username'];
        $arr_user['active'] = 1;
        $oneuser = $this->user_model->getOneUserDetails($arr_user, "*");
        if (count($oneuser) > 0) {
            if ($oneuser->usertype == 1 || $oneuser->usertype == 4) {
                echo '1';
            } else {
                echo '2';
            }
        } else {
            echo '0';
        }
    }

    function RandomString() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    function RandomStringForgotPassword() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function subheader($front = NULL, $brnh = NULL, $uri = NULL) {
        $subheader = '';       //// Initilizing Variable
        $detect = new Mobile_Detect();
        $brnh ? $branch = '/' . $brnh : $branch = '';
        if ($front == 'front') {
            $uri == '' ? $home = 'class ="activeclass"' : $home = '';
            $uri == 'signup' ? $signup = 'class ="activeclass"' : $signup = '';
            $uri == 'ambassador' ? $ambassador = 'class ="activeclass"' : $ambassador = '';

            $subheader = '<nav><ul>
			<li><a href="' . base_url() . '"' . $home . '>Home</a></li>
			<li><a href="' . base_url() . 'home/consumerSignUp" ' . $signup . '>Sign Up</a></li>
			<li><a href="' . base_url() . 'home/ambassador"' . $ambassador . '>Ambassadors</a></li>
			</ul></nav>
			';
        } else if ($front == 'signup') {
            $uri == 'package' ? $pk_select = 'class ="activeclass"' : $pk_select = '';
            $uri == 'businessLocation' ? $buss_select = 'class ="activeclass"' : $buss_select = '';
            $uri == 'information' ? $in_select = 'class ="activeclass"' : $in_select = '';
            //$uri=='confirmation'? $cu_select='class ="activeclass"':$cu_select='';
            $uri == 'payment' ? $py_select = 'class ="activeclass"' : $py_select = '';

            $subheader = '<nav class="business"><ul>
			<li><a href="' . base_url() . 'index.php/home/package" ' . $pk_select . '>Choose Packages</a></li>
			<li><a href="' . base_url() . 'index.php/home/businessLocation' . $branch . '"' . $buss_select . '>Business Location</a></li>
			<li><a href="' . base_url() . 'index.php/home/information' . $branch . '" ' . $in_select . '>Basic Information</a></li>
			<li><a href="' . base_url() . 'index.php/home/payment' . $branch . '" ' . $py_select . '>Payment</a></li>
			</ul></nav>';
        }

        return $subheader;
    }

    function signup() {
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
            redirect('businessUser/account');
        } else {
            $this->session->unset_userdata('user_logged_in');
            redirect(base_url());
        }
        /*
          //$this->load->view('front_header_new');
          $data['business_category'] = $this->user_model->getCategory();

          $data['countries'] = $this->country_model->get_countries();

          $data['business'] = 0;
          $data['uri'] = $this->uri->segment(2);

          $arr_card_type['active'] = 1;
          $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);

          $referral_name = $this->uri->segment(3);
          if ($referral_name != '') {
          $details = $this->user_model->referral_check($referral_name);
          $data['ambassador_id'] = $details->user_Id;
          } else {
          $data['ambassador_id'] = '';
          }
          $data['package'] = $this->package_model->getPackage();
          $this->load->view('signup', $data);
          $this->load->view('front_footer_new',$data); */
    }

    function signup_prev() {
        redirect(base_url());

        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == false && $header['login']->accesslevel == '' && $header['login']->active == 0) {
            $this->session->unset_userdata('business_branch');
            $this->session->unset_userdata('business_userdata');

            if ($this->input->post('usertype') == 1) {
                $this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]');
                $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
            } else {
                $this->form_validation->set_rules('bu_name', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('bu_email', 'Email Address', 'trim|required|valid_email|is_unique[users.email]');

                $this->form_validation->set_rules('bu_username', 'Username', 'trim|required|is_unique[users.username]');
                $this->form_validation->set_rules('bu_password', 'Password', 'trim|required|min_length[4]|max_length[32]');
            }
            $data = '';
            $data['imageHref'] = '';
            if ($this->form_validation->run() == FALSE) {
                $data['uri'] = $this->uri->segment(2);

                $data['subheader'] = $this->subheader('front', '', $data['uri']);
                $businessSignup = $this->session->userdata('business_signup');

                $data['ambassador_id'] = '';

                $detect = new Mobile_Detect();
                if ($detect->isAndroidOS()) {
                    $data['imageHref'] = android_play_store_url;
                    ;
                }
                if ($detect->isiOS()) {

                    $data['imageHref'] = itunes_app_store_url;
                }

                if ($businessSignup == 'business') {
                    $this->load->view('signup1', $data);
                } else {
                    $data['business'] = 0;

                    $this->load->view('front_header', $data);
                    $this->load->view('index', $data);
                    $this->load->view('front_footer', $data);
                }
            } else {

                if ($this->input->post('usertype') == 1) {
                    $username = $this->input->post('username');
                    $email = $this->input->post('email');
                    $businessName = '';
                    $firstname = $this->input->post('firstname');
                    $lastname = $this->input->post('lastname');
                    $password = $this->input->post('password');

                    $gender = $this->input->post('gender');
                    $date = DateTime::createFromFormat('d/m/Y', $this->input->post('dob'));

                    $date_of_birth = $date->format('Y-m-d');
                    ;
                    $active = 1;
                    $package = 0;
                } else {
                    $username = $this->input->post('bu_username');
                    $businessName = $this->input->post('bu_businessname');
                    $email = $this->input->post('bu_email');
                    $name = $this->input->post('bu_name');
                    $password = $this->input->post('bu_password');
                    $date = '';
                    $month = '';
                    $year = '';
                    $gender = '';
                    $date_of_birth = '';
                    $active = 0;
                    $package = 0;
                }

                $usertype = $this->input->post('usertype');
                if ($usertype == 1) {
                    $save['firstname'] = $firstname;
                    $save['lastname'] = $lastname;
                } else {
                    $arr_name = explode(' ', $name);
                    //// GET NAME
                    $save['firstname'] = $arr_name[0];
                    $save['lastname'] = '';
                    if (count($arr_name) > 1) {
                        $i = 0;
                        $sur_name = '';
                        foreach ($arr_name as $surname) {
                            if ($i != 0) {
                                $sur_name = $sur_name . ' ' . $surname;
                            }
                            $i++;
                        }
                        $save['lastname'] = $sur_name;
                    }
                }
                //// Create Array to Save Data into Database
                $date = date('YmdHis');
                $save['user_Id'] = '';
                $save['email'] = $email;
                $save['username'] = $username;
                $save['businessName'] = $businessName;
                $save['password'] = md5($password);
                $save['active'] = $active;
                $save['usertype'] = $usertype;
                $save['image'] = 'user.png';
                $save['header_image'] = 'profileBG.jpg';
                $save['loginSource'] = 'normal';
                $save['createdDate'] = $date;
                $save['firstLogin'] = $date;
                $save['package'] = $package;
                $save['date_of_birth'] = $date_of_birth;
                $save['gender'] = $gender;

                if ($this->input->post('usertype') == 1) {
                    $inserid = $this->user_model->insertsignup($save);

                    $coins = array(
                        'userid' => $inserid,
                        'coins' => '100'
                    );
                    $this->score_model->signupCoins($coins);

                    $userCoins = array(
                        'userid' => $inserid,
                        'coins' => 100,
                        'coins_type' => 8,
                        'game_id' => 0,
                        'businessid' => 0,
                        'actionType' => 'add',
                        'createdDate' => date('YmdHis')
                    );
                    $this->score_model->insertCoins($userCoins);

                    //// Create Array for Login Session
                    $session = array();
                    $session = (object) array(
                                'user_Id' => $inserid,
                                'username' => $username,
                                'password' => md5($password),
                                'email' => $email,
                                'active' => $active,
                                'usertype' => $usertype,
                                'firstname' => $save['firstname'],
                                'lastname' => $save['lastname'],
                                'image' => '',
                                'accesslevel' => 'consumer'
                    );

                    $this->session->set_userdata('logged_in', $session);    //// Create Login Session
                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// GET EMAIL FROM DATABASE

                    $email_template = $this->email_model->getoneemail('consumer_signup');

                    //// MESSAGE OF EMAIL
                    $messages = $email_template->message;

                    $hurree_image = base_url() . 'assets/template/frontend/img/consumer_signup.png';
                    $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                    $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                    //// replace strings from message
                    $messages = str_replace('{Username}', ucfirst($username), $messages);
                    $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                    //$messages = str_replace('{App_Store_Image}', $appstore, $messages);
                    //$messages = str_replace('{Google_Image}', $googleplay, $messages);
                    //// FROM EMAIL
                    $this->email->from('hello@marketmyapp.co', 'Hurree');
                    $this->email->to($email);
                    $this->email->subject($email_template->subject);
                    $this->email->message($messages);
                    $this->email->send();    ////  EMAIL SEND

                    redirect('businessUser/account');
                } else {

                    $this->session->set_userdata('business_userdata', $save);
                    redirect('home/package/');
                }
            }
        } else {
            if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
                redirect('businessUser/account');
            } else {
                $this->session->unset_userdata('user_logged_in');
                redirect('home/index');
            }
        }
    }

    function businessRegistration() {
        $firstname = $this->input->post('firstname');
        $lastname = $this->input->post('lastname');
        $email = $this->input->post('email');
        $businessName = $this->input->post('businessName');
        $ambassadorId = $this->input->post('ambassador_id');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if ($ambassadorId == 'undefined') {
            $ambassadorId = 0;
        }
        $business = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'businessName' => $businessName,
            'ambassadorId' => $ambassadorId,
            'email' => $email,
            'username' => $username,
            'password' => $password
        );
        $this->session->set_userdata('business', $business);
        $package = $this->input->post('package');
        $userdata = $this->session->userdata('business');
        $userdata['package'] = $package;
        $this->session->set_userdata('business', $userdata);
        return;
    }

    function businessbranchlocation() {
        //$this->load->library('MY_Session');
        $count = 0;
        $old[] = '';

        //$array = $this->session->userdata('branch');
        if ($this->session->userdata('branch')) {
            $this->session->unset_userdata('branch');
        }

        $email = $this->input->post('email');
        $mobile = $this->input->post('mobile');
        $business_category = $this->input->post('business_category');
        $country = $this->input->post('country');
        $address = $this->input->post('address1');
        $address2 = $this->input->post('address2');
        $town = $this->input->post('town');
        $postcode = $this->input->post('postcode');

        $geoCodeAddress = $address . ',' . $address2 . ',' . $town;
        $location = $this->GetGeoCode($geoCodeAddress);
        if (isset($location) && $location != "") {
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];
        } else {
            $latitude = "";
            $longitude = "";
        }

        $branch = array(
            'email' => $email,
            'phone' => $mobile,
            'businessCategory' => $business_category,
            'country' => $country,
            'address' => $address,
            'address2' => $address2,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'town' => $town,
            'postcode' => $postcode,
            'main_branch' => 1
        );

        // $all_brnch = $old;
        $all_brnch[0] = $branch;
        //$this->session->set_userdata('branch', $all_brnch);

        $this->session->set_userdata('branch', $all_brnch);
        //$arr = $this->session->userdata('branch');
        return;
    }

    function businessbranchinformation() {

        $website = $this->input->post('website');
        $visitors = $this->input->post('visitors');
        $description = $this->input->post('description');

        $business = $this->session->userdata('business');

        $userdata = $this->session->userdata('branch');
        $count = count($userdata) - 1;

        $userdata[$count]['website'] = $website;
        $userdata[$count]['visitors'] = $visitors;
        $userdata[$count]['description'] = $description;

        $this->session->set_userdata('branch', $userdata);

        //Payment display Start
        $branch = $this->session->userdata('branch');
        $no_of_branch = count($branch);

        $arr_charge['active'] = 1;
        $arr_charge['name'] = 'Branch';
        $charges = $this->user_model->getcharges($arr_charge, $row = '');
        //// Get Branch Charges
        $arr_pachage['package_id'] = $business['package'];
        $packageDetails = $this->subscription_model->getpackageDetails('*', $arr_pachage, $row = '');

        $country_id = $userdata[0]['country'];  //// Get Country of Main Business
        $country = $this->country_model->getcountry('country_id', $country_id, '1');

        if ($country->country_name == 'UK') {
            $branchCost = $packageDetails->price_gbp;
            $currencySymbol = '£';
            $vat = $charges->vat;
        } else {
            $branchCost = $packageDetails->price_usd;
            $currencySymbol = '$';
            $vat = 0.00;
        }

        $discount = $charges->discount;

        $cost_total_branch = $branchCost * $no_of_branch;

        if ($no_of_branch >= 2) {
            $discount_all_branch = ($cost_total_branch * $discount) / 100;

            $final_branch_cost = $cost_total_branch - $discount_all_branch;

            $total_amount = $final_branch_cost;
        } else {
            $final_branch_cost = $cost_total_branch;
            $total_amount = $cost_total_branch;
            $discount_all_branch = 0;
        }

        $data['vatCharge'] = 0;

        if (isset($vat)) {
            if ($vat != 0) {
                //$vatCharge=($branchCost*$vat)/100;
                $vatCharge = ($total_amount * $vat) / 100;   // VAT charge on total amount
                $data['vatCharge'] = number_format($vatCharge, 2);
                $total_amount = $total_amount + $data['vatCharge'];
            }
        }
        $data = array(
            'branches' => $no_of_branch,
            'branchesCost' => number_format($final_branch_cost, 2),
            'Currency' => $currencySymbol,
            'VAT' => $data['vatCharge'],
            'total' => number_format($total_amount, 2),
            'discount' => $discount_all_branch
        );
        echo json_encode($data);
    }

    public function businessAddLocation() {
        $this->load->model('location_model');
        $package_id = $this->input->post('package_id');
        $package = $this->location_model->getOnePackage($package_id);
        $location_amount = 0;
        $num_of_locations = 0;
        //print_r($package); exit;
        if (count($package) > 0) {
            $amount = $package->amount;
            $num_of_locations = $package->num_of_locations;
            $location_amount = number_format($amount * $num_of_locations, 2);
        }

        $business = $this->session->userdata('business');

        $userdata = $this->session->userdata('branch');
        //Payment display Start
        $branch = $this->session->userdata('branch');
        $no_of_branch = count($branch);

        $arr_charge['active'] = 1;
        $arr_charge['name'] = 'Branch';
        $charges = $this->user_model->getcharges($arr_charge, $row = '');
        //// Get Branch Charges
        $arr_pachage['package_id'] = $business['package'];
        $packageDetails = $this->subscription_model->getpackageDetails('*', $arr_pachage, $row = '');

        $country_id = $userdata[0]['country'];  //// Get Country of Main Business
        $country = $this->country_model->getcountry('country_id', $country_id, '1');

        if ($country->country_name == 'UK') {
            $branchCost = $packageDetails->price_gbp;
            $currencySymbol = '£';
            $vat = $charges->vat;
        } else {
            $branchCost = $packageDetails->price_usd;
            $currencySymbol = '$';
            $vat = 0.00;
        }

        $discount = $charges->discount;

        $cost_total_branch = $branchCost * $no_of_branch + $location_amount;

        if ($no_of_branch >= 2) {
            $discount_all_branch = ($cost_total_branch * $discount) / 100;

            $final_branch_cost = $cost_total_branch - $discount_all_branch;

            $total_amount = $final_branch_cost;
        } else {
            $final_branch_cost = $cost_total_branch;
            $total_amount = $cost_total_branch;
            $discount_all_branch = 0;
        }

        $data['vatCharge'] = 0;

        if (isset($vat)) {
            if ($vat != 0) {
                //$vatCharge=($branchCost*$vat)/100;
                $vatCharge = ($total_amount * $vat) / 100;   // VAT charge on total amount
                $data['vatCharge'] = number_format($vatCharge, 2);
                $total_amount = $total_amount + $data['vatCharge'];
            }
        }
        $total = number_format($total_amount, 2);
        $data = array(
            'branches' => $no_of_branch,
            'branchesCost' => number_format($final_branch_cost, 2),
            'Currency' => $currencySymbol,
            'VAT' => $data['vatCharge'],
            'total' => number_format($total_amount, 2),
            'discount' => $discount_all_branch,
            'totalLocations' => $num_of_locations,
            'trail_account_msg' => "Free 30 day trial, after which you will be charged $currencySymbol$total"
        );
        echo json_encode($data);
    }

    function businessPayment() {
        $last_insert_user_id = $this->user_model->getMaxUserRow();
        $last_insert_id = rand();
        if (isset($last_insert_user_id->user_Id)) {
            $last_insert_id = $last_insert_user_id->user_Id + 1;
        }
        $currDate = date('YmdHis');
        $name = $this->input->post('card_name');
        $this->input->post('card_number');
        $this->input->post('card_type');
        $this->input->post('month');
        $this->input->post('year');
        $this->input->post('cvv2');
        $this->input->post('amount');
        $totalLocations = $this->input->post('totalLocations');
        $payment_trail = $this->input->post('payment_trail');

        //Add vat and amount in email
        $vat = $this->input->post('vat');
        $totalCost = $this->input->post('total');

        $vat1 = str_replace(' ', '', $vat);
        $totalCost1 = str_replace(' ', '', $totalCost);
        //End
        //Taking firstname and lastname
        $arr_name = explode(' ', trim($name));
        $cnt_name = count($arr_name);

        $firstname = '';
        for ($i = 0; $i < $cnt_name - 1; $i++) {
            $firstname = $firstname . ' ' . $arr_name[$i];
        }

        $lastname = $arr_name[$i]; //End

        $userdata = $this->session->userdata('business');

        $sess_business = $this->session->userdata('branch');

        $email = $userdata['email'];
        $country = $sess_business[0]['country'];
        $country = $this->country_model->getcountry('country_id', $country, 1);

        if ($country->country_code == 'GB') {
            $code = 1;
        } else {
            $code = 0;
        }

        $onecurency = $this->country_model->getcurrency($code);

        $curency = $onecurency->currency_code;
        //echo $curency;die;

        $card_no = $this->input->post('card_number');
        $exp_month = $this->input->post('month');
        $exp_year = $this->input->post('year');

        $cvv_no = $this->input->post('cvv2');
        $address = $sess_business[0]['address'];
        $city = $sess_business[0]['town'];
        $zip_code = $sess_business[0]['postcode'];
        $total = $this->input->post('amount');
        $amount = str_replace("$ ", "", $total);
        $amount = str_replace("£ ", "", $amount);
        $amount = str_replace("&#163; ", "", $amount);

        $currency = $curency;
        $card_type = $this->input->post('card_type');
        if ($card_type == 'Master Card') {
            $card_type = 'MasterCard';
        }

        $paymentType = urlencode('Sale');    // or 'Sale'
        $firstName = urlencode($firstname);
        $lastName = urlencode($lastname);
        $creditCardType = urlencode($card_type);
        $creditCardNumber = urlencode($card_no);
        $expDateMonth = $exp_month;
        // Month must be padded with leading zero
        $padDateMonth = urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));

        $expDateYear = urlencode($exp_year);
        $cvv2Number = urlencode($cvv_no);
        $address1 = urlencode($address);
        $city = urlencode($city);
        $state = urlencode($country->country_code);
        $zip = urlencode($zip_code);
        $country = urlencode($country->country_code);    // US or other valid country code
        $amount = urlencode($amount);
        $currencyID = urlencode($curency);       // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
        $email = urlencode($email);
        //echo gmdate("Y-m-d\TH:i:s\Z",time() + 2628000);
        if (!empty($payment_trail)) {
            $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z", time() + 2628000));
            $expiryDate = date('Y-m-d H:i:s', strtotime("+30 days"));
        } else {
            $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z"));
            $expiryDate = '';
        }
        //$amount = '79.99';
        //// Old string
        /* $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
          "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
          "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";
         */
        //new string

        $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
                "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country" .
                "&CURRENCYCODE=$currencyID&PROFILESTARTDATE=$profileStartDate&MAXFAILEDPAYMENTS=3" .
                "&DESC=Payment+of+business+user+signup&BILLINGPERIOD=Month" .
                "&BILLINGFREQUENCY=1";

        //SEND REQUEST TO PAYPAL direct payment
        //$httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);
        //SEND REQUEST TO PAYPAL reoccuring payment
        $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);
        //echo "<pre>";
        //print_r($httpParsedResponseAr);
        //echo "</pre>"; die;
        if ($httpParsedResponseAr['ACK'] == 'Failure') {
            if ($userdata['ambassadorId'] != '') {
                $ambassador_is_new = 1;
            } else {
                $ambassador_is_new = 0;
            }
            $userdata = $this->session->userdata('business');
            $date = date('YmdHis');
            $user['user_Id'] = '';
            $user['firstname'] = $userdata['firstname'];
            $user['lastname'] = $userdata['lastname'];
            $user['businessId'] = $last_insert_id;
            $user['email'] = $userdata['email'];
            $user['accountType'] = $payment_trail;
            $user['expiryDate'] = $expiryDate;
            $user['businessName'] = $userdata['businessName'];
            $user['ambassadorId'] = $userdata['ambassadorId'];
            $user['ambassador_is_new'] = $ambassador_is_new;
            $user['username'] = $userdata['username'];
            $user['password'] = md5($userdata['password']);
            $user['active'] = 1;
            $user['country'] = $sess_business[0]['country'];
            $user['usertype'] = 6;
            $user['image'] = 'user.png';
            $user['firstLogin'] = $currDate;
            $user['createdDate'] = $date;
            $user['paypal_profileid'] = 'failure1'; //$httpParsedResponseAr['PROFILEID']; //added by yogesh for reoccuring payment
            $user['paypal_response'] = json_encode($httpParsedResponseAr); //added by yogesh for reoccuring payment
            $user['package'] = $userdata['package'];
            $arr_package['package_id'] = $user['package'];
            $package = $this->subscription_model->getpackageDetails('*', $arr_package);
            $last_id = $this->user_model->insertsignup($user); //echo "heresss"; print_r($sess_business); exit;
            //$last_id = '60';
            foreach ($sess_business as $session) {
                $branch['branch_id'] = '';
                $branch['userid'] = $last_id;
                $branch['email'] = $session['email'];
                $branch['businessCategory'] = $session['businessCategory'];
                $branch['country'] = $session['country'];
                $branch['store_name'] = $userdata['businessName'];
                $branch['businessId'] = $last_insert_id;
                $branch['address'] = $session['address'];
                $branch['address2'] = $session['address2'];
                $branch['latitude'] = $session['latitude'];
                $branch['longitude'] = $session['longitude'];
                $branch['town'] = $session['town'];
                $branch['postcode'] = $session['postcode'];
                $branch['phone'] = $session['phone'];
                $branch['website'] = $session['website'];
                $branch['peopleVisit'] = $session['visitors'];
                $branch['description'] = $session['description'];
                $branch['main_branch'] = $session['main_branch'];
                $branch['active'] = 1;
                $branch['coinDate'] = $currDate;
                $branch['createdDate'] = $currDate;

                $branchid = $this->user_model->savebusinessbranch($branch);

                $userCoins = array(
                    'userid' => $last_id,
                    'coins' => $package->coins,
                    'coins_type' => 8,
                    'game_id' => 0,
                    'businessid' => $branchid,
                    'actionType' => 'add',
                    'createdDate' => date('YmdHis')
                );
                $this->score_model->insertCoins($userCoins);

                $coins = array(
                    'userid' => $last_id,
                    'coins' => $package->coins,
                    'branchid' => $branchid
                );
                $this->score_model->signupCoins($coins);

                $business_arr = array(
                    'businessId' => $last_insert_id,
                    'businessName' => $userdata['businessName'],
                    'country' => $sess_business[0]['country']
                );
                $this->user_model->saveBusinessId($business_arr);
            }

            //yogesh commented the payment insert in pyament status
            /*
              $payment['payment_id'] = '';    //comment start
              $payment['user_id'] = $last_id;
              $payment['purchasedOn'] = date('YmdHis');
              $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
              //$payment['currency']=urldecode($httpParsedResponseAr['CURRENCYCODE']);
              if ($currency == 'USD') {
              $payment['currency'] = '&#36;';
              } else {
              $payment['currency'] = '&pound;';
              }
              $payment['transationId'] = $httpParsedResponseAr['TRANSACTIONID'];
              $payment['paymentInfo'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '////CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
              $payment['isActive'] = 1;
              $payment['IsDelete'] = 0;
              $payment['createdDate'] = date('YmdHis');

              $last_payment_id = $this->payment_model->savepayment($payment); */  //comment end
            /* Coins For Business User */
            /* Save package details */
            $date = date('YmdHis');
            $userPackage['user_pro_id'] = '';
            $userPackage['user_id'] = $last_id;
            $userPackage['businessId'] = $last_insert_id;
            $userPackage['totalCoins'] = $package->coins;
            $userPackage['totalBeacons'] = $package->beacons;
            $userPackage['totalCampaigns'] = $package->campaigns;
            $userPackage['totalGeoFence'] = $package->geoFence;
            $userPackage['totalIndividualCampaigns'] = $package->individual_campaigns;
            $userPackage['totalLocations'] = $totalLocations;
            $userPackage['createdDate'] = $date;
            $userPackage['modifiedDate'] = $date;

            $last_package_id = $this->user_model->savePackage($userPackage);
            /* End save package   */

            //// SEND  EMAIL START
            $this->emailConfig();   //Get configuration of email
            //// GET EMAIL FROM DATABASE

            $email_template = $this->email_model->getoneemail('business_signup');

            //// MESSAGE OF EMAIL
            $messages = $email_template->message;

            $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
            $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
            $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

            //// replace strings from message
            $messages = str_replace('{Username}', ucfirst($userdata['username']), $messages);
            $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
            $messages = str_replace('{Price}', $totalCost1, $messages);
            $messages = str_replace('{Vat}', $vat1, $messages);
            //$messages = str_replace('{App_Store_Image}', $appstore, $messages);
            //$messages = str_replace('{Google_Image}', $googleplay, $messages);
            //// FROM EMAIL
            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to($userdata['email']);
            $this->email->subject($email_template->subject);
            $this->email->message($messages);
            $this->email->send();    ////  EMAIL SEND

            $userdata = $this->session->userdata('business');
            $message = '<strong>Business User Details</strong><br>';
            $message .= 'Name: ' . $userdata['firstname'] . ' ' . $userdata['lastname'] . '<br>';
            $message .= 'Email: ' . $userdata['email'] . '<br>';
            $message .= 'Business Name: ' . $userdata['businessName'] . '<br>';
            $message .= 'Username: ' . $userdata['username'] . '<br><br>';
            $message .= 'Total Price: ' . $totalCost1 . '<br><br>';
            $message .= 'VAT: ' . $vat1 . '<br><br>';
            $i = 1;
            foreach ($sess_business as $session) {

                $businessCategory = $this->user_model->getBusinessCat($session['businessCategory']);
                $this->user_model->getonecountry('country_id', $session['country']);
                $country = $this->user_model->getOneCountry($session['country']);

                $message .= '<strong>Branch ' . $i . '</strong><br>';
                $message .= 'Email: ' . $session['email'] . '<br>';
                $message .= 'Business Category: ' . $businessCategory->category . '<br>';   //'Business Category: '.$session['businessCategory'].'<br>'
                $message .= 'Country: ' . $country->country_name . '<br>';
                $message .= 'Address: ' . $session['address'] . '<br>';
                $message .= 'Address 2: ' . $session['address2'] . '<br>';
                $message .= 'Town: ' . $session['town'] . '<br>';
                $message .= 'Postcode: ' . $session['postcode'] . '<br>';
                $message .= 'Phone: ' . $session['phone'] . '<br>';
                $message .= 'Website: ' . $session['website'] . '<br>';
                $message .= 'Visitors: ' . $session['visitors'] . '<br>';
                $message .= 'Description: ' . $session['description'] . '<br><br>';

                $i++;
            }
            //// SEND  EMAIL START
            $this->emailConfig();   //Get configuration of email
            //// FROM EMAIL
            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to('Business@hurree.co');
            $this->email->subject('New Business Sign Up');
            $this->email->message($message);
            $this->email->send();    ////  EMAIL SEND

            $user['username'] = $userdata['username'];
            $user['usertype'] = 6;
            $user['createdDate'] = $date;

            $userDetails = $this->user_model->getUser($last_id);

            $this->session->set_userdata('logged_in', $userDetails); //echo "called"; exit;
            echo 'Success';
        } else {
            echo $httpParsedResponseAr['L_LONGMESSAGE0'];
        }
    }

    function businessPaymentByPaypal() {
        //Add vat and amount in email
        $total = $this->input->post('amount');
        $vat = $this->input->post('vat');
        $totalCost = $this->input->post('total');

        $totalLocations = $this->input->post('totalLocations');
        $payment_trail = $this->input->post('payment_trail');

        $vat1 = str_replace(' ', '', $vat);
        $totalCost1 = str_replace(' ', '', $totalCost);
        //End

        $userdata = $this->session->userdata('business');

        $sess_business = $this->session->userdata('branch');

        $email = $userdata['email'];
        $country = $sess_business[0]['country'];
        $country = $this->country_model->getcountry('country_id', $country, 1);

        if ($country->country_code == 'GB') {
            $code = 1;
        } else {
            $code = 0;
        }

        $onecurency = $this->country_model->getcurrency($code);

        $currency = $onecurency->currency_code;
        $address = $sess_business[0]['address'];
        $city = $sess_business[0]['town'];
        $zip_code = $sess_business[0]['postcode'];
        $amount = str_replace("$ ", "", $total);
        $amount = str_replace("£ ", "", $amount);
        $amount = str_replace("&#163; ", "", $amount);
        //$amount = '1.00';
        $paypal_details = array('vat' => $vat1, 'amount' => $amount, 'currencyID' => $currency, 'totalLocations' => $totalLocations, 'payment_trail' => $payment_trail);
        $this->session->set_userdata('paypal_details', $paypal_details);
        echo 'Success';
    }

    function paypalTokenAuthorize() {
        $paypal_details = $this->session->userdata('paypal_details');
        $amount = $paypal_details['amount'];
        $currencyID = $paypal_details['currencyID'];
        $paymentType = 'Sale';
        $token = '';

        $returnUrl = base_url() . 'home/paypalRecurringToken';
        $cancelUrl = base_url() . 'home/';
        $desc = 'Payment of business user signup';
        $nvpStr = "&L_BILLINGTYPE0=RecurringPayments&L_BILLINGAGREEMENTDESCRIPTION0=$desc&RETURNURL=$returnUrl&CANCELURL=$cancelUrl&PAYMENTREQUEST_n_AMT=$amount&PAYMENTREQUEST_0_PAYMENTACTION=$paymentType&PAYMENTREQUEST_0_CURRENCYCODE=$currencyID";

        $httpParsedResponseAr = $this->PPHttpPost('SetExpressCheckout', $nvpStr);
        $token = $httpParsedResponseAr['TOKEN'];
        redirect("https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=$token");
    }

    function paypalRecurringToken() {
        $token = $_GET['token']; //EC-0S431878A2959822T;
        $paypal_details = $this->session->userdata('paypal_details');
        $amount = $paypal_details['amount'];
        $currencyID = $paypal_details['currencyID'];
        $paymentType = 'Sale';
        $desc = 'Payment of business user signup';
        $totalLocations = $paypal_details['totalLocations'];
        $payment_trail = $paypal_details['payment_trail'];

        if (isset($token)) {
            $nvpStr = "&TOKEN=$token";

            $httpParsedResponseAr = $this->PPHttpPost('GetExpressCheckoutDetails', $nvpStr);
            //echo '<pre>'; print_r($httpParsedResponseAr);
            if ($httpParsedResponseAr['ACK'] == 'Success') {

                $currDate = date('YmdHis');
                $userdata = $this->session->userdata('business');

                $paypal_details = $this->session->userdata('paypal_details');

                $sess_business = $this->session->userdata('branch');

                $email = $userdata['email'];
                $country = $sess_business[0]['country'];
                $country = $this->country_model->getcountry('country_id', $country, 1);

                if ($country->country_code == 'GB') {
                    $code = 1;
                } else {
                    $code = 0;
                }

                $onecurency = $this->country_model->getcurrency($code);

                $currency = $onecurency->currency_code;
                $address = $sess_business[0]['address'];
                $city = $sess_business[0]['town'];
                $zip_code = $sess_business[0]['postcode'];
                $total = $paypal_details['amount'];
                $vat = urlencode($paypal_details['vat']);
                $amount = urlencode($paypal_details['amount']);
                $currencyID = urlencode($paypal_details['currencyID']);

                // or 'Sale'
                $firstname = $httpParsedResponseAr['FIRSTNAME'];
                $lastname = $httpParsedResponseAr['LASTNAME'];

                $payerId = $httpParsedResponseAr['PAYERID'];

                $nvpStr = "&TOKEN=$token&PAYERID=$payerId&PAYMENTREQUEST_0_AMT=$amount&PAYMENTREQUEST_0_PAYMENTACTION=$paymentType&PAYMENTREQUEST_0_CURRENCYCODE=$currencyID";

                $httpParsedResponseAr = $this->PPHttpPost('DoExpressCheckoutPayment', $nvpStr);
                //echo '<pre>'; print_r($httpParsedResponseAr);
                //$profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z"));
                $billingPeried = 'Month';

                if (!empty($payment_trail)) {
                    $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z", time() + 2628000));
                    $expiryDate = date('Y-m-d H:i:s', strtotime("+30 days"));
                } else {
                    $profileStartDate = urlencode(gmdate("Y-m-d\TH:i:s\Z"));
                    $expiryDate = '';
                }

                $nvpStr = "&TOKEN=$token&PAYERID=$payerId&PROFILESTARTDATE=$profileStartDate&DESC=$desc&BILLINGPERIOD=$billingPeried&BILLINGFREQUENCY=1&AMT=$amount&COUNTRYCODE=US&CURRENCYCODE=$currencyID&&MAXFAILEDPAYMENTS=3";

                $httpParsedResponseAr = $this->PPHttpPost('CreateRecurringPaymentsProfile', $nvpStr);
                //echo '<pre>'; print_r($httpParsedResponseAr);

                if ($httpParsedResponseAr['ACK'] == 'Success') {
                    if ($userdata['ambassadorId'] != '') {
                        $ambassador_is_new = 1;
                    } else {
                        $ambassador_is_new = 0;
                    }
                    $last_insert_user_id = $this->user_model->getMaxUserRow();
                    $last_insert_id = rand();
                    if (isset($last_insert_user_id->user_Id)) {
                        $last_insert_id = $last_insert_user_id->user_Id + 1;
                    }

                    $userdata = $this->session->userdata('business');
                    $date = date('YmdHis');
                    $user['user_Id'] = '';
                    $user['firstname'] = $userdata['firstname'];
                    $user['lastname'] = $userdata['lastname'];
                    $user['businessId'] = $last_insert_id;
                    $user['email'] = $userdata['email'];
                    $user['businessName'] = $userdata['businessName'];
                    $user['accountType'] = $payment_trail;
                    $user['expiryDate'] = $expiryDate;
                    $user['ambassadorId'] = $userdata['ambassadorId'];
                    $user['ambassador_is_new'] = $ambassador_is_new;
                    $user['username'] = $userdata['username'];
                    $user['password'] = md5($userdata['password']);
                    $user['active'] = 1;
                    $user['usertype'] = 6;
                    $user['country'] = $sess_business[0]['country'];
                    $user['image'] = 'user.png';
                    $user['firstLogin'] = $currDate;
                    $user['createdDate'] = $date;
                    $user['paypal_profileid'] = $httpParsedResponseAr['PROFILEID']; //added by yogesh for reoccuring payment
                    $user['paypal_response'] = json_encode($httpParsedResponseAr); //added by yogesh for reoccuring payment
                    $user['package'] = $userdata['package'];
                    $arr_package['package_id'] = $user['package'];
                    $package = $this->subscription_model->getpackageDetails('*', $arr_package);
                    $last_id = $this->user_model->insertsignup($user);
                    //$last_id = '60';
                    foreach ($sess_business as $session) {
                        $branch['branch_id'] = '';
                        $branch['userid'] = $last_id;
                        $branch['email'] = $session['email'];
                        $branch['businessId'] = $last_insert_id;
                        $branch['store_name'] = $userdata['businessName'];
                        $branch['businessCategory'] = $session['businessCategory'];
                        $branch['country'] = $session['country'];
                        $branch['address'] = $session['address'];
                        $branch['address2'] = $session['address2'];
                        $branch['latitude'] = $session['latitude'];
                        $branch['longitude'] = $session['longitude'];
                        $branch['town'] = $session['town'];
                        $branch['postcode'] = $session['postcode'];
                        $branch['phone'] = $session['phone'];
                        $branch['website'] = isset($session['website']) ? $session['website'] : '';
                        $branch['peopleVisit'] = isset($session['visitors']) ? $session['visitors'] : '';
                        $branch['description'] = isset($session['description']) ? $session['description'] : '';
                        $branch['main_branch'] = $session['main_branch'];
                        $branch['active'] = 1;
                        $branch['coinDate'] = $currDate;
                        $branch['createdDate'] = $currDate;

                        $branchid = $this->user_model->savebusinessbranch($branch);

                        $userCoins = array(
                            'userid' => $last_id,
                            'coins' => $package->coins,
                            'coins_type' => 8,
                            'game_id' => 0,
                            'businessid' => $branchid,
                            'actionType' => 'add',
                            'createdDate' => date('YmdHis')
                        );
                        $this->score_model->insertCoins($userCoins);

                        $coins = array(
                            'userid' => $last_id,
                            'coins' => $package->coins,
                            'branchid' => $branchid
                        );
                        $this->score_model->signupCoins($coins);

                        $business_arr = array(
                            'businessId' => $last_insert_id,
                            'businessName' => $userdata['businessName'],
                            'country' => $country
                        );
                        $this->user_model->saveBusinessId($business_arr);
                    }

                    //yogesh commented the payment insert in pyament status

                    $payment['payment_id'] = '';    //comment start
                    $payment['user_id'] = $last_id;
                    $payment['purchasedOn'] = date('YmdHis');
                    $payment['amount'] = urldecode($amount);
                    //$payment['currency']=urldecode($httpParsedResponseAr['CURRENCYCODE']);
                    if ($currency == 'USD') {
                        $payment['currency'] = '&#36;';
                    } else {
                        $payment['currency'] = '&pound;';
                    }
                    $payment['transationId'] = $httpParsedResponseAr['CORRELATIONID']; //$httpParsedResponseAr['TRANSACTIONID'];
                    $payment['paymentInfo'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '////CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
                    $payment['isActive'] = 1;
                    $payment['IsDelete'] = 0;
                    $payment['createdDate'] = date('YmdHis');

                    $last_payment_id = $this->payment_model->savepayment($payment);  //comment end
                    /* Coins For Business User */

                    /* Save package details */
                    $date = date('YmdHis');
                    $userPackage['user_pro_id'] = '';
                    $userPackage['user_id'] = $last_id;
                    $userPackage['businessId'] = $last_insert_id;
                    $userPackage['totalCoins'] = $package->coins;
                    $userPackage['totalBeacons'] = $package->beacons;
                    $userPackage['totalCampaigns'] = $package->campaigns;
                    $userPackage['totalGeoFence'] = $package->geoFence;
                    $userPackage['totalIndividualCampaigns'] = $package->individual_campaigns;
                    $userPackage['totalLocations'] = $totalLocations;
                    $userPackage['createdDate'] = $date;
                    $userPackage['modifiedDate'] = $date;

                    $last_package_id = $this->user_model->savePackage($userPackage);
                    /* End save package   */

                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// GET EMAIL FROM DATABASE

                    $email_template = $this->email_model->getoneemail('business_signup');

                    //// MESSAGE OF EMAIL    $payerId = $httpParsedResponseAr['PAYERID'];
                    $messages = $email_template->message;

                    $hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
                    $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
                    $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

                    //// replace strings from message
                    $messages = str_replace('{Username}', ucfirst($userdata['username']), $messages);
                    $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                    $messages = str_replace('{Price}', $amount, $messages);
                    $messages = str_replace('{Vat}', $vat, $messages);
                    //$messages = str_replace('{App_Store_Image}', $appstore, $messages);
                    //$messages = str_replace('{Google_Image}', $googleplay, $messages);
                    //// FROM EMAIL
                    $this->email->from('hello@marketmyapp.co', 'Hurree');
                    $this->email->to($userdata['email']);
                    $this->email->subject($email_template->suebject);
                    $this->email->message($messages);
                    $this->email->send();    ////  EMAIL SEND

                    $userdata = $this->session->userdata('business');
                    $message = '<strong>Business User Details</strong><br>';
                    $message .= 'Name: ' . $userdata['firstname'] . ' ' . $userdata['lastname'] . '<br>';
                    $message .= 'Email: ' . $userdata['email'] . '<br>';
                    $message .= 'Business Name: ' . $userdata['businessName'] . '<br>';
                    $message .= 'Username: ' . $userdata['username'] . '<br><br>';
                    $message .= 'Total Price: ' . $amount . '<br><br>';
                    $message .= 'VAT: ' . $vat . '<br><br>';
                    $i = 1;
                    foreach ($sess_business as $session) {

                        $businessCategory = $this->user_model->getBusinessCat($session['businessCategory']);
                        $this->user_model->getonecountry('country_id', $session['country']);
                        $country = $this->user_model->getOneCountry($session['country']);

                        $message .= '<strong>Branch ' . $i . '</strong><br>';
                        $message .= 'Email: ' . $session['email'] . '<br>';
                        $message .= 'Business Category: ' . $businessCategory->category . '<br>';   //'Business Category: '.$session['businessCategory'].'<br>'
                        $message .= 'Country: ' . $country->country_name . '<br>';
                        $message .= 'Address: ' . $session['address'] . '<br>';
                        $message .= 'Address 2: ' . $session['address2'] . '<br>';
                        $message .= 'Town: ' . $session['town'] . '<br>';
                        $message .= 'Postcode: ' . $session['postcode'] . '<br>';
                        $message .= 'Phone: ' . $session['phone'] . '<br>';
                        $message .= 'Website: ' . isset($session['website']) ? $session['website'] : '' . '<br>';
                        $message .= 'Visitors: ' . isset($session['visitors']) ? $session['visitors'] : '' . '<br>';
                        $message .= 'Description: ' . isset($session['description']) ? $session['description'] : '' . '<br><br>';

                        $i++;
                    }
                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// FROM EMAIL
                    $this->email->from('hello@marketmyapp.co', 'Hurree');
                    $this->email->to('Business@hurree.co');
                    $this->email->subject('New Business Sign Up');
                    $this->email->message($message);
                    $this->email->send();    ////  EMAIL SEND

                    $user['username'] = $userdata['username'];
                    $user['usertype'] = 6;
                    $user['createdDate'] = $date;

                    $userDetails = $this->user_model->getUser($last_id);
                    $this->session->unset_userdata('paypal_details');
                    $this->session->unset_userdata('paypalToken');
                    $this->session->set_userdata('logged_in', $userDetails);
                    redirect('timeline');  // exit;
                    //print_r($this->session->userdata('logged_in')); exit;
                    echo 'Success';
                } else {
                    echo $httpParsedResponseAr['L_LONGMESSAGE0'];
                }
            }
        } else {
            redirect('home');
        }
    }

    function ipn() {
        // STEP 1: read POST data
        // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
        // Instead, read raw POST data from the input stream.
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        $fp = fopen('/var/www/html/hurree3.1/file.txt', 'a+');
        fwrite($fp, print_r($myPost, TRUE));
        fclose($fp);

        //STEP: 2 insert data in database        

        if (!empty($myPost)) {
            $businessUserData = $this->user_model->getOneUserFromPayerId(urlencode($myPost['recurring_payment_id']));
            if (empty($businessUserData)) {
                $userId = 1;
            } else {
                $userId = $businessUserData->user_Id;
            }

            $payment = array();

            if ($myPost['txn_type'] == 'recurring_payment') {
                //get user id from payerid paypal                     

                $payment['user_id'] = $userId;
                $payment['amount'] = $myPost["amount"];
                $payment['transationId'] = $myPost['txn_id'];
                $payment['paymentInfo'] = json_encode($myPost);
                $payment['payment_status'] = $myPost['payment_status'];
                $payment['txn_type'] = $myPost["txn_type"];
                $payment['isActive'] = 1;
                $payment['IsDelete'] = 0;
                $payment['createdDate'] = date('YmdHis');
                $payment['currency'] = $myPost["currency_code"];
                $last_payment_id = $this->payment_model->savepayment($payment);
            }
        }

        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }
        // Step 2: POST IPN data back to PayPal to validate
        $ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if (!($res = curl_exec($ch))) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        curl_close($ch);
    }

    function countBranch() {
        $business = $this->session->userdata('branch');
        $count = count($business);
        //print_r($business);
        echo $count;
        return;
    }

    function businessPaymentPromocode() {
        $promo['promo_code'] = $this->input->post('promocode');
        $promo['active'] = 1;

        $promo = $this->promocode_model->getpromocode($promo);

        if (count($promo) > 0) {
            $promoValue = $promo->value;
            $promovaluetype = $promo->type;
            $currdate = date('Y-m-d');
            $vat = $this->input->post('vat');

            $total = $this->input->post('branchesCost');

            if (strpos($total, '$') !== false) {
                $currency = '$';
            }
            if (strpos($total, '£') !== false) {
                $currency = '£';
            }

            $amount = str_replace("$ ", "", $total);
            //echo 'Amount:'.$amount;
            $amount = str_replace("£ ", "", $amount);
            $amount = str_replace("&#163; ", "", $amount);

            $vatAmount = str_replace("$ ", "", $vat);
            $vatAmount = str_replace("£ ", "", $vatAmount);
            $vatAmount = str_replace("&#163; ", "", $vatAmount);

            $total = $amount + $vatAmount;

            if ($currdate >= $promo->valid_from && $currdate <= $promo->valid_till) {
                if ($promovaluetype == '%') {
                    $promo_amount = ($total * $promoValue) / 100;

                    $promo_amount = round($promo_amount, 2);
                    //echo $promo_amount;
                    $total_amount = $total - $promo_amount;

                    echo $currency . ' ' . $total_amount;
                }
            } else {
                echo 'Expired';
            }
        } else {

            echo 'Invalid';
        }
    }

    function sessionDestroy() {
        $this->session->unset_userdata('business');
        $this->session->unset_userdata('branch');

        return;
    }

    function download() {
        $data['showTopSignIn'] = 1;
        $this->load->view('header');
        $this->load->view('download');
        $this->load->vissew('footer');
    }

    function login() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $session_details = $this->user_model->check_username($username, $password);
        $checkUserTrail = checkUserTrailAccount($session_details->user_Id);

        if (count($session_details) > 0) {
            //// IF USERNAME IS ALREADY REGISTERED,  WILL GO FOR CHECK PASSWORD
            if ($session_details->usertype != 3) {
                //Check business user is on hold
                if ($session_details->usertype == 2 || $session_details->usertype == 6 || $session_details->usertype == 7 || $session_details->usertype == 8 || $session_details->usertype == 9) {
                    $where['userid'] = $session_details->user_Id;
                    $loginStatus = $this->user_model->getLoginStatus('*', $where);
                    if (count($loginStatus) == 0) {
                        $this->session->set_userdata('logged_in', $session_details);
                        $sess = $this->session->userdata('logged_in');
                        $_SESSION['hurree-business'] = 'access';
                        if ($session_details->usertype == 8 || $session_details->usertype == 9) {
                            $this->session->set_userdata('logged_in', $session_details);
                            if ($session_details->usertype == 8) {
                                $sdkShowMessage = checkUserSDKAlertMessage($session_details->businessId);
                                if (($sdkShowMessage['dont_show_message'] == 0) && !($checkUserTrail['remainingDays'] < 1) && ($checkUserTrail['accountType'] == "trail")) {
                                    $this->session->set_userdata('sdkAlertPopup', 1);
                                }
                            }
                            $sess = $this->session->userdata('logged_in');
                            echo '8';
                        } else {
                            echo '1';
                        }
                    } else {
                        $hold = $loginStatus->hold;
                        if ($hold === "1") {
                            echo '2';
                        } else {
                            $this->session->set_userdata('logged_in', $session_details);
                            $sess = $this->session->userdata('logged_in');
                            echo '1';
                        }
                    }
                } else {
                    //$this->session->set_userdata('logged_in', $session_details);
                    //$sess = $this->session->userdata('logged_in');
                    echo '3';
                }
            } else {
                echo '0';
                return "Invalid Username/Password";
            }
        }
    }

    function signin() {
        $data = '';
        $data['imageHref'] = '';
        if ($this->input->post('usertype') == 'consumer') {
            //// VALIDATION FOR USERNAME
            $this->form_validation->set_rules('login_username', 'Username', 'trim|required');
            $this->form_validation->set_rules('login_password', 'Password', 'trim|required|callback_username_check');
        } else {
            $this->form_validation->set_rules('bulogin_username', 'Business Username', 'trim|required');
            $this->form_validation->set_rules('bulogin_password', 'Password', 'trim|required|callback_username_check');
        }

        if ($this->form_validation->run() == FALSE) {
            $detect = new Mobile_Detect();
            if ($detect->isAndroidOS()) {
                $data['imageHref'] = "https://play.google.com/store?hl=en";
            }
            if ($detect->isiOS()) {

                $data['imageHref'] = "https://itunes.apple.com/en/genre/ios/id36?mt=8";
            }
            $uri = $this->uri->segment(2);
            $data['subheader'] = $this->subheader('front', '', $uri);

            $this->load->view('signup', $data);
        } else {

            if ($this->input->post('usertype') == 'consumer') {
                $username = $this->input->post('login_username');
                $password = $this->input->post('login_password');
            } else {
                $username = $this->input->post('bulogin_username');
                $password = $this->input->post('bulogin_password');
            }

            //// CHECK FOR USERNAME IN DATABASE
            $session_details = $this->user_model->check_username($username, $password);

            if (count($session_details) > 0) {
                //// IF USERNAME IS ALREADY REGISTERED,  WILL GO FOR CHECK PASSWORD
                if ($session_details->usertype != 3) {
                    $this->session->set_userdata('logged_in', $session_details);
                    $sess = $this->session->userdata('logged_in');
                    redirect('businessUser/account');
                } else {
                    $this->session->set_flashdata("error_message", "Invalid Username/Password");
                    redirect("home/signup");
                }
            } else {
                redirect('home/signup');
            }
        }
    }

    function username_check($password) {
        if ($this->input->post('usertype') == 'consumer') {
            $username = $this->input->post('login_username');
        } else {
            $username = $this->input->post('bulogin_username');
        }

        $session_details = $this->user_model->check_username($username, $password);

        if (count($session_details) > 0) {
            return true;
        } else {
            if ($this->input->post('usertype') == 'consumer') {
                $this->form_validation->set_message('username_check', 'Invalid Username/Password');
            } else {
                $this->form_validation->set_message('username_check', 'Invalid Username/Password');
            }
            return false;
        }
    }

    function index_prev() {
        $data['imageHref'] = '';
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == false && $header['login']->accesslevel == '') {

            $uri = $this->uri->segment(2);
            $data['subheader'] = $this->subheader('front', '', $uri);
            $detect = new Mobile_Detect();

            if ($detect->isAndroidOS()) {
                $data['imageHref'] = android_play_store_url;
            }
            $detect = new Mobile_Detect();
            if ($detect->isiOS()) {

                $data['imageHref'] = itunes_app_store_url;
            }

            $data['business_category'] = $this->user_model->getCategory();

            $data['countries'] = $this->country_model->get_countries();

            $data['business'] = 0;
            $data['uri'] = $this->uri->segment(2);

            $arr_card_type['active'] = 1;
            $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);

            $referral_name = $this->uri->segment(3);
            if ($referral_name != '') {
                $details = $this->user_model->referral_check($referral_name);
                $data['ambassador_id'] = $details->user_Id;
            } else {
                $data['ambassador_id'] = '';
            }

            $data['package'] = $this->package_model->getPackage(); //Get package data and send to front_footer

            $this->load->view('front_header', $data);
            $this->load->view('business', $data);
            //$this->load->view('index', $data);
            $this->load->view('front_footer', $data);
        } else {
            if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
                redirect('businessUser/account');
            } else {
                $this->session->unset_userdata('user_logged_in');
                redirect('home/index');
            }
        }
    }

    function index() {  //$this->session->sess_destroy();
        // echo "<pre>";  print_r($this->session->all_userdata()); die;
        $data['imageHref'] = '';
        $header['login'] = $this->administrator_model->front_login_session();
        // echo "<pre>";  print_r($header['login']); die;

        if ($header['login']->true == false && $header['login']->accesslevel == '') {

            $uri = $this->uri->segment(2);
            $data['subheader'] = $this->subheader('front', '', $uri);
            $detect = new Mobile_Detect();

            if ($detect->isAndroidOS()) {
                $data['imageHref'] = android_play_store_url;
            }
            $detect = new Mobile_Detect();
            if ($detect->isiOS()) {

                $data['imageHref'] = itunes_app_store_url;
            }

            $data['business_category'] = $this->user_model->getCategory();

            $data['countries'] = $this->country_model->get_countries();

            $data['business'] = 0;
            $data['uri'] = $this->uri->segment(2);

            $arr_card_type['active'] = 1;
            $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);

            $referral_name = $this->uri->segment(3);
            if ($referral_name != '') {
                $details = $this->user_model->referral_check($referral_name);
                $data['ambassador_id'] = $details->user_Id;
            } else {
                $data['ambassador_id'] = '';
            }

            $data['package'] = $this->package_model->getPackage(); //Get package data and send to front_footer

            $this->load->view('front_header_new', $data);
            $this->load->view('business_new', $data);
            //$this->load->view('index', $data);
            $this->load->view('front_footer_new', $data);
            //  echo "<pre>";  print_r($this->session->all_userdata()); die;
            // die;
        } else {
            if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
                redirect('businessUser/account');
            } else {
                $this->session->unset_userdata('user_logged_in');
                redirect('home/index');
            }
        }
    }

    function ambassador() {
        $data['uri'] = $this->uri->segment(2);
        $this->load->view('header');
        $this->load->view('ambassador_view', $data);
        $this->load->view('footer');
    }

    function store($regionid = NULL) {
        $header['login'] = $this->administrator_model->front_login_session();
        $userid = $header['login']->user_id;
        $data['coins'] = $this->score_model->getUserCoins($userid);

        $data['uri'] = $this->uri->segment(2);
        /* Get Regions List */

        $data['regions'] = $this->store_model->store_regions();

        /* Get Store Produts */

        $data['store'] = $this->store_model->getProduct($regionid, 1);
        $data['regionid'] = $regionid;
        //echo '<pre>';print_r($data);
        $this->load->view('store', $data);
    }

    function delivery() {

        $id = $this->uri->segment(3);

        $data['store_product'] = $this->store_model->product_detail($id);
        $data['countries'] = $this->country_model->get_countries();

        $header['login'] = $this->administrator_model->front_login_session();
        $userid = $header['login']->user_id;

        $data['user_detail'] = $this->user_model->getOneUser($userid);

        $data['uri'] = $this->uri->segment(2);
        $this->load->view('delivery', $data);
    }

    function sendDelivery() {
        $email = $this->input->post('email');
        $username = $this->input->post('username');
        $item = $this->input->post('item');
        $coins = $this->input->post('coins');
        $name = $this->input->post('name');
        $address = $this->input->post('address');
        $country = $this->input->post('country');
        $postcode = $this->input->post('postcode');

        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('store');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;

        $hurree_image = base_url() . 'hurree/assets/template/frontend/img/app-icon.png';
        $appstore = base_url() . 'hurree/assets/template/frontend/img/appstore.gif';
        $googleplay = base_url() . 'hurree/assets/template/frontend/img/googleplay.jpg';

        //// replace strings from message
        $messages = str_replace('{Username}', ucfirst($username), $messages);
        $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
        $messages = str_replace('{App_Store_Image}', $appstore, $messages);
        $messages = str_replace('{Google_Image}', $googleplay, $messages);
        $messages = str_replace('{Product}', $item, $messages);

        //// FROM EMAIL
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($email);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
        //// FROM EMAIL
        $messages = '<p>Product Ordered: ' . $item . '</p><p>No. of Coins Redeemed: ' . $coins . '</p><p>Full Name: ' . $name . '</p><p>Address: ' . $address . '</p><p>Country: ' . $country . '</p><p>Post/Zip Code: ' . $postcode . '</p>';
        $this->email->from($email, $name);
        $this->email->to('Store@Hurree.co');
        $this->email->subject('A user redeemed coins on Hurree');
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
        //// END EMAIL
        echo '<p style="font-family:Roboto,sans-serif;text-align:center;margin-top:6%;">Our Bots are working on your order</p>';

        //redirect('home/timeline');
        $header['login'] = $this->administrator_model->front_login_session();
        $userid = $header['login']->user_id;

        //Insert
        $insert = array(
            'userid' => $userid,
            'coins' => $coins,
            'coins_type' => 5,
            'game_id' => 0
        );

        $this->score_model->insert($insert);

        //Update User Coins

        $userTotalCoins = $this->score_model->getUserCoins($userid);
        $updateCoins = $userTotalCoins->coins - $coins;

        $update = array(
            'userid' => $userid,
            'coins' => $updateCoins
        );
        $this->score_model->update($update);
    }

    function userprofile() {
        //echo "here"; exit;
        if ($this->uri->segment('1') == 'business') {
            $id = $this->uri->segment('2');
        } else {
            $id = $this->uri->segment('3');
        }
        //if($id != ''){
        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->active == 0) {
            redirect('home');
        }

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {

            if (!is_numeric($id)) {
                $select = "user_Id, usertype";
                $arr_where['username'] = $id;
                $arr_where['active'] = 1;
                $user = $this->user_model->getOneUserDetails($arr_where, $select);
                if (count($user) > 0) {
                    //echo $this->db->last_query(); die;
                    $id = $user->user_Id;
                }
            }

            $block['userid'] = $header['login']->user_id;
            $block['block_user_id'] = $id;
            $result = $this->user_model->getblockUser($block, 1);
            if (count($result) > 0) {
                $data['block'] = 1;
            } else {
                $data['block'] = 0;
            }

            $checkBlock['userid'] = $id;
            $checkBlock['block_user_id'] = $header['login']->user_id;
            $checkLoginUserIsBlock = $this->user_model->checkLoginUserIsBlock($checkBlock);
            //echo count($checkLoginUserIsBlock);die;
            if (count($checkLoginUserIsBlock) > 0) {
                $data['checkLoginUserIsBlock'] = 1;
            } else {
                $data['checkLoginUserIsBlock'] = 0;
            }
            $this->load->helper('convertlink');
            $this->load->helper('follow');
            $this->load->helper('like');

            $data['loginuser'] = $header['login']->user_id;
            $data['loginusername'] = $header['login']->username;   //// Logged In User Username
            $data['user'] = $this->user_model->getOneUser($id);

            if (count($data['user']) > 0 && $data['user']->usertype != 3) {
                $data['user']->bio = convert_hurree_links($data['user']->bio);

                $data['totalcoins'] = $this->score_model->getUserCoins($id);

                //Count activities
                /* $arr_status['user_status.userid'] = $id;
                  $arr_status['status_image'] = '';
                  $activities = $this->user_model->getStatusDetails($arr_status, 1); */
                $activities = $this->user_model->getUserStatusCount($id);

                $data['activities'] = $activities;
                //$data['activities_data'] = $activities;
                //End Count activities
                //Count Photos
                $photos = $this->user_model->getUserStatusPhotsCount($id);
                $data['photos'] = $photos;
                //End Count Photos
                //Follower users
                $followers = $this->user_model->getLoggedInUserFollowers($id);
                $data['userid'] = $id;
                $data['followers'] = $followers;

                //Following users
                $following = $this->user_model->getLoggedInUserFollowing($id);
                $data['following'] = $following;

                $header['login'] = $this->administrator_model->front_login_session();
                $userid = $header['login']->user_id;
                $data['loggedin'] = $userid;
                $data['loggedinUsertype'] = $header['login']->usertype;
                $data['loggedinUserImage'] = $header['login']->image;

                $data['hashTag'] = '';

                $follow = array(
                    'userId' => $userid,
                    'followUserId' => $id
                );

                $data['followUsers'] = $this->user_model->getfollowUsers($follow);

                $array_following['followUserId'] = $id;
                $array_following['active'] = 1;
                $followers = $this->user_model->getfollowinguserid($array_following);

                //People to follow
                //$type = 1;
                $data['peoples'] = $this->user_model->getpeopletofollow($id, '1');
                /* echo '<pre>';
                  print_r($data['peoples']); die; */

                $data['businesses'] = $this->user_model->getpeopletofollow($id, '2');

                //Activities Section
                $orderby = 'DESC';
                $data['status_activities_records'] = $this->status_model->getUserActivities($id, '', '', $orderby, $header['login']->user_id);
                $config['base_url'] = base_url() . 'index.php/business/' . $this->uri->segment('3');
                $config['total_rows'] = $data['status_activities_records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $orderby = 'DESC';
                $data['status_activities'] = $this->status_model->getUserActivities($id, $data['page'], $config['per_page'], $orderby, $header['login']->user_id);
                $data['statuscount'] = count($data['status_activities']);
                $data['noofstatus'] = $config['per_page'];
                //End Activities
                //Replies Section
                $orderby = 'DESC';
                $data['status_replies_records'] = $this->status_model->getUserReplies($id, '', '', $orderby, $header['login']->user_id);
                $config['base_url'] = base_url() . 'index.php/business/' . $this->uri->segment('3');
                $config['total_rows'] = $data['status_replies_records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $orderby = 'DESC';
                $data['status_replies'] = $this->status_model->getUserReplies($id, $data['page'], $config['per_page'], $orderby, $header['login']->user_id);
                $data['statuscount'] = count($data['status_activities']);
                $data['noofstatus'] = $config['per_page'];
                //End Replies Section
                //Photos and Videos Section
                $orderby = 'DESC';
                $data['status_media_records'] = $this->status_model->getUserMedia($id, '', '', $orderby, $header['login']->user_id);
                $config['base_url'] = base_url() . 'index.php/business/' . $this->uri->segment('3');
                $config['total_rows'] = $data['status_media_records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $data['page'] = $page;

                $orderby = 'DESC';
                $data['status_media'] = $this->status_model->getUserMedia($id, $data['page'], $config['per_page'], $orderby, $header['login']->user_id);
                $data['statuscount'] = count($data['status_media']);
                $data['noofstatus'] = $config['per_page'];
                //End Photos and Videos Section
                //$orderby = 'DESC';
                //$data['timeline'] = $this->status_model->getUserStatus($id, '', '', $orderby, $header['login']->user_id);


                /* Replies Section and Likes Section  */
                $noticeStatusid = NULL;
                $data['records'] = $this->user_model->getFollowerStatus($id, '', '', '', $noticeStatusid, '', $header['login']->user_id);

                //Likes statuses count
                $records = $data['records'];
                $j = 0;
                foreach ($records as $status) {
                    if ($status->like == 'true') {
                        $j++;
                    }
                }
                $data['likesCount'] = $j;

                $block = array(
                    'userid' => $userid,
                    'block_user_id' => $id
                );
                $data['user_block'] = $this->user_model->userIsBlock($block);
                $data['equal_height'] = 1; //Don't include equal-height.js

                if ($user->usertype == 2 || $user->usertype == 5) {
                    $branch = $this->user_model->getbusinessbranches($id);

                    if (count($branch) > 0) {
                        $data['website'] = $branch[0]->website;
                    } else {
                        $data['website'] = '';
                    }
                } else {
                    $data['website'] = '';
                }

                $data['viewPage'] = 'profile';

                $this->load->view('inner_header', $data);
                $this->load->view('userfullprofile', $data);
                $this->load->view('inner_footer', $data);
            } else {
                redirect('businessUser/account');
            }
        } else {
            $this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
            redirect("home/signup");
        }
    }

    function timeline($noticeStatusid = NULL) {
//redirect('businessUser/account');
        if (isset($_COOKIE['hurree_campaignUrl'])) {

            redirect($_COOKIE['hurree_campaignUrl']);
        } else {
            redirect('businessUser/account');
            // echo date("h:i:sa"); exit;

            $this->load->helper('convertlink');
            $this->load->helper('follow');

            if ($this->uri->segment(2) != 'timeline' && $this->uri->segment(3) != 'timeline') {
                $noticeStatusid = $this->uri->segment(2);
            }
            //// Get Login User Details
            $header['login'] = $this->administrator_model->front_login_session();

            /* Check user is on Hold */
            $data['loginStatus'] = '';
            if ($header['login']->usertype == 2 || $header['login']->usertype == 5) {
                $where['userid'] = $header['login']->user_id;
                $loginStatus = $this->user_model->getLoginStatus('*', $where);

                if (count($loginStatus) > 0) {
                    $cancel = $loginStatus->cancel;
                    if ($cancel === "1") {
                        $data['loginStatus'] = "hold";
                    }
                }
            }
            /* End Check user is on Hold */

            $data['oneNotice'] = 0;
            $data['equal_height'] = ''; //Include equal-height.js
            $data['newNotification'] = 0;
            $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
            if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
                /* Get Login User Push Message */

                $pushdetails['usertype'] = $header['login']->usertype;
                $pushdetails['userid'] = $header['login']->user_id;
                $pushdetails['active'] = 1;
                $data['pushmessage'] = $this->pushmessage($pushdetails);      //// Get Push Message

                /* Start Pagination for Timline */
                $data['records'] = $this->user_model->getFollowerStatus($header['login']->user_id, '', '', $count = 1, $noticeStatusid, '', $header['login']->user_id);   //// Get Total No of Records in Database
                $config['base_url'] = base_url() . 'index.php/home/timeline/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                if ($noticeStatusid != '') {
                    $page = '';
                    $config['per_page'] = '';
                }
                $data['page'] = $page;
                $data['loggedinUsertype'] = $header['login']->usertype;
                $data['timeline'] = $this->user_model->getFollowerStatus($header['login']->user_id, $data['page'], $config['per_page'], '', $noticeStatusid, '', $header['login']->user_id);  //// Get Record
                $data['last_record'] = $this->status_model->getlastid();

                /* End Pagination for Timline */
                /* Define Variables  */
                $data['noticeStatusid'] = $noticeStatusid;
                $data['statuscount'] = count($data['timeline']);
                $data['noofstatus'] = $config['per_page'];
                if ($noticeStatusid != '') {
                    $data['oneNotice'] = 1;
                }

                /* Start Get Notification */
                $select = "*";
                $arr_notification['actionTo'] = $header['login']->user_id;
                $arr_notification['notification.active'] = 1;
                $arr_notification['notification.isDelete'] = 0;
                $arr_notification['notification.is_new'] = 1;

                $Notificationrecords = $this->notification_model->getnotification($arr_notification, $row = 1, $select, '', '', $totalrecords = 1);   //// Get Total No of 	Records in Database
                if ($Notificationrecords != '0') {
                    $data['newNotification'] = 1;
                }

                /* End Get Notification */

                //// Get Users Follower User Id
                $data['followuser'] = $this->user_model->getfollowuser($header['login']->user_id, $onlyid = 1);   //// Get Follower Userid

                $data['loginuser'] = $header['login']->user_id;   //// Logged In User Id
                $data['loginusername'] = $header['login']->username;   //// Logged In User Username
                $data['loginusertype'] = $header['login']->usertype;   //// Logged In User Usertype
                $data['hashTag'] = '';   //// Logged In User Usertype
                //$data['allgames'] = $this->games_model->checkAllGamesSubscription($data);
                //QR Code
                $qrcode = $this->qrcode_model->getQRcodeDetails($header['login']->user_id);
                $data['previous_qrcode'] = count($qrcode);
                $data['qr_code'] = $qrcode;

                //Get Campaigns list
                if ($header['login']->usertype == 2 || $header['login']->usertype == 5) {
                    $data['campaigns'] = $this->campaign_model->getAllCampaigns($header['login']->user_id);
                    //echo '<pre>';
                    //print_r($data['campaigns']); die;
                }

                //Suggested follow
                $data['businesses'] = $this->user_model->getpeopletofollow($header['login']->user_id, '2', '5', 'website');

                //// Get List of Received Direct Messages
                $data['messageDetails'] = $this->message_model->getreceiveMessagesUser($header['login']->user_id);

                /* Start Suggested Follow */
                $arr_login['user_Id'] = $data['loginuser'];
                $arr_login['active'] = 1;
                $userLogin = $this->user_model->getOneUserDetails($arr_login, '*');
                $loginDate = $userLogin->firstLogin;


                $currentDate = date('Y-m-d H:i:s');

                $date1 = new DateTime($currentDate);
                $date2 = new DateTime($loginDate);

                $diff = $date2->diff($date1);

                $hours = $diff->h;
                $hours = $hours + ($diff->days * 24);

                if ($loginDate == $userLogin->createdDate) {
                    $firstLogin = 1;
                    $limit = 6;
                } else {
                    $firstLogin = 0;
                    $limit = 1;
                }

                // Get Old Suggesed Follow
                $arr_lastSuggest = '';
                $arr_newSuggest = '';
                $arr_sugg['loginuserid'] = $header['login']->user_id;
                $previousSuggestion = $this->user_model->getSuggestedFollow($arr_sugg, '1');

                if (count($previousSuggestion) > 0) {
                    foreach ($previousSuggestion as $pre) {
                        $arr_lastSuggest[] = $pre->followuserid;
                        if ($pre->accept != 1) {
                            $arr_newSuggest[] = $pre->followuserid;
                        }
                    }
                }

                /* echo $hours;  */
                if ($hours >= 9 || count($previousSuggestion) == 0) {
                    $data['suggest_follow'] = $this->user_model->getpeopletofollow($header['login']->user_id, '1', $limit, 'timeline', '', $arr_lastSuggest);
                    //echo 'Count:'.count($data['suggest_follow']);
                    $followSuggestionId = '';
                    $this->user_model->deleteSuggest($header['login']->user_id);
                    foreach ($data['suggest_follow'] as $foll) {
                        $arr_suggestion['suggest_id'] = '';
                        $arr_suggestion['loginuserid'] = $header['login']->user_id;
                        $arr_suggestion['followuserid'] = $foll->user_Id;
                        $arr_suggestion['accept'] = '0';
                        $arr_suggestion['createdDate'] = date('YmdHis');
                        $arr_suggestion['modifiedDate'] = '';
                        $this->user_model->saveSuggestedFollow($arr_suggestion);
                    }

                    $update['user_Id'] = $header['login']->user_id;
                    $update['firstLogin'] = date('YmdHis');
                    $this->user_model->save($update);
                } else {
                    $data['suggest_follow'] = $this->user_model->suggestDetails($arr_newSuggest);
                }
                /* END Suggested Follow */


                //Code by Hassan for showing games list
                $userid = $header['login']->user_id;
                $data['gamelist'] = $this->games_model->gameslist($userid);

                //$data['checkallGames'] = $data['gamelist'][4]->lock;  //Check All games lock or Unlock
                $toalChallenges = $this->challenge_model->getChallenge($userid);

                foreach ($toalChallenges as $challenge) {
                    $challengefrom = $challenge->challengefrom;
                    $username = $this->user_model->getusername($challengefrom);
                    $challenge->challengefrom = $username->username;
                }
                $data['challenge_recieve'] = $toalChallenges;

                $data['loggedinUser'] = $header['login']->user_id;
                $data['loggedinUserImage'] = $header['login']->image;
                $data['loginusertype'] = $header['login']->usertype;
                $data['viewPage'] = 'timeline';
                //Display total coins of user
                $data['totalcoins'] = $this->score_model->getUserCoins($userid); // Get total user's coins
                $data['user'] = $this->user_model->getOneUser($userid); // confusion
                $challenge = $this->input->post('challenge');

                if ($challenge != 1) {
                    //blank field validation
                    $this->form_validation->set_rules('message', 'Message', 'trim|required');
                    if ($this->form_validation->run() == FALSE) {
                        $data['invalid_username'] = '';
                        $data['blank_message'] = '';
                        $data['msg_username'] = '';
                        $data['challenge_username'] = '';
                        $data['game_subscription'] = '';
                        $data['coins_validation'] = '';
                        $data['challengegameid'] = '';

                        $this->load->view('inner_header', $data);
                        $this->load->view('timeline', $data);
                        $this->load->view('inner_footer');
                    } else {
                        //username validation
                        $sendmessage = $this->input->post('message');
                        $var = explode(' ', $sendmessage);
                        $username = str_replace('@', '', $var[0]);
                        $getusername = $this->message_model->getUsername($username);
                        $message = str_replace($var[0], '', $sendmessage);
                        if (count($getusername) == 0) {

                            $data['invalid_username'] = "User doesn't exits"; //Error for invalid username
                            $data['blank_message'] = '';
                            $data['challenge_username'] = '';
                            $data['game_subscription'] = '';
                            $data['coins_validation'] = '';
                            $data['challengegameid'] = '';

                            $this->load->view('inner_header', $header);
                            $this->load->view('timeline', $data);
                            $this->load->view('inner_footer');
                        }
                        //Message validation
                        $sendmessage = $this->input->post('message');
                        $var = explode(' ', $sendmessage);
                        $username = str_replace('@', '', $var[0]);
                        //$getusername = $this->message_model->getUsername($username);
                        $message = str_replace($var[0], '', $sendmessage);
                        if ($message == '' && count($getusername) == 1) {

                            $data['msg_username'] = "@" . $username;
                            $data['blank_message'] = 'Please enter your message'; //Error for invalid username
                            $data['challenge_username'] = '';
                            $data['game_subscription'] = '';
                            $data['coins_validation'] = '';
                            $data['challengegameid'] = '';

                            $this->load->view('inner_header', $header);
                            $this->load->view('timeline', $data);
                            $this->load->view('inner_footer');
                        }
                        $sendmessage = $this->input->post('message');
                        $var = explode(' ', $sendmessage);
                        $username = str_replace('@', '', $var[0]);
                        $message = str_replace($var[0], '', $sendmessage);

                        if ($message != '' && count($getusername) == 1) {
                            $sendmessage = $this->input->post('message');
                            $var = explode(' ', $sendmessage);
                            $username = str_replace('@', '', $var[0]);
                            $user = $this->message_model->getUserId($username);
                            $message = str_replace($var[0], '', $sendmessage);

                            $data = array(
                                'message_from' => $header['login']->user_id,
                                'userid' => $user->user_id,
                                'username' => $username,
                                'message' => $message,
                                'isDelete' => 0,
                                'createdDate' => date('YmdHis'),
                                'modifiedDate' => date('YmdHis')
                            );
                            $this->message_model->add($data);
                            $this->session->set_flashdata('success_message', 'Message sent successfully');
                            redirect('businessUser/account');
                        }
                    }
                }//challenge input
                else {
                    $this->form_validation->set_rules('username', 'Username', 'trim|required');
                    $this->form_validation->set_rules('coins', 'Coins', 'trim|required|numeric');
                    if ($this->form_validation->run() == FALSE) {
                        $data['invalid_username'] = '';
                        $data['challenge_username'] = '';
                        $data['blank_message'] = '';
                        $data['msg_username'] = '';
                        $data['game_subscription'] = '';
                        $data['coins_validation'] = '';
                        $data['challengegameid'] = $this->input->post('game');

                        $this->load->view('inner_header', $header);
                        $this->load->view('timeline', $data);
                        $this->load->view('inner_footer');
                    } else {
                        $inputusername = $this->input->post('username');
                        $var = explode(' ', $inputusername);
                        $username = str_replace('@', '', $var[0]);
                        $getusername = $this->message_model->getUsername($username);
                        if (count($getusername) == 0) {
                            /* echo 'in'; die; */
                            $data['invalid_username'] = '';
                            $data['blank_message'] = '';
                            $data['msg_username'] = '';
                            $data['challenge_username'] = "User Doesn't exits"; //Error for invalid username
                            $data['game_subscription'] = '';
                            $data['coins_validation'] = '';
                            $data['challengegameid'] = $this->input->post('game');

                            $this->load->view('inner_header', $header);
                            $this->load->view('timeline', $data);
                            $this->load->view('inner_footer');
                        } else {

                            $inputusername = $this->input->post('username');
                            $var = explode(' ', $inputusername);
                            $username = str_replace('@', '', $var[0]);
                            $getusername = $this->message_model->getUsername($username);

                            if (count($getusername) > 0 && $getusername->usertype != 1) {
                                /* echo 'in'; die; */
                                $data['invalid_username'] = '';
                                $data['blank_message'] = '';
                                $data['msg_username'] = '';
                                $data['challenge_username'] = "You can't challange businesses"; //Error for invalid username
                                $data['game_subscription'] = '';
                                $data['coins_validation'] = '';
                                $data['challengegameid'] = $this->input->post('game');

                                $this->load->view('inner_header', $header);
                                $this->load->view('timeline', $data);
                                $this->load->view('inner_footer');
                            } else {
                                //Check game is purchase
                                $gameid = $this->input->post('game');
                                $coins = $this->input->post('coins');
                                $purchasegame = $this->games_model->getGameId($userid);
                                //print_r($purchasegame);
                                if (count($purchasegame) > 0) {
                                    foreach ($purchasegame as $userGame) {
                                        //echo $userGame->game_id;
                                        if ($userGame->game_id == $gameid || $userGame->game_id == 5) {

                                            $totalcoins = $this->games_model->userCoins($userid);

                                            if (count($totalcoins) > 0) {
                                                if ($coins <= $totalcoins->coins) {
                                                    $inputusername = $this->input->post('username');
                                                    $var = explode(' ', $inputusername);
                                                    $username = str_replace('@', '', $var[0]);
                                                    $getuserid = $this->message_model->getUserId($username);
                                                    $challenge_to = $getuserid->user_id;
                                                    $gameid = $this->input->post('game');
                                                    $coins = $this->input->post('coins');
                                                    $challenge = array(
                                                        'challenge_from' => $userid,
                                                        'challenge_to' => $challenge_to,
                                                        'game_id' => $gameid,
                                                        'challenge_coins' => $coins,
                                                        'approval' => 0,
                                                        'winner' => 0,
                                                        'createdDate' => date('YmdHis'),
                                                        'modifiedDate' => date('YmdHis')
                                                    );
                                                    //print_r($challenge);
                                                    $challangeid = $this->challenge_model->add($challenge);
                                                    $this->session->set_flashdata('create_challenge', 'You sent a challenge!');

                                                    /* Send Notification to Challeged Person  */

                                                    $arr_notice['notification_id'] = '';
                                                    $arr_notice['actionFrom'] = $userid;
                                                    $arr_notice['actionTo'] = $challenge_to;
                                                    $arr_notice['action'] = 'CC';
                                                    $arr_notice['actionString'] = ' Challenged You!';
                                                    $arr_notice['message'] = '';
                                                    $arr_notice['statusid'] = '';
                                                    $arr_notice['challangeid'] = $challangeid;
                                                    $arr_notice['active'] = '1';
                                                    $arr_notice['createdDate'] = date('YmdHis');
                                                    $notice_id = $this->notification_model->savenotification($arr_notice);

                                                    redirect('timeline');
                                                } else {
                                                    $data['invalid_username'] = '';
                                                    $data['blank_message'] = '';
                                                    $data['msg_username'] = '';
                                                    $data['challenge_username'] = '';
                                                    $data['game_subscription'] = '';
                                                    $data['challengegameid'] = $this->input->post('game');
                                                    //$data['coins_validation']="You have only ".$totalcoins->coins." Coins";
                                                    $data['coins_validation'] = "You don't have enough coins";

                                                    $this->load->view('inner_header', $header);
                                                    $this->load->view('timeline', $data);
                                                    $this->load->view('inner_footer');
                                                }
                                            } else {
                                                $data['invalid_username'] = '';
                                                $data['blank_message'] = '';
                                                $data['msg_username'] = '';
                                                $data['challenge_username'] = '';
                                                $data['game_subscription'] = '';
                                                $data['challengegameid'] = $this->input->post('game');
                                                $data['coins_validation'] = "You don't have enough coins, buy some?";

                                                $this->load->view('inner_header', $header);
                                                $this->load->view('timeline', $data);
                                                $this->load->view('inner_footer');
                                            }
                                        } else {
                                            $data['invalid_username'] = '';
                                            $data['blank_message'] = '';
                                            $data['msg_username'] = '';
                                            $data['challenge_username'] = '';
                                            $data['challengegameid'] = $this->input->post('game');
                                            $data['game_subscription'] = 'Please purchase this game first';
                                            $data['coins_validation'] = '';
                                            //redirect('home/timeline');
                                            $this->load->view('inner_header', $header);
                                            $this->load->view('timeline', $data);
                                            $this->load->view('inner_footer');
                                        }
                                    }
                                } else {

                                    $data['invalid_username'] = '';
                                    $data['blank_message'] = '';
                                    $data['msg_username'] = '';
                                    $data['challenge_username'] = '';
                                    $data['coins_validation'] = '';
                                    $data['challengegameid'] = $this->input->post('game');
                                    $data['game_subscription'] = 'Please purchase game first';

                                    $this->load->view('inner_header', $header);
                                    $this->load->view('timeline', $data);
                                    $this->load->view('inner_footer');
                                }
                            }
                        }
                    }
                }
                //Challenge Receive
            } else {
                /* echo '<pre>'; print_r($header['login']); die; */
                $this->session->set_flashdata('error_message', $header['login']->message);
                redirect("home/signup");
            }
        }
    }

    function statusPagination() {

        //// Check For Logged-In Session
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            //// Define Array & Variables
            $arr_timeline = array();
            $data['showpopup'] = 1;
            $this->load->helper('follow');
            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];
            $max_status_id = @$_POST['status_id'];

            $start = $statuscount;
            //// Get Status of User
            //echo $statuscount; exit;

            $data['timeline'] = $this->user_model->getFollowerStatus($header['login']->user_id, $start, $noofStatus, '', '', $max_status_id, $header['login']->user_id);

            $data['loginuser'] = $header['login']->user_id;
            $data['loginusername'] = $header['login']->username;
            //$data['loggedinUser']=$header['login']->user_id;
            $data['loggedinUserImage'] = $header['login']->image;
            $data['loggedinUsertype'] = $header['login']->usertype;
            //// Load View Page
            $data['sigleRecord'] = 1;
            $data['pagination'] = 1;
            $data['user'] = $this->user_model->getOneUser($data['loginuser']); // confusion
            //

            if (count($data['timeline']) > 0) {
                $this->load->view('addmoretimeline', $data);
            }
        } else {
            //$this->session->set_flashdata('error_message','Session Expired. Please Sign in again');
            $this->session->set_flashdata('error_message', $header['login']->message);
            redirect("home/signup");
        }
    }

    function profileStatusPagination() {

        //// Check For Logged-In Session
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            //// Define Array & Variables
            $arr_timeline = array();
            $data['showpopup'] = 1;

            $this->load->helper('follow');
            $this->load->helper('like');

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];
            $max_status_id = @$_POST['status_id'];

            $id = $_POST['userid'];

            if (!is_numeric($id)) {
                $select = "user_Id, usertype";
                $arr_where['username'] = $id;
                $arr_where['active'] = 1;
                $user = $this->user_model->getOneUserDetails($arr_where, $select);
                if (count($user) > 0) {
                    //echo $this->db->last_query(); die;
                    $id = $user->user_Id;
                }
            }

            $start = $statuscount;
            $data['timeline'] = $this->status_model->getUserActivities($id, $start, $noofStatus, 'DESC', $header['login']->user_id);
            //$data['timeline'] = $this->user_model->getFollowerStatus($header['login']->user_id, $start, $noofStatus, '', '', $max_status_id);


            $data['user'] = $this->user_model->getOneUser($id);

            $data['loginuser'] = $header['login']->user_id;
            $data['loginusername'] = $header['login']->username;
            $data['loggedinUserImage'] = $header['login']->image;
            $data['loggedinUsertype'] = $header['login']->usertype;
            //// Load View Page
            $data['sigleRecord'] = 'activities';
            $data['pagination'] = 1;
            //$data['user'] = $this->user_model->getOneUser($data['loginuser']); // confusion
            //

    		if (count($data['timeline']) > 0) {
                $this->load->view('addmoretimeline', $data);
            }
        } else {
            //$this->session->set_flashdata('error_message','Session Expired. Please Sign in again');
            $this->session->set_flashdata('error_message', $header['login']->message);
            redirect("home/signup");
        }
    }

    function profileRepliesPagination() {


        //print_r($_POST);
        //// Check For Logged-In Session
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            //// Define Array & Variables
            $arr_timeline = array();
            $data['showpopup'] = 1;

            $this->load->helper('follow');
            $this->load->helper('like');

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];
            $max_status_id = @$_POST['status_id'];

            $id = $_POST['userid'];

            if (!is_numeric($id)) {
                $select = "user_Id, usertype";
                $arr_where['username'] = $id;
                $arr_where['active'] = 1;
                $user = $this->user_model->getOneUserDetails($arr_where, $select);
                if (count($user) > 0) {
                    //echo $this->db->last_query(); die;
                    $id = $user->user_Id;
                }
            }

            $start = $statuscount;
            $data['timeline'] = $this->status_model->getUserReplies($id, $start, $noofStatus, 'DESC', $header['login']->user_id);
            //print_r($data['timeline']);
            //$data['timeline'] = $this->user_model->getFollowerStatus($header['login']->user_id, $start, $noofStatus, '', '', $max_status_id);


            $data['user'] = $this->user_model->getOneUser($id);

            $data['loginuser'] = $header['login']->user_id;
            $data['loginusername'] = $header['login']->username;
            $data['loggedinUserImage'] = $header['login']->image;
            $data['loggedinUsertype'] = $header['login']->usertype;
            //// Load View Page
            $data['sigleRecord'] = 'replies';
            $data['pagination'] = 1;

            if (count($data['timeline']) > 0) {

                $this->load->view('addmoretimeline', $data);
            }
        } else {
            //$this->session->set_flashdata('error_message','Session Expired. Please Sign in again');
            $this->session->set_flashdata('error_message', $header['login']->message);
            redirect("home/signup");
        }
    }

    function profileMediaPagination() {
        //print_r($_POST);
        //// Check For Logged-In Session
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $header['login'] = $this->administrator_model->front_login_session();

        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            //// Define Array & Variables
            $arr_timeline = array();
            $data['showpopup'] = 1;

            $this->load->helper('follow');
            $this->load->helper('like');

            //// Fetch Value From Post
            $totalrecord = $_POST['totalrecord'];
            $statuscount = $_POST['statuscount'];
            $noofStatus = $_POST['noofstatus'];
            $max_status_id = @$_POST['status_id'];

            $id = $_POST['userid'];

            if (!is_numeric($id)) {
                $select = "user_Id, usertype";
                $arr_where['username'] = $id;
                $arr_where['active'] = 1;
                $user = $this->user_model->getOneUserDetails($arr_where, $select);
                if (count($user) > 0) {
                    //echo $this->db->last_query(); die;
                    $id = $user->user_Id;
                }
            }

            $start = $statuscount;
            $data['timeline'] = $this->status_model->getUserMedia($id, $start, $noofStatus, 'DESC', $header['login']->user_id);
            //print_r($data['timeline']); die;
            //$data['timeline'] = $this->user_model->getFollowerStatus($header['login']->user_id, $start, $noofStatus, '', '', $max_status_id);


            $data['user'] = $this->user_model->getOneUser($id);

            $data['loginuser'] = $header['login']->user_id;
            $data['loginusername'] = $header['login']->username;
            $data['loggedinUserImage'] = $header['login']->image;
            $data['loggedinUsertype'] = $header['login']->usertype;
            //// Load View Page
            $data['sigleRecord'] = 'media';
            $data['pagination'] = 1;

            if (count($data['timeline']) > 0) {

                $this->load->view('addmoretimeline', $data);
            }
        } else {
            //$this->session->set_flashdata('error_message','Session Expired. Please Sign in again');
            $this->session->set_flashdata('error_message', $header['login']->message);
            redirect("home/signup");
        }
    }

    function pushmessage($pushdetails) {
        $push = array();
        $data['pushmessage'] = $this->pushmessage_model->getForntPushMessage($pushdetails);

        if (count($data['pushmessage']) > 0) {
            $block['pushMessageid'] = $data['pushmessage']->msg_id;
            $block['userid'] = $pushdetails['userid'];

            $blockmsg = $this->pushmessage_model->getbolckmessage($block);

            if (count($blockmsg) == 0) {
                $push = $data['pushmessage'];
            }
        }
        return $push;
    }

    function blockpushmessgae() {
        $login = $this->administrator_model->front_login_session();

        $id = $_POST['id'];
        $push['blockMessage_id'] = '';
        $push['pushMessageid'] = $id;
        $push['userid'] = $login->user_id;
        $push['createdDate'] = date('YmdHis');

        $this->pushmessage_model->saveBlockMessage($push);
    }

    function contact() {
        echo 'In Progress';
    }

    function logout() {
        session_destroy();
        $this->session->unset_userdata('user_logged_in');
        $this->session->sess_destroy();
        unset($_SESSION['lastinsrtId']);
        unset($_SESSION['previous_url']);
        delete_cookie('group');
        redirect(base_url());
    }

    function shareStatus() {
        //
        $header['login'] = $this->administrator_model->front_login_session();

        $share['share_id'] = '';
        $share['statusid'] = $_POST['status_id'];
        $share['shareFromUserId'] = $_POST['postedby'];
        $share['userId'] = $header['login']->user_id;
        $share['createdDate'] = date('YmdHis');
        // new code for share
        $arr_status['status_id'] = $_POST['status_id'];
        $original_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $header['login']->user_id); //// Check Status Exits or not

        $statusDetail['status'] = $original_status->status;
        $statusDetail['originalPosterId'] = $original_status->originalPosterId;
        $statusDetail['status_image'] = $original_status->original_status_image;
        $statusDetail['media_thumb'] = $original_status->media_thumb;
        $statusDetail['createdDate'] = date('YmdHis');
        // start update orignal status's share users ids
        $laststatus = $this->status_model->updateOrignalStatus($_POST['status_id'], $original_status->shareFromUsers, $header['login']->user_id);
        // end
        $laststatus = $this->status_model->saveStatus($statusDetail);
        $arr_status['status_id'] = $laststatus->status_id;
        $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $header['login']->user_id);
        $arr_share['share_id'] = '';
        $arr_share['statusId'] = $user_status->status_id;

        $arr_share['shareFromUserId'] = $header['login']->user_id;
        $arr_share['userId'] = $user_status->userid;
        $arr_share['createdDate'] = date('YmdHis');
        $id = $this->user_model->savesharestatus($arr_share);
        // end new code
        //$id = $this->status_model->savesharestatus($share);

        if ($id != '') {

            /* Start Notification */

            $arr_notice['notification_id'] = '';
            $arr_notice['actionFrom'] = $header['login']->user_id;
            $arr_notice['actionTo'] = $_POST['postedby'];
            $arr_notice['action'] = 'SS';
            $arr_notice['actionString'] = ' shared your status';
            $arr_notice['message'] = '';
            $arr_notice['statusid'] = $_POST['status_id'];
            $arr_notice['challangeid'] = '';
            $arr_notice['active'] = '1';
            $arr_notice['createdDate'] = date('YmdHis');

            $notice_id = $this->notification_model->savenotification($arr_notice);

            /* End Notification */

            $arr_status['status_id'] = $_POST['status_id'];
            $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $header['login']->user_id);
            $arr_like['statusId'] = $user_status->status_id;
            $arr_like['userId'] = $header['login']->user_id;
            $arr_like['active'] = 1;

            $like_status = $this->user_model->getlikestatus($arr_like); //// Check Status Is Liked or not
            if (count($like_status) == 1) {
                $liked = "true";
            } else {
                $liked = "false";
            }
            /* Get Reply Of Same Status */
            $arr_reply['parentStatusid'] = $_POST['status_id'];
            $replys = $this->user_model->getStatusDetails($arr_reply, $row = 1, '', '', 1, 'webservice', '', $header['login']->user_id);
            if ($user_status->userid != $header['login']->user_id) {

                $originalPoster = array(
                    "userid" => $original_status->userid,
                    "username" => $original_status->username,
                    "userimage" => $original_status->userimage
                );
                $shareFromUser = array(
                    "userid" => $header['login']->user_id,
                    "username" => $header['login']->username,
                    "userimage" => base_url() . 'upload/profile/thumbnail/' . $header['login']->image,
                );
                // send push notification code start
                $deviceInfo = $this->user_model->getdeviceToken($original_status->userid);
                if (count($deviceInfo) > 0) {
                    foreach ($deviceInfo as $device) {
                        $deviceToken = $device->key;
                        $deviceType = $device->deviceTypeID;
                        $title = 'My Test Message';
                        $sound = 'default';


                        $title = 'My Test Message';
                        $sound = 'default';

                        $msgpayload = json_encode(array(
                            'aps' => array(
                                'alert' => 'Shared Status: ' . $header["login"]->username . ' shared your status!',
                                "statusid" => $user_status->status_id,
                                "status" => $user_status->status,
                                "userid" => $user_status->userid,
                                "username" => $user_status->username,
                                "name" => isset($user_status->name) ? $user_status->name : '',
                                "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                "createdDate" => $user_status->createdDate,
                                "liked" => $liked,
                                "shared" => "true",
                                "reply" => $replys,
                                "originalPoster" => $originalPoster,
                                "shareFromUser" => $shareFromUser,
                                'type' => 'share',
                                'sound' => $sound
                        )));


                        $message = json_encode(array(
                            'default' => $title,
                            'APNS_SANDBOX' => $msgpayload
                        ));
                        //$message = 'Shared Status: '.$userdetail['username'].' shared your status!';

                        $result = $this->amazonSns($deviceToken, $message, $deviceType);
                    }
                }
            }

            // end

            echo $header['login']->username;
        } else {
            echo 'error';
        }
    }

    function updateSuggestedFollower() {
        $login = $this->administrator_model->front_login_session();
        $user_Id = $_POST['user_Id'];

        $suggested['followuserid'] = $user_Id;
        $suggested['loginuserid'] = $login->user_id;
        $suggested['accept'] = 1;

        $this->user_model->updateSuggestedFollow($suggested);
    }

    function likestatus() {
        //
        $arr_notice = '';
        $login = $this->administrator_model->front_login_session();

        $like['statusid'] = $_POST['id'];
        $like['userid'] = $login->user_id;
        $like['active'] = 1;
        $likestatus = $this->status_model->getlikestatus($like);

        $arr_status['status_id'] = $_POST['id'];
        $arr_status['active'] = 1;

        $statusDetails = $this->user_model->getStatusInformation($arr_status);  //// Get Status Details

        if (count($likestatus) > 0) {
            $this->user_model->deletelike($likestatus->like_id);
            $id = $likestatus->like_id;
            $action = 'unlike';

            /* Start Delete Notification */
            $arr_notice['statusid'] = $statusDetails->status_id;
            $arr_notice['actionFrom'] = $login->user_id;
            $arr_notice['action'] = 'L';
            $arr_notice['active'] = 1;

            $this->notification_model->delete_notification($arr_notice);
            /* End Delete Notification */
        } else {
            $like['createdDate'] = date('YmdHis');
            $id = $this->user_model->savelikestatus($like);  //// Save Like
            $action = 'like';

            /* Start Notification */
            //$arr_status['status_id'] = $_POST['id'];
            //$arr_status['active'] = 1;
            //$statusDetails = $this->user_model->getStatusInformation($arr_status);  //// Get Status Details
            $arr_status1['status_id'] = $_POST['id'];
            $user_status = $this->user_model->getStatusDetails($arr_status1, '', '', '', '', 'webservice', '', $login->user_id);

            $arr_notice['notification_id'] = '';
            $arr_notice['actionFrom'] = $login->user_id;
            $arr_notice['actionTo'] = $user_status->userid;
            $arr_notice['action'] = 'L';
            $arr_notice['actionString'] = ' liked your status';
            $arr_notice['message'] = '';
            $arr_notice['statusid'] = $user_status->status_id;
            $arr_notice['challangeid'] = '';
            $arr_notice['active'] = '1';
            $arr_notice['createdDate'] = date('YmdHis');
            $notice_id = $this->notification_model->savenotification($arr_notice);
            //echo $this->db->last_query();die;

            /* End Notification */
            /* Get All Reply Of Status */
            $arr_reply['parentStatusid'] = $user_status->status_id;
            $replys = $this->user_model->getStatusDetails($arr_reply, $row = 1, '', '', 1, 'webservice', '', $login->user_id);
            /* Ckeck Status is Shared By loggedin User or Originally Posted */
            $arr_shared['userId'] = $login->user_id;
            $arr_shared['statusId'] = $user_status->status_id;
            $shatre_status = $this->user_model->getshareStatus($arr_shared);
            count($shatre_status) > 0 ? $shared = 'true' : $shared = 'false';
            if ($statusDetails->userid != $login->user_id) {
                //code for aws push notification
                $deviceInfo = $this->user_model->getdeviceToken($statusDetails->userid);

                if (count($deviceInfo) > 0) {
                    foreach ($deviceInfo as $device) {


                        $deviceToken = $device->key;
                        $deviceType = $device->deviceTypeID;
                        $title = 'My Test Message';

                        $sound = 'default';
                        $msgpayload = json_encode(array(
                            'aps' => array(
                                'alert' => 'Like Status: ' . $login->username . ' liked your status',
                                "statusid" => $_POST['id'],
                                "status" => $user_status->status,
                                "userid" => $user_status->userid,
                                "username" => $user_status->username,
                                "name" => isset($user_status->name) ? $user_status->name : '',
                                "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                "createdDate" => $user_status->createdDate,
                                "liked" => "true",
                                "shared" => $shared,
                                "reply" => $replys,
                                "originalPoster" => $user_status->userid,
                                "shareFromUser" => "",
                                'type' => 'like',
                                'sound' => $sound,
                        )));


                        $message = json_encode(array(
                            'default' => $title,
                            'APNS_SANDBOX' => $msgpayload
                        ));


                        $result = $this->amazonSns($deviceToken, $message, $deviceType);
                    }
                }
            }

            // end
        }

        echo $action;
    }

    function package() {
        $userdata = $this->session->userdata("business_userdata");
        if ($userdata['package'] == 0) {
            if (!$_POST) {
                $data = '';
                //// Load Header Div Value
                $uri = $this->uri->segment(2);
                $header['subheader'] = $this->subheader('signup', '', $uri);
                $header['showTopSignIn'] = 0;

                $data['package'] = $this->subscription_model->getpackageDetails('*', '', 1);

                $this->load->view('header', $header);
                $this->load->view('package', $data);
                $this->load->view('footer');
            } else {
                $package = $this->input->post('package');
                $userdata['package'] = $package;
                $this->session->set_userdata('business_userdata', $userdata);
                redirect('home/businessLocation');
            }
        } else {
            redirect('home/businessLocation');
        }
    }

    function businessLocation($brnh = NULL) {
        $userdata = $this->session->userdata("business_userdata");
        if ($userdata)
        //// Initilize Default Variables
            $data['country_val'] = '';
        $data['category_val'] = '';
        $data['email'] = $userdata['email'];
        $data['businessCategory'] = '';
        $data['country'] = '';
        $data['address'] = '';
        $data['address2'] = '';
        $data['town'] = '';
        $data['postcode'] = '';
        $data['phone'] = '';
        $data['brnh'] = $brnh;

        $data['business_category'] = $this->user_model->getCategory();           //// Get List of Business Categories
        $data['countries'] = $this->country_model->get_countries();              //// Get List of Country
        //// Form Validation
        $this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email');
        $this->form_validation->set_rules('business_category', 'Business Category', 'trim|required');
        $this->form_validation->set_rules('address', 'Address 1', 'trim|required');
        $this->form_validation->set_rules('town', 'Town', 'trim|required');
        $this->form_validation->set_rules('country', 'Country', 'trim|required');
        $this->form_validation->set_rules('postcode', 'Postcode', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->post('actiontype') == 'previous') {
                $userdata = $this->session->userdata('business_branch');       //// Get Data from Session

                $arr_cnt = count($userdata);
                $i = $arr_cnt - 1;

                $data['email'] = $userdata[$i]['email'];
                $data['category_val'] = $userdata[$i]['businessCategory'];
                $data['country_val'] = $userdata[$i]['country'];
                $data['address'] = $userdata[$i]['address'];
                $data['address2'] = $userdata[$i]['address2'];
                $data['town'] = $userdata[$i]['town'];
                $data['postcode'] = $userdata[$i]['postcode'];
                $data['phone'] = $userdata[$i]['phone'];
                $data['country'] = 1;
                $data['category'] = 1;
            } else {
                $data['country'] = 0;
                $data['category'] = 0;
                $data['country_val'] = $this->input->post('country');
                $data['category_val'] = $this->input->post('business_category');
            }

            //// Load Header Div Value
            $uri = $this->uri->segment(2);
            $header['subheader'] = $this->subheader('signup', $brnh, $uri);
            $header['showTopSignIn'] = 0;

            $this->load->view('header', $header);
            $this->load->view('businesslocation', $data);
            $this->load->view('footer');
        } else {
            if ($brnh == '') {
                $brnhs = 0;
                $url = '';
                $main = 1;
            } else {
                $brnhs = $brnh;
                $url = '/' . $brnh;
                $main = 0;
            }

            $userdata = $this->session->userdata('business_branch');

            $save = array(
                'branch_id' => '',
                'userid' => '',
                'email' => $this->input->post('email'),
                'businessCategory' => $this->input->post('business_category'),
                'country' => $this->input->post('country'),
                'address' => $this->input->post('address'),
                'address2' => $this->input->post('address1'),
                'town' => $this->input->post('town'),
                'postcode' => $this->input->post('postcode'),
                'phone' => $this->input->post('phone'),
                'main_branch' => $main,
                'createdDate' => date('YmdHis')
            );
            address2 == '' ? $complete_address = $save['address'] . ',' . $save['town'] : $complete_address = $save['address'] . ',' . $save['address2'] . ',' . $save['town'];
            $complete_address = urlencode($complete_address);

            $response = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $complete_address . '&sensor=false');
            $json = json_decode($response);
            foreach ($json->results as $res) {
                $geo = $res->geometry;
                $location = $geo->location;

                $latitude = $location->lat;
                $longitude = $location->lng;

                $save['latitude'] = $latitude;
                $save['longitude'] = $longitude;
            }

            if (count($userdata) > 0) {
                $branch = $userdata;
            } else {
                $branch = array();
            }

            $branch[$brnhs] = $save;

            $this->session->set_userdata('business_branch', $branch);
            $userdata = $this->session->userdata('business_branch');

            redirect('home/information' . $url);
        }
    }

    function information($brnh = NULL) {

        $data['website'] = '';
        $data['description'] = '';
        $data['peopleVisit'] = '';
        $data['brnh'] = $brnh;
        // 		/$userdata=$this->session->userdata('business_userdata');

        $sess_business = $this->session->userdata('business_branch');

        $this->form_validation->set_rules('website', 'Website', 'trim');
        $this->form_validation->set_rules('description', 'Description', 'trim|required');
        $this->form_validation->set_rules('peopleVisit', 'Pepple visit', 'trim');

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->post('actiontype') == 'previous') {
                $userdata = $this->session->userdata('business_branch');

                $arr_cnt = count($userdata);
                $i = $arr_cnt - 1;
                $data['website'] = $userdata[$i]['website'];
                $data['description'] = trim($userdata[$i]['description']);
                $data['peopleVisit'] = $userdata[$i]['peopleVisit'];
            }
            //// Load Header Div Value
            $uri = $this->uri->segment(2);
            $header['subheader'] = $this->subheader('signup', $brnh, $uri);
            $header['showTopSignIn'] = 0;

            $data['newbrnh'] = $brnh + 1;

            $this->load->view('header', $header);
            $this->load->view('basicinformation', $data);
            $this->load->view('footer', $header);
        } else {

            if ($brnh == '') {
                $brnhs = 0;
                $url = '';
            } else {
                $brnhs = $brnh;
                $url = '/' . $brnh;
            }

            $userdata = $this->session->userdata('business_branch');

            $branch = $userdata;

            $branch [$brnhs]['website'] = $this->input->post('website');
            $branch [$brnhs]['peopleVisit'] = $this->input->post('peopleVisit');
            $branch [$brnhs]['description'] = $this->input->post('description');

            $this->session->set_userdata('business_branch', $branch);

            redirect('home/payment' . $url);
        }
    }

    function confirmation($brnh = NULL) {
        $data['err_description'] = ''; //// Initilize An Array
        $data['brnh'] = $brnh;
        $sess_business = $this->session->userdata('business_branch');

        $brnh ? $i = $brnh : $i = 0;

        $data['newbrnh'] = $brnh + 1;

        $data['description'] = $sess_business[$i]['description'];

        $country_id = $sess_business[0]['country'];  //// Get Country of Main Business
        $country = $this->country_model->getcountry('country_id', $country_id, '1');  //// Get Details of Main Country

        /* Create Array to Get Charges For Same Country */
        $arr_charge['name'] = 'Branch';
        $arr_charge['active'] = 1;
        $charges = $this->user_model->getcharges($arr_charge, $row = ''); //// Get Branch Charges

        if ($country->country_name == 'UK') {
            $data['branchCost'] = $charges->amount;    //// Branch Charge
            $data['currencySymbol'] = '&#163;';            //// Currency
        } else {
            $data['branchCost'] = $charges->amount_usd;  //// Branch Charge
            $data['currencySymbol'] = '&#36;';            //// Currency
        }


        $this->form_validation->set_rules('description', 'Description', 'trim|required');

        $data['brnh'] = $brnh;

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->post('actiontype') == 'previous') {
                $userdata = $this->session->userdata('business_branch');
                $data['description'] = trim($userdata[$i]['description']);

                $data['err_description'] = 1;
            }
            $uri = $this->uri->segment(2);
            $header['subheader'] = $this->subheader('signup', $brnh, $uri);
            $header['showTopSignIn'] = 0;

            $this->load->view('header', $header);
            $this->load->view('confirmation', $data);
            $this->load->view('footer');
        } else {
            if ($brnh == '') {
                $brnhs = 0;
                $url = '';
            } else {
                $brnhs = $brnh;
                $url = '/' . $brnh;
            }

            $userdata = $this->session->userdata('business_branch');
            $branch = $userdata;
            $branch [$brnhs]['description'] = $this->input->post('description');

            $this->session->set_userdata('business_branch', $branch);

            redirect('home/payment' . $url);
        }
    }

    function payment($brnh = NULL) {
        //// Initilize Variables
        $data['invalid_card'] = '';
        $data['invalid_exp_date'] = '';
        $data['promo_error'] = '';
        $data['vatCharge'] = '0';
        $data['promo_amount'] = '';

        $exp_validation = '';
        $userdata = $this->session->userdata("business_userdata");

        $sess_business = $this->session->userdata('business_branch');    //// Get Business Branch Session
        $arr_cnt = count($sess_business);
        $data['brnh'] = $brnh;

        //// Form Validation
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('card_number', 'Card Number', 'trim|required');
        $this->form_validation->set_rules('promocode', 'Promo Code', 'trim');
        $this->form_validation->set_rules('card_type', 'Card Type', 'trim|required');
        $this->form_validation->set_rules('month', 'Month', 'trim|required');
        $this->form_validation->set_rules('year', 'Year', 'trim|required');
        $this->form_validation->set_rules('code', 'Security Code', 'trim|required');
        //// Validation For Credit Card Number and Card Type
        if ($this->input->post('card_number') != '') {
            $varas = validate_card($this->input->post('card_number'), $this->input->post('card_type'));
            $data['invalid_card'] = $varas;

            if ($varas != 1) {
                $exp_validation = 1;
            } else {
                $data['invalid_card'] = '';
            }
        }
        //// Validation For Credit Card Expiry Month and Year
        if ($this->input->post('month') != '' && $this->input->post('year')) {
            if (!card_expiry_valid($this->input->post('month'), $this->input->post('year'))) {
                $data['invalid_exp_date'] = 'Invalid Expiry Date';
                $exp_validation = 1;
            }
        }

        $data['no_of_branch'] = $arr_cnt;                 //// Get No of Branch of main Business


        $country_id = $sess_business[0]['country'];  //// Get Country of Main Business
        $country = $this->country_model->getcountry('country_id', $country_id, '1');

        $arr_charge['active'] = 1;
        $arr_charge['name'] = 'Branch';
        $charges = $this->user_model->getcharges($arr_charge, $row = ''); //// Get Branch Charges

        $arr_pachage['package_id'] = $userdata['package'];
        $packageDetails = $this->subscription_model->getpackageDetails('*', $arr_pachage, $row = ''); //// Get Branch Charges

        if ($country->country_name == 'UK') {
            $branchCost = $packageDetails->price_gbp;
            $data['currencySymbol'] = '&#163;';
            $vat = $charges->vat;
        } else {
            $branchCost = $packageDetails->price_usd;
            $data['currencySymbol'] = '&#36;';
            $vat = 0;
        }
        /* echo $branchCost; die; */
        $data['branchCost'] = $branchCost;
        /* echo "One branchCost: ".$branchCost.'</br>'; */

        $discount = $charges->discount;   //// Per Branch Discount in cost
        //echo "Branch Cost :".$branchCost."</br>";
        $cost_total_branch = $branchCost * $data['no_of_branch'];

        if ($data['no_of_branch'] > 1) {
            $discount_all_branch = ($cost_total_branch * $discount) / 100;
            /* echo $cost_total_branch; die; */
            $final_branch_cost = $cost_total_branch - $discount_all_branch;

            $total_amount = $final_branch_cost;
        } else {
            $final_branch_cost = $cost_total_branch;
            $total_amount = $cost_total_branch;
        }

        /* 	echo "vat : ".$vat."</br>"; */
        if ($vat != 0) {
            $vatCharge = ($branchCost * $vat) / 100;
            $data['vatCharge'] = number_format($vatCharge, 2);
            /* 	echo " Vat Charges : ".$data['vatCharge'].'</br>'; */
            $total_amount = $total_amount + $data['vatCharge'];
        }

        /* echo "total_amount : ".$total_amount.'</br>'; */

        if ($this->input->post('promocode') != '') {
            $promo['promo_code'] = $this->input->post('promocode');
            $promo['active'] = 1;

            $promo = $this->promocode_model->getpromocode($promo);
            /* echo '<pre>'; print_r($promo ); die; */
            $promoValue = $promo->value;
            $promovaluetype = $promo->type;
            $currdate = date('Y-m-d');


            if ($currdate >= $promo->valid_from && $currdate <= $promo->valid_till) {
                if ($promovaluetype == '%') {
                    $promo_amount = ($total_amount * $promoValue) / 100;
                    $data['promo_amount'] = $promo_amount;
                    $total_amount = $total_amount - $promo_amount;
                }
            } else {
                $data['promo_error'] = 'Invalid Promo code';
                $exp_validation = 1;
            }
        }

        $data['branchcost'] = $final_branch_cost;
        //$data['totalcost']=round($total_amount,2);   ///// Round Off the Value
        $data['totalcost'] = number_format($total_amount, 2);

        $arr_card_type['active'] = 1;
        $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);
        /* die; */
        if ($this->form_validation->run() == FALSE || $exp_validation == 1) {
            $uri = $this->uri->segment(2);
            $header['subheader'] = $this->subheader('signup', $brnh, $uri);   //// Get Page Header Tabs
            $header['showTopSignIn'] = 0;

            $this->load->view('header', $header);
            $this->load->view('payment', $data);
            $this->load->view('footer');
        } else {
            //// If all Data  Are Correct

            $userdata = $this->session->userdata('business_userdata');
            $firstname = $userdata['firstname'];
            $lastname = trim($userdata['lastname']);
            $email = $userdata['email'];
            $country = $sess_business[0]['country'];
            $country = $this->country_model->getcountry('country_id', $country, 1);
            if ($country->country_code == 'GB') {
                $code = 1;
            } else {
                $code = 0;
            }

            $onecurency = $this->country_model->getcurrency($code);
            $curency = $onecurency->currency_code;


            $card_no = $this->input->post('card_number');

            $exp_month = $this->input->post('month');
            $exp_year = $this->input->post('year');

            $cvv_no = $this->input->post('code');
            $address = $sess_business[0]['address'];
            $city = $sess_business[0]['town'];
            $zip_code = $sess_business[0]['postcode'];
            $amount = $data['totalcost'];
            $currency = $curency;
            $card_type = $this->input->post('card_type');
            if ($card_type == 'Master Card') {
                $card_type = 'MasterCard';
            }

            $paymentType = urlencode('Sale');    // or 'Sale'
            $firstName = urlencode($firstname);
            $lastName = urlencode($lastname);
            $creditCardType = urlencode($card_type);
            $creditCardNumber = urlencode($card_no);
            $expDateMonth = $exp_month;
            // Month must be padded with leading zero
            $padDateMonth = urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));

            $expDateYear = urlencode($exp_year);
            $cvv2Number = urlencode($cvv_no);
            $address1 = urlencode($address);
            $city = urlencode($city);
            $state = urlencode($country->country_code);
            $zip = urlencode($zip_code);
            $country = urlencode($country->country_code);    // US or other valid country code
            $amount = urlencode($amount);
            $currencyID = urlencode($curency);       // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
            $email = urlencode($email);
            //// CREATE AN STRING
            $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                    "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$email" .
                    "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";

            //// SEND REQUEST TO PAYPAL
            $httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);
            /* echo '<pre>'; print_r($httpParsedResponseAr); die; */
            if ($httpParsedResponseAr['ACK'] == 'Success') {
                $userdata = $this->session->userdata('business_userdata');
                //echo '<pre>'; print_r($userdata); die;
                $date = date('YmdHis');
                $user['user_Id'] = '';
                $user['firstname'] = $userdata['firstname'];
                $user['lastname'] = $userdata['lastname'];
                $user['email'] = $userdata['email'];
                $user['businessName'] = $userdata['businessName']; //viv
                $user['username'] = $userdata['username'];
                $user['password'] = $userdata['password'];
                $user['active'] = 1;
                $user['usertype'] = $userdata['usertype'];
                $user['image'] = $userdata['image'];
                $user['firstLogin'] = $date;
                $user['createdDate'] = $date;

                $user['package'] = $userdata['package'];
                $arr_package['package_id'] = $user['package'];
                $package = $this->subscription_model->getpackageDetails('*', $arr_package);
                $last_id = $this->user_model->insertsignup($user);
                $v = 0;
                foreach ($sess_business as $session) {
                    $v == 0 ? $main = 1 : $main = 0;

                    //Get Geo code
                    $geoCodeAddress = $session['address'] . ',' . $session['town'];
                    $location = $this->GetGeoCode($geoCodeAddress);
                    if (isset($location) && $location != "") {
                        $latitude = $location['latitude'];
                        $longitude = $location['longitude'];
                    } else {
                        $latitude = "";
                        $longitude = "";
                    }

                    $branch['branch_id'] = '';
                    $branch['userid'] = $last_id;
                    $branch['email'] = $session['email'];
                    $branch['businessCategory'] = $session['businessCategory'];
                    $branch['country'] = $session['country'];
                    $branch['address'] = $session['address'];
                    $branch['address2'] = $session['address2'];
                    $branch['latitude'] = $latitude;
                    $branch['longitude'] = $longitude;
                    $branch['town'] = $session['town'];
                    $branch['postcode'] = $session['postcode'];
                    $branch['phone'] = $session['phone'];
                    $branch['website'] = $session['website'];
                    $branch['peopleVisit'] = $session['peopleVisit'];
                    $branch['description'] = $session['description'];
                    $branch['main_branch'] = $main;
                    $branch['active'] = 1;
                    $branch['coinDate'] = $session['createdDate'];
                    $branch['createdDate'] = $session['createdDate'];

                    $branchid = $this->user_model->savebusinessbranch($branch);
                    $v++;

                    $userCoins = array(
                        'userid' => $last_id,
                        'coins' => $package->coins,
                        'coins_type' => 8,
                        'game_id' => 0,
                        'businessid' => $branchid,
                        'actionType' => 'add',
                        'createdDate' => date('YmdHis')
                    );
                    $this->score_model->insertCoins($userCoins);

                    $coins = array(
                        'userid' => $last_id,
                        'coins' => $package->coins,
                        'branchid' => $branchid
                    );
                    $this->score_model->signupCoins($coins);
                }

                $payment['payment_id'] = '';
                $payment['user_id'] = $last_id;
                $payment['purchasedOn'] = date('YmdHis');
                $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
                //$payment['currency']=urldecode($httpParsedResponseAr['CURRENCYCODE']);
                $payment['currency'] = $data['currencySymbol'];
                $payment['transationId'] = $httpParsedResponseAr['TRANSACTIONID'];
                $payment['paymentInfo'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '////CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
                $payment['isActive'] = 1;
                $payment['IsDelete'] = 0;
                $payment['createdDate'] = date('YmdHis');

                $last_payment_id = $this->payment_model->savepayment($payment);
                /* Coins For Business User */

                //// SEND  EMAIL START
                $this->emailConfig();   //Get configuration of email
                //// GET EMAIL FROM DATABASE

                $email_template = $this->email_model->getoneemail('business_signup');

                //// MESSAGE OF EMAIL
                $messages = $email_template->message;

                $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
                $appstore = base_url() . '/assets/template/frontend/img//appstore.gif';
                $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

                //// replace strings from message
                $messages = str_replace('{Username}', $userdata['username'], $messages);
                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                $messages = str_replace('{Google_Image}', $googleplay, $messages);

                //// FROM EMAIL
                $this->email->from('hello@marketmyapp.co', 'Hurree');
                $this->email->to($userdata['email']);
                $this->email->subject($email_template->subject);
                $this->email->message($messages);
                $this->email->send();    ////  EMAIL SEND

                $userDetails = $this->user_model->getOneUser($last_id);

                $this->session->set_userdata('logged_in', $userDetails);

                redirect('timeline');
            } else {
                redirect('home/paymentstatus/' . $httpParsedResponseAr['L_LONGMESSAGE0']);
            }
        }
    }

    function PPHttpPost($methodName_, $nvpStr_) {
//echo $methodName_;
//echo '<br>'.$nvpStr_;
        //echo $nvpStr_; exit;
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

        return $httpParsedResponseAr; //exit;
    }

    function paymentstatus($paymentid) {

        if (is_numeric($paymentid)) {
            $payment['payment_id'] = $paymentid;
            $payment['isActive'] = 1;
            $payment['isDelete'] = 0;
            $payment = $this->payment_model->getpayment($payment, $row = '');

            $data['userid'] = $payment->user_id;
            $data['status'] = 'Success';
            $data['transation_id'] = $payment->transationId;
            $data['message'] = '';
            $data['amount'] = $payment->currency . ' ' . $payment->amount;
        } else {
            $data['status'] = 'Failure';
            $data['transation_id'] = '';
            $data['message'] = urldecode($paymentid);
            $data['amount'] = '';
        }

        $data['showTopSignIn'] = 0;
        $header['subheader'] = $this->subheader();
        $this->load->view('header', $header);
        $this->load->view('payment_status', $data);
        $this->load->view('footer');
    }

    function userlogin($userid, $method = '') {
        //// Destroy Business Sign Up Session
        $this->session->unset_userdata('business_branch');
        $this->session->unset_userdata('business_userdata');

        $userid = $this->user_model->getOneUser($userid);   //// Get User Details

        $sess = array();   //// Initilize An Array
        $sess = (object) array(
                    'user_Id' => $userid->user_Id,
                    'username' => $userid->username,
                    'active' => $userid->active,
                    'email' => $userid->email,
                    'usertype' => $userid->usertype,
                    'accessLevel' => $userid->accesslevel,
                    'firstname' => $userid->firstname,
                    'lastname' => $userid->lastname,
                    'image' => $userid->image
        );

        $this->session->set_userdata('logged_in', $sess);   //// Create Login Session
        redirect('timeline');
    }

    function privacy_policy() {

        $this->load->view('privacy');
    }

    function termsofservice() {
        $this->load->view('termsofservice');
    }

    function getintouch() {
        $this->load->view('getintouch');
    }

    function sendMessage() {

        $login = $this->administrator_model->front_login_session();
        $message = $this->input->post('msg');

        $seconduserid = $this->input->post('seconduserid');
        if (!is_numeric($seconduserid)) {

            /* Get Second UserId  */
            $var = explode(' ', $seconduserid);

            $username = str_replace('@', '', $var[0]);
            $arr_user['username'] = $username;
            $arr_user['active'] = 1;
            $select = "user_Id";

            $user = $this->user_model->getOneUserDetails($arr_user, $select);
            $seconduserid = $user->user_Id;
        }
        $user = $this->user_model->getOneUser($seconduserid);

        $data = array(
            'message_from' => $login->user_id,
            'userid' => $seconduserid,
            'username' => $user->username,
            'message' => $message,
            'isDelete' => 0,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );

        $messageId = $this->message_model->add($data);
        $blok['secondUserId'] = $login->user_id;
        $blok['userId'] = $seconduserid;

        $this->message_model->removeDeletedMessage($blok);
        $row = array();

        $row['messageId'] = $messageId;
        $row['userImage'] = $login->image;
        echo json_encode($row);
        die;
    }

    function writeMessage() {
        $baseurl = base_url();
        $login = $this->administrator_model->front_login_session();
        $data['user'] = $login;
        $header['login'] = $this->administrator_model->front_login_session();
        $data['loggedinuser'] = $login->user_id;
        $this->load->view('writeMessage', $data);
    }

    /**
     * Function to Load conversion between two users
     */
    function viewMessage($seconduserid = NULL) {
        $loadview = '1';
        $data = '';
        $data['userBlock'] = "0";
        $data['readonly'] = '';
        // Get URI Segment
        $seconduserid = $this->uri->segment(3);

        if ($_POST) {
            $seconduserid = $_POST['seconduserid'];
            $loadview = $_POST['loadview'];
        }
        // Get Loggin Session
        $baseurl = base_url();
        $login = $this->administrator_model->front_login_session();

        if ($login->true == 1 && $login->accesslevel != '') {
            // Get All Messages From Perticulat User
            $data['userid'] = $login->user_id;
            //messages between two users
            $data['messageDetails'] = $this->message_model->getReceivedMessage($data['userid'], $seconduserid);

            // Get Second User Details
            $data['seconduser'] = $this->user_model->getOneUser($seconduserid);


            if ($loadview == 1) {
                //url last message id
                $messageid = $_GET['messageid'];
                if ($messageid != '') {
                    // Get Message Details
                    $message['message_id'] = $messageid;
                    $message['isDelete'] = 0;
                    //message details of message which is coming in url
                    $onemessage = $this->message_model->getOnemessage($message);

//                    echo "</pre>";
//            print_r( $onemessage);
//            echo "</pre>"; die;
                    //userid of url message id creator

                    $message_from = ($login->user_id == $onemessage->userid) ? $onemessage->message_from : $onemessage->userid;
//                    $userid = $onemessage->userid;
//                    $message_from = $onemessage->message_from;
                    $arr_msg['is_new'] = 1;
                    $this->message_model->updateNew($login->user_id, $message_from, $arr_msg);
                }

                $data['loggedinuser'] = $login->user_id;
                $data['seconduserid'] = $seconduserid;

                // Get Block Details
                $arr_block['userid'] = $data['userid'];
                $arr_block['block_user_id'] = $seconduserid;
                $blockDetails = $this->user_model->getblockUser($arr_block, 1);
                if (count($blockDetails) > 0) {
                    $data['userBlock'] = "1";
                    $data['readonly'] = 'readonly="readonly"';
                }

                //on view the message set the recent flag/read unread accordingly

                $this->load->view('viewMessage', $data);
            } else {
                echo json_encode($data['messageDetails']);
            }
        } else {
            $this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
            redirect("home/signup");
        }
    }

    function getallusername() {
        $arr_return = array();
        //// Get LoggedIn User details
        $baseurl = base_url();

        $login = $this->administrator_model->front_login_session();
        $data['user'] = $login;

        $userid = $login->user_id;

        $username = $_POST['username'];

        if (strstr($_POST['username'], "@") != '') {
            $hashtag = 1;   //// For Username
        } else {

            $hashtag = 2;  //// For HashTag
        }
        if ($hashtag == 1) {
            $username = str_replace("@", "", $_POST['username']);

            if ($username != '') {
                $limit = 5;
                $user = $this->user_model->getuserlist($username, $limit);

                $return = array(); //$r->user_Id
                $final = array();
                $i = 0;
                foreach ($user as $r) {
                    if ($r->usertype == 1 || $r->usertype == 4) {
                        $name = $r->firstname . ' ' . $r->lastname;
                    }
                    if ($r->usertype == 2 || $r->usertype == 5) {
                        $name = $r->businessName;
                    }

                    $arr = array(
                        'userid' => $r->user_Id,
                        'image' => $r->image,
                        'name' => $name,
                        'username' => $r->username
                    );
                    $i++;

                    $arr_return[] = $arr;
                }
            }
        } else {
            $limit = 5;
            $username = str_replace("#", "", $username);
            if ($username != '') {
                $hashtag = $this->user_model->getHashlist($username, $limit);
                //echo $this->db->last_query();

                $arrOneHash = array();
                $arr_Hash = array();
                $i = 0;
                foreach ($hashtag as $hash) {
                    $arrHash = explode(",", $hash->hasgTag);

                    foreach ($arrHash as $arhash) {
                        if (strstr($arhash, $username) != '') {
                            //array_push($arrOneHash,$arhash);
                            $arrOneHash[$i] = ucfirst($arhash);
                            $i++;
                        }
                    }
                }
                $arr_return = array_unique($arrOneHash);

                sort($arr_return);
            }
        }
        echo json_encode($arr_return);
    }

    function gethashtags($text) {

        //Match the hashtags
        preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $text, $matchedHashtags);
        $hashtag = '';
        // For each hashtag, strip all characters but alpha numeric
        if (!empty($matchedHashtags[0])) {
            foreach ($matchedHashtags[0] as $match) {
                $hashtag .= preg_replace("/[^a-z0-9]+/i", "", $match) . ',';
            }
        }
        //to remove last comma in a string
        echo rtrim($hashtag, ',');
    }

    function followuser() {

        $pagename = $_POST['pagename'];
        $login = $this->administrator_model->front_login_session();

        $follow['userId'] = $login->user_id;
        $follow['followUserId'] = $_POST['id'];

        $follow['active'] = 1;
        $followuser = $this->user_model->getfollowinguserid($follow);
        $cntFlo1 = count($followuser);

        if (count($followuser) == 0) {
            $follow['follow_id'] = '';
            $follow['createdDate'] = date('YmdHis');
            $this->user_model->savefollow($follow, '', '');

            /* Start Notification */
            $arr_notice['notification_id'] = '';
            $arr_notice['actionFrom'] = $login->user_id;
            $arr_notice['actionTo'] = $_POST['id'];
            $arr_notice['action'] = 'F';
            $arr_notice['actionString'] = ' followed you!';
            $arr_notice['message'] = '';
            $arr_notice['statusid'] = '';
            $arr_notice['challangeid'] = '';
            $arr_notice['active'] = '1';
            $arr_notice['createdDate'] = date('YmdHis');
            $notice_id = $this->notification_model->savenotification($arr_notice);

            /* End Notification */
            if ($pagename == 'user_profile1') {
                $usertype = $_POST['usertype'];
                if ($usertype == 'consumer') {
                    $type = 1;
                } else {
                    $type = 2;
                }
                $consumer = $this->user_model->getpeopletofollow($login->user_id, $type, 1, '', 'row');
                $str = $this->db->last_query();


                if (count($consumer) > 0) {
                    $appenddata[] = $consumer->user_Id;
                    if ($usertype == 'consumer') {
                        $appenddata[] = '<li id="consumer' . $consumer->user_Id . '" class="consumerFollowli"><div class="propic"><img src="' . base_url() . 'upload/profile/thumbnail/' . $consumer->image . '" alt=""></div><div class="info"><p><strong>' . trim($consumer->firstname) . " " . trim($consumer->lastname) . '</strong>@' . ucfirst($consumer->username) . '</p><a href="javascript:void(0);" id="fl_' . $consumer->user_Id . '" onclick="followuser(' . $consumer->user_Id . ')">Follow</a></div></li><input type="hidden" id="pagename' . $consumer->user_Id . '" class="consumer" value="user_profile1" />';
                    } else {
                        $appenddata[] = '<li id="business' . $consumer->user_Id . '" class="businessFollowli"><div class="propic"><img src="' . base_url() . 'upload/profile/thumbnail/' . $consumer->image . '" alt=""></div><div class="info"><p><strong>' . trim($consumer->firstname) . " " . trim($consumer->lastname) . '</strong>@' . ucfirst($consumer->username) . '</p><a href="javascript:void(0);" id="fl_' . $consumer->user_Id . '" onclick="followuser(' . $consumer->user_Id . ')">Follow</a></div></li><input type="hidden" id="pagename' . $consumer->user_Id . '" class="business" value="user_profile1" />';
                    }
                    $appenddata [] = $str;
                } else {
                    echo $appenddata = '';
                }
                echo json_encode($appenddata);
            } else {
                echo json_encode('followed');
            }
        } else {
            $this->user_model->deletefollow($followuser->follow_id);

            /* Start Delete Notification */
            $arr_notice['actionTo'] = $_POST['id'];
            $arr_notice['actionFrom'] = $login->user_id;
            $arr_notice['action'] = 'F';
            $arr_notice['active'] = 1;

            $this->notification_model->delete_notification($arr_notice);
            /* End Delete Notification */
            $appenddata[0] = 'unfollowed';
            echo json_encode($appenddata);
        }
    }

    function stauspostpopup() {
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $userstatus = $_POST['user_status'];
        $enteredUsername = $_POST['enteredUsername'];
        $enteredHash = $_POST['enteredHash'];

        $matches = array();
        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details

        $logginusername = $login->username;
        $videoThumb = '';
        if (@$_FILES['image']['size'] > 0) {
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $login->user_id;
            $mediumImagePath = 'upload/status_image/medium/' . $login->user_id;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }
            $tmp_name = $_FILES["image"]["tmp_name"];

            $name = mktime() . $_FILES["image"]["name"];
            move_uploaded_file($tmp_name, "$path/$name");
            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            // image resize in thumbnail size in thumbnail directory
            if (in_array($ext, $extionArray)) {
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['source_image'] = $path . '/' . $name;
                $config['new_image'] = $mediumImagePath . '/' . $name;

                $config['maintain_ratio'] = TRUE;
                $config['width'] = 400;
                $config['height'] = 350;
                $this->image_lib->initialize($config);
                $rtuenval = $this->image_lib->resize();
                $this->image_lib->clear();
            } else {
                $videoThumbPath = 'upload/videoThumb' . '/' . $login->user_id;
                if (!is_dir($videoThumbPath)) {
                    if (mkdir($videoThumbPath, 0777, true)) {
                        $thumbPath = $videoThumbPath;
                    } else {
                        $thumbPath = $videoThumbPath;
                    }
                } else {
                    $thumbPath = $videoThumbPath;
                }
                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                $videothumb = strtotime(date('Ymdhis')) . 'thumb.jpg';
                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . '  -deinterlace -an -ss 5 -f mjpeg -t 1 -r 1 -y -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;
                // echo $cmd; exit;
                exec($cmd . ' ' . '2>&1', $out, $res);

                $videoThumb = $login->user_id . '/' . $videothumb;
            }

            $status_image = $login->user_id . '/' . $name;

            // Image Upload End
        } else {

            $status_image = '';
        }

        /* For HashTag */
        $arr_hashtag = explode(",", $enteredHash);
        $arr_uniquehashtag = array_unique(array_map("StrToLower", $arr_hashtag));
        //print_r($arr_uniqueusername);
        $enteredHash = implode(",", $arr_uniquehashtag);

        /* For Username  */
        $arr_username = explode(",", $enteredUsername);

        $arr_uniqueusername = array_unique(array_map("StrToLower", $arr_username));

        $enteredUsername = implode(",", $arr_uniqueusername);

        /* Array of Status */

        $status['status_id'] = '';
        $status['userid'] = $login->user_id;
        $status['status_image'] = $status_image;
        $status['createdDate'] = date('YmdHis');
        $status['usermentioned'] = $enteredUsername;
        $status['media_thumb'] = $videoThumb;
        $statusid = $this->user_model->saveUserStatus($status);   //// save new status into databas
        //echo $this->db->last_query();die;
        $arr_status['status'] = str_replace('changeparameter(New);', "changeparameter(" . $statusid . ");", $userstatus);
        $arr_status['status_id'] = $statusid;
        $statusid = $this->user_model->saveUserStatus($arr_status);
        $newstatus = strip_tags($userstatus);
        preg_match_all('/#([^\s]+)/', $newstatus, $matches);
        if (count($matches) > 0) {

            $this->user_model->saveUserHashtag($matches[1]);
        }
        //// update last inserted status

        /* Send Notification to Users Whom mentioned in status */
        $arr = array();
        if ($enteredUsername != '') {

            foreach ($arr_uniqueusername as $oneuser) {

                //if($oneuser!=$login->username)

                if (strcasecmp($logginusername, $oneuser) !== 0) {
                    $arr_oneUser['username'] = $oneuser;
                    $arr_oneUser['active'] = 1;
                    $oneuserDetails = $this->user_model->getOneUserDetails($arr_oneUser, 'user_Id');

                    $arr_notice['notification_id'] = '';
                    $arr_notice['actionFrom'] = $login->user_id;
                    $arr_notice['actionTo'] = $oneuserDetails->user_Id;
                    $arr_notice['action'] = 'NM';
                    $arr_notice['actionString'] = ' mentioned you!';
                    $arr_notice['message'] = '';
                    $arr_notice['statusid'] = $statusid;
                    $arr_notice['challangeid'] = '';
                    $arr_notice['active'] = '1';
                    $arr_notice['createdDate'] = date('YmdHis');


                    $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database

                    $ch = curl_init();
                    $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
                    curl_setopt($ch, CURLOPT_URL, $to);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    $arr[] = "@" . $oneuser;
                }
                // }
            }
            $enteredNewUsername = implode(" ", $arr);

            $data['loginusername'] = $login->username;
            $data['loggedinUserImage'] = $login->image;
            $data['timeline'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $statusid);
            $data['enteredUsername'] = $enteredNewUsername;
            $data['sigleRecord'] = '1';
            $data['loginuser'] = $login->user_id;
            $data['loggedinUsertype'] = $login->usertype;
            $data['user'] = $this->user_model->getOneUser($login->user_id);
            $data['pagination'] = 1;

            $this->load->view('addmoretimeline', $data);
        }
    }

    function stauspostpopupUserProfile() {
        /* echo '<pre>'; print_r($_POST); die; */
        $userstatus = $_POST['user_status'];
        $enteredUsername = $_POST['enteredUsername'];
        $enteredHash = $_POST['enteredHash'];
        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $logginusername = $login->username;
        if (@$_FILES['image']['size'] > 0) {

            $uploads_dir = 'upload/status_image/full';
            $tmp_name = $_FILES["image"]["tmp_name"];
            $name = mktime() . $_FILES["image"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            // image resize in thumbnail size in thumbnail directory
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['source_image'] = "upload/status_image/full/" . $name;
            $config['new_image'] = 'upload/status_image/medium/' . $name;

            $config['maintain_ratio'] = TRUE;
            $config['width'] = 400;
            $config['height'] = 350;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();

            $status_image = $name;

            // Image Upload End
        } else {

            $status_image = '';
        }

        /* For HashTag */
        $arr_hashtag = explode(",", $enteredHash);
        $arr_uniquehashtag = array_unique(array_map("StrToLower", $arr_hashtag));
        //print_r($arr_uniqueusername);
        $enteredHash = implode(",", $arr_uniquehashtag);

        /* For Username  */
        $arr_username = explode(",", $enteredUsername);
        $arr_uniqueusername = array_unique(array_map("StrToLower", $arr_username));
        //print_r($arr_uniqueusername);
        $enteredUsername = implode(",", $arr_uniqueusername);

        /* Array of Status */
        $status['status_id'] = '';
        $status['userid'] = $login->user_id;
        $status['status_image'] = $status_image;
        $status['createdDate'] = date('YmdHis');
        $status['usermentioned'] = $enteredUsername;
        $status['hasgTag'] = $enteredHash;

        $statusid = $this->user_model->saveUserStatus($status);   //// save new status into database
        //echo $this->db->last_query();die;
        $arr_status['status'] = str_replace('changeparameter(New);', "changeparameter(" . $statusid . ");", $userstatus);
        $arr_status['status_id'] = $statusid;
        $statusid = $this->user_model->saveUserStatus($arr_status);   //// update last inserted status

        /* Send Notification to Users Whom mentioned in status */

        if ($enteredUsername != '') {
            foreach ($arr_uniqueusername as $oneuser) {
                //if($oneuser!=$login->username)

                if (strcasecmp($logginusername, $oneuser) !== 0) {
                    $arr_oneUser['username'] = $oneuser;
                    $arr_oneUser['active'] = 1;
                    $oneuserDetails = $this->user_model->getOneUserDetails($arr_oneUser, 'user_Id');

                    $arr_notice['notification_id'] = '';
                    $arr_notice['actionFrom'] = $login->user_id;
                    $arr_notice['actionTo'] = $oneuserDetails->user_Id;
                    $arr_notice['action'] = 'NM';
                    $arr_notice['actionString'] = ' mentioned you!';
                    $arr_notice['message'] = '';
                    $arr_notice['statusid'] = $statusid;
                    $arr_notice['challangeid'] = '';
                    $arr_notice['active'] = '1';
                    $arr_notice['createdDate'] = date('YmdHis');

                    $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                }
                $arr[] = "@" . $oneuser;
            }
        }
        $enteredNewUsername = implode(" ", $arr);

        $data['loggedinUsername'] = $login->username;
        $data['loggedinUserImage'] = $login->image;
        $data['timeline'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $statusid);
        $data['enteredUsername'] = $enteredNewUsername;
        $data['sigleRecord'] = '1';
        $data['loginuser'] = $login->user_id;
        $data['loggedinUsertype'] = $login->usertype;

        $this->load->view('addmoretimeline1', $data);
    }

    //Created by Hassan 25-11-2015
    function beaconStatusPost() {
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $userstatus = $_POST['user_status'];
        $beaconId = $_POST['beaconId'];
        $coins = $_POST['coins'];
        $minAge = $_POST['minAge'];
        $maxAge = $_POST['maxAge'];
        $gender = $_POST['gender'];
        $autoStatus = $_POST['autoStatus'];
        //echo $autoStatus; die;

        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $logginusername = $login->username;

        //if($autoStatus == 1){

        /* Array of Status */
        $status['status_id'] = '';
        $status['userid'] = $login->user_id;
        $status['status_image'] = '';
        $status['createdDate'] = date('YmdHis');
        $status['usermentioned'] = $logginusername;
        $status['hasgTag'] = '';

        $statusid = $this->user_model->saveUserStatus($status);   //// save new status into database

        $arr_status['status'] = str_replace('changeparameter(New);', "changeparameter(" . $statusid . ");", $userstatus);
        $arr_status['status_id'] = $statusid;
        $statusid = $this->user_model->saveUserStatus($arr_status);   //// update last inserted status
        //Check offer exist for beacon, if exist update offer and notification else create new offer
        $offer = $this->beacon_model->getOffer($beaconId);

        if (count($offer) != 0) {
            //Update offer to inactive
            $update['beconOfferId'] = $offer->beconOfferId;
            $update['isActive'] = 0;
            $this->beacon_model->saveBeaconOffers($update);
        }

        //Create Beacon Offer
        $arr_beacon['beconOfferId'] = '';
        $arr_beacon['beaconId'] = $beaconId;
        $arr_beacon['notificationMessagge'] = $userstatus;
        $arr_beacon['noofcoins'] = $coins;
        $arr_beacon['minAge'] = $minAge;
        $arr_beacon['maxAge'] = $maxAge;
        $arr_beacon['gender'] = $gender;
        $arr_beacon['isActive'] = 1;
        $arr_beacon['isDelete'] = 0;
        $arr_beacon['createdDate'] = date('YmdHis');
        $arr_beacon['modifiedDate'] = date('YmdHis');

        $offer_id = $this->beacon_model->saveBeaconOffers($arr_beacon);

        $data['loggedinUsername'] = $login->username;
        $data['loggedinUserImage'] = $login->image;
        $data['timeline'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $statusid);
        $data['enteredUsername'] = $logginusername;
        $data['sigleRecord'] = '1';
        $data['loginuser'] = $login->user_id;
        $data['loggedinUsertype'] = $login->usertype;

        if ($autoStatus == 1) {
            $this->load->view('addmoretimeline', $data);
        }
    }

    //Created by Hassan 25-11-2015
    function getBeaconOffer() {

        $beaconId = $_POST['beaconId'];
        $offer = $this->beacon_model->getOffer($beaconId);

        if (count($offer) != 0) {
            echo 'exist';
        } else {
            echo 'not exist';
        }
    }

    /*  Neelam 11-4-2014 */

    function homesignin($redirect = NULL) {
        if ($redirect == '') {
            //// login form submit
            $arr_user = array();   //// Define Array;
            //// Define Variables
            $currenturl = $this->input->post('currenturl');
            $username = $this->input->post('login_username');
            $password = $this->input->post('login_password');

            $redirecturl = $this->getredirecturl($currenturl);   //// Get Redirect Url

            if ($redirecturl == '') {
                $redirecturl = 'home';
            }

            if ($username != '' && $password != '') {
                $user['username'] = $username;
                $user['active'] = 1;
                $arr_user = $this->administrator_model->check_username($user);


                if (count($arr_user) == 0) {
                    $user = '';
                    $user['email'] = $this->input->post('login_username');
                    $user['active'] = 1;
                    $arr_user = $this->administrator_model->check_username($user);
                }

                if (count($arr_user) > 0) {
                    $login['username'] = $username;
                    $login['email'] = $username;
                    $login['password'] = md5($password);

                    $arr_user = $this->administrator_model->check_login($login);

                    if (count($arr_user) > 0) {
                        $usertype = $arr_user->usertype;

                        if ($usertype == 2) {
                            $where['userid'] = $arr_user->user_Id;
                            $loginStatus = $this->user_model->getLoginStatus('*', $where);
                            if (count($loginStatus) == 0) {
                                $redirect = $this->createsession($arr_user);
                                redirect($redirect);
                            } else {
                                $hold = $loginStatus->hold;
                                if ($hold === "1") {
                                    $this->session->set_flashdata('top_error_message', 'Account is On Hold. Contact Admininstrator');
                                    redirect($redirect);
                                } else {
                                    $redirect = $this->createsession($arr_user);
                                    redirect($redirect);
                                }
                            }
                        } else {
                            $redirect = $this->createsession($arr_user);
                            redirect($redirect);
                        }
                    } else {
                        $this->session->set_flashdata('top_error_message', 'Please check you @Usernames or Password is correct');
                        redirect($redirect);
                    }
                } else {
                    $this->session->set_flashdata("top_error_message", "Username doesn't exist");
                    redirect($redirecturl);
                }
            } else {
                if ($username == '' && $password == '') {
                    $this->session->set_flashdata('top_error_message', 'Please enter username and password');
                } else if ($username == '') {
                    $this->session->set_flashdata('top_error_message', 'Please enter username');
                } else if ($password == '') {
                    $this->session->set_flashdata('top_error_message', 'Please enter password');
                }
                /* echo $redirecturl; die; */
                redirect($redirecturl);
            }
        }
    }

    function getredirecturl($val) {
        $val = str_replace(base_url() . 'index.php/', '', $val);
        return $val;
    }

    function createsession($userdetails) {
        if ($userdetails->accesslevel == 'admin') {

            $sess_admin = (object) array(
                        "ad_userid" => $userdetails->user_id,
                        "ad_username" => $userdetails->username,
                        "ad_active" => $userdetails->active,
                        "ad_usertype" => $userdetails->usertype,
                        "ad_accesslevel" => $userdetails->accesslevel,
                        "ad_firstname" => $userdetails->firstname,
                        "ad_lastname" => $userdetails->lastname,
                        "ad_image" => $userdetails->image,
                        "ad_active" => $userdetails->active
            );

            $this->session->set_userdata('sess_admin', $sess_admin);
            return ('profile/index');
        } else {

            $this->session->set_userdata('logged_in', $userdetails);
            $userdata = $this->session->userdata('logged_in');

            if ($userdetails->accessLevel == 'Consumer') {
                return ('timeline');
            } else {
                return ('timeline');
            }
        }
    }

    function viewprofile() {
        $data = '';
        $userdata = $this->session->userdata('logged_in');
        $userid = $userdata->user_Id;
        $data['user'] = $this->user_model->getOneUser($userid);

        $this->load->view('userprofile', $data);
    }

    function challenge_decline() {

        $update = array(
            'challenge_id' => $this->input->post('challengeid'),
            'approval' => 2,
            'modifiedDate' => date('YmdHis')
        );

        $this->challenge_model->update($update);


        $challangeDetails = $this->challenge_model->getchallanges('challenge_id', $this->input->post('challengeid'));

        /* Update Challned Received Notification */

        $arr_preNotice['challangeid'] = $this->input->post('challengeid');
        $arr_preNotice['actionFrom'] = $challangeDetails->challenge_from;
        $arr_preNotice['actionTo'] = $challangeDetails->challenge_to;
        $arr_preNotice['is_new'] = 1;
        $arr_preNotice['action'] = 'CC';
        $notificationDetails = $this->notification_model->getOneNotification($arr_preNotice);

        $notice_where['where'] = 'notification_id';
        $notice_where['val'] = $notificationDetails->notification_id;
        $arr_noticeupdate['is_new'] = 0;
        $this->notification_model->updateNotification($arr_noticeupdate, $notice_where);   //// Update Old Notifiaction in Database

        /* Send Notification to Sender of Challange */
        $arr_challange['challenge_id'] = $this->input->post('challengeid');
        $challange = $this->challenge_model->getchallanges($arr_challange);
        $login = $this->administrator_model->front_login_session();  //// Get Login Details From Session

        $arr_notice['notification_id'] = '';
        $arr_notice['actionFrom'] = $login->user_id;
        $arr_notice['actionTo'] = $challange->challenge_from;
        $arr_notice['action'] = 'CD';
        $arr_notice['actionString'] = ' declined your challenge ';
        $arr_notice['message'] = '';
        $arr_notice['statusid'] = '';
        $arr_notice['challangeid'] = $this->input->post('challengeid');
        $arr_notice['active'] = '1';
        $arr_notice['createdDate'] = date('YmdHis');
        $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
        //redirect('home/timeline');
    }

    function challenge_accept($id) {

        $header['login'] = $this->administrator_model->front_login_session();
        $userid = $header['login']->user_id;

        $challengeCoins = $this->challenge_model->challenge_coins($id);
        //print_r($challengeCoins);
        foreach ($challengeCoins as $coins) {
            $coins->challenge_coins;
            $coins->game_id;
        }
        $game = $this->games_model->getGameId($userid);
        //print_r($game);
        foreach ($game as $gameid) {
            $gameid->game_id;
        }
        if (count($game) > 0) {
            if ($gameid->game_id == $coins->game_id || $gameid->game_id == 5) {

                $userCoins = $this->games_model->userCoins($userid);
                $userCoins->coins;
                if (count($userCoins) == 0) {

                    $this->session->set_flashdata('challenge_messege', 'You dont have coins');
                    redirect('timeline');
                } else {
                    if ($userCoins->coins >= $coins->challenge_coins) {
                        $update = array(
                            'challenge_id' => $id,
                            'approval' => 1,
                            'modifiedDate' => date('YmdHis')
                        );

                        $this->challenge_model->update($update);
                        $this->session->set_flashdata('challenge_messege', 'Challenge accept successfully');
                        redirect('timeline');
                    } else {
                        echo "You have only " . $userCoins->coins . " coins";
                        $this->session->set_flashdata('challenge_messege', 'You have only ' . $userCoins->coins . ' coins');
                        redirect('timeline');
                    }
                }
            } else {
                //echo "You not purchased this game";
                $this->session->set_flashdata('challenge_messege', 'You not purchased this game');
                redirect('timeline');
            }
        } else {
            //echo "Please purchase game first to accept this challenge";
            $this->session->set_flashdata('challenge_messege', 'Please purchase game first to accept this challenge');
            redirect('timeline');
        }
    }

    /* Method For Show Userprofile  */

    function userprofilepopup($userid) {
        $this->load->helper('convertlink');

        if (!is_numeric($userid)) {
            $select = "user_Id";
            $arr_where['username'] = $userid;
            $arr_where['active'] = 1;
            $user = $this->user_model->getOneUserDetails($arr_where, $select);
            if (count($user) > 0) { //echo $this->db->last_query(); die;
                $userid = $user->user_Id;
            }
        }

        //// Get Logged-In Session
        $login = $this->administrator_model->front_login_session();
        $data['loggedinUser'] = $login->user_id;
        //// Get logged-In User Details

        $data['userDetails'] = $this->user_model->getOneUser($userid);
        if (count($data['userDetails']) > 0) {
            $data['userDetails']->bio = convert_hurree_links($data['userDetails']->bio);

            $data['profileuserid'] = $userid;
            //// Get User Own Status

            $start = 0;
            $perpage = 2;
            $orderby = 'DESC';
            $arr_status['user_status.userid'] = $userid;
            $arr_status['status_image'] = '';

            //$data['timeline']=$this->user_model->getUserStatus($userid, $start,$perpage, $orderby);

            $data['timeline'] = $this->user_model->getStatusDetails($arr_status, '1', $start, $perpage, $orderby);

            $follow = array(
                'userId' => $data['loggedinUser'],
                'followUserId' => $userid
            );

            $data['followUsers'] = $this->user_model->getfollowUsers($follow);

            $block = array(
                'userid' => $login->user_id,
                'block_user_id' => $userid
            );
            $data['user_block'] = $this->user_model->userIsBlock($block);
            $data['message'] = '0';


            if ($data['userDetails']->usertype == 2 || $data['userDetails']->usertype == 5) {
                $branch = $this->user_model->getbusinessbranches($userid);
                if ($branch[0]->website != '') {
                    $data['website'] = $branch[0]->website;
                } else {
                    $data['website'] = '';
                }
            } else {
                $data['website'] = '';
            }
        } else {
            $data['message'] = '1';
        }
        //// Load View Page
        $this->load->view('userprofile', $data);
    }

    function ajaxuserprofile() {
        $userid = $_POST['username'];
        if (!is_numeric($userid)) {
            $userid = str_replace("@", "", $userid);
            $arr_user['username'] = $userid;
        } else {
            $arr_user['user_Id'] = $userid;
        }
        $arr_user['active'] = 1;

        $userDetails = $this->user_model->getOneUserDetails($arr_user);

        if (count($userDetails) > 0) {
            echo '1';
            return '1';
        } else {
            echo '0';
            return '0';
        }
    }

    function ajaxuserprofileDetails() {
        /* echo '<pre>'; print_r($_POST); */
        $userid = $_POST['userid'];
        if (!is_numeric($userid)) {
            $userid = str_replace("@", "", $userid);
            $arr_user['username'] = $userid;
        } else {
            $arr_user['user_Id'] = $userid;
        }
        $arr_user['active'] = 1;
        $userDetails = $this->user_model->getOneUserDetails($arr_user);
        if (count($userDetails) > 0) {
            $users['user_Id'] = $userDetails->user_Id;
            $users['image'] = $userDetails->image;
            $users['header_image'] = $userDetails->header_image;

            if ($userDetails->usertype == 1 || $userDetails->usertype == 4) {
                $users['firstname'] = $userDetails->firstname;
                $users['lastname'] = $userDetails->lastname;
            }
            if ($userDetails->usertype == 2 || $userDetails->usertype == 5) {
                $users['firstname'] = $userDetails->businessName;
                $users['lastname'] = '';
            }

            $users['location'] = $userDetails->location;
            $this->load->helper('convertlink');
            $users['bio'] = convert_hurree_links($userDetails->bio);

            echo json_encode($users);
        } else {
            echo 'error';
        }
    }

    /**
     * Save reply of main status from timeline and full profile page
     *
     */
    function postreply() {

        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $login = $this->administrator_model->front_login_session();
        $enteredUsername = $_POST['enteredUsername'];
        $videoThumb = '';
        $statusid = $_POST['statusid'];
        $reply = $_POST['reply_status_timeline'];
        if (@$_FILES['ImageReplyFile']['size'] > 0) {
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $login->user_id;
            $mediumImagePath = 'upload/status_image/medium/' . $login->user_id;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }
            // Image upload in full size in profile directory


            $tmp_name = $_FILES["ImageReplyFile"]["tmp_name"];
            $name = mktime() . $_FILES["ImageReplyFile"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            $ext = strtolower(pathinfo($_FILES["ImageReplyFile"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, $extionArray)) {
                // image resize in thumbnail size in thumbnail directory
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $path . '/' . $name;
                $config['new_image'] = $mediumImagePath . '/' . $name;

                $config['maintain_ratio'] = TRUE;
                $config['width'] = 400;
                $config['height'] = 350;
                $this->image_lib->initialize($config);
                $rtuenval = $this->image_lib->resize();
                $this->image_lib->clear();
                $videoThumb = '';
                $status_image = $name;
            } else {
                $videoThumbPath = 'upload/videoThumb' . '/' . $login->user_id;
                if (!is_dir($videoThumbPath)) {
                    if (mkdir($videoThumbPath, 0777, true)) {
                        $thumbPath = $videoThumbPath;
                    } else {
                        $thumbPath = $videoThumbPath;
                    }
                } else {
                    $thumbPath = $videoThumbPath;
                }
                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                $videothumb = strtotime(date('Ymdhis')) . 'thumb.jpg';
                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . '  -deinterlace -an -ss 5 -f mjpeg -t 1 -r 1 -y -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;
                // echo $cmd; exit;
                exec($cmd . ' ' . '2>&1', $out, $res);

                $videoThumb = $login->user_id . '/' . $videothumb;
            }
            $status_image = $login->user_id . '/' . $name;
            // Image Upload End
        } else {
            $status_image = '';
        }

        $arr_username = explode(",", $enteredUsername);
        $arr_uniqueusername = array_unique($arr_username);

        $enteredUsername = implode(",", $arr_uniqueusername);

        //echo $_POST['reply_status_timeline']; die;
        $statusid = $_POST['statusid'];
        $reply = $_POST['reply_status_timeline'];
        $date = date('Y-m-d H:i:s');
        $arr_reply['status_id'] = '';
        $arr_reply['parentStatusid'] = $statusid;
        $arr_reply['status'] = str_replace("/n", "", $reply);
        $arr_reply['userid'] = $login->user_id;
        $arr_reply['media_thumb'] = $videoThumb;
        $arr_reply['usermentioned'] = $enteredUsername;
        $arr_reply['createdDate'] = $date;
        $arr_reply['status_image'] = $status_image;
        /* echo '<pre>'; print_r($arr_reply); die; */
        $status_id = $this->user_model->saveUserStatus($arr_reply);


        $status['status_id'] = $_POST['statusid'];

        $statusdetails = $this->user_model->getStatusDetails($status);
        //echo '<pre>'; print_r($statusdetails); exit;
        $mainstatusUserId = $statusdetails->userid;

        $originalUserDetails = $this->user_model->getOneuser($mainstatusUserId);

        // Logged In Username
        $logginusername = $login->username;

        if ($enteredUsername != '') {
            foreach ($arr_uniqueusername as $oneuser) {
                //if($login->user_id!=$mainstatusUserId)

                if ($logginusername != $oneuser) {
                    if (strcasecmp($originalUserDetails->username, $oneuser) == 0) {
                        if ($originalUserDetails->username != $login->username) {
                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $login->user_id;
                            $arr_notice['actionTo'] = $mainstatusUserId;
                            $arr_notice['action'] = 'R';
                            $arr_notice['actionString'] = ' replied to you!';
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = $status_id; //$_POST['statusid'];
                            $arr_notice['challangeid'] = '';
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = date('YmdHis');
                            $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                            // send notification code start
                            $deviceInfo = $this->user_model->getdeviceToken($mainstatusUserId);
                            if (count($deviceInfo) > 0) {
                                foreach ($deviceInfo as $device) {
                                    $deviceToken = $device->key;
                                    $deviceType = $device->deviceTypeID;
                                    $title = 'My Test Message';
                                    $sound = 'default';
                                    $msgpayload = json_encode(array(
                                        'aps' => array(
                                            'alert' => 'Reply: ' . $login->username . ' replied to you!',
                                            'statusid' => $status_id,
                                            "type" => 'reply',
                                            'sound' => $sound
                                    )));


                                    $message = json_encode(array(
                                        'default' => $title,
                                        'APNS_SANDBOX' => $msgpayload
                                    ));

                                    $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                }
                            }

                            // end
                        }
                    } else {
                        if ($originalUserDetails->username != $login->username) {
                            $arr_mention['username'] = $oneuser;
                            $arr_mention['active'] = 1;
                            /* print_r($arr_mention); die; */
                            $mentionUserDetails = $this->user_model->getOneUserDetails($arr_mention, '*');
                            $arr_notice['actionTo'] = $mentionUserDetails->user_Id;
                            $arr_notice['actionString'] = ' mentioned you!';
                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $login->user_id;
                            $arr_notice['action'] = 'M';
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = $status_id; //$_POST['statusid'];
                            $arr_notice['challangeid'] = '';
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = date('YmdHis');
                            $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                            // send notification code start
                            $deviceInfo = $this->user_model->getdeviceToken($mainstatusUserId);
                            if (count($deviceInfo) > 0) {
                                foreach ($deviceInfo as $device) {
                                    $deviceToken = $device->key;
                                    $deviceType = $device->deviceTypeID;
                                    $title = 'My Test Message';
                                    $sound = 'default';
                                    $msgpayload = json_encode(array(
                                        'aps' => array(
                                            'alert' => 'Mention: ' . $login->username . ' mentioned you!',
                                            'statusid' => $status_id,
                                            "type" => 'usermention',
                                            'sound' => $sound
                                    )));


                                    $message = json_encode(array(
                                        'default' => $title,
                                        'APNS_SANDBOX' => $msgpayload
                                    ));

                                    $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                }
                            }

                            // end
                        }
                    }
                }
                $arr[] = "@" . $oneuser;
            }
        }


        $enteredNewUsername = implode(" ", $arr);

        $times = get_ago_time($date);

        if ($status_image != '') {
            $baseurl = base_url();
            $images = '<p><a href="' . $baseurl . 'upload/status_image/full/' . $status_image . '" class="imgPopup"><img src="' . $baseurl . 'upload/status_image/medium/' . $status_image . '" width="400" height="224" alt=""></a></p>';
        } else {
            $images = '';
        }
        $pageName = @$_POST['pageName'];
        if ($pageName == 'user_profile1') {
            $data['sigleRecord'] = 'replyFP';
        } else {
            $data['sigleRecord'] = 'reply';
        }

        $header['login'] = $this->administrator_model->front_login_session();
        $arr_status['status'] = $reply;
        $arr_status['status_id'] = $status_id;
        $data['enteredUsername'] = $enteredNewUsername;

        $data['loggedinUsertype'] = $header['login']->usertype;
        $data['parentStatusId'] = $statusid;
        $data['loggedinUserImage'] = $header['login']->image;
        $data['loginusername'] = $header['login']->username;
        $data['time'] = $this->user_model->getStatusDetailsUserProfile($arr_status);

        $data['loginuser'] = $login->user_id;

        $this->load->helper('follow');
        $this->load->helper('like');

        $data['page'] = $this->input->post('replyPage');
        // initiate curl for update posted status
        $ch = curl_init();
        $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        // end curl
        $this->load->view('addmoretimeline', $data);
    }

    function submit_game() {

        $this->load->view('submitgame');
    }

    function sendGame() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $userDetail = $this->user_model->getOneUser($userid);

        $username = $userDetail->username;
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $number = $this->input->post('number');
        $stage = $this->input->post('stage');
        $concept = $this->input->post('concept');

        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('submitGame');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;


        $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
        $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
        $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

        //// replace strings from message
        $messages = str_replace('{Username}', ucfirst($username), $messages);
        $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
        $messages = str_replace('{App_Store_Image}', $appstore, $messages);
        $messages = str_replace('{Google_Image}', $googleplay, $messages);

        //// Email to user
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($email);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
        //// Email to Hello@Hurree.co
        $messages = '<p>Full Name: ' . $name . '</p><p>Contact Number: ' . $number . '</p><p>Stage of Game:: ' . $stage . '</p><p>Concept: ' . $concept . '</p>';
        $this->email->from($email, $name);
        $this->email->to($email_template->from_email);
        $this->email->subject('Submit Game');
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND

        echo '<p style="font-family:Roboto,sans-serif;text-align:center;margin-top:40%;">Thanks for submitting a game!</p>';
    }

    function sendOffer() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $userDetail = $this->user_model->getOneUser($userid);

        $username = $userDetail->username;
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $number = $this->input->post('number');
        if ($this->input->post('region') == 'All') {
            $regionName = 'All Regions';
        } else {
            $regionid = $this->input->post('region');
            $region = $this->user_model->getregion($regionid);
            $regionName = $region->region_name;
        }

        //echo $regionName; die;

        $rrp = $this->input->post('rrp');
        $discount = $this->input->post('discount');
        $product = $this->input->post('product_details');
        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('submitOffer');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;


        $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
        $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
        $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

        //// replace strings from message
        $messages = str_replace('{Username}', ucfirst($username), $messages);
        $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
        $messages = str_replace('{App_Store_Image}', $appstore, $messages);
        $messages = str_replace('{Google_Image}', $googleplay, $messages);

        //// Email to user
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($email);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
        //// Email to Business@Hurree.co
        $messages = '<p>Full Name: ' . $name . '</p><p>Contact Number: ' . $number . '</p><p>Region: ' . $regionName . '</p><p>RRP of offer: ' . $rrp . '</p><p>Discount: ' . $discount . '</p><p>Product Details: ' . $product . '</p>';
        $this->email->from($email, $name);
        $this->email->to($email_template->from_email);
        $this->email->subject('Submit Offer');
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND
        echo 1;
    }

    function userBlock() {

        $blockUserId = $_POST['id'];
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $arr_user['userid'] = $userid;
        $arr_user['block_user_id'] = $blockUserId;
        $blockuser = $this->user_model->getBolckUserDetails($arr_user);

        if (count($blockuser) == 0) {
            $block = array(
                'block_id' => '',
                'userid' => $userid,
                'block_user_id' => $blockUserId,
                'createdDate' => date('YmdHis'),
                'modifiedDate' => date('YmdHis')
            );
            $this->user_model->block($block);

            //Update follow table if user follow to block user
            $arr_follow = array(
                'userId' => $userid,
                'followUserId' => $blockUserId
            );
            $checkfollow = $this->user_model->checkfollow($arr_follow);

            if (count($checkfollow) > 0) {

                $this->user_model->deletefollow($checkfollow->follow_id);
            }
            echo 'block';
        } else {

            $block_id = $blockuser->block_id;
            $this->user_model->deleteblock($block_id);
            echo 'unblock';
        }
    }

    function purchaseGame() {
        $gameid = $this->uri->segment(3);
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $userDetail = $this->user_model->getOneUser($userid);

        $country = $userDetail->country;

        if ($country != 0) {
            $country = $this->country_model->getcountry('country_id', $country, 1);

            if ($country->country_code == 'GB' || $country->country_code == '') {
                $code = 1;
            } else {
                $code = 0;
            }

            $onecurency = $this->country_model->getcurrency($code);
            $currency = $onecurency->currency_code;
        } else {
            $currency = 'GBP'; //USD
        }
        //Get Card Types
        $card_row = 1;
        $data['cardtype'] = $this->user_model->getcardtype('', $card_row);

        $data['countries'] = $this->country_model->get_countries();
        $data['userDetail'] = $userDetail;
        //$data['country'] = $country->country_code;
        $data['currency'] = $currency;

        $data['game'] = $this->games_model->getGame($gameid);
        $this->load->view('game_payment', $data);
    }

    function paymentPayPal() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $username = $login->username;
        $email = $login->email;
        //$name= $this->input->post('name');

        $firstname = $this->input->post('name');
        $lastname = $this->input->post('lastname');

        $gameid = $this->input->post('game');
        $card_no = $this->input->post('card');
        $card_type = $this->input->post('cardtype');
        $exp_month = $this->input->post('month');
        $exp_year = $this->input->post('year');
        $cvv2 = $this->input->post('cvv2');
        $address = $this->input->post('address');

        $country = $this->input->post('country');
        $countryCode = $this->country_model->getcountry('country_id', $country, 1);
        if ($countryCode->country_code == 'GB') {
            $code = 1;
        } else {
            $code = 0;
        }
        $onecurency = $this->country_model->getcurrency($code);
        $currency = $onecurency->currency_code;

        $oneamount = $this->games_model->getprice($currency);
        $amount = $oneamount->price;

        $state = $this->input->post('state');
        $city = $this->input->post('city');
        $zip_code = $this->input->post('zip');

        $paymentType = urlencode('Sale');    // or 'Sale'
        $firstName = urlencode($firstname);
        $lastName = urlencode($lastname);
        $creditCardType = "visa";
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

        //// CREATE AN STRING
        $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$emails" .
                "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";


        //// SEND REQUEST TO PAYPAL
        $httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);

        if ($httpParsedResponseAr['ACK'] == 'Success') {
            $duration = '12';
            $date = date_create(date('Y-m-d'));    ///// CREATE OBJECT
            date_add($date, date_interval_create_from_date_string($duration . ' month'));     ///// ADD DURATION INTO
            $duration_month = date_format($date, 'Y-m-d');

            $payment['id'] = '';
            $payment['user_id'] = $userid;
            $payment['expiration'] = $duration_month;
            $payment['purchasedOn'] = date('YmdHis');
            $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
            //$payment['currency']=urldecode($httpParsedResponseAr['CURRENCYCODE']);
            $payment['currency'] = $currency;
            $payment['transaction_id'] = $httpParsedResponseAr['TRANSACTIONID'];
            $payment['paymentInfo'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '|CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
            $payment['active'] = 1;
            $payment['game_id'] = $gameid;
            //$payment['IsDelete']=0;
            $payment['createdDate'] = date('YmdHis');
            $payment['modifiedDate'] = date('YmdHis');

            $last_payment_id = $this->games_model->savepayment($payment);

            //// SEND  EMAIL START
            $this->emailConfig();   //Get configuration of email
            //// GET EMAIL FROM DATABASE

            $email_template = $this->email_model->getoneemail('purchase_game');

            //// MESSAGE OF EMAIL
            $messages = $email_template->message;


            $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
            $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
            $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

            //// replace strings from message
            $messages = str_replace('{Username}', ucfirst($username), $messages);
            $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
            $messages = str_replace('{App_Store_Image}', $appstore, $messages);
            $messages = str_replace('{Google_Image}', $googleplay, $messages);

            //// Email to user
            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to($email);
            $this->email->subject($email_template->subject);
            $this->email->message($messages);
            $this->email->send();    ////  EMAIL SEND

            echo '1';
        } else {
            echo '<p style="font-family:Roboto,sans-serif;text-align:center;margin-top:6%;">' . $httpParsedResponseAr['L_LONGMESSAGE0'] . '</p>';
        }
    }

    function deleteMessage() {
        $login = $this->administrator_model->front_login_session();
        $loginuser = $login->user_id;

        $message_from = $_POST['messagefrom'];
        $messageid = $_POST['messageid'];

        $arr_mess['id'] = "";
        $arr_mess['userId'] = $loginuser;
        $arr_mess['secondUserId'] = $message_from;
        $arr_mess['lastDeletedMessageId'] = $messageid;
        $arr_mess['createdDate'] = date('YmsHis');
        $this->message_model->saveDeleteMessage($arr_mess);
    }

    function editprofile() {

        $login = $this->administrator_model->front_login_session();
        if ($login->active == 0) {
            redirect('home');
        }
        $userid = $login->user_id;
        //print_r($login->usertype);die;
        $data['loginuser'] = $login->user_id;
        $data['user'] = $this->user_model->getOneUser($userid);

        $this->load->helper('convertlink');
        $this->load->helper('follow');
        $this->load->helper('like');

        $data['user']->bio = convert_hurree_links($data['user']->bio);
        $data['totalcoins'] = $this->score_model->getUserCoins($userid);

        //Count activities
        /* $arr_status['user_status.userid'] = $id;
          $arr_status['status_image'] = '';
          $activities = $this->user_model->getStatusDetails($arr_status, 1); */
        $activities = $this->user_model->getUserStatusCount($userid);

        $data['activities'] = $activities;
        //$data['activities_data'] = $activities;
        //End Count activities
        //Count Photos
        $photos = $this->user_model->getUserStatusPhotsCount($userid);
        $data['photos'] = $photos;
        //End Count Photos
        //Follower users
        $followers = $this->user_model->getLoggedInUserFollowers($userid);
        $data['userid'] = $userid;
        $data['followers'] = $followers;

        //Following users
        $following = $this->user_model->getLoggedInUserFollowing($userid);
        $data['following'] = $following;

        $header['login'] = $this->administrator_model->front_login_session();
        $userid = $header['login']->user_id;
        $data['loggedin'] = $userid;
        $data['loginusername'] = $header['login']->username;
        $data['loggedinUsertype'] = $header['login']->usertype;
        $data['loggedinUserImage'] = $header['login']->image;

        $data['hashTag'] = '';

        $array_following['followUserId'] = $userid;
        $array_following['active'] = 1;
        $followers = $this->user_model->getfollowinguserid($array_following);

        $data['peoples'] = $this->user_model->getpeopletofollow($userid, '1');

        $data['businesses'] = $this->user_model->getpeopletofollow($userid, '2');

        //Activities Section
        $orderby = 'DESC';
        $data['status_activities_records'] = $this->status_model->getUserActivities($userid, '', '', $orderby, $header['login']->user_id);
        $config['base_url'] = base_url() . 'index.php/business/' . $this->uri->segment('3');
        $config['total_rows'] = $data['status_activities_records'];
        $config['per_page'] = '6';
        $config['uri_segment'] = 3;

        $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['page'] = $page;

        $orderby = 'DESC';
        $data['status_activities'] = $this->status_model->getUserActivities($userid, $data['page'], $config['per_page'], $orderby, $header['login']->user_id);
        $data['statuscount'] = count($data['status_activities']);
        $data['noofstatus'] = $config['per_page'];
        //End Activities
        //$orderby = 'DESC';
        //$data['timeline'] = $this->status_model->getUserStatus($userid, '', '', $orderby, $header['login']->user_id);

        /* Replies Section and Likes Section  */
        $noticeStatusid = NULL;
        $data['records'] = $this->user_model->getFollowerStatus($userid, '', '', '', $noticeStatusid, '', $header['login']->user_id);

        //Likes statuses count
        $records = $data['records'];
        $j = 0;
        foreach ($records as $status) {
            if ($status->like == 'true') {
                $j++;
            }
        }
        $data['likesCount'] = $j;

        if ($login->usertype == 2 || $login->usertype == 5) {
            $branch = $this->user_model->getbusinessbranches($userid);
            if (count($branch) > 0) {
                $data['website'] = $branch[0]->website;
            } else {
                $data['website'] = '';
            }
        } else {
            $data['website'] = '';
        }
        $data['viewPage'] = 'editprofile';

        $this->load->view('inner_header', $data);
        $this->load->view('user_edit_profile');
        $this->load->view('inner_footer');
    }

    function editProfileSave() {

        //$_FILES['headerImage']['name'] = str_replace(" ", "", $_FILES['headerImage']['name']);
        //$_FILES['profilePic']['name'] = str_replace(" ", "", $_FILES['profilePic']['name']);

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $user = $this->user_model->getOneUser($userid);
        $path = getcwd();
        if (@$_FILES['profilePic']['size'] > 0) {
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/profile/full';
            $tmp_name = $_FILES["profilePic"]["tmp_name"];
            $name = mktime() . $_FILES["profilePic"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            // image resize in medium size in medium directory
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = "upload/profile/full/" . $name;
            $config['new_image'] = 'upload/profile/medium/' . $name;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 300;
            $config['height'] = 300;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();

            //Image resize in thumbnail size in thumbnail directory
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = "upload/profile/full/" . $name;
            $config['new_image'] = 'upload/profile/thumbnail/' . $name;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 100;
            $config['height'] = 100;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();

            $profile_image = $name;

            $login->image = $name;
            $this->session->set_userdata("logged_in", $login);
            // Image Upload End

            /* Unlink Old Header Image */
            //// CREATE PATH OF IMAGE
            if ($user->image != 'user.png') {
                $filepathFull = $path . '/upload/profile/full/' . $user->image;
                $filepathmedium = $path . '/upload/profile/medium/' . $user->image;
                $filepaththumbnail = $path . '/upload/profile/thumbnail/' . $user->image;
                //// UNLINK PREVIOUS Images
                $responce = unlink($filepathFull);
                $responce = unlink($filepathmedium);
                $responce = unlink($filepaththumbnail);
            }
        } else {

            $profile_image = '';
        }

        if (@$_FILES['headerImage']['size'] > 0) {
            $uploads_dir = 'upload/headerimage/full';
            $tmp_name = $_FILES["headerImage"]["tmp_name"];
            $name = mktime() . $_FILES["headerImage"]["name"];
            //$fileSizeMB = ($_FILES["headerImage"]["size"] / 1024 / 254);

            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = "upload/headerimage/full/" . $name;
            $config['new_image'] = 'upload/headerimage/resize/' . $name;
            $config['maintain_ratio'] = FALSE;
            $config['width'] = 1366;
            $config['height'] = 254;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();
            $header_image = $name;

            /* Unlink Old Image */
            if ($user->header_image != 'profileBG.jpg') {
                $filepath1 = $path . '/upload/headerimage/full/' . $user->header_image;       //// CREATE PATH OF Header Image
                $filepath2 = $path . '/upload/headerimage/resize/' . $user->header_image;       //// CREATE PATH OF Header Image
                $responce = unlink($filepath1);     //// UNLINK PREVIOUS PDF
                $responce = unlink($filepath2);             //// UNLINK PREVIOUS PDF
            }
        } else {
            $header_image = '';
        }

        $name = $this->input->post('name');

        //// Split name into firstname and lastname
        $arr_name = explode(' ', $name);
        $firstname = $arr_name[0];
        $save['lastname'] = '';
        if (count($arr_name) > 1) {
            $i = 0;
            $sur_name = '';
            foreach ($arr_name as $surname) {
                if ($i != 0) {
                    $sur_name = $sur_name . ' ' . $surname;
                }
                $i++;
            }
            $lastname = trim($sur_name);
        } else {
            $lastname = '';
        }

        $location = trim($this->input->post('location'));
        $website = $this->input->post('website');
        $bio = $this->input->post('bio');
        $businessName = $this->input->post('businessName');
        $opentime = $this->input->post('opentime');
        $closetime = $this->input->post('closetime');

        if ($profile_image == '') {
            $profileImage = $user->image;
        } else {
            $profileImage = $profile_image;
        }

        if ($header_image == '') {
            $headerImage = $user->header_image;
        } else {
            $headerImage = $header_image;
        }


        $update = array(
            'user_Id' => $user->user_Id,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'businessName' => $businessName,
            'bio' => $bio,
            'location' => $location,
            'image' => $profileImage,
            'header_image' => $headerImage,
            'openTime' => $opentime,
            'closeTime' => $closetime,
            'modifiedDate' => date('YmdHis')
        );
        //print_r($update); die;

        if ($this->input->post('editable') == 1) {
            $username = str_replace("@", "", $this->input->post('username'));
            if ($this->input->post('old_username') != $username) {
                $update['username'] = $username;
                $update['editable'] = 0;
            }
        }

        $this->user_model->updateProfile($update);

        $session = array();
        $session = (object) array(
                    'user_Id' => $user->user_Id,
                    'username' => $user->username,
                    'password' => $user->password,
                    'email' => $user->email,
                    'active' => $user->active,
                    'usertype' => $user->usertype,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'image' => $profileImage,
                    'accesslevel' => 'consumer'
        );

        $this->session->set_userdata('logged_in', $session);

        $updateWebsite = array(
            'userid' => $user->user_Id,
            'website' => $website
        );
        $this->user_model->updateWebsite($updateWebsite);

        redirect('business/' . ucfirst($user->username));
    }

    function validatecard() {
        //print_r($_POST);
        $cardtype = $this->input->post('cardtype');
        if ($this->input->post('cardtype') == 'MasterCard') {
            $cardtype = 'Master Card';
        }

        $varas = validate_card($this->input->post('card_no'), $cardtype);
        echo $varas;
    }

    function validatecardexpdate() {
        $cardError = card_expiry_valid($this->input->post('month'), $this->input->post('year'));
        echo $cardError;
    }

    function forgotPassword() {
        $this->load->view('header');
        $this->load->view('forgot_password');
        $this->load->view('footer');
    }

    function sendforgotpassword() {
        $email = $this->input->post('email');
        $checkemail = $this->user_model->checkEmail($email);

        if (count($checkemail) != 0) {

            if ($checkemail->active == 0) {
                echo 'Inactive';
            } else {

                $userid = $checkemail->user_Id;
                $username = $checkemail->username;

                $rand = $this->RandomStringForgotPassword();

                //$encode = base64_encode($userid);
                $link = base_url() . 'home/changePassword/' . $rand;  //$link = baseurl.'home/changePassword/'.$encode /*previous*/

                $update['user_Id'] = $userid;
                $update['password_reset_token'] = $rand;
                $this->user_model->save($update);

                //// SEND  EMAIL START
                //$this->emailConfig();   //Get configuration of email
                //// GET EMAIL FROM DATABASE

                $email_template = $this->email_model->getoneemail('forgot_password');

                //// MESSAGE OF EMAIL
                $messages = $email_template->message;

                $hurree_image = base_url() . 'assets/img/Graph-icon-white-grey.png';
                $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
                $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

                //// replace strings from message
                $messages = str_replace('{Username}', ucfirst($username), $messages);
                $messages = str_replace('{Password Reset Link}', $link, $messages);
                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                $messages = str_replace('{Google_Image}', $googleplay, $messages);
                
                $httpClient = new \Http\Adapter\Guzzle6\Client(new Client());
                $sparky = new SparkPost($httpClient, ['key' => SPARKPOSTKEYSUB]);
                $promise = $sparky->transmissions->post([
                     'content' => [
                        'from' => [
                            'name' => 'Hurree',
                            'email' => "hello@marketmyapp.co",
                        ],
                        'subject' => $email_template->subject,
                        'html' => $messages,
                        'text' => '',
                    ],
                    'recipients' => [
                        [
                            'address' => [
                                'name' => '',
                                'email' => $email,
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
                //echo $messages;die;
                //// Email to user
                //$this->email->from('hello@hurree.co', 'Hurree');
                //$this->email->to($email);
                //$this->email->subject($email_template->subject);
                //$this->email->message($messages);
                //$this->email->send();

                echo '1';
            }
        } else {
            echo '0';
        }
    }

    function changePassword() {

        $passwordToken = $this->uri->segment(3);
        $data['password_reset_token'] = $passwordToken;
        $arr_user['password_reset_token'] = $passwordToken;
        $arr_user['active'] = 1;
        $user = $this->user_model->getOneUserDetails($arr_user, '*');
        if (count($user) > 0) {
            $this->load->view('change_password', $data);
        } else {
            $this->session->set_userdata('forgotLinkInactive', '1');
            redirect('pages');
        }
    }

    function resetPassword() {

        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[15]|matches[passconf]|md5');
        $this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim|required|min_length[6]|max_length[15]');
        if ($this->form_validation->run() == FALSE) {
            $passwordToken = $this->uri->segment(3);

            $data['password_reset_token'] = $this->input->post('password_reset_token');
            $this->load->view('change_password', $data);
        } else {

            $arr_user['password_reset_token'] = $this->input->post('password_reset_token');
            $arr_user['active'] = 1;
            $user = $this->user_model->getOneUserDetails($arr_user, '*');

            $update = array(
                'user_Id' => $user->user_Id,
                'password' => $this->input->post('password'),
                'password_reset_token' => ''
            );

            $this->user_model->reset_password($update);
            $this->session->set_userdata('forgotPasswordSucess', '1');

            redirect('home/signup');

            /* $userid = $user->user_Id;
              $userdetail = $this->user_model->getOneUser($userid);

              $username = $userdetail->username;
              $password = $userdetail->password;

              //// CHECK FOR USERNAME IN DATABASE
              $session_details = $this->user_model->check_resetpassword($username, $password);

              if (count($session_details) > 0) {

              //// IF USERNAME IS ALREADY REGISTERED,  WILL GO FOR CHECK PASSWORD
              if ($session_details->usertype != 3) {
              $this->session->set_userdata('logged_in', $session_details);
              redirect('timeline');
              } else {

              $sess_admin = (object) array(
              "ad_userid" => $session_details->user_id,
              "ad_username" => $session_details->username,
              "ad_active" => $session_details->active,
              "ad_usertype" => $session_details->usertype,
              "ad_accesslevel" => $session_details->accessLevel,
              "ad_firstname" => $session_details->firstname,
              "ad_lastname" => $session_details->lastname
              );

              $this->session->set_userdata('sess_admin', $sess_admin);

              redirect('profile');
              }
              } else {
              redirect('home/signup');
              } */
        }
    }

    function pushGameData() {
        $challengeId = '';
        $success = 0;
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $gameid = $this->input->post('gameId');
        $score = $this->input->post('gameScore');
        $challengeId = @$_POST['challengeId'];

        if ($challengeId != '') {
            $arr_challenge['challenge_id'] = $challengeId;
            $challenge = $this->challenge_model->getchallanges($arr_challenge);
            if ($login->user_id == $challenge->challenge_from) {
                $arr_score['challenge_from'] = $challenge->challenge_from;
                $arr_score['challengeFomCoins'] = $score;
            } else {
                $arr_score['challenge_to'] = $login->user_id;
                $arr_score['challengeToCoins'] = $score;

                //$message='<a data-game-active="active" target="_blank" style="text-decoration:none; " data-challenge-id= '.$challenge->challenge_id.' data-game-id='. $challenge->game_id .' href="javascript:void(0);" >@ '.$login->username.' has accepted your Challenge. Tap to play</a>';
                $message = ' has accepted your Challenge. Tap to play';
                //Start Notification
                $arr_notice['notification_id'] = '';
                $arr_notice['actionFrom'] = $login->user_id;
                $arr_notice['actionTo'] = $challenge->challenge_from;
                $arr_notice['action'] = 'CA';
                $arr_notice['actionString'] = $message;
                $arr_notice['message'] = '';
                $arr_notice['statusid'] = '';
                $arr_notice['challangeid'] = $challengeId;
                $arr_notice['active'] = '1';
                $arr_notice['createdDate'] = date('YmdHis');
                $this->notification_model->savenotification($arr_notice);   //// Save Notification in notification Table
                //End Notification
                //Push Notification
                $deviceInformation = $this->user_model->userdeviceinformation('userid', $challenge->challenge_from);

                $message = "@" . $arr_notice['actionString'];
                $push = array(
                    'devices' => $deviceInformation,
                    'NotificationCount' => 1,
                    'NotificationMessage' => $message
                );

                $pushnotification = array('messages' => array($push));
                $url = PUSHURL;
                $fields = json_encode($pushnotification);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POST, count($fields));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($fields))
                );
                $result = curl_exec($ch);
                curl_close($ch);
                //Push Notification
            }
            $arr_score['challenge_id'] = $challengeId;
            $arr_score['modifiedDate'] = date('YmdHis');


            $this->challenge_model->update($arr_score);

            /*  Get Updated Record to Set Winner And Looser */
            $challenge = $this->challenge_model->getchallanges($arr_challenge);
            if ($challenge->challengeFomCoins != '0' && $challenge->challengeToCoins != '0') {
                if ($challenge->challengeFomCoins < $challenge->challengeToCoins) {
                    $arr_challenge['winner'] = $challenge->challenge_to;
                    $loose_user = $challenge->challenge_from;
                } else {
                    $arr_challenge['winner'] = $challenge->challenge_from;
                    $loose_user = $challenge->challenge_to;
                }

                /* Update Winner */
                $arr_game['challenge_id'] = $challengeId;
                $arr_game['winner'] = $arr_challenge['winner'];
                $this->challenge_model->update($arr_game);

                /*  Send Notification to Winner */
                $arr_notice = array();
                $action = 'CW';
                /* $message=' won that challenge! '; */
                $message = "You lost the challenge against ";
                //Start Notification
                $arr_notice['notification_id'] = '';
                $arr_notice['actionFrom'] = $arr_challenge['winner'];
                $arr_notice['actionTo'] = $loose_user;
                $arr_notice['action'] = 'CL';
                $arr_notice['actionString'] = $message;
                $arr_notice['message'] = '';
                $arr_notice['statusid'] = '';
                $arr_notice['challangeid'] = $challengeId;
                $arr_notice['active'] = '1';
                $arr_notice['createdDate'] = date('YmdHis');
                $this->notification_model->savenotification($arr_notice);   //// Save Notification in notification Table


                $message = "You won the challenge against ";
                $arr_notice['actionFrom'] = $loose_user;
                $arr_notice['actionTo'] = $arr_challenge['winner'];
                $arr_notice['action'] = 'CW';
                $arr_notice['actionString'] = $message;
                $this->notification_model->savenotification($arr_notice);   //// Save Notification in notification Table
                //Auto Update
                $userWinner = $this->user_model->getOneUser($arr_challenge['winner']);
                $userWinner->username;

                $userLooser = $this->user_model->getOneUser($loose_user);
                $userLooser->username;

                $arr_status['status_id'] = '';
                $arr_status['parentStatusid'] = 0;
                $arr_status['status'] = '<un>@' . $userWinner->username . '</un> won a challenge against <un>@' . $userLooser->username . '</un>';
                $arr_status['userid'] = $arr_challenge['winner'];
                $arr_status['status_image'] = '';
                $arr_status['usermentioned'] = $userWinner->username . ',' . $userLooser->username;
                $arr_status['shareFrom'] = 0;
                $arr_status['share'] = 0;
                $arr_status['active'] = 1;
                $arr_status['isCheckInUserCoinsId'] = '';
                $arr_status['createdDate'] = date('YmdHis');
                $arr_status['modifiedDate'] = date('YmdHis');
                $status_id = $this->user_model->saveUserStatus($arr_status);
                //End Auto Update
                //Insert This Transation in to Usercoin
                $WinneruserCoins = array(
                    'userid' => $arr_challenge['winner'],
                    'coins' => $challenge->challenge_coins,
                    'coins_type' => 2,
                    'game_id' => $challenge->game_id,
                    'actiontype' => 'add',
                    'createdDate' => date('YmdHis')
                );
                $this->score_model->insert($WinneruserCoins);  //// Save loose user transation in USERCOIN.
                //Update Winner User Total Coins
                $winnerTotalCoins = $this->score_model->getUserCoins($arr_challenge['winner']);
                $winnerTotalCoins = $winnerTotalCoins->coins + $challenge->challenge_coins;
                $arr_total_coins['coins'] = $winnerTotalCoins;
                $arr_total_coins['userid'] = $arr_challenge['winner'];
                $this->score_model->update($arr_total_coins);    ///// update user total coins
                //Update looser User Total Coins
                $LooseuserCoins = array(
                    'userid' => $loose_user,
                    'coins' => $challenge->challenge_coins,
                    'coins_type' => 3,
                    'game_id' => $challenge->game_id,
                    'actiontype' => 'sub',
                    'createdDate' => date('YmdHis')
                );
                $this->score_model->insert($LooseuserCoins); //// Save loose user transation in USERCOIN.
                $arr_total_coins = '';
                $looseTotalCoins = $this->score_model->getUserCoins($loose_user);
                $looseTotalCoins = $looseTotalCoins->coins - $challenge->challenge_coins;
                $arr_total_coins['coins'] = $looseTotalCoins;
                $arr_total_coins['userid'] = $loose_user;
                $this->score_model->update($arr_total_coins); ///// update user total coins
            }
        }
        if ($challengeId == '') {
            $challengeId = '0';
        }
        $data = array(
            'userid' => $userid,
            'gameid' => $gameid,
            'challengeid' => $challengeId
        );
        /* print_r($data); */
        $row = $this->user_model->getScoreRow($data);

        if (count($row) > 0) {
            $currentDate = date('Y-m-d H:i:s');
            $row->createdDate;
            $date1 = new DateTime($row->createdDate);
            $date2 = new DateTime($currentDate);

            $diff = $date2->diff($date1);
            $hours = $diff->h;
            $hours = $hours + ($diff->days * 24);

            if ($hours >= 24) {
                $success = 1;
            }
        } else {
            $success = 1;
        }

        if ($success == 1) {
            $insert = array(
                'user_id' => $userid,
                'game_id' => $gameid,
                'coins' => $score,
                'score' => $score,
                'challengeid' => $challengeId,
                'createdDate' => date('YmdHis'),
                'modifiedDate' => date('YmdHis')
            );
            $this->score_model->pushscore($insert);

            $userCoins = array(
                'userid' => $userid,
                'coins' => $score,
                'coins_type' => 4,
                'game_id' => $gameid,
                'actiontype' => 'add',
                'createdDate' => date('YmdHis')
            );
            $this->score_model->insertCoins($userCoins);

            $totalcoins = $this->score_model->getUserCoins($userid);

            $updateTotalCoins = array(
                'userid' => $userid,
                'coins' => $score + $totalcoins->coins
            );
            $this->user_model->updateCoins($updateTotalCoins);
        }

        $insert['status'] = 'success';
        $this->data['output'] = $insert;
        $this->load->view('json_view', $this->data);
    }

    function shareStatusPopUp($statusid) {
        $status['status_id'] = $statusid;
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $data['statustype'] = $this->input->get('action');
        $statusdetails = $this->user_model->getStatusDetailsUserProfile($status, '', '', '', '', '', '', $userid);

        $data['statusdetails'] = $statusdetails;

        $data['status'] = utf8_decode($statusdetails->status);
        $this->load->view('share_status', $data);
    }

    function deleteStatusPopUp() {

        $statusid = $this->uri->segment(3);
        $type = $this->uri->segment(4);
        $data['statusid'] = $statusid;
        $data['type'] = $type;
        $this->load->view('delete_status', $data);
    }

    function deleteReplyStatusPopup() {

        $statusid = $this->uri->segment(3);
        $data['statusid'] = $statusid;
        $this->load->view('delete_reply', $data);
    }

    function declineChallengePopup() {

        $challengeid = $this->uri->segment(3);
        $dynamicId = $this->uri->segment(4);
        $data['challengeid'] = $challengeid;
        $data['dynamicId'] = $dynamicId;
        $this->load->view('decline_challenge', $data);
    }

    function generateQRcodePopup() {

        $data['message'] = '';
        $login = $this->administrator_model->front_login_session();
        $data['userid'] = $login->user_id;

        /* echo '<pre>'; */
        $where['userid'] = $login->user_id;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);
        /* print_r($loginStatus); */
        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email Business@Hurree.co to reactivate your business account";
            }
        }
        $data['offer_name'] = $this->input->get('offer_name');
        $data['date'] = $this->input->get('date');
        $data['coins'] = $this->input->get('coins');

        /* print_r($data); die; */
        $this->load->view('qrcode_confirmation', $data);
    }

    function deleteMessagePopup() {

        $data['message'] = array(
            'userid' => $this->input->get('messageUserid'),
            'messageid' => $this->input->get('messageid')
        );

        $this->load->view('delete_message', $data);
    }

    function invitefriends() {

        //$this->load->view('invite_friends');
        $this->load->view('find_friends');
    }

    function invite() {
        $this->load->view('popup');
    }

    function invite2() {
        $this->load->view('step_2_events');
    }

    function invite3() {
        $login = $this->administrator_model->front_login_session();
        $data['userid'] = $login->user_id;
        $this->load->view('step_3_contacts', $data);
    }

    function sendInvite() {

        $email = $_POST['emails'];


        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $username = $login->username;

        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('invite_friend');
        //echo '<pre>'; print_r($email_template);
        //// MESSAGE OF EMAIL
        $messages = $email_template->message;

        $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
        $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
        $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

        //// replace strings from message
        $messages = str_replace('{Username}', ucfirst($username), $messages);
        $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
        $messages = str_replace('{App_Store_Image}', $appstore, $messages);
        $messages = str_replace('{Google_Image}', $googleplay, $messages);
        $check = 0;

        $this->load->helper('email');
        if (valid_email($email)) {
            $check = 0;
            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to($email);
            $this->email->subject($email_template->subject);
            $this->email->message($messages);
            $this->email->send();
        } else {
            $check = 1;
        }

        if ($check == 0) {
            //return 'Invitation successfully sent.';
            echo 1;
        } else {
            // return 'There was an error sending email.';
            //return 'Please enter valid email.';
            echo 0;
        }


        die;
    }

    function challenge($id) {

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $data['userid'] = $userid;
        $data['userdetail'] = $this->user_model->getOneUser($id);
        $data['games'] = $this->games_model->getGames();

        $this->load->view('challenge_popup', $data);
    }

    function saveChallenge() {

        $data = array(
            'challenge_from' => $this->input->post('challenge_from'),
            'challenge_to' => $this->input->post('challenge_to'),
            'game_id' => $this->input->post('game'),
            'challenge_coins' => $this->input->post('coins'),
            'approval' => 0,
            'winner' => 0,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );

        $this->challenge_model->add($data);
        echo 1;
    }

    function showactivity() {
        $where_image = '';

        $userid = $_POST['userid'];
        $action = $_POST['action'];
        $activity = $_POST['activity'];

        $start = 0;
        $perpage = $_POST['perpage'];
        $orderby = 'DESC';

        $arr_status['user_status.userid'] = $userid;
        if ($action == 'activity') {
            $arr_status['status_image'] = '';
        } else {
            $where_image = 'photo';
        }

        $data['action'] = $action;
        //echo $where_image; die;

        $data['timeline'] = $this->user_model->getStatusDetails($arr_status, '1', $start, $perpage, $orderby, '', $where_image);
        /* echo '<pre>'; print_r($arr_status); die; */
        $this->load->view('activity_photo', $data);
    }

    function getcoinsdetails() {
        $challenged_coins = $_POST['coins'];
        $gameid = $_POST['gameid'];
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $arr_coins = array();
        $userdetails = $this->games_model->userCoins($userid);

        if (count($userdetails) > 0) {
            $usercoins = $userdetails->coins;
        } else {
            $usercoins = 0;
        }
        //echo $usercoins; die;

        if ($usercoins >= $challenged_coins) {
            // True
            $arr_coins[] = 'true';
            $arr_coins[] = '';
        } else {
            //False
            $arr_coins[] = 'false';
            $arr_coins[] = 'You Have not Enough Coins';
        }

        //$game['game_id']=$gameid;
        $arr_game['game_id'] = $gameid;
        $arr_game['user_id'] = $userid;
        $arr_game['active'] = 1;

        $subscription = $this->games_model->getUserOneGameSubscription($arr_game);

        if (count($subscription) > 0) {
            /* if (($subscription->game_id == $gameid) || ($subscription->game_id == 5)) { */
            $arr_subs[] = 'true';
            $arr_subs[] = "";
            /* } else {
              $arr_subs[] = 'false';
              $arr_subs[] = "You are Not Subscribe for this game";
              } */
        } else {
            $arr_subs[] = 'false';
            $arr_subs[] = "You are Not Subscribe for this game";
        }

        $arr_final['coins'] = $arr_coins;
        $arr_final['game'] = $arr_subs;

        echo json_encode($arr_final);
    }

    function uniqueemail() {

        $email = $_POST['email'];
        $arr_user['email'] = $email;
        $arr_user['active'] = 1;

        $user = $this->user_model->getOneUserDetails($arr_user, '*');
        if (count($user) == 0) {
            echo 'not exits';
        } else {
            echo 'exits';
        }
    }

    function uniqueReferralCode() {
        $code = $_POST['code'];
        $refferalCount = $this->user_model->checkReferralCodeExist($code);
        if ($refferalCount > 0) {
            echo 'exits';
        } else {
            echo 'not exits';
        }
    }

    function uniqueemailForProfile() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $email = $_POST['email'];
        $arr_user['email'] = $email;
        $arr_user['active'] = 1;


        $user = $this->user_model->getOneUserDetailForProfile($arr_user, '*', $userid);
        if (count($user) == 0) {
            echo 'not exits';
        } else {
            echo 'exits';
        }
    }

    function uniqueusername() {
        $username = $_POST['username'];

        $arr_user['username'] = $username;
        $arr_user['active'] = 1;

        $user = $this->user_model->getOneUserDetails($arr_user, '*');
        /* 	echo $this->db->last_query(); */
        if (count($user) == 0) {
            echo 'not exits';
        } else {
            echo 'exits';
        }
    }

    function buy_coins() {

        $data['message'] = '';
        /* echo '<pre>'; */

        $type = $this->uri->segment(3);
        $login = $this->administrator_model->front_login_session();
        $where['userid'] = $login->user_id;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);
        $userid = $login->user_id;
        $userDetail = $this->user_model->getOneUser($userid);
        //echo '<pre>';
        $autoTopUp = $userDetail->autoTopUp;
        //print_r($userDetail); die;
        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }

        $where = array(
            'usertype' => $type
        );
        $data['userid'] = $login->user_id;
        $data['autoTopUp'] = $autoTopUp;
        $data['type'] = $type;
        $data['buyCoins'] = $this->store_model->buy_coins($where);
        $this->load->view('buy_coins', $data);
    }

    function paymentCoins() {
        $buyCoins_id = $this->uri->segment(3);
        $auto_renewal_status = 0;
        if ($this->uri->segment(4) != '') {
            $auto_renewal_status = $this->uri->segment(4);
        }
        //$auto_renewal_status = $this->uri->segment(5);

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $userDetail = $this->user_model->getOneUser($userid);
        $country = $userDetail->country;

        if ($country != 0) {
            $country = $this->country_model->getcountry('country_id', $country, 1);

            if ($country->country_code == 'GB' || $country->country_code == '') {
                $code = 1;
            } else {
                $code = 0;
            }

            $onecurency = $this->country_model->getcurrency($code);
            $currency = $onecurency->currency_code;
        } else {
            $currency = 'GBP';
        }
        //Get Card Types
        $card_row = 1;
        $data['cardtype'] = $this->user_model->getcardtype('', $card_row);

        $data['countries'] = $this->country_model->get_countries();
        $data['userDetail'] = $userDetail;
        //$data['country'] = $country->country_code;
        $data['currency'] = $currency;
        $data['auto_renewal'] = $auto_renewal_status;

        $data['type'] = $userDetail->usertype;
        $data['buycoin'] = $this->store_model->buyCoinsRow($buyCoins_id);
        $this->load->view('coins_payment', $data);
    }

    function paymentCoinsPayPal() {
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $username = $login->username;
        $email = $login->email;
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        $lastname = $this->input->post('lastname');
        $auto_renewal = $this->input->post('auto_renewal');
        $arr_name = explode(' ', $name);
        $firstname = $arr_name[0];
        $save['lastname'] = '';
        if (count($arr_name) > 1) {
            $i = 0;
            $sur_name = '';
            foreach ($arr_name as $surname) {
                if ($i != 0) {
                    $sur_name = $sur_name . ' ' . $surname;
                }
                $i++;
            }
            $lastname = trim($sur_name);
        }//End Split name

        $buycoins_id = $this->input->post('buycoins_id');
        $coins = $this->input->post('coins');
        $card_no = $this->input->post('card');
        $card_type = $this->input->post('cardtype');
        $exp_month = $this->input->post('month');
        $exp_year = $this->input->post('year');
        $cvv2 = $this->input->post('cvv2');
        $address = $this->input->post('address');

        $country = $this->input->post('country');
        //Get Country Code
        $countryCode = $this->country_model->getcountry('country_id', $country, 1);
        if ($countryCode->country_code == 'GB') {
            $code = 1;
        } else {
            $code = 0;
        }
        //Get currency code
        $onecurency = $this->country_model->getcurrency($code);
        $currency = $onecurency->currency_code;

        $oneamount = $this->store_model->buyCoinsRow($buycoins_id);
        if ($currency == 'USD') {
            $amount = $oneamount->price_usd;
        } else {
            $amount = $oneamount->price_gbp;
        }
        $state = $this->input->post('state');
        $city = $this->input->post('city');
        $zip_code = $this->input->post('zip');

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

        //// CREATE AN STRING
        $nvpStr = "&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber" .
                "&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName&EMAIL=$emails" .
                "&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID";

        //// SEND REQUEST TO PAYPAL
        $httpParsedResponseAr = $this->PPHttpPost('DoDirectPayment', $nvpStr);
        if ($httpParsedResponseAr['ACK'] == 'Success') {
            $duration = '12';
            $date = date_create(date('Y-m-d'));    ///// CREATE OBJECT
            date_add($date, date_interval_create_from_date_string($duration . ' month'));     ///// ADD DURATION INTO
            $duration_month = date_format($date, 'Y-m-d');

            $payment['payment_id'] = '';
            $payment['user_id'] = $userid;
            $payment['buyCoins_id'] = $buycoins_id;
            $payment['purchasedOn'] = date('YmdHis');
            $payment['amount'] = urldecode($httpParsedResponseAr['AMT']);
            //$payment['currency']=urldecode($httpParsedResponseAr['CURRENCYCODE']);
            $payment['currency'] = $currency;
            $payment['transaction_id'] = $httpParsedResponseAr['TRANSACTIONID'];
            $payment['paymentInfo'] = 'Status: ' . $httpParsedResponseAr['ACK'] . '|CORRELATIONID : ' . $httpParsedResponseAr['CORRELATIONID'];
            $payment['createdDate'] = date('YmdHis');
            $payment['modifiedDate'] = date('YmdHis');

            $last_payment_id = $this->store_model->savepayment($payment);

            $totalCoins = $this->score_model->getUserCoins($userid);
            $totalCoins->coins;
            $update = array(
                'userid' => $userid,
                'coins' => $totalCoins->coins + $oneamount->coins,
            );
            $this->score_model->update($update);

            if ($type == 2) { // business user
                $insert = array(
                    'userid' => $userid,
                    'coins' => $oneamount->coins,
                    'coins_type' => 7,
                    'businessid' => $userid,
                    'actionType' => 'add',
                    'createdDate' => date('YmdHis')
                );
            }
            if ($type == 1) { // consumer user
                $insert = array(
                    'userid' => $userid,
                    'coins' => $oneamount->coins,
                    'coins_type' => 7,
                    'businessid' => 0,
                    'actionType' => 'add',
                    'createdDate' => date('YmdHis')
                );
            }
            $this->score_model->insert($insert);

//// SEND  EMAIL START
            $this->emailConfig();   //Get configuration of email
//// GET EMAIL FROM DATABASE

            if ($type == 1) { //For Consumer User
                $email_template = $this->email_model->getoneemail('buy_coins');
            }
            if ($type == 2) { //For Business User
                $email_template = $this->email_model->getoneemail('buy_coins_business');
            }

//// MESSAGE OF EMAIL
            $messages = $email_template->message;


            $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
            $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
            $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

//// replace strings from message
            $messages = str_replace('{Username}', ucfirst($username), $messages);
            $messages = str_replace('{Coins}', $oneamount->coins, $messages);
            if ($currency == 'USD') {
                $messages = str_replace('{Cost}', '$' . $amount, $messages);
            } else {
                $messages = str_replace('{Cost}', '&pound;' . $amount, $messages);
            }
            $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
            $messages = str_replace('{App_Store_Image}', $appstore, $messages);
            $messages = str_replace('{Google_Image}', $googleplay, $messages);

//// Email to user
            $this->email->from('hello@marketmyapp.co', 'Hurree');
            $this->email->to($email);
            $this->email->subject($email_template->subject);
            $this->email->message($messages);
            $this->email->send();    ////  EMAIL SEND
            //redirect('timeline');
            echo '1';
            exit;
            //echo $this->email->print_debugger();die;
            //echo '<p style="font-family:Roboto,sans-serif;text-align:center;margin-top:6%;">You just bought coins, go you!</p>';
            //echo '<p style="font-family:Roboto,sans-serif;text-align:center;">Transaction Id: '.$httpParsedResponseAr['TRANSACTIONID'].'</p>';
        } else {
            //print_r($httpParsedResponseAr );
            //redirect('home/paymentstatus/'.$httpParsedResponseAr['L_LONGMESSAGE0']);
            echo '<p style="font-family:Roboto,sans-serif;text-align:center;margin-top:6%;">' . $httpParsedResponseAr['L_LONGMESSAGE0'] . '</p>';
        }
    }

    function search() {

        $this->load->view('search_popup');
    }

    function replyPopUpUserProfile() {

        $replyid = $this->uri->segment(3);
        $data['page'] = '';
        $data['tab'] = '';
        if (@$_GET) {
            $data['page'] = $_GET['page'];
            $data['tab'] = @$_GET['tab'];
        }

        $data['login'] = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $status['status_id'] = $replyid;
        $statusdetails = $this->user_model->getStatusDetails($status);
        $data['statusdetails'] = $statusdetails;

        $originalPoster['status_id'] = $statusdetails->parentStatusid;
        $data['originalstatus'] = $this->user_model->getStatusDetails($originalPoster);

        $data['status'] = utf8_decode($statusdetails->status);
        /* echo '<pre>'; print_r($data); die;  */
        $this->load->view('reply_popup_userprofile', $data);
    }

    function replyPopUp() {

        $replyid = $this->uri->segment(3);
        $data['page'] = '';
        $data['tab'] = '';
        if (@$_GET) {
            $data['page'] = $_GET['page'];
            $data['tab'] = @$_GET['tab'];
        }

        $data['login'] = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $status['status_id'] = $replyid;
        $statusdetails = $this->user_model->getStatusDetails($status);
        $data['statusdetails'] = $statusdetails;

        $originalPoster['status_id'] = $statusdetails->parentStatusid;
        $data['originalstatus'] = $this->user_model->getStatusDetails($originalPoster);

        $data['status'] = utf8_decode($statusdetails->status);
        /* echo '<pre>'; print_r($data); die;  */
        $this->load->view('reply_popup', $data);
    }

    function postreplytoReply() {
        $mainStatusId = $this->uri->segment(3);
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $enteredUsername = $_POST['enteredUsername'];
        $videoThumb = '';
        //echo $enteredUsername; die;
        $page = $_POST['page'];
        //echo $page; die;
        if (@$_FILES['image']['size'] > 0) {

            $uploads_dir = 'upload/status_image/full/' . $login->user_id;
            $mediumImagePath = 'upload/status_image/medium/' . $login->user_id;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            $sucess = 1;
            // Image upload in full size in profile directory
            $this->load->library('image_lib');

            $tmp_name = $_FILES["image"]["tmp_name"];

            $name = mktime() . $_FILES["image"]["name"];
            $result = move_uploaded_file($tmp_name, "upload/status_image/full/" . $login->user_id . "/" . $name);

            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            // image resize in thumbnail size in thumbnail directory
            if (in_array($ext, $extionArray)) {
                // image resize in thumbnail size in thumbnail directory

                $config['image_library'] = 'gd2';
                $config['source_image'] = "upload/status_image/full/" . $login->user_id . "/" . $name;
                $config['new_image'] = 'upload/status_image/medium/' . $login->user_id . "/" . $name;

                $config['maintain_ratio'] = TRUE;
                $config['width'] = 400;
                $config['height'] = 350;
                $this->image_lib->initialize($config);
                $rtuenval = $this->image_lib->resize();
                $this->image_lib->clear();
                $status_image = $name;
                $videoThumb = '';
            } else {
                $videoThumbPath = 'upload/videoThumb' . '/' . $login->user_id;
                if (!is_dir($videoThumbPath)) {
                    if (mkdir($videoThumbPath, 0777, true)) {
                        $thumbPath = $videoThumbPath;
                    } else {
                        $thumbPath = $videoThumbPath;
                    }
                } else {
                    $thumbPath = $videoThumbPath;
                }
                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                $videothumb = strtotime(date('Ymdhis')) . 'thumb.jpg';
                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . ' -deinterlace -an -ss 5 -f mjpeg -t 1 -r 1 -y -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;
                //echo $cmd; exit;
                exec($cmd . ' ' . '2>&1', $out, $res);
                // print_r($out); exit;
                $videoThumb = $login->user_id . '/' . $videothumb;
            }
            $status_image = $login->user_id . "/" . $name;
        } else {

            $status_image = '';
        }


        $arr_username = explode(",", $enteredUsername);
        $arr_uniqueusername = array_unique($arr_username);

        $enteredUsername = implode(",", $arr_uniqueusername);

        $postedby = $this->input->post('postedby');
        $statusid = $mainStatusId;
        $reply = $this->input->post('reply');

        $insert = array(
            'parentStatusid' => $statusid,
            'status' => $reply,
            'userid' => $postedby,
            'status_image' => $status_image,
            'shareFrom' => 0,
            'share' => 0,
            'active' => 1,
            'usermentioned' => $enteredUsername,
            'media_thumb' => $videoThumb,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );
        //print_r($insert);die;
        $status_id = $this->user_model->savereply($insert);

        if ($enteredUsername != '') {
            //print_r($arr_uniqueusername); exit;
            foreach ($arr_uniqueusername as $oneuser) {

                if ($oneuser != $login->username) {

                    $arr_oneUser['username'] = $oneuser;

                    $arr_oneUser['active'] = 1;
                    $oneuserDetails = $this->user_model->getOneUserDetails($arr_oneUser, 'user_Id');
                    if (count($oneuserDetails) > 0) {
                        $arr_notice['notification_id'] = '';
                        $arr_notice['actionFrom'] = $login->user_id;
                        $arr_notice['actionTo'] = $oneuserDetails->user_Id;
                        $arr_notice['action'] = 'NM';
                        $arr_notice['actionString'] = 'mentioned you!';
                        $arr_notice['message'] = '';
                        $arr_notice['statusid'] = $statusid;
                        $arr_notice['challangeid'] = '';
                        $arr_notice['active'] = '1';
                        $arr_notice['createdDate'] = date('YmdHis');

                        $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                        if ($oneuserDetails->user_Id != $login->user_id) {
                            // echo  $oneuserDetails->user_Id; exit;
                            // send notification code start
                            $deviceInfo = $this->user_model->getdeviceToken($oneuserDetails->user_Id);
                            if (count($deviceInfo) > 0) {
                                foreach ($deviceInfo as $device) {
                                    $deviceToken = $device->key;
                                    $deviceType = $device->deviceTypeID;
                                    $title = 'My Test Message';
                                    $sound = 'default';
                                    $msgpayload = json_encode(array(
                                        'aps' => array(
                                            'alert' => 'Mention: ' . $login->username . ' mentioned you!',
                                            'statusid' => $statusid,
                                            "type" => 'usermention',
                                            'sound' => $sound
                                    )));


                                    $message = json_encode(array(
                                        'default' => $title,
                                        'APNS_SANDBOX' => $msgpayload
                                    ));

                                    $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                }
                            }

                            // end
                        }
                    }
                }
                $ch = curl_init();
                $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
                curl_setopt($ch, CURLOPT_URL, $to);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                $arr[] = "@" . $oneuser;
            }
        }
        //echo 'hassan';
        //echo $this->input->post('page');
        //die;
        if ($this->input->post('page') == 'timeline1' || $this->input->post('page') == 'search_results') {

            //echo '<p>Reply posted successfully</p>';
            $data['sigleRecord'] = 1;
            $data['timeline'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $status_id);
            /* echo '<pre>'; print_r($data['timeline']); die; */

            $data['loginuser'] = $login->user_id;
            $data['loggedinUserImage'] = $login->image;
            $data['loggedinUsername'] = $login->username;
            $data['loggedinUsertype'] = $login->usertype;
            $data['loginusername'] = $login->username;
            $this->load->view('addmoretimeline', $data);
        } else {
            $status['status_id'] = $status_id;
            $data['time'] = $this->user_model->getStatusDetails($status);
            $data['loginusername'] = $login->username;
            $data['parentStatusId'] = $statusid;
            $data['loginuser'] = $login->user_id;
            $data['loggedinUserImage'] = $login->image;
            $data['loggedinUsername'] = $login->username;
            $data['loggedinUsertype'] = $login->usertype;
            $data['user'] = $this->user_model->getOneUser($login->user_id); // confusion
            //// Load View Page
//            if ($page == '') {
//
//            } else {
//             $data['page']='timeline';
//            }
            $data['page'] = 'timeline';
            $data['sigleRecord'] = 'reply';

            if (count($data['time']) > 0) {
                $this->load->view('addmoretimeline', $data);
            }
        }
    }

    function postreplytoReplyUserprofile() {

        $mainStatusId = $this->uri->segment(3);
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $enteredUsername = $_POST['enteredUsername'];

        $page = $_POST['page'];
        $postedby = $this->input->post('postedby');
        $videoThumb = '';
        if (@$_FILES['image']['size'] > 0) {
            $sucess = 1;

            // Image upload in full size in profile directory
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $login->user_id;
            $mediumImagePath = 'upload/status_image/medium/' . $login->user_id;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $tmp_name = $_FILES["image"]["tmp_name"];
            $name = mktime() . $_FILES["image"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");
            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, $extionArray)) {
                // image resize in medium size in medium directory
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = "upload/status_image/full/" . $login->user_id . "/" . $name;
                $config['new_image'] = 'upload/status_image/medium/' . $login->user_id . "/" . $name;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 400;
                $config['height'] = 350;
                $this->image_lib->initialize($config);
                $rtuenval = $this->image_lib->resize();
                $this->image_lib->clear();
            } else {
                $videoThumbPath = 'upload/videoThumb' . '/' . $login->user_id;
                if (!is_dir($videoThumbPath)) {
                    if (mkdir($videoThumbPath, 0777, true)) {
                        $thumbPath = $videoThumbPath;
                    } else {
                        $thumbPath = $videoThumbPath;
                    }
                } else {
                    $thumbPath = $videoThumbPath;
                }
                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                $videothumb = strtotime(date('Ymdhis')) . 'thumb.jpg';
                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . ' -deinterlace -an -ss 5 -f mjpeg -t 1 -r 1 -y -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;

                exec($cmd . ' ' . '2>&1', $out, $res);
                // print_r($out); exit;
                $videoThumb = $login->user_id . '/' . $videothumb;
            }
            $status_image = $login->user_id . "/" . $name;
            // Image Upload End
        } else {
            $status_image = '';
        }


        $arr_username = explode(",", $enteredUsername);

        $arr_uniqueusername = array_unique($arr_username);

        $enteredUsername = implode(",", $arr_uniqueusername);

        $postedby = $this->input->post('postedby');
        $statusid = $mainStatusId;
        $reply = $this->input->post('reply');

        $insert = array(
            'parentStatusid' => $statusid,
            'status' => $reply,
            'userid' => $postedby,
            'status_image' => $status_image,
            'media_thumb' => $videoThumb,
            'shareFrom' => 0,
            'share' => 0,
            'active' => 1,
            'usermentioned' => $enteredUsername,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );
        //print_r($insert);die;
        $status_id = $this->user_model->savereply($insert);

        //#tag isert
        $newstatus = strip_tags($reply);
        preg_match_all('/#([^\s]+)/', $newstatus, $matches);
        if (count($matches) > 0) {

            $this->user_model->saveUserHashtag($matches[1]);
        }

        if ($enteredUsername != '') {
            //echo $enteredUsername; die;
            //print_r($arr_uniqueusername); die;
            foreach ($arr_uniqueusername as $oneuser) {
                //echo 'Oneuser:'.$login->username; die;
                if (trim($oneuser) == trim($login->username)) {
                    
                } else {
                    $arr_oneUser = array();
                    $arr_oneUser['username'] = $oneuser;
                    $arr_oneUser['active'] = 1;
                    $oneuserDetails = $this->user_model->getOneUserDetails($arr_oneUser, '*');
                    //echo $this->db->last_query();die;
                    //print_r($oneuserDetails);die;
                    if (count($oneuserDetails) > 0) {
                        $arr_notice['notification_id'] = '';
                        $arr_notice['actionFrom'] = $login->user_id;
                        $arr_notice['actionTo'] = $oneuserDetails->user_Id;
                        $arr_notice['action'] = 'NM';
                        $arr_notice['actionString'] = ' mentioned you!';
                        $arr_notice['message'] = '';
                        $arr_notice['statusid'] = $statusid;
                        $arr_notice['challangeid'] = '';
                        $arr_notice['active'] = '1';
                        $arr_notice['createdDate'] = date('YmdHis');

                        $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                        // send notification code start
                        $deviceInfo = $this->user_model->getdeviceToken($oneuserDetails->user_Id);
                        if (count($deviceInfo) > 0) {
                            foreach ($deviceInfo as $device) {
                                $deviceToken = $device->key;
                                $deviceType = $device->deviceTypeID;
                                $title = 'My Test Message';
                                $sound = 'default';
                                $msgpayload = json_encode(array(
                                    'aps' => array(
                                        'alert' => 'Mention: ' . $login->username . ' mentioned you!',
                                        'statusid' => $statusid,
                                        "type" => 'usermention',
                                        'sound' => $sound
                                )));


                                $message = json_encode(array(
                                    'default' => $title,
                                    'APNS_SANDBOX' => $msgpayload
                                ));

                                $result = $this->amazonSns($deviceToken, $message, $deviceType);
                            }
                        }

                        // end
                    }
                }
                $arr[] = "@" . $oneuser;
            }
        }


        $status['status_id'] = $status_id;
        $data['time'] = $this->user_model->getStatusDetailsUserProfile($status);
        //$data['time'] = $this->user_model->getStatusDetails($status);
        //$data['time'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $status_id);
        /* echo '<pre>'; print_r($data['timeline']); die; */


        $data['parentStatusId'] = $statusid;
        $data['loginuser'] = $login->user_id;
        $data['loggedinUserImage'] = $login->image;
        $data['loggedinUsername'] = $login->username;
        $data['loggedinUsertype'] = $login->usertype;
        //// Load View Page
        //$data['sigleRecord'] = 1;
        $data['sigleRecord'] = 'replyFP';

        $this->load->helper('follow');
        $this->load->helper('like');
        //$data['reply'] = 0;

        $ch = curl_init();
        $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);

        $this->load->view('addmoretimeline', $data);
    }

    function postreplytoReplyUserprofilePhotos() {

        $mainStatusId = $this->uri->segment(3);
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $enteredUsername = $_POST['enteredUsername'];
        $page = $_POST['pageName'];
        $postedby = $this->input->post('postedby');
        $videoThumb = '';
        if (@$_FILES['ImageReplyFile']['size'] > 0) {
            $sucess = 1;

            // Image upload in full size in profile directory
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $login->user_id;
            $mediumImagePath = 'upload/status_image/medium/' . $login->user_id;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $tmp_name = $_FILES["ImageReplyFile"]["tmp_name"];
            $name = mktime() . $_FILES["ImageReplyFile"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");
            $ext = strtolower(pathinfo($_FILES["ImageReplyFile"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, $extionArray)) {
                // image resize in medium size in medium directory
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = "upload/status_image/full/" . $login->user_id . "/" . $name;
                $config['new_image'] = 'upload/status_image/medium/' . $login->user_id . "/" . $name;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 400;
                $config['height'] = 350;
                $this->image_lib->initialize($config);
                $rtuenval = $this->image_lib->resize();
                $this->image_lib->clear();
            } else {
                $videoThumbPath = 'upload/videoThumb' . '/' . $login->user_id;
                if (!is_dir($videoThumbPath)) {
                    if (mkdir($videoThumbPath, 0777, true)) {
                        $thumbPath = $videoThumbPath;
                    } else {
                        $thumbPath = $videoThumbPath;
                    }
                } else {
                    $thumbPath = $videoThumbPath;
                }
                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                $videothumb = strtotime(date('Ymdhis')) . 'thumb.jpg';
                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . ' -deinterlace -an -ss 5 -f mjpeg -t 1 -r 1 -y -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;

                exec($cmd . ' ' . '2>&1', $out, $res);
                // print_r($out); exit;
                $videoThumb = $login->user_id . '/' . $videothumb;
            }

            $status_image = $login->user_id . "/" . $name;
            // Image Upload End
        } else {
            $status_image = '';
        }


        $arr_username = explode(",", $enteredUsername);

        $arr_uniqueusername = array_unique($arr_username);

        $enteredUsername = implode(",", $arr_uniqueusername);

        $postedby = $this->input->post('postedby');
        $statusid = $mainStatusId;
        $reply = $this->input->post('reply');

        $insert = array(
            'parentStatusid' => $statusid,
            'status' => $reply,
            'userid' => $postedby,
            'status_image' => $status_image,
            'media_thumb' => $videoThumb,
            'shareFrom' => 0,
            'share' => 0,
            'active' => 1,
            'usermentioned' => $enteredUsername,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );
        //print_r($insert);die;
        $status_id = $this->user_model->savereply($insert);

        //#tag insert
        $newstatus = strip_tags($reply);
        preg_match_all('/#([^\s]+)/', $newstatus, $matches);
        if (count($matches) > 0) {

            $this->user_model->saveUserHashtag($matches[1]);
        }

        if ($enteredUsername != '') {
            //echo $enteredUsername; die;
            //print_r($arr_uniqueusername); die;
            foreach ($arr_uniqueusername as $oneuser) {
                //echo 'Oneuser:'.$login->username; die;
                if (trim($oneuser) == trim($login->username)) {
                    
                } else {
                    $arr_oneUser = array();
                    $arr_oneUser['username'] = $oneuser;
                    $arr_oneUser['active'] = 1;
                    $oneuserDetails = $this->user_model->getOneUserDetails($arr_oneUser, '*');
                    //echo $this->db->last_query();die;
                    //print_r($oneuserDetails);die;
                    if (count($oneuserDetails) > 0) {
                        $arr_notice['notification_id'] = '';
                        $arr_notice['actionFrom'] = $login->user_id;
                        $arr_notice['actionTo'] = $oneuserDetails->user_Id;
                        $arr_notice['action'] = 'NM';
                        $arr_notice['actionString'] = ' mentioned you!';
                        $arr_notice['message'] = '';
                        $arr_notice['statusid'] = $statusid;
                        $arr_notice['challangeid'] = '';
                        $arr_notice['active'] = '1';
                        $arr_notice['createdDate'] = date('YmdHis');

                        $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                        // send notification code start
                        $deviceInfo = $this->user_model->getdeviceToken($oneuserDetails->user_Id);
                        if (count($deviceInfo) > 0) {
                            foreach ($deviceInfo as $device) {
                                $deviceToken = $device->key;
                                $deviceType = $device->deviceTypeID;
                                $title = 'My Test Message';
                                $sound = 'default';
                                $msgpayload = json_encode(array(
                                    'aps' => array(
                                        'alert' => 'Mention: ' . $login->username . ' mentioned you!',
                                        'statusid' => $statusid,
                                        "type" => 'usermention',
                                        'sound' => $sound
                                )));


                                $message = json_encode(array(
                                    'default' => $title,
                                    'APNS_SANDBOX' => $msgpayload
                                ));

                                $result = $this->amazonSns($deviceToken, $message, $deviceType);
                            }
                        }

                        // end
                    }
                }
                $arr[] = "@" . $oneuser;
            }
        }


        $status['status_id'] = $status_id;
        $data['time'] = $this->user_model->getStatusDetailsUserProfile($status);
        //$data['time'] = $this->user_model->getStatusDetails($status);
        //$data['time'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $status_id);
        //echo '<pre>'; print_r($data['time']); die;

        $data['parentStatusId'] = $statusid;
        $data['loginuser'] = $login->user_id;
        $data['loggedinUserImage'] = $login->image;
        $data['loggedinUsername'] = $login->username;
        $data['loggedinUsertype'] = $login->usertype;
        //// Load View Page
        //$data['sigleRecord'] = 1;
        $data['sigleRecord'] = 'replyPhotosFP';

        $this->load->helper('follow');
        $this->load->helper('like');

        $ch = curl_init();
        $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);

        $this->load->view('addmoretimeline', $data);
    }

    function postreplytoReplyUserprofileLikes() {

        $mainStatusId = $this->uri->segment(3);
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $login = $this->administrator_model->front_login_session();   //// Get Login Session Details
        $enteredUsername = $_POST['enteredUsername'];
        $page = $_POST['page'];
        $postedby = $this->input->post('postedby');
        $videoThumb = '';
        if (@$_FILES['ImageReplyFile']['size'] > 0) {
            $sucess = 1;

            // Image upload in full size in profile directory
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $login->user_id;
            $mediumImagePath = 'upload/status_image/medium/' . $login->user_id;
            if (!is_dir($mediumImagePath)) {
                if (mkdir($mediumImagePath, 0777, true)) {
                    $mediumpath = $mediumImagePath;
                } else {
                    $mediumpath = $mediumImagePath;
                }
            } else {
                $mediumpath = $mediumImagePath;
            }

            if (!is_dir($uploads_dir)) {
                if (mkdir($uploads_dir, 0777, true)) {
                    $path = $uploads_dir;
                } else {
                    $path = $uploads_dir;
                }
            } else {
                $path = $uploads_dir;
            }

            $tmp_name = $_FILES["ImageReplyFile"]["tmp_name"];
            $name = mktime() . $_FILES["ImageReplyFile"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");
            $ext = strtolower(pathinfo($_FILES["ImageReplyFile"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, $extionArray)) {
                // image resize in medium size in medium directory
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = "upload/status_image/full/" . $login->user_id . "/" . $name;
                $config['new_image'] = 'upload/status_image/medium/' . $login->user_id . "/" . $name;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 400;
                $config['height'] = 350;
                $this->image_lib->initialize($config);
                $rtuenval = $this->image_lib->resize();
                $this->image_lib->clear();
            } else {
                $videoThumbPath = 'upload/videoThumb' . '/' . $login->user_id;
                if (!is_dir($videoThumbPath)) {
                    if (mkdir($videoThumbPath, 0777, true)) {
                        $thumbPath = $videoThumbPath;
                    } else {
                        $thumbPath = $videoThumbPath;
                    }
                } else {
                    $thumbPath = $videoThumbPath;
                }
                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                $videothumb = strtotime(date('Ymdhis')) . 'thumb.jpg';
                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . ' -deinterlace -an -ss 5 -f mjpeg -t 1 -r 1 -y -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;

                exec($cmd . ' ' . '2>&1', $out, $res);
                // print_r($out); exit;
                $videoThumb = $login->user_id . '/' . $videothumb;
            }

            $status_image = $login->user_id . "/" . $name;
            // Image Upload End
        } else {
            $status_image = '';
        }


        $arr_username = explode(",", $enteredUsername);

        $arr_uniqueusername = array_unique($arr_username);

        $enteredUsername = implode(",", $arr_uniqueusername);

        $postedby = $this->input->post('postedby');
        $statusid = $mainStatusId;
        $reply = $this->input->post('reply');

        $insert = array(
            'parentStatusid' => $statusid,
            'status' => $reply,
            'userid' => $postedby,
            'status_image' => $status_image,
            'media_thumb' => $videoThumb,
            'shareFrom' => 0,
            'share' => 0,
            'active' => 1,
            'usermentioned' => $enteredUsername,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );
        //print_r($insert);die;
        $status_id = $this->user_model->savereply($insert);

        //#tag insert
        $newstatus = strip_tags($reply);
        preg_match_all('/#([^\s]+)/', $newstatus, $matches);
        if (count($matches) > 0) {

            $this->user_model->saveUserHashtag($matches[1]);
        }

        if ($enteredUsername != '') {
            //echo $enteredUsername; die;
            //print_r($arr_uniqueusername); die;
            foreach ($arr_uniqueusername as $oneuser) {
                //echo 'Oneuser:'.$login->username; die;
                if (trim($oneuser) == trim($login->username)) {
                    
                } else {
                    $arr_oneUser = array();
                    $arr_oneUser['username'] = $oneuser;
                    $arr_oneUser['active'] = 1;
                    $oneuserDetails = $this->user_model->getOneUserDetails($arr_oneUser, '*');
                    //echo $this->db->last_query();die;
                    //print_r($oneuserDetails);die;
                    if (count($oneuserDetails) > 0) {
                        $arr_notice['notification_id'] = '';
                        $arr_notice['actionFrom'] = $login->user_id;
                        $arr_notice['actionTo'] = $oneuserDetails->user_Id;
                        $arr_notice['action'] = 'NM';
                        $arr_notice['actionString'] = ' mentioned you!';
                        $arr_notice['message'] = '';
                        $arr_notice['statusid'] = $statusid;
                        $arr_notice['challangeid'] = '';
                        $arr_notice['active'] = '1';
                        $arr_notice['createdDate'] = date('YmdHis');

                        $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                        // send notification code start
                        $deviceInfo = $this->user_model->getdeviceToken($oneuserDetails->user_Id);
                        if (count($deviceInfo) > 0) {
                            foreach ($deviceInfo as $device) {
                                $deviceToken = $device->key;
                                $deviceType = $device->deviceTypeID;
                                $title = 'My Test Message';
                                $sound = 'default';
                                $msgpayload = json_encode(array(
                                    'aps' => array(
                                        'alert' => 'Mention: ' . $login->username . ' mentioned you!',
                                        'statusid' => $statusid,
                                        "type" => 'usermention',
                                        'sound' => $sound
                                )));


                                $message = json_encode(array(
                                    'default' => $title,
                                    'APNS_SANDBOX' => $msgpayload
                                ));

                                $result = $this->amazonSns($deviceToken, $message, $deviceType);
                            }
                        }

                        // end
                    }
                }
                $arr[] = "@" . $oneuser;
            }
        }


        $status['status_id'] = $status_id;
        $data['time'] = $this->user_model->getStatusDetailsUserProfile($status);
        //$data['time'] = $this->user_model->getStatusDetails($status);
        //$data['time'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $status_id);
        /* echo '<pre>'; print_r($data['timeline']); die; */

        $data['parentStatusId'] = $statusid;
        $data['loginuser'] = $login->user_id;
        $data['loggedinUserImage'] = $login->image;
        $data['loggedinUsername'] = $login->username;
        $data['loggedinUsertype'] = $login->usertype;
        //// Load View Page
        //$data['sigleRecord'] = 1;
        $data['sigleRecord'] = 'replyLikesFP';

        $this->load->helper('follow');
        $this->load->helper('like');

        $ch = curl_init();
        $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);

        $this->load->view('addmoretimeline', $data);
    }

    function ajaxChallangeUpdate() {
        $header['login'] = $this->administrator_model->front_login_session();
        $challangeid = $_POST['challangeid'];
        $result = '';
        $val = $challangeid;
        $where = 'challenge_id';
        $challangeDetails = $this->challenge_model->getchallanges($where, $val);
        $gameid = $challangeDetails->game_id;
        $game = $this->games_model->getGame($gameid);
        $gameName = $game->gameName;

        $loginuser_id = $header['login']->user_id;

        //$subscription= $this->subscription_model->getusersubscription('user_id',$loginuser_id,1,1);
        //$subscription= $this->subscription_model->getusersubscription('user_id',$loginuser_id,1,1,'game_id',$gameid);

        $arr_game['user_id'] = $loginuser_id;
        $arr_game['active'] = 1;

        $subsciptionGameId = array();
        $subscription = $this->games_model->getUserOneGameSubscription($arr_game, 1);


        if (count($subscription) > 0) {    //if(!empty($subscription))
            foreach ($subscription as $val) {
                $subsciptionGameId[] = $val->game_id;
            }
            //if($subscription->game_id==$gameid ||  $subscription->game_id=='5')
            if (in_array($gameid, $subsciptionGameId) || in_array(5, $subsciptionGameId)) {
                $userCoin = $this->score_model->getUserCoins($header['login']->user_id);

                if ($challangeDetails->challenge_coins <= $userCoin->coins) {
                    $arr_challange['challenge_id'] = $challangeid;
                    $arr_challange['approval'] = 1;
                    $arr_challange['modifiedDate'] = date("YmdHis");
                    $this->challenge_model->update($arr_challange);
                    $result = '1';
                } else {
                    $result = "You don't have enough coins, buy some?"; //// nNot Enough Coins
                }
            } else {
                $result = "You don’t own this game, buy " . $gameName;
            }
        } else {
            $result = "You don’t own this game, buy " . $gameName;
        }

        echo $result;
    }

    function notification() {
        //echo '<pre>';
        $baseurl = base_url();
        $login = $this->administrator_model->front_login_session();
        $data['user'] = $login;
        /* echo '<pre>'; print_r($login); die; */
        $select = "*";
        $arr_notification['actionTo'] = $login->user_id;
        $arr_notification['notification.active'] = 1;
        $arr_notification['notification.isDelete'] = 0;
        $arr_notification['actionFrom !='] = $login->user_id;
        //$arr_notification['notification.is_new']=1;

        $records = $this->notification_model->getnotification($arr_notification, $row = 1, $select = '*', '', '', $totalrecords = 1);   //// Get Total No of Records in Database

        $config['base_url'] = base_url() . 'index.php/home/notification/';
        $config['total_rows'] = $records;
        $config['per_page'] = '6';
        $config['uri_segment'] = 3;

        $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['page'] = $page;

        $limit = $config['per_page'];
        $order_by['order_by'] = 'distance';
        $order_by['sequence'] = 'DESC';

        $select = ' CONCAT("@", UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username , firstname, lastname, image, user_Id, image , actionString , action,  notification.createdDate as postedDate, notification.*, ( case when (usertype = 1 or usertype = 4) THEN  CONCAT_WS( " ", users.firstname, users.lastname ) ELSE  users.businessName END ) as name, challenge.game_id';

        $data['notification'] = $this->notification_model->getnotification($arr_notification, $row = 1, $select, $page, $limit);

        $arr_update['is_new'] = 0;
        $notWhere['where'] = 'actionTo';
        $notWhere['val'] = $login->user_id;
        $this->notification_model->updateNotification($arr_update, $notWhere);
        //echo '<pre>';print_r($data);die;
        $this->load->view('notification', $data);
    }

    function searchResults() {
        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            $str = $this->input->get('search');

            if (strpos($str, '@') !== false && strpos($str, "@") == 0) {
                $username = str_replace('@', "", $str);
                $where['username'] = $username;
                $searchedUser = $this->user_model->getOneUserDetails($where, '*');
                if (count($searchedUser) > 0) {
                    $data['searchedUserId'] = $searchedUser->user_Id;
                    $data['searchedUserFullName'] = $searchedUser->firstname . ' ' . $searchedUser->lastname;
                    $data['searchedUserUsername'] = $searchedUser->username;
                    $data['searchedUserimage'] = $searchedUser->image;
                    $data['searchedUserBio'] = $searchedUser->bio;
                    $data['searchedUserType'] = $searchedUser->usertype;
                    $data['searchedUserBusinessName'] = $searchedUser->businessName;
                } else {
                    $data['searchedUserId'] = '';
                }

                //$this->user_model->search_username();
            } else {
                $data['searchedUserId'] = '';
            }

            $search = $this->unicode($str);

            $data['loginuser'] = $header['login']->user_id;

            $data['loggedinUsername'] = $header['login']->username;
            $data['loggedinUserImage'] = $header['login']->image;
            $data['loggedinUsertype'] = $header['login']->usertype;
            $search = trim($search);
            $data['search'] = $search;

            $data['timeline'] = $this->user_model->search_results($search, '', '', $count = 1, '', '', $header['login']->user_id);   //// Get Total No of Records in Database

            $config['base_url'] = base_url() . 'index.php/home/searchResults?search=' . $search;
            $config['total_rows'] = count($data['timeline']);    //count($data['records']); for See more activities

            $config['per_page'] = '10';
            $config['uri_segment'] = 3;

            $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
            $data['page'] = $page;
            $data['equal_height'] = 1;
            $data['statuscount'] = count($data['timeline']);
            $data['noofstatus'] = $config['per_page'];
            $data['records'] = count($data['timeline']);   ////count($data['records']); for See more activities
            $data['peoples'] = $this->user_model->getpeopletofollow($header['login']->user_id, '1');
            $data['businesses'] = $this->user_model->getpeopletofollow($header['login']->user_id, '2');
            if ($this->input->get('searchpage') == '') {
                $data['searchpage'] = '';
                $this->load->view('search_results', $data);
            } else {
                $data['searchpage'] = 1;
                $this->load->view('search_results2', $data);
            }
        } else {
            //$this->session->set_flashdata('error_message','Session Expired. Please Sign in again');
            $this->session->set_flashdata('error_message', $header['login']->message);
            redirect("home/signup");
        }
    }

    function unicode($str) {
        $str = str_ireplace("u0023", "#", $str);
        $str = str_ireplace("u0026", "&", $str);
        $str = str_ireplace("u002B", "+", $str);
        return $str;
    }

    function status_delete() {

        $statusid = $_POST['statusid'];

        $login = $this->administrator_model->front_login_session();
        $arr_statusDetails['status_id'] = $statusid;
        $status = $this->status_model->getOneStatus($arr_statusDetails, "status_image");

        $image = $status->status_image;
        if ($image != '') {
            //// GET PATH OF DIRECTORY
            $path = getcwd();
            //// CREATE PATH OF PDF
            $filepath1 = $path . '/upload/status_image/full/' . $image;
            $filepath2 = $path . '/upload/status_image/medium/' . $image;

            //// UNLINK PREVIOUS PDF
            $responce1 = unlink($filepath1);
            $responce2 = unlink($filepath2);
        }
        $arr_status['status_id'] = $statusid;
        $arr_status['userid'] = $login->user_id;
        $arr_status['active'] = 0;
        $arr_status['isDelete'] = 1;

        $this->status_model->delete_status($arr_status);

        $update_like['statusId'] = $statusid;
        $update_like['userid'] = $login->user_id;
        $update_like['active'] = 0;
        $this->status_model->update_likestatus($update_like);

        $statusData = $this->user_model->getStatusData($statusid);
        preg_match_all('/#([^\s]+)/', $statusData->status, $matches);

        // decrement hashtag count when status delete
        $this->user_model->decHashTagCount($matches[0]);
    }

    function submit_offer() {

        $data['regions'] = $this->store_model->store_regions();

        $login = $this->administrator_model->front_login_session();
        $where['userid'] = $login->user_id;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);

        $data['message'] = '';
        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }

        $this->load->view('submitoffer', $data);
    }

    function business() {

        redirect(base_url());

        $data['business'] = 1;
        $data['business_category'] = $this->user_model->getCategory();

        $data['countries'] = $this->country_model->get_countries();

        $arr_card_type['active'] = 1;
        $data['card_type'] = $this->user_model->getcardtype($arr_card_type, $row = 1);

        //Check referral name is present or not
        $referral_name = $this->uri->segment(3);
        if ($referral_name != '') {
            $details = $this->user_model->referral_check($referral_name);
            $data['ambassador_id'] = $details->user_Id;
        } else {
            $data['ambassador_id'] = '';
        }

        $this->load->view('front_header', $data);
        $this->load->view('business', $data);
        $this->load->view('front_footer', $data);
    }

    function businessSignUp() {

        $business = 'business';
        $this->session->set_userdata('business_signup', $business);
        redirect("home/signup");
    }

    function consumerSignUp() {

        $business = 'consumer';
        $this->session->set_userdata('business_signup', $business);
        redirect("home/signup");
    }

    function ambassadorSignUp() {

        $data['referral'] = '';
        $data['name'] = '';
        $data['hear_about_us'] = '';
        $data['company_name'] = '';
        $data['website'] = '';
        $data['countries'] = $this->country_model->get_countries();
        $this->load->view('header');
        $this->load->view('ambassador_signup', $data);
        $this->load->view('footer');
    }

    function uniquereferral() {

        $referral = $this->input->post('referral');

        $arr_user['referral_name'] = $referral;
        //$arr_user['active']=1;

        $checkReferral = $this->user_model->getOneUserDetails($arr_user, '*');

        echo count($checkReferral);
    }

    function ambassador_signup() {
        $companyName = $this->input->post('company_name');
        $referral = $this->input->post('referral');
        $firstname = $this->input->post('firstname');
        $lastname = $this->input->post('lastname');
        $website = $this->input->post('website');
        $hear_about_us = $this->input->post('hear_about_us');
        $description = $this->input->post('description');
        $email = $this->input->post('email');
        $username = $this->input->post('username');
        $password = md5($this->input->post('password'));
        $contact_number = $this->input->post('contact_number');
        $country = $this->input->post('country');
        $address1 = $this->input->post('address1');
        $address2 = $this->input->post('address2');
        $town = $this->input->post('town');
        $postCode = $this->input->post('postCode');
        $paypal_email = $this->input->post('paypal_email');
        $userTable = array(
            'user_Id' => '',
            'firstname' => $firstname,
            'lastname' => $lastname,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'country' => $country,
            'usertype' => 4,
            'image' => 'user.png',
            'header_image' => 'profileBG.jpg',
            'active' => 1,
            'loginSource' => 'normal',
            'referral_name' => $referral,
            'companyName' => $companyName,
            'website' => $website,
            'hear_about_us' => $hear_about_us,
            'description' => $description,
            'contactNumber' => $contact_number,
            'country' => $country,
            'address1' => $address1,
            'address2' => $address2,
            'town' => $town,
            'postCode' => $postCode,
            'paypal_email' => $paypal_email,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );

        $last_id = $this->user_model->insertsignup($userTable);
        //	echo $this->db->last_query(); die;

        /* Insert Coins */
        $coins = array(
            'userid' => $last_id,
            'coins' => '100'
        );
        $this->score_model->signupCoins($coins);

        $userCoins = array(
            'userid' => $last_id,
            'coins' => 100,
            'coins_type' => 8,
            'game_id' => 0,
            'businessid' => 0,
            'actionType' => 'add',
            'createdDate' => date('YmdHis')
        );
        $this->score_model->insertCoins($userCoins);

        /* $session = array();

          $session = (object) array(
          'user_Id' => $last_id,
          'username' => $username,
          'password' => $password,
          'email' => $email,
          'active' => 1,
          'usertype' => 4,
          'firstname' => $firstname,
          'lastname' => $lastname,
          'image' => '',
          'accesslevel' => 'ambassador'
          );

          $this->session->set_userdata('logged_in', $session); */    //// Create Login Session
        //// SEND  EMAIL START
        $this->emailConfig();   //Get configuration of email
        //// GET EMAIL FROM DATABASE

        $email_template = $this->email_model->getoneemail('ambassador_signup');

        //// MESSAGE OF EMAIL
        $messages = $email_template->message;

        $hurree_image = base_url() . 'assets/template/frontend/img/app-icon.png';
        $appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
        $googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';
        //$referral ='http://www.hurree.co/'.$referral;
        $referral = "<a href=" . base_url() . 'home/business/' . $referral . " style='text-decoration:none; ' target='_blank'>" . base_url() . 'home/business/' . $referral . "</a>";
        //// replace strings from message
        $messages = str_replace('{Username}', ucfirst($username), $messages);
        $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
        $messages = str_replace('{App_Store_Image}', $appstore, $messages);
        $messages = str_replace('{Google_Image}', $googleplay, $messages);
        $messages = str_replace('{Insert Unique URL}', $referral, $messages);


        //// FROM EMAIL
        $this->email->from('hello@marketmyapp.co', 'Hurree');
        $this->email->to($email);
        $this->email->subject($email_template->subject);
        $this->email->message($messages);
        $this->email->send();    ////  EMAIL SEND

        echo '1';
    }

    function referralSignup($referral_name) {

        $details = $this->user_model->referral_check($referral_name);
        //print_r($details);die;
        //echo $details->user_Id;die;
        $data['ambassador_id'] = $details->user_Id;
        if (count($details) > 0) {
            $this->load->view('signup1', $data);
        } else {
            redirect('home');
        }
    }

    function create_offer() {
        $data['user'] = $this->administrator_model->front_login_session();
        $this->load->view('create_offer', $data);
    }

    function previewQRcode() {

        $userid = $this->uri->segment(3);
        $data['qrcode'] = $this->qrcode_model->getQRcodeDetails($userid);
        //echo '<pre>';
        //print_r($data['qrcode']);die;
        if (count($data['qrcode']) > 0) {
            $this->load->view('preview_qr_code', $data);
        } else {
            echo '<p>First generate QR Code</p>';
        }
    }

    function qrcode() {
        $userid = $_POST['user_id'];
        $offerName = $_POST['offer_name'];
        $date = $_POST['date'];
        $coins = $_POST['coins'];
        $qrcode = $this->qrcode_model->getQRcodeDetails($userid);

        if ($offerName != '' && $date != '' && $coins != '') {
            if (!empty($qrcode)) {
                $update = array(
                    'qrcode_id' => $qrcode->qrcode_id,
                    'userid' => $userid,
                    'active' => 0,
                );

                $this->qrcode_model->update($update);
            }

            $busineesUser = $this->user_model->getOneUser($userid);
            $insert = array(
                'userid' => $userid,
                'offer_name' => $offerName,
                'coins' => $coins,
                'validFrom' => date('YmdHis'),
                'validTill' => date("Y-m-d", strtotime($date)),
                'active' => 1,
                'createdDate' => date('YmdHis'),
                'modifiedDate' => date('YmdHis')
            );


            $qrcode_id = $this->qrcode_model->insert($insert);

            //Insert for status
            $status = array(
                'status_id' => '',
                'status' => 'Just created an offer for ' . $coins . ' coins, visit us to get some coins!',
                'userid' => $userid,
                'shareFrom' => 0,
                'share' => 0,
                'usermentioned	' => $busineesUser->username,
                'active' => 1,
                'createdDate' => date('YmdHis'),
                'modifiedDate' => date('YmdHis')
            );
            $this->user_model->saveUserStatus($status);
        }

        if ($offerName == '' || $date == '' || $coins == '') {
            $this->session->set_flashdata('validation', 'Please enter required fields');
            redirect('timeline');
        }
        return true;
        //redirect('timeline');
    }

    function pdf() {
        $this->load->helper('pdf_helper');

        $id = $this->uri->segment(3);
        $arr_where['qrcode_id'] = $id;
        $arr_where['active'] = 1;
        $data['qrcode'] = $this->qrcode_model->getQrCode('*', $arr_where);
        //print_r($qrcode);die;
        $this->load->view('pdfreport', $data);
    }

    function getUpdate() {
        $this->load->view("getupdate");
    }

    function newChatMessage() {
        $login = $this->administrator_model->front_login_session();
        $lastmessage_id = $_POST['messageid'];
        /* echo $lastmessage_id; die; */
        $seconsuserId = $_POST['secondusername'];

        if ($seconsuserId == 'undefined') {
            echo 0;
            die;
        }
        $message = $this->message_model->getReceivedMessage($login->user_id, $seconsuserId, NULL, $lastmessage_id);
        //		echo $this->db->last_query(); die;
        $max = $this->message_model->get_max_id($login->user_id, $seconsuserId);
        $message_id = $max->message_id;
        $return[] = $message;
        $return[] = $message_id;
        echo json_encode($return);
    }

    function newNotification() {
        $login = $this->administrator_model->front_login_session();
        $loginuser = $login->user_id;

        $arr_notice['actionTo'] = $loginuser;
        $arr_notice['isDelete'] = '0';
        $arr_notice['active'] = '1';
        $arr_notice['is_new'] = '1';
        $notice = $this->notification_model->getOneNotification($arr_notice, '1', $loginuser);

        $cnt_notice = count($notice);

        echo $cnt_notice;
    }

    function getPromocode() {
        $promocode['promo_code'] = $_POST['promocode'];
        $promocode['active'] = 1;

        $promo = $this->promocode_model->getpromocode($promocode);
        if (count($promo) == 0) {
            $code[] = 'Error';
            $code[] = 'Incorrect Promocode';
        } else {
            $valid_from = $promo->valid_from;
            $valid_till = $promo->valid_till;
            $currentDate = date('Y-m-d');
            if ($valid_from <= $currentDate && $valid_till >= $currentDate) {
                /* $discount */
                $code[] = 'sucess';
                $code[] = $promo;
            } else {
                $code[] = 'Error';
                $code[] = 'Promocode is expired';
            }
        }

        echo json_encode($code);
    }

    function confirmEmail() {
        if ($_POST) {
            $sucess = false;

            $generatePass = $this->RandomString();
            $password = md5($generatePass);

            $email = $_POST['email'];
            $date = $_POST['date'];
            $month = $_POST['month'];
            $year = $_POST['year'];
            $gender = $_POST['gender'];
            $fb = $_POST['fb'];

            if ($fb == 0) {
                $this->db->select_max('user_Id');
                $result = $this->db->get('users')->result_array();
                foreach ($result as $row) {
                    $userId = $row['user_Id'];
                }

                $sql = "SELECT user_Id FROM users where active = 0 and twitterid != 0 and user_Id=" . $userId;
                $query = $this->db->query($sql);
                $twitterUserdata = $query->result();
                //print_r($twitterUserdata);
                foreach ($twitterUserdata as $user) {
                    $user->user_Id;
                }
                $lastid = $user->user_Id;
                //die;
                //$lastid= base64_decode($_SESSION['lastinsrtId']);
            } else {
                $lastid = $this->session->userdata('fb_lastid');
            }
            $date_of_birth = $year . '-' . $month . '-' . $date;
            $arr_user['user_Id'] = $lastid;
            if ($fb == 0) {
                $arr_user['email'] = $email;
            }
            $arr_user['active'] = 1;
            $arr_user['password'] = $password;
            $arr_user['date_of_birth'] = $date_of_birth;
            $arr_user['gender'] = $gender;
            $sucess = $this->user_model->save($arr_user);
            //echo $this->db->last_query();
            if ($sucess == true) {
                $userdata = $this->user_model->getOneUser($lastid);
                $this->session->set_userdata('logged_in', $userdata);

                // Send Email to user
                //$config['mailtype'] = 'html';    //// ENABLE HTML

                $this->emailConfig();   //Get configuration of email
                //// GET EMAIL FROM DATABASE
                $email_template = $this->email_model->getoneemail('facebook_signup');

                //// MESSAGE OF EMAIL
                $messages = $email_template->message;

                $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
                $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
                $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

                //// replace strings from message
                $messages = str_replace('{Username}', ucfirst($userdata->username), $messages);
                $messages = str_replace('{password}', $generatePass, $messages);

                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                $messages = str_replace('{Google_Image}', $googleplay, $messages);

                // echo $messages;
                //// Email to user
                $this->email->from('hello@marketmyapp.co', 'Hurree');
                $this->email->to($email);
                $this->email->subject($email_template->subject);
                $this->email->message($messages);
                $this->email->send();    ////  EMAIL SEND
                //echo $this->email->print_debugger();
                echo true;
            }
            unset($_SESSION['lastinsrtId']);
        } else {

            $fbsession = '';
            $data['fb'] = 0;
            $fbsession = $this->session->userdata('fb_lastid');

            if (!empty($fbsession)) {
                $data['fb'] = 1;
            }

            $this->load->view('confirm_email', $data);
        }
    }

    function error() {

        $agent['emailPopop'] = 0;

        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == false && $header['login']->accesslevel == '' && $header['login']->active == 0) {

            $data['subheader'] = $this->subheader('front');
            $data['showTopSignIn'] = 1;

            $detect = new Mobile_Detect();
            if ($detect->isAndroidOS()) {

                $agent['agent'] = 'android';
            } else {
                $agent['agent'] = 'browser';
            }
            if ($detect->isiOS()) {
                $agent['agent'] = 'iOS';
            }
            $agent['message'] = "The page you requested was not found !";
            /* echo '<pre>'; print_r($data); die; */
            //$this->load->view('header', $data);
            $this->load->view('error', $agent);
            //$this->load->view('footer');
        } else {
            if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
                redirect('timeline');
            } else {
                $this->session->unset_userdata('user_logged_in');
                /* redirect('home/features'); */
                redirect('index');
            }
        }
    }

    /*     * **************************************************************************************************************************
      # Function Action Get Geo Code
     * ************************************************************************************************************************** */

    public function GetGeoCode($newAddress) {
        $address = str_replace(" ", "+", $newAddress);
        $geocode = @file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $address);
        $output = json_decode($geocode);
        if (isset($output) && $output != "") {

            if ($output->status == "OK") {
                $lat = $output->results[0]->geometry->location->lat;
                $long = $output->results[0]->geometry->location->lng;
                $a = array('latitude', 'longitude');
                $b = array($lat, $long);
                $location = array_combine($a, $b);
                return $location;
            } else {
                $lat = "";
                $long = "";
                $a = array('latitude', 'longitude');
                $b = array($lat, $long);
                $location = array_combine($a, $b);
                return $location;
            }
        } else {
            $lat = "";
            $long = "";
            $a = array('latitude', 'longitude');
            $b = array($lat, $long);
            $location = array_combine($a, $b);
            return $location;
        }
    }

    function beacon_control() {

        $login = $this->administrator_model->front_login_session(); //Get login user id
        $userid = $login->user_id;

        $data['beaconCheck'] = $this->beacon_model->check_beacon($userid); //Check beacon is exist for login user

        $beaconCheck = $this->beacon_model->check_beacon($userid);
        $beaconOffer = array();
        if (count($beaconCheck) > 0) {
            foreach ($beaconCheck as $beacon) {

                $results = $this->beacon_model->getOffer($beacon->beaconId);
                $beaconOffer[] = $results;
            }
        }
        $data['beaconOffer'] = $beaconOffer;

        $userdetails = $this->games_model->userCoins($userid);
        $data['totalCoins'] = $userdetails->coins;

        $data['message'] = '';
        /* Check user on Hold  */
        $where['userid'] = $userid;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);

        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }

        /* End Check user on Hold  */

        $this->load->view('beacon_control', $data);
    }

    function getCoins() {
        $coins = $_POST['coins'];
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $arr_coins = array();
        $userdetails = $this->games_model->userCoins($userid);

        if ($coins <= $userdetails->coins) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    function cancelBeaconOffer() {

        $beaconOfferId = $_POST['beaconOfferId'];

        $update['beconOfferId'] = $beaconOfferId;
        $update['isActive'] = 0;
        $this->beacon_model->saveBeaconOffers($update);
        echo 'Success';
    }

    function ageStats() {

        $businessUserId = $this->uri->segment('3');
        $data['users'] = $this->user_model->ageStats($businessUserId);

        /* Check user is on hold  */
        $data['message'] = '';
        $where['userid'] = $businessUserId;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);


        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }
        /* End Check user is on hold */

        $this->load->view('ageStats', $data);
    }

    function genderStats() {

        $businessUserId = $this->uri->segment('3');
        $data['users'] = $this->user_model->ageStats($businessUserId);

        /* Check user is on hold  */
        $data['message'] = '';
        $where['userid'] = $businessUserId;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);


        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }
        /* End Check user is on hold */

        $this->load->view('genderStats', $data);
    }

    function scanQRcodeUsersRewarded() {

        $businessUserId = $this->uri->segment('3');
        $data['rewardedUsers'] = $this->user_model->rewardedUsers($businessUserId);

        /* Check user is on hold  */
        $data['message'] = '';
        $where['userid'] = $businessUserId;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);


        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }
        /* End Check user is on hold */

        $this->load->view('coins_rewarded', $data);
    }

    function checkInUsers() {

        $businessUserId = $this->uri->segment('3');
        $data['checkInUsers'] = $this->user_model->checkInUsers($businessUserId);

        /* Check user is on hold  */
        $data['message'] = '';
        $where['userid'] = $businessUserId;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);


        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }
        /* End Check user is on hold */

        $this->load->view('checkInsReviews', $data);
    }

    function customerProfiling() {

        $businessUserId = $this->uri->segment('3');

        /* Check user is on hold */
        $where['userid'] = $businessUserId;
        $loginStatus = $this->user_model->getLoginStatus('*', $where);
        $data['message'] = '';
        if (count($loginStatus) > 0) {
            $cancel = $loginStatus->cancel;
            if ($cancel === "1") {
                $data['message'] = "Your account is inactive, please email <a style='text-decoration:none;' href='mailto:Business@Hurree.co'>Business@Hurree.co</a> to reactivate your business account";
            }
        }
        /* End Check user is on hold */

        //$data['onetimeScanUsers'] = $this->user_model->rewardedUsers($businessUserId);
        $allUsers = $this->user_model->scannedUsers($businessUserId);  //3
        //$newCustomers = $this->user_model->newCustomers($businessUserId);

        $singleScanUsers = array();
        $multipleScanUsers = array();
        $data['singleScanUsers'] = array();
        $data['multipleScanUsers'] = array();

        $i = 0;
        $j = 0;
        foreach ($allUsers as $user) {
            $userid = $user->user_Id;
            $userCoins = array(
                'userId' => $user->user_Id,
                'businessId' => $businessUserId
            );
            $results = $this->user_model->countScannedUsers($userCoins);
            /* echo '<pre>';
              echo count($results); */
            if (count($results) > 1) {
                if ($i < 20) {
                    $multipleScanUsers[$i] = $user;
                    $i++;
                }
            } else {
                if ($j < 20) {
                    $singleScanUsers[$j] = $user;
                    $j++;
                }
            }

            if ($i == 20 && $j == 20) {
                break;
            }
            //print_r($results);
        }
        $data['singleScanUsers'] = $singleScanUsers;
        $data['multipleScanUsers'] = $multipleScanUsers;

        $this->load->view('customerProfling', $data);
    }

    function useful_tips() {

        $this->load->view('useful_tips');
    }

    /* function emailConfig() {

      $this->load->library('email');   //// LOAD LIBRARY

      $config['protocol'] = 'smtp';
      $config['smtp_host'] = 'auth.smtp.1and1.co.uk';
      $config['smtp_port'] = '587';
      $config['smtp_timeout'] = '7';
      $config['smtp_user'] = 'support@hurree.co';
      $config['smtp_pass'] = 'aaron8164';
      $config['charset'] = 'utf-8';
      $config['newline'] = "\r\n";
      $config['mailtype'] = 'html'; // or html

      $this->email->initialize($config);
      } */

    function emailConfig() {

        $this->load->library('email');   //// LOAD LIBRARY

        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'smtp.sendgrid.net'; //auth.smtp.1and1.co.uk
        $config['smtp_port'] = 587;
        $config['smtp_user'] = 'aaronhurree'; //support@hurree.co.uk
        $config['smtp_pass'] = 'aaron8164';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html

        $this->email->initialize($config);
    }

    function updateTopUp() {

        $userid = $_POST['userid'];
        $active = $_POST['active'];

        $update['user_Id'] = $userid;
        $update['autoTopUp'] = $active;

        $this->user_model->updateAutoTopUp($update);
    }

    function boostPost() {

        $data['statusid'] = $_GET['statusId'];
        $this->load->view('boost_post', $data);
    }

    function createTargetOffer() {

        $minAge = $_POST['minAge'];
        $maxAge = $_POST['maxAge'];
        $coin_buget = $_POST['coin_buget'];
        $duration = $_POST['duration'];
        $gender = $_POST['gender'];
        $statusid = $_POST['statusid'];

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $created_date = date("Y-m-d H:i:s");

        $data = array(
            'user_id' => $userid,
            'status_id' => $statusid,
            'min_age' => $minAge,
            'max_age' => $maxAge,
            'gender' => $gender,
            'coin_buget' => $coin_buget,
            'available_coins' => $coin_buget,
            'duration' => $duration,
            'created_date' => $created_date,
            'status' => 1
        );

        $this->targetoffer_model->insert($data);

        $status_boosted_data = array(
            'boosted' => 1
        );
        $this->db->where('status_id', $statusid);
        $this->db->update('user_status', $status_boosted_data);

        echo 1;
    }

    function cancelTargetOffer() {
        $status_id = $_POST['statusId'];
        $data = array(
            'status' => 0,
        );

        $this->db->where('status_id', $status_id);
        $this->db->where('status', 1);
        $this->db->update('target_offer_table', $data);

        $status_boosted_data = array(
            'boosted' => 0
        );
        $this->db->where('status_id', $status_id);
        $this->db->update('user_status', $status_boosted_data);

        echo 1;
    }

//Created by Hassan Ali
    function createChallenge() {

        $arr_user['username'] = $_POST['username'];
        $arr_user['active'] = 1;
        /* print_r($arr_user); */
        $oneuser = $this->user_model->getOneUserDetails($arr_user, "*");
        //print_r($oneuser->user_Id);

        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        $challenge_to = $oneuser->user_Id;
        $gameid = $this->input->post('game');
        $coins = $this->input->post('coins');
        $challenge = array(
            'challenge_from' => $userid,
            'challenge_to' => $challenge_to,
            'game_id' => $gameid,
            'challenge_coins' => $coins,
            'approval' => 0,
            'winner' => 0,
            'createdDate' => date('YmdHis'),
            'modifiedDate' => date('YmdHis')
        );
        //print_r($challenge);
        $challangeid = $this->challenge_model->add($challenge);
        //$this->session->set_flashdata('create_challenge', 'You sent a challenge!');

        /* Send Notification to Challeged Person  */

        $arr_notice['notification_id'] = '';
        $arr_notice['actionFrom'] = $userid;
        $arr_notice['actionTo'] = $challenge_to;
        $arr_notice['action'] = 'CC';
        $arr_notice['actionString'] = ' Challenged You!';
        $arr_notice['message'] = '';
        $arr_notice['statusid'] = '';
        $arr_notice['challangeid'] = $challangeid;
        $arr_notice['active'] = '1';
        $arr_notice['createdDate'] = date('YmdHis');
        $notice_id = $this->notification_model->savenotification($arr_notice);

        echo 'success';
    }

    function getGameSubscriptiondetails() {
        $challenged_coins = $_POST['coins'];
        $gameid = $_POST['gameid'];
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        //echo $userid;

        $arr_coins = array();
        $userdetails = $this->games_model->userCoins($userid);

        $usercoins = $userdetails->coins;
        if ($usercoins >= $challenged_coins) {
            // True
            $arr_coins[] = 'true';
            $arr_coins[] = '';
        } else {
            //False
            $arr_coins[] = 'false';
            $arr_coins[] = 'You Have not Enough Coins';
        }

        //$arr_game['game_id']=$gameid;
        $arr_game['user_id'] = $userid;
        $arr_game['active'] = 1;

        $subsciptionGameId = array();
        $subscription = $this->games_model->getUserOneGameSubscription($arr_game, 1);
        if (count($subscription) > 0) {
            foreach ($subscription as $val) {
                $subsciptionGameId[] = $val->game_id;
            }

            if (in_array($gameid, $subsciptionGameId) || in_array(5, $subsciptionGameId)) {
                $arr_subs[] = 'true';
                $arr_subs[] = "";
            } else {
                $arr_subs[] = 'false';
                $arr_subs[] = "You are Not Subscribe for this game";
            }
        } else {
            $arr_subs[] = 'false';
            $arr_subs[] = "You are Not Subscribe for this game";
        }

        $arr_final['coins'] = $arr_coins;
        $arr_final['game'] = $arr_subs;

        echo json_encode($arr_final);
    }

    function loadOneStatus() {

        $statusId = $_POST['id'];


        $data['sigleRecord'] = 'reply';
        $arr_status['status_id'] = $statusId;

        $data['time'] = $this->user_model->getStatusDetails($arr_status);
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();

        $login = $this->administrator_model->front_login_session();
        $data['loginuser'] = $login->user_id;
        $data['page'] = "timeline";
        $this->load->view('addmoretimeline', $data);
    }

    function stats() {

        $header['login'] = $this->administrator_model->front_login_session();
        if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
            $usertype = $header['login']->usertype;
            if ($usertype == 2 || $usertype == 5) {

                $this->load->helper('convertlink');

                $userid = $header['login']->user_id;
                $data['loginuser'] = $header['login']->user_id;
                $data['user'] = $this->user_model->getOneUser($userid);
                $data['viewPage'] = 'stats';

                /* Customer Profiling  */
                $businessUserId = $userid;
                $allUsers = $this->user_model->scannedUsers($businessUserId);
                //echo '<pre>';
                //print_r($allUsers); exit;
                $singleScanUsers = array();
                $multipleScanUsers = array();
                $data['singleScanUsers'] = array();
                $data['multipleScanUsers'] = array();

                $i = 0;
                $j = 0;
                foreach ($allUsers as $user) {
                    $userid = $user->user_Id;
                    $userCoins = array(
                        'userId' => $user->user_Id,
                        'businessId' => $businessUserId
                    );
                    $results = $this->user_model->countScannedUsers($userCoins);

                    if (count($results) > 1) {
                        //$multipleScanUsers[$i] = $user;
                        if ($i < 20) {
                            $multipleScanUsers[$i] = $user;
                            $i++;
                        }
                    } else {
                        if ($j < 20) {
                            $singleScanUsers[$j] = $user;
                            $j++;
                        }
                    }

                    if ($i == 20 && $j == 20) {
                        break;
                    }
                }
                $data['singleScanUsers'] = $singleScanUsers;
                $data['multipleScanUsers'] = $multipleScanUsers;
                //echo '<pre>'; print_r($singleScanUsers);  print_r($multipleScanUsers); exit;
                /* End Customer Profiling  */

                /* Gender Stats and Age Stats  */
                $data['genderUsers'] = $this->user_model->ageStats($businessUserId);
                /* End Gender Stats and Age Stats  */

                /* Check Ins & Reviews  */
                $data['checkInUsers'] = $this->user_model->checkInUsers($businessUserId);
                /* End Check Ins & Reviews  */
                $this->load->view('inner_header', $data);
                $this->load->view('stats', $data);
                //$this->load->view('custom_chart', $data);
                $this->load->view('inner_footer', $data);
            } else {
                redirect("timeline");
            }
        } else {
            $this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
            redirect("home");
        }
    }

    function notificationfull() {
        $baseurl = base_url();
        $data['viewPage'] = 'timeline';
        $login = $this->administrator_model->front_login_session();
        //$data['user'] = $login;

        $userid = $login->user_id;
        $data['user'] = $this->user_model->getOneUser($userid);
        $data['gamelist'] = $this->games_model->gameslist($userid);
        $toalChallenges = $this->challenge_model->getChallenge($userid);

        foreach ($toalChallenges as $challenge) {
            $challengefrom = $challenge->challengefrom;
            $username = $this->user_model->getusername($challengefrom);
            $challenge->challengefrom = $username->username;
        }
        $data['challenge_recieve'] = $toalChallenges;
        //Display total coins of user
        $data['totalcoins'] = $this->score_model->getUserCoins($userid); // Get total user's coins

        $data['challenge_username'] = '';
        $data['game_subscription'] = '';
        $data['coins_validation'] = '';
        $data['challengegameid'] = '';

        //Get Campaigns list
        if ($login->usertype == 2 || $login->usertype == 5) {
            $data['campaigns'] = $this->campaign_model->getAllCampaigns($login->user_id);
        }

        /* echo '<pre>'; print_r($login); die; */
        $select = "*";
        $arr_notification['actionTo'] = $login->user_id;
        $arr_notification['notification.active'] = 1;
        $arr_notification['notification.isDelete'] = 0;
        $arr_notification['actionFrom !='] = $login->user_id;
        //$arr_notification['notification.is_new']=1;
        $records = $this->notification_model->getnotification($arr_notification, $row = 1, $select = '*', '', '', $totalrecords = 1);   //// Get Total No of Records in Database

        $config['base_url'] = base_url() . 'index.php/home/notification/';
        $config['total_rows'] = $records;
        $config['per_page'] = $records;
        $config['uri_segment'] = 3;
        $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['page'] = $page;
        $limit = $config['per_page'];
        $order_by['order_by'] = 'distance';
        $order_by['sequence'] = 'DESC';
        $select = ' CONCAT("@", UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username , firstname, lastname, image, user_Id, image , actionString , action,  notification.createdDate as postedDate, notification.*, ( case when (usertype = 1 or usertype = 4) THEN  CONCAT_WS( " ", users.firstname, users.lastname ) ELSE  users.businessName END ) as name, challenge.game_id';
        $data['notification'] = $this->notification_model->getnotification($arr_notification, $row = 1, $select, $page, $limit);
        $arr_update['is_new'] = 0;
        $notWhere['where'] = 'actionTo';
        $notWhere['val'] = $login->user_id;
        $this->notification_model->updateNotification($arr_update, $notWhere);
        $qrcode = $this->qrcode_model->getQRcodeDetails($login->user_id);
        $data['previous_qrcode'] = count($qrcode);
        $data['qr_code'] = $qrcode;

        $this->load->view('inner_header', $data);
        $this->load->view('notificationfull', $data);
        $this->load->view('inner_footer');
    }

    public function firendsimport($id) {

        $query = $this->db->query("SELECT * FROM friends_import where id=" . $id);
        $friendData = $query->result();
        $emails = explode(',', $friendData[0]->email);
        $baseurl = base_url();
        $login = $this->administrator_model->front_login_session();
        $data['userLogin'] = $login;
        $data['contacts'] = $emails;
        $this->load->view('step_3_contacts', $data);
    }

    public function chatfull() {
        $baseurl = base_url();
        $data['viewPage'] = 'timeline';
        $login = $this->administrator_model->front_login_session();
        //$data['user'] = $login;

        $data['gamelist'] = $this->games_model->gameslist($login->user_id);
        $toalChallenges = $this->challenge_model->getChallenge($login->user_id);
        $data['user'] = $this->user_model->getOneUser($login->user_id);
        foreach ($toalChallenges as $challenge) {
            $challengefrom = $challenge->challengefrom;
            $username = $this->user_model->getusername($challengefrom);
            $challenge->challengefrom = $username->username;
        }
        $data['challenge_recieve'] = $toalChallenges;
        //Display total coins of user
        $data['totalcoins'] = $this->score_model->getUserCoins($login->user_id); // Get total user's coins
        //Get Campaigns list
        if ($login->usertype == 2 || $login->usertype == 5) {
            $data['campaigns'] = $this->campaign_model->getAllCampaigns($login->user_id);
        }

        $data['challenge_username'] = '';
        $data['game_subscription'] = '';
        $data['coins_validation'] = '';
        $data['challengegameid'] = '';
        $qrcode = $this->qrcode_model->getQRcodeDetails($login->user_id);
        $data['previous_qrcode'] = count($qrcode);
        $data['qr_code'] = $qrcode;
        $data['messageDetails'] = $this->message_model->getreceiveMessagesUser($login->user_id);

        $this->load->view('inner_header', $data);
        $this->load->view('chatfull', $data);
        $this->load->view('inner_footer');
    }

    public function sendCoins() {
        $no_of_coins = $_POST['no_of_coins'];
        $sendToUser = $_POST['username'];
        $login = $this->administrator_model->front_login_session();

        $arr_seconUser['username'] = $sendToUser;
        $arr_seconUser['active'] = 1;
        $secondUserDetails = $this->user_model->getOneUserDetails($arr_seconUser, '*');

        if ($secondUserDetails->user_Id != $login->user_id) {
            if ($no_of_coins >= 1) {
                $userTotalCoins = $this->score_model->getUserCoins($login->user_id);
                if ($userTotalCoins->coins >= $no_of_coins) {
                    $arr_coins['transferCoinId'] = '';
                    $arr_coins['userFrom'] = $login->user_id;
                    $arr_coins['userTo'] = $secondUserDetails->user_Id;
                    $arr_coins['coins'] = $no_of_coins;
                    $arr_coins['isActive'] = 1;
                    $arr_coins['createdDate'] = date('YmdHis');
                    $this->user_model->saveUserTransferCoins($arr_coins);

                    $updateCoins = $userTotalCoins->coins - $no_of_coins;

                    $update = array(
                        'userid' => $login->user_id,
                        'coins' => $updateCoins
                    );
                    $this->score_model->update($update);
                    echo '1';
                    exit;
                } else {
                    echo '2';
                    exit; // no of coins more than user's total coins
                }
            } else {
                echo '3';
                exit; // when no of coins less than 1
            }
        } else {
            echo '4';
            exit; // when user send coins to own profile
        }
    }

    public function amazonDemo() {
        $this->load->view('amazonSns.php');
    }

    function report_status($statusId) {

        $login = $this->administrator_model->front_login_session();
        $userId = $login->user_id;

        $data['statusId'] = $statusId;
        $data['userid'] = $userId;
        $userReport = $this->user_model->getUserReport($data);

        $data['userRepoted'] = count($userReport);
        $data['reportType'] = $this->user_model->getReportType();

        $this->load->view('report_status', $data);
    }

    function reportMessage() {

        $statusId = $this->input->post('statusId');
        $report = $this->input->post('report');
        $this->load->helper('cookie');
        $status_id = array(
            'name' => 'report_statusid',
            'value' => $statusId,
            'expire' => time() + 3600
        );
        $report = array(
            'name' => 'report_type',
            'value' => $report,
            'expire' => time() + 3600
        );
        $this->input->set_cookie($status_id);
        $this->input->set_cookie($report);

        //print_r($data); die;
        $this->load->view('report_message');
    }

    function sendReport() {

        $login = $this->administrator_model->front_login_session();
        $userId = $login->user_id;
        $statusId = $this->input->post('statusId');
        $reportOption = $this->input->post('reportOption');
        $message = $this->input->post('message');

        $where['status_id'] = $statusId;
        $status = $this->status_model->getOneStatus($where, "*");

        $date = date('Y-m-d H:i:s');
        $insert = array(
            'id' => '',
            'userid' => $userId,
            'reportUserId' => $status->userid,
            'statusId' => $statusId,
            'reportType' => $reportOption,
            'message' => $message,
            'isDelete' => '0',
            'createdDate' => $date
        );

        $insertId = $this->status_model->insert_report($insert);
        delete_cookie("report_statusid");
        delete_cookie("report_type");
        echo 1;
    }

    function deleteCookie() {
        delete_cookie("report_statusid");
        delete_cookie("report_type");
        echo 1;
    }

    function location() {
        $baseurl = base_url();
        $data['viewPage'] = 'timeline';
        $login = $this->administrator_model->front_login_session();
        $data['user'] = $login;

        $userid = $login->user_id;
        $data['gamelist'] = $this->games_model->gameslist($userid);
        $toalChallenges = $this->challenge_model->getChallenge($userid);

        foreach ($toalChallenges as $challenge) {
            $challengefrom = $challenge->challengefrom;
            $username = $this->user_model->getusername($challengefrom);
            $challenge->challengefrom = $username->username;
        }
        $data['challenge_recieve'] = $toalChallenges;
        //Display total coins of user
        $data['totalcoins'] = $this->score_model->getUserCoins($userid); // Get total user's coins

        $data['challenge_username'] = '';
        $data['game_subscription'] = '';
        $data['coins_validation'] = '';
        $data['challengegameid'] = '';
        //mysql query to find business user and their locaiton
        $query = $this->db->query('SELECT u.user_Id,u.username, u.firstname, u.lastname,u.usertype, u.businessName,bb.branch_id, bb.latitude, bb.longitude FROM  users u LEFT JOIN business_branch bb ON u.user_Id=bb.userid WHERE u.usertype IN (2, 5) AND bb.latitude IS NOT NULL AND bb.longitude IS NOT NULL AND bb.latitude !=0 AND bb.longitude !=0');
        $businessUserData = $query->result();
        $data['businessUserData'] = $businessUserData;
        $this->load->view('inner_header', $data);
        $this->load->view('location', $data);
        $this->load->view('inner_footer');
    }

    public function getstatus() {
        $reply_status_id = $_POST['reply_status_id'];
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $data['targetOffer'] = $this->targetoffer_model->getAllTargetOffers();
        $data['loginusername'] = $login->username;
        $data['loggedinUserImage'] = $login->image;
        $data['timeline'] = $this->user_model->getFollowerStatus($login->user_id, '', '', '', $reply_status_id);
        $data['enteredUsername'] = '$enteredNewUsername';
        $data['sigleRecord'] = '1';
        $data['loginuser'] = $login->user_id;
        $data['loggedinUsertype'] = $login->usertype;
        $data['user'] = $this->user_model->getOneUser($login->user_id);
        $data['pagination'] = 1;


        $this->load->view('addmoretimeline', $data);
    }

    public function getlaststatusid() {
        $id = $this->status_model->getlastid();
        echo $id;
    }

    function clearnotificationcount($userid) {
        $resutl = $this->notification_model->clearNotificationCount($userid);

        if ($resutl > 0) {
            $baseurl = base_url();

            $login = $this->administrator_model->front_login_session();
            $data['user'] = $login;
            $select = "*";
            $arr_notification['actionTo'] = $login->user_id;
            $arr_notification['notification.active'] = 1;
            $arr_notification['notification.isDelete'] = 0;
            $arr_notification['actionFrom !='] = $login->user_id;
            //$arr_notification['notification.is_new']=1;
            $records = $this->notification_model->getnotification($arr_notification, $row = 1, $select = '*', '', '', $totalrecords = 1);   //// Get Total No of Records in Database

            $select = ' CONCAT("@", UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username , firstname, lastname, image, user_Id, image , actionString , action,  notification.createdDate as postedDate, notification.*, ( case when (usertype = 1 or usertype = 4) THEN  CONCAT_WS( " ", users.firstname, users.lastname ) ELSE  users.businessName END ) as name, challenge.game_id';
            $data['notification'] = $this->notification_model->getnotification($arr_notification, $row = 1, $select, NULL, $records);


            $var = '';
            if (count($data['notification']) > 0) {
                $img = "this.onerror=null;this.src='https://hurree.co/upload/profile/medium/user.png';";
                foreach ($data['notification'] as $notice) {
                    $var .='<li> <div class="propic"><img src="' . base_url() . 'upload/profile/thumbnail/' . $notice->image . '" alt="" onerror="' . $img . '" /></div>
                    <div class="info">
                        <p>
                        	<strong>' . $notice->name . '<a href="' . base_url() . 'business/' . ltrim($notice->username, "@") . '">' . $notice->username . '</a></strong>
                        	<small>' . $notice->actionString . '</small>
                        </p>
                        <div class="time">' . $notice->createdDate . '</div>
                    </div>
                </li>';
                }
            }
            echo $var;
            die;
        }

        echo $resutl;
        die;
    }

    public function getSearchedStatus() {
        $searchContent = $_POST['search'];
        $searchResult = $this->user_model->search_userhash($searchContent);
        print_r($searchResult);
        exit;
    }

    public function searchResult() {
        $pagination = '&start=0&rows=6';
        $status = $this->uri->segment(3);     // user for status or hashtag

        $to = solr_url . "hurree/select?q=status%3A*" . $status . "*&wt=json&indent=true" . $pagination;
        //echo $to; exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $str = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($str);
        $header['login'] = $this->administrator_model->front_login_session();
        $id = $header['login']->user_id;
        if ($data == '') {
            $statusList = array();
            $i = 0;
        } else {
            $solrTotalRecords = $data->response->numFound;
            $statusList = $data->response->docs;

            $i = 0;
            foreach ($statusList as &$status) {
                $arr_status['parentStatusid'] = $status->status_id;
                $status->createdDate = $status->statusCreatedDate;
                $status->message = '';
                $status->image = $status->userImage;
                $status->bio = isset($status->bio) ? $status->bio : '';
                $status->location = isset($status->location) ? $status->location : '';
                $status->firstname = isset($status->firstname) ? $status->firstname : '';
                $status->lastname = isset($status->lastname) ? $status->lastname : '';
                $status->header_image = $status->header_image;
                $totallike = $this->status_model->gettotallikestatus($status->status_id);
                $status->likeCount = count($totallike);
                $like['statusId'] = $status->status_id;
                $like['userId'] = $id;
                $like['active'] = 1;
                $likestatus = $this->user_model->getlikestatus($like);

                if (count($likestatus) > 0) {
                    $status->like = "true";
                } else {
                    $status->like = "false";
                }


                $totalrply_status = $this->status_model->gettotalrply($status->status_id);
                $status->rplyCount = count($totalrply_status);

                $status->userid = $status->user_Id;
                $status->likedUsers = '';

                $replys = $this->status_model->getStatusDetails($arr_status, $row = 1, '', '', 1, $userid = NULL, $id);
                $status->reply = $replys;
                $status->usermentionednew = '';
                $i++;
            }
        }

        $this->load->helper('convertlink');
        $this->load->helper('follow');
        $this->load->helper('like');

        $result['user'] = $this->user_model->getOneUser($id); // confusion


        $result['peoples'] = $this->user_model->getpeopletofollow($id, '1');
        $result['loggedin'] = $id;
        $result['loggedinUsertype'] = $header['login']->usertype;
        $result['loggedinUserImage'] = $header['login']->image;
        $result['loginusername'] = $header['login']->username;
        $result['loginuser'] = $id;
        $result['userid'] = $id;
        $result['viewPage'] = 'timeline';
        $result['businesses'] = $this->user_model->getpeopletofollow($id, '2');
        $result['timeline'] = $statusList;
        $result['statuscount'] = 0;
        $result['noofstatus'] = 0;
        $result['records'] = 0;
        $result['solr_records'] = $i;
        $result['solrTotalRecords'] = 1;


        $this->load->view('inner_header', $result);
        $this->load->view('searchPage', $result);
    }

    public function searchHashTag() {
        $status = $this->uri->segment(3);     // user for status or hashtag
        $pagination = '&start=0&rows=6';
        $to = solr_url . "hurree/select?q=status%3A*%23" . $status . "*&wt=json&indent=true" . $pagination;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $str = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($str);
        $header['login'] = $this->administrator_model->front_login_session();
        $id = $header['login']->user_id;
        if ($data == '') {
            $statusList = array();
            $i = 0;
        } else {
            $statusList = $data->response->docs;
            $solrTotalRecords = $data->response->numFound;

            $i = 0;
            foreach ($statusList as &$status) {
                $arr_status['parentStatusid'] = $status->status_id;
                $status->createdDate = $status->statusCreatedDate;
                $status->message = '';

                $status->image = $status->userImage;
                $status->firstname = isset($status->firstname) ? $status->firstname : '';
                $status->lastname = isset($status->lastname) ? $status->lastname : '';
                $status->bio = $status->bio;
                $status->location = $status->location;
                $status->header_image = $status->header_image;
                $totallike = $this->status_model->gettotallikestatus($status->status_id);
                $status->likeCount = count($totallike);
                $like['statusId'] = $status->status_id;
                $like['userId'] = $id;
                $like['active'] = 1;
                $likestatus = $this->user_model->getlikestatus($like);

                if (count($likestatus) > 0) {
                    $status->like = "true";
                } else {
                    $status->like = "false";
                }

                $totalrply_status = $this->status_model->gettotalrply($status->status_id);
                $status->rplyCount = count($totalrply_status);
                $status->userid = $status->user_Id;
                $status->likedUsers = '';
                $status->rplyCount = 0;
                $status->usermentionednew = '';
                $replys = $this->status_model->getStatusDetails($arr_status, $row = 1, '', '', 1, $userid = NULL, $id);
                $status->reply = $replys;
                $i++;
            }
        }
        $this->load->helper('convertlink');
        $this->load->helper('follow');
        $this->load->helper('like');

        $result['user'] = $this->user_model->getOneUser($id); // confusion
        $result['peoples'] = $this->user_model->getpeopletofollow($id, '1');
        $result['loggedin'] = $id;
        $result['loggedinUsertype'] = $header['login']->usertype;
        $result['loggedinUserImage'] = $header['login']->image;
        $result['loginusername'] = $header['login']->username;
        $result['loginuser'] = $id;
        $result['userid'] = $id;
        $result['viewPage'] = 'timeline';
        $result['businesses'] = $this->user_model->getpeopletofollow($id, '2');
        $result['timeline'] = $statusList;
        $result['statuscount'] = 0;
        $result['noofstatus'] = 0;
        $result['records'] = 0;
        $result['solr_records'] = $i;
        $result['solrTotalRecords'] = 1;
        $this->load->view('inner_header', $result);
        $this->load->view('searchPage', $result);
    }

    function solrpagination() {
        $this->load->view('convert_to_link');
        $startFrom = $_POST['startFrom'];
        $content = $_POST['content'];
        $pagination = '&start=' . $startFrom . '&row=6';

        if ($_POST['urisegment'] === 'searchHashTag') {
            $to = solr_url . "hurree/select?q=status%3A*%23" . $content . "*&wt=json&indent=true" . $pagination;
        } else {
            $to = solr_url . "hurree/select?q=status%3A*" . $content . "*&wt=json&indent=true" . $pagination;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $str = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($str);
        $statusList = $data->response->docs;
        $solrTotalRecords = $data->response->numFound;
        $header['login'] = $this->administrator_model->front_login_session();
        $id = $header['login']->user_id;
        $i = 0;
        foreach ($statusList as &$status) {
            $arr_status['parentStatusid'] = $status->status_id;
            $status->createdDate = $status->statusCreatedDate;
            $status->message = '';

            $status->image = $status->userImage;
            $status->firstname = isset($status->firstname) ? $status->firstname : '';
            $status->lastname = isset($status->lastname) ? $status->lastname : '';
            $totallike = $this->status_model->gettotallikestatus($status->status_id);
            $status->likeCount = count($totallike);
            $status->like = 'true';
            $totalrply_status = $this->status_model->gettotalrply($status->status_id);
            $status->rplyCount = count($totalrply_status);
            $like['statusId'] = $status->status_id;
            $like['userId'] = $id;
            $like['active'] = 1;
            $likestatus = $this->user_model->getlikestatus($like);

            if (count($likestatus) > 0) {
                $status->like = "true";
            } else {
                $status->like = "false";
            }
            $status->bio = '';
            $status->location = '';
            $status->likeCount = 0;
            $status->header_image = '';
            $status->userid = $status->user_Id;
            $status->likedUsers = '';
            $status->rplyCount = 0;
            $status->usermentionednew = '';
            $replys = $this->status_model->getStatusDetails($arr_status, $row = 1, '', '', 1, $userid = NULL, $id);
            $status->reply = $replys;
            $i++;
        }

        $result['user'] = $this->user_model->getOneUser($id); // confusion
        $result['loggedin'] = $id;
        $result['loggedinUsertype'] = $header['login']->usertype;
        $result['loggedinUserImage'] = $header['login']->image;
        $result['loginusername'] = $header['login']->username;
        $result['loginuser'] = $id;
        $result['userid'] = $id;
        $result['viewPage'] = 'timeline';
        $result['statuscount'] = 0;
        $result['noofstatus'] = 0;
        $result['records'] = 0;
        $result['solr_records'] = $i; // not showing see more activities link on status helper view
        $result['solrTotalRecords'] = 2;

        $result['solrTotalRecords'] = $solrTotalRecords;
        $result['viewPage'] = 'timeline';
        $result['timeline'] = $statusList;
        $this->load->view('statusHelper', $result);
    }

    public function populatesearch() {
        $content = $_POST['text'];

        if (preg_match('@((https?://)?([-\\w]+\\.[-\\w\\.]+)+\\w(:\\d+)?(/([-\\w/_\\.]*(\\?\\S+)?)?)*)@', $content)) {
            echo '1';
            exit;
        }


        if ($content[0] === '@') {
            $content = str_replace("@", '', $content);
            $to = solr_url . "users/select?q=username:" . $content . "*&wt=json&indent=true";
        } else if ($content[0] === '#') {
            $content = str_replace("#", '', $content);
            $to = solr_url . "hashTag/select?q=hashTag%3A*%23*" . $content . "*&wt=json&indent=true";
        } else {
            echo '2';
            exit; // return 2 when firstchar has no @ or #
        }
        //echo $to ; exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $to);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $str = curl_exec($ch);
        curl_close($ch);
        echo $str;
        //$data = json_decode($str);
        //print_r( $data);
    }

    function newMgsCount() {
        $login = $this->administrator_model->front_login_session(); //print_r($login); exit;
        $result = $this->message_model->newMgsCount($login->user_id);
        echo $result;
        die;
    }

    function updateNotificationRow() {
        $this->load->view('time');
        $login = $this->administrator_model->front_login_session();
        $messages = $this->message_model->getreceiveMessagesUser($login->user_id);
        $count = $this->message_model->newMgsCount($login->user_id);
        $var = '';
        if (count($messages) > 0) {
            $img = "this.onerror=null;this.src='https://hurree.co/upload/profile/medium/user.png';";
            foreach ($messages as $message) {
                //  print_r($message);
                $class = ($message->is_new == 0) ? $class = "unread" : $class = "";
                $var .='<li class="' . $class . '">
                    <a onclick="newMgsCount();" class="modalPopup" data-class="findFriends" data-title="View Message" href="' . base_url() . 'home/viewMessage/' . $message->userid . '?messageid=' . $message->message_id . '">
                        <div class="propic"><img src="' . $message->userimage . '" alt="" onerror="' . $img . '" /></div>
                        <div class="info">
                            <p>
                                <strong>' . $message->name . '</strong>
                                <small>' . $message->message . '</small>
                            </p>
                            <div class="time">' . $this->ago_time($message->createdDate) . ' ago</div>
                        </div>
                    </a>
                	<em></em>
                 </li>';
            }
        } else {
            $var .='<li><p style=" color:#7870cc; padding:15px" class="errorMsg">No messages</p></li>';
        }
        echo $var;
        die;
    }

    public static function ago_time($date = NULL) {
        if (empty($date)) {
            return "No date provided";
        }

        $periods = array("s", "m", "h", "d", "w", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $unix_date = strtotime($date);

// check validity of date
        if (empty($unix_date)) {
            return "Bad date";
        }

// is it future date or past date
        /* echo $now.' </br>' ;
          echo $unix_date ; */
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";
        } else {
            $difference = $unix_date - $now;
            //$tense         = "from now";
            $tense = "ago";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        /* if($difference != 1) {
          $periods[$j].= "s";
          } */

        $showtime = "$difference $periods[$j]";

        if ($periods[$j] == 'month' || $periods[$j] == 'year') {
            $showtime = date("d/m/y", strtotime($date));
        }

        return $showtime;
    }

    function thanks() {

        $this->load->view('thanks');
    }

    function thanks_reply() {
        $this->load->view('thanks_reply');
    }

    function saveprofileimage() {

        $ime = $_POST['pic'];

        $image = explode(';base64,', $ime);
        $size = getimagesize($ime);
        $type = $size['mime'];
        $typea = explode('/', $type);
        $extnsn = $typea[1];
        $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

////$data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));
//        if (in_array($extnsn, $valid_exts)) {
//          if($extnsn == 'jpeg' || $extnsn == 'jng'){
//            $image = str_replace('data:image/jpeg;base64,', '', $image);
//          }else if($extnsn == 'png'){
//            $image = str_replace('data:image/png;base64,', '', $image);
//          }else if($extnsn == 'gif'){
//            $image = str_replace('data:image/gif;base64,', '', $image);
//          }
//        }

        $img_cont = str_replace(' ', '+', $image[1]);
        //$img_cont=$image[1];
        $data = base64_decode($img_cont);
        $im = imagecreatefromstring($data);
        $filename = time() . '.' . $extnsn;
        //echo $im; exit;
        $thumbnailpath = 'upload/profile/thumbnail/' . $filename;
        $mediumpath = 'upload/profile/medium/' . $filename;
        $fullpath = 'upload/profile/medium/' . $filename;


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
                $resp = imagejpeg($im, $thumbnailpath, $quality);
                $resp = imagejpeg($im, $mediumpath, $quality);
                $resp = imagejpeg($im, $fullpath, $quality);
            } else if ($extnsn == 'png') {
                $resp = imagepng($im, $thumbnailpath);
                $resp = imagepng($im, $mediumpath);
                $resp = imagepng($im, $fullpath);
            } else if ($extnsn == 'gif') {
                $resp = imagegif($im, $thumbnailpath);
                $resp = imagegif($im, $mediumpath);
                $resp = imagegif($im, $fullpath);
            }
        }
        // code for update user image
        $login = $this->administrator_model->front_login_session();



        $userid = $login->user_id;
        $businessId = $login->businessId;
        $update = array(
            'user_Id' => $userid,
            'image' => $filename,
        );
        $this->user_model->updateProfile($update, $businessId);
        return $resp;
    }

    public function saveBusinessMail() {
        $emails = $_POST['emails'];
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;

        //$emails = 'yogesh@live.com,yogesh@qsstechnosoft.com';
        //$emailsArray = explode(",", $emails);
        //print_r($emailsArray);
        $this->load->helper('email');
        $validEmailFlag = 0;

        // foreach ($emailsArray as $email) {
        //     if (valid_email($email)) {
        //         $validEmailFlag = 0;
        //     } else {
        //         $validEmailFlag = 1;
        //         echo $email;
        //     }
        // }

        if (valid_email($emails)) {

            $validEmailFlag = 1;
        } else {

            $validEmailFlag = 0;
            echo $emails;
        }

        if ($validEmailFlag == 0) {
            echo 0;
            die;
        } else {
            $date = date('Y-m-d H:i:s');
            $data = array(
                'emails' => $emails,
                'active' => '1',
                'isDelete' => '0',
                'createdDate' => $date,
                'modifiedDate' => $date,
                'user_id' => $userid,
                'source' => 'email',
                'source_id' => NULL,
                'access_token' => NULL,
                'refresh_token' => NULL,
                'oauth_token_secret' => NULL,
                'token_type' => NULL,
                'expires_in' => NULL
            );
            $this->db->insert('social', $data);
            echo $id = $this->db->insert_id();
            die;
        }
    }

    function video() {
        $statusid = $this->uri->segment('3');
        $arr_statusDetails['status_id'] = $statusid;
        $data['status_image'] = $this->status_model->getOneStatus($arr_statusDetails, "status_image");
        $this->load->view('video', $data);
    }

    function imagePopup() {
        $statusid = $this->uri->segment('3');
        $arr_statusDetails['status_id'] = $statusid;
        $data['statusdetails'] = $this->status_model->getOneStatus($arr_statusDetails, "status_image");
        $this->load->view('imagePopup', $data);
    }

    // aws push notification
    public function amazonSns($deviceToken, $message, $deviceType) {
        $this->load->library('Aws_sdk');
        $Aws_sdk = new Aws_sdk();
        if ($deviceType == 'ios') {
            $iOS_AppArn = "arn:aws:sns:us-west-2:831947047245:app/APNS_SANDBOX/Hurree";

            $endpoint = $Aws_sdk->generateEndpoint($deviceToken, $iOS_AppArn);
            $result = $Aws_sdk->SendPushNotification($message, $endpoint, $deviceToken);
            return $result;
        }
    }

    public function about() {
        $this->load->view('front_header_new');
        $this->load->view('about_us');
//$this->load->view('index', $data);
        $this->load->view('front_footer_new');
    }

    public function solutions() {
        $this->load->view('front_header_new');
        $this->load->view('solutions');
//$this->load->view('index', $data);
        $this->load->view('front_footer_new');
    }

    public function contactUs() {
        $this->load->view('front_header_new');
        $this->load->view('contact_us');
        $this->load->view('front_footer_new');
    }

    function sendContact() {

        $name = $_POST['name'];
        $email = $_POST['email'];
        $subject = $_POST['subject'];
        $message = $_POST['message']; //htmlspecialchars($_POST['message'])

        $this->emailConfig();
        //// MESSAGE OF EMAIL
        $message = 'Name: ' . $name . '<br><br>Email: ' . $email . '<br><br>Subject: ' . $subject . '<br><br>Message:<br><br> ' . $message;

        //// FROM EMAIL
        $this->email->from('hello@marketmyapp.co');
        $this->email->to('business@hurree.co');  //$this->email->to('business@hurree.co');
        $this->email->subject('Contact Enquiry');
        $this->email->message(nl2br($message));
        $this->email->send();     ////  EMAIL SEND
        //echo $this->email->print_debugger();
        echo 'success';
    }

    public function real_time_data_analytics() {

        $this->load->view('front_header_new');
        $this->load->view('real_time_data_analytics');
        $this->load->view('front_footer_new');
    }

    public function geofencing() {

        $this->load->view('front_header_new');
        $this->load->view('geofencing');
        $this->load->view('front_footer_new');
    }

    public function inStoreTrafficAnalysis() {

        $this->load->view('front_header_new');
        $this->load->view('in_store_traffic');
        $this->load->view('front_footer_new');
    }

    public function terms() {

        $this->load->view('front_header_new');
        $this->load->view('terms');
        $this->load->view('front_footer_new');
    }

    public function privacy() {

        $this->load->view('front_header_new');
        $this->load->view('privacy_policy');
        $this->load->view('front_footer_new');
    }

    public function resources() {

        $this->load->view('front_header_new');
        $this->load->view('resources');
        $this->load->view('front_footer_new');
    }

    public function resourceselection() {
        $this->load->view('front_header_new');
        $this->load->view('selection');
        $this->load->view('front_footer_new');
    }

    public function resourcesexample() {
        $this->load->view('front_header_new');
        $this->load->view('example_info_graphic');
        $this->load->view('front_footer_new');
    }

    public function resourceCustomerProfilingVideo() {
        $this->load->view('front_header_new');
        $this->load->view('customer_profiling_video');
        $this->load->view('front_footer_new');
    }

    public function resourceCustomerProfiling() {
        $this->load->view('front_header_new');
        $this->load->view('resource_customer_profiling');
        $this->load->view('front_footer_new');
    }

    public function resourceVideos() {
        $this->load->view('front_header_new');
        $this->load->view('resource_videos');
        $this->load->view('front_footer_new');
    }

    public function resourceGuides() {
        $this->load->view('front_header_new');
        $this->load->view('resource_guides');
        $this->load->view('front_footer_new');
    }

    public function resourceCaseStudies() {
        $this->load->view('front_header_new');
        $this->load->view('resource_case_studies');
        $this->load->view('front_footer_new');
    }

    public function resourceInfoGraphics() {
        $this->load->view('front_header_new');
        $this->load->view('resource_info_graphics');
        $this->load->view('front_footer_new');
    }

    public function businessUserSignup() {
        //echo "djhdgjhd"; exit;
        $this->load->view('front_header_new');
        $this->load->view('business_new');
        $this->load->view('front_footer_new');
    }

    function blockuser() {

        $login = $this->administrator_model->front_login_session();

        $block['userid'] = $login->user_id;
        $block['block_user_id'] = $_POST['id'];

        $block['isActive'] = 1;
        $blockuser = $this->user_model->getblockuserid($block, '');
        //echo count($blockuser); die;

        if (count($blockuser) == 0) {
            $follow['follow_id'] = '';
            $follow['createdDate'] = date('YmdHis');
            $this->user_model->block($block);

            echo json_encode('Blocked');
        } else {

            $this->user_model->deleteblock($blockuser->block_id);

            echo json_encode('unblocked');
        }
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
                    'value' => 'salesqualifiedlead'//'lead'
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

    function userRegistration() {
        $brandUser = array();
        $firstname = $this->input->post('firstname');
        $lastname = $this->input->post('lastname');
        $email = $this->input->post('email');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $referralCodeParent = $this->input->post('referred_parent');
        if (empty($referralCodeParent)) {
            $referralCodeParent = NULL;
        }
        generateReferalCode:
        //generate referal code
        $referralCode = $this->generateReferralCode();
        //check referal code in database        
        $referralCount = $this->user_model->checkReferralCodeExist($referralCode);
        if ($referralCount > 0) {
            goto generateReferalCode;
        }

        
        $date = date('YmdHis');
        $user['user_Id'] = '';
        $user['firstname'] = $firstname;
        $user['lastname'] = $lastname;
        $user['businessId'] = 0;
        $user['businessName'] = "";
        $user['accountType'] = 'trail';
        $user['username'] = $username;
        $user['password'] = md5($password);
        $user['email'] = $email;
        $user['usertype'] = '8';
        $user['image'] = 'user.png';
        $user['developerLogo'] = '';
        $user['active'] = 1;
        $user['firstLogin'] = $date;
        $user['createdDate'] = $date;
        $user['referred_parent'] = $referralCodeParent;
        $user['referral_code'] = $referralCode;
        $last_id = $this->user_model->insertsignup($user);
        $users['businessId'] = $last_id;
        $this->db->where('user_Id', $last_id);
        $this->db->update("users", $users);
        $business = array(
            "businessId" => $last_id,
            "businessName" => "",
            "country" => 0
        );
        $this->db->insert("business", $business);
        $brand_arr = array(
            'user_id' => $last_id,
            "businessId" => $last_id,
            'totalIosApps' => 1,
            'totalAndroidApps' => 1,
            'totalCampaigns' => 1,
            'totalAppGroup' => 1,
            'androidCampaign' => 1,
            'iOSCampaign' => 1,
            'emailCampaign' => 5,
            'crossChannel' => 1,
            'inAppMessaging' => 5,
            'webhook' => 5,
            'createdDate' => date('YmdHis')
        );
        $this->brand_model->savePackage($brand_arr);

        /* check and update the entry in referfriend table */
        if (!is_null($referralCodeParent)) {
            $refferParentData = $this->user_model->getUserByReferralCode($referralCodeParent);
            $emailPerMonthCount = $this->referfriend_model->getCountReferralFriendPerMonth($refferParentData[0]->user_Id);
            $emailExixtCountInReferalFriend = $this->referfriend_model->checkReferralFriendEmailExistWithSignupId($email, $refferParentData[0]->user_Id);
            if (($emailPerMonthCount < 3) && ($emailExixtCountInReferalFriend <= 0)) {
                $data = array();
                $login = $this->administrator_model->front_login_session();
                $data['userId'] = $last_id;
                $data['email'] = $email;
                $data['status'] = 'Completed';
                $data["createdDate"] = $date;
                $insertCount = $this->referfriend_model->insertReferralFriend($data);
            }
            $this->user_model->getUserByReferralCode($referralCodeParent);
            $referralData = $this->referfriend_model->updateReferralFriendOnUserSignup($parentData[0]->user_Id, $email);
        }
        // create session
        $where['user_Id'] = $last_id;
        $userDetails = $this->user_model->getOneUserDetails($where, '*, usertype as accesslevel');
        $this->session->set_userdata('logged_in', $userDetails);
        //insert the record in email_signup table for sending email after signup through cron
        $data = array('user_id' => $last_id, "sent" => 0, 'modifiedDate' => $date);
        $this->db->insert('email_signup', $data);
        //end insert code of email_signup
        //$this->session->set_userdata('bransUser', $brandUser);
        echo 'Success';
    }

    public function signupEmail() {
        $signupEmailData = $this->user_model->getSignupMail();
        foreach ($signupEmailData as $sentEmailData) {
            $userData = $this->user_model->getUser($sentEmailData->user_id);
            $this->user_model->updateSignupMailStatus($sentEmailData->id);
            if (!empty($userData->email)) {
                // send data to hubspot
                $portal = HUBPORTALID;

                $email_template = $this->email_model->getoneemail('brandSignUp');
                //// MESSAGE OF EMAIL
                $messages = $email_template->message;
                $hurree_image = base_url() . 'assets/img/Graph-icon-white-grey.png';
                //// replace strings from message
                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{Username}', ucfirst($userData->username), $messages);
                $httpClient = new \Http\Adapter\Guzzle6\Client(new Client());
                $sparky = new SparkPost($httpClient, ['key' => SPARKPOSTKEYSUB]);
                $promise = $sparky->transmissions->post([
                    'content' => [
                        'from' => [
                            'name' => 'Hurree',
                            'email' => 'hello@marketmyapp.co',
                        ],
                        'subject' => $email_template->subject,
                        'html' => $messages,
                        'text' => '',
                    ],
                    'recipients' => [
                        [
                            'address' => [
                                'name' => '',
                                'email' => $userData->email,
                            ],
                        ],
                    ],
                ]);
                $promise = $sparky->transmissions->get();
                try {
                    $response = $promise->wait();
                    echo $response->getStatusCode() . "\n";
                    print_r($response->getBody()) . "\n";
                } catch (\Exception $e) {
                    echo $e->getCode() . "\n";
                    echo $e->getMessage() . "\n";
                }

                $status = $this->hubspotAuthenticaion($portal);
                if ($status == 302) {
                    $responce_code = $this->savecontactToHubspot($userData->email, $userData->firstname, $userData->lastname);
                    if ($responce_code != 200) {
                        $responcecode = $this->savecontactToHubspot($userData->email, $userData->firstname, $userData->lastname);
                        if ($responcecode != 200) {
                            $responcecode = $this->savecontactToHubspot($userData->email, $userData->firstname, $userData->lastname);
                        }
                    }
                }
            }
        }
    }

    ////  EMAIL SEND 


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

    /* function to generate all user referal code. */

    public function generateReferralCodeToAllUsers() {
        $query = $this->db->query('SELECT * FROM users WHERE referral_code is null');

        // echo $this->db->last_query();; die;
        $allUsers = $query->result_array();

        foreach ($allUsers as $user) {
            generateReferalCode:
            //generate referal code
            $referralCode = $this->generateReferralCode();
            //check referal code in database        
            $referralCount = $this->user_model->checkReferralCodeExist($referralCode);
            if ($referralCount > 0) {
                goto generateReferalCode;
            }

            $data = array('referral_code' => $referralCode);
            $this->db->where('user_Id', $user['user_Id']);
            $this->db->update('users', $data);
            $afftectedRows = $this->db->affected_rows();

            //SELECT `referral_code`, COUNT(*) c FROM users GROUP BY `referral_code` HAVING c > 1
        }
    }
    /*
     * call this function as executeSingleToUpdateKey
     */
    function executeSingleToUpdateKey($error = 0)
    {
        $result = $this->user_model->getUserHaveNull($error);
        if($result != null) {
            foreach ($result as $row) {
                if ($row->referral_code == '')
                    $referralCode = substr(md5(uniqid(mt_rand(), true)), 0, 8);
                else
                    $referralCode = $row->referral_code;
                $userID = $row->user_Id;
                $sparkPostArray = array(
                    "name" => $referralCode,
                    "key_label" => "API Key for $referralCode",
                    "key_grants" => array(
                        "0" => 'smtp/inject',
                        "1" => 'sending_domains/manage',
                        "2" => 'message_events/view',
                        "3" => 'suppression_lists/manage',
                        "4" => 'tracking_domains/view',
                        "5" => 'tracking_domains/manage',
                    ),
                    "key_valid_ips" => array(),
                    "ip_pool" => ''
                );
                $sparkPostJson = json_encode($sparkPostArray);

                ///
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
                ///
                //$responseSparkPost = '{"results":{"key":"c2239d2aafda9211a2aad1a2e6261c22d3374c75","short_key":"c223","label":"API Key for Sparkle Ponies Subaccount","subaccount_id":2}}';
                $responseSparkPostArray = json_decode($responseSparkPost, true);
                if(isset($responseSparkPostArray['results']['key']))
                    $this->user_model->updateUsersSparkPostDetails($responseSparkPost, $responseSparkPostArray['results']['key'], $userID);
                else
                    $this->user_model->updateUsersSparkPostDetails(NULL, $responseSparkPostArray['results']['key'], $userID);

            }
        }
    }
    function executeSingleToUpdateKeyError($error = 1)
    {
        $result = $this->user_model->getUserHaveNull($error);
        if($result != null) {
            foreach ($result as $row) {
                if ($row->referral_code == '')
                    $referralCode = substr(md5(uniqid(mt_rand(), true)), 0, 8);
                else
                    $referralCode = $row->referral_code;
                $userID = $row->user_Id;
                $sparkPostArray = array(
                    "name" => $referralCode,
                    "key_label" => "API Key for $referralCode",
                    "key_grants" => array(
                        "0" => 'smtp/inject',
                        "1" => 'sending_domains/manage',
                        "2" => 'message_events/view',
                        "3" => 'suppression_lists/manage',
                        "4" => 'tracking_domains/view',
                        "5" => 'tracking_domains/manage',
                    ),
                    "key_valid_ips" => array(),
                    "ip_pool" => ''
                );
                $sparkPostJson = json_encode($sparkPostArray);

                ///
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
                ///
                //$responseSparkPost = '{"results":{"key":"c2239d2aafda9211a2aad1a2e6261c22d3374c75","short_key":"c223","label":"API Key for Sparkle Ponies Subaccount","subaccount_id":2}}';
                $responseSparkPostArray = json_decode($responseSparkPost, true);
                if(isset($responseSparkPostArray['results']['key']))
                    $this->user_model->updateUsersSparkPostDetails($responseSparkPost, $responseSparkPostArray['results']['key'], $userID);
                else
                    $this->user_model->updateUsersSparkPostDetails(NULL, $responseSparkPostArray['results']['key'], $userID);

            }
        }
    }

}
