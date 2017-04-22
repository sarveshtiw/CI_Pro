<?php  //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Profile extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('form');

		$this->load->model(array('country_model','user_model','administrator_model'));
		$this->load->library('session');
		$this->load->library('image_lib');
		//$this->load->model('profile_model');
		$this->load->library('form_validation');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');

	}


	function index()
	{
		$data='';

		$login=$this->administrator_model->login_session();
		//echo '<pre>'; print_r($login); die;

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('profile',$data);
			$this->load->view('admin_footer');
		}
		else
		{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function account()
	{
		if($this->session->userdata('logged_in'))
		{
			$this->load->model('profile_model');
			$data['query_result'] = $this->profile_model->userInformation();
			$this->load->view('header');
			$this->load->view('subheader');
			$this->load->view('profile', $data);
			$this->load->view('footer');

		}else{
			$this->session->set_flashdata('error_message','You must login First');
			redirect('home');
		}

	}

	function editAccount($id=NULL)
	{
		$data='';

		//
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];



			$this->form_validation->set_rules('firstName', 'Firstname', 'trim|required');
			$this->form_validation->set_rules('firstName', 'Lastname', 'trim|required');
			if($this->input->post('password')!='')
			{
				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]');
				$this->form_validation->set_rules('con_password', 'Re Password', 'trim|required|matches[password]');
			}

			if($this->form_validation->run() == FALSE)
			{
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('edit_account', $data);
				$this->load->view('admin_footer');
			}else
			{

				if($_FILES['image']['size']>0){
					// Image upload in full size in profile directory

					$uploads_dir = 'upload/profile/full/';

					$tmp_name = $_FILES["image"]["tmp_name"];
					$name = mktime().$_FILES["image"]["name"];
					move_uploaded_file($tmp_name, "$uploads_dir/$name");

					// image resize in thumbnail size in thumbnail directory

					$config['image_library'] = 'gd2';
					$config['source_image']	= "upload/profile/full/".$name;
					$config['new_image'] = 'upload/profile/medium/' . $name;

					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 200;
					$config['height']	 = 250;
					$this->image_lib->initialize($config);
					$rtuenval= $this->image_lib->resize();
					$this->image_lib->clear();

					$config['image_library'] = 'gd2';
					$config['source_image']	= "upload/profile/medium/".$name;
					$config['new_image'] = 'upload/profile/thumbnail/' . $name;

					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 40;
					$config['height']	 = 45;
					$this->image_lib->initialize($config);
					$rtuenval= $this->image_lib->resize();
					$this->image_lib->clear();

					$user_img=$name;

					//// GET OLD IMAGE NAME
					$old_image=$this->input->post('img');
					//// GET PATH OF DIRECTORY
					$path=getcwd();
					//// GET PATH OF IMAGE IN DIRECTROY
					//$imagepath=$path.'/uploads/image/profile/'.$old_image;
					//$thumb_imagepath=$path.'/uploads/image/thumbnail/'.$old_image;

					//// UNLINK IMAGE
					//$responce = unlink($imagepath);
					//$responce = unlink($thumb_imagepath);

					// Image Upload End

				}else{

					$user_img=$this->input->post('img');
				}

				$save['user_Id']=$this->input->post('user_id');
				if($this->input->post('password')!='')
				{
					$save['password']=md5($this->input->post('password'));
				}
				$save['firstname']=$this->input->post('firstName');
				$save['lastname']=$this->input->post('lastName');
				$save['modifiedDate']=date('YmdHis');
				$save['image']=$user_img;

				$this->user_model->insertsignup($save);
				$this->session->set_flashdata('success_messege','Profile Has Been Updated Sucessfully');
				redirect('profile');

			}

			/* $admin=$this->user_model->getOneUser($id);


		$data = array(

				'userid' => $admin->user_Id,
				'username' => $admin->username,
				'email' => $admin->email,
				'firstname' => $admin->firstname,
				'lastname' => $admin->lastname,
				'image' => 'upload/profile/thumbnail/'.$admin->image
		); */
		//print_r($data);die;
		/* $this->load->view('admin_header',$header);
		$this->load->view('admin_subheader',$header);
		$this->load->view('edit_account', $data);
		$this->load->view('admin_footer');
 */
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function updateProfile($id)
	{
		if($this->session->userdata('logged_in'))
		{
			$userloggin=$this->session->userdata('logged_in');
			$username = $this->input->post('user_name');
			$email = $this->input->post('email');
			$password = $this->input->post('password');

			$this->load->model('profile_model');

			//$this->form_validation->set_rules('user_name', 'User Name', 'trim|required|min_length[4]|xss_clean|is_unique[users.username]');
			//$this->form_validation->set_rules('email_address', 'Your Email', 'trim|required|valid_email|is_unique[users.email]');
			$this->form_validation->set_rules('firstName', 'First Name', 'trim|required|xss_clean');

			$this->form_validation->set_rules('lastName', 'Last Name', 'trim|required|xss_clean');
			$this->form_validation->set_rules('gender', 'gender', 'trim|required');
			$this->form_validation->set_rules('phoneNumber', 'Phone Number', 'trim|required|regex_match[/^[0-9().-]+$/]|xss_clean');
			$this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');
			$this->form_validation->set_rules('country', 'country', 'trim|required');

			if($this->input->post('state_new')=='other')
			{
				$this->form_validation->set_rules('other_state', 'Other State', 'trim|required');
			}else{
				$this->form_validation->set_rules('state_new', 'State', 'trim|required');
			}
			$this->form_validation->set_rules('city', 'City', 'trim|required|xss_clean');
			$this->form_validation->set_rules('pincode', 'Pincode', 'trim|required|xss_clean');

			if($this->form_validation->run() == FALSE)
			{
				//$admin=$this->profile_model->getOneuser($id);

				$countries=$this->countries->get_countries();
				if($this->input->post('country')=='')
				{
					$countryid=1;
				}else{
					$countryid=$this->input->post('country');
				}
				$states=$this->countries->get_states($countryid);


				//echo $this->input->post('phoneNumber').'jhfhfhf'; die;
				if($userloggin['type']=='student')
				{
					$phoneno='';
				}else{
					$phoneno=$this->input->post('phoneNumber');
				}
				$data = array(

					'userid' => $this->input->post('user_id'),
					'username' => $this->input->post('user_name'),

					'firstname' => $this->input->post('firstName'),
					'lastname' => $this->input->post('lastName'),
					'gender' => $this->input->post('gender'),
					'phoneNumber' => $phoneno,
					'address' => $this->input->post('address'),
					'city' => $this->input->post('city'),
					'state_id' => $this->input->post('state_new'),
					'other_state' => $this->input->post('other_state'),
					'country_id' => $this->input->post('country'),
					'pincode' => $this->input->post('pincode'),
					//'countries' => $this->input->post('countries'),
					//'states' => $this->input->post('states'),
					'image' => $this->input->post('img'),
					'email' => $this->input->post('email'),
					'countries' =>$countries,
					'states'=>$states,
					'first_login'=>1

				);
				//echo '<pre>'; print_r($data); die;
				$this->load->view('header');
				$this->load->view('subheader');
				$this->load->view('edit_account', $data);
				$this->load->view('footer');

			}else
			{

				if($_FILES['image']['size']>0)
				{
					// Image upload in full size in profile directory

					$uploads_dir = 'uploads/image/profile/';
					$tmp_name = $_FILES["image"]["tmp_name"];
					$name = mktime().$_FILES["image"]["name"];
					move_uploaded_file($tmp_name, "$uploads_dir/$name");

					// image resize in thumbnail size in thumbnail directory

					$config['image_library'] = 'gd2';
					$config['source_image']	= "uploads/image/profile/".$name;
					$config['new_image'] = 'uploads/image/thumbnail/' . $name;

					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 40;
					$config['height']	 = 40;
					$this->image_lib->initialize($config);
					$rtuenval= $this->image_lib->resize();
					$this->image_lib->clear();

					$user_img=$name;

					//// GET OLD IMAGE NAME
					$old_image=$this->input->post('img');
					//// GET PATH OF DIRECTORY
					$path=getcwd();
					//// GET PATH OF IMAGE IN DIRECTROY
					$imagepath=$path.'/uploads/image/profile/'.$old_image;
					$thumb_imagepath=$path.'/uploads/image/thumbnail/'.$old_image;

					//// UNLINK IMAGE
					$responce = unlink($imagepath);
					$responce = unlink($thumb_imagepath);

					// Image Upload End
				}else{

					$user_img=$this->input->post('img');
				}
				$password = $this->input->post('password');


				$this->profile_model->update_account($user_img, $userloggin);

				/*
				if($password!='')
				{
					$user=$this->input->post('firstName').' '.$this->input->post('lastName');
					$email=$this->input->post('email_address');


					$messages= "<p>Dear ".$firstName." ".$lastName.", <p>
					<p>Your Account on YouVsTheWorld has been sucessfully updated.</p>
					<p>Your Account Details are  : <p>
					<p> Email Id : ".$email."</p>
					<p> Password : ".$password."</p>
					<p style='margin-top: 50px;'></p>
					<p>Thanks</p>
					<p>You Vs The World Team</p>
					";
					// Send email to user
					$this->load->library('email');
					$config['mailtype'] = 'html';
					$this->email->initialize($config);
					$this->email->from('Hello@YouvsTheWorld.co', 'You Vs The World');
					$this->email->to($email);
					$this->email->subject('YouVsTheWorld User Account Updated');
					$this->email->message($messages);
					$this->email->send();
				}
				*/
				$this->session->set_flashdata('success_messege', $username.' account has been updated successfully');
				redirect('profile/account');
			}
		}else{

		}

	}

	function editOrgAccount($id=NULL){

		$admin=$this->profile_model->getOneuser($id);

		$data['countries']=$this->countries->get_countries();
		$data['states']=$this->countries->get_states($admin->country_id);

		$data = array(

				'user_id' => $admin->userid,
				'userid' => $admin->userid,
				'org_name' => $admin->name,
				'domain' => $admin->domain,
				'username' => $admin->username,
				'email' => $admin->email,
				'firstname' => $admin->firstname,
				'lastname' => $admin->lastname,
				'contactEmailId' => $admin->contactEmailId,
				'contactPerson' => $admin->contactPerson,
				'contactPhoneNumber' => $admin->contactPhoneNumber,
				'gender' => $admin->gender,
				'phoneNumber' => $admin->phoneNumber,
				'address' => $admin->address,
				'city' => $admin->city,
				'state_id' => $admin->state_id,
				'other_state' => $admin->other_state,
				'country_id' => $admin->country_id,
				'pincode' => $admin->pincode,
				'countries' => $data['countries'],
				'states' => $data['states'],
				'image' =>$admin->image
		);
		//echo '<pre>'; print_r($admin); die;
		$this->load->view('header');
		$this->load->view('subheader');
		$this->load->view('edit_org_account', $data);
		$this->load->view('footer');

	}

	function updateOrgProfile($id=NULL){

		$orgname = $this->input->post('org_name');
		$username = $this->input->post('user_name');
		$email = $this->input->post('email_address');
		$password = $this->input->post('password');

		$this->load->model('profile_model');

		$this->form_validation->set_rules('org_name', 'Organisation Name', 'trim|required');
		$this->form_validation->set_rules('domain', 'Domain', 'trim|required');
		//$this->form_validation->set_rules('email_address', 'Your Email', 'trim|required|valid_email|is_unique[users.email]');

		$this->form_validation->set_rules('firstName', 'First Name', 'trim|required|xss_clean');
		$this->form_validation->set_rules('lastName', 'Last Name', 'trim|required|xss_clean');
		$this->form_validation->set_rules('contactEmailId', 'Contact Email', 'trim|required|valid_email|is_unique[users.email]');

		$this->form_validation->set_rules('gender', 'gender', 'trim|required');
		$this->form_validation->set_rules('contactPerson', 'Contact Persson', 'trim|required');

		$this->form_validation->set_rules('phoneNumber', 'Phone Number', 'trim|required|regex_match[/^[0-9().-]+$/]|xss_clean');
		$this->form_validation->set_rules('contactPhoneNumber', 'Contact Number', 'trim|required|regex_match[/^[0-9().-]+$/]|xss_clean');
		$this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');

		$this->form_validation->set_rules('country', 'country', 'trim|required');
		if($this->input->post('state_new')=='other')
		{
			$this->form_validation->set_rules('other_state', 'Other State', 'trim|required');
		}else{
			$this->form_validation->set_rules('state_new', 'State', 'trim|required');
		}


		$this->form_validation->set_rules('city', 'City', 'trim|required|xss_clean');
		$this->form_validation->set_rules('pincode', 'Pincode', 'trim|required|xss_clean');


		if($this->form_validation->run() == FALSE)
		{
			$admin=$this->profile_model->getOneuser($id);

			$data['countries']=$this->countries->get_countries();
			$data['states']=$this->countries->get_states($this->input->post('country'));

			$data = array(

				'user_id' => $this->input->post('user_id'),
				'userid' => $this->input->post('user_id'),
				'org_name' => $this->input->post('org_name'),
				'domain' => $this->input->post('domain'),
				'email' => $this->input->post('email_address'),
				'password' => $this->input->post('password'),
				'firstname' => $this->input->post('firstName'),
				'lastname' => $this->input->post('lastName'),
				'contactEmailId' => $this->input->post('contactEmailId'),
				'contactPerson' => $this->input->post('contactPerson'),
				'contactPhoneNumber' => $this->input->post('contactPhoneNumber'),
				'gender' => $this->input->post('gender'),
				'phoneNumber' => $this->input->post('phoneNumber'),
				'address' => $this->input->post('address'),
				'city' => $this->input->post('city'),
				'state_id' => $this->input->post('state_new'),
				'other_state' => $this->input->post('other_state'),
				'country_id' => $this->input->post('country'),
				'pincode' => $this->input->post('pincode'),
				'countries' => $data['countries'],
				'states' => $data['states'],
				'image' =>$admin->image

				);
		//echo '<pre>'; print_r($data);die;

			$this->load->view('header');
			$this->load->view('subheader');
			$this->load->view('edit_org_account', $data);
			$this->load->view('footer');
		}else
		{

			if($_FILES['image']['size']>0){
			// Image upload in full size in profile directory

			$uploads_dir = 'uploads/image/profile/';
			$tmp_name = $_FILES["image"]["tmp_name"];
			$name = mktime().$_FILES["image"]["name"];
			move_uploaded_file($tmp_name, "$uploads_dir/$name");

			// image resize in thumbnail size in thumbnail directory

			$config['image_library'] = 'gd2';
			$config['source_image']	= "uploads/image/profile/".$name;
			$config['new_image'] = 'uploads/image/thumbnail/' . $name;

			$config['maintain_ratio'] = TRUE;
			$config['width']	 = 40;
			$config['height']	 = 40;
			$this->image_lib->initialize($config);
			$rtuenval= $this->image_lib->resize();
			$this->image_lib->clear();

			$user_img=$name;

			// Image Upload End

		}else{

			$user_img=$this->input->post('img');
		}

		$this->profile_model->update_organization($user_img);

		$password=$this->input->post('password');
		/*
		if($password!='')
		{
			$user=$this->input->post('firstName').' '.$this->input->post('lastName');
			$email=$this->input->post('email_address');


			$messages= "<p>Dear ".$firstName." ".$lastName.", <p>
			<p>Your Account on YouVsTheWorld has been sucessfully updated.</p>
			<p>Your Account Details are  : <p>
			<p> Email Id : ".$email."</p>
			<p> Password : ".$password."</p>
			<p style='margin-top: 50px;'></p>
			<p>Thanks</p>
			<p>You Vs The World Team</p>
			";
			// Send email to user
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from('Hello@YouvsTheWorld.co', 'You Vs The World');
			$this->email->to($email);
			$this->email->subject('YouVsTheWorld User Account Updated');
			$this->email->message($messages);
			$this->email->send();
		}
		*/
		$this->session->set_flashdata('success_messege', $orgname.' account has been updated successfully');
		redirect('profile/account');

		$this->load->view('header');
		$this->load->view('subheader');
		$this->load->view('account_update');
		$this->load->view('footer');

		}
}



}
