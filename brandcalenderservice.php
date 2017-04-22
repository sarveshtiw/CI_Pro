<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Brandcalenderservice extends CI_Controller {
	
	
	public function __construct() {
		parent::__construct();
	
		$this->load->model(array('administrator_model','location_model','campaign_model','geofence_model'));
                $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
	
	}
	/* Web service of campaigns for calender */
	public function events(){
		
		$login = $this->administrator_model->front_login_session();
		
		$userid = $login->user_id;
		$usertype = $login->usertype;
		$businessId = $login->businessId;
		
		$postDate = $_POST['eventdate'];
		
		if($usertype == 8){
			$activeGroups = $this->campaign_model->getActiveAppGroups($businessId);
		
			$app_group_id = '';
			foreach($activeGroups as $group){
				$app_group_id[] = $group->app_group_id;
			}
			//echo '<pre>';
			//print_r($app_group_id); die;
			
			//$where['businessId'] = $businessId;
			$where['app_group_id'] = $app_group_id;
			//$where['date'] = $postDate;
			$allcampaigns = $this->campaign_model->getBrandAdminLivePushCampaigns($where);
			
		}
		if($usertype == 9){
			
			$groups = $this->campaign_model->getUserGroups($login->user_id);
			if(count($groups) > 0){
			foreach($groups as $group){
			
				$AppUserCampaigns[] = $group->app_group_id;
			
			}
			}else{
				$AppUserCampaigns = '';
			}
			
			$allcampaigns = $this->campaign_model->getBrandUserLivePushCampaigns($AppUserCampaigns);
			
		}
		
		echo json_encode($allcampaigns);
		
	}
	
	/* Web service of geofence for calender */
	function geofenceEvents(){
		
		$login = $this->administrator_model->front_login_session();
		
		$userid = $login->user_id;
		$usertype = $login->usertype;
		$businessId = $login->businessId;
		
		$postDate = $_GET['eventdate'];
		
		if($usertype == 6){
			
			$activeGeofences = $this->geofence_model->getActiveGeofences($businessId);
			
			$geofenceId = '';
			foreach($activeGeofences as $geofence){
				$geofenceId[] = $geofence->geofenceId;
			}
			
			$where['businessId'] = $businessId;
			$where['geofence_id'] = $geofenceId;
			$where['date'] = $postDate;
			$allGeofence = $this->geofence_model->getBusinessAdminAllGeofence($where);
			
			//echo '<pre>';
			//print_r($allGeofence);
		}
		if($usertype == 7){
			$locations = $this->location_model->getUserLocations($login->user_id);
		
			foreach($locations as $userlocation){
		
				$locationArray[] = $userlocation->locationid;
		
			}
		
			$geofences = $this->geofence_model->getBusinessUserGeofence($locationArray);
			$geofenceId = '';
			foreach($geofences as $geofence){
				$geofenceId[] = $geofence->geofenceId;
			}
			
			$where['geofence_id'] = $geofenceId;
			$where['date'] = $postDate;
			$allGeofence = $this->geofence_model->getBusinessUserAllGeofence($where);
		}
		
		echo json_encode($allGeofence);
		
	}
	
	
	
	
	
}