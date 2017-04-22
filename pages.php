<?php

class Pages extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('url'));
        //$this->load->library(array('form_validation', 'facebook', 'email', 'image_lib', 'user_agent', 'Mobile_Detect', 'session'));
        $this->load->model(array('settings_model', 'administrator_model'));
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    function index() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $data['blogPost'] = $this->getBlogPosts();
        //echo "<pre>"; print_r($data['blogPost']); exit;
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/home', $data);
        $this->load->view('3.1/front_footer', $data);
    }

    function referral($referralCode = NULL) {

        $login = $this->administrator_model->front_login_session();
        //echo '<pre>'; print_r($login ); die;
        if ($login->active != 0) {
            redirect('appUser');
        } else {
            // var_dump($referralCode) ; die;
            $data = array();
            $result = $this->settings_model->get_settings();
            if ($result[0]['value'] == 'on') {
                $data['frontPagePopup'] = TRUE;
                $data['url'] = $result[1]['value'];
            }
            $data['blogPost'] = $this->getBlogPosts();

            $this->load->view('3.1/front_header');
            $this->load->view('3.1/home', $data);
            $this->load->view('3.1/front_footer', $data);
        }
    }

    function getBlogPosts() {
        $blog_url = "https://api.hubapi.com/content/api/v2/blog-posts?hapikey=" . HAPIKEY . "&order_by=-publish_date";
        // create curl resource
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $blog_url);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output contains the output string
        $response = curl_exec($ch);
        // close curl resource to free up system resources
        curl_close($ch);
        $response = json_decode($response, true);
        //	echo "<pre>";
        $blogArr = array();
        foreach ($response['objects'] as $key => $blog) {
            //	echo $blog['analytics_page_id'];
            //print_r($blog); exit;
            if ($blog['is_draft'] == false && $blog['publish_immediately'] == true) { //$key == 0
                $blogArr = array("blog_title" => $blog['html_title'], "blog_url" => $blog['url'], "featured_image" => $blog['featured_image']);
                break;
            }
        }
        return $blogArr;
        //print_r($blogArr); exit;
        //print_r($response);
        //exit();
    }

    function about() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }

        $this->load->view('3.1/front_header');
        $this->load->view('3.1/about_us');
        $this->load->view('3.1/front_footer', $data);
    }

    function solutions() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/customer_profiling');
        $this->load->view('3.1/front_footer', $data);
    }

    function resources() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/resources');
        $this->load->view('3.1/front_footer', $data);
    }

    function contactUs() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/contact_us');
        $this->load->view('3.1/front_footer', $data);
    }

    function real_time_data_analytics() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/real_time_data_analytics');
        $this->load->view('3.1/front_footer', $data);
    }

    function geofencing() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/geofencing');
        $this->load->view('3.1/front_footer', $data);
    }

    function mobile_marketing() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/mobile_marketing');
        $this->load->view('3.1/front_footer', $data);
    }

    function guides() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/guides');
        $this->load->view('3.1/front_footer', $data);
    }

    function case_studies() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/case_studies');
        $this->load->view('3.1/front_footer', $data);
    }

    function infoGraphics() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/infoGraphics');
        $this->load->view('3.1/front_footer', $data);
    }

    function terms() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/terms');
        $this->load->view('3.1/front_footer', $data);
    }

    function privacy() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/privacy');
        $this->load->view('3.1/front_footer', $data);
    }

    function messaging() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/messaging');
        $this->load->view('3.1/front_footer', $data);
    }

    function listManagement() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/listManagement');
        $this->load->view('3.1/front_footer', $data);
    }

    function prohibitedContent() {
        $data = array();
        $result = $this->settings_model->get_settings();
        if ($result[0]['value'] == 'on') {
            $data['frontPagePopup'] = TRUE;
            $data['url'] = $result[1]['value'];
        }
        $this->load->view('3.1/front_header');
        $this->load->view('3.1/prohibitedContent');
        $this->load->view('3.1/front_footer', $data);
    }

    function socialAlertMessage() {
        $this->session->unset_userdata('fblogin', 0);
        $this->session->unset_userdata('googlelogin', 0);
        $this->session->unset_userdata('fbsignup', 0);
        $this->session->unset_userdata('googlesignup', 0);
        $this->session->set_flashdata('email_id_exist', '');
        echo 1;
        exit();
    }

}
