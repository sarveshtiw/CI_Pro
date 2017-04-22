<?php

//use Exception;

class Referralfriend {

public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree','cookie', 'salesforce_helper', 'permission_helper', 'permission'));

        $this->load->library(array('form_validation', 'pagination'));

        $this->load->model(array('user_model', 'brand_model', 'payment_model', 'administrator_model', 'groupapp_model', 'notification_model', 'country_model', 'permission_model', 'location_model', 'email_model', 'campaign_model', 'reward_model', 'businessstore_model','offer_model','geofence_model','role_model','contact_model','hubSpot_model','crosschannel_model'));

        emailConfig();
    }


}

?>
