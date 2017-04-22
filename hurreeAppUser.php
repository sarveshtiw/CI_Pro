<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class hurreeAppUser extends CI_Controller {

      public function __construct()	{
      		parent::__construct();

      		$this->load->helper(array('form', 'url'));
      		$this->load->library(array('form_validation','session','pagination','image_lib','email'));
      		$this->load->database();
      		$this->load->model(array('user_model','email_model','brand_model','administrator_model','country_model','score_model','beacon_model','payment_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    	}

      public function index() {
        $this->session->unset_userdata('refer_from');
        $login=$this->administrator_model->login_session();

        if( $login['true']==1 && $login['accesslevel']=='admin')
        {

          $usertype="8";
          $subusertype = "9";
          $activeLogin="1";
          $data['records']=$this->user_model->getOneUserType($usertype,'','','','', $activeLogin,$subusertype);      //// Count Same User Type
          $config['base_url'] = base_url().'index.php/hurreeAppUser/index/';
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
          //// Load View Pages
          $this->load->view('admin_header',$header);
          $this->load->view('admin_subheader',$header);
          $this->load->view('appUserList',$data);
          $this->load->view('admin_footer');
        }else
        {
          $this->session->set_flashdata('alert_message','Username Does Not Exits');
          redirect('administrator');
        }
    }


    public function delete()
    {
    		$userid = $this->uri->segment('3');

    		$user = $this->user_model->getOneUser($userid);

    		$save['user_Id']=$userid;
    		$save['active']=0;
    		$save['modifiedDate']=date('YmdHis');
    		$this->user_model->insertsignup($save);

    		$user = $this->user_model->getOneUser($userid);

    		if($user->usertype == 8){
    		    $this->session->set_flashdata('success_messege','App Admin User has been deleted successfully');
    		   redirect($_SERVER['HTTP_REFERER']);
    		}
    		elseif ($user->usertype == 9){
    			$this->session->set_flashdata('success_messege','App Sub User has been deleted successfully');
    			redirect($_SERVER['HTTP_REFERER']);
    		}
   }

   public function appUser_form()
   {
     $id = $this->uri->segment(3);

     $header['sess_details']=$this->session->userdata('sess_admin');

     if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
     {
       $header['url']=$this->uri->segment(1);

       $data['usertype']='8';
       $data['id']=$id;


       $data['username']='';
       $data['firstname']='';
       $data['lastname']='';
       $data['email']='';
       $data['image']='';

       if($id!='')
       {
         $oneuser=$this->user_model->getOneUser($id);
         $data['username']=$oneuser->username;
         $data['email']=$oneuser->email;
         $data['firstname']=$oneuser->firstname;
         $data['lastname']=$oneuser->lastname;
         $data['username']=$oneuser->username;
         $data['image']=$oneuser->image;

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
         $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[32]');
         $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|max_length[32]');
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
         $this->load->view('appUser_form',$data);
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

         $user_id = $this->user_model->insertsignup($user);

         if(empty($id)){

             $last_insert_user_id = $this->user_model->getMaxUserRow();
             $last_insert_id = rand();
             if(isset($last_insert_user_id->user_Id)){
               $last_insert_id = $last_insert_user_id->user_Id + 1;
             }
             $users['businessId'] =  $last_insert_id;
             $this->db->where('user_Id',$user_id);
             $this->db->update("users", $users);

             $business = array(

                 "busi_id" =>'',
                 "businessId" => $user_id,
                 "businessName" => ""

             );


             $this->db->insert("business", $business);

             $brand_arr = array(
                 'user_id' => $user_id,
                 "businessId" => $last_insert_id,
                 'totalIosApps' => 1,
                 'totalAndroidApps' => 1,
                 'totalCampaigns' => 1,
                 'totalAppGroup' => 1,
                 'androidCampaign' => 1,
                 'iOSCampaign' => 1,
                 'emailCampaign' => 5,
                 'createdDate' => date('YmdHis')
             );

             $this->brand_model->savePackage($brand_arr);

             // send data to hubspot
             $portal = HUBPORTALID;
             $status = $this->hubspotAuthenticaion($portal);
             if($status == 302)
             {
               $responce_code = $this->savecontactToHubspot($user['email'], $user['firstname'], $user['lastname'] );
               if($responce_code != 200)
               {
                 $responcecode = $this->savecontactToHubspot($user['email'], $user['firstname'], $user['lastname'] );
                 if($responcecode  != 200)
                 {
                   $responcecode = $this->savecontactToHubspot($user['email'], $user['firstname'], $user['lastname'] );
                 }
               }


             }

             //// SEND  EMAIL START
             $this->emailConfig();   //Get configuration of email
             //// GET EMAIL FROM DATABASE

             $email_template = $this->email_model->getoneemail('brandSignUp');

             //// MESSAGE OF EMAIL
             $messages = $email_template->message;
             $hurree_image = base_url() . 'assets/img/Graph-icon-white-grey.png';
             //// replace strings from message
             $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
             $messages = str_replace('{Username}', ucfirst($user['username']), $messages);

             //// FROM EMAIL
             //$this->email->from($email_template->from_email, 'Hurree');
             $this->email->from('hello@hurree.co', 'Hurree');
             $this->email->to($user['email']);
             $this->email->subject($email_template->subject);
             $this->email->message($messages);
             $this->email->send();    ////  EMAIL SEND

         }

         if($id=='')
         {
           $this->session->set_flashdata('success_messege','App Admin has been created');
         }else{
           $this->session->set_flashdata('success_messege','App Admin has been updated sucessfully');
         }
         redirect('hurreeAppUser');
       }
     }else{
       $this->session->set_flashdata('alert_message','Username Does Not Exits');
       redirect('H5fgs2134vbdsgtfdsrt');
     }
   }

   	function emailConfig(){

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


    function hubspotAuthenticaion($portalId)
    {
      	$endpoint = 'https://app.hubspot.com/auth/authenticate?client_id='.HUBCLIENTID.'&portalId='.$portalId.'&redirect_uri='.base_url().'home/hubspotAuthenticaion&scope=offline';

      	$ch = @curl_init();
      	@curl_setopt($ch, CURLOPT_URL, $endpoint);
      	@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      	@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
      	@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
      	$response = @curl_exec($ch);
      	$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
      	$curl_errors = curl_error($ch);
      	@curl_close($ch);
      	// 		echo "curl Errors: " . $curl_errors;
      	// 		echo "\nStatus code: " . $status_code;
      	// 		echo "\nResponse: " . $response;
      	return  $status_code;
    }

    function savecontactToHubspot($email, $firstname, $lastname )
    {

      	$arr = array(
      			'properties' => array(
      					array(
      							'property' => 'email',
      							'value' => $email
      					),
      					array(
      							'property' => 'firstname',
      							'value' => $firstname
      					),
      					array(
      							'property' => 'lastname',
      							'value' => $lastname
      					),
      					array(
      							'property' => 'lifecyclestage',
      							'value' => 'lead'
      					)
      			)
      	);

      	$post_json = json_encode($arr);
      	//echo $post_json; die;
      	//$hapikey = HUBAPIKEY;
      	//$endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch?hapikey=' . $hapikey;
      	$endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey='.HAPIKEY ;
      	$ch = @curl_init();
      	@curl_setopt($ch, CURLOPT_POST, true);
      	@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
      	@curl_setopt($ch, CURLOPT_URL, $endpoint);
      	@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      	@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
      	@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);

      	$response = @curl_exec($ch);
      	$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
      	$curl_errors = curl_error($ch);
      	@curl_close($ch);
      	//echo "curl Errors: " . $curl_errors;
      	//echo "\nStatus code: " . $status_code;
      	//  			echo "\nResponse: " . $response;


      	return  $status_code;
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
?>
