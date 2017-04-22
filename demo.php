<?php
/**
 * Excel dengan CI & Spout
 *
 */
//load Spout Library
require_once('vendor/autoload.php');
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
class Demo extends CI_Controller {
    public function spark() {
        $httpClient = new \Http\Adapter\Guzzle6\Client(new Client());
        $sparky = new SparkPost($httpClient, ['key' => '7a05eb007a77b08c017f6f6464c223e4650178db']);
        $promise = $sparky->transmissions->post([
            'content' => [
                'from' => [
                    'name' => 'SparkPost Team',
                    'email' => 'hello@hurree.co',
                ],
                'subject' => 'First Mailing From PHP',
                'html' => '<html><body><h1>Congratulations, {{name}}!</h1><p>You just sent your very first mailing!</p></body></html>',
                'text' => 'Congratulations, {{name}}!! You just sent your very first mailing!',
            ],
            'substitution_data' => ['name' => 'Yogesh'],
            'recipients' => [
                [
                    'address' => [
                        'name' => 'Yogesh',
                        'email' => 'yogesh@qsstechnosoft.com',
                    ],
                ],
            ],
            'cc' => [
                [
                    'address' => [
                        'name' => 'hassan',
                        'email' => 'hassan@qsstechnosoft.com',
                    ],
                ],
            ],
        ]);
        $promise = $sparky->transmissions->get();

        try {
            $response = $promise->wait();
            echo $response->getStatusCode() . "\n";
            print_r($response->getBody()) . "\n";
        } catch (\Exception $e) {
            echo $e->getCode() . "\n";
            echo $e->getMessage() . "\n";
        }

        echo "I will print out after the promise is fulfilled";
    }

}

//end of class