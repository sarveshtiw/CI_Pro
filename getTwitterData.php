<?php
require("twitteroauth.php");
session_start();

if (!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
    // We've got everything we need
    	$twitteroauth = new TwitterOAuth(Tw_CONSUMER_KEY, Tw_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    // Let's request the access token
   	$access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
    // Save it in a session var
	$_SESSION['access_token'] = $access_token;
    // Let's get the user's info
	$user_info = $twitteroauth->get('account/verify_credentials');
    // Print user's info
	
    // echo '<pre>';
    // print_r($user_info);
    // echo '</pre><br/>';
    
    if (isset($user_info->error)) {
    	
        // Something's wrong, go back to square 1  
        header('Location: '.base_url().'home');
    } else {

	$twitter_data = array();
	$twitter_data['name'] = $user_info->name;
        $twitter_data['twitterid'] = $user_info->id;
        $twitter_data['username'] = $user_info->screen_name;
        $twitter_data['bio'] = $user_info->description;
        $twitter_data['password'] = '';
        $twitter_data['country'] = $user_info->location;
        $twitter_data['header_image'] = $user_info->profile_image_url;
        
	$twitter_data['oauth_token'] = $_SESSION['oauth_token'];
	$twitter_data['oauth_token_secret'] = $_SESSION['oauth_token_secret'];
	$twitter_data['oauth_verifier'] = $_GET['oauth_verifier'];
        
	twitterOuthSocialConnect($twitter_data);
    }
} else {
    // Something's missing, go back to square 1
    header('Location: login-twitter.php');
}
?>
