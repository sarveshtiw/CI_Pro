<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class hurreeEmail extends CI_Controller {

     public function __construct() {
		  parent::__construct();
                          $this->load->model(array('brand_model'));
                  $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
     }

     public function bounces(){
        require_once APPPATH . 'third_party/ses/bounces.php';
     }

     public function complaints(){
        require_once APPPATH . 'third_party/ses/complaints.php';
     }

     public function unsubscribe($email = null, $businessId = null, $app_group_id = null){
         if($email == null || $businessId == null || $app_group_id == null)
         { 
            //echo "<h1>Unsubscribed Url is incorrect.</h1>"; exit;
            $data['message'] = 'Unsubscribed Url is incorrect.';
            
         }else{
                $data['email'] = $email;
                $data['businessId'] = $businessId;
                $data['app_group_id'] = $app_group_id;
                $email = base64_decode(urldecode($email));
                
                if(!$this->brand_model->isValideParameters($email, $businessId, $app_group_id))
                 { 
                    //echo "<h1>Unsubscribed Url is incorrect.</h1>"; exit;
                    $data['message'] = 'Unsubscribed Url is incorrect.';
                 }else{
                     
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                        $result = $this->brand_model->getUsersUnsubscribeEmails($email, $app_group_id);
                        
                        if(count($result) > 0){
                          $id = $result['Id'];
//                          $arr = array('Id' => $id);
//                          $data = $data + $arr;
//                          $this->db->where('Id',$id);
//                          $this->db->update('unsubscribe_emails',$data);
                          
                          $data = array('Id' => $id, 'from_email' => $email, 'unsubscribe' => 1,'businessId' =>$businessId,'app_group_id' => $app_group_id,'modifiedDate' => date('Y-m-d H:i:s'));
                          $this->brand_model->updateUnsubscribeEmail($data);
                        }else{
                          $data = array('from_email' => $email, 'unsubscribe' => 1,'businessId' =>$businessId,'app_group_id' => $app_group_id,'createdDate' => date('Y-m-d H:i:s'));
                          $this->brand_model->insertUnsubscribeEmail($data); 
                          //$this->db->insert('unsubscribe_emails',$data);
                        }

                        //$this->session->set_flashdata('success_message', "<h1>You've been Unsubscribed successfully.</h1>");
                        $data['message'] = "You've been unsubscribed successfully.";

                      }else{
                       $data['message'] = 'Unsubscribed Url is incorrect.';
                     }
                 }       
         }
         
         $this->load->view('unsubscribe',$data);
     }

     public function unsubscribeEmail(){
        $email = base64_decode($this->uri->segment(3));
        $businessId = $this->uri->segment(4);
        $app_group_id = $this->uri->segment(5);
        $data = array('from_email' => $email, 'unsubscribe' => 1,'businessId' =>$businessId,'app_group_id' => $app_group_id,'createdDate' => date('YmdHis'));

        $this->db->select('*');
        $this->db->where('from_email',$email);
        $this->db->where('type','unsubscribe');
        $result = $this->db->get('unsubscribe_emails');
        $result = $result->row_array();
        if(count($result) > 0){
          $id = $result['Id'];
          $arr = array('Id' => $id);
          $data = $data + $arr;
		  	  $this->db->where('Id',$id);
          $this->db->update('unsubscribe_emails',$data);
        }else{
       		 $this->db->insert('unsubscribe_emails',$data);
        }

			 $this->session->set_flashdata('success_message', "<h1>You've been Unsubscribed successfully.</h1>");
			 redirect('hurreeEmail/subscribe/'.base64_encode($email).'/'.$businessId.'/'.$app_group_id);
       //  redirect('hurreeEmail/unsubscribeSuccess');
        //$this->load->view('unsubscribe',$data);
     }

     public function unsubscribeSuccess(){
       echo "<h1>You've Been Unsubscribed.</h1>"; exit();
     }

     public function sendmail(){
       $ci = get_instance();
        $ci->load->library('email');
        $config['smtp_host'] = 'smtp.hurree.co';//auth.smtp.1and1.co.uk
        $config['smtp_port'] = '587';//587
        $config['smtp_user'] = 'support@hurree.co';//support@hurree.co.uk
        $config['smtp_pass'] = 'aaron8164';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html

        $ci->email->initialize($config);

        $ci->email->from('support@hurree.co', 'Hurree');
        $list = array('sarvesh@qsstechnosoft.com');
        $ci->email->to($list);
        $ci->email->subject('This is an email test');
        $ci->email->message('It is working. Great!');
        if($ci->email->send())
        {
          echo 'Email sent.';
        }
        else
        {
         show_error($this->email->print_debugger());
        }
     }

  	public function sesmail(){
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

		 $ci->email->from('hello@hurree.co', 'Hurree');
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

	public function subscribe(){
		 $data['email'] = '';
		 $data['email'] = $this->uri->segment(3);
                 $data['businessId'] = $this->uri->segment(4);
                 $data['app_group_id'] = $this->uri->segment(5);

                 $email = base64_decode($this->uri->segment(3));
                 $businessId = $this->uri->segment(4);
                 $app_group_id = $this->uri->segment(5);
		 $data = array('from_email' => $email, 'unsubscribe' => 0,'businessId' =>$businessId,'app_group_id'=>$app_group_id);

		 $this->db->select('*');
		 $this->db->where('from_email',$email);
                 $this->db->where('type','unsubscribe');
		 $result = $this->db->get('unsubscribe_emails');
		 $result = $result->row_array();
		 if(count($result) > 0){
			 $id = $result['Id'];
			 $arr = array('Id' => $id);
			 $data = $data + $arr;
                         $this->db->where('Id',$id);
                    $this->db->update('unsubscribe_emails',$data);
		 }else{
                    $this->db->insert('unsubscribe_emails',$data);
		 }

		 $this->session->set_flashdata('success_message', "<h1>You've been subscribed successfully.</h1>");
		 $this->load->view('subscribe',$data);
	}

	public function subscribeEmail(){
		 $email = base64_decode($this->uri->segment(3));
                 $businessId = $this->uri->segment(4);
                 $app_group_id = $this->uri->segment(5);
		 $data = array('from_email' => $email, 'unsubscribe' => 0,'businessId' =>$businessId,'app_group_id'=>$app_group_id);

		 $this->db->select('*');
		 $this->db->where('from_email',$email);
                 $this->db->where('type','unsubscribe');
		 $result = $this->db->get('unsubscribe_emails');
		 $result = $result->row_array();
		 if(count($result) > 0){
			 $id = $result['Id'];
			 $arr = array('Id' => $id);
			 $data = $data + $arr;
	 	   $this->db->where('Id',$id);
			 $this->db->update('unsubscribe_emails',$data);
		 }else{
				$this->db->insert('unsubscribe_emails',$data);
		 }

		 $this->session->set_flashdata('success_message', "<h1>You've been subscribed.</h1>");
		 redirect('hurreeEmail/unsubscribe/'.base64_encode($email).'/'.$businessId.'/'.$app_group_id);
		 //$this->load->view('unsubscribe',$data);
	}

}
