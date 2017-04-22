<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Beacon extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
	
		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('user_model','beacon_model','administrator_model'));
		$this->load->library('pagination');
		$this->load->library('image_lib');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
		
			
	}

	function index(){
//	echo 'INDEX'; die;
		$login=$this->administrator_model->login_session();
		
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$methods = $this->uri->segment(2); 
			if($methods == "beaconForm")
			{
				$this->beaconForm();
				//die;
			} else if($methods == "savebeacon"){
				
				$this->savebeacon();
				
			} else  if($methods == "saveBeacon") {
				
				// Add/Update beacon
				$this->beaconDetailForm();
				
			} else if($methods == "saveBeaconDetails") {
				// Save Beacon Details 
				
				$this->saveBeaconDetails(); 
				
			} else if($methods =="delete"){
				$this->deleteBeacon(); 
			} else {
				
				$beaconAttr = array();
				
				 $searchParam = $this->input->get();
// 			/	echo '<pre>'; print_r($searchParam); die;	
				
				$data['admin']=$this->user_model->getOneUser($login['userid']);
				
				$header['url']=$this->uri->segment(1);
				$header['image']=$login['image'];
				$header['username']=$login['username'];
				
				$select = "beacons.beaconId,uuid,beacons.isActive, beacons.isDelete, beacons.createdDate, connectionId, userId, users.username, beaconUserConnection.isActive as connectionActive";
				$row = "1";
				$where = array();
				if(isset($searchParam['searchBy']) &&  $searchParam['searchBy'] !=='')
				{
					$where[$searchParam['searchBy']] = $searchParam['search'];
				}
				if( isset($searchParam['username']) && $searchParam['username']!='' ) {
					$where['username'] = $searchParam['username'];
				}
				$where['beacons.isDelete'] = 0;
					
				//print_r($where ); die;
				$data['records']=$this->beacon_model->getBeacon($select,$where, $row);
// 				echo $this->db->last_query();
// 				echo '</br></br>';
				$config['base_url'] = base_url().'index.php/beacon/index/';
				$config['total_rows'] =count($data['records']);
				$config['per_page'] = '10';
				$config['uri_segment']= 3;
				$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
				$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
				
				$data['beacons'] = $this->beacon_model->getBeacon($select, $where , $row,$page, $config['per_page']);
// 				echo $this->db->last_query();
// 				die;
			//die;
				
				$data['searchPara'] = $searchParam ;
				
				// Get All beacon list for autocomplete
				$wh = array();
				$data['allBeacons']=$this->beacon_model->getOnlyBeacon("beaconId, minor, major, uuid", $wh,"1");
				
				
				// Get all business userlist for autocomplete
				$data['allusername']= $this->user_model->getOneUserType("2", NULL, NULL, NULL,"user_Id, username");
			//	echo '<pre>'; print_r($data); die;
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('beacons',$data);
				$this->load->view('admin_footer');
				
			}	 
		} else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
		

	function beaconForm() {
		$login=$this->administrator_model->login_session();
	
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data = array(); 
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		//	echo '<pre>';
		//	$data['admin']=$this->user_model->getOneUser($login['userid']);
	
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
 
			$select = "user_Id, username";
			// Get All Business Users 
			$data['allBusinessUser']= $this->user_model->getOneUserType($usertype=2, $start=NULL, $pagesize=NULL, $order_by=NULL,$select, $activeLogin=NULL);
	
			//print_r($allBeacons); die;
			
			$id = $this->uri->segment(3);
			$select = "beacons.beaconId,uuid,beacons.isActive, beacons.major, beacons.minor, beacons.isDelete, beacons.createdDate, connectionId, userId, users.username, beaconUserConnection.isActive as connectionActive";
			$row = "";
			$where = array(
					"beacons.beaconId" => $id
			);
			
			$data['beaconDetails'] = $this->beacon_model->getBeacon($select,$where, $row);
			
			// get all unassign beacon uuid
			//$data['allBeacons'] = $this->beacon_model->getUnAssignBeacons($id);
		//	echo '<pre>'; print_r($data); die;
				
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('beaconForm',$data);
			$this->load->view('admin_footer');

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	
	}
	
	function savebeacon()
	{
		$beaconsDetails = $_POST;
		$data['connectionId'] = $beaconsDetails['connectionId'];
		$data['userId'] = $beaconsDetails['username'];
		$data['beaconId'] = $beaconsDetails['beaconId'];
		$data['isActive'] = $beaconsDetails['active'];
		
		if($beaconsDetails['connectionId'] == "")
		{
			$data['createdDate'] = date('YmdHis');
		} else {
			$data['modifiedDate'] = date('YmdHis');
		}
		if($beaconsDetails['username'] == '')
		{
			$where = array(
					"beaconUserConnection.connectionId" =>$beaconsDetails['connectionId'],
					
			);
				
			$this->beacon_model->deleteBeaconUserConnection($where);
		} else {
			$this->beacon_model->savebeaconUserConnection($data);
		}
		
		//echo $this->db->last_query(); die;
		redirect("beacon");
	}
	
	function beaconDetailForm()
	{
	$login=$this->administrator_model->login_session();
	
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data = array();
			$id = '';
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		//	echo '<pre>';
		//	$data['admin']=$this->user_model->getOneUser($login['userid']);
	
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			$id = $this->uri->segment(3);
			if($id != '')
			{
				$select ="*";
				$where = array ("beaconId"=> $id);
				$data['oneBeacon'] = $this->beacon_model->getOnlyBeacon($select, $where, "");
			} else {
				$oneBeacon = (object) array(
						"beaconId" => '',
						"uuid" => '',
						"major" => '',
						"minor" => '',
						"isActive" => 1, 
					);
				$data['oneBeacon'] = $oneBeacon; 
			}
			
			
				
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('beaconSaveForm',$data);
			$this->load->view('admin_footer');

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
		
		
	}
	
	function saveBeaconDetails()
	{
		echo '<pre>'; 
		$save['beaconId'] = $_POST['beaconId'];
		$save['uuid'] = $_POST['uuid'];
		$save['major'] = $_POST['major'];
		$save['minor'] = $_POST['minor'];
		$save['isActive'] = $_POST['isActive'];
		$save['createdDate'] = date('YmdHis');
		
		$this->beacon_model->saveBeacons($save);
		$this->session->set_flashdata('success_messege','Beacon has been saved successfully');
		redirect("beacon");
		//print_r($save); die;
		
	}
	
	function deleteBeacon()
	{
		$id = $this->uri->segment(3);
		
		$data = array(
					"beaconId" => $id,
					"isDelete" => "1",
					"isActive" => "0"
			);
				
			$this->beacon_model->saveBeacons($data );
			
			$connection = array(
					"beaconId" => $id,
			);
			
			$this->beacon_model->deleteBeaconUserConnection($connection);
			//echo $this->db->last_query(); die;
			redirect("beacon");
		//$this->db->
	}
	
	
}
