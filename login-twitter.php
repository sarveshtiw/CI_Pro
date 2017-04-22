<?php
	require("twitteroauth.php");
	session_start();
	
	$_SESSION['previous_url'] = $_SERVER['HTTP_REFERER'];

	$twitteroauth = new TwitterOAuth(Tw_CONSUMER_KEY, Tw_CONSUMER_SECRET);
	// Requesting authentication tokens, the parameter is the URL we will be redirected to
	$request_token = $twitteroauth->getRequestToken();

	// Saving them into the session

	$_SESSION['oauth_token'] = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        /*
	$tw_oauth_data = array(
                'oauth_token' => $request_token['oauth_token'],
                'oauth_token_secret' => $request_token['oauth_token_secret']
                );
   	$this->session->set_userdata('tw_oauth_data', $tw_oauth_data);  
        */
	// If everything goes well..
	if ($twitteroauth->http_code == 200) {
		// Let's generate the URL and redirect
		$url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
		header('Location: ' . $url);
	} else {
		// It's a bad idea to kill the script, but we've got to know when there's an error.
		header('Location: ' . $url);
	}

