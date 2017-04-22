<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Subscriptions extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
	
		$this->load->helper(array('form', 'url','credit_card_helper'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('user_model','payment_model','administrator_model'));
		$this->load->library('pagination');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	
			
	}	
	
	function index()
	{
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			
			$data['records']=$this->payment_model->getSubscriptions();
			$config['base_url'] = base_url().'index.php/subscriptions/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$data['subscriptions'] = $this->payment_model->getSubscriptions($page, $config['per_page']);
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('subscriptions',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function edit(){
		
		$id = $this->uri->segment(3);
		//echo $id;die;
		$data['paymentId']='';
		$data['firstname']='';
		$data['lastname']='';
		$data['email']='';
		$data['purchasedOn']='';
		$data['amount']='';
		$data['currency']='';
		
		$login=$this->administrator_model->login_session();
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			
			$data['admin']=$this->user_model->getOneUser($login['userid']);
			
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
			
			$data['subscription']= $this->payment_model->getOneSubscription($id);
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('edit_subscription',$data);
			$this->load->view('admin_footer');	
		}
		else
		{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function update()
	{
		
		$data='';
		
		$login=$this->administrator_model->login_session();
		
		if($login['true']==1 && $login['accesslevel']=='admin'){
			
		$data['admin']=$this->user_model->getOneUser($login['userid']);
		
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];
		
			$data['purchasedOn']='';
			$data['amount']='';
			
			$this->form_validation->set_rules('purchasedOn', 'Purchase Date', 'trim|required');
			$this->form_validation->set_rules('amount', 'Amount', 'trim|required');
			
			if ($this->form_validation->run() == FALSE)
			{
				
				$data['firstname']=$this->input->post('firstname');
				$data['lastname']=$this->input->post('lastname');
				$data['email']=$this->input->post('email');
				$data['paymentId']=$this->input->post('paymentId');
				$data['purchasedOn']=$this->input->post('purchasedOn');
				$data['amount']=$this->input->post('amount');
				$data['currency']=$this->input->post('currency');
				
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('edit_subscription',$data);
				$this->load->view('admin_footer');
			}else
			{
				//Update database table
				if($this->input->post('currency') == '$'){
					$currency = '&#36;';
				}
				else{
					$currency = '&pound;';
				}
				$data = array(
						'payment_id'	=> $this->input->post('paymentId'),
						'purchasedOn' 	=> $this->input->post('purchasedOn'),
						'amount' 		=> $this->input->post('amount'),
						'currency' 		=> $currency
						);
				$this->payment_model->updateSubscription($data);
				
				$this->session->set_flashdata("success_messege","Subscription has been updated sucessfully");
				redirect('subscriptions');
			}
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
		
	}
	
	function delete(){
		
		$id = $this->uri->segment(3);
		
		$data = array(
			
				'payment_id' => $id,
				'isActive'	 => '0'
		);
		$this->payment_model->delete($data);
		$this->session->set_flashdata('success_messege','Subscription has been deleted sucessfully');
		//redirect('subscriptions');
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	
}