<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('credit_card_helper', 'time'));

        $this->load->library(array('form_validation', 'facebook', 'email', 'image_lib', 'user_agent', 'Mobile_Detect', 'session'));
        $this->load->model(array('user_model', 'email_model', 'country_model', 'payment_model', 'promocode_model', 'administrator_model', 'pushmessage_model', 'message_model', 'games_model', 'score_model', 'challenge_model', 'store_model', 'notification_model', 'status_model', 'qrcode_model', 'subscription_model', 'beacon_model', 'targetoffer_model', 'campaign_model','package_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');

    }

    function index($signup=False) {
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
            $data['signup'] = $signup;

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
        } else {
            if ($header['login']->true == 1 && $header['login']->accesslevel != '' && $header['login']->active == 1) {
                redirect('timeline');
            } else {
                $this->session->unset_userdata('user_logged_in');
                redirect('home/index');
            }
        }
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
                            </ul></nav>';
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
}
