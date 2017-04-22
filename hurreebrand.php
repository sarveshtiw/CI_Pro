<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Hurreebrand extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('salesforce_helper', 'hubspot_helper'));
        $this->load->model(array('hurreebrand_model','hubSpot_model','contact_model','inapp_model','brand_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');

    }


    function login_signup(){

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);

    	$app_group_key = $params->app_group_key;
    	$app_group_apps_key = $params->app_group_apps_key;

    	if (isset($app_group_key) && $app_group_key != '' && isset($app_group_apps_key) && $app_group_apps_key != '') {


    		$keys['app_group_key'] = $app_group_key;
    		$keys['app_group_apps_key'] = $app_group_apps_key;
    		$AppKeys = $this->hurreebrand_model->checkAppKeys($keys);

    		if(count($AppKeys) > 0){

    			$user['email'] = trim($params->email);
    			$user['app_group_id'] = $AppKeys->appGroupId;
    			$user['app_group_apps_id'] = $AppKeys->appGroupAppsId;

    			$existUser = $this->hurreebrand_model->checkExistUser($user);

    			if(count($existUser) > 0){

    				//Login

    				$data['UUID'] = $params->UUID;
    				$data['external_user_id'] = $existUser->external_user_id;

    				$existDevice = $this->hurreebrand_model->existDevice($data);
    				if(count($existDevice) > 0){

    					//Update
    					$update['active_device_id'] = $existDevice->active_device_id;
    					$update['isActive'] = 1;
    					$update['dateTime'] = date('YmdHis');
    					$update['logoutTime'] = '';
    					$update['push_notification_token'] = $params->push_notification_token;
    					$this->hurreebrand_model->updateDevice($update);



    					//$success = 1;
    					//$statusMessage = 'Login successfully';

    				}else{
    					//Insert

    					$device['active_device_id'] = '';
    					$device['external_user_id'] = $existUser->external_user_id;
    					$device['UUID'] = trim($params->UUID);
    					$device['version'] = $params->version;
    					$device['device_model'] = $params->device_model;
    					$device['push_notification_token'] = $params->push_notification_token;
    					$device['sdk_version'] = $params->sdk_version;
    					$device['isActive'] = 1;
    					$device['dateTime'] = date('YmdHis');
    					$device['logoutTime'] = '';

    					$this->hurreebrand_model->device_registration($device);

    					//$success = 1;
    					//$statusMessage = 'Login successfully';
    				}

    				$updateUserDetails['external_user_id'] = $existUser->external_user_id;
    				$updateUserDetails['loginDate'] = $params->loginDate;
    				$updateUserDetails['timezone'] = $params->timezone;
    				$this->hurreebrand_model->updateExternalUser($updateUserDetails);

    				$success = 'success';
    				$statusMessage = 'Login successfully';


    			}else{

    				//Signup

    				$UUID = trim($params->UUID);

    				$check['app_group_id'] = $AppKeys->appGroupId;
    				$check['app_group_apps_id'] = $AppKeys->appGroupAppsId;
    				$check['firstName'] = $UUID;
    				$checkUUIDSignUp = $this->hurreebrand_model->checkUUIDSignUp($check);

    				if(count($checkUUIDSignUp) > 0){

    					$update['external_user_id'] = $checkUUIDSignUp->external_user_id;
    					$update['exteranal_app_user_id'] = $params->external_app_user_id;
    					$update['app_group_id'] = $AppKeys->appGroupId;
    					$update['app_group_apps_id'] = $AppKeys->appGroupAppsId;
    					$update['firstName'] = $params->firstName;
    					$update['lastName'] = $params->lastName;
    					$update['email'] = trim($params->email);
    					$update['phoneNumber'] = $params->phoneNumber;
    					$update['user_image'] = $params->userImage;
    					$update['gender'] = $params->gender;
    					$update['date_of_birth'] = $params->date_of_birth;
    					$update['loginDate'] = $params->loginDate;
    					$update['timezone'] = $params->timezone;
    					$update['createdDate'] = date('YmdHis');

    					$this->hurreebrand_model->updateExternalUser($update);

    					$device['active_device_id'] = '';
    					$device['external_user_id'] = $checkUUIDSignUp->external_user_id;
    					$device['UUID'] = trim($params->UUID);
    					$device['version'] = $params->version;
    					$device['device_model'] = $params->device_model;
    					$device['push_notification_token'] = $params->push_notification_token;
    					$device['sdk_version'] = $params->sdk_version;
    					$device['isActive'] = 1;
    					$device['dateTime'] = date('YmdHis');
    					$device['logoutTime'] = '';

    					$this->hurreebrand_model->device_registration($device);
    				}
    				else{
    					$signup['external_user_id'] = '';
    					$signup['exteranal_app_user_id'] = $params->external_app_user_id;
    					$signup['app_group_id'] = $AppKeys->appGroupId;
    					$signup['app_group_apps_id'] = $AppKeys->appGroupAppsId;
    					$signup['firstName'] = $params->firstName;
    					$signup['lastName'] = $params->lastName;
    					$signup['email'] = trim($params->email);
    					$signup['phoneNumber'] = $params->phoneNumber;
    					$signup['user_image'] = $params->userImage;
    					$signup['gender'] = $params->gender;
    					$signup['date_of_birth'] = $params->date_of_birth;
    					$signup['loginDate'] = $params->loginDate;
    					$signup['timezone'] = $params->timezone;
    					$signup['createdDate'] = date('YmdHis');

    					$insert_id = $this->hurreebrand_model->signup($signup);

    					$device['active_device_id'] = '';
    					$device['external_user_id'] = $insert_id;
    					$device['UUID'] = trim($params->UUID);
    					$device['version'] = $params->version;
    					$device['device_model'] = $params->device_model;
    					$device['push_notification_token'] = $params->push_notification_token;
    					$device['sdk_version'] = $params->sdk_version;
    					$device['isActive'] = 1;
    					$device['dateTime'] = date('YmdHis');
    					$device['logoutTime'] = '';

    					$this->hurreebrand_model->device_registration($device);
    				}





    				$success = 'success';
    				$statusMessage = 'Registration successfully done';
    			}


    		}else{
    			$success = 'error';
    			$statusMessage = "Error occoured. App group key and app key are not valid";
    		}


    	}else{
    		$success = 'error';
    		$statusMessage = "Error occoured. App group key and app key can't be blank";
    	}



    	$response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => $success,
    					"statusMessage" => $statusMessage,
    			)
    	);

    	echo json_encode($response);


    }

    function logout(){

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);

    	$app_group_key = $params->app_group_key;
    	$app_group_apps_key = $params->app_group_apps_key;

    	if (isset($app_group_key) && $app_group_key != '' && isset($app_group_apps_key) && $app_group_apps_key != '') {

    		$keys['app_group_key'] = $app_group_key;
    		$keys['app_group_apps_key'] = $app_group_apps_key;
    		$AppKeys = $this->hurreebrand_model->checkAppKeys($keys);

    		if(count($AppKeys) > 0){

    			$data['UUID'] = $params->UUID;
    			$existDevice = $this->hurreebrand_model->activeDevice($data);

    			if(count($existDevice) > 0){
    				//Update
    				$update['active_device_id'] = $existDevice->active_device_id;
    				$update['isActive'] = 0;
    				$update['logoutTime'] = date('YmdHis');
    				$this->hurreebrand_model->updateDevice($update);

    				$success = 1;
    				$statusMessage = 'Logout successfully';
    			}else{
    				$success = 0;
    				$statusMessage = "Error occoured. Invalid device UUID";
    			}
    		}else{

    			$success = 0;
    			$statusMessage = "Error occoured. App group key and app key are not valid";
    		}

    	}else{
    		$success = 0;
    		$statusMessage = "Error occoured. App group key and app key can't be blank";
    	}
    	$response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => "success",
    					"statusMessage" => $statusMessage,
    			)
    	);

    	echo json_encode($response);
    }

    function addEvents(){

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);
      $eResponce = '';

    	$app_group_key = $params->app_group_key;
    	$app_group_apps_key = $params->app_group_apps_key;

    	if (isset($app_group_key) && $app_group_key != '' && isset($app_group_apps_key) && $app_group_apps_key != '') {

    		$keys['app_group_key'] = $app_group_key;
    		$keys['app_group_apps_key'] = $app_group_apps_key;
    		$AppKeys = $this->hurreebrand_model->checkAppKeys($keys);

    		if(count($AppKeys) > 0){

    			$user['email'] = $params->email;
    			$user['app_group_id'] = $AppKeys->appGroupId;
    			$user['app_group_apps_id'] = $AppKeys->appGroupAppsId;

    			$existUser = $this->hurreebrand_model->checkExistUser($user);
    			//print_r($existUser->external_user_id); die;
    			if(count($existUser) > 0){

    				$data['UUID'] = $params->UUID;
    				$data['external_user_id'] = $existUser->external_user_id;

    				$existDevice = $this->hurreebrand_model->existDevice($data);

    				if(count($existDevice) > 0){
    					if(array_key_exists('events',$params)){
    					$events = $params->events;
    					foreach($events as $event){

    						$insert['external_user_id'] = $existUser->external_user_id;
    						$insert['app_group_apps_id'] = $AppKeys->appGroupAppsId;
    						$insert['active_device_id'] = $existDevice->active_device_id;
    						$insert['screenName'] =  $event->eventScreen;
    						$insert['eventName'] = $event->eventName;
    						$insert['eventDate'] = $event->eventDate;
    						$insert['eventType'] = $event->eventType;
    						$insert['createdDate'] = date('YmdHis');

    						$insert_id = $this->hurreebrand_model->saveEvent($insert);

    						//Update latitude and longitude
    						$update['external_user_id'] = $existUser->external_user_id;
    						$update['latitude'] = $params->latitude;
    						$update['longitude'] = $params->longitude;

    						$this->hurreebrand_model->updateExternalUser($update);

                            $appGroupDate = $this->hurreebrand_model->getBusinessIdByAppGroupId($AppKeys->appGroupId);
                            if(count($appGroupDate) > 0){
                                $businessId = $appGroupDate->businessId;
                                $brandUsers = $this->hurreebrand_model->getUserByBusinessId($businessId);
                                //echo print_r($brandUsers); die;
                                if(count($brandUsers) > 0){
                                    foreach($brandUsers as $brandUser){
                                    $this->load->model('brand_model');
                                    $hubspot = $this->brand_model->getHubSpotDetails($brandUser->user_Id);
                                    //print_r($hubspot); die;
                                    if($hubspot->on_off == 1){

                                    //$hubDetails = $this->gethubspotDetails($brandUser->user_Id);
                                    $access_token = $hubspot->accress_token;
                                    $portalId = $hubspot->portalId;
                                    // Get Contact Vid form hubspot
                                    $vid = getOneContactDetailsHibspot($params->email, $access_token, $portalId);

                                        if($vid != 'false'){
                                            /*if($event->eventType == 'contactNote'){
                                                $eventText = $event->eventName.': '.$evt->noteText;
                                            }else{
                                                $eventText = $evt->eventName;
                                            }*/
                                            $request = array
                                            (
                                                    "engagement" => array (
                                                            "active" => true,
                                                            "type" => "NOTE"
                                                    ),
                                                    "associations" => array
                                                    (
                                                            "contactIds" => Array
                                                            (
                                                                    "0" => $vid
                                                            ),
                                                            "companyIds" => Array
                                                            (
                                                            ),
                                                            "dealIds" => Array
                                                            (
                                                            )
                                                    ),
                                                    "metadata" => array
                                                    (
                                                            "body" => $existUser->firstName.' '.$existUser->lastName.' ' .$event->eventName
                                                    )
                                            );
                                            $eResponce = createEnganementHubspot($vid, $request, $access_token, $portalId);

                                        }
                                    }

                                    } // End foreach($brandUsers as $brandUser){

                                        if($eResponce == true )
                                            {
                                                $save['eventId']= $insert_id;
                                                $save['isExportHubspot'] = 1;
                                                $this->contact_model->saveEvent($save);
                                            }

                                }  //if(count($brandUsers) > 0){

                            } //if(count($appGroupDate) > 0){


    					}  //End foreach
    					}  //End if(array_key_exists('events',$params))


    					if(array_key_exists('purchase',$params)){
    						$purchases = $params->purchase;
	    					if(count($purchases) > 0){
	    						foreach($purchases as $purchase){
			    					$savePurchase['external_user_id'] = $existUser->external_user_id;   //$existUser->external_user_id
			    					$savePurchase['identifier'] = $purchase->purchaseIdentifier;
			    					$savePurchase['currencyCode'] = $purchase->currencyCode;
			    					$savePurchase['price'] = $purchase->purchasePrice;
			    					$savePurchase['quantity'] = $purchase->purchaseQuantity;
                                    $savePurchase['purchaseDate'] = $purchase->purchaseDate;

			    					$this->hurreebrand_model->savePurchase($savePurchase);

                                    //Export events to Hubspot

                    $this->load->model('brand_model');
                    $externalUserRow = $this->brand_model->getExternalUserRowById($existUser->external_user_id);
                    if(count($externalUserRow) > 0){
                      $activeDeviceRow = $this->brand_model->getLastActiveDeviceIdByExternalUserId($existUser->external_user_id);
                      if(count($activeDeviceRow) > 0){
                        $active_device_id = $activeDeviceRow->active_device_id;
                      }else{
                        $active_device_id = 0;
                      }
                      $saveEvent['external_user_id'] = $externalUserRow->external_user_id;
                      $saveEvent['app_group_apps_id'] = $externalUserRow->app_group_apps_id;
                      $saveEvent['active_device_id'] = $active_device_id;
                      $saveEvent['screenName'] = $purchase->purchaseIdentifier;
                      $saveEvent['eventName'] = 'Purchased '.$purchase->purchaseIdentifier.',  No. of quantity : '. $purchase->purchaseQuantity.', Pay amount : '.$purchase->purchasePrice . ' ' . $purchase->currencyCode;
                      $saveEvent['eventDate'] = $purchase->purchaseDate;
                      $saveEvent['eventType'] = 'Purchaselog';
                      $saveEvent['isExportHubspot'] = 0;
                      $saveEvent['isDelete'] = 0;
                      $saveEvent['createdDate'] = date('YmdHis');

                      $lastInsert_id = $this->brand_model->saveEventsHistory($saveEvent);

                      //Export events to Hubspot
                      $appGroupDate = $this->hurreebrand_model->getBusinessIdByAppGroupId($AppKeys->appGroupId);
                      if(count($appGroupDate) > 0){
                      	$businessId = $appGroupDate->businessId;
                      	$brandUsers = $this->hurreebrand_model->getUserByBusinessId($businessId);
                      	//echo print_r($brandUsers); die;
                      	if(count($brandUsers) > 0){
                      		foreach($brandUsers as $brandUser){
                      			$this->load->model('brand_model');
                      			$hubspot = $this->brand_model->getHubSpotDetails($brandUser->user_Id);
                      			//print_r($hubspot); die;
                      			if($hubspot->on_off == 1){

                      				//$hubDetails = $this->gethubspotDetails($brandUser->user_Id);
                      				$access_token = $hubspot->accress_token;
                      				$portalId = $hubspot->portalId;
                      				// Get Contact Vid form hubspot
                      				$vid = getOneContactDetailsHibspot($params->email, $access_token, $portalId);

                      				if($vid != 'false'){
                      					/*if($event->eventType == 'contactNote'){
                      					 $eventText = $event->eventName.': '.$evt->noteText;
                      					 }else{
                      					 $eventText = $evt->eventName;
                      					 }*/
                      					$request = array
                      					(
                      							"engagement" => array (
                      									"active" => true,
                      									"type" => "NOTE"
                      							),
                      							"associations" => array
                      							(
                      									"contactIds" => Array
                      									(
                      											"0" => $vid
                      									),
                      									"companyIds" => Array
                      									(
                      									),
                      									"dealIds" => Array
                      									(
                      									)
                      							),
                      							"metadata" => array
                      							(
                      									"body" => $existUser->firstName.' '.$existUser->lastName.' ' .'Purchased '.$purchase->purchaseIdentifier.',  No. of quantity : '. $purchase->purchaseQuantity.', Pay amount : '.$purchase->purchasePrice . ' ' . $purchase->currencyCode
                      							)
                      					);
                      					$eResponce = createEnganementHubspot($vid, $request, $access_token, $portalId);

                      				}
                      			}

                      		} // End foreach($brandUsers as $brandUser){

                      		if($eResponce == true )
                      		{
                      			$save['eventId']= $lastInsert_id;
                      			$save['isExportHubspot'] = 1;
                      			$this->contact_model->saveEvent($save);
                      		}

                      	}  //if(count($brandUsers) > 0){

                      } //if(count($appGroupDate) > 0){

                    }
	    						}
	    					}
    					}
    					$success = 1;
    					$statusMessage = "Events are added successfully";

    					/* if(isset($eventName) && $eventName != '' && isset($eventDate) && $eventDate != ''){

    						$event['external_user_id'] = $existUser->external_user_id;
    						$event['app_group_apps_id'] = $AppKeys->appGroupAppsId;
    						$event['active_device_id'] = $existDevice->active_device_id; //screenName
    						$event['screenName'] = $screenName;
    						$event['eventName'] = $eventName;
    						$event['eventDate'] = $eventDate;
    						$event['createdDate'] = date('YmdHis');

    						$insert_id = $this->hurreebrand_model->saveEvent($event);

    						$success = 1;
    						$statusMessage = "Event is added successfully";

    					}else{
    						$success = 0;
    						$statusMessage = "Error occoured. Event and date are mandatory";
    					} */

    				}else{
    					$success = 0;
    					$statusMessage = "Error occoured. Device not found";
    				}

    			}else{
    				$success = 0;
    				$statusMessage = "Error occoured. User not exist";
    			}

    		}else{
    			$success = 0;
    			$statusMessage = "Error occoured. App group key and app key are not valid";
    		}

    	}else{
    		$success = 0;
    		$statusMessage = "Error occoured. App group key and app key can't be blank";
    	}

    	$response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => "success",
    					"statusMessage" => $statusMessage,
    			)
    	);

    	echo json_encode($response);
    }

    function crashreport(){

    	$app_group_key = $_POST['app_group_key'];
    	$app_group_apps_key = $_POST['app_group_apps_key'];
    	$UUID = $_POST['UUID'];
    	$os_Version = $_POST['os_Version'];
    	$reportDate = $_POST['reportDate'];
    	//$_FILES['fileUpload']['size'];


    	if (isset($app_group_key) && $app_group_key != '' && isset($app_group_apps_key) && $app_group_apps_key != '') {

    		$keys['app_group_key'] = $app_group_key;
    		$keys['app_group_apps_key'] = $app_group_apps_key;
    		$AppKeys = $this->hurreebrand_model->checkAppKeys($keys);

    		if(count($AppKeys) > 0){

    			$user['email'] = $_POST['email'];
    			$user['app_group_id'] = $AppKeys->appGroupId;
    			$user['app_group_apps_id'] = $AppKeys->appGroupAppsId;

    			$existUser = $this->hurreebrand_model->checkExistUser($user);
    			if(count($existUser) > 0){
    			//$_FILES['fileUpload']['size'];

    			$uploads_dir = 'upload/crashReports';

    			if (!is_dir($uploads_dir)) {
    				if (mkdir($uploads_dir, 0777, true)) {
    					$path = $uploads_dir;
    				} else {
    					$path = $uploads_dir;
    				}
    			} else {
    				$path = $uploads_dir;
    			}

    			$tmp_name = $_FILES["fileUpload"]["tmp_name"];
    			$name = mktime() . $_FILES["fileUpload"]["name"];
    			move_uploaded_file($tmp_name, "$uploads_dir/$name");
    			$ext = strtolower(pathinfo($_FILES["fileUpload"]["name"], PATHINFO_EXTENSION));

    			$crashReportFile = $name;

    			$save['external_user_id'] = $existUser->external_user_id;
    			$save['app_group_key'] = $AppKeys->appGroupId;
    			$save['app_group_apps_key'] = $AppKeys->appGroupAppsId;
    			$save['crashReportFile'] = $crashReportFile;
    			$save['UUID'] = $_POST['UUID'];
    			$save['os_Version'] = $_POST['os_Version'];
    			$save['reportDate'] = $_POST['reportDate'];
    			$save['createdDate'] = date('YmdHis');

    			$last_insert_id = $this->hurreebrand_model->saveCrashReports($save);

    			$external_user_id = $existUser->external_user_id;

          if(!empty($external_user_id)){
              $this->load->model('brand_model');
              $crashReportRow = $this->brand_model->getCrashReportRowById($last_insert_id);
              $externalUserRow = $this->brand_model->getExternalUserRowById($external_user_id);
              if(count($externalUserRow) > 0){
                $activeDeviceRow = $this->brand_model->getLastActiveDeviceIdByExternalUserId($external_user_id);
                if(count($activeDeviceRow) > 0){
                  $active_device_id = $activeDeviceRow->active_device_id;
                }else{
                  $active_device_id = 0;
                }
                $saveEvent['external_user_id'] = $externalUserRow->external_user_id;
                $saveEvent['app_group_apps_id'] = $externalUserRow->app_group_apps_id;
                $saveEvent['active_device_id'] = $active_device_id;
                $saveEvent['screenName'] = 'crash Report';
                $crash_file_name = $crashReportRow->crashReportFile;
                $file_url =  "<a href='".base_url()."upload/crashReports/$crash_file_name' target='_blank'> - File attachments</a>";
                $saveEvent['eventName'] = 'triggerAction crash Reports '.$file_url;
                $saveEvent['eventDate'] = $reportDate;
                $saveEvent['eventType'] = 'crashReports';
                $saveEvent['isExportHubspot'] = 0;
                $saveEvent['isDelete'] = 0;
                $saveEvent['createdDate'] = date('YmdHis');

                $this->brand_model->saveEventsHistory($saveEvent);
              }
          }

    			$success = 1;
    			$statusMessage = "Crash report is added successfully";
    		}else{
    			$success = 0;
    			$statusMessage = "Error occoured. User not exist";
    			}
    		}else{
    			$success = 0;
    			$statusMessage = "Error occoured. App group key and app key are not valid";
    		}

    	}else{
    		$success = 0;
    		$statusMessage = "Error occoured. App group key and app key can't be blank";
    	}

    	$response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => "success",
    					"statusMessage" => $statusMessage,
    			)
    	);

    	echo json_encode($response);

    }

    function launchApp(){

    	$json = file_get_contents('php://input');
    	$params = json_decode($json);

    	$app_group_key = $params->app_group_key;
    	$app_group_apps_key = $params->app_group_apps_key;

    	if (isset($app_group_key) && $app_group_key != '' && isset($app_group_apps_key) && $app_group_apps_key != '') {

    		$keys['app_group_key'] = $app_group_key;
    		$keys['app_group_apps_key'] = $app_group_apps_key;
    		$AppKeys = $this->hurreebrand_model->checkAppKeys($keys);

    		if(count($AppKeys) > 0){

    			$UUID = trim($params->UUID);
    			$existUUID = $this->hurreebrand_model->checkUUID($UUID);
    			if(count($existUUID) > 0){
    				//Exist UUID in database
    				$status = 'error';
    				$statusMessage = "UUID already exist";
    			}else{
    				// Insert UUID in external_users table in fisrtname column
    				$signup['app_group_id'] = $AppKeys->appGroupId;
    				$signup['app_group_apps_id'] = $AppKeys->appGroupAppsId;
    				$signup['firstName'] = $UUID;
    				$signup['loginDate'] = $params->loginDate;
    				$insert_id = $this->hurreebrand_model->signup($signup);

    				$status = 'success';
    				$statusMessage = "Success. Launch event logged";
    			}

    		}else{
    			$status = 'error';
    			$statusMessage = "App group key and app key are not valid";

    		}

    	}else{
    		$status = 'error';
    		$statusMessage = "App group key and app key can't be blank";
    	}

    	$response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => $status,
    					"statusMessage" => $statusMessage,
    			)
    	);

    	echo json_encode($response);

    }

    function gethubspotDetails ($userId)
    {
        $portalId = $this->session->userdata('hubPortalId');

        $hwhere ['userid'] = $userId;
        //$hwhere['portalId'] = $portalId;
        $hwhere ['isActive'] = 1;
        $select = 'userHubSpotId, refresh_token, portalId, accress_token';
        $hubDetails = $this->hubSpot_model->getHubSpotDetails($select, $hwhere);
        return $hubDetails;
    }


    	public function getInAppMessaging(){
    			$json = file_get_contents('php://input');
    			$params = json_decode($json);

    			$app_group_key = $params->app_group_key;
    			$app_group_apps_key = $params->app_group_apps_key;
    			$notification_id = $params->notification_id;
    			$inAppMessagingId = $params->inAppMessagingId;
    			$inAppArr = array();

    			if (isset($app_group_key) && $app_group_key != '' && isset($app_group_apps_key) && $app_group_apps_key != '') {

        		$keys['app_group_key'] = $app_group_key;
        		$keys['app_group_apps_key'] = $app_group_apps_key;
    				$this->load->model('hurreebrand_model');
        		$AppKeys = $this->hurreebrand_model->checkAppKeys($keys);

        		if(count($AppKeys) > 0){

        			$data['UUID'] = $params->UUID;
        			$existDevice = $this->hurreebrand_model->activeDevice($data);

        			if(count($existDevice) > 0){
                $userId = $existDevice->external_user_id;
                $userRow = $this->brand_model->getUserRowByUserId($userId);
                $firstname = "";
                $lastname = "";
                $gender = "";
                $email = "";
                $phoneNumber = "";
                $company = "";
                $date_of_birth = "";
                $timezone = "";
                $last_used_app_date = "";
                $most_recent_app_version = "";
                $username = "";

                $inAppRow = $this->inapp_model->getCampaign($inAppMessagingId);
                if(count($userRow) > 0){
                  $firstname = $userRow->firstName;
                  $lastname = $userRow->lastName;
                  $gender = $userRow->gender;
                  $email = $userRow->email;
                  $phoneNumber = $userRow->phoneNumber;
                  $company = $userRow->company;
                  $date_of_birth = $userRow->date_of_birth;
                  $timezone = $userRow->timezone;
                  $last_used_app_date = $existDevice->dateTime;
                  $most_recent_app_version = $existDevice->sdk_version;
                  $campaignName = $inAppRow->campaignName;
                }
    						if(count($inAppRow) > 0){
                  $custom_html_url = "";
                  $image = '';
                  $fontawesome_icon_img  = '';
                  if(!empty($inAppRow->image)){
                    $image = base_url().'upload/in_app/'.$inAppRow->image;
                  }
                  if(!empty($inAppRow->image_url)){
                    $image = $inAppRow->image_url;
                  }
                  if(!empty($inAppRow->custom_html)){
                    $custom_html_url =  base_url().'upload/customhtml/'.$inAppRow->custom_html;
                  }
                  if(!empty($inAppRow->fontawesome_icon_img)){
                    $fontawesome_icon_img = base_url().'upload/in_app/fontIcons/'.$inAppRow->fontawesome_icon_img;
                  }

                  $push_title = $inAppRow->header;

        					$push_title = str_replace('{{${date_of_birth}}}', $date_of_birth, $push_title);
        					$push_title = str_replace('{{${company}}}', $company, $push_title);
        					$push_title = str_replace('{{${email_address}}}', $email, $push_title);
        					$push_title = str_replace('{{${first_name}}}', $firstname, $push_title);
        					$push_title = str_replace('{{${last_name}}}', $lastname, $push_title);
        					$push_title = str_replace('{{${gender}}}', $gender, $push_title);
        					$push_title = str_replace('{{${last_used_app_date}}}', $last_used_app_date, $push_title);
        					$push_title = str_replace('{{${most_recent_app_version}}}', $most_recent_app_version, $push_title);
        					$push_title = str_replace('{{${phone_number}}}', $phoneNumber, $push_title);
        					$push_title = str_replace('{{${time_zone}}}', $timezone, $push_title);
        					$push_title = str_replace('{{${username}}}', $username, $push_title);
        					$push_title = str_replace('{{campaign.${name}}}', $campaignName, $push_title);

                  $push_message = $inAppRow->body;

                  $push_message = str_replace('{{${date_of_birth}}}', $date_of_birth, $push_message);
                  $push_message = str_replace('{{${email_address}}}', $email, $push_message);
                  $push_message = str_replace('{{${first_name}}}', $firstname, $push_message);
                  $push_message = str_replace('{{${last_name}}}', $lastname, $push_message);
                  $push_message = str_replace('{{${gender}}}', $gender, $push_message);
                  $push_message = str_replace('{{${last_used_app_date}}}', $last_used_app_date, $push_message);
                  $push_message = str_replace('{{${most_recent_app_version}}}', $most_recent_app_version, $push_message);
                  $push_message = str_replace('{{${phone_number}}}', $phoneNumber, $push_message);
                  $push_message = str_replace('{{${time_zone}}}', $timezone, $push_message);
                  $push_message = str_replace('{{${username}}}', $username, $push_message);
                  $push_message = str_replace('{{campaign.${name}}}', $campaignName, $push_message);

    							$inAppArr = array(
    									"app_group_id" => $inAppRow->app_group_id,
    									"campaignName" => $inAppRow->campaignName,
    									"message_type" => $inAppRow->message_type,
    									"layout" => $inAppRow->layout,
    									"device_orientation" => $inAppRow->device_orientation,
    									"device_type" => $inAppRow->device_type,
    									"header" => $push_title,
    									"body" => $push_message,
    									"header_text_color" => $inAppRow->header_text_color,
    									"header_text_opacity" => $inAppRow->header_text_opacity,
    									"body_text_color" => $inAppRow->body_text_color,
    									"body_text_opacity" => $inAppRow->body_text_opacity,
    									"background_color" => $inAppRow->background_color,
    									"background_color_opacity" => $inAppRow->background_color_opacity,
    									"frame_color" => $inAppRow->frame_color,
    									"frame_color_opacity" => $inAppRow->frame_color_opacity,
    									"on_click_behavior" => $inAppRow->on_click_behavior,
    									"button1_text" => $inAppRow->button1_text,
    									"button1_customUrl" => $inAppRow->button1_customUrl,
    									"button1_redirectUrl" => $inAppRow->button1_redirectUrl,
    									"button1_background_color" => $inAppRow->button1_background_color,
    									"button1_background_color_opacity" => $inAppRow->button1_background_color_opacity,
    									"button1_text_color" => $inAppRow->button1_text_color,
    									"button1_text_color_opacity" => $inAppRow->button1_text_color_opacity,
    									"button2_text" => $inAppRow->button2_text,
    									"button2_customUrl" => $inAppRow->button2_customUrl,
    									"button2_redirectUrl" => $inAppRow->button2_redirectUrl,
    									"button2_background_color" => $inAppRow->button2_background_color,
    									"button2_background_color_opacity" => $inAppRow->button2_background_color_opacity,
    									"button2_text_color" => $inAppRow->button2_text_color,
    									"button2_text_color_opacity" => $inAppRow->button2_text_color_opacity,
    									"message_close" => $inAppRow->message_close,
    									"image_type" => $inAppRow->image_type,
    									"image_url" => $image,
    									"fontawesome_icon" => $fontawesome_icon_img,
    									"fontawesome_background_color" => $inAppRow->fontawesome_background_color,
    									"fontawesome_background_opacity" => $inAppRow->fontawesome_background_opacity,
    									"fontawesome_border_color" => $inAppRow->fontawesome_icon_color,
    									"fontawesome_border_color_opacity" => $inAppRow->fontawesome_icon_color_opacity,
    									"text_alignment" => $inAppRow->text_alignment,
    									"closing_button_background_color" => $inAppRow->closing_button_background_color,
    									"closing_button_background_color_opacity" => $inAppRow->closing_button_background_color_opacity,
    									"slide_up_position" => $inAppRow->slide_up_position,
    									"chevron_color" => $inAppRow->chevron_color,
    									"chevron_color_opacity" => $inAppRow->chevron_color_opacity,
    									"custom_html" => $custom_html_url,
                      "message_close_autotime" => "10",
                      "message_category" => $inAppRow->message_category
    								);
                    $update = array('id' => $notification_id, 'is_view' => '1');
                    $notificationRow = $this->inapp_model->updateNotificationHistory($update);
    						}
    						//print_r($inAppMessagingRow); exit();

        				$success = 1;
    						$response = $inAppArr;
        				$statusMessage = 'InApp Messaging Row details.';
        			}else{
        				$success = 0;
        				$statusMessage = "Error occoured. Invalid device UUID";
        			}
        		}else{

        			$success = 0;
        			$statusMessage = "Error occoured. App group key and app key are not valid";
        		}

        	}else{
        		$success = 0;
        		$statusMessage = "Error occoured. App group key and app key can't be blank";
        	}
        	$response = array(
        			"c2dictionary" => true,
        			"data" => array(
        					"status" => "success",
    							"data" => $inAppArr,
        					"statusMessage" => $statusMessage,
        			)
        	);

        	echo json_encode($response);
    	}


}
