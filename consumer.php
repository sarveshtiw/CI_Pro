<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Consumer extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session','pagination','image_lib','email'));
		$this->load->database();
		$this->load->model(array('user_model','email_model','administrator_model','country_model','score_model','beacon_model','payment_model'));
            $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	}

	function index($user=NULL)
	{
		$this->session->unset_userdata('refer_from');
		//// Get Loggin Session
		$header['sess_details']=$this->session->userdata('sess_admin');

		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			if($this->uri->segment(2)=='Business')
			{
				$usertype=6;
			}else{
				$usertype=1;
			}

			$data['records']=$this->user_model->getOneUserType($usertype);      //// Count Same User Type
			$config['base_url'] = base_url().'index.php/consumer/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$order_by['order_by']='createdDate';
			$order_by['sequence']="DESC";

			$data['users']=$this->user_model->getOneUserType($usertype,$page, $config['per_page'],$order_by);    //// Get List Of Same User Type
			///echo $this->db->last_query(); die;
			$data['type'] = 'consumer';

			$header['url']=$this->uri->segment(1);
			$header['consumer']=1;
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;           //// Get Admin Username

			//// Load View Pages
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('consumer',$data);
			$this->load->view('admin_footer');
		}else
		{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('administrator');
		}

	}

	function form()
	{
		$id = $this->uri->segment(3);

		//echo $_SERVER['HTTP_REFERER']; die;
		//$refer_from = $this->input->post('refer_from');
		//$this->session->unset_userdata('refer_from');
		$refer_from=$_SERVER['HTTP_REFERER'];

		$arr=explode('/',$refer_from);
		$ind=count($arr)-1;
		$ind1=count($arr)-2;
		 $arr[$ind1];
		if($arr[$ind]==='consumer' or $arr[$ind1]==='index'){$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);

		}
		//$this->session->unset_userdata('refer_from');

		$header['sess_details']=$this->session->userdata('sess_admin');

		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);

			$data['usertype']='1';
			$data['id']=$id;


			$data['username']='';
			$data['firstname']='';
			$data['lastname']='';
			$data['email']='';
			$data['image']='';

			$data['editable']='';
			//$data['username']='';

			if($id!='')
			{
				$oneuser=$this->user_model->getOneUser($id);
				$data['username']=$oneuser->username;
				$data['email']=$oneuser->email;
				$data['firstname']=$oneuser->firstname;
				$data['lastname']=$oneuser->lastname;
				$data['username']=$oneuser->username;
				$data['image']=$oneuser->image;
				$data['editable']=$oneuser->editable;

				$data['id']='/'.$id;

			}

			$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
			$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');

			if($id == '')
			{
				$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|is_unique[users.email]');
				$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]');

			}

			if(($id == '') || ($id != '' && $this->input->post('password') != ''))
			{
				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
				$this->form_validation->set_rules('con_password', 'Confirm Password', 'trim|required|matches[password]');
			}

			$admin=$this->user_model->getOneUser($header['sess_details']->ad_userid);   //// Get Admin Details

			$header['url']=$this->uri->segment(1);
			$header['consumer']=1;
			$header['image']=$admin->image;       //// Get Admin Image
			$header['username']=$admin->username;           //// Get Admin Username

			if ($this->form_validation->run() == FALSE)
			{
				if($_POST)
				{
					$data['firstname']=$this->input->post('firstname');
					$data['lastname']=$this->input->post('lastname');
				}
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('user_form',$data);
				$this->load->view('admin_footer');
			}else
			{
				/* echo 'viv'; die; */
				if($_FILES['image']['size']>0)
				{
					// Image upload in full size in profile directory
					$uploads_dir = 'upload/profile/full/';
					$tmp_name = $_FILES["image"]["tmp_name"];
					$name = mktime().$_FILES["image"]["name"];
					move_uploaded_file($tmp_name, "$uploads_dir/$name");

					// image resize in medium size in medium directory
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

					//Image resize in thumbnail size in thumbnail directory
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

					if($id!='')
					{
						/* echo $oneuser->image; die; */
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

				}else{

					if($id=='')
					{
						$user_img='user.png';
					}else
					{
						$user_img=$this->input->post('img');
					}
				}

				$user['user_Id'] = $id;
				$user['username'] = $this->input->post('username');
				$user['email'] = $this->input->post('email');
				if($id == ''){
				$user['password'] = md5($this->input->post('password'));
				}
				else if($id != '' && $this->input->post('password') != ''){
					$user['password'] = md5($this->input->post('password'));
				}
				$user['firstname'] = $this->input->post('firstname');
				$user['lastname'] = $this->input->post('lastname');
				$user['image'] = $user_img;

				if($id == '')
				{
					$user['createdDate']=date('YmdHis');
				}else{
					$user['modifiedDate']=date('YmdHis');
				}
				$user['active'] = 1;
				$user['usertype'] = $this->input->post('usertype');
				//$user['username']=$this->input->post('username');

				$user_id = $this->user_model->insertsignup($user);
				if($id == '')
				{
				$coins = array(
								'userid' 	=> $user_id,
								'coins'		=> '100'
							);
				$this->score_model->signupCoins($coins);

				$userCoins = array(
						'userid' 		=> $user_id,
						'coins'  		=> 100,
						'coins_type'	=> 8,
						'game_id'		=> 0,
						'businessid' 	=> 0,
						'actionType' 	=> 'add',
						'createdDate' 	=> date('YmdHis')

				);

				$this->score_model->insertCoins($userCoins);
				}

				if($id=='')
				{
					$this->session->set_flashdata('success_messege','Consumer has been created');
				}else{
					$this->session->set_flashdata('success_messege','Consumer has been updated sucessfully');
				}
				//redirect('consumer');
				redirect($this->session->userdata('refer_from'));
			}
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function delete()
	{
		$userid = $this->uri->segment('3');

		$user = $this->user_model->getOneUser($userid);

		$save['user_Id']=$userid;
		$save['active']=0;
		$save['modifiedDate']=date('YmdHis');
		$this->user_model->insertsignup($save);

		if($user->usertype == 2){

			$subs = $this->payment_model->getoneUserSubscription($userid);

			$subscriptionId = $subs->payment_id;
			$data = array(
					'payment_id' => $subscriptionId,
					'isActive'	 => '0'
			);
			$this->payment_model->delete($data);
		}
		$user = $this->user_model->getOneUser($userid);
		//echo '<pre>'; print_r($user); die;
		if($user->usertype == 1){
		$this->session->set_flashdata('success_messege','Consumer has been deleted successfully');
		//redirect('consumer');
		redirect($_SERVER['HTTP_REFERER']);
		}
		elseif ($user->usertype == 2){
			$this->session->set_flashdata('success_messege','Business user has been deleted successfully');
			//redirect('consumer/business');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}


	function business()
	{
		$this->session->unset_userdata('refer_from');
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$usertype=6;
			$activeLogin="1";
			$data['records']=$this->user_model->getOneUserType($usertype,'','','','', $activeLogin);      //// Count Same User Type
			$config['base_url'] = base_url().'index.php/consumer/business/';
			$config['total_rows'] = count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;

			$order_by['order_by']="createdDate";
			$order_by['sequence']="DESC";

			$data['users']=$this->user_model->getOneUserType($usertype,$page, $config['per_page'],$order_by,'',$activeLogin);    //// Get List Of Same User Type
			/* echo $this->db->last_query();
			echo '<pre>'; print_r($data['users']); die; */

			$header['url']=$this->uri->segment(1);
			$header['consumer']=2;  /// for Business
			$header['image']=$login['image'];       //// Get Admin Image
			$header['username']=$login['username'];           //// Get Admin Username
			$data['type'] = 'business';
			//// Load View Pages
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('consumer',$data);
			$this->load->view('admin_footer');
		}else
		{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('administrator');
		}
	}

	function business_form()
	{
		$login=$this->administrator_model->login_session();

		$refer_from=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';

		$arr=explode('/',$refer_from);
		$ind=count($arr)-1;
		$ind1=count($arr)-2;
		$arr[$ind1];

		if($arr[$ind1]==='consumer' or $arr[$ind1]==='business' or $arr[$ind]==='business'){
			$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);

		}
		$last_insert_id  = '';
		$id = $this->uri->segment(3);
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$header['url']=$this->uri->segment(1);
			//$data['branchCount']=1;
			if($id == ''){
				$this->load->model('user_model');
				$last_insert_user_id = $this->user_model->getMaxUserRow();
				$last_insert_id = rand();
				if(isset($last_insert_user_id->user_Id)){
					$last_insert_id = $last_insert_user_id->user_Id + 1;
				}
			}
			$data['usertype']='6';
			$data['id']=$id;

			$data['username']='';
			$data['firstname']='';
			$data['lastname']='';
			$data['businessName'] = '';
			$data['email']='';
			$data['image']='';
			$data['editable']='';
			$data['assignBeacons'] = array();
			if(@$_GET['org'] != ''){
				$data['orgId'] = $_GET['org'];
			}else{
				$data['orgId']= '';
			}

			if($id!='')
			{
				$oneuser=$this->user_model->getOneUser($id);
				$data['username']=$oneuser->username;
				$data['email']=$oneuser->email;
				$data['firstname']=$oneuser->firstname;
				$data['lastname']=$oneuser->lastname;
				$data['businessName']=$oneuser->businessName;
				$data['username']=$oneuser->username;
				$data['image']=$oneuser->image;

				$data['id']='/'.$id;
				$data['editable']=$oneuser->editable;

				$data['businessBranch']=$this->user_model->getbusinessbranches($id);
				$last_insert_id = $data['businessBranch'][0]->businessId;
				// Get Beacons details
				$where = array(
						"beaconUserConnection.userId"=> $id
				);
				$data['assignBeacons'] = $this->beacon_model->getBeacon('beacons.*',$where, "1" );
// 				echo '<pre>'; print_r($data['assignBeacons']); die;
			}

			$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
			$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
			if(isset($oneuser->username) && $oneuser->username == $this->input->post('username')){
				$this->form_validation->set_rules('username', 'Username', 'trim|required');
			}else{
				$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]');
			}

			if($id=='')
			{
				$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|is_unique[users.email]');

				$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[users.username]');

				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
				$this->form_validation->set_rules('con_password', 'Confirm Password', 'trim|required|matches[password]');
			}

			$admin=$this->user_model->getOneUser($login['userid']);   //// Get Admin Details

			$header['url']=$this->uri->segment(1);
			$header['consumer']=2;
			$header['image']=$admin->image;       //// Get Admin Image
			$header['username']=$admin->username;           //// Get Admin Username

			$beaconId='';
			$v= 0;
			$arr_beacon = array();
			if(count($data['assignBeacons'])> 0)
			{
				foreach($data['assignBeacons'] as $bb)
				{
					if($v > 0)
					{
						$beaconId .=",";
					}
					$beaconId .= $bb->beaconId;
					$v++;
					$arr_beacon[] = $bb->beaconId;
				}
			}

			$data['allBeacons']= $this->beacon_model->getUnAssignBeacons('',$beaconId);
			//echo $this->db->last_query(); die;
			$data['selectedBeacon'] = $arr_beacon;
			if ($this->form_validation->run() == FALSE)
			{
				$data['business_category']=$this->user_model->getCategory();           //// Get List of Business Categories
				$data['countries']=$this->country_model->get_countries();              //// Get List of Country
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('business_form',$data);
				$this->load->view('admin_footer');
			}else
			{

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

					if($id!='')
					{
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

				}else{

					if($id=='')
					{
						$user_img='user.png';
					}else
					{
						$user_img=$this->input->post('img');
					}
				}
				/* echo '<pre>'; print_r($_POST); die; */
				$date = date('YmdHis');
				$email= $this->input->post('email');

				$user['user_Id']=$id;
				$user['firstname']=$this->input->post('firstname');
				$user['lastname']=$this->input->post('lastname');
				$user['username']=$this->input->post('username');
				$user['email']=$email;
				$user['businessId'] = $last_insert_id;
				$user['businessName']=$this->input->post('businessname');

				$password = $this->input->post('con_password');
				if(!empty($password)){
					$user['password']=md5($this->input->post('password'));
				}

				foreach($_POST['country'] as $key=>$val){
					$user['country']=$_POST['country'][$key];
				}

				//$user['country'] = $this->input->post('country');
				$user['image']=$user_img;
				$user['organizationId'] = $data['orgId'];
				if($id=='')
				{
					$user['createdDate']=$date;
				}else{
					$user['modifiedDate']=$date;
				}
				$user['active']=1;
				$user['usertype']=$this->input->post('usertype');

				//echo '<pre>'; print_r($user);die;
				$user_id=$this->user_model->insertsignup($user);
				//$user_id=3;

				$v=0;
				foreach($_POST['phoneNo'] as $key=>$val)
				{
					//echo $key.'/'.$val.'</br>';

					//Get Geo code
					$geoCodeAddress = $_POST['address'][$key].','.$_POST['town'][$key];
					$location = $this->GetGeoCode($geoCodeAddress);
					if(isset($location) && $location != ""){
						$latitude = $location['latitude'];
						$longitude = $location['longitude'];
					}else{
						$latitude = "";
						$longitude = "";
					}

					$v==0?$main=1:$main=0;
					$date = date('YmdHis');

					$branch['branch_id']=$_POST['branch_id'][$key];
					$branch['userid']=$user_id;
					$branch['businessId'] = $last_insert_id;
					$branch['store_name'] = $this->input->post('businessname');
					$branch['email']=$email;
					$branch['businessCategory']=$_POST['businessCategory'][$key];
					$branch['country']=$_POST['country'][$key];
					$branch['address']=$_POST['address'][$key];
					$branch['address2']=$_POST['address2'][$key];
					$branch['latitude'] = $latitude;
					$branch['longitude'] = $longitude;
					$branch['town']=$_POST['town'][$key];
					$branch['postcode']=$_POST['postcode'][$key];
					$branch['phone']=$_POST['phoneNo'][$key];
					$branch['website']=$_POST['website'][$key];
					$branch['peopleVisit']=$_POST['no_of_visit'][$key];
					$branch['description']=$_POST['description'][$key];
					$branch['main_branch']=$main;
					$branch['active']=1;
					$branch['coinDate']=$date;
					$branch['createdDate']=$date;


					$branchid = $this->user_model->savebusinessbranch($branch);
					$v++;

					if($id=='')
					{
						$userCoins = array(

								'userid' 		=> $user_id,
								'coins'  		=> 1250,
								'coins_type'	=> 8,
								'game_id'		=> 0,
								'businessid' 	=> $branchid,
								'actionType' 	=> 'add',
								'createdDate' 	=> date('YmdHis')

						);
						$this->score_model->insertCoins($userCoins);

						$coins = array(

								'userid' 	=> $user_id,
								'coins'		=> '1250',
								'branchid'	=> $branchid
						);
						$this->score_model->signupCoins($coins);


						$business_arr = array(
								'businessId' => $last_insert_id,
								'businessName' => $user['businessName'],
								'country' => $user['country']
						);
						$this->user_model->saveBusinessId($business_arr);

						$arr_package['package_id'] = 1;
						$package = $this->subscription_model->getpackageDetails('*', $arr_package);

						$date = date('YmdHis');
            $userPackage['user_pro_id'] = '';
            $userPackage['user_id'] = $user_id;
            $userPackage['businessId'] = $last_insert_id;
            $userPackage['totalCoins'] = $package->coins;
            $userPackage['totalBeacons'] = $package->beacons;
            $userPackage['totalCampaigns'] = $package->campaigns;
            $userPackage['totalGeoFence'] = $package->geoFence;
            $userPackage['totalIndividualCampaigns'] = $package->individual_campaigns;
            $userPackage['totalLocations'] = $totalLocations;
            $userPackage['createdDate'] = $date;
            $userPackage['modifiedDate'] = $date;

            $last_package_id = $this->user_model->savePackage($userPackage);
					}
				}

				/* Save Beacon Connections*/
				$allBeacon = $this->input->post('beacons');
				foreach( $allBeacon  as $oneBeacon)
				{
					// Initiliza and reset variables
					$where = '';
					$connectionId = "";
					$beacon = array();
					// Check this beacon is already assign to this user or not
					$where = array(
							"beaconUserConnection.beaconId" =>$oneBeacon,
							"beaconUserConnection.userId" => $user_id,
							"beaconUserConnection.isActive" => 1,
							"beaconUserConnection.isDelete" => 0
					);

					$this->beacon_model->deleteBeaconUserConnection($where);

// 					$oneAssignBeacon = $this->beacon_model->getBeacon('beacons.beaconId', $where);

// 					if(count($oneAssignBeacon )>0)
// 					{
						//$connectionId = $oneAssignBeacon->beaconId;
// 					}

					$beacon['connectionId'] = $connectionId;
					$beacon['userId'] = $user_id;
					$beacon['beaconId'] = $oneBeacon;
					$beacon['isActive'] = "1";

					if($connectionId == "")
					{
						$beacon['createdDate'] = date('YmdHis');
					} else {
						$beacon['modifiedDate'] = date('YmdHis');
					}

					$this->beacon_model->savebeaconUserConnection($beacon);
				}



				$this->session->set_flashdata("success_messege","Business user has been created Sucessfully");
				//redirect('consumer/business');  /// id is userid
				redirect($this->session->userdata('refer_from'));
			}
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function business_details($userid='')
	{
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$data='';
			$data['id']="";
			$data['email']='';
			$data['phone']='';
			$data['business_category']='';

			$admin=$this->user_model->getOneUser($login['userid']);   //// Get Admin Details

			$header['url']=$this->uri->segment(1);
			$header['consumer']=2;
			$header['image']=$admin->image;       //// Get Admin Image
			$header['username']=$admin->username;


			$admin=$this->user_model->getOneUser($login['userid']);   //// Get Admin Details

			$data['business_category']=$this->user_model->getCategory();           //// Get List of Business Categories
			$data['countries']=$this->country_model->get_countries();              //// Get List of Country



			if ($this->form_validation->run() == FALSE)
			{
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				/* echo $data['id'];
				echo '<pre>'; print_r($data); die; */
				$this->load->view('business_details',$data);
				$this->load->view('admin_footer');
			}else{

			}
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('administrator');
		}
	}

	function businessprofile(){

		$userid = $this->uri->segment(3);

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$header['url']=$this->uri->segment(1);
			$header['consumer']=2;
			$header['image']=$login['image'];       //// Get Admin Image
			$header['username']=$login['username'];

			$data['businessUser'] = $this->user_model->getonebusiness($userid);
			$data['branches'] = $this->user_model->getbusinessbranches($userid);

			//echo '<pre>';
			//print_r($data['branches']);die;
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('business_profile',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}


	function getBranch()
	{
		$data['business_category']=$this->user_model->getCategory();           //// Get List of Business Categories
		$data['countries']=$this->country_model->get_countries();              //// Get List of Country

		$branchCount = $_POST['branchCount'];
		$data['branchNo']=$branchCount+1;
		$data['businessCategory'] ='';
		$data['country'] ='';
		$data['address'] ="";
		$data['address2'] ="";
		$data['town'] ="";
		$data['postcode'] ="";
		$data['website'] ="";
		$data['peopleVisit'] ="";
		$data['description'] ="";
		$data['branch_id'] ="";

		$data['phoneNo']='';
		$this->load->view('business_branch',$data);
	}

	function changeCustomerloginStatus()
	{
		$userid=$_POST['userid'];
		$action=$_POST['action'];

		$login['userid']=$userid;
		$getDetails=$this->user_model->getLoginStatus('*',$login);

		if(count($getDetails)>0)
		{
			$arr_user['loginstatus_id']=$getDetails->loginstatus_id;
		}else{
			$arr_user['loginstatus_id']='';
		}
		$arr_user['userid']=$userid;
		if($action=='inactive')
		{
			$arr_user['cancel']=1;
			$mail='Inactive';
		}else if($action=='active')
		{
			$arr_user['cancel']=0;
			$mail='Active';
		}elseif ($action=='hold')
		{
			$arr_user['cancel']=1;
			$arr_user['hold']=1;
			$mail='Hold';
		}elseif($action=='unhold')
		{
			$arr_user['cancel']=0;
			$arr_user['hold']=0;
			$mail='';
		}
		$arr_user['createdDate']=date('YmdHis');

		$actionStatus= $this->user_model->saveUserLoginStatus($arr_user);

		if($mail!='')
		{

			//// SEND  EMAIL START
			$this->emailConfig();   //Get configuration of email
			//// GET EMAIL FROM DATABASE

			$email_template=$this->email_model->getoneemail($mail);

			/* $config['mailtype'] = 'html';    //// ENABLE HTML
			$this->email->initialize($config); */
			//// MESSAGE OF EMAIL
			$messages= $email_template->message;


			$hurree_image= 'http://54.254.239.126/hurree/assets/template/hurree/images/app-icon.png';
			$appstore='http://54.254.239.126/hurree_images/appstore.gif';
			$googleplay='http://54.254.239.126/hurree_images/googleplay.jpg';

			/* Get Username */
			$where['user_Id']=$userid;
			$userDetails= $this->user_model->getOneUserDetails($where, '*');
			$username=$userDetails->username;
			//// replace strings from message
			$messages=str_replace('{Username}', ucfirst($username), $messages);

			$messages=str_replace('{Hurree_Image}', $hurree_image, $messages);
			$messages=str_replace('{App_Store_Image}', $appstore, $messages);
			$messages=str_replace('{Google_Image}', $googleplay, $messages);

			//// Email to user
			$this->email->from($email_template->from_email, 'Hurree');
			$this->email->to($userDetails->email);
			$this->email->subject($email_template->subject);
			$this->email->message($messages);
			$this->email->send();    ////  EMAIL SEND
		}
		echo $actionStatus;

	}
	/*In additional , When you click on Unhold button, it will turn to into normal button but account wil be still Inactive..   */

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

}
?>
