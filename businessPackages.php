<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class businessPackages extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session','pagination','image_lib','email'));
		$this->load->model(array('user_model','email_model','administrator_model','country_model','package_model'));
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	}

	public function index(){

		$id = 1;//$this->uri->segment(2);

		$header['sess_details'] = $this->session->userdata('sess_admin');

		if(count($header['sess_details']) > 0 && $header['sess_details']->ad_accesslevel == 'admin')
		{
			$header['url'] = $this->uri->segment(1);
			$data['package_id'] = $id;

			$data['beacons'] = '';
			$data['campaigns'] = '';
			$data['geoFence'] = '';
			$data['individual_campaigns'] = '';
      $data['num_of_locations'] = '';
			$data['price_gbp'] = '';
			$data['price_usd'] = '';

			if($id!='')
			{
				$onePackage = $this->package_model->getOnePackage($id);
				$data['beacons'] = $onePackage->beacons;
				$data['campaigns'] = $onePackage->campaigns;
        $data['geoFence'] = $onePackage->geoFence;
  			$data['individual_campaigns'] = $onePackage->individual_campaigns;
				$data['num_of_locations'] = $onePackage->num_of_locations;
				$data['price_gbp'] = $onePackage->price_gbp;
				$data['price_usd'] = $onePackage->price_usd;

				$data['package_id']='/'.$id;
			}

		  $this->form_validation->set_rules('beacons', 'Beacons', 'trim|required|numeric');
		  $this->form_validation->set_rules('campaigns', 'Campaigns', 'trim|required|numeric');
    	$this->form_validation->set_rules('geoFence', 'Geofence', 'trim|required|numeric');
    	$this->form_validation->set_rules('individual_campaigns', 'Individual Campaigns', 'trim|required|numeric');
			$this->form_validation->set_rules('num_of_locations', 'Num of Locations', 'trim|required|numeric');
			$this->form_validation->set_rules('price_gbp', 'Price GBP', 'trim|required|numeric');
			$this->form_validation->set_rules('price_usd', 'Price USD', 'trim|required|numeric');

			$admin=$this->user_model->getOneUser($header['sess_details']->ad_userid);   //// Get Admin Details

			$header['url']=$this->uri->segment(1);
			$header['image']=$admin->image;       //// Get Admin Image
			$header['username']=$admin->username;           //// Get Admin Username

			if ($this->form_validation->run() == FALSE)
			{
				//// Load View Page
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('business_package_form',$data);
				$this->load->view('admin_footer');
			}
			else
			{
				$package['package_id'] = $id;
				$package['beacons'] = $this->input->post('beacons');
				$package['campaigns'] = $this->input->post('campaigns');
				$package['geoFence'] = $this->input->post('geoFence');
				$package['individual_campaigns'] = $this->input->post('individual_campaigns');
				$package['num_of_locations'] = $this->input->post('num_of_locations');
				$package['price_gbp'] = $this->input->post('price_gbp');
				$package['price_usd'] = $this->input->post('price_usd');

				if($id == '')
				{
					$package['createdDate']=date('YmdHis');
				}

				$package_id = $this->package_model->insertPackage($package);

				if($package_id==1)
				{
					$this->session->set_flashdata('success_messege','Package has been updated sucessfully');
				}

				redirect('businessPackages');
			}
		}else{
			$this->session->set_flashdata('alert_message','Username Does Not Exits');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

}
?>
