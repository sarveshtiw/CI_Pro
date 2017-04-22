<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ambassadors extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('user_model','ambassador_model','administrator_model','country_model','email_model','score_model'));
		$this->load->library('pagination');
		$this->load->library('image_lib');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');


	}

	function index(){

		$this->session->unset_userdata('refer_from');
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['records']=$this->ambassador_model->getUsers();
			$config['base_url'] = base_url().'index.php/ambassadors/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$data['ambassadors'] = $this->ambassador_model->getUsers($page, $config['per_page']);

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('ambassadors',$data);
			$this->load->view('admin_footer');

		}

			else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function ambassador(){

		$login=$this->administrator_model->login_session();
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$id = $this->uri->segment(3);

		$data['ambassador'] = $this->ambassador_model->getOneUser($id);
		$data['business_users'] = $this->ambassador_model->getBusinessUsers($id);
		//echo $this->db->last_query();
		//echo '<pre>';
		//print_r($data['ambassador']);die;
		$data['noOfBusinessUsers'] = count($data['business_users']);

		$this->load->view('admin_header',$header);
		$this->load->view('admin_subheader',$header);
		$this->load->view('ambassador_profile',$data);
		$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function edit(){

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$refer_from=$_SERVER['HTTP_REFERER'];

			$arr=explode('/',$refer_from);
			$ind=count($arr)-1;
			$ind1=count($arr)-2;
			$arr[$ind1];
			if($arr[$ind]==='ambassadors' or $arr[$ind1]==='index'){

				$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
			}


			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$id = $this->uri->segment(3);

			$ambassador = $this->ambassador_model->getOneUser($id);
			$data = array(

					'userid'	=> $ambassador->userid,
					'username' => $ambassador->username,
					'email'	   => $ambassador->email,
					'companyName' => $ambassador->companyName,
					'referral_name' => $ambassador->referral_name,
					'firstname'	=> $ambassador->firstname,
					'lastname'	=> $ambassador->lastname,
					'website'	=> $ambassador->website,
					'hear_about_us' => $ambassador->hear_about_us,
					'description'	=> $ambassador->description,
					'contactNumber'	=> $ambassador->contactNumber,
					'address1'	=> $ambassador->address1,
					'address2'	=> $ambassador->address2,
					'town'	=> $ambassador->town,
					'postCode'	=> $ambassador->postCode,
					'paypal_email'	=> $ambassador->paypal_email,
					'image'		=> $ambassador->image
			);
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('edit_ambassador',$data);
			$this->load->view('admin_footer');

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
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



			$this->form_validation->set_rules('firstname', 'Firstname', 'trim|required');
			$this->form_validation->set_rules('lastname', 'Lastname', 'trim|required');
			if($this->input->post('password')!='')
				{
				$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]');
				$this->form_validation->set_rules('con_password', 'Re Password', 'trim|required|matches[password]');
			}
			$this->form_validation->set_rules('description', 'Description', 'trim|required');
			$this->form_validation->set_rules('contactNumber', 'Contact Number', 'trim|required');
			$this->form_validation->set_rules('address1', 'Address 1', 'trim|required');
			$this->form_validation->set_rules('town', 'Town', 'trim|required');
			$this->form_validation->set_rules('postCode', 'Post Code', 'trim|required');
			$this->form_validation->set_rules('paypal_email', 'PayPal Email', 'trim|required|valid_email');
				if($this->form_validation->run() == FALSE)
				{

					$data = array(

					'userid'	=> $this->input->post('user_id'),
					'username' => $this->input->post('user_name'),
					'email'	   => $this->input->post('email'),
					'companyName' => $this->input->post('companyName'),
					'referral_name' => $this->input->post('referral_name'),
					'firstname'	=> $this->input->post('firstname'),
					'lastname'	=> $this->input->post('lastname'),
					'website'	=> $this->input->post('website'),
					'hear_about_us' => $this->input->post('hear_about_us'),
					'description'	=> $this->input->post('description'),
					'contactNumber'	=> $this->input->post('contactNumber'),
					'address1'	=> $this->input->post('address1'),
					'address2'	=> $this->input->post('address2'),
					'town'	=> $this->input->post('town'),
					'postCode'	=> $this->input->post('postCode'),
					'paypal_email'	=> $this->input->post('paypal_email'),
			);

				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('edit_ambassador', $data);
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
					$save['password']=$this->input->post('password');
				}
				$save['firstname']=$this->input->post('firstname');
				$save['lastname']=$this->input->post('lastname');
				$save['modifiedDate']=date('YmdHis');
				$save['image']=$user_img;

				$this->user_model->insertsignup($save);

				//Update ambassador_details table
				$update['ambassadorId'] = $this->input->post('user_id');
				$update['companyName'] = $this->input->post('companyName');
				$update['website'] = $this->input->post('website');
				$update['hear_about_us'] = $this->input->post('hear_about_us');
				$update['description'] = $this->input->post('description');
				$update['contactNumber'] = $this->input->post('contactNumber');
				$update['address1'] = $this->input->post('address1');
				$update['address2'] = $this->input->post('address2');
				$update['town'] = $this->input->post('town');
				$update['postCode'] = $this->input->post('postCode');
				$update['paypal_email'] = $this->input->post('paypal_email');
				$update['modifiedDate'] = date('YmdHis');

				$this->ambassador_model->updateDetails($update);
				$this->session->set_flashdata('success_messege','Profile Has Been Updated Sucessfully');
				//redirect('ambassadors');
				redirect($this->session->userdata('refer_from'));
				}

	}
	else{
					$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
					redirect('H5fgs2134vbdsgtfdsrt');
	}
	}

	function delete(){

		$id = $this->uri->segment(3);
		$update['user_Id'] = $id;
		$update['active'] = 0;
		$this->ambassador_model->delete($update);
		$this->session->set_flashdata('success_messege','Profile has been deleted sucessfully');
		//redirect('ambassadors');
		redirect($_SERVER['HTTP_REFERER']);
	}

	function addAmbassador(){

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['hear_about_us'] = '';
			$data['countries']=$this->country_model->get_countries();
			$data['companyName']='';
			$data['website']='';
			$data['country_id'] = '';

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('add_ambassador',$data);
			$this->load->view('admin_footer');

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function insertAmbassador(){

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$this->form_validation->set_rules('user_name', 'Username', 'trim|required|is_unique[users.username]');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
			$this->form_validation->set_rules('con_password', 'Confirm Password', 'trim|required|matches[password]');
			$this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|is_unique[users.email]');
			$this->form_validation->set_rules('referral_name', 'Referral Name', 'trim|required|is_unique[users.referral_name]');
			$this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
			$this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
			$this->form_validation->set_rules('description', 'Description', 'trim|required');
			$this->form_validation->set_rules('contactNumber', 'Contact Number', 'trim|required');
			$this->form_validation->set_rules('address1', 'Address 1', 'trim|required');
			$this->form_validation->set_rules('town', 'Town', 'trim|required');
			$this->form_validation->set_rules('postCode', 'Post Code', 'trim|required');
			$this->form_validation->set_rules('paypal_email', 'PayPal Email', 'trim|required|valid_email');

			if ($this->form_validation->run() == FALSE)
			{
				$data['country_id'] = $this->input->post('country');
				$data['countries']=$this->country_model->get_countries();
				$data['hear_about_us'] = $this->input->post('hear_about_us');
				$data['companyName']=$this->input->post('company_name');
				$data['website']=$this->input->post('website');
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('add_ambassador',$data);
				$this->load->view('admin_footer');
			}
			else{
				if($_FILES['image']['size']>0)
				{
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
					$config['width']	 = 250;
					$config['height']	 = 200;
					//echo '<pre>'; print_r($config); die;
					$this->image_lib->initialize($config);

					$this->image_lib->resize();
					$this->image_lib->clear();


					$config['image_library'] = 'gd2';
					$config['source_image']	= "upload/profile/medium/".$name;
					$config['new_image'] = 'upload/profile/thumbnail/' . $name;

					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 40;
					$config['height']	 = 40;
					$this->image_lib->initialize($config);
					$rtuenval= $this->image_lib->resize();
					$this->image_lib->clear();

					$user_img=$name;

					// Image Upload End

				}else{

						$user_img='user.png';

				}
				$user['user_id']='';
				$user['username']=$this->input->post('user_name');
				$user['email']=$this->input->post('email');
				$user['password']=md5($this->input->post('password'));
				$user['firstname']=$this->input->post('firstname');
				$user['lastname']=$this->input->post('lastname');
				$user['country'] = $this->input->post('country');
				$user['image']=$user_img;
				$user['header_image'] = 'profileBG.jpg';
				$user['active']=1;
				$user['usertype']=4;
				$user['referral_name'] = $this->input->post('referral_name');
				$user['loginSource'] = 'normal';
				$user['createdDate']=date('YmdHis');

				//print_r($user);die;
				$user_id=$this->user_model->insertsignup($user);

				$ambassador_details = array(

						'ambassadorId'	=> $user_id,
						'companyName'	=> $this->input->post('company_name'),
						'website'		=> $this->input->post('website'),
						'hear_about_us'	=> $this->input->post('hear_about_us'),
						'description'	=> $this->input->post('description'),
						'contactNumber'	=> $this->input->post('contactNumber'),
						'country'		=> $this->input->post('country'),
						'address1'		=> $this->input->post('address1'),
						'address2'		=> $this->input->post('address2'),
						'town'			=> $this->input->post('town'),
						'postCode'		=> $this->input->post('postCode'),
						'paypal_email'	=> $this->input->post('paypal_email'),
						'createdDate'	=> date('YmdHis'),
						'modifiedDate'	=> date('YmdHis')
				);
				$this->user_model->insertAmbassadorDetails($ambassador_details);

				/* Insert Coins */
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

				$this->session->set_flashdata('success_messege','New Ambassador is added successfully');
				redirect('ambassadors');

			}

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}





	}

	function paymentReset(){

		$ambassadorId = $this->uri->segment(3);
		$ambassador = $this->ambassador_model->getOneUser($ambassadorId);
		//print_r($ambassador);die;
		$email = $ambassador->email;
		$username = $ambassador->username;
		$businessUsers = $this->ambassador_model->getBusinessUsers($ambassadorId);

		foreach($businessUsers as $business){

			$update['user_Id']= $business->user_Id;
			$update['ambassador_is_new'] = 0;
			$this->user_model->updateProfile($update);
		}

		//// SEND  EMAIL START
		$this->emailConfig();   //Get configuration of email
		//// GET EMAIL FROM DATABASE


		$email_template=$this->email_model->getoneemail('ambassador_payment');

		//// MESSAGE OF EMAIL
		$messages= $email_template->message;

		$hurree_image= 'http://54.254.239.126/hurree/assets/template/hurree/images/app-icon.png';
		$appstore='http://54.254.239.126/hurree_images/appstore.gif';
		$googleplay='http://54.254.239.126/hurree_images/googleplay.jpg';
		$referral ='http://www.hurree.co/'.$referral;
		//// replace strings from message
		$messages=str_replace('{Username}', ucfirst($username), $messages);
		$messages=str_replace('{Hurree_Image}', $hurree_image, $messages);
		$messages=str_replace('{App_Store_Image}', $appstore, $messages);
		$messages=str_replace('{Google_Image}', $googleplay, $messages);


		//// FROM EMAIL
		$this->email->from($email_template->from_email, 'Hurree');
		$this->email->to($email);
		$this->email->subject($email_template->subject);
		$this->email->message($messages);
		$this->email->send();    ////  EMAIL SEND

		//// END EMAIL

		redirect('ambassadors/ambassador/'.$ambassadorId);
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
}
