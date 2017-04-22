<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Organization extends CI_Controller {
	
	
	public function __construct()
	{
		parent::__construct();
	
		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session','pagination','image_lib','email'));
		$this->load->database();
		$this->load->model(array('user_model','email_model','administrator_model','country_model','payment_model','organization_model'));
	$this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	}
	
	function index(){
	
		$this->session->unset_userdata('refer_from');
		$login=$this->administrator_model->login_session();
		
		if($login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			$usertype = 5;
			$data['records']=$this->user_model->getOneUserType($usertype);
			
			$config['base_url'] = base_url().'index.php/organization/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$order_by['order_by']='createdDate';
			$order_by['sequence']="DESC";
			//echo '<pre>';
			//print_r($data['records']);die;
			$data['users']=$this->user_model->getOneUserType($usertype,$page, $config['per_page'],$order_by);
			
		$this->load->view('admin_header',$header);
		$this->load->view('admin_subheader',$header);
		$this->load->view('organization',$data);
		$this->load->view('admin_footer');
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
		
	}
	
	function add(){
	
		
		$login=$this->administrator_model->login_session();
		if($login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			$data['countries']=$this->country_model->get_countries();
			$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]');
			$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|is_unique[users.email]');
			$this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
			$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
			$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
			$this->form_validation->set_rules('amount', 'Amount paid', 'trim|required');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
			$this->form_validation->set_rules('con_password', 'Confirm Password', 'trim|required|matches[password]');
			$this->form_validation->set_rules('phoneNo', 'Phone Number', 'trim|required');
			$this->form_validation->set_rules('country', 'Country', 'required');
			$this->form_validation->set_rules('address', 'Address 1', 'trim|required');
			$this->form_validation->set_rules('town', 'Town', 'trim|required');
			$this->form_validation->set_rules('postcode', 'Postcode', 'trim|required');
			
			if ($this->form_validation->run() == FALSE)
			{
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('organization_form',$data);
			$this->load->view('admin_footer');
			}else{
				//echo 'entry';
				if($_FILES['image']['size']>0)
				{
					// Image upload in full size in profile directory
				
					$uploads_dir = 'upload/profile/full/';
					$tmp_name = $_FILES["image"]["tmp_name"];
					$name = mktime().$_FILES["image"]["name"];
					move_uploaded_file($tmp_name, "$uploads_dir/$name");
				
					// image resize in thumbnail size in thumbnail directory
					$this->load->library('image_lib');
					$config['image_library'] = 'gd2';
					$config['source_image']	= "upload/profile/full/".$name;
					$config['new_image'] = 'upload/profile/medium/' . $name;
					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 300;
					$config['height']	 = 300;
					$this->image_lib->initialize($config);
					$rtuenval= $this->image_lib->resize();
					$this->image_lib->clear();
				
				
					$this->load->library('image_lib');
					$config['image_library'] = 'gd2';
					$config['source_image']	= "upload/profile/full/".$name;
					$config['new_image'] = 'upload/profile/thumbnail/' . $name;
					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 100;
					$config['height']	 = 100;
					$this->image_lib->initialize($config);
					$rtuenval= $this->image_lib->resize();
					$this->image_lib->clear();
				
					$user_img=$name;
				}else{
					$user_img='user.png';
				}
				//$business_name = $this->input->post('business_name');
				$date = date('YmdHis');
				$email= $this->input->post('email');
				$user['user_Id'] = '';
				$user['firstname']=$this->input->post('firstname');
				$user['lastname']=$this->input->post('lastname');
				$user['username']=$this->input->post('username');
				$user['email']=$email;
				$user['businessName']=$this->input->post('business_name');
				$user['password']=md5($this->input->post('password'));
				$user['image']=$user_img;
				$user['createdDate']=$date;
				$user['modifiedDate']=$date;
				$user['active']=1;
				$user['usertype']=$this->input->post('usertype');
				//$user['phone']=$this->input->post('phoneNo');
				//$user['country']=$this->input->post('country');
				//$user['address'] = $this->input->post('address');
				//$user['address2'] = $this->input->post('address2');
				//$user['town'] = $this->input->post('town');
				//$user['postcode'] = $this->input->post('postcode');
				
				//echo '<pre>';
				//print_r($user);die;
				
				$user_id=$this->user_model->insertsignup($user);
				
				//Get Geo code
				$geoCodeAddress = $this->input->post('address').','.$this->input->post('town');
				$location = $this->GetGeoCode($geoCodeAddress);
				if(isset($location) && $location != ""){
					$latitude = $location['latitude'];
					$longitude = $location['longitude'];
				}else{
					$latitude = "";
					$longitude = "";
				}
				
				//Add Branch
				$branch['branch_id']='';
				$branch['userid']=$user_id;
				$branch['email']=$email;
				$branch['businessCategory']='';
				$branch['country']=$this->input->post('country');
				$branch['address']=$this->input->post('address');
				$branch['address2']=$this->input->post('address2');
				$branch['latitude']=$latitude;
				$branch['longitude']=$longitude;
				$branch['town']=$this->input->post('town');
				$branch['postcode']=$this->input->post('postcode');
				$branch['phone']=$this->input->post('phoneNo');
				$branch['website']='';
				$branch['peopleVisit']='';
				$branch['description']='';
				$branch['main_branch']=1;
				$branch['active']=1;
				$branch['createdDate']=$date;
				$branchid = $this->user_model->savebusinessbranch($branch);
				//End Add Branch
				
				//Save amount which is paid by organization in payment_status table
				$amount['payment_id']='';
				$amount['user_id'] = $user_id;
				$amount['purchasedOn'] = $date;
				$amount['currency'] = '&pound;';
				$amount['amount'] = $this->input->post('amount');
				$amount['transationId'] = '';
				$amount['paymentInfo'] = 'Paid by organization';
				$amount['isActive'] = 1;
				$amount['IsDelete'] = 0;
				$amount['createdDate'] = $date;
				
				$last_payment_id=$this->payment_model->savepayment($amount);
				
				$this->session->set_flashdata('success_messege','Organization has been created');
				redirect('organization');
			}
			
		}
		else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	
	}
	
	function profile(){
		
		$userid = $this->uri->segment('3');
		$login=$this->administrator_model->login_session();
		
		if($login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			$data['userDeatail']=$this->user_model->getOneUser($userid);
			$data['userBranch']=$this->user_model->getbusinessbranches($userid);
			
			
			$data['business_users'] = $this->organization_model->getOrgUsers($userid);
			if(count($data['business_users']) > 0){
			foreach($data['business_users'] as $businessid){
				$businessUserId = $businessid->user_Id;
				$businessName = $businessid->businessName;
				$businessCoins = $this->organization_model->orgTotalCoins($businessUserId);
					
				$arr_buss['name'] = $businessName;
				$arr_buss['coins'] = $businessCoins->totalCoins;
					
				$arr_final[]= $arr_buss;
			}
			$data['business_details']= $arr_final;
			}else{
				$data['business_details'] = 0;
			}
			
			$orgCoins = $this->organization_model->orgCoinsUsed($userid);
			$data['orgcoins'] = $orgCoins->totalCoins;
			
			$totalCoins = $this->organization_model->orgTotalCoins($userid);
			//print_r($totalCoins);
			$data['totalCoinsUse'] = $totalCoins->totalCoins;
			$data['createdDate'] = $data['userDeatail']->createdDate;
			//echo $data['userDeatail']->country;die;
			$data['countries'] = $this->country_model->getonecountry('country_id',$data['userBranch'][0]->country);
			//echo '<pre>';
			//print_r($data['business_users']);die;
			
			//Check Invoice sent or not to show buttons Pending Payment
			$data['sendInvoice'] = $this->organization_model->sentInvoice($userid);
			//echo '<pre>';
			//print_r($data['sendInvoice']);die;
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('organization_profile',$data);
			$this->load->view('admin_footer');
			
		}else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}
		
	}
	
	function edit(){
		
		$userid = $this->uri->segment('3');
		
		$login=$this->administrator_model->login_session();
		
		if($login['true']==1 && $login['accesslevel']=='admin')
		{
			
			$refer_from=$_SERVER['HTTP_REFERER'];
			
			$arr=explode('/',$refer_from);
			$ind=count($arr)-1;
			$ind1=count($arr)-2;
			$arr[$ind1];
			if($arr[$ind]==='organization' or $arr[$ind1]==='index'){
				
				$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
				
			}
			
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
		$data['userDeatail']=$this->user_model->getOneUser($userid);
		$data['userBranch']=$this->user_model->getbusinessbranches($userid);
		
		//echo '<pre>';
		//print_r($data);die;
		
		$payment['user_id']= $userid;
		$payment['paymentInfo']= 'Paid by organization';
		$data['amount'] = $this->payment_model->getpayment($payment,'');
		
		$data['countries'] = $this->country_model->get_countries();
		
		$country = $this->country_model->getonecountry('country_id',$data['userBranch'][0]->country);
		foreach($country as $countri){
			$countri['country_id'];
		}
		
		$data['country_id'] = $countri['country_id'];
		//echo '<pre>';
		//print_r($data['countries']);
		//print_r($data['country']);die;
		
		$this->load->view('admin_header',$header);
		$this->load->view('admin_subheader',$header);
		$this->load->view('organization_edit',$data);
		$this->load->view('admin_footer');
		
		}else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}	
		
	}
	
	function update(){
		
		$login=$this->administrator_model->login_session();
		
		if($login['true']==1 && $login['accesslevel']=='admin')
		{
			
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
		
		//$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]');
		//$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|is_unique[users.email]');
		$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
		$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
		$this->form_validation->set_rules('amount', 'Amount paid', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', '');
		if($this->input->post('password') != ''){
		$this->form_validation->set_rules('con_password', 'Confirm Password', 'trim|required|matches[password]');
		}
		$this->form_validation->set_rules('phoneNo', 'Phone Number', 'trim|required');
		$this->form_validation->set_rules('country', 'Country', 'required');
		$this->form_validation->set_rules('address', 'Address 1', 'trim|required');
		$this->form_validation->set_rules('address2', 'Address 2', '');
		$this->form_validation->set_rules('town', 'Town', 'trim|required');
		$this->form_validation->set_rules('postcode', 'Postcode', 'trim|required');
		
		if ($this->form_validation->run() == FALSE)
		{
			
			$userid = $this->input->post('user_id');
			$data['userDeatail']=$this->user_model->getOneUser($userid);
			$data['countries'] = $this->country_model->get_countries();
			
			if($this->input->post('country') != ''){
			$country = $this->country_model->getonecountry('country_id',$this->input->post('country'));
			foreach($country as $countri){
				$countri['country_id'];
			}
			
			$data['country_id'] = $countri['country_id'];
			}else{
				$data['country_id'] = '';
			}
			
			$payment['user_id']= $userid;
			$payment['paymentInfo']= 'Paid by organization';
			$data['amount'] = $this->payment_model->getpayment($payment,'');
			
			//echo '<pre>';
			//print_r($data['userDeatail']);die;
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('organization_edit',$data);
			$this->load->view('admin_footer');
		}else{
			$userid = $this->input->post('user_id');
			if($_FILES['image']['size']>0)
			{
				// Image upload in full size in profile directory
			
				$uploads_dir = 'upload/profile/full/';
				$tmp_name = $_FILES["image"]["tmp_name"];
				$name = mktime().$_FILES["image"]["name"];
				move_uploaded_file($tmp_name, "$uploads_dir/$name");
			
				// image resize in thumbnail size in thumbnail directory
				$this->load->library('image_lib');
				$config['image_library'] = 'gd2';
				$config['source_image']	= "upload/profile/full/".$name;
				$config['new_image'] = 'upload/profile/medium/' . $name;
				$config['maintain_ratio'] = TRUE;
				$config['width']	 = 300;
				$config['height']	 = 300;
				$this->image_lib->initialize($config);
				$rtuenval= $this->image_lib->resize();
				$this->image_lib->clear();
			
			
				$this->load->library('image_lib');
				$config['image_library'] = 'gd2';
				$config['source_image']	= "upload/profile/full/".$name;
				$config['new_image'] = 'upload/profile/thumbnail/' . $name;
				$config['maintain_ratio'] = TRUE;
				$config['width']	 = 100;
				$config['height']	 = 100;
				$this->image_lib->initialize($config);
				$rtuenval= $this->image_lib->resize();
				$this->image_lib->clear();
			
				$user_img=$name;
				$user['image']=$user_img;
				$oneuser=$this->user_model->getOneUser($userid);
			
				
					if($oneuser->image!='user.png')
					{
						$path=getcwd();
						$filepathFull = $path.'/upload/profile/full/'.$oneuser->image;
						$filepathmedium = $path.'/upload/profile/medium/'.$oneuser->image;
						$filepaththumbnail = $path.'/upload/profile/thumbnail/'.$oneuser->image;
						//// UNLINK PREVIOUS Images
						$responce=unlink($filepathFull);
						$responce=unlink($filepathmedium);
						$responce=unlink($filepaththumbnail);
					}
				
			
			}
			
			
			//Update user details
			$user['user_Id']=$this->input->post('user_id');
			$user['email']= $this->input->post('user_email');
			$user['username']=$this->input->post('user_name');
			$user['businessName']=$this->input->post('business_name');
			if($this->input->post('password') != ''){
			$user['password']= md5($this->input->post('password'));
			}
			$user['firstname']=$this->input->post('firstname');
			$user['lastname']=$this->input->post('lastname');
			$user['modifiedDate']=date('YmdHis');
			$user['active']=1;
			$user['usertype']=$this->input->post('usertype');
			
			$user_id=$this->user_model->insertsignup($user);
			
			
			
			//Update Branch details
			
			//Get Geo code
			$geoCodeAddress = $this->input->post('address').','.$this->input->post('town');
			$location = $this->GetGeoCode($geoCodeAddress);
			if(isset($location) && $location != ""){
				$latitude = $location['latitude'];
				$longitude = $location['longitude'];
			}else{
				$latitude = "";
				$longitude = "";
			}
			
			$branch['userid']=$this->input->post('user_id');
			$branch['phone'] = $this->input->post('phoneNo');
			$branch['country'] = $this->input->post('country');
			$branch['address'] = $this->input->post('address');
			$branch['address2'] = $this->input->post('address2');
			$branch['latitude']= $latitude;
			$branch['longitude']= $longitude;
			$branch['town'] = $this->input->post('town');
			$branch['postcode'] = $this->input->post('postcode');
			$branch['modifiedDate']=date('YmdHis');
			
			$this->organization_model->updateBranch($branch);
			
			
			
			//echo '<pre>';
			//print_r($user);die;
			$payment['user_id'] = $this->input->post('user_id');
			$payment['paymentInfo'] = 'Paid by organization';
			$payment_details = $this->payment_model->getpayment($payment,'');
			//echo '<pre>';
			//print_r($payment_details);die;
			
			$payment['payment_id']=$payment_details->payment_id;
			$payment['user_id']=$this->input->post('user_id');
			$payment['purchasedOn']=$payment_details->purchasedOn;
			$payment['amount'] = $this->input->post('amount');
			$payment['currency']='&pound;';
			$payment['transationId']='';
			$payment['paymentInfo']='Paid by organization';
			$payment['isActive']=1;
			$payment['IsDelete']=0;
			$payment['createdDate'] = $payment_details->createdDate;
			
			$this->payment_model->updateSubscription($payment);
			
			$this->session->set_flashdata('success_messege','Organization has been updated successfully');
			//redirect('organization/profile/'.$this->input->post('user_id'));
			redirect($this->session->userdata('refer_from'));
			
		}
		
		
		}else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}
		
	}
	
	function delete(){
		
		$userid = $this->uri->segment('3');
		$userArray = '';
		$users = $this->organization_model->getOrgAndBusinessUsers($userid);
		
		foreach($users as $user){
			
			$userArray .= $user->user_Id.',';
		}
		$userids = rtrim($userArray,',');
		
		$result = $this->organization_model->delete($userids);
		
		if($result == 1){
			$this->session->set_flashdata('success_messege','Organization has been deleted successfully');
			redirect('organization');
		}else{
			$this->session->set_flashdata('success_messege','Server is not responding, please try again');
			//redirect('organization');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	
	# Function Action Get Geo Code
	/****************************************************************************************************************************/
	public function GetGeoCode($newAddress){
		$address=str_replace(" ","+",$newAddress);
		$geocode=@file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$address);
		$output= json_decode($geocode);
		if(isset($output) && $output != ""){
	
			if($output->status == "OK"){
				$lat = $output->results[0]->geometry->location->lat;
				$long = $output->results[0]->geometry->location->lng;
				$a = array('latitude', 'longitude');
				$b = array($lat, $long);
				$location  = array_combine($a, $b);
				return $location;
	
			}else{
				$lat = "";
				$long = "";
				$a = array('latitude', 'longitude');
				$b = array($lat, $long);
				$location  = array_combine($a, $b);
				return $location;
			}
		}else{
			$lat = "";
			$long = "";
			$a = array('latitude', 'longitude');
			$b = array($lat, $long);
			$location  = array_combine($a, $b);
			return $location;
		}
	
	}
	
	function invoice(){
		
		$userid = $this->uri->segment('3');
		
		$login=$this->administrator_model->login_session();
		
		if($login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
		
		$data['business_users'] = $this->organization_model->getOrgUsers($userid);
		
		foreach($data['business_users'] as $businessid){
			$businessUserId = $businessid->user_Id;
			$businessName = $businessid->businessName;
			$businessUsername = $businessid->username;
			$businessCoins = $this->organization_model->orgTotalCoins($businessUserId);
			
			$arr_buss['name'] = $businessName;
			$arr_buss['username'] = $businessUsername;
			$arr_buss['coins'] = $businessCoins->totalCoins;
			
			$arr_final[]= $arr_buss;
		}
		$data['business_details']= $arr_final;
		
		//Get only Org coins which are used
		$orgCoins = $this->organization_model->orgCoinsUsed($userid);
		$data['orgcoins'] = $orgCoins->totalCoins;
		
		//Get total coins of org as well as their accounts
		$totalCoins = $this->organization_model->orgTotalCoins($userid);
		$data['totalcoins'] = $totalCoins->totalCoins;
		
		$this->load->view('admin_header',$header);
		$this->load->view('admin_subheader',$header);
		$this->load->view('organization_invoice',$data);
		$this->load->view('admin_footer');
		
		}
		else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function sendInvoice(){
		
		$orgid = $this->input->post('orgid');
		$org = $this->user_model->getOneUser($orgid);
		
		$OrgUsers = $this->organization_model->getOrgUsers($orgid);
		//echo '<pre>';
		//print_r($OrgUsers);die;
		
		$update['userid'] = $orgid;
		$update['sendInvoice'] = 1;
		$this->organization_model->updateUserCoins($update);
		
		foreach($OrgUsers as $branch){
			
			$update['userid'] = $branch->user_Id;
			$update['sendInvoice'] = 1;
			$this->organization_model->updateUserCoins($update);
		}
		
		$date = date('YmdHis');
		$insert['userid'] = $orgid;
		$insert['status'] = 0;
		$insert['createdDate'] = $date;
		$insert['modifiedDate'] = $date;
		$this->organization_model->insertPaymentStatus($insert);
		//echo $this->db->last_query();
		//die;
		//echo '<pre>';
		//print_r($org);die;
		$username = $org->username;
		$email = $org->email;
		
		$totalcoins = $this->input->post('totalcoins');
		$price = $this->input->post('price');
		
		$count = $this->input->post('count');
		for($i=1;$i<=$count;$i++){
		
		$branch	= $this->input->post('Branch'.$i);
		$coins	= $this->input->post('coins'.$i);
		$branch = array(
			$i => 'Branch '.$i.' (@'.$branch.'): '.$coins.' Coins'		
		);
		//print_r($branch);
		
		$message='';
		foreach($branch as $brch)
		{
			if($message=='')
			{
				$message=$brch.'</br>';
			} else{
				$message .= $message.$brch;
			}
		}
		
		$message;
		
		$this->emailConfig();
		
		$email_template=$this->email_model->getoneemail('organization_invoice');
		
		//echo $this->db->last_query();die;
		
		$messages= $email_template->message;
		
		//print_r($messages);die;
		
		$hurree_image= 'http://54.254.239.126/hurree/assets/template/hurree/images/app-icon.png';
		$appstore='http://54.254.239.126/hurree_images/appstore.gif';
		$googleplay='http://54.254.239.126/hurree_images/googleplay.jpg';
		
		//// replace strings from message
		$messages=str_replace('{Username}', ucfirst($username), $messages);
		$messages=str_replace('{Msg}', $message, $messages);
		$messages=str_replace('{Total Coins}', $totalcoins, $messages);
		$messages=str_replace('{Amount}', '&pound;'.$price, $messages);
		$messages=str_replace('{Hurree_Image}', $hurree_image, $messages);
		$messages=str_replace('{App_Store_Image}', $appstore, $messages);
		$messages=str_replace('{Google_Image}', $googleplay, $messages);
		
		
		//// Email to user
		$this->email->from($email_template->from_email, 'Hurree');
		$this->email->to($email);
		$this->email->subject($email_template->subject);
		$this->email->message($messages);
		$this->email->send();    ////  EMAIL SEND
		
		//Email to Billing@Hurree.co
		$this->email->from($email_template->from_email, 'Hurree');
		$this->email->to('Billing@Hurree.co');
		$this->email->subject($email_template->subject);
		$this->email->message($messages);
		$this->email->send();    ////  EMAIL SEND
		
		redirect('organization/invoice/'.$orgid);
		
		}
		
	}
	
	function emailConfig(){
	
		$this->load->library('email');   //// LOAD LIBRARY
	
		$config['protocol']     = 'smtp';
		$config['smtp_host']    = 'auth.smtp.1and1.co.uk';
		$config['smtp_port']    = '587';
		$config['smtp_timeout'] = '7';
		$config['smtp_user']    = 'support@hurree.co';
		$config['smtp_pass']    = 'aaron8164';
		$config['charset']      = 'utf-8';
		$config['newline']      = "\r\n";
		$config['mailtype']     = 'html'; // or html
			
		$this->email->initialize($config);
	}
	
	function paymentReceived(){
		$userid = $this->input->post('userid');
		
		$sendInvoice = $this->organization_model->sentInvoice($userid);
		//echo '<pre>';
		//print_r($sendInvoice);
		//echo $sendInvoice->org_payment_status_id;
		
		$update['org_payment_status_id'] = $sendInvoice->org_payment_status_id;
		$update['status'] = 1;
		$update['modifiedDate'] = date('YmdHis');
		//print_r($update);die;
		
		$this->organization_model->updatePaymentStatus($update);
		//echo $this->db->last_query();
		
		 
	}
}