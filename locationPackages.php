<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class locationPackages extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session','pagination','image_lib','email'));
		$this->load->model(array('user_model','email_model','administrator_model','country_model','location_model'));
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	}

	public function index(){

		$this->session->unset_userdata('refer_from');
		//// Get Loggin Session
		$header['sess_details']=$this->session->userdata('sess_admin');

		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$data['records']=$this->location_model->get_packages();      //// Count Same User Type
			$config['base_url'] = base_url().'index.php/locationPackages/index/';
			$config['total_rows'] = count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$order_by['order_by']='createdDate';
			$order_by['sequence']="DESC";

			$data['packages']=$this->location_model->get_packages('',$page, $config['per_page'],$order_by);    //// Get List Of Same User Type
			///echo $this->db->last_query(); die;
			$data['type'] = 'consumer';

			$header['url']=$this->uri->segment(1);
			$header['consumer']=1;
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;           //// Get Admin Username

			//// Load View Pages
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('locationsPackagesList',$data);
			$this->load->view('admin_footer');
		}
		else
		{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('administrator');
		}

	}

	function locationPackagesForm()
	{
		$id = $this->uri->segment(3);

		$refer_from = $_SERVER['HTTP_REFERER'];

		$arr = explode('/',$refer_from);
		$ind = count($arr)-1;
		$ind1 = count($arr)-2;
		$arr[$ind1];

		if($arr[$ind] === 'consumer' or $arr[$ind1] === 'index'){
			$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
		}

		$header['sess_details'] = $this->session->userdata('sess_admin');

		if(count($header['sess_details']) > 0 && $header['sess_details']->ad_accesslevel == 'admin')
		{
			$header['url'] = $this->uri->segment(1);
			$data['id'] = $id;

			$data['package_name'] = '';
			$data['package_description'] = '';
			$data['num_of_locations'] = '';
			$data['amount'] = '';

			if($id!='')
			{
				$onePackage = $this->location_model->getOnePackage($id);
				$data['package_name'] = $onePackage->package_name;
				$data['package_description'] = $onePackage->package_description;
				$data['num_of_locations'] = $onePackage->num_of_locations;
				$data['amount'] = $onePackage->amount;

				$data['id']='/'.$id;
			}

			$this->form_validation->set_rules('package_name', 'Package Name', 'trim|required');
			$this->form_validation->set_rules('package_description', 'Package Description', 'trim|required');
			$this->form_validation->set_rules('num_of_locations', 'Num of Locations', 'trim|required|numeric');
			$this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric');

			$admin=$this->user_model->getOneUser($header['sess_details']->ad_userid);   //// Get Admin Details

			$header['url']=$this->uri->segment(1);
			$header['consumer']=1;
			$header['image']=$admin->image;       //// Get Admin Image
			$header['username']=$admin->username;           //// Get Admin Username

			if ($this->form_validation->run() == FALSE)
			{
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('location_package_form',$data);
				$this->load->view('admin_footer');
			}
			else
			{
				$package['id'] = $id;
				$package['package_name'] = $this->input->post('package_name');
				$package['package_description'] = $this->input->post('package_description');
				$package['num_of_locations'] = $this->input->post('num_of_locations');
				$package['amount'] = $this->input->post('amount');

				if($id == '')
				{
					$package['created_date']=date('YmdHis');
				}

				$package_id = $this->location_model->insertPackage($package);

				if($id=='')
				{
					$this->session->set_flashdata('success_messege','Package has been created');
				}else{
					$this->session->set_flashdata('success_messege','Package has been updated sucessfully');
				}
				redirect('locationPackages');
			}
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function delete()
	{
		$package_id = $this->uri->segment('3');

		$onePackage = $this->location_model->getOnePackage($package_id);

		$save['id'] = $package_id;
		$save['is_delete']=1;
		$package_id = $this->location_model->insertPackage($save);

		$this->session->set_flashdata('success_messege','Package has been deleted sucessfully');
		redirect('locationPackages');
	}

}
?>
