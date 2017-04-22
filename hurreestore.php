<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hurreestore extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper(array('form', 'url',));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('user_model','administrator_model','store_model'));
		$this->load->library('pagination');
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

			$data['records']=$this->store_model->getstoreoffers();
			$config['base_url'] = base_url().'index.php/hurreestore/index/';
			$config['total_rows'] =count($data['records']);
			$config['per_page'] = '10';
			$config['uri_segment']= 3;
			$this->pagination->initialize($config);
			$page = ($this->uri->segment(3))? $this->uri->segment(3) : 0;
			$orderby['orderby']="store.createdDate";
			$orderby['val']='DESC';
			$data['offers']=$this->store_model->getstoreoffers($page, $config['per_page'],$orderby);

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('offers',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function addoffer(){

		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['regions'] = $this->store_model->store_regions();
			//echo '<pre>';
			//print_r($data['regions']);die;
			$data['icon']='';
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('hurree_store',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function submitoffer(){
		$login=$this->administrator_model->login_session();

		if( $login['true']==1 && $login['accesslevel']=='admin')
		{
			$data['admin']=$this->user_model->getOneUser($login['userid']);

			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			//Validation
			$this->form_validation->set_rules('title', 'Title', 'trim|required');
			$this->form_validation->set_rules('coins', 'Coins', 'trim|required');
			$this->form_validation->set_rules('details', 'Details of offer', 'trim|required');
			$this->form_validation->set_rules('quantity', 'No. of offers', 'trim|required');

			if($this->form_validation->run() == FALSE)
			{
				$data['regions'] = $this->store_model->store_regions();
				//echo '<pre>';
				//print_r($data['regions']);die;
				$data['icon']='';
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('hurree_store',$data);
				$this->load->view('admin_footer');
			}
			else{

				if($_FILES['image']['size']>0){

					$uploads_dir = 'upload/store/';

					$tmp_name = $_FILES["image"]["tmp_name"];
					$name = mktime().$_FILES["image"]["name"];
					move_uploaded_file($tmp_name, "$uploads_dir/$name");

					$config['image_library'] = 'gd2';
					$config['source_image'] = 'upload/store/' . $name;
					$config['create_thumb'] = false;
					$config['maintain_ratio'] = false;
					$config['width'] = 104;
					$config['height'] = 68;
					$this->load->library('image_lib', $config);
					$this->image_lib->resize();
					$this->image_lib->clear();

					$icon=$name;

					$save['region_id']=$this->input->post('area');
					$save['item']=$this->input->post('title');
					$save['description']=$this->input->post('details');
					$save['quantity']=$this->input->post('quantity');
					$save['coins']=$this->input->post('coins');
					$save['image']=$icon;
					$save['active']=1;
					$save['createdDate']=date('YmdHis');

					//echo '<pre>';
					//print_r($save);die;

					$this->store_model->insertoffer($save);
					$this->session->set_flashdata('success_messege','Hurree Store offer has been updated sucessfully');
					redirect('hurreestore');

				}
				else{
					$data['icon']='Upload image first';
					$data['regions'] = $this->store_model->store_regions();

					$this->load->view('admin_header',$header);
					$this->load->view('admin_subheader',$header);
					$this->load->view('hurree_store',$data);
					$this->load->view('admin_footer');
				}

			}
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function delete()
	{

		$id = $this->uri->segment(3);
		$data['store_id']=$id;
		$data['active']=0;
		$data['modifiedDate']=date('YmdHis');
		$this->store_model->delete($data);

		$this->session->set_flashdata('success_messege','Hurree Store offer has been deleted sucessfully');
		//redirect('hurreestore');
		redirect($_SERVER['HTTP_REFERER']);

	}

	function editoffer(){

		$login=$this->administrator_model->login_session();
		if( $login['true']==1 && $login['accesslevel']=='admin')
		{

			$refer_from=$_SERVER['HTTP_REFERER'];

			$arr=explode('/',$refer_from);
			$ind=count($arr)-1;
			$ind1=count($arr)-2;
			$arr[$ind1];
			if($arr[$ind]==='hurreestore' or $arr[$ind1]==='index'){

				$this->session->set_userdata('refer_from', $_SERVER['HTTP_REFERER']);
			}

			$store_id =  $this->uri->segment(3);
			//$data['admin']=$this->user_model->getOneUser($login['userid']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['regions'] = $this->store_model->store_regions();
			$data['offers']=$this->store_model->getoneoffer($store_id);

			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('edit_offer',$data);
			$this->load->view('admin_footer');
		}
		else{
			$this->session->set_flashdata('alert_message','Session Has Been Expire. Please Login to continue');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}

	function updateoffer(){

		$store_id = $this->uri->segment(3);
		$data='';
		$data['offers']=array();
		$login=$this->administrator_model->login_session();

		//echo '<pre>'; print_r($_POST); /* die; */
		$this->form_validation->set_rules('item', 'Title', 'trim|required');
		$this->form_validation->set_rules('coins', 'Coins', 'trim|required');
		$this->form_validation->set_rules('details', 'Details of offer', 'trim|required');
		$this->form_validation->set_rules('quantity', 'No. of offers', 'trim|required');

		if($this->form_validation->run() == FALSE)
		{
			//$store_id =  $this->uri->segment(3);
			$mainoffers=$this->store_model->getoneoffer($store_id);

// 			echo $this->input->post('item').'viv';
			$data['offers']=(object) array(
					'item'=>$this->input->post('item'),
					'store_id'=>$mainoffers->store_id,
					'area'=>$this->input->post('area'),
					'coins'=>$this->input->post('coins'),
					'description'=>$this->input->post('details'),
					'quantity'=>$this->input->post('quantity'),
					'image'=>$this->input->post('old_image'),
					'item'=>$mainoffers->item,
					'image'=>$mainoffers->image
					);

		//	echo '<pre>'; print_r($data['offers']);
			$header['url']=$this->uri->segment(1);
			$header['image']=$login['image'];
			$header['username']=$login['username'];

			$data['regions'] = $this->store_model->store_regions();

	//		echo '<pre>'; print_r($data); die;
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('edit_offer',$data);
			$this->load->view('admin_footer');

		}else{

			if($_FILES['image']['size']>0){

				$uploads_dir = 'upload/store/';

				$tmp_name = $_FILES["image"]["tmp_name"];
				$name = mktime().$_FILES["image"]["name"];
				move_uploaded_file($tmp_name, "$uploads_dir/$name");

				$config['image_library'] = 'gd2';
				$config['source_image'] = 'upload/store/' . $name;
				$config['create_thumb'] = false;
				$config['maintain_ratio'] = false;
				$config['width'] = 104;
				$config['height'] = 68;
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				$this->image_lib->clear();

				$icon=$name;

			}
			else{
				$icon = $this->input->post('old_image');
			}

			$update['store_id']=$store_id;
			$update['item'] = $this->input->post('item');
			$update['quantity'] = $this->input->post('quantity');
			$update['coins'] = $this->input->post('coins');
			$update['image'] = $icon;
			$update['description'] = $this->input->post('details');
			$update['region_id'] = $this->input->post('area');
			$update['active'] = 1;
			$update['modifiedDate'] = date('YmdHis');

			//echo '<pre>';
			//print_r($update);
			$this->store_model->updateoffer($update);

			$this->session->set_flashdata('success_messege','Hurree Store offer has been updated sucessfully');
			//redirect('hurreestore');
			redirect($this->session->userdata('refer_from'));
		}
	}


}
