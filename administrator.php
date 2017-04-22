<?php  //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Administrator extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
			
		$this->load->model(array('administrator_model'));
		$this->load->library(array('session','form_validation'));
		//$this->load->library('image_lib');
		$this->load->database();
		$this->load->helper(array('form', 'url'));
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
			
	}
	
	
	function index()
	{
		/* echo 'asdsad'; die; */
		
		$data='';
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]');
		
		if($this->form_validation->run() == FALSE)
		{
			$this->form_validation->set_error_delimiters('<div style="color:red; ">', '</div>');
			
			$header['subheader']='';
			$this->load->view('header',$header);
			$this->load->view('admin_login',$data);
			$this->load->view('footer');
		}else
		{
			$username=$this->input->post('username');
			
			$userdetails=$this->checkusername($username);
			//echo '<pre>'; print_r($userdetails); die; 
			if(count($userdetails)>0)
			{
				$password=$this->input->post('password');
				$login=$this->check_login($username, $password);
				if(count($login)>0)
				{
					
					if($login->accesslevel=='admin')
					{
						$sess_admin= (object) array(
										"ad_userid"=>$login->user_Id,
										"ad_username"=>$login->username,
										"ad_active"=>$login->active,
										"ad_usertype"=>$login->usertype,
										"ad_accesslevel"=>$login->accesslevel,
										"ad_firstname"=>$login->firstname,
										"ad_lastname"=>$login->lastname,
										"ad_image"=>$login->image
								
						);
						
						$this->session->set_userdata('sess_admin',$sess_admin);
						redirect('profile');
					}else{
						$this->session->set_flashdata('alert_message','Unauthorizes Username');
						redirect('administrator');
					}
				}else{
					$this->session->set_flashdata('alert_message','Incorrect Username/Password');
					redirect('administrator');
				}
			}else{
				$this->session->set_flashdata('alert_message','Username Does Not Exits');
				redirect('administrator');
			} 
		}
		
		
	}
	
	function adminLogin()
	{
		$arr_user=array();
		$username=$_POST['username'];
		$password=$_POST['password'];
		
		$userDetails= $this->checkusername($username);
		if(count($userDetails)>0)
		{
			$userLogin= $this->check_login($username,$password);
			if(count($userLogin)>0)
			{
				if($userLogin->accesslevel=='admin'&& $userLogin->usertype==3)
				{
					$sess_admin= (object) array(
							"ad_userid"=>$userLogin->user_Id,
							"ad_username"=>$userLogin->username,
							"ad_active"=>$userLogin->active,
							"ad_usertype"=>$userLogin->usertype,
							"ad_accesslevel"=>$userLogin->accesslevel,
							"ad_firstname"=>$userLogin->firstname,
							"ad_lastname"=>$userLogin->lastname,
							"ad_image"=>$userLogin->image
					
					);
					
					$this->session->set_userdata('sess_admin',$sess_admin);
					
					$arr_user[]="";
					$arr_user[]="1";
				}else{
					$arr_user[]="Unauthorized User";
					$arr_user[]="0";
				}
			}else{
				$arr_user[]="Incorrect Username Or Password";
				$arr_user[]="0";
			}
		}else{
			$arr_user[]="Username is not registered with us";
			$arr_user[]="0";
		}
		
		/* echo '<pre>'; print_r($arr_user); die; */
		echo json_encode($arr_user);
		
	}
	
	function checkusername($username)
	{
		$user['username']=$username; 
		$user['active']=1;
		$user_details=$this->administrator_model->check_username($user);
		return $user_details;  
	}
	
	function check_login($username=NULL, $password=NULL)
	{
		if($username==''|| $password=='')
		{
			return false;
		}else{
				
			$arr_user['username']=$username;
			$arr_user['password']=md5($password);
			$arr_user['email']=$username; 
			$user_details=$this->administrator_model->check_login($arr_user);
			
			return $user_details; 
		}
		
	}
	
	function logout(){
		
		$this->session->sess_destroy();
		redirect('administrator');
	}
	
}
	