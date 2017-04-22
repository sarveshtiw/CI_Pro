<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Reportstatuses extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
	
		$this->load->helper(array('credit_card_helper', 'time'));
	
		$this->load->library(array('form_validation',  'email', 'image_lib', 'user_agent', 'Mobile_Detect', 'session'));
		$this->load->model(array('user_model', 'email_model', 'country_model', 'administrator_model', 'status_model','report_model'));
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	
	}
	
	function index(){
	$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			
			$data['records']=$this->report_model->getReportStatuses();
			$config['base_url'] = base_url().'index.php/reportstatuses/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$data['reportstatuses'] = $this->report_model->getReportStatuses($page, $config['per_page']);
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('admin_report_status',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function delete_report(){
		
		$reportStatuses_id = $this->uri->segment('3');
		
		$update['id']=$reportStatuses_id;
		$update['isDelete']=1;
		$this->report_model->updateReportStatuses($update);
		
		$this->session->set_flashdata('success_messege','Report message has been deleted sucessfully');
		redirect($_SERVER['HTTP_REFERER']);
		
	}
	
	function delete_status(){
		
		$status_id = $this->uri->segment('3');
		
		$update['status_id']=$status_id;
		$update['active'] = 0;
		$update['isDelete']=1;
		$this->report_model->updateStatuses($update);
		
		$this->session->set_flashdata('success_messege','Status has been deleted sucessfully');
		redirect($_SERVER['HTTP_REFERER']);
	}
	
}