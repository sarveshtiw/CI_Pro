<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class settings extends CI_Controller {

    public function __construct() {
        parent::__construct();

        //$this->load->helper(array('form', 'url'));
        $this->load->library(array('form_validation', 'session', 'pagination',""));
        $this->load->model(array('user_model', 'administrator_model', 'settings_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    public function index() {

        $header['sess_details'] = $this->session->userdata('sess_admin');

        if (count($header['sess_details']) > 0 && $header['sess_details']->ad_accesslevel == 'admin') {

            $admin = $this->user_model->getOneUser($header['sess_details']->ad_userid);   //// Get Admin Details
            $header['url'] = $this->uri->segment(1);
            $header['image'] = $admin->image;
            $header['username'] = $admin->username;
            $result = $this->settings_model->get_settings();
            $data['popup_status'] = $result[0]['value'];
            $data['popup_url'] = $result[1]['value'];
            if (isset($_POST['submit'])) {                
                if ($_POST['submit'] == 'submit') {
                    $this->form_validation->set_rules('popup_status', 'popup_status', 'trim|required');
                    $this->form_validation->set_rules('popup_url', 'popup_url', 'required|valid_url_format');

                    if ($this->form_validation->run() == FALSE) {
                        $data['popup_status'] = $result[0]['value'];
                        $data['popup_url'] = $result[1]['value'];
                       
                    } else {
                        $this->settings_model->set_settings($_POST['popup_status'], $_POST['popup_url']);
                        //$this->session->set_flashdata('success_messege', 'Settings saved successfully');
                        $result = $this->settings_model->get_settings();
                        $data['popup_status'] = $result[0]['value'];
                        $data['popup_url'] = $result[1]['value'];
                        
                    }
                }
            }
           
            $this->load->view('admin_header', $header);
            $this->load->view('admin_subheader', $header);
            $this->load->view('settings', $data);
            $this->load->view('admin_footer');
        } else {
            $this->session->set_flashdata('alert_message', 'Username Does Not Exits');
            redirect('H5fgs2134vbdsgtfdsrt');
        }
    }
    
 
}
?>

