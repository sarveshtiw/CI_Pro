<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Questions extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	
		$this->load->helper(array('form', 'url'));
		$this->load->library(array('form_validation','session'));
		$this->load->database();
		$this->load->model(array('promocode_model','country_model','games_model','languages_model','question_model'));
		$this->load->library('pagination');
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	
			
	}
	
	function addQuestion(){
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;
			
			$data['games'] = $this->games_model->getGames();
			$data['languages'] = $this->languages_model->get_languages();
			$data['countries'] = $this->country_model->get_countries();
			
			if(isset($_POST['submit']))
			{
				$this->form_validation->set_rules('question', 'Question', 'trim|required|min_length[10]|xss_clean|is_unique[question_bank.question]');
				$this->form_validation->set_rules('answer1', 'Answer 1', 'trim|required|min_length[3]|xss_clean');
				$this->form_validation->set_rules('answer2', 'Answer 2', 'trim|required|min_length[3]|xss_clean');
				$this->form_validation->set_rules('answer3', 'Answer 3', 'trim|required|min_length[3]|xss_clean');
				$this->form_validation->set_rules('answer4', 'Answer 4', 'trim|required|min_length[3]|xss_clean');
				$this->form_validation->set_rules('hint', 'Hint', 'trim|required|xss_clean');
				$this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
				$this->form_validation->set_rules('game', 'Game', 'trim|required|xss_clean');
				$this->form_validation->set_rules('language', 'Language', 'trim|required|xss_clean');
				$this->form_validation->set_rules('country', 'Country', 'trim|required|xss_clean');
			}
			if($this->form_validation->run() == FALSE)
			{
				//Field validation failed.  User redirected to Question add page
					
				//print_r($data['packagedata']);
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('question_form',$data);
				$this->load->view('admin_footer');
			}
			else{
				//Insert Question
				$this->question_model->insertQuestion();
				redirect('questions/questionlist');
			}
			
			
		}
		else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	
	function questionlist(){
		
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;
			
			$this->load->library('pagination');
			$this->load->model('question_model');
			$config['base_url'] = base_url().'/index.php/questions/questionlist/';
			
			$config['uri_segment'] = 3;
			$config['per_page'] = 10;
			
			//sorting parameter pass in url
			$config['suffix'] = '?'.http_build_query($_GET, '', "&");
			$config['first_url'] = $config['base_url'].$config['suffix'];
			//End
			
			$data['quescountry'] = $this->question_model->questionCountry();
			$data['games'] = $this->question_model->questionGame();
			$data['language'] = $this->question_model->questionLang();
			
			$countryid=$this->input->get('country');
			$gameid=$this->input->get('game');
			$langId = $this->input->get('lang');
			$orgId = $this->input->get('org');
			
			if($countryid || $gameid || $langId || $orgId){
				$data['query_result'] = $this->question_model->listQuestion($config['per_page'], $this->uri->segment(3),$countryid, $gameid, $langId, $orgId);//pagination code
				$config['total_rows'] = count($this->question_model->listQuestion('','',$countryid, $gameid, $langId, $orgId));
				
			}else{
				$data['query_result'] = $this->question_model->listQuestion($config['per_page'], $this->uri->segment(3));//pagination code
				$config['total_rows'] = count($this->question_model->listQuestion());
			//echo $this->db->last_query(); die;
			}
			
			$this->pagination->initialize($config);
			
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('question_list', $data);
			$this->load->view('admin_footer');
		}
		else{
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function deleteQuestion()
	{
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;
			
			$this->load->model('question_model');
			$this->question_model->delete_question();
			$this->session->set_flashdata('success_messege', 'Question has been deleted successfully');
			redirect('questions/questionlist');
		}else{
			$this->session->set_flashdata('error_message','Please Login First');
			redirect('H5fgs2134vbdsgtfdsrt');
		}
	}
	
	function editQuestion()
	{
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;
			
			$data['games']=$this->games_model->getGames();
				
			$data['countries']=$this->country_model->get_countries();
				
			$data['languages'] = $this->languages_model->get_languages();
				
			$id = $this->uri->segment(3);
				
			$questions= $this->question_model->getOnequetion($id);
			//echo '<pre>'; print_r($questions);die;
			$question = $questions->question;
			$answer1 = $questions->answer1;
			$answer2 = $questions->answer2;
			$answer3 = $questions->answer3;
			$answer4 = $questions->answer4;
			$hint = $questions->hint;
			$type = $questions->type;
			$country_id = $questions->countryId;
			$language = $questions->languageId;
			$gameId = $questions->gameId;
				
			$data=array(
					'id' => $id,
					'question' 		=> $question,
					'answer1' 		=> $answer1,
					'answer2' 		=> $answer2,
					'answer3' 		=> $answer3,
					'answer4' 		=> $answer4,
					'hint' 			=> $hint,
					'type' 			=> $type,
					'country_id' 	=> $country_id,
					'language' 		=> $language,
					'gameId' 		=> $gameId,
					'countries' 	=> $data['countries'],
					'games' 		=> $data['games'],
					'languages' 	=> $data['languages'],
					
			);
				
			$this->load->view('admin_header',$header);
			$this->load->view('admin_subheader',$header);
			$this->load->view('edit_question', $data);
			$this->load->view('admin_footer');
		}else{
			redirect('H5fgs2134vbdsgtfdsrt');
	}
	}
	
	function updateQuestion(){
		$header['sess_details']=$this->session->userdata('sess_admin');
		if(count($header['sess_details'])>0 && $header['sess_details']->ad_accesslevel=='admin')
		{
			$header['url']=$this->uri->segment(1);
			$header['image']=$header['sess_details']->ad_image;       //// Get Admin Image
			$header['username']=$header['sess_details']->ad_username;
			
			$this->form_validation->set_rules('question', 'Question', 'trim|required|min_length[10]|xss_clean');
			$this->form_validation->set_rules('answer1', 'Answer 1', 'trim|required|min_length[3]|xss_clean');
			$this->form_validation->set_rules('answer2', 'Answer 2', 'trim|required|min_length[3]|xss_clean');
			$this->form_validation->set_rules('answer3', 'Answer 3', 'trim|required|min_length[3]|xss_clean');
			$this->form_validation->set_rules('answer4', 'Answer 4', 'trim|required|min_length[3]|xss_clean');
			$this->form_validation->set_rules('hint', 'Hint', 'trim|required|xss_clean');
			$this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
			$this->form_validation->set_rules('game', 'Game', 'trim|required|xss_clean');
			$this->form_validation->set_rules('language', 'Language', 'trim|required|xss_clean');
			$this->form_validation->set_rules('country', 'Country', 'trim|required|xss_clean');
			
			if($this->form_validation->run() == FALSE)
			{
			
				$id = $this->uri->segment(3);
				$data['games']=$this->games_model->getGames();
				$data['countries']=$this->country_model->get_countries();
				$data['languages'] = $this->languages_model->get_languages();
			
				if($_POST)
				{
					$question = $this->input->post('question');
					$answer1 = $this->input->post('answer1');
					$answer2 = $this->input->post('answer2');
					$answer3 = $this->input->post('answer3');
					$answer4 = $this->input->post('answer4');
					$hint = $this->input->post('hint');
					$type = $this->input->post('type');
					$organizationId = $this->input->post('organizationId');
					$country_id = $this->input->post('country');
					$language = $this->input->post('language');
					$usertype = $this->input->post('usertype');
					$gameId = $this->input->post('game');
					$age = $this->input->post('age');
				}else{
					$questions= $this->question_model->getOnequetion($id);
					//echo '<pre>'; print_r($questions);die;
					$question = $questions->question;
					$answer1 = $questions->answer1;
					$answer2 = $questions->answer2;
					$answer3 = $questions->answer3;
					$answer4 = $questions->answer4;
					$hint = $questions->hint;
					$type = $questions->type;
					
					$country_id = $questions->countryId;
					$language = $questions->languageId;
					
					$gameId = $questions->gameId;
					
				}
				$data=array(
						'id' => $id,
						'question' => $question,
						'answer1' => $answer1,
						'answer2' => $answer2,
						'answer3' => $answer3,
						'answer4' => $answer4,
						'hint' => $hint,
						'type' => $type,
						'country_id' => $country_id,
						'language' => $language,
						'gameId' => $gameId,
						'countries' => $data['countries'],
						'games' => $data['games'],
						'languages' => $data['languages']
				);
			
				$this->load->view('admin_header',$header);
				$this->load->view('admin_subheader',$header);
				$this->load->view('edit_question', $data);
				$this->load->view('admin_footer');
			}else
			{
				$this->question_model->update_question();
			
				$this->session->set_flashdata('success_messege', 'Question has been updated successfully');
				redirect('questions/questionlist');
			}
		}
		else{
			
		}
		
	}
	
}