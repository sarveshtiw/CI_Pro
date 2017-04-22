<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Promocode extends CI_Controller {


	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('promocode_model'));
		$this->load->library('pagination');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');


	}


	function index(){

		$this->session->unset_userdata('refer_from');
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
		$header['url']=$this->uri->segment(1);
		$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
		$header['username']=$header['sess_details']->ad_username;

		$data['records']=$this->promocode_model->lists();      //// Count records
		$config['base_url'] = base_url().'index.php/promocode/index/';
		$config['total_rows'] =count($data['records']);
		$config['per_page'] = '10';
		$config['uri_segment']= 3;
		$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
		$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;

		$data['promocodes'] = $this->promocode_model->lists($page, $config['per_page']);

		$this->load->view('admin_header',$header);
		$this->load->view('admin_subheader',$header);
		$this->load->view('promocode',$data);
		$this->load->view('admin_footer');
		}
		else{
			redirect('home');
		}
	}

	function add(){
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('add_promocode');
			$this->load->view('admin_footer');
		}
		else{
			redirect('home');
		}
	}

	function insertPromocode(){

		$header['sess_details']=$this->session->userdata('sess_admin');

		$header['url']=$this->uri->segment(1);
		$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
		$header['username']=$header['sess_details']->ad_username;

		$this->form_validation->set_rules('promo_code', 'Promo Code', 'trim|required|is_unique[promoCode.promo_code]|min_length[4]');
		$this->form_validation->set_rules('value', 'Value', 'trim|required');
		$this->form_validation->set_rules('valid_from', 'valid from', 'trim|required');
		$this->form_validation->set_rules('valid_till', 'valid till', 'trim|required|callback__compare_submission_dates');


		if ($this->form_validation->run() == FALSE)
		{
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('add_promocode');
			$this->load->view('admin_footer');
		}
		else{
			$promo['promo_id']='';
			$promo['promo_code']=$this->input->post('promo_code');
			$promo['value']=$this->input->post('value');
			$promo['type']='%';
			$promo['valid_from']=date('Y-m-d',strtotime($this->input->post('valid_from')));
			$promo['valid_till']=date('Y-m-d',strtotime($this->input->post('valid_till')));
			$promo['active']=1;
			$promo['createdDate']=date('YmdHis');
			$promo['modifiedDate']=date('YmdHis');
			//print_r($promo); exit;
			$this->promocode_model->insertpromocode($promo);

			$this->session->set_flashdata('success_messege','Promo code has been added successfully');

			redirect('promocode');
		}

	}

	function _compare_submission_dates()
	{
		if(isset($_POST['valid_from']) && isset($_POST['valid_till']))
		{
			if($_POST['valid_from'] >= $_POST['valid_till'])
			{
				$this->form_validation->set_message('_compare_submission_dates', 'Please Enter Date More Than valid From Date');
				return FALSE;
			}else{
				return TRUE;
			}
		}else{
			return FALSE;
		}
	}

	function edit(){
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{

			$refer_from=$_SERVER['HTTP_REFERER'];
			$arr=explode('/',$refer_from);
			$ind=count($arr)-1;
			$ind1=count($arr)-2;
			$arr[$ind1];
			if($arr[$ind]==='promocode' or $arr[$ind1]==='index'){
				$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
			}
			$id = $this->uri->segment(3);

			$data['id']=$id;
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;

			if($id!='')
			{

				$onepromo=$this->promocode_model->getonepromo($id);

				$data['promo_code']=$onepromo->promo_code;
				$data['value']=$onepromo->value;
				$data['type']=$onepromo->type;
				$data['valid_from']=$onepromo->valid_from;
				$data['valid_till']=$onepromo->valid_till;

				$data['promo_id']=$id;
				//print_r($data);die;

				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('promocode_form',$data);
				$this->load->view('admin_footer');

			}
		}
		else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function edit_promocode(){

		$header['sess_details']=$this->session->userdata('sess_admin');

		$header['url']=$this->uri->segment(1);
		$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
		$header['username']=$header['sess_details']->ad_username;

		//$this->form_validation->set_rules('promo_code', 'Promo Code', 'trim|required|is_unique[promoCode.promo_code]|min_length[4]');
		$this->form_validation->set_rules('value', 'Value', 'trim|required');
		$this->form_validation->set_rules('valid_from', 'valid from', 'trim|required');
		$this->form_validation->set_rules('valid_till', 'valid till', 'trim|required|callback__compare_submission_dates');

		if ($this->form_validation->run() == FALSE)
		{
			$data['promo_id']=$this->input->post('promo_id');
			$data['promo_code']=$this->input->post('promo_code');
			$data['value']=$this->input->post('value');
			$data['valid_from']=$this->input->post('valid_from');
			$data['valid_till']=$this->input->post('valid_till');
			//// Load View Page
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('promocode_form',$data);
			$this->load->view('admin_footer');
		}else
		{
			$promo['promo_id']=$this->input->post('promo_id');
			$promo['promo_code']=$this->input->post('promo_code');
			$promo['value']=$this->input->post('value');
			$promo['type']='%';
			$promo['valid_from']=$this->input->post('valid_from');
			$promo['valid_till']=$this->input->post('valid_till');
			$promo['active']=1;
			$promo['createdDate']=date('YmdHis');
			$promo['modifiedDate']=date('YmdHis');
			$this->promocode_model->insertpromocode($promo);

			$this->session->set_flashdata('success_messege','Promo code has been updated successfully');

			//redirect('promocode');
			redirect($this->session->userdata('refer_from'));
		}
	}

function delete()
	{

		$id = $this->uri->segment(3);
		$save['promo_id']=$id;
		$save['active']=0;
		$save['modifiedDate']=date('YmdHis');
		$this->promocode_model->delete($save);

		$this->session->set_flashdata('success_messege','Promo Code has been deleted sucessfully');
		//redirect('promocode');
		redirect($_SERVER['HTTP_REFERER']);
	}





}
