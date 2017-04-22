<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class HurreeSDK extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree','cookie', 'salesforce_helper', 'permission_helper', 'permission'));

        $this->load->library(array('form_validation', 'pagination'));

        $this->load->model(array('user_model', 'brand_model', 'payment_model', 'administrator_model', 'groupapp_model', 'notification_model', 'country_model', 'permission_model', 'location_model', 'email_model', 'campaign_model', 'reward_model', 'businessstore_model','offer_model','geofence_model','role_model','contact_model','hubSpot_model'));
		    $header['allPermision'] = $this->_getpermission();
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active == 0) {
          redirect(base_url());
        }
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    function _getpermission ()
    {
      $login = $this->administrator_model->front_login_session();

      if(isset($login->user_id) && isset($login->usertype)){

        $userid= $login->user_id;
        $usertype= $login->usertype;

        if($usertype == 9) {

          $allPermision = getAssignPermission( $userid);
              //  print_r($data['allPermision']); die;
          return $allPermision;
        } else {
          return false;
        }
      } else {
          return false;
      }
    }

    public function index(){
      $this->load->view('3.1/sdk/header');
      $this->load->view('3.1/sdk/index');
      $this->load->view('3.1/sdk/footer');
    }

    public function ios(){
      $this->load->view('3.1/sdk/header');
      $this->load->view('3.1/sdk/left-sidebar');
      $this->load->view('3.1/sdk/ios');
      $this->load->view('3.1/sdk/footer');
    }

    public function android(){
        $this->load->view('3.1/sdk/header');
        $this->load->view('3.1/sdk/left-sidebar');
        $this->load->view('3.1/sdk/android');
        $this->load->view('3.1/sdk/footer');
    }
    
    public function platformFeatures(){
        $this->load->view('3.1/sdk/header');
        $this->load->view('3.1/sdk/left-sidebar');
        $this->load->view('3.1/sdk/platform_features');
        $this->load->view('3.1/sdk/footer');
    }
    
    public function analytics(){
        $this->load->view('3.1/sdk/header');
        $this->load->view('3.1/sdk/left-sidebar');
        $this->load->view('3.1/sdk/hurree_analytics');
        $this->load->view('3.1/sdk/footer');
    }
    
    public function phonegap(){
        $this->load->view('3.1/sdk/header');
        $this->load->view('3.1/sdk/left-sidebar');
        $this->load->view('3.1/sdk/phonegap');
        $this->load->view('3.1/sdk/footer');
    }

}
