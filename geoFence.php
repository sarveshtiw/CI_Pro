<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class GeoFence extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('user_model', 'administrator_model','location_model','geofence_model','permission_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

   function index($branchId=NULL) {
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'geoFencing';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
            $businessId = $login->businessId;
            $data['user'] = $this->user_model->getOneUser($login->user_id);
            $data['geofencePermission'] = array();
            $usertype = $header['usertype'];


            if($usertype == 6){

            	$geofences = $this->geofence_model->getBusinessAdminGeofence($businessId);

            	if(count($geofences) > 0){
            	foreach($geofences as $geofence){
            		$geofenceLocations[] = $geofence->geofence_id;
            	}
            	}else{
            		$geofenceLocations = '';
            	}

            	$locations = $this->geofence_model->getGeofenceLocations($geofenceLocations);

            	if(count($locations) > 0){
            		foreach($locations as $location){
            			$branch[] = $location->locationId;
            		}
            		/* echo '<pre>';
            		print_r($branch); die; */
            		for($i=0;$i<count($branch);$i++){
            			//foreach($branch as $b)
            			 $query_res = $this->geofence_model->getUserGeofenceLocations($branch[$i]);
            			 if(isset($query_res)&&!empty($query_res))
            			 {
            				$userlocations[]=$query_res;

            			}
            			else{
            				$userlocations = array();
            			}
            		}


            	}else{
            		$userlocations = array();
            	}
            	if($branchId == ''){
            		$data['index'] = '';
            	}else{
            		$data['index'] = $branchId;
            	}

            	$data['locations'] = $userlocations;



            }
            if($usertype == 7){
            	$locations = $this->location_model->getUserLocations($login->user_id);

            	foreach($locations as $userlocation){

            		$location['branch_id'] = $userlocation->locationid;

            		$locationArray[] = $userlocation->locationid;

            	}

            	$geofences = $this->geofence_model->getBusinessUserGeofence($locationArray);

            	if(count($geofences) > 0){
            		$geofenceData=array();
            		for($i=0;$i<count($geofences);$i++){
            		$vars=$this->geofence_model->getBusinessUserActiveGeofence($geofences[$i]->geofenceId);
            		if(!empty($vars)){
            			$geofenceData[] = $vars;
            			unset($vars);
            		}

            			//$branch[] = $geofence->locationId;
            		}
            		//echo count($geofenceData); die;
            		if(count($geofenceData) > 0){
            		for($j=0;$j<count($geofenceData);$j++){
            			$geofenceLocations[] = $geofenceData[$j]->geofence_id;
            		}

            		$a = $this->geofence_model->businessUserGeofenceLocations($locationArray,$geofenceLocations);

            		foreach($a as $b){
            			$branch[] = $b->locationId;
            		}
            		//print_r($branch); die;
            		for($k=0;$k<count($branch);$k++){
            			$userlocations[] = $this->geofence_model->getUserGeofenceLocations($branch[$k]);
            		}
            		 }else{
            			$userlocations = array();
            		}


            	}else{
            		$userlocations = array();
            	}

            	if($branchId == ''){
            		$data['index'] = '';
            	}else{
            		$data['index'] = $branchId;
            	}

            	$data['locations'] = $userlocations;
            	$data['geofencePermission'] = $this->permission_model->getGeofencePermission($login->user_id);
            }

            if($branchId == ''){
            //Show map data
            	if(count($userlocations) > 0){
            	$locationId = $userlocations[0]->branch_id;

            	$geofenceLocationMap = $this->geofence_model->getGeofenceId($locationId);

            	$geofenceId = $geofenceLocationMap->geofenceId;
            	}else{
            		$geofenceId = '';
            	}
            	$data['geofence'] = $this->geofence_model->getGeofenceData($geofenceId);
            }else{
            	$geofenceLocationMap = $this->geofence_model->getGeofenceId($branchId);


            	$geofenceId = $geofenceLocationMap->geofenceId;


            	$data['geofence'] = $this->geofence_model->getGeofenceData($geofenceId);

            }

            $this->load->view('inner_header3.0', $header);
            $this->load->view('geoFencingPage',$data);
            $this->load->view('inner_footer3.0');
        } else {
            redirect(base_url());
        }
    }

    function createGeoFence() {
    	$login = $this->administrator_model->front_login_session();
    	if ($login->active != 0) {

            $usertype = $login->usertype;
            $businessId = $login->businessId;

            $userPackage = $this->geofence_model->getUserPackageInfo($businessId);

            if (count($userPackage) > 0) {
            	$data['countTotalGeoFence'] = $userPackage->totalGeoFence;
            } else {
            	$data['countTotalGeoFence'] = 0;
            }

            $extraPackage = $this->geofence_model->getUserPackage($businessId);

            if (count($extraPackage) > 0) {
            	$data['extraGeofenceQuantity'] = $extraPackage->quantity;
            } else {
            	$data['extraGeofenceQuantity'] = 0;
            }

            if($usertype == 6){
              $location['businessId'] = $businessId;
              $location['active'] = 1;
             	$location['isDelete'] = 0;
            	$data['locations'] = $this->location_model->getLocations($location);
            }
            if($usertype == 7){
            	$locations = $this->location_model->getUserLocations($login->user_id);

            	foreach($locations as $userlocation){

            		$location['branch_id'] = $userlocation->locationid;
            		$locationArray[] = $userlocation->locationid;

            	}
            	$data['locations'] = $this->location_model->getUserlocationBranch($locationArray);
            }

        	$this->load->view('createGeoFence', $data);
      }else{
      	redirect(base_url());
      }
   }

    function saveGeofence(){
    	$login = $this->administrator_model->front_login_session();
    	$userid = $login->user_id;
    	$businessId = $login->businessId;
    	$username = $login->username;

    	 //print_r($_POST); die;
    	//echo $_FILES['imageGeofence']['size']; die;

    	if (@$_FILES['imageGeofence']['size'] > 0) {

    		// Image upload in full size in profile directory
    		$extionArray = array('jpg', 'jpeg', 'png', 'gif');

    		// Image upload in full size in profile directory

    		$uploads_dir = 'upload/status_image/full/' . $userid;
    		$mediumImagePath = 'upload/status_image/medium/' . $userid;
    		if (!is_dir($mediumImagePath)) {
    			if (mkdir($mediumImagePath, 0777, true)) {
    				$mediumpath = $mediumImagePath;
    			} else {
    				$mediumpath = $mediumImagePath;
    			}
    		} else {
    			$mediumpath = $mediumImagePath;
    		}

    		if (!is_dir($uploads_dir)) {
    			if (mkdir($uploads_dir, 0777, true)) {
    				$path = $uploads_dir;
    			} else {
    				$path = $uploads_dir;
    			}
    		} else {
    			$path = $uploads_dir;
    		}

    		$tmp_name = $_FILES["imageGeofence"]["tmp_name"];
    		$name = mktime() . $_FILES["imageGeofence"]["name"];
    		move_uploaded_file($tmp_name, "$uploads_dir/$name");

    		// image resize in medium size in medium directory
    		$this->load->library('image_lib');
    		$config['image_library'] = 'gd2';
    		$config['source_image'] = "upload/status_image/full/" . $userid . "/" . $name;
    		$config['new_image'] = 'upload/status_image/medium/' . $userid . "/" . $name;
    		$config['maintain_ratio'] = TRUE;
    		$config['width'] = 400;
    		$config['height'] = 350;
    		$this->image_lib->initialize($config);
    		$rtuenval = $this->image_lib->resize();
    		$this->image_lib->clear();

    		$notification_image = $userid . "/" . $name;
    		// Image Upload End
    	} else {
    		$notification_image = '';
    	}

    	$save['businessId'] = $businessId;
    	$save['geofenceText'] = $_POST['notification'];
    	$save['geofenceType'] = $_POST['geofenceType'];
    	$save['geofenceImage'] = $notification_image;
    	$save['geofenceRadius'] = $_POST['radius'];
    	$save['isActive'] = 1;
    	$save['createdBy'] = $userid;
    	$save['createdDate'] = date('YmdHis');

    	$lastId = $this->geofence_model->saveGeofence($save);

    	$this->geofence_model->geofenceLocationMap($lastId,$_POST['location']);

    	$extraPackage = $this->geofence_model->getUserPackage($businessId);

    	if (count($extraPackage) > 0) {
    		$extraGeofenceQuantity = $extraPackage->quantity;

    		if($extraGeofenceQuantity > 0){
    			if((count($_POST['location']) == $extraGeofenceQuantity) || (count($_POST['location']) <= $extraGeofenceQuantity)){
    				$update['businessId'] = $businessId;
    				$update['packageid'] = 6;
    				$update['quantity'] = $extraGeofenceQuantity - count($_POST['location']);
    				$this->geofence_model->UpdateExtraPackage($update);  //Update Query for Extra package
    			}
    			if(count($_POST['location']) > $extraGeofenceQuantity){

    				$newCount = count($_POST['location']) - $extraGeofenceQuantity;
    				$update['businessId'] = $businessId;
    				$update['packageid'] = 6;
    				$update['quantity'] = 0;
    				$this->geofence_model->UpdateExtraPackage($update);  //Update Query for Extra package

    				//Update Default package
    				$userPackage = $this->geofence_model->getUserPackageInfo($businessId);
    				$defaultCount = $userPackage->totalGeoFence - $newCount;

    				$updateDefault['businessId'] = $businessId;
    				$updateDefault['totalGeoFence'] = $defaultCount;
    				$this->geofence_model->UpdateDefaultPackage($updateDefault); //Update Query for Default package

    			}
    		}else{
    			$userPackage = $this->geofence_model->getUserPackageInfo($businessId);
    			$countTotalGeoFence = $userPackage->totalGeoFence;
    			$defaultCount = $userPackage->totalGeoFence - count($_POST['location']);
    			$updateDefault['businessId'] = $businessId;
    			$updateDefault['totalGeoFence'] = $defaultCount;
    			$this->geofence_model->UpdateDefaultPackage($updateDefault); //Update Query for Default package
    		}

    	} else{
    		$userPackage = $this->geofence_model->getUserPackageInfo($businessId);
    		$countTotalGeoFence = $userPackage->totalGeoFence;
    		$defaultCount = $userPackage->totalGeoFence - count($_POST['location']);
    		$updateDefault['businessId'] = $businessId;
    		$updateDefault['totalGeoFence'] = $defaultCount;
    		$this->geofence_model->UpdateDefaultPackage($updateDefault); //Update Query for Default package
    	}

    	echo 'save';
    }

    function checkGeofence(){

    	$login = $this->administrator_model->front_login_session();

    	$businessId = $login->businessId;


    	$location = explode(",", $_POST['location']);


    	$checkCreatedGeofence = $this->geofence_model->checkLocations($location);

    	if(count($checkCreatedGeofence) > 0){
    	foreach($checkCreatedGeofence as $created){
    		$locationArray[] = $created->locationId;
    	  }
    	  $checkedArray = $this->geofence_model->geofenceLocations($locationArray);
    	  for($i=0;$i<count($checkedArray);$i++){
    	  	$array[] = $checkedArray[$i]->store_name;
    	  }
    	   $checked = implode(",",$array);
    	}else{

    		$userPackage = $this->geofence_model->getUserPackageInfo($businessId);

    		if (count($userPackage) > 0) {
    			$countTotalGeoFence = $userPackage->totalGeoFence;
    		} else {
    			$countTotalGeoFence = 0;
    		}

    		$extraPackage = $this->geofence_model->getUserPackage($businessId);

    		if (count($extraPackage) > 0) {
    			$extraGeofenceQuantity = $extraPackage->quantity;
    		} else {
    			$extraGeofenceQuantity = 0;
    		}

    		$totalGeofence = $countTotalGeoFence + $extraGeofenceQuantity;

    		if(count($location) <= $totalGeofence){
    			$checked = 1;
    		}else{

    		$checked = 0;
    		}
    	}

    	echo $checked;

    }

    function deleteGeofence($geofence_id){

    	$data['geofenceId'] = $geofence_id;

    	$this->load->view('geofence_delete',$data);
    }

    public function geofencePerformance($geofence_id){
      $login   = $this->administrator_model->front_login_session();
      $user_id = $login->user_id;
      $type    = 'geofence';

    	$data['geofenceId'] = $geofence_id;
      $data['genderUsers'] = $this->geofence_model->getPerformances($type,$geofence_id,$user_id);
      $geofenceNotiPer = $this->geofence_model->getPerformancesByDate($type,$geofence_id,$user_id);
      //echo '<pre>';
      //print_r($data['genderUsers']); exit;
      $data['geofenceNotiPer'] = $geofenceNotiPer ;
    	$this->load->view('geofence_performance',$data);
    }

    function updateGeofence(){

    	$geofenceId = $_POST['geofenceId'];

    	$login = $this->administrator_model->front_login_session();

    	$businessId = $login->businessId;

    	$update['geofence_id'] = $geofenceId;
    	$update['isActive'] = 0;
    	$this->geofence_model->updateGeofence($update);

    	$locations = $this->geofence_model->getGeofenceLocations($geofenceId);

    	$extraPackage = $this->geofence_model->getUserPackage($businessId);
    	if (count($extraPackage) > 0) {
    		$extraGeofenceQuantity = $extraPackage->quantity;

    		$update1['businessId'] = $businessId;
    		$update1['packageid'] = 6;
    		$update1['quantity'] = $extraGeofenceQuantity + count($locations);
    		$this->geofence_model->UpdateExtraPackage($update1);  //Update Query for Extra package
    	}else{
    		$userPackage = $this->geofence_model->getUserPackageInfo($businessId);
    		$countTotalGeoFence = $userPackage->totalGeoFence;

    		$defaultCount = $userPackage->totalGeoFence + count($locations);
    		$updateDefault['businessId'] = $businessId;
    		$updateDefault['totalGeoFence'] = $defaultCount;
    		$this->geofence_model->UpdateDefaultPackage($updateDefault); //Update Query for Default package
    	}

    	$delete['geofenceId'] = $geofenceId;
    	$this->geofence_model->deleteGeofenceLocationMap($delete);

    	echo 1;
    }

   function demo() {
        $lat = 28.662946; // tech
        $lng = 77.221227;
        $radius = 2;
        $result = $this->db->query("SELECT  CONCAT(business_branch.latitude, ',', business_branch.longitude) as latlong, ( 6371 * acos( cos( radians({$lat}) ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians({$lng}) ) + sin( radians({$lat}) ) * sin( radians( `latitude` ) ) ) ) AS distance FROM `business_branch` HAVING distance <= {$radius} ORDER BY distance ASC");

        $firstResult = $result->result_array();
        echo '<pre>'; print_r($firstResult); exit;
        $latlngArray = array();
       for($i = 0; $i<count($firstResult);$i++){
           $latlngArray[] = $firstResult[$i]['latlong'];
           unset($firstResult[$i]['distance']);
       }
    // echo 'lastlng'; echo '<pre>'; print_r($latlngArray);
        $result1 = $this->db->query("SELECT CONCAT(bb.latitude, ',', bb.longitude) as latlong  FROM geofenceLocationMap as gl  INNER JOIN business_branch as bb ON bb.branch_id = gl.locationId " );


        $secondResult = $result1->result_array();
       // echo '<pre>'; print_r($secondResult);

        $newArraySecond = array();
        for($i = 0; $i<count($secondResult);$i++){

            $newArraySecond[] = $secondResult[$i]['latlong'];
        }

         $newArray = array();
        for($i = 0; $i<count($secondResult);$i++){

         if(in_array($secondResult[$i]['latlong'],$latlngArray)){

             $newArray[] = $secondResult[$i]['latlong'];
         }
       }
       echo '<pre>'; print_r($newArray);
        exit;

      }


}
