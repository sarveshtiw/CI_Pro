<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Developer extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->helper(array('hurree','form', 'url'));
		$this->load->library(array('form_validation','session','pagination','image_lib','email'));
		$this->load->model(array('user_model','email_model','administrator_model'));
		emailConfig();
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	}
	
	public function index(){
		
		 $login=$this->administrator_model->login_session();

        if( $login['true']==1 && $login['accesslevel']=='admin')
        {
			
			  $usertype="8";
	          $subusertype = "9";
	          $activeLogin="1";
	          $data['records']=$this->user_model->getOneUserType($usertype,'','','','', $activeLogin,$subusertype);      //// Count Same User Type
	          $config['base_url'] = base_url().'index.php/developer/index/';
	          $config['total_rows'] = count($data['records']);
	          $config['per_page'] = '10';
	          $config['uri_segment']= 3;
	          $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
	          $page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
	
	          $order_by['order_by']="user_Id";
	          $order_by['sequence']="DESC";
	
	          $data['users']=$this->user_model->getOneUserType($usertype,$page, $config['per_page'],$order_by,'',$activeLogin,$subusertype);    //// Get List Of Same User Type
	          /* echo $this->db->last_query();
	          echo '<pre>'; print_r($data['users']); die; */
	
	          $header['url']=$this->uri->segment(1);
	          $header['consumer']=2;  /// for Business
	          $header['image']=$login['image'];       //// Get Admin Image
	          $header['username']=$login['username'];           //// Get Admin Username
	          $data['type'] = 'appUser';
			
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('3.1/developer',$data);
			$this->load->view('admin_footer');
			
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
		
	}
	
	function addDeveloper($id=Null){
		$header['sess_details'] = $this->session->userdata('sess_admin');
		if(count($header['sess_details']) > 0 && $header['sess_details']->ad_accesslevel == 'admin')
		{

			if($id == ''){
				$data['firstname'] = '';
				$data['lastname'] = '';
				$data['email'] = '';
				$data['businessName'] = '';
				$data['editable']='';
				$data['id']='';
				$data['additional_profit'] = 0;
			}else{
				$oneuser=$this->user_model->getOneUser($id);
				$data['email']=$oneuser->email;
				$data['firstname']=$oneuser->firstname;
				$data['lastname']=$oneuser->lastname;
				$data['businessName'] = $oneuser->businessName;
				$data['editable']='0';
				$data['id']=$id;
				$data['additional_profit'] = $oneuser->additional_profit;
			}
			$data['usertype'] = 8;  //For Developer
			
			$header['url']=$this->uri->segment(1);
			$header['consumer']=1;
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;           //// Get Admin Username
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('3.1/add_developer',$data);
			$this->load->view('admin_footer');
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function insertDeveloper(){
	
			$userid = $this->input->post('userid');
			
			if($userid == ''){
				
				$lastBusinessId = $this->user_model->getMaxBusinessRow();
				$businessId = $lastBusinessId->businessId+1;
				
				$business = array(
				
						"busi_id" =>'',
						"businessId" => $businessId,
						"businessName" => ''
				
				);
				$this->user_model->insertBusiness($business);
				
			}
				
				$rand = $this->RandomStringCreateUsername();
				
				$user['user_Id']=$userid;
				$user['firstname']=$this->input->post('firstname');
				$user['lastname']=$this->input->post('lastname');
				if($userid == ''){
				$user['username'] = '{Username}';
				$user['businessId'] = $businessId;
				$user['image'] = 'user.png';
				$user['username_create_token'] = $rand;
				}
				$user['businessName']=$this->input->post('businessName');
				$user['email']=$this->input->post('email');
				$user['usertype']=$this->input->post('usertype');
				$user['additional_profit']=$this->input->post('additional_profit');
				$user['active']=1;
				$user['loginSource'] = 'normal';
				$user['createdDate']=date('YmdHis');
				$user['modifiedDate']=date('YmdHis');
				
				$last_id = $this->user_model->insertsignup($user);
				
				if($userid == ''){
				$firstname = $this->input->post('firstname');
				$email = $this->input->post('email');
				
				$link = base_url() . 'appUser/createUsername/' . $rand;
				
				$url = '<a style="color:rgb(43,170,223)" href="' . $link . '" target="_blank">Create account here</a>';
				
				//// SEND  EMAIL START
				//// GET EMAIL FROM DATABASE
				
				$email_template = $this->email_model->getoneemail('app_user_signup');
				
				//// MESSAGE OF EMAIL
				$messages = $email_template->message;
				
				//// replace strings from message
				$messages = str_replace('{BusinessUserFirstName}', ucfirst($firstname), $messages);
				$messages = str_replace('{MasterAdminName}', 'Hurree', $messages);
				$messages = str_replace('{BusinessUserEmail}', $email, $messages);
				$messages = str_replace('{createUsername}', $url, $messages);
				
				//// FROM EMAIL
				$this->email->from('hello@hurree.co', 'Hurree');
				$this->email->to($email);  //$email
				$this->email->subject($email_template->subject);
				$this->email->message($messages);
				$this->email->send();
				}
				
				echo $last_id;
				
			//}
	}
	
	function delete($id=Null){
		
		$user['user_Id']=$id;
		$user['active']=0;
		$user['modifiedDate']=date('YmdHis');
		
		$this->user_model->insertsignup($user);
		redirect('developer');
	}
	
	public function RandomStringCreateUsername() {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < 20; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	
	public function appUserProfile(){
	
		$userid = $this->uri->segment(3);
	
		$login=$this->administrator_model->login_session();
	
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
	
			$header['url']=$this->uri->segment(1);
			$header['consumer']=2;
			$header['image']=$login['image'];       //// Get Admin Image
			$header['username']=$login['username'];
	
			$data['appUser'] = $this->user_model->getUserRowByUserId($userid);
	
			//echo '<pre>';
			//print_r($data['appUser']);die;
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('appuser_profile',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
}