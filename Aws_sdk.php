
<?php

require_once APPPATH . "/third_party/vendor/autoload.php";

use Aws\Sns\SnsClient; //here you define what module(s) you want to use

class Aws_sdk {

    public $snsClient;
    public $ci;

    public function __construct() {
        $this->ci = & get_instance();
        $this->ci->load->config('aws_sdk');
        // Create a new Amazon SNS client
        $this->sns = SnsClient::factory(array(
                    'region' => $this->ci->config->item('region'),
                    'version' => $this->ci->config->item('version'),
                    'credentials' => array(
                        'key' => $this->ci->config->item('aws_access_key'),
                        'secret' => $this->ci->config->item('aws_secret_key')
                    )
        )); //Change this to instantiate the module you want. Look at the documentation to find out what parameters you need.
    }

    // functio for generate endpoints
    public function generateEndpoint($token, $arn) {
        try {
            $response = $this->sns->createPlatformEndpoint(array(
                // PlatformApplicationArn is required
                'PlatformApplicationArn' => $arn,
                // Token is required
                'Token' => $token));
            if (isset($response['EndpointArn'])) {

                return $response['EndpointArn'];
            } else
                return false;
        } catch (Exception $e) {
            $message = $e->getMessage();
            preg_match("/(arn:aws:sns[^ ]+)/", $message, $matches);
            if (isset($matches[0]) && !empty($matches[0]))
                return $matches[0];
            return false;
        }
    }

//method to send notification 
    public function SendPushNotification($message, $target, $token) {
      
        try {

            $result = $this->sns->publish(array('Message' => $message,
                'TargetArn' => $target, 'MessageStructure' => 'json'));


            $endpointAtt = $this->sns->getEndpointAttributes(array(
                // PlatformApplicationArn is required
                'EndpointArn' => $target,
            ));



            if ($endpointAtt['Attributes']['Enabled'] == false) { // Endpoint is either have invalid token or it is marked as disabled.
                $endresult = $this->sns->setEndpointAttributes(array(
                    // EndpointArn is required
                    'EndpointArn' => $target,
                    // Attributes is required
                    'Attributes' => array(
                        // Associative array of custom 'String' key names
                        'Enabled' => 'true',
                    // ... repeated
                    ),
                ));
            }



            return true;
        } catch (Exception $e) {
            return false;
            //echo "<strong>Failed:</strong> ".$endpointArn."<br/><strong>Error:</strong> ".$e->getMessage()."<br/>";
        }
    }

}

?>
