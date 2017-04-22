<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class OfferNotification extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');

    }

    // function for send push notifications related to offers

    function sendOfferPushNotification(){
   
      // send aws notification code start
    	$this->load->model('user_model');
       
        $deviceInfo = $this->user_model->getAllDeviceToken($_POST['userid'],$_POST['gender'],$_POST['minAge'],$_POST['maxAge']);
        
        if(count($deviceInfo)>0){
            foreach ($deviceInfo as $device) {
            $deviceToken = $device->key;
            $deviceType = $device->deviceTypeID;
          
            $title = 'My Test Message';
            $sound = 'default';
            $msgpayload=json_encode(array(
                    'aps' => array(
                    'alert' => $_POST['autoText'],
                    'offerId'=> $_POST['offerId'],
                    'OfferUrl' => $_POST['offerUrl'],
                    'name' => $_POST['name'],
                    'username'=>$_POST['username'],
                    'userimage'=>$_POST['userimage'],
                    'offerimage'=>$_POST['offerimage'],
                    'createdDate'=>$_POST['createdDate'],
                    'availability'=>$_POST['availability'],
                    'discountValue'=>$_POST['discountValue'],
                    'coins'=>$_POST['coins'],
                    'type'=>$_POST['type'],
                    'sound'=>$sound

                    )));


            $message = json_encode(array(
            'default' => $title,
            'APNS_SANDBOX' => $msgpayload
            ));
                                                        
            $result = $this->amazonSns($deviceToken,$message,$deviceType);
        }
    }

    // end
    }

    function sendIndividualOfferPushNotification(){
   
      // send aws notification code start
        $this->load->model('user_model');
        $deviceInfo = $this->user_model->getDeviceToken($_POST['receiverid']);
      
        if(count($deviceInfo)>0){
            foreach ($deviceInfo as $device) {
            $deviceToken = $device->key;
            $deviceType = $device->deviceTypeID;
          
            $title = 'My Test Message';
            $sound = 'default';
             $msgpayload=json_encode(array(
                    'aps' => array(
                    'alert' => $_POST['autoText'],
                    'offerId'=> $_POST['offerId'],
                    'OfferUrl' => $_POST['offerUrl'],
                    'name' => $_POST['name'],
                    'username'=>$_POST['username'],
                    'userimage'=>$_POST['userimage'],
                    'offerimage'=>$_POST['offerimage'],
                    'createdDate'=>$_POST['createdDate'],
                    'availability'=>isset($_POST['availability'])?$_POST['availability']:'',
                    'discountValue'=>$_POST['discountValue'],
                    'coins'=>$_POST['coins'],
                    'type'=>$_POST['type'],
                    'sound'=>$sound

                    )));


            $message = json_encode(array(
            'default' => $title,
            'APNS_SANDBOX' => $msgpayload
            ));
                                                        
            $result = $this->amazonSns($deviceToken,$message,$deviceType);
        }
    }

    // end
    }

        // aws push notification
		public function amazonSns($deviceToken,$message,$deviceType){ 
                  
		  $this->load->library('Aws_sdk');
		  $Aws_sdk = new Aws_sdk();
		  if($deviceType == 'ios'){
		  $iOS_AppArn = "arn:aws:sns:us-west-2:831947047245:app/APNS_SANDBOX/Hurree";

		  $endpoint = $Aws_sdk->generateEndpoint($deviceToken,$iOS_AppArn);
                 
		  $result = $Aws_sdk->SendPushNotification($message,$endpoint,$deviceToken);
                 
		  return $result;
		  }
		 
		 
		}

// end amazon sns code
}// end class