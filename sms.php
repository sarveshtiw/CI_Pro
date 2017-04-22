<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Sms extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
	
		$this->load->library(array('form_validation', 'pagination'));
		$this->load->model(array('administrator_model','sms_model','permission_model'));
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	}
	
	function index() {
		$login = $this->administrator_model->front_login_session();
		if ($login->active != 0) {
			$header['page'] = 'sms';
			$header['userid'] = $login->user_id;
			$header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
			$header['usertype'] = $login->usertype;
			$header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
	
			$this->load->view('inner_header3.0', $header);
			$this->load->view('smsPage');
			$this->load->view('inner_footer3.0');
		} else {
			redirect(base_url());
		}
	}
	
	function addSms(){
		
		$this->load->view('add_sms');
	}
	
	function checkPhoneNumberExist(){
		$phone = "+".trim($_POST['phone']);
		$exist = $this->sms_model->checkPhoneNumber($phone);
		echo count($exist);
	}
	
	function saveSms(){
		
		$login = $this->administrator_model->front_login_session();
		
		$createdBy = $login->user_id;
		$businessId = $login->businessId;
		
		$name = $_POST['name'];
		$phone = "+".trim($_POST['phone']);
		
		$save['sms_id'] = '';
		$save['businessId'] = $businessId;
		$save['name'] = $name;
		$save['phoneNumber'] = $phone;
		$save['createdBy'] = $createdBy;
		$save['createdDate'] = date('YmdHis');
		
		$last_id = $this->sms_model->saveSms($save);
		echo 1;
		
	}
	
	function smsListingResponse() {
		$login = $this->administrator_model->front_login_session();
		if ($login->active != 0) {
	
			$where['businessId'] = $login->businessId;
			$where['isActive'] = 1;
			$data2['sms'] = $this->sms_model->getSms($where);
	
			$data1 = array();
			
			for ($i = 0; $i < count($data2['sms']); $i++) {
			
				
			
			
				$data1[$i] = array(
						$data2['sms'][$i]['name'],
						$data2['sms'][$i]['phoneNumber'],
						$data2['sms'][$i]['action']
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
	
	function deleteSmsPopUp($sms_id){
		$data['sms_id'] = $sms_id;
		$this->load->view('sms_delete',$data);
	}
	
	function deleteSms(){
		
		$update['sms_id'] = $_POST['sms_id'];
		$update['isActive'] = 0;
		$this->sms_model->updateSms($update);
		echo 1;
	}
	
	
}