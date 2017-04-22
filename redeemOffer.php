<?php
if (! defined ( 'BASEPATH' ))	exit ( 'No direct script access allowed' );

class RedeemOffer extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('time'));

        $this->load->library(array('form_validation','session', 'email'));
        $this->load->model(array('user_model','administrator_model','games_model','score_model','offer_model','email_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');

    }

	public function index($campaignId) {
		$useragent = $_SERVER ['HTTP_USER_AGENT'];

		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))

		{

		$checkoffer = $this->offer_model->checkOffer ( $campaignId );
		if ($checkoffer) {

			$this->session->set_userdata ( 'comId', $campaignId );
			$this->session->set_userdata ( 'current_url', current_url () );
			$baseurl = base_url ();
			$_SERVER ['SERVER_NAME'];
			$this->load->helper ( 'url' );
			$this->load->library ( 'session' );
			$data ['viewPage'] = 'timeline';
			$login = $this->administrator_model->front_login_session ();

			// code: if user is not login redirect to user to website home page
			if (! isset ( $login->user_id )) {

				$this->load->view ( 'redeemHeader' );
				$this->load->view ( 'redeemHomePage' );
				$this->load->view ( 'redeemFooter' );
			} else {
				// $data['username'] = $login->username;
				// $data['email'] = $login->email;
				// $data['userid'] = $login->user_id;
				// $result = $this->emailOffer($userid,$username,$email);
				// $this->session->set_flashdata('emailsent','Sent');
				if (isset ( $_COOKIE ['hurree_campaignId'] )) {
					$offerId = $_COOKIE ['hurree_campaignId'];
				} else {
					$offerId = '0';
				}
				// check this already redeemed or not
				$redeemedOffer = $this->offer_model->redeemedOffer ( $offerId, $login->user_id );
				if ($redeemedOffer == 0) {

					$data ['user'] = $login;

					// check email already sent or not
					$result = $this->offer_model->getSavedEmailStatus($login->user_id,$offerId );

					if ($result) {
						$data ['emailSent'] = 1;
					} else {
						$data ['emailSent'] = 0;
					}
					// campain code start here
					$sqlCampaign = "SELECT * FROM user_campaigns where isActive = 1 and isDelete = 0 and campaign_id = " . $campaignId;
					$queryCampaign = $this->db->query ( $sqlCampaign );
					$campaignsData = $queryCampaign->result ();
					if (count ( $campaignsData ) > 0) {
						$data ['campaign'] = $campaignsData [0];

						// $sqlCampaignOwnere = "SELECT * FROM users where active = 1 and isDelete = 0 and user_Id = " . $campaignsData[0]->user_id;
						$sqlCampaignOwnere = "SELECT * FROM users where active = 1  and user_Id = " . $campaignsData [0]->user_id;
						$queryCampaignOwnere = $this->db->query ( $sqlCampaignOwnere );
						$campaignsOwnerData = $queryCampaignOwnere->result ();
						$data ['campaignsOwner'] = $campaignsOwnerData [0];
						$data ['campaignId'] = $campaignId;
					} else {
						$data ['notFound'] = 'Offer Not Found.';
					}

					$this->load->view ( 'redeemHeader' );
					$this->load->view ( 'redeemoffer', $data );
					$this->load->view ( 'redeemFooter' );
				} else {
					$data ['already'] = '1';
					$this->load->view ( 'redeemHeader' );
					$this->load->view ( 'redeemoffer', $data );
					$this->load->view ( 'redeemFooter' );
				}
			}
		} else {
			   echo 'Offer Not Found.';
	  	}
		}
		else{
		 $this->load->view('PCnotsupport');
		}
	}

	// .login function for redeem offer page- only consumer can login
	function login() {
		$campaignId = $this->session->userdata ( 'comId' );
		$current_url = $this->session->userdata ( 'current_url' );
		$username = $this->input->post ( 'username' );
		$password = $this->input->post ( 'password' );

		$session_details = $this->user_model->check_username ( $username, $password );

		if (count ( $session_details ) > 0) {
			// // IF USERNAME IS ALREADY REGISTERED, WILL GO FOR CHECK PASSWORD
			if ($session_details->usertype != 3) {
				// Check business user is on hold
				if ($session_details->usertype == 1) {
					$this->load->helper ( 'cookie' );

					if (! (get_cookie ( 'hurree_campaignId' ) === false)) {
						$cookieCampaignId = array (
								'name' => 'campaignId',
								'value' => '',
								'expire' => '0',
								'domain' => '.' . $_SERVER ['SERVER_NAME'],
								'path' => '/',
								'prefix' => 'hurree_'
						);

						delete_cookie ( $cookieCampaignId );
					}
					if (! (get_cookie ( 'hurree_campaignUrl' ) === false)) {
						$campaignUrl = array (
								'name' => 'campaignUrl',
								'value' => '',
								'expire' => '0',
								'domain' => '.' . $_SERVER ['SERVER_NAME'],
								'path' => '/',
								'prefix' => 'hurree_'
						);

						delete_cookie ( $campaignUrl );
					}

					$cookieCampaignId = array (
							'name' => 'campaignId',
							'value' => $campaignId,
							'expire' => time () + 86400,
							'domain' => '.' . $_SERVER ['SERVER_NAME'],
							'path' => '/',
							'prefix' => 'hurree_'
					);

					$campaignUrl = array (
							'name' => 'campaignUrl',
							'value' => $current_url,
							'expire' => time () + 86500,
							'domain' => '.' . $_SERVER ['SERVER_NAME'],
							'path' => "/",
							'prefix' => 'hurree_'
					);
					set_cookie ( $campaignUrl );
					set_cookie ( $cookieCampaignId );
					// cookie set code end

					$where ['userid'] = $session_details->user_Id;
					$loginStatus = $this->user_model->getLoginStatus ( '*', $where );
					if (count ( $loginStatus ) == 0) {
						$this->session->set_userdata ( 'logged_in', $session_details );
						$sess = $this->session->userdata ( 'logged_in' );
						echo '1';
					} else {
						$hold = $loginStatus->hold;
						if ($hold === "1") {
							echo '2';
						} else {
							$this->session->set_userdata ( 'logged_in', $session_details );
							$sess = $this->session->userdata ( 'logged_in' );
							echo '1';
						}
					}
				} else {

					echo '3';
				}
			} else {
				echo '0';
				return "Invalid Username/Password";
			}
		}
	}

	// signup function for consumer
	function signup() {

		$campaignId = $this->session->userdata ( 'comId' );
		$current_url = $this->session->userdata ( 'current_url' );
		$header ['login'] = $this->administrator_model->front_login_session ();

		if ($header ['login']->true == false && $header ['login']->accesslevel == '' && $header ['login']->active == 0) {

			$username = $this->input->post ( 'username' );
			$email = $this->input->post ( 'email' );
			$businessName = '';
			$firstname = $this->input->post ( 'firstname' );
			$lastname = $this->input->post ( 'lastname' );
			$password = $this->input->post ( 'password' );

			$gender = $this->input->post ( 'gender' );

			$date_of_birth =  $this->input->post ( 'year' ). '-' . $this->input->post ( 'month' ) . '-' .$this->input->post ( 'day' );
			$active = 1;
			$usertype = $this->input->post ( 'usertype' );

			$save ['firstname'] = $firstname;
			$save ['lastname'] = $lastname;

			// // Create Array to Save Data into Database
			$date = date ( 'YmdHis' );
			$save ['user_Id'] = '';
			$save ['email'] = $email;
			$save ['username'] = $username;
			$save ['password'] = md5 ( $password );
			$save ['active'] = $active;
			$save ['usertype'] = $usertype;
			$save ['image'] = 'user.png';
			$save ['header_image'] = 'profileBG.jpg';
			$save ['loginSource'] = 'normal';
			$save ['createdDate'] = $date;
			$save ['firstLogin'] = $date;
			$save ['date_of_birth'] = $date_of_birth;
			$save ['gender'] = $gender;

			if ($this->input->post ( 'usertype' ) == 1) {
				$inserid = $this->user_model->insertsignup ( $save );

				$coins = array (
						'userid' => $inserid,
						'coins' => '0'
				);
				$this->score_model->signupCoins ( $coins );

				$userCoins = array (
						'userid' => $inserid,
						'coins' => 100,
						'coins_type' => 8,
						'game_id' => 0,
						'businessid' => 0,
						'actionType' => 'add',
						'createdDate' => date ( 'YmdHis' )
				);
				$this->score_model->insertCoins ( $userCoins );

				// // Create Array for Login Session
				$session = array ();
				$session = ( object ) array (
						'user_Id' => $inserid,
						'username' => $username,
						'password' => md5 ( $password ),
						'email' => $email,
						'active' => $active,
						'usertype' => $usertype,
						'firstname' => $save ['firstname'],
						'lastname' => $save ['lastname'],
						'image' => '',
						'accesslevel' => 'consumer'
				);

				$this->session->set_userdata ( 'logged_in', $session ); // // Create Login Session
				                                                     // // SEND EMAIL START
				$this->emailConfig (); // Get configuration of email
				                      // // GET EMAIL FROM DATABASE

				$email_template = $this->email_model->getoneemail ( 'consumer_signup' );

				// // MESSAGE OF EMAIL
				$messages = $email_template->message;

				$hurree_image = base_url () . 'hurree/assets/template/frontend/img/app-icon.png';
				$appstore = base_url () . 'hurree/assets/template/frontend/img/appstore.gif';
				$googleplay = base_url () . 'hurree/assets/template/frontend/img/googleplay.jpg';

				// // replace strings from message
				$messages = str_replace ( '{Username}', ucfirst ( $username ), $messages );
				$messages = str_replace ( '{Hurree_Image}', $hurree_image, $messages );
				$messages = str_replace ( '{App_Store_Image}', $appstore, $messages );
				$messages = str_replace ( '{Google_Image}', $googleplay, $messages );

				// // FROM EMAIL
				$this->email->from ( $email_template->from_email, 'Hurree' );
				$this->email->to ( $email );
				$this->email->subject ( $email_template->subject );
				$this->email->message ( $messages );
				$this->email->send (); // // EMAIL SEND

				$this->load->helper ( 'cookie' );

				if (! (get_cookie ( 'hurree_campaignId' ) === false)) {
					$cookieCampaignId = array (
							'name' => 'campaignId',
							'value' => '',
							'expire' => '0',
							'domain' => '.' . $_SERVER ['SERVER_NAME'],
							'path' => '/',
							'prefix' => 'hurree_'
					);

					delete_cookie ( $cookieCampaignId );
				}

				if (! (get_cookie ( 'hurree_campaignUrl' ) === false)) {
					$campaignUrl = array (
							'name' => 'campaignUrl',
							'value' => '',
							'expire' => '0',
							'domain' => '.' . $_SERVER ['SERVER_NAME'],
							'path' => '/',
							'prefix' => 'hurree_'
					);

					delete_cookie ( $campaignUrl );
				}

				$cookieCampaignId = array (
						'name' => 'campaignId',
						'value' => $campaignId,
						'expire' => time () + 86400,
						'domain' => '.' . $_SERVER ['SERVER_NAME'],
						'path' => '/',
						'prefix' => 'hurree_'
				);

				$campaignUrl = array (
						'name' => 'campaignUrl',
						'value' => $current_url,
						'expire' => time () + 86500,
						'domain' => '.' . $_SERVER ['SERVER_NAME'],
						'path' => "/",
						'prefix' => 'hurree_'
				);
				set_cookie ( $campaignUrl );
				set_cookie ( $cookieCampaignId );

				// cookie set code end
				redirect ( 'timeline' );
			}
		} else {
			if ($header ['login']->true == 1 && $header ['login']->accesslevel != '' && $header ['login']->active == 1) {
				redirect ( 'timeline' );
			} else {
				$this->session->unset_userdata ( 'user_logged_in' );
				redirect ( 'home/index' );
			}
		}
	}

	// when only 10 min to redeem offer, this function will send a email to logged in user
	function lessTimeAlert() {
		// logged in username
		$login = $this->administrator_model->front_login_session ();
		$username = $login->username;
		$email = $login->email;

		// // SEND EMAIL START
		$this->emailConfig (); // Get configuration of email
		                      // // GET EMAIL FROM DATABASE

		$email_template = $this->email_model->getoneemail ( 'alert_offer' );

		// // MESSAGE OF EMAIL
		$messages = $email_template->message;

		$hurree_image = base_url () . 'assets/template/frontend/img/redeem_success.png';

		// // replace strings from message
		$messages = str_replace ( '{Username}', ucfirst ( $username ), $messages );
		$messages = str_replace ( '{Hurree_Image}', $hurree_image, $messages );

		// // FROM EMAIL
		$this->email->from ( $email_template->from_email, 'Hurree' );
		$this->email->to ( $email );
		$this->email->subject ( $email_template->subject );
		$this->email->message ( $messages );
		$sent = $this->email->send ();

		// // EMAIL SEND
		if ($sent) {
			echo '1';
			exit ();
		} else {

			echo '0';
			exit ();
		}
	}

	function redeemLogout($campId) {
		$this->load->helper ( 'cookie' );

		session_destroy ();
		$this->session->unset_userdata ( 'user_logged_in' );
		$this->session->sess_destroy ();
		unset ( $_SESSION ['lastinsrtId'] );
		unset ( $_SESSION ['previous_url'] );

		$cookieCampaignId = array (
				'name' => 'campaignId',
				'value' => '',
				'expire' => '0',
				'domain' => '.' . $_SERVER ['SERVER_NAME'],
				'path' => '/',
				'prefix' => 'hurree_'
		);

		delete_cookie ( $cookieCampaignId );

		$campaignUrl = array (
				'name' => 'campaignUrl',
				'value' => '',
				'expire' => '0',
				'domain' => '.' . $_SERVER ['SERVER_NAME'],
				'path' => '/',
				'prefix' => 'hurree_'
		);

		delete_cookie ( $campaignUrl );
		redirect ( 'redeemOffer/index/' . $campId );
	}
	// display redeem page
	function redeemCode($campaignId) {
		// campain code start here
		$sqlCampaign = "SELECT * FROM user_campaigns where isActive = 1 and isDelete = 0 and campaign_id = " . $campaignId;
		$queryCampaign = $this->db->query ( $sqlCampaign );
		$campaignsData = $queryCampaign->result ();
		$data ['campaign'] = $campaignsData [0];
		// $sqlCampaignOwnere = "SELECT * FROM users where active = 1 and isDelete = 0 and user_Id = " . $campaignsData[0]->user_id;
		$sqlCampaignOwnere = "SELECT * FROM users where active = 1  and user_Id = " . $campaignsData [0]->user_id;
		$queryCampaignOwnere = $this->db->query ( $sqlCampaignOwnere );
		$campaignsOwnerData = $queryCampaignOwnere->result ();
		$data ['campaignsOwner'] = $campaignsOwnerData [0];

		$this->load->view ( 'redeemCode', $data );
	}

	// function for send email when user arrive on offer page
	function emailOffer() {
		$userid = $_POST ['userid'];
		$offerId = $_POST ['offerId'];
		$username = $_POST ['username'];
		$email = $_POST ['email'];
		$businessUsername = $_POST ['businessUsername'];
		$notification = $_POST ['notification'];

		$result = $this->offer_model->saveEmailStatus ( $userid, $offerId );

		if (isset ( $_COOKIE ['hurree_campaignUrl'] )) {
			$url = $_COOKIE ['hurree_campaignUrl'];
		} else {
			$url = 'No url found.';
		}

		if ($result == 1) {

			// // SEND EMAIL START
			$this->emailConfig (); // Get configuration of email
			                      // // GET EMAIL FROM DATABASE

			$email_template = $this->email_model->getoneemail ( 'get_offer' );

			// // MESSAGE OF EMAIL
			$messages = $email_template->message;

			$hurree_image = base_url () . 'assets/template/frontend/img/redeem_success.png';

			// // replace strings from message
			$messages = str_replace ( '{Username}', ucfirst ( $username ), $messages );
			$messages = str_replace ( '{Url}', $url, $messages );
			$messages = str_replace ( '{BusinessUsername}', ucfirst ( $businessUsername ), $messages );
			$messages = str_replace ( '{NotificationText}', $notification, $messages );
			$messages = str_replace ( '{Hurree_Image}', $hurree_image, $messages );

			// // FROM EMAIL
			$this->email->from ( $email_template->from_email, 'Hurree' );
			$this->email->to ( $email );
			$this->email->subject ( $email_template->subject );
			$this->email->message ( $messages );
			$sent = $this->email->send ();

			// // EMAIL SEND
			if ($sent) {

				return true;
			} else {

				return false;
			}
		} else {

			return true;
		}
	}

	// email configuration
	public function emailConfig() {
		$this->load->library ( 'email' ); // // LOAD LIBRARY

		$config ['protocol'] = 'smtp';
		$config ['smtp_host'] = 'auth.smtp.1and1.co.uk';
		$config ['smtp_port'] = '587';
		$config ['smtp_timeout'] = '7';
		$config ['smtp_user'] = 'support@hurree.co';
		$config ['smtp_pass'] = 'aaron8164';
		$config ['charset'] = 'utf-8';
		$config ['newline'] = "\r\n";
		$config ['mailtype'] = 'html'; // or html

		$this->email->initialize ( $config );
	}

	// redeem offer process
	function grabOffer() {
		$login = $this->administrator_model->front_login_session ();

		$userid = $login->user_id;
		$username = $login->username;
		$email = $login->email;
		$code = $_POST ['qrcode'];
		$campaignOwnerId = $_POST ['campaignOwnerId'];
		// campaign owner details
		$campaignsOwnerDetails = $this->user_model->getOneUser ( $campaignOwnerId );
		$campaignsOwnerEmail = $campaignsOwnerDetails->email;
		$campaignsOwnerUsername = $campaignsOwnerDetails->username;
		// end
		if (isset ( $_COOKIE ['hurree_campaignId'] )) {
			$offerId = $_COOKIE ['hurree_campaignId'];
		} else {
			$offerId = '0';
		}

		$checkoffer = $this->offer_model->checkOffer ( $offerId );
		if ($checkoffer) {

			// check this already redeemed or not
			$redeemedOffer = $this->offer_model->redeemedOffer ( $offerId, $userid );
			if ($redeemedOffer == 0) {
				// get offer

				$offerResult = $this->offer_model->getOffer ( $offerId, $code );

				if ($offerResult) {
                                    if($offerResult->availability >0){
					if ($offerResult->discount_percentage == 0) {
						$type = 'coins';
						$value = 200;
					} else {
						$type = 'discount';
						$value = $offerResult->discount_percentage;
					}

					$arr = array (
							'userid' => $userid,
							'offerId' => $offerId,
							'code' => $code,
              'offerOwnerId' => $offerResult->user_id,
							 $type => $value,
							'redeemType' => 3,
							'active' => 1,
							'isDelete' => 0
					);

					$result = $this->offer_model->redeemOffer ( $arr ); // save redeem offer details

					// decrease number of Availability users
					if ($offerResult->availability > 0) {
						$updatedCount = $offerResult->availability - 1;
					} else {
						$updatedCount = $offerResult->availability;
					}

					$this->offer_model->updateAvailability ( $offerId, $updatedCount );

					$userTotalCoins = $this->score_model->getUserCoins ( $userid );
					$updateCoins = $userTotalCoins->coins + 200; // static value, add 200 more coins in total coins
					$update = array (
							'userid' => $userid,
							'coins' => $updateCoins
					);
					$this->score_model->update ( $update ); // update user total coins
          if(isset($offerResult->coins)){
          	$redeem_coins = $offerResult->coins;
          }else{
          	$redeem_coins = 200;
          }

					$insert = array(
							'userid' => $userid,
							'coins' => $redeem_coins,
							'actionType' => 'add',
							'coins_type' => 13,
							'campaign_id' => $offerId,
							'businessid' => $offerResult->user_id,
							'createdDate' => date('Y-m-d h:i:s')
					);

					$this->score_model->insert($insert); // update user  coins
          // code for upadte user offers coins in businessConsumerRedeemCoins table
            $data['businessUserId'] = $offerResult->user_id;
            $data['consumerId'] = $userid;
            $coins = $redeem_coins;
            $this->user_model->OfferCoins($data,$coins);
					if ($result) {
						$this->offer_model->redeemSuccessEmailtoConsumer ( $offerResult->notification, $username, $email );
						$arr ['redeemedUsername'] = $username;
						$arr ['campaignsOwnerUsername'] = $campaignsOwnerUsername;
						$arr ['campaignsOwnerEmail'] = $campaignsOwnerEmail;
						$this->offer_model->redeemSuccessEmailtoBusinessuser ( $arr );
						// insert entry in event send history table for crone job
						// check user from website or not
						$viaAppCheck = $this->user_model->viaApp ( $userid );

						if ($viaAppCheck) {

							$this->offer_model->saveEvent ( $userid );
						}

						echo '1'; // offer redeemed
					} else {

						echo '2'; // error occured during redeem
					}
                                        } else {
					echo '6'; // avali
				}
				} else {
					echo '3'; // invalid qrcode
				}
			} else {
				echo '4'; // already redeemed offer
			}
		} else {
			echo '5'; // Offer Not Found
		}
	}

	// function for call success popup when offer redeemed
	function redeemOfferSuccess($discount, $businessName) {
		$data ['discount'] = $discount;
		$data ['businessName'] = $businessName;
		$this->load->view ( 'redeemSuccess', $data );
	}

	function missedOffer() {
		$this->load->view ( 'missedOffer' );
	}
}
?>
