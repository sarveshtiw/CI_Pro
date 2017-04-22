<?php
if (!defined('BASEPATH'))
exit('No direct script access allowed');

class Addcontacts extends CI_Controller {
  
public function __construct() {
    parent::__construct();
    $this->load->model(array('administrator_model','contact_model','permission_model','groupapp_model','hurreebrand_model'));
}

function login(){
    $json = file_get_contents('php://input');
    $params = json_decode($json);
    
    $email = $params->email;
    $password = $params->password;
    
    if(isset($email) && $email != '' && isset($password) && $password != ''){
        $session_details = $this->contact_model->login($email, $password);
        if(count($session_details) > 0){
            //print_r($session_details); die;
            if($session_details->oauth_token == ''){
                
            $len=10;
            $base='JKLMNOPQRS123456789';
            $max=strlen($base)-1;
            $rand_num='';
            mt_srand((double)microtime()*1000000);
            while (strlen($rand_num)<$len+1)
            $rand_num.=$base{mt_rand(0,$max)};
            
            $update['oauth_token'] = $rand_num;
            $update['user_Id'] = $session_details->user_id;
            
            $this->contact_model->createOauth($update);
            
                $success = 0;
                $statusMessage = "Success. Oauth Token:".$rand_num;
                
            }else{
                $success = 0;
                $statusMessage = "Success. Oauth Token:".$session_details->oauth_token; 
            }
        }else{
            $success = 0;
            $statusMessage = "Error occoured. Email and Password are incorrect";
        }
    }else{
        $success = 0;
    	$statusMessage = "Error occoured. Email and Password can't be blank";
    }
    
    $response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => $success,
    					"statusMessage" => $statusMessage
    			)
        );
    
    echo json_encode($response);
    
    
}
function uploadContacts(){
    
    $json = file_get_contents('php://input');
    $params = json_decode($json);
    
    $oauth_token = $params->oauth_token;
    $app_group_key = $params->app_group_key;
    $notAdded = array();
    if(isset($oauth_token) && $oauth_token != '' && isset($app_group_key) && $app_group_key != ''){
        
        $user = $this->contact_model->checkUserExist($oauth_token);
       
        if(count($user) > 0){
            
            $check['businessId'] = $user->businessId;
            $check['app_group_key'] = $app_group_key;
            $group = $this->contact_model->checkUserAppGroup($check);
            //print_r($group); die;
            if(count($group) > 0){
                $app_group_id = $group->app_group_id;
                if(array_key_exists('contacts',$params)){
                    $contacts = $params->contacts;
                    //print_r($contacts); die;
                    if(count($contacts) <= 100){
                        foreach($contacts as $cont){
                            
                        //echo $app_group_id; die;
                        //print_r($cont); die;  
                        $contact['email'] = $cont->email;
                        $contact['app_group_id'] = $app_group_id;
                        $contactUser = $this->contact_model->checkContactExist($contact);
                        
                       if(count($contactUser) == 0 && !filter_var($cont->email, FILTER_VALIDATE_EMAIL) === false){
                           
                            $insert['external_user_id'] = '';
                            $insert['exteranal_app_user_id'] = 0;
                            $insert['app_group_id'] = $app_group_id;
                            $insert['firstName'] = $cont->firstname;
                            $insert['lastName'] =  $cont->lastname;
                            $insert['email'] = $cont->email;
                            $insert['phoneNumber'] = $cont->phone;
                            $insert['user_image'] = $cont->user_image;
                            $insert['gender'] = $cont->gender;
                            $insert['date_of_birth'] = $cont->date_of_birth;
                            $insert['website'] = $cont->website;
                            $insert['company'] = $cont->company;
                            $insert['address'] = $cont->address;
                            $insert['city'] = $cont->city;
                            $insert['state'] = $cont->state;
                            $insert['zip'] = $cont->zip;
                            $insert['isDelete'] = 0;
                            $insert['createdDate'] = date('YmdHis');
                            
                            $this->contact_model->saveContact($insert);
                            
                       }else{
                           
                           $notAdded[] =  $cont->email;
                           
                       }          
                        
                    }
                    
                        $success = 1;
                        $statusMessage = "Contacts are added successfully";
                    }else{
                       $success = 0;
                        $statusMessage = "Error occoured. Not more than 100 contacts can be upload"; 
                    }
                    
                }
            }else{
                $success = 0;
                $statusMessage = "Error occoured. Invalid Group";
            }
            
        }else{
            $success = 0;
            $statusMessage = "Error occoured. Outh Token is not valid";
        }
        
        
    }else{
        $success = 0;
    	$statusMessage = "Error occoured. Oauth Token and App Group key can't be blank";
    }
    
    if($success == 1){
        $success = 'success';
    }else{
        $success = 'error';
    }
    
    if(count($notAdded)>0){
        $response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => $success,
    					"statusMessage" => $statusMessage,
                                        "Contacts not added" => $notAdded
    			)
        );
    }else{
        $response = array(
    			"c2dictionary" => true,
    			"data" => array(
    					"status" => $success,
    					"statusMessage" => $statusMessage
    			)
        );
    }
    

    echo json_encode($response);
    
}  
    
    
    
    
    
    
}
?>