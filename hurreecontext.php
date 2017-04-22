<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Hurreecontext extends CI_Controller {
	
public function __construct() {
        parent::__construct();

        $this->load->library(array('form_validation', 'facebook', 'email', 'image_lib', 'user_agent', 'Mobile_Detect', 'session'));
        $this->load->model(array('user_model', 'email_model', 'country_model', 'administrator_model', 'campaign_model','social_model','businessstore_model'));
    $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
        
}
	
	
	function index(){
		
		$header['login'] = $this->administrator_model->front_login_session();
		if ($header['login']->true == 1 && $header['login']->accesslevel != '') {
			$usertype = $header['login']->usertype;
			if ($usertype == 2 || $usertype == 5) {
				 
				$this->load->helper('convertlink');
				 
				$userid = $header['login']->user_id;
				$data['loginuser'] = $header['login']->user_id;
				$data['user'] = $this->user_model->getOneUser($userid);
				$data['viewPage'] = 'hurreecontext';
				$data['campaigns'] = $this->campaign_model->getAllCampaigns($header['login']->user_id);
		
				$this->load->view('inner_header', $data);
				$this->load->view('hurree_context');
				$this->load->view('inner_footer');
			}else{
				redirect("timeline");
			}
		}else{
			$this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
			redirect("home");
		}
	}
	
	public static function ago_time($date = NULL) {
		if (empty($date)) {
			return "No date provided";
		}
	
		$periods = array("s", "m", "h", "d", "w", "month", "year", "decade");
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
	
		$now = time();
		$unix_date = strtotime($date);
	
		// check validity of date
		if (empty($unix_date)) {
			return "Bad date";
		}
	
		// is it future date or past date
		/* echo $now.' </br>' ;
		echo $unix_date ; */
		if ($now > $unix_date) {
		$difference = $now - $unix_date;
		$tense = "ago";
		} else {
		$difference = $unix_date - $now;
		//$tense         = "from now";
		$tense = "ago";
		}
	
		for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
		$difference /= $lengths[$j];
	}
	
	$difference = round($difference);
	
	/* if($difference != 1) {
	$periods[$j].= "s";
	} */
	
	$showtime = "$difference $periods[$j]";
	
	if ($periods[$j] == 'month' || $periods[$j] == 'year') {
	$showtime = date("d/m/y", strtotime($date));
    }
	
	    return $showtime;
	}
	
	
	
	
}