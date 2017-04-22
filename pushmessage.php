<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pushmessage extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url',));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('user_model','administrator_model','pushmessage_model'));
		$this->load->library('pagination');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');


	}

	function index(){

		$this->session->unset_userdata('refer_from');
		//list of Push messages
		$login=$this->administrator_model->login_session();
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['records']=$this->pushmessage_model->lists();
			$config['base_url'] = base_url().'index.php/pushmessage/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$data['pushmsgs'] = $this->pushmessage_model->lists($page, $config['per_page']);

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('pushmessage_list',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('administrator');
		}
	}

	function sendMessage($id=''){

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['types'] = $this->user_model->getUserTypes();

			//echo "<pre>";
			//print_r($data);die;
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('push_message',$data);
			$this->load->view('admin_footer');

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function uploadEditorImage(){

	 // Allowed extentions.
	 $allowedExts = array("gif", "jpeg", "jpg", "png", "blob");

	 // Get filename.
	 $temp = explode(".", $_FILES["file"]["name"]);

	 // Get extension.
	 $extension = end($temp);

	 // An image check is being done in the editor but it is best to
	 // check that again on the server side.
	 // Do not use $_FILES["file"]["type"] as it can be easily forged.
	 $finfo = finfo_open(FILEINFO_MIME_TYPE);
	 $mime = finfo_file($finfo, $_FILES["file"]["tmp_name"]);

	 if ((($mime == "image/gif")
			 || ($mime == "image/jpeg")
			 || ($mime == "image/pjpeg")
			 || ($mime == "image/x-png")
			 || ($mime == "image/png"))
			 && in_array(strtolower($extension), $allowedExts)) {
				 // Generate new random name.
				 $name = sha1(microtime()) . "." . $extension;

				 // Save file in the uploads folder.
				 move_uploaded_file($_FILES["file"]["tmp_name"], getcwd() . "/upload/editor/" . $name);

				 // Generate response.
				 $response = new StdClass;
				 $response->link = base_url()."upload/editor/" . $name;
				 echo stripslashes(json_encode($response));
			 }
	}

	function loadEditorImages(){
	 $response = array();

	 // Image types.
	 $image_types = array(
			 "image/gif",
			 "image/jpeg",
			 "image/pjpeg",
			 "image/jpeg",
			 "image/pjpeg",
			 "image/png",
			 "image/x-png"
	 );

	 // Filenames in the uploads folder.
	 $fnames = scandir("upload/editor");

	 // Check if folder exists.
	 if ($fnames) {
		 // Go through all the filenames in the folder.
		 foreach ($fnames as $name) {
			 // Filename must not be a folder.
			 if (!is_dir($name)) {
				 // Check if file is an image.
				 if (in_array(mime_content_type(getcwd() . "/upload/editor/" . $name), $image_types)) {
					 // Build the image.
					 $img = new StdClass;
					 $img->url = base_url()."upload/editor/" . $name;
					 $img->thumb = base_url()."upload/editor/" . $name;
					 $img->name = $name;

					 // Add to the array of image.
					 array_push($response, $img);
				 }
			 }
		 }
	 }

	 // Folder does not exist, respond with a JSON to throw error.
	 else {
		 $response = new StdClass;
		 $response->error = "Images folder does not exist!";
	 }

	 $response = json_encode($response);

	 // Send response.
	 echo stripslashes($response);
	}

	function deleteEditorImage(){
		 // Get src.
		 $src = $_POST["src"];

		 // Check if file exists.
		 if (file_exists(getcwd() . $src)) {
			 // Delete file.
			 unlink(getcwd() . $src);
		 }
	}

	function addMessage(){

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
		  //Validation
		  $this->form_validation->set_rules('message', 'Push Message', 'trim|required');

			if($this->form_validation->run() == FALSE)
			{

				$data['types'] = $this->user_model->getUserTypes();

				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('push_message',$data);
				$this->load->view('admin_footer');
			}
			else{
				$usertpe = $this->input->post('usertype');
				$message = $this->input->post('message');

				$push = array(
						'msg_id'		=> '',
						'userType' 		=> $usertpe,
						'message'		=> $message,
						'active'		=> 1,
						'createdDate'	=> date('YmdHis'),
						'modifiedDate'	=> date('YmdHis')
				);
				$this->pushmessage_model->addMessage($push);
				$this->session->set_flashdata('success_messege','Push Message added successfully');
				redirect('pushmessage');
			}
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}

	}

	function delete(){

		$id = $this->uri->segment(3);

		$delete['msg_id']=$id;
		$delete['active']= 0;
		$delete['modifiedDate']=date('YmdHis');
		$this->pushmessage_model->delete($delete);

		$this->session->set_flashdata('success_messege','Push message has been deleted sucessfully');
		//redirect('pushmessage');
		redirect($_SERVER['HTTP_REFERER']);
	}

	function editMessage(){
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$refer_from=$_SERVER['HTTP_REFERER'];

			$arr=explode('/',$refer_from);
			$ind=count($arr)-1;
			$ind1=count($arr)-2;
			$arr[$ind1];
			if($arr[$ind]==='pushmessage' or $arr[$ind1]==='index'){
				$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
			}
			$id = $this->uri->segment(3);

			$data['pushMsg_id']='';
			$data['usertype_new']='';
			$data['update']=0;
			$data['message']='';
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['types'] = $this->user_model->getUserTypes();

			$data['pushMsg']=$this->pushmessage_model->getPushMessage($id);

			//$data['msg_ig']=$id;
			//echo "<pre>";
			//print_r($data);die;
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('edit_pushmessage',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function updateMessage(){
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['types'] = $this->user_model->getUserTypes();

			$this->form_validation->set_rules('message', 'Push Message', 'trim|required');
			if ($this->form_validation->run() == FALSE)
			{
				$data['usertype_new']=$this->input->post('usertype');
				$data['pushMsg_id']=$this->input->post('msg_id');;
				$data['message']=$this->input->post('message');
				$data['update']=1;

				//// Load View Page

				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('edit_pushmessage',$data);
				$this->load->view('admin_footer');
			}else
			{
				//Update Push Message
				$msg_id = $this->input->post('msg_id');
				$usertype = $this->input->post('usertype');
				$message = $this->input->post('message');
				$update = array(

						'msg_id' 		=> $msg_id,
						'userType'		=> $usertype,
						'message'		=> $message,
						'modifiedDate'	=> date('YmdHis')
				);
				$this->pushmessage_model->update($update);
				$this->session->set_flashdata('success_messege','Push Message is updated successfully');
				//redirect('pushmessage');
				redirect($this->session->userdata('refer_from'));
			}

		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	public function sendMessageAllUsers(){
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$refer_from=$_SERVER['HTTP_REFERER'];

			$arr=explode('/',$refer_from);
			$ind=count($arr)-1;
			$ind1=count($arr)-2;
			$arr[$ind1];
			if($arr[$ind]==='pushmessage' or $arr[$ind1]==='index'){
				$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
			}
			$notification_id = $this->uri->segment(3);
			$pushmessageRow = $this->pushmessage_model->getPushMessage($notification_id);
			$userType = $pushmessageRow->userType;

			$userResults = $this->user_model->getAllUsersByUserType($userType);
			$arr = array();
			if(count($userResults) > 0){
				 foreach ($userResults as $userResult) {
				 	 $data = array('email' => $userResult->email,'username'=>$userResult->username,'message'=>$pushmessageRow->message);
					 $this->sendmail($data);

					 $arr = array('user_id'=> $userResult->user_Id,'pushmessage_id'=>$notification_id,'notificationType' => 1,'createdDate'=>date('Y-m-d H:i:s'));

		 			 $this->db->insert('userNotifications',$arr);
				 }
			}

			//$data = array('email' => 'sarvesh@qsstechnosoft.com','username'=>'sarveshqss','message'=>$pushmessageRow->message);
			//$this->sendmail($data);

			$update = array(
					'msg_id' 		=> $notification_id,
					'is_send'		=> 1,
					'modifiedDate'	=> date('YmdHis')
			);
			$this->pushmessage_model->update($update);
			$this->session->set_flashdata('success_messege','Email notification has been send successfully');
			//redirect('pushmessage');
			redirect($this->session->userdata('refer_from'));
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	public function sendmail($data){
		$this->emailConfig();
		$this->load->model('email_model');

		$email_template = $this->email_model->getoneemail('hurree_notification');

		//// MESSAGE OF EMAIL
		$messages = $email_template->message;

		$hurree_image = base_url() . 'assets/template/frontend/img/hurree_business.png';
		$appstore = base_url() . 'assets/template/frontend/img/appstore.gif';
		$googleplay = base_url() . 'assets/template/frontend/img/googleplay.jpg';

		$messages = str_replace('{username}', ucfirst($data['username']), $messages);
		$messages = str_replace('{message}', $data['message'], $messages);

		//// FROM EMAIL
		$this->email->from($email_template->from_email, 'Hurree');
		$this->email->to($data['email']);
		$this->email->subject($email_template->subject);
		$this->email->message($messages);
		$this->email->send();    ////  EMAIL SEND`
  }

	function emailConfig() {

			$this->load->library('email');   //// LOAD LIBRARY

			$config['protocol'] = 'smtp';
			$config['smtp_host'] = 'ssl://email-smtp.eu-west-1.amazonaws.com';//auth.smtp.1and1.co.uk
			$config['smtp_port'] = 465;
			$config['smtp_user'] = 'AKIAJUJGM2OYDQR4TSWA';//support@hurree.co.uk
			$config['smtp_pass'] = 'AkINVk1QbB5FLbvbu43cduRlx4be3zFGmvMqmu99Aw2t';
			$config['charset'] = 'utf-8';
			$config['newline'] = "\r\n";
			$config['mailtype'] = 'html'; // or html

			$this->email->initialize($config);
	}

	public function sendmailTest(){
		 $ci = get_instance();
		 $ci->load->library('email');

		 $config['protocol'] = 'smtp';
		 $config['smtp_host'] = 'ssl://email-smtp.eu-west-1.amazonaws.com';//auth.smtp.1and1.co.uk
		 $config['smtp_port'] = 465;
		 $config['smtp_user'] = 'AKIAJUJGM2OYDQR4TSWA';//support@hurree.co.uk
		 $config['smtp_pass'] = 'AkINVk1QbB5FLbvbu43cduRlx4be3zFGmvMqmu99Aw2t';
		 $config['charset'] = 'utf-8';
		 $config['newline'] = "\r\n";
		 $config['mailtype'] = 'html'; // or html
		 $ci->email->initialize($config);

		 $ci->email->from('pankaj@qsstechnosoft.com', 'Hurree');
		 $list = array('sarvesh@qsstechnosoft.com');
		 $ci->email->to($list);
		 $ci->email->subject('This is an email test');
		 $ci->email->message('It is working. Great!');


		 if($this->email->send())
		 {
			 echo 'Email sent.';
		 }
		 else
		 {
			show_error($this->email->print_debugger());
		 }
	}

}
