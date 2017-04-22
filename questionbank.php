<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Questionbank extends CI_Controller {
	
	
	public function __construct()
	{
		parent::__construct();
			
		$this->load->helper('form');
		//$this->load->model('organization_model');
		$this->load->model(array('user_model','question_model','games_model','email_model'));
		$this->load->library('session');
		$this->load->library('form_validation');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
			
	}
	
	
	function index(){
		
		$gameid = $this->input->get('id');
		$email = $this->input->get('username');
		//$ip = $this->input->ip_address();
		//echo $ip;
		$data = $this->question_model->webServices($gameid);
		/*
		 echo $this->db->last_query();
		echo '<pre>'; print_r($data);
		die;
		*/
		
		$cnt_value=count($data);
		//echo '<pre>';print_r($data);
		$arr_questions= array();
		foreach($data as $service){
		
			$shuffle = array(
					html_entity_decode($service->answer1, ENT_QUOTES, "UTF-8"),
					html_entity_decode($service->answer2, ENT_QUOTES, "UTF-8"),
					html_entity_decode($service->answer3, ENT_QUOTES, "UTF-8"),
					html_entity_decode($service->answer4, ENT_QUOTES, "UTF-8")
			);
			//echo '<pre>';print_r($shuffle);die;
		
			$orig = array_flip($shuffle);
			shuffle($shuffle);
			foreach($shuffle AS $key=>$n)
			{
				$data[$n] = $orig[$n];
			}
			//echo '<pre>'; print_r(array_flip($data));
		
			$keys = array_keys($shuffle);
			shuffle($keys);
			$random = array();
			foreach ($keys as $key) {
				$random[$key] = $shuffle[$key];
			}
			//echo '<pre>';print_r($random);
		
			$correct = html_entity_decode($service->answer1, ENT_QUOTES, "UTF-8");
			$position = array_search($correct, $random);
			//echo $position;die;
			$arr_question=array(
					array(
							html_entity_decode($service->question, ENT_QUOTES, "UTF-8"),
							$position,
							$service->type
					),
					$random,
					array(
							html_entity_decode($service->hint, ENT_QUOTES, "UTF-8")
					)
		
			);
			$arr_questions[]=$arr_question;
			//print_r($arr_question);
		}
		
		$arr_final=array(
				"c2array"=> true,
				"size"=> array(
						$cnt_value,
						3,
						4
				),
				"data"=>$arr_questions
		);
		//echo '<pre>'; print_r($arr_final);
		$web_services = json_encode($arr_final);
		print_r($web_services);
	}
	
	
	
}