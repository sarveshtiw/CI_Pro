<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Userservice extends CI_Controller {

    private $httpVersion = "HTTP/1.1";

    public function __construct() {
        parent::__construct();
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    /* updated by shiwangi (18-01-2016) */

    public function login() {

        $this->setHttpHeaders(200);

        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '') {
            $this->load->model('user_model');


            $userdetail = $this->user_model->getuserHash($userhash);

            if ($userdetail['status'] == 1) {
                $response = array();

                $userid = $userdetail['userid'];
                $email = $userdetail['email'];
                $firstname = isset($userdetail['firstname']) ? $userdetail['firstname'] : '';
                $lastname = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                $name = $firstname . ' ' . $lastname;

                $dateOfBirth = $userdetail['date_of_birth'];
                $birthDate = explode("-", $dateOfBirth);
                $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[1], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));

                $gender = $userdetail['gender'];

                if ($userdetail['usertype'] == 1) {
                    $type = 'Consumer';
                }
                if ($userdetail['usertype'] == 2) {
                    $type = 'Business';
                }
                if ($userdetail['usertype'] == 3) {
                    $type = 'Admin';
                }
                if ($userdetail['usertype'] == 4) {
                    $type = 'ambassador';
                }
                if ($userdetail['usertype'] == 1 || $userdetail['usertype'] == 4) {
                    if (isset($userdetail['image'])) {
                        $userimage = base_url() . 'upload/profile/thumbnail/' . $userdetail['image'];
                    } else {
                        $userimage = '';
                    }
                    //$location  = isset($userdetail['location'])?$userdetail['location']:'';
                    //$bio  = isset($userdetail['bio'])?$userdetail['bio']:'';
                    $gender = isset($gender) ? $gender : '';
                    $userdetails = array(
                        'userid' => $userid,
                        'username' => $userdetail['username'],
                        'email' => $email,
                        'name' => $name,
                        'userimage' => $userimage,
                        'type' => $type,
                        'header_image' => base_url() . 'upload/headerimage/resize/' . $userdetail['header_image'],
                        'location' => isset($userdetail['location'])?$userdetail['location']:'',
                        'bio' => isset($userdetail['bio'])?$userdetail['bio']:'',
                        'age' => $age,
                        'gender' => $gender,
                        'loginSource' => $userdetail['loginSource']
                    );

                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "Logged in Successfully",
                            "user" => $userdetails
                        )
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "Business App, coming soon!"
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Invalid Email Address/Username or Password."   //Error occoured. Username not found
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Enter username and password"
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    /* updated by shiwangi (19-01-2016) */

    public function questions() {
        $this->load->model('question_model');
        $gameid = $this->input->get('id');
        $email = $this->input->get('username');
        $data = $this->question_model->webServices($gameid);
        $cnt_value = count($data);
        $arr_questions = array();
        foreach ($data as $service) {

            $shuffle = array(
                html_entity_decode($service->answer1, ENT_QUOTES, "UTF-8"),
                html_entity_decode($service->answer2, ENT_QUOTES, "UTF-8"),
                html_entity_decode($service->answer3, ENT_QUOTES, "UTF-8"),
                html_entity_decode($service->answer4, ENT_QUOTES, "UTF-8")
            );

            $orig = array_flip($shuffle);
            shuffle($shuffle);
            foreach ($shuffle AS $key => $n) {
                $data[$n] = $orig[$n];
            }
            $keys = array_keys($shuffle);
            shuffle($keys);
            $random = array();
            foreach ($keys as $key) {
                $random[$key] = $shuffle[$key];
            }
            $correct = html_entity_decode($service->answer1, ENT_QUOTES, "UTF-8");
            $position = array_search($correct, $random);

            $arr_question = array(
                array(
                    html_entity_decode($service->question, ENT_QUOTES, "UTF-8"),
                    $position,
                    $service->type
                ),
                $random,
                array(
                    html_entity_decode($service->hint, ENT_QUOTES, "UTF-8")
                )
            );
            $arr_questions[] = $arr_question;
        }

        $arr_final = array(
            "c2array" => true,
            "size" => array(
                $cnt_value,
                3,
                4
            ),
            "data" => $arr_questions
        );
        echo json_encode($arr_final);
        exit;
    }

    public function gameslist() {
        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '') {
            $this->load->model(array('user_model', 'games_model'));

            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $response = array();
                $email = $userdetail['email'];
                $password = $userdetail['password'];

                $new_pass = md5($password);
                $result = $this->user_model->userlogin($email, $new_pass);
                $userid = $result['user_id'];
                $email = $result['user_email'];
                $name = $result['name'];

                $gamelist = $this->games_model->gameslist($userid);
                $data['checkallGames'] = $gamelist[4]->lock;

                $n = 0;
                foreach ($gamelist as $game) {
                    if ($game->lock == 0) {
                        $lock = 'unlock';
                    } else {
                        $lock = 'lock';
                    }
                    if ($game->game_id != 5) {

                        $gamedata['gameid'] = $game->game_id;
                        $gamedata['gamename'] = $game->gameName;
                        $gamedata['gameimage'] = base_url() . 'upload/games/' . $game->image;
                        $gamedata['gamedescription'] = $game->description;

                        $game->enable ? $enable = 'true' : $enable = 'false';
                        $gamedata['enabled'] = $enable;
                        $gamedata['price_gbp'] = $game->app_price_gbp;
                        $gamedata['price_usd'] = $game->app_price_usd;

                        $arr_subscription['user_id'] = $userid;
                        $arr_subscription['active'] = 1;

                        $subscription = $this->games_model->getUserOneGameSubscription($arr_subscription, $row = 'service');
                        $subsGames = array();
                        foreach ($subscription as $subs) {
                            $subsGames[] = $subs->game_id;
                        }
                        if (count($subscription) > 0) {
                            if ((in_array($game->game_id, $subsGames))) {
                                $bought = 'true';
                            } else {
                                $bought = 'false';
                            }
                        } else {
                            $bought = 'false';
                        }


                        $gamedata['bought'] = $bought;
                        $response[] = $gamedata;
                    }
                }

                //print_r($response); exit;
            } else {
                $response = array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. User not found"
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or value cannot be blank."
                )
            );
        }
        echo json_encode($response);
    }

// not implement in ios
    public function submitgame() {
        $userhash = $this->input->post('userHash');
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $number = $this->input->post('number');
        $stage = $this->input->post('stage');
        $concept = $this->input->post('concept');
        if (isset($userhash) && $userhash != '' && isset($name) && $name != '' && isset($email) && $email != '' && isset($number) && $number != '' && isset($stage) && $stage != '' && isset($concept) && $concept != '') {
            $this->load->model(array('user_model', 'email_model'));

            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {

                $username = $userdetail['username'];
                // SEND  EMAIL START
                $this->emailConfig();   //Get configuration of email
                // GET EMAIL FROM DATABASE
                // SEND  EMAIL START
                // GET EMAIL FROM DATABASE
                $email_template = $this->email_model->getoneemail('submitGame');

                //// MESSAGE OF EMAIL
                $messages = $email_template->message;


                $hurree_image = base_url() . 'assets/template/hurree/images/app-icon.png';
                $appstore = base_url() . 'hurree_images/appstore.gif';
                $googleplay = base_url() . '/hurree_images/googleplay.jpg';

                //// replace strings from message
                $messages = str_replace('{Username}', ucfirst($username), $messages);
                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                $messages = str_replace('{Google_Image}', $googleplay, $messages);

                //// Email to user
                $this->email->from($email_template->from_email, 'Hurree');
                $this->email->to($email);
                $this->email->subject($email_template->subject);
                $this->email->message($messages);
                $this->email->send();    ////  EMAIL SEND
                //// Email to Hello@Hurree.co
                $messages = '<p>Full Name: ' . $name . '</p><p>Contact Number: ' . $number . '</p><p>Stage of Game:: ' . $stage . '</p><p>Concept: ' . $concept . '</p>';
                $this->email->from($email, $name);
                $this->email->to($email_template->from_email);
                $this->email->subject('Submit Game');
                $this->email->message($messages);
                $this->email->send();    ////  EMAIL SEND

                $response = array(
                    "status" => "success",
                    "statusMessage" => "Thanks for submitting game"
                );
            } else {
                $response = array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. User not found"
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or value cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function timeline() {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('max_execution_time', 300);
        error_reporting(E_ALL);

        $newuser = 0;
        $userhash = $this->input->post('userHash');
        $page = $this->input->post('page');

        if (isset($userhash) && $userhash != '' && isset($page)) {
            $this->load->model(array('user_model', 'status_model'));
            $baseurl = base_url();
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);
            if ($userdetail['status'] == 1) {
                $hashtag = @$this->input->post('userHash');
                $action = "timeline";
                /* Get User Status */
                $records = $this->status_model->getWebServiceTimeline($userdetail['userid'], '', '', 1, $action);   //// Get Total No of Records in Database
                if ($page == '') {
                    $page = 0;
                }
                $limit = 50;
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'index.php/userservice/timeline/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                $data['page'] = $page;

                $timeline = $this->status_model->getWebServiceTimeline($userdetail['userid'], $page, $limit, '', $action);  //// Get Record

                $cnt_all_record = count($timeline);

                /* Start Suggestion To Follow  */
                $loginDate = $userdetail['firstLogin'];
                $currentDate = date('Y-m-d H:i:s');
                $date1 = new DateTime($currentDate);
                $date2 = new DateTime($loginDate);
                $diff = $date2->diff($date1);
                $hours = $diff->h;
                $hours = $hours + ($diff->days * 24);

                if ($loginDate == $userdetail['createdDate']) {
                    $firstLogin = 1;
                    $limit = 6;
                } else {
                    $firstLogin = 0;
                    $limit = 1;
                }

                // Get Old Suggesed Follow
                $arr_lastSuggest = '';
                $arr_newSuggest = '';
                $arr_sugg['loginuserid'] = $userdetail['userid'];
                $previousSuggestion = $this->user_model->getSuggestedFollow($arr_sugg, '1');
                if (count($previousSuggestion) > 0) {
                    foreach ($previousSuggestion as $pre) {
                        $arr_lastSuggest[] = $pre->followuserid;
                        if ($pre->accept != 1) {
                            $arr_newSuggest[] = $pre->followuserid;
                        }
                    }
                }
                if ($hours >= 9 || count($previousSuggestion) == 0) {
                    $follow_suggestion1 = $this->user_model->getpeopletofollow($userdetail['userid'], '1', $limit, 'timeline', '', $arr_lastSuggest);
                    // print_r($follow_suggestion); exit;
                    // add by shiwangi 22 march
                    if (count($follow_suggestion1) > 0) {

                        foreach ($follow_suggestion1 as $sugg) {
                            $follow_suggestion[] = array(
                                'userid' => $sugg->user_Id,
                                'username' => $sugg->username,
                                'businessName' => isset($sugg->businessName) ? $sugg->businessName : '',
                                'name' => isset($sugg->name) ? $sugg->name : '',
                                'userimage' => base_url() . 'upload/profile/thumbnail/' . $sugg->image,
                                'followed' => 'false'
                            );
                        }
                    }else{
                        $follow_suggestion = array() ;
                    }

                    // add by shiwangi 22 march
                    $followSuggestionId = '';
                    $this->user_model->deleteSuggest($userdetail['userid']);
                    if(count($follow_suggestion)>0){
                    foreach ($follow_suggestion as $foll) {


                        $arr_suggestion['suggest_id'] = '';
                        $arr_suggestion['loginuserid'] = $userdetail['userid'];
                        $arr_suggestion['followuserid'] = $foll['userid'];
                        $arr_suggestion['accept'] = '0';
                        $arr_suggestion['createdDate'] = date('YmdHis');
                        $arr_suggestion['modifiedDate'] = '';
                        $this->user_model->saveSuggestedFollow($arr_suggestion);
                    }
                }
                    $update['user_Id'] = $userdetail['userid'];
                    $update['firstLogin'] = date('YmdHis');
                    $this->user_model->save($update);
                } else {

                    $followSuggestion = $this->user_model->suggestDetails($arr_newSuggest);
                    if (count($followSuggestion) > 0) {
                        foreach ($followSuggestion as $sugg) {

                            $follow_suggestion[] = array(
                                'userid' => $sugg->user_Id,
                                'username' => $sugg->username,
                                'businessName' => isset($sugg->businessName) ? $sugg->businessName : '',
                                'name' => isset($sugg->name) ? $sugg->name : '',
                                'userimage' => base_url() . 'upload/profile/thumbnail/' . $sugg->image,
                                'followed' => 'false'
                            );
                        }
                    } else {
                        $follow_suggestion = '';
                    }
                }

                /* End Suggestion To Follow  */
                $today_birthday = $this->status_model->getbirthdaypeople($userdetail['userid'], '1', $limit, 'timeline', '', $arr_lastSuggest);
//echo '<pre>'; print_r($timeline); exit;
                /* Final response For Timeline  */
                $response = array(
                    "status" => "sucess",
                    "statusMessage" => "Success",
                    "data" => array(
                        "userid" => $userdetail['userid'],
                        "username" => ucfirst(ltrim($userdetail['username'])),
                        "name" => ltrim($userdetail['firstname']) . ' ' . ltrim($userdetail['lastname']),
                        "userimage" => base_url() . "upload/profile/thumbnail/" . $userdetail['image'],
                        "cnt_all_record" => $cnt_all_record,
                        "user_status" => $timeline
                    )
                );

                $suggest = '';
                if ($page == 0) {
                    $response['data']['follow_suggestion'] = $follow_suggestion;
                    $response['data']['today_birthday'] = $today_birthday;
                }
            } else {
                $response = array(
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found."
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }

        echo json_encode($response);
    }

    /* updated by shiwangi (21-01-2016) */

    public function status_like() {
        $userhash = $this->input->post('userHash');
        $status_id = $this->input->post('statusid'); //// Get UserHash
        if (isset($userhash) && $userhash != '' && isset($status_id) && $status_id != '') {
            $this->load->model(array('user_model', 'notification_model', 'status_model'));
            $sucess = 1;
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash); //// Get UserHash Details

            if ($userdetail['status'] == 1) {
                $sucess = 1;
                //// Get Status Id
                if ($status_id != '') {
                    $sucess = 1;
                    $arr_status['status_id'] = $status_id;

                    $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $userdetail['userid']);      //// Check Status Exits or not

                    if (count($user_status) > 0) {
                        $sucess = 1;

                        $arr_like['statusId'] = $status_id;
                        $arr_like['userId'] = $userdetail['userid'];
                        $arr_like['active'] = 1;

                        $like_status = $this->user_model->getlikestatus($arr_like);       //// Check Status Is Liked or not

                        /* Ckeck Status is Shared By loggedin User or Originally Posted */
                        $arr_shared['userId'] = $userdetail['userid'];
                        $arr_shared['statusId'] = $status_id;
                        $shatre_status = $this->user_model->getshareStatus($arr_shared);
                        count($shatre_status) > 0 ? $shared = 'true' : $shared = 'false';

                        /* Get All Reply Of Status */
                        $arr_reply['parentStatusid'] = $status_id;
                        $replys = $this->user_model->getStatusDetails($arr_reply, $row = 1, '', '', 1, 'webservice', '', $userdetail['userid']);

                        /*  If Status is not Liked */
                        if (count($like_status) == 0) {

                            $sucess = 1;
                            $arr_like['createdDate'] = date('YmdHis');
                            $likeid = $this->user_model->saveStatusLike($arr_like);    //// Save like into DataBase
                            //Save Notification
                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $userdetail['userid'];
                            $arr_notice['actionTo'] = $user_status->userid;
                            $arr_notice['action'] = 'L';
                            $arr_notice['actionString'] = 'liked your status';
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = $status_id;
                            $arr_notice['challangeid'] = '';
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = date('YmdHis');
                            $notice_id = $this->notification_model->savenotification($arr_notice);


                            /* like SucessFull Action */
                            if ($likeid != '') {
                                if ($user_status->userid != $userdetail['userid']) {
                                    //code for aws push notification
                                    $deviceInfo = $this->user_model->getdeviceToken($user_status->userid);

                                    if (count($deviceInfo) > 0) {
                                        foreach ($deviceInfo as $device) {


                                            $deviceToken = $device->key;
                                            $deviceType = $device->deviceTypeID;
                                            $title = 'My Test Message';

                                            $sound = 'default';
                                            $msgpayload = json_encode(array(
                                                'aps' => array(
                                                    'alert' => '@'.$userdetail['username'] . ' liked your status',
                                                    "statusid" => $status_id,
                                                    "status" => $user_status->status,
                                                    "userid" => $user_status->userid,
                                                    "username" => $user_status->username,
                                                    "name" => isset($user_status->name) ? $user_status->name : '',
                                                    "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                                    "createdDate" => $user_status->createdDate,
                                                    "liked" => "true",
                                                    "shared" => $shared,
                                                    "reply" => $replys,
                                                    "originalPoster" => $user_status->userid,
                                                    "shareFromUser" => "",
                                                    'type' => 'like',
                                                    'sound' => $sound,
                                            )));


                                            $message = json_encode(array(
                                                'default' => $title,
                                                'APNS_SANDBOX' => $msgpayload
                                            ));

                                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                        }
                                    }
                                }

                                // end


                                $sucess = 1;
                                $statusMessage = 'Status Liked Sucessfully';
                                $user_status = array(
                                    "statusid" => $status_id,
                                    "status" => $user_status->status,
                                    "userid" => $user_status->userid,
                                    "username" => $user_status->username,
                                    "name" => isset($user_status->name) ? $user_status->name : '',
                                    "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                    "createdDate" => $user_status->createdDate,
                                     "url" => $user_status->url,
                                    "liked" => "true",
                                    "shared" => $shared,
                                    "reply" => $replys,
                                    "originalPoster" => $user_status->userid,
                                    "shareFromUser" => "",
                                );
                            } else {
                                /* Like Unsucessfull Action */
                                $sucess = 0;
                                $statusMessage = "Error occoured. Please Like Again";
                            }
                        }
                        else {
                            $sucess = 1;
                            $likeid = $like_status->like_id;         //// Get Like Id
                            $deletelike = $this->user_model->deletelike($likeid);

                            /* Start Delete Notification */
                            $arr_notice[
                                    'statusid'] = $status_id;
                            $arr_notice['actionFrom'] = $userdetail['userid'];
                            $arr_notice['action'] = 'L';
                            $arr_notice['active'] = 1;

                            $this->notification_model->delete_notification($arr_notice);

                            /* Unline SucessFull Action */
                            if ($deletelike == 1) {

                                $sucess = 1;
                                $statusMessage = 'Status Unliked Sucessfully';

                                $user_status = array(
                                    "statusid" => $status_id,
                                    "status" => $user_status->status,
                                    "userid" => $user_status->userid,
                                    "username" => $user_status->username,
                                    "name" => isset($user_status->name) ? $user_status->name : '',
                                    "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                    "createdDate" => $user_status->createdDate,
                                     "url" => $user_status->url,
                                    "liked" => "false",
                                    "shared" => $shared,
                                    "reply" => $replys,
                                    "originalPoster" => $user_status->userid,
                                    "shareFromUser" => "",
                                );
                            } else {
                                /* Unlike Unsucessfull Action  */
                                $sucess = 0;
                                $statusMessage = "Error occoured. Please Unlike Again";
                            }
                        }
                    } else {
                        $sucess = 0;
                        $statusMessage = "Error occoured. Status not found";
                    }
                } else {
                    $sucess = 0;
                    $statusMessage = "Error occoured. Status not found";
                }
            } else {
                $sucess = 0;
                $statusMessage = "Error occoured. User not found";
            }
        } else {
            $sucess = 0;
            $statusMessage = "Error occoured. Parameters not found or values cannot be blank.";
        }

        if ($sucess != 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "sucess",
                    "statusMessage" => $statusMessage,
                    "user_status" => $user_status
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "st atus" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function testForm() {
        $this->load->view('test');
    }

    public function status_reply() {
        $userhash = $this->input->post('userHash');
        $status_id = $this->input->post('statusid');   //// Get Status Id
        $usermentioned = $this->input->post('usermentioned'); //// Get UserHash
        $reply = $this->input->post('status');


        if (isset($userhash) && $userhash != '' && isset($status_id) && $status_id != '' && isset($usermentioned) && isset($reply)) {
            $this->load->model(array('user_model', 'notification_model', 'status_model'));
            $this->load->library('image_lib');
            $sucess = 1;
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);  //// Get UserHash Details

            if ($userdetail['status'] == 1) {
                $sucess = 1;
                if ($status_id != '') {
                    $reply = isset($reply) ? $reply : '';

                    $sucess = 1;
                    $arr_status['status_id'] = $status_id;

                    $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $userdetail['userid']);      //// Check Status Exits or not
                    $mainstatusUserId = $user_status->userid;
                    if (count($user_status) > 0) {
                        /* Status Exits  */
                        $sucess = 1;
                        $videoThumb = '';
                        /* Either Image or status   */
                        if (@$_FILES['image']['size'] > 0 || $reply != '') {
                            if (@$_FILES['image']['size'] > 0) {
                                $sucess = 1;
                                $extionArray = array('jpg', 'jpeg', 'png', 'gif');
                                $uploads_dir = 'upload/status_image/full/' . $userdetail['userid'];
                                $mediumImagePath = 'upload/status_image/medium/' . $userdetail['userid'];
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
                                // Image upload in full size in profile directory
                                $this->load->library('image_lib');
                                //$extionArray = array('jpg','jpeg','png','gif');
                                //$uploads_dir = 'upload/status_image/full';
                                $tmp_name = $_FILES["image"]["tmp_name"];
                                $name = mktime() . $_FILES["image"]["name"];
                                move_uploaded_file($tmp_name, "$uploads_dir/$name");

                                // image resize in thumbnail size in thumbnail directory
                                $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                                if (in_array($ext, $extionArray)) {
                                    $config['image_library'] = 'gd2';
                                    $config['source_image'] = $path . "/" . $name;
                                    $config['new_image'] = $mediumpath . '/' . $name;

                                    $config['maintain_ratio'] = TRUE;
                                    $config['width'] = 400;
                                    $config['height'] = 350;
                                    $this->image_lib->initialize($config);
                                    $rtuenval = $this->image_lib->resize();
                                    $this->image_lib->clear();
                                    $videoThumb = '';
                                    // video thumbnail code
                                } else {

                                    $videoThumbPath = 'upload/videoThumb' . '/' . $userdetail['userid'];
                                    if (!is_dir($videoThumbPath)) {
                                        if (mkdir($videoThumbPath, 0777, true)) {
                                            $thumbPath = $videoThumbPath;
                                        } else {
                                            $thumbPath = $videoThumbPath;
                                        }
                                    } else {
                                        $thumbPath = $videoThumbPath;
                                    }
                                    $dirPath = $_SERVER['DOCUMENT_ROOT'];
                                    //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                                    $videothumb = strtotime(date('Ymdhis')) . 'thumb.png';
                                    $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . ' -frames:v 1 -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;
                                    exec($cmd . ' ' . '2>&1', $out, $res);


                                    $videoThumb = $userdetail['userid'] . '/' . $videothumb;
                                }
                                // end video thumbnail code
                                $status_image = $userdetail['userid'] . '/' . $name;
                            } else {
                                $status_image = '';
                            }
                            /* Save Reply In Database */
                            $arr_reply['status_id'] = '';
                            $arr_reply['parentStatusid'] = $status_id;
                            $arr_reply['status'] = $reply;
                            $arr_reply['usermentioned'] = $usermentioned;
                            $arr_reply['userid'] = $userdetail['userid'];
                            $arr_reply['status_image'] = $status_image;
                            $arr_reply['media_thumb'] = $videoThumb;
                            $arr_reply['createdDate'] = date('YmdHis');

                            $laststatusid = $this->user_model->saveUserStatus($arr_reply);    //// Save Reply
                            /* echo $this->db->last_query();
                             */

                            // Save notification for reply
                            if ($userdetail['userid'] != $mainstatusUserId) {   //if loggedin user is not parent user of status
                                $arr_notice['notification_id'] = '';
                                $arr_notice['actionFrom'] = $userdetail['userid'];
                                $arr_notice['actionTo'] = $mainstatusUserId;
                                $arr_notice['action'] = 'R';
                                $arr_notice['actionString'] = 'replied to you!';
                                $arr_notice['message'] = '';
                                $arr_notice['statusid'] = $status_id;
                                $arr_notice['challangeid'] = '';
                                $arr_notice['active'] = '1';
                                $arr_notice['createdDate'] = date('YmdHis');
                                $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                                if ($user_status->userid != $userdetail['userid']) {
                                    // send notification code start
                                    $deviceInfo = $this->user_model->getdeviceToken($mainstatusUserId);
                                    if (count($deviceInfo) > 0) {
                                        foreach ($deviceInfo as $device) {
                                            $deviceToken = $device->key;
                                            $deviceType = $device->deviceTypeID;
                                            $title = 'My Test Message';
                                            $sound = 'default';
                                            $msgpayload = json_encode(array(
                                                'aps' => array(
                                                    'alert' => '@'.$userdetail['username'] . ' replied to you!',
                                                    'statusid' => $status_id,
                                                    "type" => 'reply',
                                                    'sound' => $sound
                                            )));


                                            $message = json_encode(array(
                                                'default' => $title,
                                                'APNS_SANDBOX' => $msgpayload
                                            ));

                                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                        }
                                    }

                                    // end
                                }
                            }

                            //Save notification for user mention
                            $arr = explode(',', $usermentioned);
                            foreach ($arr as $username) {
                                $select = 'user_Id, username';
                                $where['username'] = trim($username);
                                //If username is not loggedin username
                                if ($userdetail['username'] != $username) {
                                    $usermentionResults = $this->user_model->getOneUserDetails($where, $select, 1);
                                    if (count($usermentionResults) > 0) {

                                        foreach ($usermentionResults as $mention) {
                                            $mentionuserid = $mention->user_Id;
                                        }

                                        $arr_notice['notification_id'] = '';
                                        $arr_notice['actionFrom'] = $userdetail['userid'];
                                        $arr_notice['actionTo'] = $mentionuserid;
                                        $arr_notice['action'] = 'M';
                                        $arr_notice['actionString'] = 'mentioned you!';
                                        $arr_notice['message'] = '';
                                        $arr_notice['statusid'] = $status_id;
                                        $arr_notice['challangeid'] = '';
                                        $arr_notice['active'] = '1';
                                        $arr_notice['createdDate'] = date('YmdHis');
                                        $notice_id = $this->notification_model->savenotification($arr_notice);   //// Save Notifiaction in Database
                                        // send notification code start
                                        $deviceInfo = $this->user_model->getdeviceToken($mentionuserid);
                                        if (count($deviceInfo) > 0) {
                                            foreach ($deviceInfo as $device) {
                                                $deviceToken = $device->key;
                                                $deviceType = $device->deviceTypeID;
                                                $title = 'My Test Message';
                                                $sound = 'default';
                                                $msgpayload = json_encode(array(
                                                    'aps' => array(
                                                        'alert' => '@'. $userdetail['username'] . ' mentioned you!',
                                                        'statusid' => $status_id,
                                                        "type" => 'usermention',
                                                        'sound' => $sound
                                                )));


                                                $message = json_encode(array(
                                                    'default' => $title,
                                                    'APNS_SANDBOX' => $msgpayload
                                                ));


                                                $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                            }
                                        }

                                        // end
                                    }
                                }
                            }

                            if ($laststatusid != '') {
                                $sucess = 1;
                                $statusMessage = 'Your reply has been added successfully';
                            } else {
                                $sucess = 0;
                                $statusMessage = "Error occoured. Please Post Your Reply Again ";
                            }
                        } else {
                            $sucess = 0;
                            $statusMessage = "Error occoured. Please Post Either Comment or Image";
                        }
                    } else {
                        $sucess = 0;
                        $statusMessage = "Error occoured. Status not found";
                    }
                } else {
                    $sucess = 0;
                    $statusMessage = "Error occoured. Status not found";
                }
            } else {
                $sucess = 0;
                $statusMessage = "Error occoured. User not found";
            }
        } else {
            $sucess = 0;
            $statusMessage = "Error occoured. Parameters not found or values cannot be blank.";
        }
        if ($sucess != 0) {
            /* Sucess Responce */
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "sucess",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            /* Error Responce  */
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function share_status() {
        $userhash = $this->input->post('userHash');
        $status_id = $this->input->post('status_id'); //// Get Status Id//// Get UserHash
        if (isset($userhash) && $userhash != '' && isset($status_id) && $status_id != '') {
            $this->load->model(array('user_model', 'notification_model', 'status_model'));

            $sucess = 1;
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);  //// Get UserHash Details
            if ($userdetail['status'] == 1) {
                $sucess = 1;

                if ($status_id != '') {
                    $sucess = 1;
                    $arr_status['status_id'] = $status_id;


                    $original_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $userdetail['userid']); //// Check Status Exits or not
                    //$statusDetail = array();
                    $statusDetail['status'] = $original_status->status;
                    $statusDetail['originalPosterId'] = $original_status->originalPosterId;
                    $statusDetail['status_image'] = $original_status->original_status_image;
                    $statusDetail['media_thumb'] = $original_status->media_thumb;
                    $statusDetail['createdDate'] = date('YmdHis');
                    // start update orignal status's share users ids
                    $laststatus = $this->status_model->updateOrignalStatus($status_id, $original_status->shareFromUsers, $userdetail['userid']);

                    $laststatus = $this->status_model->saveStatus($statusDetail);
                    $arr_status['status_id'] = $laststatus->status_id;
                    $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $userdetail['userid']);

                    if (count($user_status) > 0) {
                        //$sucess = 1;
                        // $arr_shared['userId'] = $userdetail['userid'];
                        // $arr_shared['statusId'] = $status_id;
                        // $shatre_status = $this->user_model->getshareStatus($arr_shared);
                        // if (count($shatre_status) == 0) {
                        $sucess = 1;
                        $arr_share['share_id'] = '';
                        $arr_share['statusId'] = $user_status->status_id;
                        // $arr_share['shareFromUserId'] = $user_status->userid;
                        // $arr_share['userId'] = $userdetail['userid'];
                        $arr_share['shareFromUserId'] = $userdetail['userid'];
                        $arr_share['userId'] = $user_status->userid;
                        $arr_share['createdDate'] = date('YmdHis');
                        $shareid = $this->user_model->savesharestatus($arr_share);

                        if ($shareid != '') {
                            $sucess = 1;

                            $arr_like['statusId'] = $user_status->status_id;
                            $arr_like['userId'] = $userdetail['userid'];
                            $arr_like['active'] = 1;

                            $like_status = $this->user_model->getlikestatus($arr_like); //// Check Status Is Liked or not

                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $userdetail['userid'];
                            $arr_notice['actionTo'] = $user_status->userid;
                            $arr_notice['action'] = 'SS';
                            $arr_notice['actionString'] = 'shared your status';
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = $user_status->status_id;
                            $arr_notice['challangeid'] = '';
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = date('YmdHis');

                            $notice_id = $this->notification_model->savenotification($arr_notice);

                            if (count($like_status) == 1) {
                                $liked = "true";
                            } else {
                                $liked = "false";
                            }
                            /* Get Reply Of Same Status */
                            $arr_reply['parentStatusid'] = $status_id;
                            $replys = $this->user_model->getStatusDetails($arr_reply, $row = 1, '', '', 1, 'webservice', '', $userdetail['userid']);

                            $originalPoster = array(
                                "userid" => $original_status->userid,
                                "username" => $original_status->username,
                                "userimage" => $original_status->userimage
                            );
                            $shareFromUser = array(
                                "userid" => $userdetail['userid'],
                                "username" => $userdetail['username'],
                                "userimage" => base_url() . 'upload/profile/thumbnail/' . $userdetail['image'],
                            );

                            $statusMessage = 'Status Shared Sucessfully';
                            if ($user_status->userid != $userdetail['userid']) {
                                // send notification code start
                                $deviceInfo = $this->user_model->getdeviceToken($original_status->userid);
                                if (count($deviceInfo) > 0) {
                                    foreach ($deviceInfo as $device) {
                                        $deviceToken = $device->key;
                                        $deviceType = $device->deviceTypeID;
                                        $title = 'My Test Message';
                                        $sound = 'default';


                                        $title = 'My Test Message';
                                        $sound = 'default';

                                        $msgpayload = json_encode(array(
                                            'aps' => array(
                                                'alert' => '@'. $userdetail['username'] . ' shared your status!',
                                                "statusid" => $user_status->status_id,
                                                "status" => $user_status->status,
                                                "user id" => $user_status->userid,
                                                "username" => $user_status->username,
                                                "name" => isset($user_status->name) ? $user_status->name : '',
                                                "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                                "createdDate" => $user_status->createdDate,
                                                "liked" => $liked,
                                                "shared" => "true",
                                                "reply" => $replys,
                                                "originalPoster" => $originalPoster,
                                                "shareFromUser" => $shareFromUser,
                                                'type' => 'share',
                                                'sound' => $sound
                                        )));


                                        $message = json_encode(array('default' => $title,
                                            'APNS_SANDBOX' => $msgpayload
                                        ));
                                        //$message = 'Shared Status: '.$userdetail['username'].' shared your status!';

                                        $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                    }
                                }
                            }

                            // end

                            $user_status = array(
                                "statusid" => $user_status->status_id,
                                "status" => $user_status->status,
                                "userid" => $user_status->userid,
                                "username" => $user_status->username,
                                "name" => isset($user_status->name) ? $user_status->name : '',
                                "userimage" => isset($user_status->userimage) ? $user_status->userimage : '',
                                "createdDate" => $user_status->createdDate,
                                "media_thumb" => $user_status->username,
                                "originalMedia" => $user_status->originalMedia,
                                "liked" => $liked,
                                "shared" => "true",
                                "reply" => $replys,
                                "originalPoster" => $originalPoster,
                                "shareFromUser" => $shareFromUser,
                            );
                        } else {
                            $sucess = 0;
                            $statusMessage = "Error occoured. Please Share this Status Again";
                        }
                        // } else {
                        //     $sucess = 0;
                        //     $statusMessage = "Error occoured. You Have Already Shared This Status";
                        // }
                    } else {
                        $sucess = 0;
                        $statusMessage = "Error occoured. Status not found";
                    }
                } else {
                    $sucess = 0;
                    $statusMessage = "Error occoured. Status not found";
                }
            } else {
                $sucess = 0;
                $statusMessage = "Error occoured. User not found";
            }
        } else {
            $sucess = 0;
            $statusMessage = "Error occoured. Parameters not found or values cannot be blank.";
        }
        if ($sucess != 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "sucess",
                    "statusMessage" => $statusMessage,
                    "user_status" => $user_status
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function countries() {

        $this->load->model('country_model');
        $countries = $this->country_model->get_countries();

        $response = array(
            "data" => array(
                "status" => "success",
                "countries" => $countries
            )
        );
        echo json_encode($response);
        exit;
    }

    public function store() {
        $userhash = $this->input->post('userHash');
        $page = $this->input->post('page');
        if (isset($userhash) && ($userhash != '') && isset($page)) {
            $this->load->model(array('user_model', 'store_model', 'score_model'));
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);
            if ($userdetail['status'] == 1) {
                $userid = $userdetail['userid'];
                $records = $this->store_model->products();
                $data['records'] = count($records);

                $config['base_url'] = base_url() . 'index.php/userservice/store/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;
                $limit = $config['per_page'];
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $order = 'ASC';
                $stores = $this->store_model->products($page, $limit, $order);
                $coins = $this->score_model->getUserCoins($userid);
                $coins = $coins->coins;
                $i = 0;
                $arr_store = array();

                foreach ($stores as $store) {
                    $store_id = $store->store_id;
                    $item = $store->item;
                    $store_coins = $store->coins;
                    $description = $store->description;
                    $image = base_url() . 'upload/store/' . $store->image;
                    $regionid = $store->region_id;
                    if ($regionid == 0) {
                        $location = 'All Regions';
                    } else {
                        $region_name = $this->user_model->getregion($regionid);
                        $location = $region_name->region_name;
                    }
                    if ($coins >= $store_coins) {
                        $collect = 'Collect';
                    } else {
                        $collect = 'Not enough coins';
                    }

                    $store = array(
                        'storeid' => $store_id,
                        'item' => $item,
                        'coins' => $store_coins,
                        'description' => $description,
                        'image' => $image,
                        'region' => $location,
                        'purchaseable' => $collect
                    );

                    $arr_store[] = $store;
                    $i++;
                }

                $response = array(
                    "data" => array(
                        "status" => "success",
                        "store" => $arr_store
                    )
                );
            } else {
                $response = array(
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function redeemCoins() {
        $userhash = $this->input->post('userHash');
        $store_id = $this->input->post('storeid');
        $name = $this->input->post('name');
        $address = $this->input->post('address');
        $country = $this->input->post('country');
        $postcode = $this->input->post('postcode');
        if (isset($userhash) && $userhash != '' && isset($store_id) && $store_id != '' && isset($name) && $name != '' && isset($address) && $address != '' && isset($country) && $country != '' && isset($postcode) && $postcode != '') {
            $this->load->model(array('user_model', 'store_model', 'email_model', 'score_model'));
            $userhash = $this->input->post('userHash');
            $userdetail = $this->user_model->getuserHash($userhash);

            if ($userdetail['status'] == 1) {

                $store_product = $this->store_model->product_detail($store_id);
                $userid = $userdetail['userid'];
                $username = $userdetail['username'];
                $email = $userdetail['email'];
                $item = $store_product->item;
                $coins = $store_product->coins;



                if (trim($name) != '' && trim($address) != '' && trim($country) != '' && trim($postcode) != '') {

                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// GET EMAIL FROM DATABASE

                    $email_template = $this->email_model->getoneemail('store');

                    //// MESSAGE OF EMAIL
                    $messages = $email_template->message;


                    $hurree_image = base_url() . 'assets/template/hurree/images/app-icon.png';
                    $appstore = base_url() . 'hurree_images/appstore.gif';
                    $googleplay = base_url() . 'hurree_images/googleplay.jpg';

                    //// replace strings from message
                    $messages = str_replace('{Username}', ucfirst($username), $messages);
                    $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                    $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                    $messages = str_replace('{Google_Image}', $googleplay, $messages);
                    $messages = str_replace('{Product}', $item, $messages);


                    //// FROM EMAIL
                    $this->email->from($email_template->from_email, 'Hurree');
                    $this->email->to($email);
                    $this->email->subject($email_template->subject);
                    $this->email->message($messages);
                    $this->email->send();    ////  EMAIL SEND
                    //// FROM EMAIL
                    $messages = '<p>Product Ordered: ' . $item . '</p><p>No. of Coins Redeemed: ' . $coins . '</p><p>Full Name: ' . $name . '</p><p>Address: ' . $address . '</p><p>Country: ' . $country . '</p><p>Post/Zip Code: ' . $postcode . '</p>';
                    $this->email->from($email, $name);
                    $this->email->to('Store@Hurree.co');
                    $this->email->subject('A user redeemed coins on Hurree');
                    $this->email->message($messages);
                    $this->email->send();    ////  EMAIL SEND
                    //Insert
                    $insert = array(
                        'userid' => $userid,
                        'coins' => $coins,
                        'coins_type' => 5,
                        'game_id' => 0
                    );

                    $this->score_model->insert($insert);

                    //Update User Coins

                    $userTotalCoins = $this->score_model->getUserCoins($userid);
                    $updateCoins = $userTotalCoins->coins - $coins;

                    $update = array(
                        'userid' => $userid,
                        'coins' => $updateCoins
                    );
                    $this->score_model->update($update);

                    $response = array(
                        "data" => array(
                            "status" => "succeess",
                            "statusMessage" => "Our Bots are working on your order"
                        )
                    );
                } else {
                    $response = array(
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "Error occoured. We need your details"
                        )
                    );
                }
            } else {
                $response = array(
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function sign_up() {
        $this->load->model(array('user_model', 'score_model', 'email_model'));
        //// GET POST VALUE
        $name = $this->input->post('fullname');
        $email = $this->input->post('email');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $service = $this->input->post('service');
        $gender = $this->input->post('gender');
        $dob = $this->input->post('date_of_birth');
        $editable = 0; //For normal sign up

        if ($service == 'facebook' || $service == 'twitter') {
            $password = $this->user_model->createTempPassword(6);
            $id = $this->input->post('id');

            $editable = 1; //For Social Sign up
        }


        //// CHECK THIS EMAIL EXITS IN DATABASE
        $cnt_email = $this->user_model->check_email($email);
        $cnt_username = $this->user_model->checkfield('username', $username);
//echo $name.'name'.'<br>'; echo $email.'email'.'<br>'; echo $username.'username'.'<br>'; echo $password.'pwd'.'<br>'; echo $service.'service'.'<br>'; echo $gender.'gender'.'<br>'; echo $dob.'dob'; exit;
        if (isset($name) && isset($email) && isset($username) && isset($password) && isset($service) && isset($gender) && isset($dob) && $name != '' && $email != '' && $username != '' && $dob != '' && $gender != '' && $service != '' && $password != '') {


            if ($cnt_email == 0 && count($cnt_username) == 0) {   //// NOT EXITS
                $sucess = 1;
                $arr_name = explode(' ', trim($name));
                $cnt_name = count($arr_name);

                //$firstname = '';

                $firstname = $arr_name[0];
                $lastname = isset($arr_name[1]) ? $arr_name[1] : '';



                //// CREATE ARRAY TO SAVE INTO DATABASE
                $date = date('YmdHis');

                $sign['user_Id'] = '';
                $sign['firstname'] = $firstname;
                $sign['lastname'] = $lastname;
                $sign['password'] = md5($password);
                $sign['email'] = $email;
                $sign['username'] = $username;
                $sign['image'] = 'user.png';
                $sign['header_image'] = 'profileBG.jpg';
                $sign['usertype'] = 1;   //For Consumer
                $sign['active'] = 1;
                $sign['editable'] = $editable;
                $sign['loginSource'] = $service;
                if ($service == 'facebook') {
                    $sign['fbid'] = $id;
                }
                if ($service == 'twitter') {
                    $sign['twitterid'] = $id;
                }
                $sign['date_of_birth'] = $dob;
                $sign['gender'] = $gender;
                $sign['firstLogin'] = $date;
                $sign['createdDate'] = $date;
                $sign['modifiedDate'] = $date;


                //// SAVE INTO USERS TABLE
                $insrt_id = $this->user_model->insertNewuser($sign);
                //// SAVE INTO USERS TABLE
                $userData['user_id'] = $this->db->insert_id();
                $userData['tempPwd'] = base64_encode($password);
                $this->user_model->tempPassword($userData);

                /* Start first Login Time Details */

                $arr_time['suggestion_id'] = '';
                $arr_time['userid'] = $insrt_id;
                $arr_time['suggestionTime'] = $sign['createdDate'];
                $arr_time['createdDate'] = $sign['createdDate'];
                $this->user_model->savesuggestionTime($arr_time);

                /* End first Login Time Details */
                $userCoins = array(
                    'userid' => $insrt_id,
                    'coins' => '200',
                    'coins_type' => '8',
                    'game_id' => '0',
                    'businessid' => '0',
                    'actionType' => 'add',
                    'createdDate' => date('YmdHis')
                );
                $this->score_model->insertCoins($userCoins);

                $coins = array(
                    'userid' => $insrt_id,
                    'coins' => '200'
                );
                $this->score_model->signupCoins($coins);

                if ($insrt_id != '') {
                    $sucess = 1;
                    $userimage = base_url() . 'upload/profile/thumbnail/user.png';

                    $statusMessage = "Registration successful";

                    $userdetails = array(
                        'email' => $email,
                        'username' => $username,
                        'name' => $firstname . ' ' . $lastname,
                        'userid' => $insrt_id,
                        'userimage' => $userimage,
                        'date_of_birth' => $dob,
                        'gender' => $gender,
                        'password' => $password
                    );
                    //$statusMessage='Choose a different username';
                } else {
                    $sucess = 0;
                    $statusMessage = 'Error occourd';
                }
            } else {
                $sucess = 0;
                if ($cnt_email > 0) {
                    $statusMessage = 'Email already exists';
                } else {
                    $statusMessage = 'Choose a different username';
                }
            }
        } else {
            $sucess = 0;
            $statusMessage = 'Please enter all details';
        }

        if ($sucess == 1) {

            //// SEND  EMAIL START
            $this->emailConfig();   //Get configuration of email
            //// GET EMAIL FROM DATABASE

            $email_template = $this->email_model->getoneemail('consumer_signup');

            //// MESSAGE OF EMAIL
            $messages = $email_template->message;


            $hurree_image = base_url() . 'assets/template/frontend/img/consumer_signup.png';
            $appstore = base_url() . 'hurree_images/appstore.gif';
            $googleplay = base_url() . 'hurree_images/googleplay.jpg';

            //// replace strings from message
            $messages = str_replace('{Username}', ucfirst($username), $messages);
            $messages = str_replace('{Password}', $password, $messages);
            $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
            //$messages = str_replace('{App_Store_Image}', $appstore, $messages);
            //$messages = str_replace('{Google_Image}', $googleplay, $messages);
            //// FROM EMAIL
            $this->email->from($email_template->from_email, 'Hurree');
            $this->email->to($email);
            $this->email->subject($email_template->subject);
            $this->email->message($messages);
            $this->email->send();    ////  EMAIL SEND
            //// END EMAIL
            //// RESPONCE ON SUCCESS
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "user" => $userdetails
                )
            );
        } else {
            //// RESPONCE ON FAILURE
            $response = array(
                "c2dictionary" => true, "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        }
        //// CONVERT INTO JSON
        echo json_encode($response);
        exit;
    }

    public function search_users() {
        $userhash = $this->input->post('userHash');
        $search = $this->input->post('search');
        if (isset($userhash) && $userhash != '' && isset($search) && $search != '') {
            $this->load->model('user_model');
            $search = str_replace("@", "", $search);
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {

                $searchs = $this->user_model->search_user($search);

                if (count($searchs) > 0) {
                    $i = 0;
                    foreach ($searchs as $search) {
                        if (isset($search->image)) {
                            $userimage = base_url() . 'upload/profile/thumbnail/' . $search->image;
                        } else {
                            $userimage = '';
                        }
                        $userid = $search->user_Id;
                        $email = $search->email;
                        $username = $search->username;
                        $firstname = isset($search->firstname) ? $search->firstname : '';
                        $lastname = isset($search->lastname) ? $search->lastname : '';
                        $userdetails[$i] = array
                            (
                            'userid' => $userid,
                            'username' => $username,
                            'name' => $firstname . " " . $lastname,
                            'email' => $email,
                            'userimage' => $userimage
                        );
                        $i++;
                    }


                    $response = array(
                        "status" => "success",
                        "data" => $userdetails
                    );
                } else {
                    $response = array(
                        "status" => "error",
                        "statusMessage" => "No record found"
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode
                ($response);
        exit;
    }

    public function updateBio() {
        $userhash = $this->input->post('userHash');
        $username = $this->input->post('username');
        $name = $this->input->post('fullname');
        $location = $this->input->post('location');
        $bio = $this->input->post('bio');
        if (isset($userhash) && $userhash != '' && isset($username) && $username != '' && isset($name) && $name != '' && isset($location) && isset($bio)) {
            $this->load->model('user_model');
            $userhash = $this->input->post('userHash');
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                //echo $userdetail['username'];

                $arr_name = explode(' ', trim($name));
                $cnt_name = count($arr_name);

                $firstname = '';
                for ($i = 0; $i < $cnt_name - 1; $i++) {
                    $firstname = $firstname . ' ' . $arr_name[$i];
                }

                $lastname = $arr_name[$i];

                if ($userdetail['username'] == $username) {


                    $update = array(
                        'user_Id' => $userdetail['userid'],
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'location' => $location,
                        'bio' => $bio,
                        'modifiedDate' => date('YmdHis')
                    );

                    $this->user_model->updatebio($update);

                    $user_detail = $this->user_model->getOneUser($userdetail['userid']);

                    $dateOfBirth = $user_detail->date_of_birth;
                    $birthDate = explode("-", $dateOfBirth);
                    $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[1], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));

                    if ($user_detail->usertype == 1) {
                        $type = 'Consumer';
                    }
                    if ($user_detail->usertype == 2) {
                        $type = 'Business';
                    }
                    if ($user_detail->usertype == 3) {
                        $type = 'Admin';
                    }
                    $firstName = isset($user_detail->firstname) ? $user_detail->firstname : '';
                    $lastName = isset($user_detail->lastname) ? $user_detail->lastname : '';
                    if ($user_detail->image) {
                        $image = base_url() . 'upload/profile/thumbnail/' . $user_detail->image;
                    } else {
                        $image = '';
                    }
                    $location = isset($user_detail->location) ? $user_detail->location : '';
                    $bio = isset($user_detail->bio) ? $user_detail->bio : '';
                    $gender = isset($user_detail->gender) ? $user_detail->gender : '';
                    $user = array(
                        'userid' => $user_detail->user_Id,
                        'username' => $user_detail->username,
                        'email' => $user_detail->email,
                        'name' => $firstName . " " . $lastName,
                        'userimage' => $image,
                        'type' => $type,
                        'header_image' => base_url() . 'upload/headerimage/resize/' . $user_detail->header_image,
                        'location' => $location,
                        'bio' => $bio,
                        'age' => $age,
                        'gender' => $gender,
                        'loginSource' => $user_detail->loginSource
                    );

                    $response = array(
                        "c2dictionary" => true,
                        "data" => array("status" => "success", "statusMessage" => "Profile details updated successfully",
                            "user" => $user
                        )
                    );
                } else {

                    $select = '*';
                    $where['username'] = $username;
                    $where['active'] = 1;

                    $userExit = $this->user_model->getOneUserDetails($where, $select);
                    if (count($userExit) == 0) {

                        $update = array(
                            'user_Id' => $userdetail[
                            'userid'],
                            'username' => $username,
                            'firstname' => $firstname,
                            'lastname' => $lastname,
                            'location' => $location,
                            'bio' => $bio,
                            'editable' => 0, //New Change
                            'modifiedDate' => date('YmdHis')
                        );

                        $this->user_model->updatebio($update);

                        $user_detail = $this->user_model->getOneUser($userdetail['userid']);
                        if ($user_detail->usertype == 1) {
                            $type = 'Consumer';
                        }
                        if ($user_detail->usertype == 2) {
                            $type = 'Business';
                        }
                        if ($user_detail->usertype == 3) {
                            $type = 'Admin';
                        }
                        $firstName = isset($user_detail->firstname) ? $user_detail->firstname : '';
                        $lastName = isset($user_detail->lastname) ? $user_detail->lastname : '';
                        if ($user_detail->image) {
                            $image = base_url() . 'upload/profile/thumbnail/' . $user_detail->image;
                        } else {
                            $image = '';
                        }
                        $location = isset($user_detail->location) ? $user_detail->location : '';
                        $bio = isset($user_detail->bio) ? $user_detail->bio : '';

                        $user = array(
                            'userid' => $user_detail->user_Id,
                            'username' => $user_detail->username,
                            'email' => $user_detail->email,
                            'name' => $firstName . " " . $lastName,
                            'userimage' => $image,
                            'type' => $type,
                            'header_image' => base_url() . 'upload/headerimage/resize/' . $user_detail->header_image,
                            'location' => $location,
                            'bio' => $bio,
                            'loginSource' => $user_detail->loginSource
                        );

                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "Username and profile details updated successfully",
                                "user" => $user
                            )
                        );
                    } else {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "error",
                                "statusMessage" => "Error occoured. This Username already Exits. Please choose different one"
                            )
                        );
                    }
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // This service for change user profile pic
    public function image() {
        $userhash = $this->input->post('userHash');
        if (isset($userhash) != '' && $userhash != '') {
            $this->load->model('user_model');
            $this->load->library('image_lib');
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $success = 1;

                if (@$_FILES['image']['size'] > 0) {
                    $uploads_dir = 'upload/profile/full/' . $userdetail['userid'];
                    $tmp_name = $_FILES["image"]["tmp_name"];
                    $name = mktime() . $_FILES["image"]["name"];
                    list($width, $height) = getimagesize($tmp_name);
                    $dirPath = $_SERVER['DOCUMENT_ROOT'];
                    $fullStructure = $uploads_dir;
                    $mediumStructure = 'upload/profile/medium/' . $userdetail['userid'];
                    $thumnailStructure = 'upload/profile/thumbnail/' . $userdetail['userid'];
                    if (!is_dir($fullStructure)) {
                        if (mkdir($fullStructure, 0777, true)) {
                            $path = $fullStructure;
                        } else {
                            $path = $fullStructure;
                        }
                    } else {
                        $path = $fullStructure;
                    }
                    if (!is_dir($mediumStructure)) {
                        if (mkdir($mediumStructure, 0777, true)) {
                            $mediumpath = $mediumStructure;
                        } else {
                            $mediumpath = $mediumStructure;
                        }
                    } else {
                        $mediumpath = $mediumStructure;
                    }
                    if (!is_dir($thumnailStructure)) {
                        if (mkdir($thumnailStructure, 0777, true)) {
                            $thumnailpath = $thumnailStructure;
                        } else {
                            $thumnailpath = $thumnailStructure;
                        }
                    } else {
                        $thumnailpath = $thumnailStructure;
                    }

                    if ($height < $width) {

                        $cmd = 'sudo convert ' . $tmp_name . ' -background transparent -rotate 90 ' . $path . '/' . $name;
                        exec($cmd . ' ' . '2>&1', $out, $res);

                        if ($res) {
                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "error",
                                    "statusMessage" => "Error occoured. Error during image upload."
                                )
                            );
                            echo json_encode($response);
                            exit;
                        }
                    } // height if
                    else {

                        if (!move_uploaded_file($tmp_name, "$uploads_dir/$name")) {
                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "error",
                                    "statusMessage" => "Error occoured. Image not upload."
                                )
                            );
                            echo json_encode($response);
                            exit;
                        }
                    }
                    //move_uploaded_file($tmp_name, "$uploads_dir/$name");
                    // image resize in medium size in medium directory

                    $config['image_library'] = 'gd2';
                    $config['source_image'] = "upload/profile/full/" . $userdetail['userid'] . '/' . $name;
                    $config['new_image'] = 'upload/profile/medium/' . $userdetail['userid'] . '/' . $name;
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 300;
                    $config['height'] = 300;
                    $this->image_lib->initialize($config);
                    $rtuenval = $this->image_lib->resize();
                    $this->image_lib->clear();

                    //Image resize in thumbnail size in thumbnail directory

                    $config['image_library'] = 'gd2';
                    $config['source_image'] = "upload/profile/full/" . $userdetail['userid'] . '/' . $name;
                    $config['new_image'] = 'upload/profile/thumbnail/' . $userdetail['userid'] . '/' . $name;
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 100;
                    $config['height'] = 100;
                    $this->image_lib->initialize($config);
                    $rtuenval = $this->image_lib->resize();
                    $this->image_lib->clear();
                    // Image Upload End
                    $return['image'] = $name;

                    $data = array(
                        'user_Id' => $userdetail['userid'],
                        'image' => $userdetail['userid'] . '/' . $name
                    );

                    $this->user_model->uploadImage($data);
                    $userid = $userdetail['userid'];

                    if ($userdetail['usertype'] == 1) {
                        $type = 'Consumer';
                    }
                    if ($userdetail['usertype'] == 2) {
                        $type = 'Business';
                    }
                    if ($userdetail['usertype'] == 3) {
                        $type = 'Admin';
                    }
                    $firstName = isset($userdetail['firstname']) ? $userdetail['firstname'] : '';
                    $lastName = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                    if ($userdetail['image']) {
                        $image = base_url() . 'upload/profile/thumbnail/' . $userdetail['image'];
                    } else {
                        $image = '';
                    }
                    $location = isset($userdetail['location']) ? $userdetail['location'] : '';
                    $bio = isset($user_detail->bio) ? $user_detail->bio : '';
                    $gender = isset($userdetail['bio']) ? $userdetail['bio'] : '';
                    $user = array
                        (
                        'userid' => $userdetail['userid'],
                        'username' => $userdetail['username'],
                        'email' => $userdetail['email'],
                        'name' => $firstName . " " . $lastName,
                        'userimage' => $image,
                        'type' => $type,
                        'header_image' => base_url() . 'upload/headerimage/resize/' . $userdetail[
                        'header_image'],
                        'location' => $location,
                        'bio' => $bio,
                        'loginSource' => $userdetail['loginSource']
                    );

                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "user" => $user,
                        )
                    );
                } else {
                    $return['image'] = '';

                    $response = array(
                        "status" => "error",
                        "statusMessage" => "Image cannot be blank"
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function header() {
        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '') {
            $this->load->model('user_model');

            $userdetail = $this->user_model->getuserHash($userhash);

            if ($userdetail['status'] == 1) {
                if (@$_FILES['image'] ['size'] > 0) {

                    $uploads_dir = 'upload/headerimage/full';
                    $tmp_name = $_FILES["image"]["tmp_name"];
                    $name = mktime() . $_FILES["image"]["name"];
                    move_uploaded_file($tmp_name, "$uploads_dir/$name");

                    $this->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = "upload/headerimage/full/" . $name;
                    $config[
                            'new_image'] = 'upload/headerimage/resize/' . $name;
                    $config['maintain_ratio'] = FALSE;
                    $config['width'] = 1366;
                    $config['height'] = 254;
                    $this->image_lib->initialize($config);
                    $rtuenval = $this->image_lib->resize();
                    $this->image_lib->clear();

                    $return['header_image'] = $name;


                    $data = array(
                        'user_Id' => $userdetail['userid'], 'header_image' => $name);
                    $this->user_model->uploadImage($data);
                    if ($userdetail['usertype'] == 1) {
                        $type = 'Consumer';
                    }
                    if ($userdetail['usertype'] == 2) {
                        $type = 'Business';
                    }
                    if ($userdetail['usertype'] == 3) {
                        $type = 'Admin';
                    }

                    if ($userdetail['header_image'] != 'profileBG.jpg') {
                        $path = getcwd();
                        $filepath1 = $path . '/upload/headerimage/full/' . $userdetail['header_image'];       //// CREATE PATH OF Header Image
                        $filepath2 = $path . '/upload/headerimage/resize/' . $userdetail['header_image'];       //// CREATE PATH OF Header Image
                        $response = unlink($filepath1);    //// UNLINK PREVIOUS Image
                        $response = unlink($filepath2);           //// UNLINK PREVIOUS Image
                    }
                    $firstName = isset($userdetail[
                                    'firstname']) ? $userdetail['firstname'] : '';
                    $lastName = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                    if ($userdetail['image']) {
                        $image = base_url() . 'upload/profile/thumbnail/' . $userdetail['image'];
                    } else {
                        $image = '';
                    }
                    $location = isset($userdetail['location']) ? $userdetail['location'] : '';
                    $bio = isset($user_detail->bio) ? $user_detail->bio : '';
                    $gender = isset($userdetail['bio']) ? $userdetail['bio'] : '';
                    $user = array(
                        'userid' => $userdetail['userid'],
                        'username' => $userdetail['username'],
                        'email' => $userdetail['email'],
                        'name' => $firstName . " " . $lastName,
                        'userimage' => $image, 'type' => $type,
                        'header_image' => base_url() . 'upload/headerimage/resize/' . $name,
                        'location' => $location,
                        'bio' => $bio,
                        'loginSource' => $userdetail['loginSource']
                    );

                    $response = array
                        (
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "user" => $user,
                        )
                    );
                } else {
                    $return['header_image'] = '';
                    $response = array(
                        "status" => "error",
                        "statusMessage" => "Header image not uploaded",
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function me(){
        //// Define Variables
        global $cnt_all_record;
        $me_user = array();
        $userhash = $this->input->post('userHash');    //// Get UserHash
        $userid = $this->input->post('userid');
        $page = $this->input->post('page');
        $key = $this->input->post('key');
        if (isset($userhash) && $userhash != '' && isset($userid) && $userid != '' && isset($page)  && isset($key) && $key!='') {

            $this->load->model(array('user_model', 'reward_model','games_model', 'store_model', 'status_model', 'score_model', 'offer_model'));
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details
            //echo '<pre>'; print_r($userdetail);
            if ($userdetail['status'] == 1) {

                $success = 1;
                if (is_numeric($userid)) {
                    $arr_users['user_Id'] = $userid;
                     $user = $this->user_model->getOneUserDetails($arr_users, '*');
                    if( $user->usertype == 1){
                      $key = 'activity';
                    }else{
                      $key = ($key == 'none' || $key == 'campaign' || $key == 'activity')?'campaign':'reward';
                    }
                } else {
                    $arr_users['username'] = $userid;
                    $user = $this->user_model->getOneUserDetails($arr_users, '*');
                    if( $user->usertype == 1){
                      $key = 'activity';
                    }else{

                      $key = ($key == 'none' || $key == 'campaign' || $key == 'activity')?'campaign':'reward';
                    }
                }
                $user = $this->user_model->getOneUserDetails($arr_users, '*');
                if (count($user) > 0) {

                   if($key == 'activity' && $user->usertype == 1){
                    if ($userid != '') {
                        /* echo 'in'; */
                        $success = 1;
                        if (is_numeric($userid)) {
                            $arr_users['user_Id'] = $userid;
                            if ($userdetail['userid'] == $userid) {
                                $ownProfile = 1;
                            } else {
                                $ownProfile = 0;
                            }
                        } else {
                            $arr_users['username'] = $userid;
                            $user = $this->user_model->getOneUserDetails($arr_users, '*');
                            $userIdByusername = $user->user_Id;
                            $arr_users['username'] = $userid;
                            if ($userdetail['username'] == $userid) {
                                $ownProfile = 1;
                            } else {
                                $ownProfile = 0;
                            }
                        }
                        $arr_users['active'] = 1;
                        $me_user = $this->user_model->getOneUserDetails($arr_users, '*');
                    }

                    if (count($me_user) > 0) {
                        if (is_numeric($userid)) {
                            $userid = $me_user->user_Id;
                        } else {
                            $userid = $me_user->username;
                        }
                        $success = 1;
                        $action = 'activity';
                        if ($action != '') {
                            $success = 1;
                            /* Follower : Whom User Follow */
                            if (is_numeric($userid)) {
                                $arr_follow_userid['userId'] = $userid;
                            } else {

                                $arr_follow_userid['userId'] = $userIdByusername;
                            }
                            $arr_follow_userid['active'] = 1;
                            $following_user = $this->user_model->getfollowinguserid($arr_follow_userid, 1);

                            $following = count($following_user);

                            /*  Following : Who Follow User */
                            if (is_numeric($userid)) {
                                $followUserId = $userid;
                            } else {

                                $followUserId = $userIdByusername;
                            }
                            $arr_follower['active'] = 1;
                            $follower_user = $this->user_model->getLoggedInUserFollowers($followUserId, 1);

                            $follower = count($follower_user);

                            /* UserCoins */
                            if (is_numeric($userid)) {
                                $userid = $userid;
                            } else {

                                $userid = $userIdByusername;
                            }
                            $usercoins = $this->games_model->userCoins($userid);
                            if (count($usercoins) > 0) {
                                $coin = $usercoins->coins;
                            } else {
                                $coin = 0;
                            }

                            $page = $_POST['page'];

                            $select = '1';

                            $page = '';
                            $limit = '';

                            if ($action == 'activity') {
                                if (is_numeric($userid)) {
                                    $userid = $userid;
                                } else {

                                    $userid = $userIdByusername;
                                }
                                $arr_status['users.user_Id'] = $userid;
                                $arr_status['users.active'] = 1;
                                //$arr_status['parentStatusid'] = 0;
                                $arr_status['status_image'] = '';
                            }
                            if (is_numeric($userid)) {
                                $receiver_id = $userid;
                            } else {

                                $receiver_id = $userIdByusername;
                            }
                            $records = $this->user_model->getSearchedUserStatus($arr_status, '', 1, '', '', $action = 'activity', $receiver_id);   //// Get Total No of Records in Database


                            $page = $_POST['page'];
                            if ($page == '') {
                                $page = 0;
                            }
                            $limit = 50;

                            //$data['records']=35;
                            $data['records'] = count($records);
                            $config['base_url'] = base_url() . 'index.php/userservice/me/';
                            $config['total_rows'] = $data['records'];
                            $config['per_page'] = '50';
                            $config['uri_segment'] = 3;

                            $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                            $data['page'] = $page;


                            if ($action == 'activity') {
                                if (is_numeric($userid)) {
                                    $arrstatus['user_Id'] = $userid;
                                } else {

                                    $arrstatus['user_Id'] = $userIdByusername;
                                }
                                //$arrstatus['serach_userId'] = $this->input->post('userid');
                                $arrstatus['active'] = 1;
                                //$arrstatus['parentStatusid'] = 0;
                                $arrstatus['status_image'] = '';
                                if (is_numeric($userid)) {
                                    $arrstatus['receiver_id'] = $userid;
                                } else {

                                    $arrstatus['receiver_id'] = $userIdByusername;
                                }
                            } else {
                                //$arrstatus['serach_userId'] = $this->input->post('userid');
                                if (is_numeric($userid)) {
                                    $arrstatus['user_Id'] = $userid;
                                } else {

                                    $arrstatus['user_Id'] = $userIdByusername;
                                }

                                $arrstatus['active'] = 1;
                                //$arrstatus['parentStatusid'] = 0;
                                if (is_numeric($userid)) {
                                    $arrstatus['receiver_id'] = $userid;
                                } else {

                                    $arrstatus['receiver_id'] = $userIdByusername;
                                }
                            }

                            //if ($action == 'all') {

                            $user_activity = $this->user_model->userActivities($arrstatus, $select, 1, $page, $limit, $action = 'activity', '', $userdetail['userid']);
                            $photos = $this->user_model->userActivities($arrstatus, $select, 1, $page, $limit, $action = 'photo', '', $userdetail['userid']);
                            $order = 'ASC';
                            if (is_numeric($userid)) {
                                $arr['user_id'] = $userid;
                            } else {

                                $arr['user_id'] = $userIdByusername;
                            }
                            $arr['isDelete'] = 0;
                            $offerrecords = $this->offer_model->getSavedOffers($arr, '', '');



                            $data['records'] = count($offerrecords);
                            $config['base_url'] = base_url() . 'index.php/userservice/me/';
                            $config['total_rows'] = $data['records'];
                            $config['per_page'] = '50';
                            $config['uri_segment'] = 3;

                            $offers = $this->offer_model->getSavedOffers($arr, $page, $limit);  //// Get Record

                            if (!empty($offers) > 0) {

                                foreach ($offers as $offer) {

                                    $offer->type = $offer->type;
                                    $offer->alert = $offer->notification;

                                    if (empty($offer->offerimage)) {
                                        $offer->offerimage = '';
                                    } else {
                                        $offer->offerimage = base_url() . 'upload/status_image/full/' . $offer->offerimage;
                                    }
                                }
                            } else {
                                $offers = array();
                            }

                            $cnt_all_record = count($user_activity);

                            $nextpage = $page + $limit;
                            if ($data['records'] <= $nextpage) {
                                $nextRecord = "No More Reult";
                            } else {
                                $nextRecord = "";
                            }

                            $follow['userId'] = $userdetail['userid'];
                            $follow['followUserId'] = $userid;
                            $follow['active'] = 1;
                            $followuser = $this->user_model->getfollowinguserid($follow);
                            if (count($followuser) != 0) {
                                $isfollow = true;
                            } else {
                                $isfollow = false;
                            }
                            $block['userid'] = $userdetail['userid'];
                            $block['block_user_id'] = $userid;
                            $blockuser = $this->user_model->getblockUser($block);
                            if (count($blockuser) != 0) {
                                $isblock = true;
                            } else {
                                $isblock = false;
                            }

                            $statusMessage = 'Me Profile';
                            $firstname = isset($me_user->firstname) ? $me_user->firstname : '';
                            $lastname = isset($me_user->lastname) ? $me_user->lastname : '';
                            $location = isset($me_user->location) ? $me_user->location : '';
                            if (isset($me_user->image)) {
                                $image = base_url() . "upload/profile/medium/" . $me_user->image;
                            } else {
                                $image = '';
                            }
                            $bio = isset($me_user->bio) ? $me_user->bio : '';

                            $me = array(
                                "userimage" => $image,
                                "header_image" => base_url() . "upload/headerimage/resize/" . $me_user->header_image,
                                "name" => ucfirst($firstname) . ' ' . ucfirst($lastname),
                                "username" => $me_user->username,

                                "userid" => $me_user->user_Id,
                                "bio" => $bio,
                                "usertype"=>$me_user->usertype,
                                "location" => $location,
                                "following" => $following,
                                "follower" => $follower,
                                "coin" => $coin,
                                "isfollow" => $isfollow,
                                "isblock" => $isblock,
                                "ownProfile" => $ownProfile,
                                "cnt_all_record" => $cnt_all_record,
                                "activities" => $user_activity,
                                "photos" => $photos,
                                "offers" => $offers,
                                "nextPagination" => $nextRecord
                            );
                        } else {
                            $success = 0;
                            $statusMessage = "Error occoured. Unknown Request Type";
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "Error occoured. User not found";
                    }
                    /* else{
                      $success=0;
                      $statusMessage="Error occoured. User not found";
                      } */
                    } else if(($key == 'campaign' || $key == 'reward' ) && ($user->usertype == 2 || $user->usertype == 6  || $user->usertype == 7)) {
                        if (is_numeric($userid)) {
                           $arr_users['user_Id'] = $userid;
                           $businessUser = $this->user_model->getOneUserDetails($arr_users, 'businessId');
                           $userId = $userid;
                           $businessId = $businessUser->businessId;

                        } else {

                            $arr_users['username'] = $userid;
                            $businessUser = $this->user_model->getOneUserDetails($arr_users, 'businessId,user_Id');

                            $userId = $businessUser->user_Id;
                            $businessId = $businessUser->businessId;
                        }

                        $arr_users['active'] = 1;
                        $me_user = $this->user_model->getOneUserDetails($arr_users, '*');

                        if($key == 'campaign'  && ($user->usertype == 2 ||  $user->usertype == 6 || $user->usertype == 7)){

                       // start code for get all campaigns for business user
                       $campaignsRecords = $this->offer_model->getCampaigns($businessId,$start = '', $limit = '');
                            if ($page == '') {
                                 $page = 0;
                             }else{
                                 $page = $page;
                             }
                            $limit = 50;


                            $data['records'] = count($campaignsRecords);
                            $config['base_url'] = base_url() . 'index.php/userservice/me/';
                            $config['total_rows'] = $data['records'];
                            $config['per_page'] = '50';
                            $config['uri_segment'] = 3;

                            $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                            $campaignsList = $this->offer_model->getCampaigns($businessId,$page,$limit);

                            foreach($campaignsList as &$campaign){
                               $campaign->image = empty($campaign->notification_image)? $campaign->notification_image:base_url().'upload/status_image/full/'.$campaign->notification_image;
                            }

                            $responseKey = 'campaigns';

                             $cnt_all_record = count($campaignsRecords);
                            //  end code for get all campaigns for business user


                            $nextpage = $page + $limit;
                            if ($data['records'] <= $nextpage) {
                                $nextRecord = "No More Reult";
                            } else {
                                $nextRecord = "";
                            }
                            }

                            else if ($key == 'reward' && ($user->usertype == 2 || $user->usertype == 6  || $user->usertype == 7)){

                               // start code for get all rewards for business user


                       $rewardRecords = $this->reward_model->getAllRewards($businessId,$start = '', $limit = '');

                            if ($page == '') {
                                 $page = 0;
                             }else{
                                 $page = $page;
                             }
                            $limit = 50;


                            $data['records'] = count($rewardRecords);
                            $config['base_url'] = base_url() . 'index.php/userservice/me/';
                            $config['total_rows'] = $data['records'];
                            $config['per_page'] = '50';
                            $config['uri_segment'] = 3;

                            $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                            $rewardsList = $this->reward_model->getAllRewards($businessId,$page,$limit);


                            foreach($rewardsList as &$reward){
                               $reward->image = empty($reward->rewardImage)? $reward->rewardImage:base_url().'upload/status_image/full/'.$reward->rewardImage;
                            }

                            $responseKey = 'rewards';


                            $cnt_all_record = count($rewardRecords);

                            //  end code for get all rewards for business user


                          $nextpage = $page + $limit;
                            if ($data['records'] <= $nextpage) {
                                $nextRecord = "No More Reult";
                            } else {
                                $nextRecord = "";
                            }

                            }
                            // end code for get all rewards for business user


                            /* Follower : Whom User Follow */

                            $arr_follow_userid['userId'] = $userId;

                            $arr_follow_userid['active'] = 1;
                            $following_user = $this->user_model->getfollowinguserid($arr_follow_userid, 1);

                            $following = count($following_user);

                            /*  Following : Who Follow User */

                            $followUserId = $userId;

                            $arr_follower['active'] = 1;
                            $follower_user = $this->user_model->getLoggedInUserFollowers($followUserId, 1);

                            $follower = count($follower_user);


                            $usercoins = $this->games_model->userCoins($userId);
                            if (count($usercoins) > 0) {
                                $coin = $usercoins->coins;
                            } else {
                                $coin = 0;
                            }
                            $follow['userId'] = $userdetail['userid'];
                            $follow['followUserId'] = $userId;
                            $follow['active'] = 1;
                            $followuser = $this->user_model->getfollowinguserid($follow);
                            if (count($followuser) != 0) {
                                $isfollow = true;
                            } else {
                                $isfollow = false;
                            }
                            $block['userid'] = $userdetail['userid'];
                            $block['block_user_id'] = $userId;
                            $blockuser = $this->user_model->getblockUser($block);
                            if (count($blockuser) != 0) {
                                $isblock = true;
                            } else {
                                $isblock = false;
                            }

                            $statusMessage = 'Me Profile';
                            $firstname = isset($me_user->firstname) ? $me_user->firstname : '';
                            $lastname = isset($me_user->lastname) ? $me_user->lastname : '';
                            $location = isset($me_user->location) ? $me_user->location : '';
                            if (isset($me_user->image)) {
                                $image = base_url() . "upload/profile/medium/" . $me_user->image;
                            } else {
                                $image = '';
                            }
                            $bio = isset($me_user->bio) ? $me_user->bio : '';

                        // business user
                      $me = array(
                                "userimage" => $image,
                                "header_image" => base_url() . "upload/headerimage/resize/" . $me_user->header_image,
                                "name" => ucfirst($firstname) . ' ' . ucfirst($lastname),
                                "username" => $me_user->businessName,
                               // "businessName" => $me_user->businessName,
                               	"businessUsername" => $me_user->username,
                                "userid" => $me_user->user_Id,
                                "bio" => $bio,
                                "usertype"=>$me_user->usertype,
                                "location" => $location,
                                "following" => $following,
                                "follower" => $follower,
                                "coin" => $coin,
                                "isfollow" => $isfollow,
                                "isblock" => $isblock,
                                "ownProfile" => 0,
                                "cnt_all_record" => $cnt_all_record,

                                $responseKey => isset($campaignsList)?$campaignsList:$rewardsList,
                                //"rewards" => $rewardsList,

                                "nextPagination" => $nextRecord );
                }
                else{
                   $success = 0;
                    $statusMessage = "Error occoured. User not found";
                }
                } else {
                    $success = 0;
                    $statusMessage = "Error occoured. User not found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error occoured. User not found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values cannot be blank.";
        }

        if ($success != 0) {
            $responce = array(
                "status" => "success",
                "statusMessage" => $statusMessage,
                "data" => $me
            );
        } else {
            $responce = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        }

        echo json_encode($responce);
    }

    // Get challenges which are sent
    public function getChallenges() {
        $arr_challange = array
                ();
        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '') {
            $this->load->model(array('user_model', 'games_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $i = 0;
            if ($userdetail['status'] == 1) {
                $userid = $userdetail['userid'];
                $username = $userdetail['username'];
                $userimage = $userdetail['image'];
                $user = $this->games_model->userCoins($userid);

                $usercoins = $user->coins;

                $recieve = $this->games_model->recieveChallenges($userid);
                $sent = $this->games_model->getChallenges($userid);
                if (count($sent) > 0) {
                    foreach ($sent as $sChallenge) {
                        $gameName = $this->games_model->getGame($sChallenge->game_id);
                        $oneuserdetails = $this->user_model->checkfield('user_Id', $sChallenge->challenge_to);
                        $arr_challange[$i] = array(
                            'challenge_id' => $sChallenge->challenge_id,
                            'game_id' => $sChallenge->game_id,
                            'gameName' => $gameName->gameName,
                            'coins' => $sChallenge->challenge_coins,
                            'challengeFrom' => $sChallenge->challenge_from,
                            'challengeTo' => $sChallenge->challenge_to,
                            'challengeFromMsg' => '',
                            'challengeToMsg' => '',
                            'createdDate' => $sChallenge->createdDate,
                            'challengeStatus' => $sChallenge->approval,
                            'challengeWinner' => $sChallenge->winner,
                            'challengeToImage' => base_url() . 'upload/profile/thumbnail/' . $oneuserdetails->image,
                            'challengeFromUsername' => $username,
                            'challengeFromImage' => base_url() . "upload/profile/thumbnail/" . $userimage,
                            'challengeToUsername' => $oneuserdetails->username,
                            'type' => 'sent'
                        );
                        $i++;
                    }
                }

                if (count($recieve) > 0) {
                    foreach ($recieve as $rChallenge) {
                        $oneuserdetails = $this->user_model->checkfield('user_Id', $rChallenge->challenge_from);
                        $gameName = $this->games_model->getGame($rChallenge->game_id);
                        $arr_challange[$i] = array(
                            'challenge_id' => $rChallenge->challenge_id,
                            'game_id' => $rChallenge->game_id,
                            'gameName' => $gameName->gameName,
                            'coins' => $rChallenge->challenge_coins,
                            'challengeFrom' => $rChallenge->challenge_from,
                            'challengeTo' => $rChallenge->challenge_to,
                            'challengeFromMsg' => '',
                            'challengeToMsg' => '',
                            'createdDate' => $rChallenge->createdDate,
                            'challengeStatus' => $rChallenge->approval,
                            'challengeWinner' => $rChallenge->winner,
                            'challengeToImage' => base_url() . "upload/profile/thumbnail/" . $userimage,
                            'challengeFromUsername' => $oneuserdetails->username,
                            'challengeFromImage' => base_url() . 'upload/profile/thumbnail/' . $oneuserdetails->image,
                            'challengeToUsername' => $username,
                            'type' => 'recieve'
                        );
                        $i++;
                    }
                }

                $response = array(
                    "status" => "sucess",
                    "statusMessage" => "",
                    "data" => array(
                        "coins" => $usercoins,
                        "challenges" =>
                        $arr_challange
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function delete_status() {
        $userhash = $this->input->post('userHash');
        $statusid = $this->input->post('status_id');

        if (isset($userhash) && $userhash != '' && isset($statusid) && $statusid != '') {
            $this->load->model(array('user_model', 'status_model'));
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);
            $userid = $userdetail['userid'];

            if ($userdetail['status'] == 1) {
                $data = array(
                    'status_id' => $statusid,
                    'userid' => $userid,
                    'active' => 0,
                    'isDelete' => 1
                );
                $statusData = $this->user_model->getStatusData($statusid);
                preg_match_all('/#([^\s]+)/', $statusData->status, $matches);

                // decrement hashtag count when status delete
                $this->user_model->decHashTagCount($matches[0]);
                $status = $this->status_model->delete_status($data);

                if ($status == 1) {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            'status' => "success",
                            'statusMessage' => "Status deleted successfully",
                            'statusid' => $statusid
                        )
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            'status' => "error",
                            'statusMessage' => "Error occoured while deleting status"
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function message() {

        $arr_challange = array();
        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '') {
            $this->load->model(array('user_model', 'message_model'));
            $userdetail = $this->user_model->getuserHash($userhash);

            $i = 0;
            if ($userdetail['status'] == 1) {
                $success = 1;
                $messageDetails = $this->message_model->getreceiveMessagesUser($userdetail['userid']);
                $statusMessage = "Message List";
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Error occoured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "message" => $messageDetails
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function messageDetail() {
        $userhash = $this->input->post('userHash');
        $seconduserid = $this->input->post('seconduserid');
        if (isset($userhash) && $userhash != '' && isset($seconduserid) && $seconduserid != '') {
            $this->load->model(array('user_model', 'message_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $i = 0;
            if ($userdetail['status'] == 1) {
                $success = 1;
                $message = array();
                $message_id = @$this->input->post('message_id');
                if ($message_id != '') {
                    $arr_message['message_id'] = $message_id;
                    $arr_message['isDelete'] = 0;
                    $message = $this->message_model->getOnemessage($arr_message);
                }
                $userid = $userdetail['userid'];


                $messageDetails = $this->message_model->getReceivedMessage($userid, $seconduserid, 'webservice');

                $statusMessage = 'Messgae Details';
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Error occoured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "message" => $messageDetails
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function sendMessage() {
        $userhash = $this->input->post('userHash'); // get userHash
        $seconduserid = $this->input->post('seconduserid'); // get second user id
        $message = trim($this->input->post('message')); //// Get message

        if (isset($userhash) && $userhash != '' && isset($seconduserid) && $seconduserid != '' && isset($message) && $message != '') {
            $this->load->model(array('user_model', 'message_model'));
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);
            $messageDetails = array();

            $i = 0;
            if ($userdetail['status'] == 1) {
                $success = 1;

                $arr_user['user_Id'] = $seconduserid;
                $arr_user['active'] = 1;
                $seconduser = $this->user_model->getOneUserDetails($arr_user);

                if (count($seconduser) > 0) {
                    $data['userid'] = $userdetail['userid'];
                    $data['block_user_id'] = $seconduserid;

                    $userBlock = $this->user_model->UserBlock($data,1);
                    if(count($userBlock) == 0){
                    $arr_msg['message_from'] = $userdetail['userid'];
                    $arr_msg['userid'] = $seconduserid;
                    $arr_msg['message'] = $_POST['message'];
                    $arr_msg['is_new'] = 0;
                    $arr_msg['createdDate'] = date('Y-m-d H:i:s');

                    $last_id = $this->message_model->add($arr_msg);
                    $statusMessage = 'Messsage Send Sucessfully';
                    // send notification code start
                    $deviceInfo = $this->user_model->getdeviceToken($seconduserid);
                    if (count($deviceInfo) > 0) {
                        foreach ($deviceInfo as $device) {
                            $deviceToken = $device->key;
                            $deviceType = $device->deviceTypeID;
                            $title = 'My Test Message';
                            $sound = 'default';
                            $msgpayload = json_encode(array(
                                'aps' => array(
                                    "alert" => '@'. $userdetail['username'] . ' sent you a message!',
                                    "message_id" => $last_id,
                                    "senderuserid" => $userdetail['userid'],
                                    "sendmessage" => $_POST['message'],
                                    "createdDate" => $arr_msg['createdDate'],
                                    "userid" => $seconduserid,
                                    "senderusername" => $userdetail['username'],
                                    "senderfirstname" => $userdetail['firstname'],
                                    "senderlastname" => $userdetail['lastname'],
                                    "senderimage" => base_url() . 'upload/profile/thumbnail/' . $userdetail['image'],
                                    "username" => $seconduser->username,
                                    "firstname" => $seconduser->firstname,
                                    "lastname" => $seconduser->lastname,
                                    "image" => base_url() . 'upload/profile/thumbnail/' . $seconduser->image,
                                    "type" => 'message',
                                    "sound" => $sound
                            )));
                            $message = json_encode(array(
                                'default' => $title,
                                'APNS_SANDBOX' => $msgpayload
                            ));
                            //$message = 'Message: '.$userdetail['username'].' sent you a message!';

                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                        }
                    }

                    // end

                    $messageDetails = array(
                        "message_id" => $last_id,
                        "senderuserid" => $userdetail['userid'],
                        "message" => $_POST['message'],
                        "createdDate" => $arr_msg['createdDate'],
                        "userid" => $seconduserid,
                        "senderusername" => $userdetail['username'],
                        "senderfirstname" => $userdetail['firstname'],
                        "senderlastname" => $userdetail['lastname'],
                        "senderimage" => base_url() . 'upload/profile/thumbnail/' . $userdetail['image'],
                        "username" => $seconduser->username,
                        "firstname" => $seconduser->firstname,
                        "lastname" => $seconduser->lastname,
                        "image" => base_url() . 'upload/profile/thumbnail/' . $seconduser->image
                    );
                     } else {
                    $success = 0;
                    $statusMessage = "You have not access to send message to this user.";
                }
                } else {
                    $success = 0;
                    $statusMessage = "Error Occured. Another User Does Not Exits";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {

            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "message" => $messageDetails
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function checkusername() {
        $username = $this->input->post('username');
        if ($username != '') {
            $this->load->model('user_model');
            $arr_user['username'] = $username;
            $arr_user['active'] = 1;
            $user = $this->user_model->getOneUserDetails($arr_user);
            if (count($user) == 0) {
                $success = 1;
                $statusMessage = "Username Available";
            } else {
                $success = 0;
                $statusMessage = "Error Occured. Username already Registered With Hurree";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Invalid Username";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function readMessage() {
        $userhash = $this->input->post('userHash');
        $seconduserid = $this->input->post('seconduserid');
        if (isset($userhash) && $userhash != '' && isset($seconduserid) && $seconduserid != '') {
            $this->load->model(array('user_model', 'message_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $i = 0;
            if ($userdetail['status'] == 1) {
                $success = 1;

                if ($seconduserid != '') {
                    $success = 1;
                    $arr_msg['is_new'] = 1;
                    $this->message_model->updateNew($userdetail['userid'], $seconduserid, $arr_msg);
                    $statusMessage = "Success. Message Updated";
                } else {
                    $success = 0;
                    $statusMessage = "Error Occured";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function searchFriends() {
        $userhash = $this->input->post('userHash');
        $emailstring = $this->input->post('emailids');
        if (isset($userhash) && $userhash != '' && isset($emailstring) && $emailstring != '') {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            $i = 0;
            if ($userdetail['status'] == 1) {
                $success = 1;

                $success = 1;
                $emails = explode(",", $emailstring);

                $str_where = "email";
                $str_val = array_filter($emails, 'strlen');

                $users = $this->user_model->getexitsusers($str_where, $str_val, $row = 1);

                $user = array();

                foreach ($users as $oneuser) {
                    /* Check this user is followed by loggedIn User */
                    if ($userdetail['userid'] != $oneuser->user_Id) {
                        $arr_follow['userId'] = $userdetail['userid'];
                        $arr_follow['followUserId'] = $oneuser->user_Id;
                        $arr_follow['active'] = 1;
                        $followuser = $this->user_model->getfollowinguserid($arr_follow);

                        count($followuser) > 0 ? $follow = "true" : $follow = "false";
                        $arr_user['userid'] = $oneuser->user_Id;
                        $arr_user['username'] = ucfirst($oneuser->username);
                        $arr_user['name'] = $oneuser->firstname . ' ' . $oneuser->lastname;
                        $arr_user['userimage'] = base_url() . "upload/profile/thumbnail/" . $oneuser->image;
                        $arr_user['email'] = $oneuser->email;
                        $arr_user['follow'] = $follow;
                        $user[] = $arr_user;
                    }
                }
                $statusMessage = count($users) . " Email Id(s) Matched";
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "users" => $user
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function followuser() {
        $userhash = $this->input->post('userHash');
        $seconduserid = $this->input->post('seconduserid');
        if (isset($userhash) && $userhash != '' && isset($seconduserid) && $seconduserid != '') {
            $this->load->model(array('user_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $i = 0;
            if ($userdetail['status'] == 1) {
                // check second userdetails
                if (is_numeric($seconduserid)) {
                    $arr_users['user_Id'] = $seconduserid;
                } else {
                    $arr_users['username'] = $seconduserid;
                }
                $result = $this->user_model->getOneUserDetails($arr_users, '*');

                if (count($result) > 0) {
                    if ($userdetail['userid'] != $seconduserid) {
                        $success = 1;

                        if (is_numeric($seconduserid)) {
                            $arr_users['user_Id'] = $seconduserid;
                        } else {
                            $arr_users['username'] = $seconduserid;
                        }

                        $arr_users['active'] = 1;
                        $secondUser = $this->user_model->getOneUserDetails($arr_users, '*');
                        $secondUserDetails = $this->user_model->getuserDeatils($seconduserid);

                        if (count($secondUser) > 0) {

                            $follow['userId'] = $userdetail['userid'];
                            $follow['followUserId'] = $secondUser->user_Id;
                            $follow['active'] = 1;
                            $followuser = $this->user_model->getfollowinguserid($follow);

                            if (count($followuser) == 0) {
                                $follow['follow_id'] = '';
                                $follow['createdDate'] = date('YmdHis');
                                $followuserid = $secondUser->user_Id;
                                $loginuserid = $userdetail['userid'];
                                $this->user_model->savefollow($follow, $followuserid, $loginuserid);
                                $followstatus = " followed";
                                $isfollow = 1;

                                /* Start Notification */
                                $arr_notice['notification_id'] = '';
                                $arr_notice['actionFrom'] = $userdetail['userid'];
                                $arr_notice['actionTo'] = $secondUser->user_Id;
                                $arr_notice['action'] = 'F';
                                $arr_notice['actionString'] = 'followed you!';
                                $arr_notice['message'] = '';
                                $arr_notice['statusid'] = '';
                                $arr_notice['challangeid'] = '';
                                $arr_notice['active'] = '1';
                                $arr_notice['createdDate'] = date('YmdHis');
                                $notice_id = $this->notification_model->savenotification($arr_notice);

                                // send notification code start
                                $deviceInfo = $this->user_model->getdeviceToken($secondUser->user_Id);
                                if (count($deviceInfo) > 0) {
                                    foreach ($deviceInfo as $device) {
                                        $deviceToken = $device->key;
                                        $deviceType = $device->deviceTypeID;
                                        $title = 'My Test Message';
                                        $sound = 'default';
                                        $msgpayload = json_encode(array(
                                            'aps' => array(
                                                "alert" => '@'. $userdetail['username'] . ' followed you',
                                                "type" => 'followuser',
                                                "sound" => $sound
                                        )));
                                        $message = json_encode(array(
                                            'default' => $title,
                                            'APNS_SANDBOX' => $msgpayload
                                        ));
                                        //$message = 'New Follower: '.$userdetail['username'].' followed you';

                                        $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                    }
                                }

                                // end

                                /* End Notification */
                            } else {
                                $this->user_model->deletefollow($followuser->follow_id);
                                $followstatus = " Unfollowed";
                                $isfollow = 0;

                                /* Start Delete Notification */
                                $arr_notice['actionTo'] = $secondUser->user_Id;
                                $arr_notice['actionFrom'] = $userdetail['userid'];
                                $arr_notice['action'] = 'F';
                                $arr_notice['active'] = 1;

                                $this->notification_model->delete_notification($arr_notice);
                                /* End Delete Notification */
                            }

                            foreach ($secondUserDetails as $details) {
                                $details->followed = $isfollow;
                                $details->bio = isset($details->bio) ? $details->bio : '';
                                $details->location = isset($details->location) ? $details->location : '';
                            }
                            $statusMessage = "You" . $followstatus . ' ' . 'this user';
                            $userDetails = $secondUserDetails;
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "You can not follow your own profile.";
                        $userDetails = '';
                    }
                } else {
                    $success = 0;
                    $statusMessage = "User Not Found.";
                    $userDetails = '';
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
                $userDetails = '';
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
            $userDetails = '';
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage,
                    'userDetails' => $secondUserDetails
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "isfollow" => $isfollow,
                    'userDetails' => $secondUserDetails
                )
            );
        }
        echo json_encode($response);
        exit;
    }

//    /* Check facebook and twitter user is registered or not  */

    public function social_login() {

        $id = $this->input->post('id');
        $service = $this->input->post('service');
        if (isset($id) && $id != '' && isset($service) && $service != '') {
            $this->load->model('user_model');
            if ($service == 'facebook') {
                $arr_users['fbid'] = $id;

                $details = $this->user_model->getOneUserDetails($arr_users, '*');


                if (count($details) != 0) {

                    if ($details->fbid != 0) {
                        if ($details->usertype == 1) {
                            $type = 'Consumer';
                        }
                        if ($details->usertype == 2) {
                            $type = 'Business';
                        }
                        if ($details->usertype == 3) {
                            $type = 'Admin';
                        }
                        $birthDate = $details->date_of_birth;
                        $birthDate = explode("-", $birthDate);
                        if ($birthDate[0] == 0000 && $birthDate[1] == 00 && $birthDate[0] == 00) {
                            $age = '';
                        } else {
                            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[1], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));
                        }

                        $gender = ($details->gender == 'undefined') ? '' : $details->gender;
                        $tempwd = $this->user_model->getTempPassword($details->user_Id);
                        $pwd = isset($tempwd->temp_pwd) ? base64_decode($tempwd->temp_pwd) : '';
                        $location = isset($details->location) ? $details->location : '';
                        $bio = isset($details->bio) ? $details->bio : '';
                        $firstname = isset($details->firstname) ? $details->firstname : '';
                        $lastname = isset($details->lastname) ? $details->lastname : '';
                        $bio = isset($details->bio) ? $details->bio : '';


                        $userdetails = array(
                            'userid' => $details->user_Id,
                            'username' => $details->username,
                            'email' => $details->email,
                            'name' => $firstname . " " . $lastname,
                            'userimage' => base_url() . 'upload/profile/thumbnail/' . $details->image,
                            'type' => $type,
                            'header_image' => base_url() . 'upload/headerimage/resize/' . $details->header_image,
                            'location' => $location,
                            'password' => $pwd,
                            'bio' => $bio,
                            'age' => $age,
                            'gender' => $gender,
                            'loginSource' => $details->loginSource
                        );

                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "User exist.",
                                "user" => $userdetails
                            )
                        );
                    } else {

                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "Your email address is already signed up :)"
                            )
                        );
                    }
                } else {

                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "User not exist."
                        )
                    );
                }
            } else {
                $arr_users['twitterid'] = $id;

                $details = $this->user_model->getOneUserDetails($arr_users, '*');

                if (count($details) != 0) {
                    if ($details->twitterid != 0) {
                        if ($details->usertype == 1) {
                            $type = 'Consumer';
                        }
                        if ($details->usertype == 2) {
                            $type = 'Business';
                        }
                        if ($details->usertype == 3) {
                            $type = 'Admin';
                        }
                        $birthDate = $details->date_of_birth;
                        $birthDate = explode("-", $birthDate);
                        if ($birthDate[0] == 0000 && $birthDate[1] == 00 && $birthDate[0] == 00) {
                            $age = '';
                        } else {
                            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[2], $birthDate[1], $birthDate[0]))) > date("md") ? ((date("Y") - $birthDate[0]) - 1) : (date("Y") - $birthDate[0]));
                        }

                        $gender = ($details->gender == 'undefined') ? '' : $details->gender;
                        $tempwd = $this->user_model->getTempPassword($details->user_Id);
                        $pwd = isset($tempwd->temp_pwd) ? base64_decode($tempwd->temp_pwd) : '';
                        $location = isset($details->location) ? $details->location : '';
                        $bio = isset($details->bio) ? $details->bio : '';
                        $firstname = isset($details->firstname) ? $details->firstname : '';
                        $lastname = isset($details->lastname) ? $details->lastname : '';
                        $bio = isset($details->bio) ? $details->bio : '';

                        $userdetails = array(
                            'userid' => $details->user_Id,
                            'username' => $details->username,
                            'email' => $details->email,
                            'name' => $details->firstname . " " . $details->lastname,
                            'userimage' => base_url() . 'upload/profile/thumbnail/' . $details->image,
                            'type' => $type,
                            'header_image' => base_url() . 'upload/headerimage/resize/' . $details->header_image,
                            'location' => $location,
                            'bio' => $bio,
                            'password' => $pwd,
                            'age' => $age,
                            'gender' => $gender,
                            'loginSource' => $details->loginSource
                        );

                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "User exist.",
                                "user" => $userdetails
                            )
                        );
                    } else {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array
                                (
                                "status" => "error",
                                "statusMessage" => "Your email address is already signed up :)"
                            )
                        );
                    }
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "User not exist."
                        )
                    );
                }
            }
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function block_user() {
        $userhash = $this->input->post('userHash');
        $seconduserid = $this->input->post('seconduserid');
        if (isset($userhash) && $userhash != '' && isset($seconduserid) && $seconduserid != '') {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            $i = 0;
            if ($userdetail['status'] == 1) {
                if ($userdetail['userid'] != $seconduserid) {
                    $success = 1;

                    if ($seconduserid != '') {

                        if (is_numeric($seconduserid)) {
                            $arr_users['user_Id'] = $seconduserid;
                        } else {
                            $arr_users['username'] = $seconduserid;
                        }

                        $arr_users['active'] = 1;
                        $secondUser = $this->user_model->getOneUserDetails($arr_users, '*');
                        if (count($secondUser) > 0) {
                            $block['userid'] = $userdetail['userid'];
                            $block['block_user_id'] = $secondUser->user_Id;
                            $blockuser = $this->user_model->getblockuserid($block);

                            if (count($blockuser) == 0) {
                                $block['block_id'] = '';
                                $block['createdDate'] = date('YmdHis');
                                $this->user_model->saveblock($block);

                                $follow['userId'] = $userdetail['userid'];
                                $follow['followUserId'] = $seconduserid;
                                $follow['active'] = 1;
                                $followuser = $this->user_model->getfollowinguserid($follow);

                                if (count($followuser) > 0) {
                                    $this->user_model->deletefollow($followuser->follow_id);
                                }

                                $blockstatus = "Blocked";
                                $isblock = true;
                            } else {
                                $this->user_model->deleteblock($blockuser->block_id);
                                $blockstatus = "Unblocked";
                                $isblock = false;
                            }
                            $statusMessage = "User Successfully " . $blockstatus;
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "Error Occured. Another User Not Found";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "You can not block to your own profile.";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "isblock" => $isblock
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function populate_username() {

        $userhash = $this->input->post('userHash');   //// Get UserHash
        $username = $this->input->post('username');
        if (isset($userhash) && $userhash != '' && isset($username) && $username != '') {
            $arr_username = array();
            $this->load->model('user_model');
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details
            if ($userdetail['status'] == 1) {
                $success = 1;

                if ($username != '@') {
                    $username = str_replace("@", "", $username);
                    if ($username != '') {
                        $limit = 50;
                        $user = $this->user_model->getuserlist($username, $limit);
                        $i = 0;

                        foreach ($user as $one) {
                            $firstname = isset($one->firstname) ? $one->firstname : '';
                            $lastname = isset($one->lastname) ? $one->lastname : '';
                            if (isset($one->image)) {
                                $image = base_url() . "upload/profile/thumbnail/" . $one->image;
                            } else {
                                $image = '';
                            }
                            $arr_username[$i]['username'] = $one->username;
                            $arr_username[$i]['user_id'] = $one->user_Id;
                            $arr_username[$i]['name'] = $firstname . ' ' . $lastname;
                            $arr_username[$i]['image'] = $image;

                            $i++;
                        }
                        $statusMessage = ' Success';
                    }
                }
            } else {
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "user" => $arr_username
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    function scrap($url){
          $supported_image = array(

                                'jpg',
                                'jpeg',
                                'png'
                            );

    require_once APPPATH . 'third_party/vendor/PHP-Curler/Curler.class.php';
    require_once APPPATH . 'third_party/vendor/PHP-MetaParser/MetaParser.class.php';

    // curling
    $curler = (new Curler());
    //$url = 'https://www.paytm.com/';
    $body = $curler->get($url);
    $parser = (new MetaParser($body, $url));

    $details = $parser->getDetails();
    $newArray= array();

    if(empty($details['openGraph']) && empty($details['images'])){
         return new ArrayObject();
    }else{
        $image1 = array();
        for($i = 0; $i<count($details['images']); $i++){
        $ext = strtolower(pathinfo($details['images'][$i], PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive

     if(!in_array($ext, $supported_image)){
       $image = array();
    }else{

        $image1[] =$details['images'][$i];
    }
        }
    }
    //print_r($image); exit;
    if(count($image1)== 0){
       return new ArrayObject();
    }
    $newArray['description'] = empty($details['meta']['description'])?$details['url']:$details['meta']['description'];
    $newArray['url'] = empty($details['url'])?'':$details['url'];

    $newArray['title'] = empty($details['title'])?'':$details['title'];

    if(!empty($details['openGraph'])){
      $newArray['image'] = $details['openGraph']['image'];
    }else{
      $newArray['image'] = empty($image1[0])? base_url().'upload/anynomous.jpg':$image1[0];
      $newArray['image'] = preg_replace('/{{(.*?)}}/', '',  $newArray['image']);
      $newArray['image'] = empty($newArray['image'])? base_url().'upload/anynomous.jpg':$newArray['image'];
    }
    return $newArray;

    }



    public function status_post() {
        $userhash = $this->input->post('userHash');
        $receiverUserId = $this->input->post('receiverUserId');
        @$business_id = $this->input->post('business_id');    /// business user id from user table
        @$branch_id = $this->input->post('branch_id');
        $usermentioned = $this->input->post('usermentioned');
        $status = urldecode($this->input->post('status'));
        $statusId = $this->input->post('statusId');
        if (preg_match(" /[\w\d\.]+\.[\w\d]{1,3}/", $status)) {
                if (!preg_match("~^(?:f|ht)tps?://~i", $status)) {

                    $url = "http://" . trim($status);
                } else {
                    $url = trim($status);
                }

                if (!filter_var($url, FILTER_VALIDATE_URL) === false) {

                    $thumbnailInfo = $this->scrap($url);


                    if (count($status) == 0) {
                        $url = "https://" . trim($status);
                        $thumbnailInfo = $this->scrap($url);
                    }
                } else {

                   $thumbnailInfo = new ArrayObject();
                }
            } else {
                $status = $status;
                $thumbnailInfo = 0;
            }

        $bu_sucess = 1;
        $date = date('YmdHis');
        $isCheckInUserCoinsId = '';

        if (isset($userhash) && $userhash != '' && isset($status) && isset($usermentioned)) {
            $videoThumb = '';
            $receiverUserId = !empty($receiverUserId) ? $receiverUserId : '';
            $usermentioned = !empty($usermentioned) ? $usermentioned : '';
            $this->load->model(array('user_model', 'score_model', 'notification_model','status_model'));
            $userdetail = $this->user_model->getuserHash($userhash);

            if ($userdetail['status'] == 1) {

// echo 'mnvd'; exit;
                if ($status != '' || @$_FILES['image']['size'] > 0 || isset($business_id) || isset($branch_id)) {  //Blank status check
                    //if (@$business_id != '' && @$branch_id != '') {
                        if (@$business_id != '' ) {
                        $bu_sucess = 1;
                        $business = $this->user_model->getOneUser($business_id);

                        if (count($business) > 0) {
                            $bu_sucess = 1;
                            if ($business->usertype == '2' || $business->usertype == '5') {
                                $bu_sucess = 1;
                                /* Start Notification */
                                $wh_coin['userid'] = $userdetail['userid'];
                                $wh_coin['coins_type'] = 6;
                                $wh_coin['businessid'] = $business_id;
                                $orderby['orderby'] = 'createdDate';
                                $orderby['order'] = 'DESC';
                                $limit['start'] = '0';
                                $limit['perpage'] = '1';
                                $scanDetails = $this->score_model->getUserActionTime('*', $wh_coin, '', $orderby, $limit);

                                if (count($scanDetails) > 0) {
                                    /*  Get The Time Difference  */
                                    $date1 = new DateTime($scanDetails->createdDate);
                                    $date2 = new DateTime(date('Y-m-d H:i:s'));

                                    $diff = $date2->diff($date1);

                                    $hours = $diff->h;
                                    $hours = $hours + ($diff->days * 24);
                                } else {
                                    $hours = 24;
                                }

                                if ($hours > 23) {
                                    $bu_sucess = 1;

                                    $arr_notice['notification_id'] = '';
                                    $arr_notice['actionFrom'] = $business_id;
                                    $arr_notice['actionTo'] = $userdetail['userid'];
                                    $arr_notice['action'] = 'CIN';
                                    $arr_notice['actionString'] = 'You have got 1 Coin by Check In';
                                    $arr_notice['message'] = '';
                                    $arr_notice['statusid'] = '';
                                    $arr_notice['challangeid'] = '';
                                    $arr_notice['active'] = '1';
                                    $arr_notice['createdDate'] = $date;

                                    $notice_id = $this->notification_model->savenotification($arr_notice);

                                    //Save notification for business user
//                                    $arr_notice['notification_id'] = '';
//                                    $arr_notice['actionFrom'] = $userdetail['userid'];
//                                    $arr_notice['actionTo'] = $business_id;
//                                    $arr_notice['action'] = 'CIN';
//                                    $arr_notice['actionString'] = 'checked in to your business';
//                                    $arr_notice['message'] = '';
//                                    $arr_notice['statusid'] = '';
//                                    $arr_notice['challangeid'] = '';
//                                    $arr_notice['active'] = '1';
//                                    $arr_notice['createdDate'] = $date;
//
//                                    $notice_id = $this->notification_model->savenotification($arr_notice);


                                    $arr_coin['coins_id'] = '';
                                    $arr_coin['userid'] = $userdetail['userid'];
                                    $arr_coin['coins'] = 1;
                                    $arr_coin['businessid'] = $business_id;
                                    $arr_coin['branchid'] = $branch_id;
                                    $arr_coin['coins_type'] = 6;
                                    $arr_coin['game_id'] = '';
                                    $arr_coin['actionType'] = 'add';
                                    $arr_coin['createdDate'] = $date;
                                    $isCheckInUserCoinsId = $this->score_model->insert($arr_coin);


                                    if ($business->usertype == '2') {
                                        $usercoins = $this->score_model->getUserCoins($userdetail['userid']);
                                        if (count($usercoins) > 0) {
                                            $user_coin = $usercoins->coins + 1;
                                            $coins['user_score_id'] = $usercoins->user_score_id;
                                            $coins['userid'] = $userdetail['userid'];
                                            $coins['coins'] = $user_coin;
                                            $coins['modifiedDate'] = $date;
                                        } else {
                                            $user_coin = 1;
                                            $coins['user_score_id'] = '';
                                            $coins['userid'] = $userdetail['userid'];
                                            $coins['coins'] = $user_coin;
                                            $coins['modifiedDate'] = $date;
                                        }

                                        $this->score_model->saveuserTotalCoins($coins);
                                    }
                                    //Detect Business user coins
                                    $buss_coin['coins_id'] = '';
                                    $buss_coin['userid'] = $business_id;
                                    $buss_coin['coins'] = 1;
                                    $buss_coin['coins_type'] = 6;
                                    $buss_coin['businessid'] = $business_id;
                                    $buss_coin['branchid'] = $branch_id;
                                    $buss_coin['actionType'] = 'sub';
                                    $buss_coin['createdDate'] = $date;

                                    $this->score_model->insert($buss_coin);    //// Save Business Detect Details in userCoin

                                    if ($business->usertype == '2' && $business->organizationId == '0') {
                                        $businessCoins = $this->score_model->getUserCoins($business_id);

                                        $bu_coin = $businessCoins->coins - 1;
                                        $buss_totalcoins['user_score_id'] = $businessCoins->user_score_id;
                                        $buss_totalcoins['userid'] = $business_id;
                                        $buss_totalcoins['coins'] = $bu_coin;
                                        $buss_totalcoins['modifiedDate'] = $date;
                                        $this->score_model->saveuserTotalCoins($buss_totalcoins);   //// Update Business Total Coins
                                    }
                                    /* End Business User Coins  */
                                    /* End Notification */
                                } else {
                                    $bu_sucess = 0;

                                    $status_message = ' You have already checked in, call again tomorrow :)';
                                }
                            } else {
                                $bu_sucess = 0;
                                $status_message = 'Business Not Found';
                            }
                        } else {
                            $bu_sucess = 0;
                            $status_message = 'Business Not Found';
                        }
                    }

                    if ($bu_sucess == 1) {
                        $success = 1;
                        if (@$_FILES['image']['size'] > 0) {

                            $success = 1;
                            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
                            $uploads_dir = 'upload/status_image/full/' . $userdetail['userid'];
                            $mediumImagePath = 'upload/status_image/medium/' . $userdetail['userid'];
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

                            // Image upload in full size in profile directory
                            // $uploads_dir = 'upload/status_image/full';
                            $tmp_name = $_FILES["image"]["tmp_name"];
                            $name = mktime() . $_FILES["image"]["name"];
                            $result = move_uploaded_file($tmp_name, "$uploads_dir/$name");

                            $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                            if (in_array($ext, $extionArray)) {
                                // image resize in thumbnail size in thumbnail directory
                                $this->load->library('image_lib');
                                $config['image_library'] = 'gd2';
                                $config['source_image'] = $path . "/" . $name;
                                $config['new_image'] = $mediumpath . '/' . $name;

                                $config['maintain_ratio'] = TRUE;
                                $config['width'] = 400;
                                $config['height'] = 350;
                                $this->image_lib->initialize($config);
                                $rtuenval = $this->image_lib->resize();
                                $this->image_lib->clear();
                                $videoThumb = '';
                            }
                            // video thumbnail code
                            else {

                                $videoThumbPath = 'upload/videoThumb' . '/' . $userdetail['userid'];
                                if (!is_dir($videoThumbPath)) {
                                    if (mkdir($videoThumbPath, 0777, true)) {
                                        $thumbPath = $videoThumbPath;
                                    } else {
                                        $thumbPath = $videoThumbPath;
                                    }
                                } else {
                                    $thumbPath = $videoThumbPath;
                                }
                                $dirPath = $_SERVER['DOCUMENT_ROOT'];
                                //echo $dirPath.'/'.$uploads_dir.'/'.$name; exit;
                                $videothumb = strtotime(date('Ymdhis')) . 'thumb.png';
                                $cmd = ' sudo /usr/bin/ffmpeg -i ' . $dirPath . '/' . $uploads_dir . '/' . $name . ' -frames:v 1 -s 320x240 ' . $dirPath . '/' . $thumbPath . '/' . $videothumb;
                                exec($cmd . ' ' . '2>&1', $out, $res);


                                $videoThumb = $userdetail['userid'] . '/' . $videothumb;
                            }
                            // end video thumbnail code
                            $status_image = $userdetail['userid'] . '/' . $name;
                        } else {

                            $status_image = '';
                        }
                        $string = $status;
                        preg_match_all('/#([^\s]+)/', $string, $matches);
                        $usermentions = str_replace(' ', ',', $usermentioned);
                        $arr_reply['status_id'] = '';
                        $arr_reply['parentStatusid'] = 0;
                        $arr_reply['status'] = $status;
                        $arr_reply['userid'] = $userdetail['userid'];
                        $arr_reply['receiver_id'] = $receiverUserId;
                        $arr_reply['status_image'] = $status_image;
                        $arr_reply['media_thumb'] = $videoThumb;
                        $arr_reply['usermentioned'] = $userdetail['username'];
                        $arr_reply['usermentioned'] = $usermentions;
                        $arr_reply['shareFrom'] = 0;
                        $arr_reply['thumbnailInfo'] = json_encode($thumbnailInfo);
                        $arr_reply['share'] = 0;
                        $arr_reply['active'] = 1;
                        $arr_reply['isCheckInUserCoinsId'] = $isCheckInUserCoinsId;
                        $arr_reply['createdDate'] = $date;
                        $arr_reply['modifiedDate'] = $date;
                         if(isset($statusId) && $statusId!=''){

                                    $arr_reply['quoteFromStatusId'] = $statusId;
                                    $arr_reply['parentStatusid'] = 0;
                                }else{

                                    $arr_reply['quoteFromStatusId'] = 0;
                                }

                        $status_id = $this->user_model->saveUserStatus($arr_reply);

                        $this->user_model->saveUserHashtag($matches[1]);
                        // initiate curl for update status for solr
                        $ch = curl_init();
                        $to = solr_url . 'hurree/dataimport?command=delta-import&wt=json';
                        curl_setopt($ch, CURLOPT_URL, $to);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_exec($ch);
                        curl_close($ch);
                        // end
                    } else {
                        $success = 0;
                    }

                    if ($success == 0) {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "error",
                                "statusMessage" => $status_message
                            )
                        );
                    } else {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "Status posted successfully"
                            )
                        );
                    }
                } else {
                    //Blank Status with no image
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "You can't upload blank status"
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error Occured. User Not Found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function brand_new() {

        $userhash = $this->input->post('userHash'); //// Get UserHash
        $page = $this->input->post('page');
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        if (isset($userhash) && $userhash != '' && isset($latitude) && $latitude != '' && isset($longitude) && $longitude != '' && isset($page)) {
            $baseurl = base_url();
            $this->load->model('user_model');
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details
            if ($userdetail['status'] == 1) {
                $success = 1;

                if ($this->input->post('latitude') != '' && $this->input->post('longitude') != '') {

                    $records = $this->user_model->getnearestBusiness(2, '', '', '', '', '', 5);   //// Get Total No of Records in Database
                    $data['records'] = count($records);
                    /* $data['records']=30; */
                    $config['base_url'] = base_url() . 'index.php/userservice/brand_new/';
                    $config['total_rows'] = $data['records'];
                    $config['per_page'] = '50';
                    $config['uri_segment'] = 3;

                    $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                    $data['page'] = $page;
                    $limit = $config['per_page'];
                    $order_by['order_by'] = 'distance';
                    $order_by['sequence'] = 'ASC';
                    $select = 'DISTINCT user_Id as userid, CONCAT(UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username, CONCAT_WS( " ", firstname, lastname ) as name, CONCAT("' . $baseurl . 'upload/profile/thumbnail/", image ) as userimage, businessName, ((ACOS(SIN(latitude * PI() / 180) * SIN(' . $latitude . ' * PI() / 180) +
	            COS(' . $latitude . ' * PI() / 180) * COS(latitude * PI() / 180) * COS((' . $longitude . ' - longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) * 1.61
	            AS distance, businessName';
                    $checkin = 0;
                    $business_user = $this->user_model->getnearestBusiness(2, $page, $limit, $order_by, $select, $checkin, '5');   //Get Records
                    foreach ($business_user as $bUser) {

                        $bUser->distance = isset($bUser->distance) ? $bUser->distance : '';
                    }

                    /* echo $this->db->last_query(); */
                    $statusMessage = 'Success, Brand New Business';
                } else {
                    $success = 0;
                    $statusMessage = "Error Occured. Your Location is Not available";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "brand_new" => $business_user
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function notification() {

        $notification = array();
        $onestatus = array();
        $userhash = $this->input->post('userHash'); //// Get UserHash
        $page = $this->input->post('page');
        if (isset($userhash) && $userhash != '' && isset($page)) {

            $this->load->model(array('user_model', 'notification_model', 'status_model', 'games_model', 'offer_model'));
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details

            if ($userdetail['status'] == 1) {
                $success = 1;


                if ($page == '') {
                    $page = 0;
                }
                // Parameters Send to Model
                $select = "*";
                $arr_notification['actionTo'] = $userdetail['userid'];
                $arr_notification['notification.active'] = 1;
                $arr_notification['notification.isDelete'] = 0;
                $arr_notification['actionFrom !='] = $userdetail['userid'];

                $records = $this->notification_model->getnotification($arr_notification, $row = 1, $select);   //// Get Total No of Records in Database
                $data['records'] = count($records);
                // $data['records']=30;
                $config['base_url'] = base_url() . 'index.php/userservice/notification/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY


                $limit = $config['per_page'];
                $order_by['order_by'] = 'distance';
                $order_by['sequence'] = 'ASC';


                $select = ' username, CONCAT_WS( " ", users.firstname, users.lastname ) as name, image, user_Id,CONCAT("' . base_url() . 'upload/profile/thumbnail/",users.image ) as userimage, users.usertype, users.businessName,CONCAT_WS(" ", users.firstname, users.lastname ,actionString ) as actionStrings, action,  notification.createdDate as postedDate, notification.* ';
                $notice = $this->notification_model->getnotification($arr_notification, $row = 1, $select, $page, $limit);

                if (count($notice) > 0) {
                    foreach ($notice as $notice) {
                        $onestatus = array();

                        $shareFromUser = '';
                        if ($notice->statusid != '') {

                            $arr_status['status_id'] = $notice->statusid;   //$notice->statusid
                            $status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $userdetail['userid']);

                            if (count($status) > 0) {

                                $onestatus['status_id'] = $status->status_id;
                                $onestatus['status'] = $status->status;
                                $onestatus['status_image'] = $status->status_image;
                                $onestatus['originalMedia'] = $status->originalMedia;
                                $onestatus['thumbnail'] = $status->thumbnail;
                                $onestatus['userid'] = $status->userid;
                                $onestatus['name'] = $status->name;
                                $onestatus['username'] = $status->username;
                                $onestatus['userimage'] = $status->userimage;
                                $onestatus['like'] = $status->like;
                                $onestatus['shared'] = $status->shared;
                                $onestatus['totalLike'] = $status->totalLike;
                                $onestatus['shareCount'] = $status->shareCount;
                                $onestatus['originalPosterId'] = $status->originalPosterId;
                                $onestatus['originalPoster'] = $status->originalPoster;
                                $onestatus['shareFromUserId'] = !empty($status->shareFromUserId) ? $status->shareFromUserId : '';
                                $onestatus['shareFromUser'] = $status->shareFromUser;
                                $onestatus['createdDate'] = $status->createdDate;
                                $onestatus['usermentioned'] = $status->usermentioned;
                                $onestatus['isCheckInUserCoinsId'] = $status->isCheckInUserCoinsId;
                                $onestatus['checkinBusinessId'] = $status->checkinBusinessId;
                                $onestatus['checkinBusinessName'] = $status->checkinBusinessName;
                                $arr_reply['parentStatusid'] = $notice->statusid;
                                $replys = $this->user_model->getStatusDetails($arr_reply, $row = 1, '', '', 1, 'webservice', '', $userdetail['userid']);
                                $onestatus['reply'] = $replys;

                                $onestatus['createdDate'] = $status->createdDate;

                                $originalPoster = array(
                                    "userid" => $status->userid,
                                    "username" => $status->username,
                                    "userimage" => $status->userimage
                                );
                                $onestatus['originalPoster'] = $originalPoster;

                                $shareFromUser = array();
                                $share = array();
                                if ($status->userid != $notice->user_Id) {

                                    $share['userId'] = $notice->user_Id;

                                    $share['statusId'] = $notice->statusid;

                                    $share = $this->user_model->getshareStatus($share);
                                    if (count($share) > 0) {
                                        $shareuser = $this->user_model->getOneUser($share->shareFromUserId);
                                        if ($shareuser->image) {
                                            $image = base_url() . "upload/profile/thumbnail/" . $shareuser->image;
                                        } else {
                                            $image = '';
                                        }
                                        $shareFromUser = array(
                                            "userid" => $share->shareFromUserId,
                                            "username" => $shareuser->username,
                                            "userimage" => $image
                                        );
                                    }
                                }

                                $onestatus['shareFromUser'] = $shareFromUser;
                            }
                        }
                        $actionStrings = '';
                        if ($notice->action == 'SQR') {

                            $string = $notice->actionStrings;
                            $arr = explode(' ', $string);

                            for ($i = 2; $i < count($arr); $i++) {
                                $actionStrings .= $arr[$i] . " ";
                            }
                        } else {
                            $actionStrings = $notice->actionStrings;
                        }
                        if (!empty($onestatus)) {
                            $onestatus = $onestatus;
                        } else {
                            $onestatus = new ArrayObject();
                        }
                        if ($notice->action === 'CC' || $notice->action === 'CS') {
                            $challenegId = $notice->challangeid;
                            $getchallenge = $this->user_model->getchallengeDetails($challenegId);
                            foreach ($getchallenge as $game) {
                                $gameId = $game->game_id;
                            }
                            $getGame = $this->games_model->getGame($gameId);
                            // echo $getGame->gameName; exit;
                            $challengeId = $challenegId;
                            $gameId = $gameId;
                            $gameName = isset($getGame->gameName) ? $getGame->gameName : '';
                        } else if ($notice->action === 'IO') {

                            $offerId = $notice->offerId;
                            $offerResult = $this->offer_model->checkIndividualOffer($offerId, 1);

                            if (!empty($offerResult)) {

                                $firstname = isset($userdetail['firstname']) ? $userdetail['firstname'] : '';
                                $lastname = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                                $name = $firstname . ' ' . $lastname;
                                if (!empty($offerResult->notification_image)) {
                                    $offerimage = base_url() . 'upload/status_image/full/' . $offerResult->notification_image;
                                } else {
                                    $offerimage = '';
                                }

                                $offers['offerId'] = $offerResult->offer_id;
                                $offers['receiverid'] = $notice->actionTo;
                                $offers['name'] = $name;
                                $offers['username'] = $userdetail['username'];
                                $offers['availability'] = '';
                                 $offers['url'] ='';
                                $offers['userimage'] = base_url() . 'upload/profile/thumbnail/' . $userdetail['image'];
                                $offers['createdDate'] = $offerResult->createdDate;
                                $offers['alert'] = $offerResult->notification;
                                $offers['discountValue'] = $offerResult->discount_percentage;
                                $offers['coins'] = 200;
                                $offers['type'] = 'individualOffer';

                                $offers['offerimage'] = $offerResult->notification_image;
                                $aps = (object) $offers;
                                $challengeId = '';
                                $gameId = '';
                                $gameName = '';

                            }
                        }
                        else if ($notice->action === 'PO'){
                              $offerId = $notice->offerId;
                              $offerResult = $this->offer_model->checkOffer($offerId, 1);

                            if (!empty($offerResult)) {
                                if($offerResult->availability==0){

                                   $this->offer_model->deleteOfferfromNoti($offerId,$type = 'publicOffer');

                                }
                                else{
                                $firstname = isset($userdetail['firstname']) ? $userdetail['firstname'] : '';
                                $lastname = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                                $name = $firstname . ' ' . $lastname;
                                if (!empty($offerResult->notification_image)) {
                                    $offerimage = base_url() . 'upload/status_image/full/' . $offerResult->notification_image;
                                } else {
                                    $offerimage = '';
                                }

                                $offers['offerId'] = $offerResult->campaign_id;
                                $offers['receiverid'] = $notice->actionTo;
                                $offers['name'] = $name;
                                $offers['username'] = $userdetail['username'];
                                $offers['availability'] = $offerResult->availability;
                                $offers['url'] = base_url().'redeemOffer/index/' . $offerResult->campaign_id;
                                $offers['userimage'] = base_url() . 'upload/profile/thumbnail/' . $userdetail['image'];
                                $offers['createdDate'] = $offerResult->createdDate;
                                $offers['alert'] = $offerResult->notification;
                                $offers['startDate'] = $offerResult->startDate;
                                $offers['discountValue'] = $offerResult->discount_percentage;
                                $offers['coins'] = $offerResult->coins;
                                $offers['type'] = 'publicOffer';

                                $offers['offerimage'] = $offerResult->notification_image;
                                $aps = (object) $offers;
                                $challengeId = '';
                                $gameId = '';
                                $gameName = '';
                            }
                            }
                        }else {
                            $challengeId = ($notice->challangeid == 0) ? '' : $notice->challangeid;
                            $gameId = '';
                            $gameName = '';
                        }
                        if (isset($notice->image)) {
                            $image = base_url() . 'upload/profile/thumbnail/' . $notice->image;
                        } else {
                            $image = '';
                        }
                        if (isset($userdetail['image'])) {
                            $secondUserImage = base_url() . 'upload/profile/thumbnail/' . $userdetail['image'];
                        } else {
                            $secondUserImage = '';
                        }


                        if (isset($aps)) {
                            $aps = $aps;
                        } else {
                            $aps = new ArrayObject();
                        }
                        if($notice->usertype == 1){
                          $name = isset($notice->businessName)?$notice->businessName:'';
                        }else{
                           $name = isset($notice->name) ? $notice->name : '';
                        }
                        $arr_notice = array(
                            "aps" => $aps,
                            "notification_id" => $notice->notification_id,
                            "createDate" => $notice->postedDate,
                            "userid" => $notice->user_Id,
                            "username" => $notice->username,
                            "usertype" => $notice->usertype,
                            "name" => trim($name),
                            "userimage" => $image,
                            "action" => $notice->action,
                            "actionString" => $actionStrings,
                            "second_user_id" => $notice->actionTo,
                            "second_username" => $userdetail['username'],
                            "second_userimage" => $secondUserImage,
                            'challengeId' => isset($challengeId) ? $challengeId : '',
                            'gameId' => isset($gameId) ? $gameId : '',
                            'gameName' => isset($gameName) ? $gameName : '',
                            "status" => $onestatus
                        );

                        $notification[
                                ] = $arr_notice;
                    }
                    $success = 1;
                    $statusMessage = 'sucess, Notification Received';
                } else {
                    $success = 1;
                    $statusMessage = 'sucess, No Notification found';
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "notification" => $notification
                )
            );
        }


        echo json_encode($response);
        exit;
    }

    public function following() {
        $userhash = $this->input->post('userHash');
        $userid = $this->input->post('userid');
        $page = $this->input->post('page');
        if (isset($userhash) && $userhash != '' && isset($userid) && $userid != '' && isset($page)) {
            $this->load->model(array('user_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                if (is_numeric($userid)) {
                    $arr_users['user_Id'] = $userid;
                } else {
                    $arr_users['username'] = $userid;
                }
                $arr_users['active'] = 1;
                $me_user = $this->user_model->getOneUserDetails($arr_users, '*');
                $userid = $me_user->user_Id;

                $records = $this->user_model->getLoggedInUserFollowing($userid);

                $data['records'] = count($records);

                $config['base_url'] = base_url() . 'index.php/userservice/following/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = 50;
                $config['uri_segment'] = 3;
                $limit = $config['per_page'];

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                $following = $this->user_model->getLoggedInUserFollowing($userid, $page, $limit);
                foreach ($following as $users) {
                    $users->businessname = isset($users->businessname) ? $users->businessname : '';
                    $users->bio = isset($users->bio) ? $users->bio : '';
                    $users->location = isset($users->location) ? $users->location : '';
                    $getResult = $this->user_model->getloggedInuserFollowstatus($users->userid, $userdetail['userid']);
                    if (!empty($getResult)) {
                        $users->followed = 1;
                    } else {
                        $users->followed = 0;
                    }
                    if (isset($users->name)) {
                        $name = $users->name;
                    } else {
                        $name = '';
                    }

                    $users->name = $name;
                }
                if (count($following) != 0) {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "Results",
                            "following" => $following
                        )
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "Results",
                            "following" => $following
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error Occured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function followers() {
        $userhash = $this->input->post('userHash');
        $userid = $this->input->post('userid');
        $page = $this->input->post('page');

        if (isset($userhash) && $userhash != '' && isset($userid) && $userid != '' && isset($page)) {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {

                $records = $this->user_model->getLoggedInUserFollowers($userid);

                $data['records'] = count($records);

                $config['base_url'] = base_url() . 'index.php/userservice/followers/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;
                $limit = $config['per_page'];
                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                if (is_numeric($userid)) {
                    $arr_users['user_Id'] = $userid;
                } else {
                    $arr_users['username'] = $userid;
                }
                $arr_users['active'] = 1;
                $me_user = $this->user_model->getOneUserDetails($arr_users, '*');
                $userid = $me_user->user_Id;

                $followers = $this->user_model->getLoggedInUserFollowers($userid, $page, $limit);

                foreach ($followers as &$follow) {
                    // $getResult = $this->user_model->getloggedInuserFollowstatus($follow->userid, $userdetail['userid']);
                    // if(!empty($getResult)){
                    //      $users->followed = 1;
                    // }else{
                    //      $users->followed = 0;
                    // }
                    if (isset($follow->name)) {
                        $name = $follow->name;
                    } else {
                        $name = '';
                    }
                    $check['userId'] = $userdetail['userid'];
                    $check['followUserId'] = $follow->userid;
                    ;
                    $data = $this->user_model->checkfollow($check);
                    if (!empty($data)) {
                        $follow->followed = 1;
                    } else {
                        $follow->followed = 0;
                    }
                    $follow->name = $name;
                    $follow->bio = isset($follow->bio) ? $follow->bio : '';
                    $follow->location = isset($follow->location) ? $follow->location : '';
                }
                if (count($followers) != 0) {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "Results",
                            "followers" => $followers
                        )
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array
                            (
                            "status" => "success",
                            "statusMessage" => "No record found.",
                            "followers" => $followers
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function forgotpassword() {

        $email = $this->input->post('email');
        if (isset($email) && $email != '') {
            $this->load->model(array('user_model', 'email_model'));
            $checkemail = $this->user_model->checkEmail($email);

            if (count($checkemail) != 0) {

                $userid = $checkemail->user_Id;
                $username = $checkemail->username;
                $rand = $this->RandomStringForgotPassword();


                $link = base_url() . 'home/changePassword/' . $rand;
                $update['user_Id'] = $userid;
                $update['password_reset_token'] = $rand;
                $this->user_model->save($update);

                //// SEND  EMAIL START
                $this->emailConfig();   //Get configuration of email
                //// GET EMAIL FROM DATABASE

                $email_template = $this->email_model->getoneemail('forgot_password');
                //// MESSAGE OF EMAIL
                $messages = $email_template->message;


                $hurree_image = base_url() . '/assets/template/frontend/img/app-icon.png';
                $appstore = base_url() . '/assets/template/frontend/img/appstore.gif';
                $googleplay = base_url() . '/assets/template/frontend/img/googleplay.jpg';

                //// replace strings from message
                $messages = str_replace('{Username}', ucfirst($username), $messages);
                $messages = str_replace('{Password Reset Link}', $link, $messages);
                $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);
                $messages = str_replace('{App_Store_Image}', $appstore, $messages);
                $messages = str_replace('{Google_Image}', $googleplay, $messages);

                //// Email to user
                $this->email->from($email_template->from_email, 'Hurree');
                $this->email->to($email);
                $this->email->subject($email_template->subject);
                $this->email->message($messages);
                $this->email->send();    ////  EMAIL SEND

                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "success",
                        "statusMessage" => 'We' . "'" . 've sent you a link to reset your password, check your email!'
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "This email is not registerd with Hurree.co"
                    )
                );
            }
        } else {
            $response = array
                (
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    function RandomStringForgotPassword() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function checkin() {
        $baseurl = base_url();
        $userhash = $this->input->post('userHash');    //// Get UserHash
        $page = $this->input->post('page');
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        if (isset($userhash) && $userhash != '' && isset($latitude) && $latitude != '' && isset($longitude) && $longitude != '' && isset($page)) {
            $this->load->model('user_model');
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details
            if ($userdetail['status'] == 1) {
                $success = 1;



                $records = $this->user_model->getnearestBusiness(2, '', '', '', '', '', 5);   //// Get Total No of Records in Database

                $data['records'] = count($records);
                /* $data['records']=30; */
                $config['base_url'] = base_url() . 'index.php/userservice/brand_new/';
                $config['total_rows'] = $data[
                        'records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                $data['page'] = $page;
                $limit = $config['per_page'];
                $order_by['order_by'] = 'distance';
                $order_by['sequence'] = 'ASC';
                $select = 'user_Id as  userid , CONCAT(UCASE(LEFT(username, 1)), LCASE(SUBSTRING(username, 2))) as username, CONCAT_WS( " ", firstname, lastname ) as name, CONCAT("' . $baseurl . 'upload/profile/thumbnail/", image ) as userimage, businessName, ROUND( (((ACOS(SIN(latitude * PI() / 180) * SIN(' . $latitude . ' * PI() / 180) +
	            COS(' . $latitude . ' * PI() / 180) * COS(latitude * PI() / 180) * COS((' . $longitude . ' - longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) * 1.61 )*0.621371, 1 ) AS distance,
					businessName, branch_id, latitude, longitude,BB.businessId';
                $checkin = 1;

                $business_user = $this->user_model->getnearestBusiness(2, $page, $limit, $order_by, $select, $checkin, '5');   //Get Records

                foreach ($business_user as $branchId) {
                    $branchId->distance = isset($branchId->distance) ? $branchId->distance : '';
                    $branchId->branch_id = isset($branchId->branch_id) ? $branchId->branch_id : '';
                    $branchId->latitude = isset($branchId->latitude) ? $branchId->latitude : 0;
                    $branchId->longitude = isset($branchId->longitude) ? $branchId->longitude : 0;
                    $branchId->businessId= isset($branchId->businessId)?$branchId->businessId:0;

                    $business = array
                        (
                        'businessid' => isset($branchId->businessId)?$branchId->businessId:'',
                        'branchid' => isset($branchId->branch_id) ? $branchId->branch_id : ''
                    );
                    $checkInEntries = $this->user_model->checkInCount($business);

                    $checkInCount = count($checkInEntries);
                    $branchId->checkincount = $checkInCount;
                }

                $statusMessage = 'Success, Brand New Business';
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array
                    (
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "brand_new" => $business_user
                )
            );
        }

        echo json_encode($response);
        exit;
    }

//
    public function scanqr() {
        $userhash = $this->input->post('userHash');   //// Get UserHash
        $code = base64_decode($this->input->post('qrcode'));
        $offerId = $this->input->post('offerId');

        if (isset($userhash) && $userhash != '' && isset($code) && $code != '' && isset($offerId) && $offerId != '') {
            $this->load->model(array('user_model', 'offer_model'));
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash); //// Get UserHash Details
            if ($userdetail['status'] == 1) {
                $success = 1;
                // check offer id exists or not

                $checkoffer = $this->offer_model->checkOffer($offerId);
                if ($checkoffer) {


                    // get offer
                    $offerResult = $this->offer_model->getOffer($offerId, $code);

                    if ($offerResult) {


                        $success = 1;
                        //$offerDetails = $arr;


                        $statusMessage = 'Match';
                    } else {
                        $success = 0;
                        $statusMessage = "Code Not Found";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Offer Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dicti onary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array("status" => "success",
                    "statusMessage" => $statusMessage,
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function device_registration() {
        //$userid = $this->input->post('userid');
        $device_id = $this->input->post('vendorIdentifier');
        $key = $this->input->post('device_token');
        $device_type = $this->input->post('device_type');
        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '' && isset($device_id) && $device_id != '' && isset($key) && $key != '' && isset($device_type) && $device_type != '') {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            $userid = $userdetail['userid'];
            if ($userdetail['status'] == 1) {
                $arr_device['deviceTypeID'] = $device_type;
                $arr_device[
                        'userid'] = $userid;
                $arr_device['deviceId'] = $device_id;
                $arr_device['active'] = 1;

                $device_info = $this->user_model->getuserdevice($arr_device, $row = 0);

                if (count($device_info) > 0 && $device_info->key != $key) {
                    $update['active'] = 0;
                    $update[
                            'id'] = $device_info->id;
                    //// MAKE INACTIVE PREVIOUS DATA
                    $this->user_model->saveUserDevice($update);
                    $arr_detail['id'] = '';
                    $arr_detail['userid'] = $userid;
                    $arr_detail['key'] = $key;
                    $arr_detail[
                            'deviceId'] = $device_id;
                    $arr_detail['deviceTypeID'] = $device_type;
                    $arr_detail['created_date'] = date('YmdHis');
                    $this->user_model->saveUserDevice($arr_detail);
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "success",
                            "statusMessage" => "User Device Updated sucessfully"
                        )
                    );
                } else {

                    $arr_detail['userid'] = $userid;
                    $arr_detail['key'] = $key;
                    $arr_detail['deviceId'] = $device_id;
                    $arr_detail['deviceTypeID'] = $device_type;

                    //// INSERT
                    $device_exist = $this->user_model->getuserdevice($arr_detail, $row = 0);

                    if (count($device_exist) > 0) {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "User Device already exist"
                            )
                        );
                    } else {
                        $arr_detail['id'] = '';
                        $arr_detail['userid'] = $userid;
                        $arr_detail['key'] = $key;
                        $arr_detail['deviceId'] = $device_id;
                        $arr_detail['deviceTypeID'] = $device_type;
                        $arr_detail['created_date'] = date('YmdHis');
                        //Insert
                        $this->user_model->saveUserDevice($arr_detail);
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "User Device added sucessfully"
                            )
                        );
                    }
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

//
//    /* List of Buy Coins  */

    public function buy_coins() {
        $userhash = $this->input->post('userHash');

        if (isset($userhash) && $userhash != '') {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $where = array(
                    'usertype' => 1
                );
                $buycoins = $this->user_model->getbuycoins($where, 1);

                foreach ($buycoins as $buy_coins) {
                    $data = array(
                        'buyCoins_id' => $buy_coins->buyCoins_id,
                        'coins' => $buy_coins->coins,
                        'price_gbp' => $buy_coins->app_price_gbp,
                        'price_usd' => $buy_coins->app_price_usd
                    );
                    $arraybuycoins[] = $data;
                }

                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "success",
                        "results" => $arraybuycoins
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "s tatusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }

        echo json_encode($response);
        exit;
    }

//
//    /* When user purchase coins from app transaction records save into database */
//
    public function coinsSubscription() {
        $userhash = $this->input->post('userHash');
        $buyCoins_id = $this->input->post('buyCoins_id');
        $transactionid = $this->input->post('transactionid');
        $price = $this->input->post('price');
        $currency = $this->input->post('currency');
        if (isset($userhash) && $userhash != '' && isset($buyCoins_id) && $buyCoins_id != '' && isset($transactionid) && $transactionid != '' && isset($price) && $price != '' && isset($currency) && $currency != '') {
            $this->load->model(array('user_model', 'store_model', 'score_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $userid = $userdetail['userid'];

                $date = date('YmdHis');
                $payment['payment_id'] = '';
                $payment['user_id'] = $userid;
                $payment['buyCoins_id'] = $buyCoins_id;
                $payment['purchasedOn'] = $date;
                $payment['amount'] = $price;
                $payment['currency'] = $currency;
                $payment['transaction_id'] = $transactionid;
                $payment['paymentInfo'] = 'success';
                $payment['createdDate'] = $date;
                $payment['modifiedDate'] = $date;

                $last_payment_id = $this->store_model->savepayment($payment);

                $buyCoins = $this->store_model->buyCoinsRow($buyCoins_id);

                $totalCoins = $this->score_model->getUserCoins($userid);
                $newTotalCoins = $totalCoins->coins + $buyCoins->coins;
                $update = array(
                    'userid' => $userid,
                    'coins' => $newTotalCoins,
                );
                $this->score_model->update($update);

                $insert = array(
                    'userid' => $userid,
                    'coins' => $buyCoins->coins,
                    'coins_type' => 7,
                    'businessid' => 0,
                    'actionType' => 'add',
                    'createdDate' => $date
                );

                $this->score_model->insert($insert);


                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "success",
                        "statusMessage" => "You just bought coins, you rock!",
                        "totalCoins" => $newTotalCoins
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    /* When user purchase game, save transaction records into database  */

    public function payment_details() {
        $userhash = $this->input->post('userHash');
        $gameid = $this->input->post('gameid');
        $transactionId = $this->input->post('transactionid');
        $price = $this->input->post('price');
        $paymentInfo = $this->input->post('paymentinfo');
        $firstpurchase = $this->input->post('firstpurchase');
        if (isset($userhash) && $userhash != '' && isset($gameid) && $gameid != '' && isset($transactionId) && $transactionId != '' && isset($price) && $price != '' && isset($firstpurchase)) {
            $this->load->model(array('user_model', 'subscription_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $userid = $userdetail['userid'];
            if ($userdetail['status'] == 1) {
                $userid = $userdetail['userid'];

                if ($firstpurchase == '') {
                    $firstPurchase = '1';
                } else {
                    $firstPurchase = $firstpurchase;
                }

                $duration = 12;    //// GET SUBSCRIPTION DURATION

                $date = date_create(date('Y-m-d'));    ///// CREATE OBJECT
                date_add($date, date_interval_create_from_date_string($duration . ' month'));     ///// ADD DURATION INTO
                $duration_month = date_format($date, 'Y-m-d');

                /*
                 * DATA INSERT FOR Subscription
                 */
                $subscription['user_id'] = $userid;
                $subscription['game_id'] = $gameid;
                $subscription['expiration'] = $duration_month;
                $subscription['purchasedOn'] = date('YmdHis');
                $subscription['amount'] = $price;
                $subscription['transaction_id'] = $transactionId;
                $subscription['paymentInfo'] = $paymentInfo;
                $subscription['active'] = '1';
                $subscription['firstPurchase'] = $firstPurchase;
                $subscription['createdDate'] = date('YmdHis');
                $subscription['modifiedDate'] = date('YmdHis');

                $this->subscription_model->saveUserSubscription($subscription);

                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "success",
                        "statusMessage" => "Transaction details saved successfully"
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    /* Create Challenge  */

    public function createChallange() {
        $userhash = $this->input->post('userHash');
        $challenge_to = $this->input->post('challenge_to');
        $challenge_coins = $this->input->post('challenge_coins');
        $game_id = $this->input->post('game_id');

        if (isset($userhash) && $userhash != '' && isset($challenge_to) && $challenge_to != '' && isset($challenge_coins) && $challenge_coins != '' && isset($game_id) && $game_id != '' && is_numeric($game_id)) {
            $userhash = $this->input->post('userHash');
            $this->load->model(array('user_model', 'subscription_model', 'score_model', 'challenge_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                if (($challenge_to != 0)) {
                    if ($challenge_to != $userdetail['userid']) {
                        $where['user_Id'] = $challenge_to;
                        $where['active'] = 1;
                        $challenge_to_user = $this->user_model->getOneUserDetails($where, '*');

                        if (count($challenge_to_user) > 0) {

                            if($challenge_to_user->usertype == 1){
                                $data['userid'] = $userdetail['userid'];
                                $data['block_user_id'] = $challenge_to;

                                $userBlock = $this->user_model->UserBlock($data,1);
                                if(count($userBlock) == 0){


                            if (is_numeric($challenge_coins) && $challenge_coins > 0) {

                                $userid = $userdetail['userid'];

                                $gamesubscription = $this->subscription_model->getusersubscription('user_id', $userid);
                                $subsgameid = array();
                                if (count($gamesubscription) > 0) {
                                    foreach ($gamesubscription as $subs_gameid) {
                                        $subsgameid[] = $subs_gameid->game_id;
                                    }
                                }

                                if (!empty($subsgameid) && (in_array($game_id, $subsgameid) || $subsgameid[0] == '5')) {

                                    $gamescore = $this->score_model->getUserCoins($userid);

                                    if (count($gamescore) > 0) {
                                        if ($gamescore->coins >= $challenge_coins) {

                                            $data = array(
                                                'challenge_id' => '',
                                                'challenge_from' => $userid,
                                                'challenge_to' => $challenge_to,
                                                'game_id' => $game_id,
                                                'challenge_coins' => $challenge_coins,
                                                'createdDate' => date('Y-m-d H:i:s')
                                            );

                                            $gameId = $this->input->post('game_id');

                                            $this->db->select('SUM(challenge_coins) AS TotalCoins');
                                            $this->db->from('challenge');
                                            $this->db->where('challenge_from', $userid);
                                            $this->db->where('approval', 1);
                                            $scores = $this->db->get();
                                            $score = $scores->row();
                                            if ($gamescore >= $score) {

                                                // get time
                                                $previousTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
                                                $currentTime = date('Y-m-d H:i:s');


                                                $this->db->select('SUM(challenge_coins) AS TotalCoins');
                                                $this->db->from('challenge');
                                                $this->db->where('challenge_from', $userid);
                                                $this->db->where('createdDate >=', $previousTime);
                                                $this->db->where('createdDate <=', $currentTime);
                                                $scores = $this->db->get();

                                                $score = $scores->row();   // total coins user have spend in 1 hour

                                                if (count($score) > 0) {
                                                    $challenge_score = $score->TotalCoins;
                                                    $game_score = $gamescore->coins - $challenge_score;
                                                }

                                                $this->db->select('SUM(challenge_coins) AS TotalCoins');
                                                $this->db->from('challenge');
                                                $this->db->where('challenge_to', $challenge_to);
                                                $this->db->where('createdDate >=', $previousTime);
                                                $this->db->where('createdDate <=', $currentTime);
                                                $this->db->where('approval', 1);
                                                $scores = $this->db->get();
                                                $score = $scores->row();   // get chanllanged user score

                                                if (count($score) > 0) {
                                                    $receive_score = $score->TotalCoins;
                                                    $game_score = $gamescore->coins - $receive_score;
                                                }

                                                $response = array();

                                                if ($game_score >= $challenge_coins) {

                                                    $result = $this->challenge_model->add($data);
                                                    $last_id = $this->db->insert_id();

                                                    $where['user_Id'] = $challenge_to;
                                                    $challenge_to_user = $this->user_model->getOneUserDetails($where, '*');

                                                    $arr_notice['notification_id'] = '';
                                                    $arr_notice['actionFrom'] = $userdetail['userid'];
                                                    $arr_notice['actionTo'] = $challenge_to;
                                                    $arr_notice['action'] = 'CC';

                                                    $arr_notice['actionString'] = "sent you a challenge! ";
                                                    $arr_notice['message'] = '';
                                                    $arr_notice['statusid'] = '';
                                                    $arr_notice['challangeid'] = $last_id;
                                                    $arr_notice['active'] = '1';
                                                    $arr_notice['createdDate'] = date('YmdHis');

                                                    $notice_id = $this->notification_model->savenotification($arr_notice);


                                                    // send notification code start
                                                    $deviceInfo = $this->user_model->getdeviceToken($challenge_to);
                                                    if (count($deviceInfo) > 0) {
                                                        foreach ($deviceInfo as $device) {
                                                            $deviceToken = $device->key;
                                                            $deviceType = $device->deviceTypeID;
                                                            $title = 'My Test Message';
                                                            $sound = 'default';
                                                            $msgpayload = json_encode(array(
                                                                'aps' => array(
                                                                    "alert" => '@'. $userdetail['username'] . ' sent you a challenge!',
                                                                    "challengeId" => $last_id,
                                                                    "userid" => '',
                                                                    "Coins" => '',
                                                                    "username" => '',
                                                                    "userimage" => '',
                                                                    "game_id" => $game_id,
                                                                    "type" => "challenge",
                                                                    "sound" => $sound
                                                            )));
                                                            $message = json_encode(array(
                                                                'default' => $title,
                                                                'APNS_SANDBOX' => $msgpayload
                                                            ));

                                                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                                        }
                                                    }

                                                    // end
                                                    $response = array(
                                                        "status" => "success",
                                                        "statusMessage" => "Your challenge has been successfully sent",
                                                        "data" => array(
                                                            "challengeId" => $last_id,
                                                            "gameId" => $gameId,
                                                            "challengeFrom" => $userid,
                                                            "challengeTo" => $challenge_to,
                                                            "coins" => $challenge_coins
                                                        )
                                                    );
                                                } else {
                                                    $response = array(
                                                        "status" => "error",
                                                        "statusMessage" => "You don't have much coins for sending challenge"
                                                    );
                                                }
                                            } else {
                                                $response = array(
                                                    "status" => "error",
                                                    "statusMessage" => "You don't have much coins for sending more challenge."
                                                );
                                            }
                                        } else {
                                            $response = array(
                                                "status" => "error",
                                                "statusMessage" => "You don't have much coins for this challenge"
                                            );
                                        }
                                    } else {
                                        $response = array(
                                            "status" => "error",
                                            "statusMessage" => "You havn't play this game. Play first to create challenge"
                                        );
                                    }
                                } else {

                                    $response = array(
                                        "status" => "error",
                                        "statusMessage" => "Please buy the game to send challenge"
                                    );
                                }
                            } else {
                                $response = array(
                                    "status" => "error",
                                    "statusMessage" => "You entered invalid coins"
                                );
                            }
                             } else {
                            $response = array(
                                "status" => "error",
                                "statusMessage" => "You have not access to send challenge to this user."
                            );
                            }
                            }else {
                            $response = array(
                                "status" => "error",
                                "statusMessage" => "You can't send challenge to business user"
                            );
                        }
                        } else {
                            $response = array(
                                "status" => "error",
                                "statusMessage" => "You can't challenge to invalid user"
                            );
                        }
                    } else {
                        $response = array(
                            "status" => "error",
                            "statusMessage" => "You cannot send challenge to own."
                        );
                    }
                } else {
                    $response = array(
                        "status" => "error",
                        "statusMessage" => "Please add a valid user"
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values cannot be blank or should be in valid format."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    /* Accept Challenge  */

    public function challengeAccept() {
        $userhash = $this->input->post('userHash');
        $challengeId = $this->input->post('challenge_id');

        if (isset($userhash) && $userhash != '' && isset($challengeId) && $challengeId != '') {

            $this->load->model(array('user_model', 'subscription_model', 'score_model', 'challenge_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $userid = $userdetail['userid'];
            if ($userdetail['status'] == 1) {

                //To check game is subscribed
                $gamesubscription = $this->subscription_model->get_one_game_subscription('user_id', $userid);
                $subsgameid = array();
                if (count($gamesubscription) > 0) {
                    foreach ($gamesubscription as $subs_gameid) {
                        $subsgameid[] = $subs_gameid->game_id;
                    }
                } else {
                    $subsgameid[] = '';
                }

                $challange = $this->challenge_model->getchallanges('challenge_id', $challengeId, $row = 0);
                if ($challange->approval == 0) {

                    if (count($challange) > 0) {
                        $challenge_gameid = $challange->game_id;
                        $target_coins = $challange->challenge_coins;

                        if ($userid == $challange->challenge_to) { //echo $challenge_gameid; print_r($subsgameid);exit;
                            if (in_array($challenge_gameid, $subsgameid) || $subsgameid == 5) {//Check subscription game and challenge game
                                $gamescore = $this->score_model->getUserCoins($userid);


                                if (count($gamescore) > 0) {
                                    if ($gamescore->coins >= $challange->challenge_coins) {

                                        $data = array(
                                            'challenge_id' => $challengeId,
                                            'approval' => 1,
                                        );

                                        $game_score = $gamescore->coins;

                                        $previousTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
                                        $currentTime = date('Y-m-d H:i:s');


                                        $this->db->select('SUM(challenge_coins) AS TotalCoins');
                                        $this->db->from('challenge');
                                        $this->db->where('challenge_from', $userid);
                                        $this->db->where('createdDate >=', $previousTime);
                                        $this->db->where('createdDate <=', $currentTime);
                                        $scores = $this->db->get();
                                        $score = $scores->row();

                                        if (count($score) > 0) {
                                            $challenge_score = $score->TotalCoins;

                                            $game_score = $game_score - $challenge_score;
                                        }


                                        $this->db->select('SUM(challenge_coins) AS TotalCoins');
                                        $this->db->from('challenge');
                                        $this->db->where('challenge_to', $userid);
                                        $this->db->where('createdDate >=', $previousTime);
                                        $this->db->where('createdDate <=', $currentTime);
                                        $this->db->where('approval', 1);
                                        $scores = $this->db->get();
                                        $score = $scores->row();
                                        if (count($score) > 0) {
                                            $receive_score = $score->TotalCoins;
                                            $game_score = $game_score - $receive_score;
                                        }
                                        $response = array();

                                        if ($game_score >= $target_coins) {
                                            $result = $this->challenge_model->challengeAccept($data);

                                            /*  Notification Array  */
                                            $arr_notice['notification_id'] = '';
                                            $arr_notice['actionFrom'] = $userdetail['userid'];
                                            $arr_notice['actionTo'] = $challange->challenge_from;
                                            $arr_notice['action'] = 'CA';
                                            //$arr_notice['actionString']="@".ucfirst($userdetail['username'])." accepts";
                                            $arr_notice['actionString'] = "accepted your challenge!";
                                            $arr_notice['message'] = '';
                                            $arr_notice['statusid'] = '';
                                            $arr_notice['challangeid'] = $challengeId;
                                            $arr_notice['active'] = '1';
                                            $arr_notice['createdDate'] = date('YmdHis');

                                            $notice_id = $this->notification_model->savenotification($arr_notice);
                                            // send notification code start
                                            $deviceInfo = $this->user_model->getdeviceToken($challange->challenge_to);
                                            if (count($deviceInfo) > 0) {
                                                foreach ($deviceInfo as $device) {
                                                    $deviceToken = $device->key;
                                                    $deviceType = $device->deviceTypeID;
                                                    $title = 'My Test Message';
                                                    $sound = 'default';
                                                    $msgpayload = json_encode(array(
                                                        'aps' => array(
                                                            "alert" => '@'. $userdetail['username'] . ' accept your challenge!',
                                                            "challengeId" => $challengeId,
                                                            "userid" => '',
                                                            "Coins" => '',
                                                            "username" => '',
                                                            "userimage" => '',
                                                            "game_id" => $challange->game_id,
                                                            "type" => "challenge",
                                                            "sound" => $sound
                                                    )));
                                                    $message = json_encode(array(
                                                        'default' => $title,
                                                        'APNS_SANDBOX' => $msgpayload
                                                    ));
                                                    //$message = 'Declined Challenge: '.$userdetail['username'].' declined your challenge!';

                                                    $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                                }
                                            }

                                            // end


                                            $response = array(
                                                "challengeId" => $challengeId,
                                                "game_id" => $challenge_gameid,
                                                "status" => "success",
                                                "statusMessage" => "You accept challenge successfully "
                                            );
                                        } else {

                                            $response = array(
                                                "status" => "error",
                                                "statusMessage" => "Get more coins"
                                            );
                                        }
                                    } else {
                                        $response = array(
                                            "status" => "error",
                                            "statusMessage" => "You don't have coins for this game challenge"
                                        );
                                    }
                                } else {
                                    $response = array(
                                        "status" => "error",
                                        "statusMessage" => "You don't have enough coins to accept this challenge."
                                    );
                                }
                            } else {
                                $response = array(
                                    "status" => "error",
                                    "statusMessage" => "Please buy the game firstly."
                                );
                            }
                        } else {

                            $response = array(
                                "status" => "error",
                                "statusMessage" => "Challenge is not applicable for this user"
                            );
                        }
                    } else {
                        $response = array(
                            "status" => "error",
                            "statusMessage" => "This Challange does not exits"
                        );
                    }
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "This challenge have accepted already."
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Paramerters not found or value cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    /* When user decline challenge */

    public function challengeDecline() {
        $userhash = $this->input->post('userHash');
        $challengeId = $this->input->post('challenge_id');

        if (isset($userhash) && $userhash != '' && isset($challengeId) && $challengeId != '') {
            $userhash = $this->input->post('userHash');
            $this->load->model(array('user_model', 'challenge_model', 'notification_model'));
            $this->load->model(array('user_model', 'challenge_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $previousTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
                $currentTime = date('Y-m-d H:i:s');
                $this->db->select('challenge_id');
                $this->db->from('challenge');
                $this->db->where('challenge_id', $challengeId);
                $this->db->where('createdDate >=', $previousTime);
                $this->db->where('createdDate <=', $currentTime);
                $duration = $this->db->get();
                $time = $duration->row();

                if (count($time) > 0) {

                    $onechallahnge = $this->challenge_model->getchallanges('challenge_id', $challengeId, $row = 0);

                    if (count($onechallahnge) > 0) {
                        if ($onechallahnge->approval == 0) {
                            $arr_clnge['challenge_id'] = $challengeId;
                            $arr_clnge['approval'] = 2;
                            $results = $this->challenge_model->challengeAccept($arr_clnge);
                            //$result = $this->user_model->challengeDecline($challengeId);
                            $userid = $userdetail['userid'];
                            //Push Notification
                            $deviceInformation = $this->user_model->userdeviceinformation('userid', $onechallahnge->challenge_from);
                            $challenge_user = $this->user_model->checkfield('user_Id', $userid);
                            $challengeusername = $challenge_user->username;
                            $message = "@" . $challengeusername . " Declined your challenge!";

                            /*  Notification Array  */
                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $userdetail['userid'];
                            $arr_notice['actionTo'] = $onechallahnge->challenge_from;
                            $arr_notice['action'] = 'CD';
                            //$arr_notice['actionString']="@".ucfirst($userdetail['username'])." declined your challenge";
                            $arr_notice['actionString'] = " declined your challenge";
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = '';
                            $arr_notice['challangeid'] = $challengeId;
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = date('YmdHis');

                            $notice_id = $this->notification_model->savenotification($arr_notice);

                           if($userdetail['userid']!= $onechallahnge->challenge_from){
                            // send notification code start
                            $deviceInfo = $this->user_model->getdeviceToken($onechallahnge->challenge_from);
                            if (count($deviceInfo) > 0) {
                                foreach ($deviceInfo as $device) {
                                    $deviceToken = $device->key;
                                    $deviceType = $device->deviceTypeID;
                                    $title = 'My Test Message';
                                    $sound = 'default';
                                    $msgpayload = json_encode(array(
                                        'aps' => array(
                                            "alert" => '@'. $userdetail['username'] . ' declined your challenge!',
                                            "challengeId" => $challengeId,
                                            "userid" => '',
                                            "Coins" => '',
                                            "username" => '',
                                            "userimage" => '',
                                            "game_id" => $onechallahnge->game_id,
                                            "type" => "challenge",
                                            "sound" => $sound
                                    )));
                                    $message = json_encode(array(
                                        'default' => $title,
                                        'APNS_SANDBOX' => $msgpayload
                                    ));
                                    //$message = 'Declined Challenge: '.$userdetail['username'].' declined your challenge!';

                                    $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                }
                            }
                        }

                            // end

                            $response = array();
                            if ($results == '1') {
                                $response = array(
                                    "challengeId" => $challengeId,
                                    "game_id" => $onechallahnge->game_id,
                                    "status" => "success",
                                    "statusMessage" => "You have decline challenge suc cessfully "
                                );
                            } else {
                                $response = array(
                                    "status" => "error",
                                    "statusMessage" => "An Error occured while decline challenge"
                                );
                            }
                        } else {
                            $response = array(
                                "status" => "error",
                                "statusMessage" => "You have already accept or declined this challenge"
                            );
                        }
                    } else {
                        $response = array(
                            "status" => "error",
                            "statusMessage" => "This Challenge Does not exits"
                        );
                    }
                } else {
                    $response = array(
                        "status" => "error",
                        "statusMessage" => "This Challenge is expired"
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Paramerts  not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function challengeScore() {

        $userhash = $this->input->post('userHash');
        $challengeId = $this->input->post('challengeId');
        $gameId = $this->input->post('gameId');
        $score = $this->input->post('score');
        if (isset($userhash) && $userhash != '' && isset($challengeId) && $challengeId != '' && isset($gameId) && $gameId != '' && isset($score)) {
            $this->load->model(array('user_model', 'challenge_model', 'score_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);

            if ($userdetail['status'] == 1) {
                $userid = $userdetail['userid'];

                $onechallahnge = $this->challenge_model->getchallanges('challenge_id', $challengeId, $row = 0);

                //Challenge exist or not
                if (count($onechallahnge) == 1) {

                    $date = date('YmdHis');
                    $insert['score_id'] = '';
                    $insert['user_id'] = $userid;
                    $insert['game_id'] = $gameId;
                    $insert['challengeid'] = $challengeId;
                    $insert['coins'] = $score;
                    $insert['score'] = $score;
                    $insert['createdDate'] = $date;
                    $insert['modifiedDate'] = $date;

                    //Challenge accepter game score
                    if ($onechallahnge->challenge_to == $userid) {

                        $data['user_id'] = $userid;
                        $data['challengeid'] = $challengeId;
                        $gameScore = $this->score_model->getChallengeScore($data);

                        if (count($gameScore) == 0) {
                            $this->score_model->pushscore($insert);

                            $arr_login['user_Id'] = $onechallahnge->challenge_from;
                            $arr_login['active'] = 1;
                            $challengeCreatorUser = $this->user_model->getOneUserDetails($arr_login, '*');
                            $challengeCreator = "@" . ucfirst($challengeCreatorUser->username);

                            /*  Notification Array  */
                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $userdetail['userid'];
                            $arr_notice['actionTo'] = $onechallahnge->challenge_from;
                            $arr_notice['action'] = 'CS';
                            $arr_notice['actionString'] = " scored " . $score . " Coins, play now?";
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = '';
                            $arr_notice['challangeid'] = $challengeId;
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = $date;


                            $notice_id = $this->notification_model->savenotification($arr_notice);

                            $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                            "status" => "success",
                            "statusMessage" => "It's " . $challengeCreator . " turn to play the game!"
                            )
                            );
                        } else {
                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "error",
                                    "statusMessage" => "Error occoured. Already score is stored for this challenge."
                                )
                            );
                        }
                    } else {
                        //Challenge creator game score
                        //echo $userdetail['username']; die;

                        $data['user_id'] = $userid;
                        $data['challengeid'] = $challengeId;
                        $gameScore = $this->score_model->getChallengeScore($data);

                        if (count($gameScore) == 0) {

                            $this->score_model->pushscore($insert);

                            $data['user_id'] = $onechallahnge->challenge_to;
                            $data['challengeid'] = $challengeId;
                            $getAccepterScore = $this->score_model->getChallengeScore($data);

                            $accepterScore = $getAccepterScore->score;
                            $challengerScore = $_POST['score'];

                            if ($challengerScore > $accepterScore) {
                                $winner = $onechallahnge->challenge_from;
                                $looser = $onechallahnge->challenge_to;
                            } else {
                                $winner = $onechallahnge->challenge_to;
                                $looser = $onechallahnge->challenge_from;
                            }

                            $challangedCoins = $onechallahnge->challenge_coins;
                            $gameid = $onechallahnge->game_id;

                            if ($winner == $userid) {
                                $message = 'You won this challenge and got ' . $challangedCoins . ' coins!';
                            } else {
                                $message = 'You lost this challenge, try again?';
                            }

                            $arr_winner_coin['coins_id'] = '';
                            $arr_winner_coin['userid'] = $winner;
                            $arr_winner_coin['coins'] = $challangedCoins;
                            $arr_winner_coin['coins_type'] = 2;
                            $arr_winner_coin['game_id'] = $gameid;
                            $arr_winner_coin['actionType'] = 'add';
                            $arr_winner_coin['createdDate'] = date('YmdHis');
                            $this->score_model->insert($arr_winner_coin);   //// Update Winner User Coins in userCoin

                            $winnerCoins = $this->score_model->getUserCoins($winner); //// Get Winner Total Coins
                            if (count($winnerCoins) > 0) {
                                $winnerTotalCoins = $winnerCoins->coins;
                                $winnerNewCoins = $winnerTotalCoins + $challangedCoins;

                                $arr_winner_total['user_score_id'] = $winnerCoins->user_score_id;
                                $arr_winner_total['modifiedDate'] = date('YmdHis');
                            } else {
                                $winnerNewCoins = $challangedCoins;
                                $arr_winner_total['user_score_id'] = '';
                                $arr_winner_total['createdDate'] = date('YmdHis');
                            }

                            $arr_winner_total['userid'] = $winner;
                            $arr_winner_total['coins'] = $winnerNewCoins;
                            $this->score_model->saveuserTotalCoins($arr_winner_total);   ///// Update winner Total Coins


                            $arr_looser_coin['coins_id'] = '';
                            $arr_looser_coin['userid'] = $looser;
                            $arr_looser_coin['coins'] = $challangedCoins;
                            $arr_looser_coin['coins_type'] = 3;
                            $arr_looser_coin['game_id'] = $gameid;
                            $arr_looser_coin['actionType'] = 'sub';
                            $arr_looser_coin['createdDate'] = date('YmdHis');
                            $this->score_model->insert($arr_looser_coin);

                            $looserCoins = $this->score_model->getUserCoins($looser); //// Update Looser User Coins in userCoin

                            if (count($looserCoins) > 0) {
                                $looserTotalCoins = $looserCoins->coins;
                                $looserNewCoins = $looserTotalCoins - $challangedCoins;

                                $arr_looser_total['user_score_id'] = $looserCoins->user_score_id;
                                $arr_looser_total['modifiedDate'] = date('YmdHis');
                            } else {
                                $looserNewCoins = 0;
                                $arr_looser_total['user_score_id'] = '';
                                $arr_looser_total['createdDate'] = date('YmdHis');
                            }

                            $arr_looser_total['userid'] = $looser;
                            $arr_looser_total['coins'] = $looserNewCoins;

                            $this->score_model->saveuserTotalCoins($arr_looser_total);  ///// Update looser Total Coins
                            //Auto Update
                            $userWinner = $this->user_model->getOneUser($winner);
                            $userWinner->username;

                            $userLooser = $this->user_model->getOneUser($looser);
                            $userLooser->username;

                            $arr_status['status_id'] = '';
                            $arr_status['parentStatusid'] = 0;
                            $arr_status['status'] = '<un>@' . $userWinner->username . '</un> won a challenge against <un>@' . $userLooser->username . '</un>';
                            $arr_status['userid'] = $winner;
                            $arr_status['status_image'] = '';
                            $arr_status['usermentioned'] = $userWinner->username . ',' . $userLooser->username;
                            $arr_status['shareFrom'] = 0;
                            $arr_status['share'] = 0;
                            $arr_status['active'] = 1;
                            $arr_status['isCheckInUserCoinsId'] = '';
                            $arr_status['createdDate'] = date('YmdHis');
                            $arr_status['modifiedDate'] = date('YmdHis');
                            $status_id = $this->user_model->saveUserStatus($arr_status);
                            //End Auto Update

                            /* Start Notification for CL  */
                            $arr_notice['notification_id'] = '';
                            $arr_notice['actionFrom'] = $winner;
                            $arr_notice['actionTo'] = $looser;
                            $arr_notice['action'] = 'CL';
                            $arr_notice['actionString'] = 'You lost a challenge against';
                            $arr_notice['message'] = '';
                            $arr_notice['statusid'] = '';
                            $arr_notice['challangeid'] = $challengeId;
                            $arr_notice['active'] = '1';
                            $arr_notice['createdDate'] = date('YmdHis');

                            $notice_id = $this->notification_model->savenotification($arr_notice);

                            /* Start Notification for CW  */
                            $arr_notice_win['notification_id'] = '';
                            $arr_notice_win['actionFrom'] = $looser;
                            $arr_notice_win['actionTo'] = $winner;
                            $arr_notice_win['action'] = 'CW';
                            $arr_notice_win['actionString'] = 'You won a challenge against';
                            $arr_notice_win['message'] = '';
                            $arr_notice_win['statusid'] = '';
                            $arr_notice_win['challangeid'] = $challengeId;
                            $arr_notice_win['active'] = '1';
                            $arr_notice_win['createdDate'] = date('YmdHis');

                            $notice_id = $this->notification_model->savenotification($arr_notice_win);



                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "success",
                                    "statusMessage" => $message
                                )
                            );
                        } else {
                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "error",
                                    "statusMessage" => "Error occoured. Already score is stored for this challenge."
                                )
                            );
                        }
                    }
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "Error occoured. Challenge not found"
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Paramerts  not found or values cannot be blank."
                )
            );
        }


        echo json_encode($response);
        exit;
    }

    public function challengeReminder() {
        $userhash = $this->input->post('userHash');
        $challengeId = $this->input->post('challenge_id');
        if (isset($userhash) && $userhash != '' && isset($challengeId) && $challengeId != '') {
            $this->load->model(array('user_model', 'challenge_model'));
            $userdetail = $this->user_model->getuserHash($userhash);

            if ($userdetail['status'] == 1) {

                $onechallahnge = $this->challenge_model->getchallanges('challenge_id', $challengeId, $row = 0);
                if ($onechallahnge->approval == 0) {
                    $response = array(
                        "challengeId" => $challengeId,
                        "status" => "success",
                        "statusMessage" => "Your challenge reminder has been sent successfully"
                    );
                } else {
                    $response = array(
                        "challengeId" => $challengeId,
                        "status" => "error",
                        "statusMessage" => "Can't send Reminder. This challange is already either approved or declined"
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Paramerts  not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function hide_user() {
        $userhash = $this->input->post('userHash');

        if (isset($userhash) && $userhash != '') {
            $this->load->model('user_model');
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details

            if ($userdetail['status'] == 1) {
                $success = 1;
                $suggestedUserid = $_POST['suggested_user_id'];
                if ($suggestedUserid != '') {
                    $success = 1;
                    $arr_user['user_Id'] = $suggestedUserid;
                    $arr_user['active'] = 1;
                    $suggested_User = $this->user_model->getOneUserDetails($arr_user, '*');
                    if (count($suggested_User) > 0) {
                        $success = 1;
                        $arr_where['userid'] = $userdetail['userid'];
                        $arr_where['lastSuggestionid'] = $suggestedUserid;
                        $suggestion = $this->user_model->getsuggestionDetail();

                        $arr_suggestion['suggestion_id'] = $suggestion->suggestion_id;
                        $arr_suggestion['lastRejectedid'] = $suggestedUserid;
                        $arr_suggestion['modifiedDate'] = date('YmdHis');
                        $this->user_model->savesuggestionTime($arr_suggestion);
                        $statusMessage = "Success. User will Not show";
                    } else {
                        $success = 1;
                        $statusMessage = "Error Occured. Suggested User Not Found";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Error Occured. Suggested User Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters  not found or values cannot be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    public function challengeWon() {
        $userhash = $this->input->post('userHash');
        $challengeId = $this->input->post('challenge_id');
        $winnerId = $this->input->post('winnerid');
        if (isset($userhash) && $userhash != '' && isset($challengeId) && $challengeId != '' && isset($winnerId) && $winnerId != '') {
            $this->load->model(array('user_model', 'challenge_model', 'score_model', 'notification_model'));
            $success = 1;
            $userdetail = $this->user_model->getuserHash($userhash);   //// check user Exits or not
            if ($userdetail['status'] == 1) {
                $success = 1;


                if ($challengeId != '' && $winnerId != '') {
                    $arr_challange = array(
                        'challenge_id' => $challengeId,
                        'winner' => $winnerId
                    );
                    $this->challenge_model->update($arr_challange);  //// Update Challange

                    $arr_chellengeData['challenge_id'] = $challengeId;
                    $chellangeDetails = $this->challenge_model->getchallanges($arr_chellengeData);  //// Get Challange Database
                    if ($chellangeDetails->challenge_from == $winnerId) {
                        $winner = $chellangeDetails->challenge_from;
                        $looser = $chellangeDetails->challenge_to;
                    } else {
                        $winner = $chellangeDetails->challenge_to;
                        $looser = $chellangeDetails->challenge_from;
                    }

                    $challangedCoins = $chellangeDetails->challenge_coins;
                    $gameid = $chellangeDetails->game_id;


                    $arr_winner_coin['coins_id'] = '';
                    $arr_winner_coin['userid'] = $winner;
                    $arr_winner_coin['coins'] = $challangedCoins;
                    $arr_winner_coin['coins_type'] = 2;
                    $arr_winner_coin['game_id'] = $gameid;
                    $arr_winner_coin['actionType'] = 'add';
                    $arr_winner_coin['createdDate'] = date('YmdHis');
                    $this->score_model->insert($arr_winner_coin);   //// Update Winner User Coins in userCoin

                    $winnerCoins = $this->score_model->getUserCoins($winner); //// Get Winner Total Coins
                    if (count($winnerCoins) > 0) {
                        $winnerTotalCoins = $winnerCoins->coins;
                        $winnerNewCoins = $winnerTotalCoins + $challangedCoins;

                        $arr_winner_total['user_score_id'] = $winnerCoins->user_score_id;
                        $arr_winner_total['modifiedDate'] = date('YmdHis');
                    } else {
                        $winnerNewCoins = $challangedCoins;
                        $arr_winner_total['user_score_id'] = '';
                        $arr_winner_total['createdDate'] = date('YmdHis');
                    }

                    $arr_winner_total['userid'] = $winner;
                    $arr_winner_total['coins'] = $winnerNewCoins;
                    $this->score_model->saveuserTotalCoins($arr_winner_total);   ///// Update winner Total Coins


                    $arr_looser_coin['coins_id'] = '';
                    $arr_looser_coin['userid'] = $looser;
                    $arr_looser_coin['coins'] = $challangedCoins;
                    $arr_looser_coin['coins_type'] = 3;
                    $arr_looser_coin['game_id'] = $gameid;
                    $arr_looser_coin['actionType'] = 'sub';
                    $arr_looser_coin['createdDate'] = date('YmdHis');
                    $this->score_model->insert($arr_looser_coin);

                    $looserCoins = $this->score_model->getUserCoins($looser); //// Update Looser User Coins in userCoin

                    if (count($looserCoins) > 0) {
                        $looserTotalCoins = $looserCoins->coins;
                        $looserNewCoins = $looserTotalCoins - $challangedCoins;

                        $arr_looser_total['user_score_id'] = $looserCoins->user_score_id;
                        $arr_looser_total['modifiedDate'] = date('YmdHis');
                    } else {
                        $looserNewCoins = 0;
                        $arr_looser_total['user_score_id'] = '';
                        $arr_looser_total['createdDate'] = date('YmdHis');
                    }

                    $arr_looser_total['userid'] = $looser;
                    $arr_looser_total['coins'] = $looserNewCoins;

                    $this->score_model->saveuserTotalCoins($arr_looser_total);  ///// Update looser Total Coins
                    //Push Notification
                    $challange = $this->challenge_model->getchallanges('challenge_id', $challengeId, $row = 0);   //// get Challange Details

                    $deviceInformation = $this->user_model->userdeviceinformation('userid', $challange->challenge_from);
                    $challenge_user = $this->user_model->checkfield('user_Id', $winner);
                    $winnerusername = $challenge_user->username;
                    if (isset($challenge_user->image)) {
                        $winnerimage = base_url() . "upload/profile/thumbnail/" . $challenge_user->image;
                    } else {
                        $winnerimage = '';
                    }

                    if ($_POST['winnerid'] == $challange->challenge_to) {
                        $message = "@" . $winnerusername . " won the challenge";
                        $pushmessage = 'You won your challenge with @' . $winnerusername;
                    } else {
                        $message = "@" . $winnerusername . " lost the challenge";
                        $pushmessage = 'You lost your challenge with @' . $winnerusername;
                    }

                    //Auto Update
                    $userWinner = $this->user_model->getOneUser($winner);
                    $userWinner->username;

                    $userLooser = $this->user_model->getOneUser($looser);
                    $userLooser->username;

                    $arr_status['status_id'] = '';
                    $arr_status['parentStatusid'] = 0;
                    $arr_status['status'] = '<un>@' . $userWinner->username . '</un> won a challenge against <un>@' . $userLooser->username . '</un>';
                    $arr_status['userid'] = $winner;
                    $arr_status['status_image'] = '';
                    $arr_status['usermentioned'] = $userWinner->username . ',' . $userLooser->username;
                    $arr_status['shareFrom'] = 0;
                    $arr_status['share'] = 0;
                    $arr_status['active'] = 1;
                    $arr_status['isCheckInUserCoinsId'] = '';
                    $arr_status['createdDate'] = date('YmdHis');
                    $arr_status['modifiedDate'] = date('YmdHis');
                    $status_id = $this->user_model->saveUserStatus($arr_status);
                    //End Auto Update


                    /* Start Notification  */
                    $arr_notice['notification_id'] = '';
                    $arr_notice['actionFrom'] = $winner;
                    $arr_notice['actionTo'] = $looser;
                    $arr_notice['action'] = 'CW';
                    $arr_notice['actionString'] = $winnerusername . ' has won the challange';
                    $arr_notice['message'] = '';
                    $arr_notice['statusid'] = '';
                    $arr_notice['challangeid'] = $challengeId;
                    $arr_notice['active'] = '1';
                    $arr_notice['createdDate'] = date('YmdHis');

                    $notice_id = $this->notification_model->savenotification($arr_notice);
                    // aws sns code
                    $deviceInfo = $this->user_model->getdeviceToken($looser);
                    if (count($deviceInfo) > 0) {
                        foreach ($deviceInfo as $device) {
                            $deviceToken = $device->key;
                            $deviceType = $device->deviceTypeID;
                            $title = 'My Test Message';
                            $sound = 'default';
                            $msgpayload = json_encode(array(
                                'aps' => array(
                                    "alert" => $pushmessage,
                                    "challengeId" => $challengeId,
                                    "userid" => isset($winner) ? $winner : '',
                                    "Coins" => isset($challangedCoins) ? $challangedCoins : '',
                                    "username" => isset($winnerusername) ? $winnerusername : '',
                                    "userimage" => $winnerimage,
                                    "game_id" => '',
                                    'type' => 'challenge',
                                    "sound" => $sound
                            )));
                            $message = json_encode(array(
                                'default' => $title,
                                'APNS_SANDBOX' => $msgpayload
                            ));
                            //$message = $pushmessage;

                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                        }
                    }

                    // end

                    /* Responce Data */
                    $challange = array
                        (
                        "challengeId" => $challengeId,
                        "userid" => isset($winner) ? $winner : '',
                        //"score"=>$score,
                        "Coins" => isset($challangedCoins) ? $challangedCoins : '',
                        "username" => isset($winnerusername) ? $winnerusername : '',
                        "userimage" => $winnerimage
                    );
                    $statusMessage = " success. " . $winnerusername . " has won the game";
                } else {
                    $sucess = 0;
                    $statusMessage = "Error occoured. Data Not Found";
                }
            } else {
                $sucess = 0;
                $statusMessage = "Error occoured. User not found";
            }
        } else {
            $sucess = 0;
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }
        if ($success == 1) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    "challenge" => $challange
                )
            );
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "Error",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function emailConfig() {

        $this->load->library('email');   //// LOAD LIBRARY

        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'auth.smtp.1and1.co.uk';
        $config['smtp_port'] = '587';
        $config['smtp_timeout'] = '7';
        $config['smtp_user'] = 'support@hurree.co';
        $config['smtp_pass'] = 'hurree123';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html'; // or html

        $this->email->initialize($config);
    }

    public function businessMapUsers() {

        $userhash = $this->input->post('userHash');
        if (isset($userhash) && $userhash != '') {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $users = $this->user_model->businessUsersMap();
                $userdata = array();
                foreach ($users as $user) {

                    $userdata[] = array(
                        "userid" => $user->user_Id,
                        "name" => isset($user->name) ? $user->name : '',
                        "username" => $user->username,
                        "businessname" => isset($user->businessname) ? $user->businessname : '',
                        "email" => $user->email,
                        "userimage" => base_url() . 'upload/profile/thumbnail/' . $user->image,
                        "branchid" => isset($user->branch_id) ? $user->branch_id : '',
                        "address" => isset($user->address) ? $user->address : '',
                        "latitude" => empty($user->latitude) ? "0" : $user->latitude,
                        "longitude" => empty($user->longitude) ? "0" : $user->longitude
                    );
                }

                if (count($users) > 0) {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array
                            (
                            "status" => "Success",
                            "statusmessage" => "success",
                            "businessUsers" => $userdata
                        )
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "Error",
                            "statusmessage" => "Data not found",
                            "businessUsers" => NULL
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "Error",
                        "statusmessage" => "Error occoured. User not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "Error",
                    "statusmessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    public function conversation() {
        $userhash = $this->input->post('userHash');
        $status_id = $this->input->post('statusid');
        $page = $this->input->post('page');
        if (isset($userhash) && $userhash != '' && isset($status_id) && $status_id != '' && isset($page)) {
            $this->load->model(array('user_model', 'status_model'));
            $checkStatusId = $this->status_model->checkStatus($status_id);
            if ($checkStatusId) {

                $action = 'timeline';
                $userdetail = $this->user_model->getuserHash($userhash);
                $loggedinId = $userdetail['userid'];
                if ($userdetail['status'] == 1) {

                    $arr_status['status_id'] = $status_id;

                    $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $loggedinId);


                    $arr_reply['parentStatusid'] = $status_id;


                    $replies = $this->user_model->getStatusDetails($arr_reply, '1', '', '', '', 'webservice', '', $loggedinId);
                    $check['userId'] = $userdetail['userid'];
                    $check['followUserId'] = $user_status->userid;
                    ;
                    $data = $this->user_model->checkfollow($check);
                    if (!empty($data)) {
                        $followed = 1;
                    } else {
                        $followed = 0;
                    }
                    $status = array(
                        'shareFromUserId' => isset($user_status->shareFromUserId) ? $user_status->shareFromUserId : '',
                        'status_id' => $user_status->status_id,
                        'status' => $user_status->status,
                        'userid' => $user_status->userid,
                        'originalPosterId' => $user_status->originalPosterId,
                        'status_image' => $user_status->status_image,
                        'originalMedia' => $user_status->originalMedia,
                        'thumbnail' => $user_status->thumbnail,
                        'url' => $user_status->url,
                        'createdDate' => $user_status->createdDate,
                        'username' => $user_status->username,
                        'usermentioned' => $user_status->usermentioned,
                        'isCheckInUserCoinsId' => $user_status->isCheckInUserCoinsId,
                        'checkinBusinessId' => $user_status->checkinBusinessId,
                        'checkinBusinessName' => $user_status->checkinBusinessName,
                        'name' => $user_status->name,
                        'header_image' => $user_status->header_image,
                        'userimage' => $user_status->userimage,
                        'like' => $user_status->like,
                        'shared' => $user_status->shared,
                        'shareFromUser' => isset($user_status->shareFromUser) ? $user_status->shareFromUser : '',
                        'totalLike' => $user_status->totalLike,
                        'shareCount' => $user_status->shareCount,
                        'followed' => $followed,
                        'reply' => $replies,
                        'quoteStatus'=>$user_status->quoteStatus
                    );
                    $firstname = isset($userdetail['firstname']) ? $userdetail['firstname'] : '';
                    $lastname = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                    $response = array(
                        "status" => "success",
                        "statusMessage" => "Success",
                        "data" => $status
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "Error",
                            "statusmessage" => "Error occoured. User not found"
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "Error",
                        "statusmessage" => "Error occoured. Status not found"
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "Error",
                    "statusmessage" => "Error occoured. Status not found"
                )
            );
        }
        echo json_encode($response);
        exit;
    }

public function statusDetails($status_id) {

            $this->load->model(array('user_model', 'status_model'));
            $checkStatusId = $this->status_model->checkStatus($status_id);
            if ($checkStatusId) {

                $action = 'timeline';
                $userdetail = $this->user_model->getuserHash($userhash);
                $loggedinId = $userdetail['userid'];
                if ($userdetail['status'] == 1) {


                    $records = $this->status_model->getWebServiceTimeline($userdetail['userid'], '', '', 1, $action);


                    if ($page == '') {
                        $page = 0;
                    }
                    $limit = 50;

                    $data['records'] = count($records);
                    $config['base_url'] = base_url() . 'index.php/userservice/conversation/';
                    $config['total_rows'] = $data['records'];
                    $config['per_page'] = '50';
                    $config['uri_segment'] = 3;

                    $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                    $data['page'] = $page;

                    //$timeline = $this->status_model->getWebServiceTimeline($userdetail['userid'], $page, $limit, '', $action);  //// Get Record
                    $arr_status['status_id'] = $status_id;

                    $user_status = $this->user_model->getStatusDetails($arr_status, '', '', '', '', 'webservice', '', $loggedinId);


                    $arr_reply['parentStatusid'] = $status_id;


                    $replies = $this->user_model->getStatusDetails($arr_reply, '1', '', '', '', 'webservice', '', $loggedinId);
                    $check['userId'] = $userdetail['userid'];
                    $check['followUserId'] = $user_status->userid;
                    ;
                    $data = $this->user_model->checkfollow($check);
                    if (!empty($data)) {
                        $followed = 1;
                    } else {
                        $followed = 0;
                    }
                    $status = array(
                        'shareFromUserId' => isset($user_status->shareFromUserId) ? $user_status->shareFromUserId : '',
                        'status_id' => $user_status->status_id,
                        'status' => $user_status->status,
                        'userid' => $user_status->userid,
                        'originalPosterId' => $user_status->originalPosterId,
                        'status_image' => $user_status->status_image,
                        'originalMedia' => $user_status->originalMedia,
                        'thumbnail' => $user_status->thumbnail,
                        'url' => $user_status->url,
                        'createdDate' => $user_status->createdDate,
                        'username' => $user_status->username,
                        'usermentioned' => $user_status->usermentioned,
                        'isCheckInUserCoinsId' => $user_status->isCheckInUserCoinsId,
                        'checkinBusinessId' => $user_status->checkinBusinessId,
                        'checkinBusinessName' => $user_status->checkinBusinessName,
                        'name' => $user_status->name,
                        'header_image' => $user_status->header_image,
                        'userimage' => $user_status->userimage,
                        'like' => $user_status->like,
                        'shared' => $user_status->shared,
                        'shareFromUser' => isset($user_status->shareFromUser) ? $user_status->shareFromUser : '',
                        'totalLike' => $user_status->totalLike,
                        'shareCount' => $user_status->shareCount,
                        'followed' => $followed,
                        'reply' => $replies,
                        'quoteStatus'=>$user_status->quoteStatus
                    );
                    $firstname = isset($userdetail['firstname']) ? $userdetail['firstname'] : '';
                    $lastname = isset($userdetail['lastname']) ? $userdetail['lastname'] : '';
                    $response = array(
                        "status" => "success",
                        "statusMessage" => "Success",
                        "data" => $status
                    );
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "Error",
                            "statusmessage" => "Error occoured. User not found"
                        )
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "Error",
                        "statusmessage" => "Error occoured. Status not found"
                    )
                );
            }

        echo json_encode($response);
        exit;
    }
    //////////////////// Created By Sarvesh Search user by usershash 06/11/2015 //////
    public function getStatus() {
        $userhash = $this->input->post('userHash');
        $search = $this->input->post('search');
        $page = $this->input->post('page');
        if (isset($userhash) && $userhash != '' && isset($search) && $search != '') {
            $this->load->model(array('user_model', 'status_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            $loggedinId = $userdetail['userid'];
            if ($userdetail['status'] == 1) {

                $searchs = $this->user_model->search_userhash($search);
                $userdetails = array();
                if (count($search) > 0) {
                    if ($page == '') {
                        $page = 0;
                    }
                    $limit = 50;

                    $data['records'] = count($searchs);
                    $config['base_url'] = base_url() . 'index.php/userservice/getStatus/';
                    $config['total_rows'] = $data['records'];
                    $config['per_page'] = '50';
                    $config['uri_segment'] = 3;

                    $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                    $searchs = $this->user_model->search_userhash($search, $page, $limit);
                    //print_r($searchs); exit;
                    $data['page'] = $page;
                    $i = 0;

                    foreach ($searchs as $search) {
                         if($search->thumbnailInfo == '0' || $search->thumbnailInfo == '' || $search->thumbnailInfo == '{}'){
                                $search->url = new ArrayObject();
                              }
                              else{

                              $thumbnailInfo = json_decode($search->thumbnailInfo);

                              $newArray = array();
                             //echo $thumbnailInfo->description; exit;
                              $newArray['image'] =  $thumbnailInfo->image;
                              $newArray['url'] =  $thumbnailInfo->url;
                              $newArray['title'] =  $thumbnailInfo->title;
                              $newArray['description'] =  $thumbnailInfo->description;
                              $search->url = (object)$newArray;

                              }

                              unset($search->thumbnailInfo);
                        if($search->quoteFromStatusId!=0){

                $arr_status1['status_id'] = $search->quoteFromStatusId;
                $quoteStatus = $this->status_model->getQuoteStatusDetails($arr_status1, $row = 1, '', '', 1, 'webservice',$loggedinId);
                $quoteStatus1 = array();
                foreach ($quoteStatus as $data){
                $quoteStatus1['shareFromUserId']= $data->shareFromUserId;
                $quoteStatus1["media_thumb"]= $data->media_thumb;
                $quoteStatus1["status_id"]= $data->status_id;
                $quoteStatus1["shareByUserId"] = $data->shareByUserId;
                $quoteStatus1["status"]= $data->status;
                $quoteStatus1["usermentioned"]= $data->usermentioned;
                $quoteStatus1["userid"]= $data->userid;
                $quoteStatus1["originalPosterId"]= $data->originalPosterId;
                $quoteStatus1["status_image"]= $data->status_image;
                $quoteStatus1["createdDate"]= $data->createdDate;
                $quoteStatus1["usertype"]= $data->usertype;
                $quoteStatus1["businessName"]= $data->businessName;
                $quoteStatus1["username"]= $data->username;
                $quoteStatus1["name"]= $data->name;
                $quoteStatus1["header_image"]= $data->header_image;
                $quoteStatus1["userimage"]= $data->userimage;
                $quoteStatus1["originalMedia"]= $data->originalMedia;
                $quoteStatus1["thumbnail"]= $data->thumbnail;
                $quoteStatus1["originalPoster"]= $data->originalPoster;
                $quoteStatus1["shareFromUser"]= $data->shareFromUser;
                $quoteStatus1["message"]= $data->message;
                $quoteStatus1["shared"]= $data->shared;
                $quoteStatus1["like"]= $data->like;
                $quoteStatus1["likeCount"]= $data->likeCount;
                $quoteStatus1["shareCount"]= $data->shareCount;
                $quoteStatus1["rplyCount"]= $data->rplyCount;
                $quoteStatus1["likedUsers"]= $data->likedUsers;
                $quoteStatus1["totalLike"]= $data->likeCount;

                }

            $quoteStatus1 = (object)$quoteStatus1;
                }else{
                  $quoteStatus1 = new ArrayObject();
                }
                 $search->quoteStatus = $quoteStatus1;
                        $userid = $search->userid;

                        $arr_login['user_Id'] = $userid;
                        $arr_login['active'] = 1;
                        $userLogin = $this->user_model->getOneUserDetails($arr_login, '*');


                        $search->status = isset($search->status) ? strip_tags($search->status) : '';
                        $search->status = preg_replace("/&#?[a-z0-9]+;/i", " ", $search->status);
                        $like['statusId'] = $search->status_id;
                        $like['userId'] = $loggedinId;
                        $like['active'] = 1;
                        $likestatus = $this->user_model->getlikestatus($like);
                        $totallikestatus = $this->status_model->gettotallikestatus($search->status_id);

                        if (count($likestatus) > 0) {
                            $search->like = "true";
                        } else {
                            $search->like = "false";
                        }
                        $search->totalLike = count($totallikestatus);
                        /* Get Status Shared By LoggedIn User  */
                        $arr_shares['statusId'] = $search->status_id;
                        $share_status = $this->status_model->getshareStatus($arr_shares);
                        $totalshare_status = $this->status_model->gettotalshareStatus($search->status_id);

                        if (count($share_status) > 0) {
                            if ($loggedinId == $share_status->userId) {
                                $search->shared = "true";
                                ini_set('display_errors', 1);
                                ini_set('display_startup_errors', 1);
                                error_reporting(E_ALL);
                            } else {
                                $search->shared = "false";
                            }
                        } else {
                            $search->shared = "false";    //$status->shared=0;
                        }

                        $search->shareCount = count($totalshare_status);
                        $status = $search->status;
                        $status_id = $search->status_id;


                        /* Check Status Image exits than Create Complete Path */
                        if ($search->status_image != '') {

                            // status is image or video code start
                            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
                            $ext = strtolower(pathinfo($search->status_image, PATHINFO_EXTENSION));

                            if (in_array($ext, $extionArray)) {
                                $originalMedia = base_url() . "upload/status_image/full/" . $search->status_image;
                                $thumbnail = '';
                            } else {
                                $originalMedia = base_url() . "upload/status_image/full/" . $search->status_image;
                                $thumbnail = base_url() . "upload/videoThumb/" . $search->media_thumb;
                            }

                            $search->originalMedia = $originalMedia;
                            $search->thumbnail = $thumbnail;
                            $search->status_image = base_url() . "upload/status_image/full/" . $search->status_image;

                            // end status is image or video  code
                        } else {
                            $search->status_image = '';
                            $search->originalMedia = '';
                            $search->thumbnail = '';
                        }

                        // Get Checked In Details
                        $checkinBusinessId = '';
                        $checkinBusinessName = '';
                        if ($search->isCheckInUserCoinsId != '0') {
                        	$arr_where['coins_id'] = $search->isCheckInUserCoinsId;
                        	$select = "businessid, branchid";
                        	$checkInDeatail = $this->user_model->getConintransactionDetails($arr_where, $select);

                        	if (count($checkInDeatail) > 0) {
                        		$checkinBusinessId = $checkInDeatail->businessid;

                        		$checkBusinessDetailsDetails = $this->user_model->getOneUser($checkInDeatail->businessid);
                        		$checkinBusinessName = $checkBusinessDetailsDetails->username;
                        	}
                        }

                        $firstname = isset($userLogin->firstname) ? $userLogin->firstname : '';
                        $lastname = isset($userLogin->lastname) ? $userLogin->lastname : '';
                        $arr_reply['parentStatusid'] = $status_id;
                        $replies = $this->user_model->getStatusDetails($arr_reply, $row = 1, '', '', 1, 'webservice', '', $loggedinId);
                        $userdetails[$i] = array(
                            'status_id' => $status_id,
                            'userid' => $userid,
                            'username' => $userLogin->username,
                            'name' => $firstname . ' ' . $lastname,
                            'userimage' => base_url() . "upload/profile/thumbnail/" . $userLogin->image,
                            'shareByUserId' => isset($search->shareByUserId) ? $search->shareByUserId : '',
                            'shareFromUserId' => isset($search->shareFromUserId) ? $search->shareFromUserId : '',
                            'createdDate' => $search->createdDate,
                            //'checkinBusinessId' => isset($search->checkinBusinessId) ? $search->checkinBusinessId : '',
                            //'checkinBusinessName' => isset($search->checkinBusinessName) ? $search->checkinBusinessName : '',
                        	'checkinBusinessId' => $checkinBusinessId,
                        	'checkinBusinessName' => $checkinBusinessName,
                            'status' => $status,
                            'url' => $search->url,
                            'status_image' => $search->status_image,
                            'originalMedia' => $search->originalMedia,
                            'thumbnail' => $search->thumbnail,
                            'usermentioned' => isset($search->usermentioned) ? $search->usermentioned : '',
                            'like' => $search->like,
                            'shared' => $search->shared,
                            'totalLike' => $search->totalLike,
                            'shareCount' => $search->shareCount,
                            'quoteStatus'=>$search->quoteStatus,
                            'replies' => $replies
                        );
                        $i++;
                    }


                    $response = array(
                        "status" => "success",
                        "data" => $userdetails
                    );
                } else {
                    $response = array(
                        "status" => "error",
                        "statusMessage" => "No record found"
                    );
                }
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. UserHash not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    //Created By Hassan Search Hash tag 17/11/2015
    public function search_hashtags() {

        $userhash = $this->input->post('userHash');
        $hashtag = $this->input->post('hashtag');
        if (isset($userhash) && $userhash != '' && isset($hashtag) && $hashtag != '') {
            $this->load->model('user_model');
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                if (strpos($hashtag, '#') !== false) {
                    $search = str_replace("#", "", $hashtag);
                    $searchs = $this->user_model->search_hashtags($search);
                    $hash = array();
                    foreach ($searchs as $hashtag) {
                        $hash[] = "#" . $hashtag->hashTag;
                    }
                    if (count($searchs) != 0) {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "#tag found",
                                "hashtags" => $hash
                            )
                        );
                    } else {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "error",
                                "statusMessage" => "#tag not found"
                            )
                        );
                    }
                } else {
                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "Enter vaild hash tag"
                        )
                    );
                }
            } else {

                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. UserHash not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }


        echo json_encode($response);
        exit;
    }

    public function beaconOffers() {
        $this->load->model(array('beacon_model', 'user_model'));
        $data['major'] = $this->input->post('major');
        $data['minor'] = $this->input->post('minor');
        if (isset($data['major']) && $data['major'] != '' && isset($data['minor']) && $data['minor'] != '') {
            $beacon = $this->beacon_model->getBeacons($data);

            if (count($beacon) > 0) {
                $offer = $this->beacon_model->getOffer($beacon->beaconId);

                if (count($offer) > 0) {

                    $age = $this->input->post('age');
                    $gender = $this->input->post('gender');

                    $beaconUser = $this->beacon_model->getBeaconUser($beacon->beaconId);
                    if (count($beaconUser) > 0) {
                        $businessid = $beaconUser->userId;

                        $user = $this->user_model->getOneUser($businessid);
                        $businessName = $user->businessName;

                        if (($age >= $offer->minAge && $age <= $offer->maxAge) && ($gender == $offer->gender || $offer->gender == 'All' || $offer->gender == 'all')) {

                            $status = 'success';
                            $statusMessage = 'Beacon offer exist';

                            $offerDetails = array(
                                'beconOfferId' => $offer->beconOfferId,
                                'beaconId' => $offer->beaconId,
                                'notificationMessagge' => $businessName . ' has an offer. ' . $offer->notificationMessagge,
                                'coins' => isset($offer->noofcoins) ? $offer->noofcoins : 0,
                                'minAge' => isset($offer->minAge) ? $offer->minAge : '',
                                'maxAge' => isset($offer->maxAge) ? $offer->maxAge : '',
                                'gender' => isset($offer->gender) ? $offer->gender : ''
                            );
                        } else {
                            $status = 'error';
                            $statusMessage = 'Offer is not valid for you!';
                            $offerDetails = array();
                        }
                    } else {
                        $status = 'error';
                        $statusMessage = 'This becon is not assigned to this user!';
                        $offerDetails = array();
                    }
                } else {
                    $status = 'error';
                    $statusMessage = 'Beacon offer not exist';
                    $offerDetails = array();
                }

                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => $status,
                        "statusMessage" => $statusMessage,
                        "BeaconOffer" => $offerDetails
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. Beacon not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    //Created by Hassan Ali 2-11-2015
    public function beaconCoinsExchange() {

        $userhash = $this->input->post('userHash');
        $beaconOfferId = $this->input->post('beaconOfferId');
        if (isset($userhash) && $userhash != '' && isset($beaconOfferId) && $beaconOfferId != '') {
            $this->load->model(array('beacon_model', 'user_model', 'score_model'));
            $userdetail = $this->user_model->getuserHash($userhash);
            if ($userdetail['status'] == 1) {
                $userId = $userdetail['userid'];

                $beaconOffer = $this->beacon_model->getOfferBeaconExchange($beaconOfferId);
                if (count($beaconOffer) > 0) {

                    $coins = $beaconOffer->noofcoins;
                    $beaconId = $beaconOffer->beaconId;
                    $beaconUser = $this->beacon_model->getBeaconUser($beaconId);
                    $businessid = $beaconUser->userId;

                    $arr_scan['userid'] = $userdetail['userid'];
                    $arr_scan['beaconOfferId'] = $beaconOfferId;
                    $arr_scan['isDelete'] = 0;
                    $orderby['orderby'] = 'createdDate';
                    $orderby['order'] = 'DESC';
                    $scanDetails = $this->beacon_model->getScannedBeacon($arr_scan, '', $orderby);  //// check this QR already scanned or not

                    if (count($scanDetails) > 0) {
                        $current_date = date('Y-m-d H:i:s');

                        $date1 = $scanDetails->createdDate;
                        $date2 = $current_date;

                        $start_date = new DateTime($date1);
                        $since_start = $start_date->diff(new DateTime($current_date));

                        $minutes = $since_start->days * 24 * 60;
                        $minutes += $since_start->h * 60;
                        $minutes += $since_start->i;
                    }
                    if (count($scanDetails) == 0 || $minutes > 30) {

                        $user = $this->user_model->getOneUser($businessid);

                        /* Get Business Coins */
                        $businessCoins = $this->score_model->getUserCoins($businessid);   //// Get Business Coins

                        if ((count($businessCoins) > 0 && $businessCoins->coins > $coins) || $user->usertype == 5) {    //// Check Business has enough coins or not
                            $success = 1;
                            /* Start Detect Business User Coins  */
                            $buss_coin['coins_id'] = '';
                            $buss_coin['userid'] = $businessid;
                            $buss_coin['coins'] = $coins;
                            $buss_coin['coins_type'] = 11;
                            $buss_coin['businessid'] = $businessid;
                            $buss_coin['actionType'] = 'sub';
                            $buss_coin['createdDate'] = date('YmdHis');
                            $this->score_model->insert($buss_coin);    //// Save Business Detect Details in userCoin

                            if ($user->usertype != 5 && $user->organizationId == 0) {
                                $bu_coin = $businessCoins->coins - $coins;
                                $buss_totalcoins['user_score_id'] = $businessCoins->user_score_id;
                                $buss_totalcoins['userid'] = $businessid;
                                $buss_totalcoins['coins'] = $bu_coin;
                                $buss_totalcoins['modifiedDate'] = date('YmdHis');
                                $this->score_model->saveuserTotalCoins($buss_totalcoins);   //// Update Business Total Coins
                            }
                            /* End Business User Coins  */

                            /* Insert Data into userCoins Table */
                            $arr_coin['coins_id'] = '';
                            $arr_coin['userid'] = $userdetail['userid'];
                            $arr_coin['coins'] = $coins;
                            $arr_coin['businessid'] = $businessid;
                            $arr_coin['coins_type'] = 11;
                            $arr_coin['game_id'] = '';
                            $arr_coin['actionType'] = 'add';
                            $arr_coin['createdDate'] = date('YmdHis');
                            $this->score_model->insert($arr_coin);   //// Save Consumer add Coins Details in userCoin

                            /* Start User Total Coins  */
                            $usercoins = $this->score_model->getUserCoins($userdetail['userid']);
                            $arr_coins = '';
                            if (count($usercoins) > 0) {

                                $user_coin = $usercoins->coins + $coins;
                                $arr_coins['user_score_id'] = $usercoins->user_score_id;
                                $arr_coins['userid'] = $userdetail['userid'];
                                $arr_coins['coins'] = $user_coin;
                                $arr_coins['modifiedDate'] = date('YmdHis');
                            } else {

                                $user_coin = $coins;
                                $arr_coins['user_score_id'] = '';
                                $arr_coins['userid'] = $userdetail['userid'];
                                $arr_coins['coins'] = $user_coin;
                                $arr_coins['createdDate'] = date('YmdHis');
                            }
                            $this->score_model->updateuserTotalCoins($arr_coins);   //// Update Consumer Total Coins

                            /* Save Scanned Beacon Details */
                            $arr_scanned['scanBeacon_id'] = '';
                            $arr_scanned['userid'] = $userdetail['userid'];
                            $arr_scanned['beaconOfferId'] = $beaconOfferId;
                            $arr_scanned['createdDate'] = date('YmdHis');
                            $arr_scanned['modifiedDate'] = date('YmdHis');
                            $this->beacon_model->saveScannedDetails($arr_scanned);

                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "success",
                                    "statusMessage" => "You just got " . $coins . " coins!"
                                )
                            );

                            /* End */
                        } else {
                            $response = array(
                                "c2dictionary" => true,
                                "data" => array(
                                    "status" => "error",
                                    "statusMessage" => "This business doesn't have enough coins"
                                )
                            );
                        }
                    } else {
                        $response = array(
                            "c2dictionary" => true,
                            "data" => array(
                                "status" => "success",
                                "statusMessage" => "You can't get coins again before 30 min, try again after sometime!"
                            )
                        );
                    }
                } else {

                    $response = array(
                        "c2dictionary" => true,
                        "data" => array(
                            "status" => "error",
                            "statusMessage" => "Error occoured. This Offer is not exist"
                        )
                    );
                }
            } else {

                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. UserHash not found"
                    )
                );
            }
        } else {
            $response = array(
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error Occured. Parameters not found or values cannot be blank."
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    /*
     * send coin from one user to another user  (shiwangi)
     */

    public function requestToSendCoins() {
        $success = 0;
        $userhash = $this->input->post('userHash'); // get userHash
        $noodCoins = $this->input->post('noofcoins'); // get no of coins
        $sendToUser = $this->input->post('sendToUser'); //// Get send username
        if (isset($userhash) && $userhash != '' && isset($noodCoins) && $noodCoins != '' && $noodCoins != ' ' && isset($sendToUser) && $sendToUser != '') {
            $this->load->model(array('user_model', 'score_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHashForShortInfo($userhash);            //// Get UserHash Details

            if ($userdetail['status'] == 1) {

                $arr_seconUser['username'] = $sendToUser;
                $arr_seconUser['active'] = 1;
                $secondUserDetails = $this->user_model->getOneUserDetails($arr_seconUser, '*');
                if (count($secondUserDetails) > 0) {
                    if ($secondUserDetails->user_Id != $userdetail['userid']) {

                                $data['userid'] = $userdetail['userid'];
                                $data['block_user_id'] = $secondUserDetails->user_Id;

                                $userBlock = $this->user_model->UserBlock($data,1);
                               if(count($userBlock) == 0){
                        if ($noodCoins >= 1) {
                            $userTotalCoins = $this->score_model->getUserCoins($userdetail['userid']);
                            if ($userTotalCoins->coins >= $noodCoins) {
                                $success = 1;
                                $arr_coins['transferCoinId'] = '';
                                $arr_coins['userFrom'] = $userdetail['userid'];
                                $arr_coins['userTo'] = $secondUserDetails->user_Id;
                                $arr_coins['coins'] = $noodCoins;
                                $arr_coins['isActive'] = 1;
                                $arr_coins['createdDate'] = date('YmdHis');
                                $this->user_model->saveUserTransferCoins($arr_coins);

                                $updateCoins = $userTotalCoins->coins - $noodCoins;


                                $update = array(
                                    'userid' => $userdetail['userid'],
                                    'coins' => $updateCoins
                                );
                                $this->score_model->update($update);
                                //Save Notification
                                $arr_notice['notification_id'] = '';
                                $arr_notice['actionFrom'] = $userdetail['userid'];
                                $arr_notice['actionTo'] = $secondUserDetails->user_Id;
                                $arr_notice['action'] = 'SC';
                                $arr_notice['actionString'] = 'Sent you coins';
                                $arr_notice['message'] = '';
                                $arr_notice['statusid'] = '';
                                $arr_notice['challangeid'] = '';
                                $arr_notice['active'] = '1';
                                $arr_notice['createdDate'] = date('YmdHis');
                                $notice_id = $this->notification_model->savenotification($arr_notice);
                                // send notification code start
                                $deviceInfo = $this->user_model->getdeviceToken($secondUserDetails->user_Id);
                                if (count($deviceInfo) > 0) {
                                    foreach ($deviceInfo as $device) {
                                        $deviceToken = $device->key;
                                        $deviceType = $device->deviceTypeID;
                                        $title = 'My Test Message';
                                        $sound = 'default';
                                        $msgpayload = json_encode(array(
                                            'aps' => array(
                                                "alert" => '@'. $userdetail['username'] . ' sent you coins!',
                                                'type' => 'coin',
                                                'sound' => $sound
                                        )));
                                        $message = json_encode(array(
                                            'default' => $title,
                                            'APNS_SANDBOX' => $msgpayload
                                        ));

                                        $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                        //echo $result; exit;
                                    }
                                }

                                // end


                                $success = 1;
                                $statusMessage = "Coins sent successfully";
                            } else {
                                $statusMessage = 'Error Occured. You do not have enough coins. ';
                            }
                        } else {
                            $statusMessage = 'Error Occured. Coins added should be more than zero.';
                        }
                    }
                        else {
                            $response = array(
                                "status" => "error",
                                "statusMessage" => "You have not access to send coins to this user."
                            );
                            }
                    } else {
                        $statusMessage = 'You can not send coins to your own profile.';
                    }
                } else {
                    $statusMessage = 'Error, Requested user not found';
                }
            } else {
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $statusMessage = "Error Occured. Parameters not found or values cannot be blank.";
        }
        $status = ($success == 0) ? 'error' : 'success';
        if ($success == 1) {
            $statusMessage = 'Coins sent successfully';
        }

        $response = array(
            "c2dictionary" => true,
            "data" => array(
                "status" => $status,
                "statusMessage" => $statusMessage
            )
        );
        echo json_encode($response);
        exit;
    }

    /**
     * Get list of send and received coins
     */
    public function userTransationCoins() {
        if ($this->input->post('userHash')) {
            $joinUserTable = "0";
            $sent = array();
            $recieve = array();

            // Get POST Value
            $userhash = $this->input->post('userHash');
            $this->load->model(array('games_model', 'user_model'));
            // Validate LoggedIn User
            $userdetail = $this->user_model->getuserHash($userhash);
            $user = $this->games_model->userCoins($userdetail['userid']);
            $usercoins = $user->coins;
            $totalCoins = count($usercoins > 0) ? $usercoins : '0';
            if ($userdetail['status'] == 1) {
                // Received Coins
                $where = " userFrom =" . $userdetail['userid'] . " AND isApproved = 0 AND isActive = 1 ";
                //$where.= " AND US.isDelete= 0".
                $select = "userTo as sendtoUser, transferCoinId, UTF.coins, UTF.createdDate,userFrom as sendFromUser, CONCAT('" . base_url() . "upload/profile/thumbnail/', image ) as userimage , US.username ";
                $joinUserTable = " as UTF RIGHT JOIN users as US on US.user_Id = UTF.userTo and US.isDelete = 0";

                $sent = $this->user_model->getTransferCoinsDetails($select . ", 'received' as type", $where, "1", $joinUserTable);

                // Send Coins
                $where = " userTo =" . $userdetail['userid'] . " AND isApproved = 0 AND isActive = 1";

                $joinUserTable = " as UTF RIGHT JOIN users as US on US.user_Id = UTF.userFrom and US.isDelete = 0";
                $recieve = $this->user_model->getTransferCoinsDetails($select . ", 'sent' as type", $where, "1", $joinUserTable);


                $response = array(
                    "status" => "sucess",
                    "statusMessage" => "",
                    "data" => array(
                        "sent" => $sent,
                        "received" => $recieve,
                        "totalCoins" => $totalCoins
                    )
                );
            } else {
                $response = array(
                    "c2dictionary" => true,
                    "data" => array(
                        "status" => "error",
                        "statusMessage" => "Error occoured. User not found."
                    )
                );
            }
        } else {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => "Error occoured. Parameters not found or values can not be blank."
                )
            );
        }

        echo json_encode($response);
        exit;
    }

    /* Decline coins request  */

    public function declineCoinsRequest() {

        $success = 0;
        $userhash = $this->input->post('userHash');    //// Get UserHash
        $transferCoinId = $this->input->post('transferCoinId');
        if (isset($userhash) && $userhash != '' && isset($transferCoinId) && $transferCoinId != '') {
            $this->load->model(array('user_model', 'score_model', 'notification_model'));
            $userdetail = $this->user_model->getuserHash($userhash);            //// Get UserHash Details

            if ($userdetail['status'] == 1) {


                $userid = $userdetail['userid'];

                $arr['transferCoinId'] = $transferCoinId;
                $arr['userid'] = $userid;
                $arr['isActive'] = 1;
                $arr['request'] = 'decline';

                $coinsRequest = $this->user_model->getCoinsRequest($arr);

                if (count($coinsRequest) > 0) {


                    $arr_coins['transferCoinId'] = $transferCoinId;
                    $arr_coins['isActive'] = 0;
                    $result = $this->user_model->saveUserTransferCoins($arr_coins);
                    $userTotalCoins = $this->score_model->getUserCoins($coinsRequest->userFrom);
                    $updateCoins = $userTotalCoins->coins + $coinsRequest->coins;


                    $update = array(
                        'userid' => $coinsRequest->userFrom,
                        'coins' => $updateCoins
                    );
                    $this->score_model->update($update);

                    //Save Notification
                    $arr_notice['notification_id'] = '';
                    $arr_notice['actionFrom'] = $userdetail['userid'];
                    $arr_notice['actionTo'] = $coinsRequest->userFrom;
                    $arr_notice['action'] = 'DC';
                    $arr_notice['actionString'] = 'Declined coins';
                    $arr_notice['message'] = '';
                    $arr_notice['statusid'] = '';
                    $arr_notice['challangeid'] = '';
                    $arr_notice['active'] = '1';
                    $arr_notice['createdDate'] = date('YmdHis');
                    $notice_id = $this->notification_model->savenotification($arr_notice);

                     if($userdetail['userid']!=$coinsRequest->userFrom){
                    // send notification code start
                    $deviceInfo = $this->user_model->getdeviceToken($coinsRequest->userFrom);
                    if (count($deviceInfo) > 0) {
                        foreach ($deviceInfo as $device) {
                            $deviceToken = $device->key;
                            $deviceType = $device->deviceTypeID;
                            $title = 'My Test Message';
                            $sound = 'default';
                            $msgpayload = json_encode(array(
                                'aps' => array(
                                    "alert" => '@'. $userdetail['username'] . ' declined your coins!',
                                    'type' => 'coin',
                                    'sound' => $sound
                            )));
                            $message = json_encode(array(
                                'default' => $title,
                                'APNS_SANDBOX' => $msgpayload
                            ));

                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                        }
                    }

                    // end
                   }
                    if ($result) {
                        $success = 1;
                        $statusMessage = "You declined coins";
                    } else {
                        $statusMessage = "Error Occured. We are not able to complete your request.";
                    }
                } else {
                    $statusMessage = "Error Occured. This coins request not found";
                }
            } else {
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }


        echo json_encode($response);
        exit;
    }

    /* Accept coins request  */

    public function acceptCoinsRequest() {

        $success = 0;
        $userhash = $this->input->post('userHash'); // Get UserHash
        $transferCoinId = $this->input->post('transferCoinId');
        if (isset($userhash) && $userhash != '' && isset($transferCoinId) && $transferCoinId != '') {
            $this->load->model(array('user_model', 'score_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $userid = $userdetail['userid'];

                $arr['transferCoinId'] = $transferCoinId;
                $arr['userid'] = $userid;
                $arr['isActive'] = 1;

                $arr['request'] = 'accept';
                $coinsRequest = $this->user_model->getCoinsRequest($arr);

                if (count($coinsRequest) > 0) {
                    if ($coinsRequest->isApproved != 1) {

                        $arr_coins['transferCoinId'] = $transferCoinId;
                        $arr_coins['isApproved'] = 1;
                        $this->user_model->saveUserTransferCoins($arr_coins);

                        //Insert transaction into userCoins table of Accepter
                        $date = date('YmdHis');
                        $requestCoins = $coinsRequest->coins;

                        $arr_accept_coin['coins_id'] = '';
                        $arr_accept_coin['userid'] = $userid;
                        $arr_accept_coin['coins'] = $requestCoins;
                        $arr_accept_coin['coins_type'] = 12;
                        $arr_accept_coin['game_id'] = '';
                        $arr_accept_coin['actionType'] = 'add';
                        $arr_accept_coin['createdDate'] = $date;
                        $this->score_model->insert($arr_accept_coin);   //// Insert Accept Coins in userCoin
                        //Update total coins of Accepter
                        $accepterCoins = $this->score_model->getUserCoins($userid); //// Get Accepter Total Coins
                        if (count($accepterCoins) > 0) {
                            $accepterTotalCoins = $accepterCoins->coins;
                            $accepterNewCoins = $accepterTotalCoins + $requestCoins;

                            $arr_accepter_total['user_score_id'] = $accepterCoins->user_score_id;
                            $arr_accepter_total['modifiedDate'] = $date;
                        } else {
                            $accepterNewCoins = $requestCoins;
                            $arr_accepter_total['user_score_id'] = '';
                            $arr_accepter_total['createdDate'] = $date;
                        }

                        $arr_accepter_total['userid'] = $userid;
                        $arr_accepter_total['coins'] = $accepterNewCoins;
                        $this->score_model->saveuserTotalCoins($arr_accepter_total);   ///// Update Accepter Total Coins
                        //Insert transaction into userCoins table of Sender
                        $arr_send_coin['coins_id'] = '';
                        $arr_send_coin['userid'] = $coinsRequest->userFrom;
                        $arr_send_coin['coins'] = $requestCoins;
                        $arr_send_coin['coins_type'] = 12;
                        $arr_send_coin['game_id'] = '';
                        $arr_send_coin['actionType'] = 'sub';
                        $arr_send_coin['createdDate'] = $date;
                        $this->score_model->insert($arr_send_coin);   //// Insert Accept Coins in userCoin
                        //Update total coins of Sender
                        $senderCoins = $this->score_model->getUserCoins($coinsRequest->userFrom); //// Get Sender Total Coins

                        if (count($senderCoins) > 0) {
                            $senderTotalCoins = $senderCoins->coins;
                            $arr_sender_total['user_score_id'] = $senderCoins->user_score_id;
                            $arr_sender_total['modifiedDate'] = $date;
                        } else {
                            $senderNewCoins = $requestCoins;
                            $arr_sender_total['user_score_id'] = '';
                            $arr_sender_total['createdDate'] = $date;
                        }


                        $arr_sender_total['userid'] = $coinsRequest->userFrom;
                        $arr_sender_total['coins'] = $senderCoins->coins;
                        $result = $this->score_model->saveuserTotalCoins($arr_sender_total);   ///// Update Sender Total Coins
                        if ($result) {
                            $success = 1;
                            $statusMessage = "You declined coins";
                        } else {
                            $statusMessage = "Error Occured. We are not able to complete your request.";
                        }
                        $success = 1;
                        $statusMessage = "You accepted " . $requestCoins . " coins!";
                    } else {
                        $statusMessage = "Error Occured. You already accepted";
                    }
                } else {
                    $statusMessage = "Error Occured. This coins request not found";
                }
            } else {
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }


        echo json_encode($response);
        exit;
    }

    /* Search refernece list for latest 4 users and hashtags  */

    public function searchTrendingList() {

        $userhash = $this->input->post('userHash'); // Get UserHash
        if (isset($userhash) && $userhash != '') {
            $success = 1;
            $this->load->model(array('user_model', 'status_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                $latestUsers = $this->user_model->getLatestUsers($userhash); // get latest 4 users
                if (count($latestUsers) > 0) {
                    foreach ($latestUsers as $latestUser) {
                        $latestUser->firstname = isset($latestUser->firstname) ? $latestUser->firstname : '';
                        $latestUser->lastname = isset($latestUser->lastname) ? $latestUser->lastname : '';
                    }
                    $latestUsers = $latestUsers;
                } else {
                    $latestUsers = array();
                }
                $trendingHashtags = $this->user_model->getTrendingHashtags($userhash); // get latest 4 hashtags
                if (count($trendingHashtags) > 0) {
                    foreach($trendingHashtags as $tags){
                          $tags->hashTag = isset($tags->hashTag) ? strip_tags($tags->hashTag) : '';
                          $tags->hashTag = preg_replace("/&#?[a-z0-9]+;/i", " ", $tags->hashTag);
                        //$tags->hashtag = hashTag
                    }
                    $trendingHashtags = $trendingHashtags;
                } else {
                    $trendingHashtags = array();
                }
            } else {
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array
                (
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "latestUsers" => $latestUsers,
                    'trendingHashtags' => $trendingHashtags
                )
            );
        }


        echo json_encode($response);
        exit;
    }

    //for getting the specific status's liked users list

    public function getLikedUsersList() {

        $userhash = $this->input->post('userHash'); // Get UserHash
        $statusId = $this->input->post('statusId'); // Get StatusId

        if (isset($userhash) && $userhash != '' && isset($statusId) && $statusId != '') {

            $this->load->model(array('user_model', 'status_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail[
                    'status'] == 1) {
                // check statusId

                $checkStatusId = $this->status_model->checkStatus($statusId);

                if ($checkStatusId) {
                    $success = 1;
                    $userList = $this->status_model->gettotallikestatus($statusId);
                    if (count($userList) > 0) {
                        foreach ($userList as $likedUserid) {

                            $userIds[] = $likedUserid->userId;
                        }

//                        if (in_array($userdetail['userid'], $userIds)) {
//                            $key = array_search($userdetail['userid'], $userIds);
//                            unset($userIds[$key]);
//                        } else {
//                            $userIds = $userIds;
//                        }

                        if (count($userIds) > 0) {
                            $users = $this->user_model->getalluserDeatils($userIds, $userdetail['userid']);
                        } else {
                            $success = 0;
                            $statusMessage = "No List Found";
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "No List Found";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Status Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
            "c2dictionary" => true,
            "data" => array(
            "status" => "error",
            "statusMessage" => $statusMessage
            )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "likedUser" => $users,
                )
            );
        }
        echo json_encode($response);
        exit;
    }

//for getting the report list for status

    public function getReportOptions() {

        $userhash = $this->input->post('userHash'); // Get UserHash


        if (isset($userhash) && $userhash != '') {
            $success = 1;
            $this->load->model(array('user_model', 'report_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $reportOptions = $this->report_model->getReport();
                if (count($reportOptions) > 0) {

                    $reportOptions = $reportOptions;
                } else {
                    $reportOptions = array();
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "reportOptions" => $reportOptions,
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // save report for status

    public function saveStatusReport() {

        $userhash = $this->input->post('userHash'); // Get UserHash
        $statusId = $this->input->post('statusId'); // Get statusId
        $reportOptionId = $this->input->post('reportOptionId'); // Get reportOptionId
        $originalPosterId = $this->input->post('originalPosterId'); // Get reportOptionId
        $message = $this->input->post('message'); // Get statusId


        if (isset($userhash) && $userhash != '' && isset($originalPosterId) && $originalPosterId != '' && isset($statusId) && $statusId != '' && isset($reportOptionId) && $reportOptionId != '') {
            $success = 1;
            $this->load->model(array('user_model', 'report_model', 'status_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                if ($userdetail['userid'] != $originalPosterId) {

                    // check statusId

                    $checkStatusId = $this->status_model->checkStatus($statusId);

                    if ($checkStatusId) {

                        // check report type is exists or not

                        $checkReportId = $this->report_model->checkReportId($reportOptionId);

                        if ($checkReportId) {
                            // check orinaluserid  is exists or not

                            $checkOrignalPoster = $this->user_model->getOneUser($originalPosterId);
                            if (count($checkOrignalPoster) > 0) {
                                $arr_param['reportUserId'] = $userdetail['userid'];
                                $arr_param['statusId'] = $statusId;
                                $arr_param['reportUserId'] = $originalPosterId;

                                // check report has submitted or not earlier

                                $isReported = $this->report_model->checkReported($arr_param);
                                if ($isReported) {
                                    $success = 0;
                                    $statusMessage = "You have already submitted report for this status";
                                } else {
                                    $success = 1;
                                    $message = isset($message) ? $message : '';
                                    $date = date('Y-m-d H:i:s');
                                    $insert = array(
                                        'id' => '',
                                        'userid' => $userdetail['userid'], // logged in userid
                                        'reportUserId' => $originalPosterId, // orignal poster id
                                        'statusId' => $statusId,
                                        'reportType' => $reportOptionId,
                                        'message' => $message,
                                        'isDelete' => '0',
                                        'createdDate' => $date
                                    );

                                    $insertId = $this->status_model->insert_report($insert);
                                    $statusMessage = "Report has submitted Sucessfully";
                                }
                            } else {
                                $success = 0;
                                $statusMessage = "Second userid not found";
                            }
                        } else {
                            $success = 0;
                            $statusMessage = "Report option id does not exists";
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "Status id does not exists";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "You can not submit report for own status";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // aws push notification
    public function amazonSns($deviceToken, $message, $deviceType) {

        $this->load->library('Aws_sdk');
        $Aws_sdk = new Aws_sdk();
        if ($deviceType == 'ios') {
            $iOS_AppArn = "arn:aws:sns:us-west-2:831947047245:app/APNS_SANDBOX/Hurree";

            $endpoint = $Aws_sdk->generateEndpoint($deviceToken, $iOS_AppArn);
            $result = $Aws_sdk->SendPushNotification($message, $endpoint, $deviceToken);
            return $result;
        }
    }

// end amazon sns code
// logout service for delete user's device token

    public function deleteDeviceToken() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $venderIdentifier = $this->input->post('venderIdentifier'); // Get venderIdentifier

        if (isset($userhash) && $userhash != '' && isset($venderIdentifier) && $venderIdentifier != '') {
            $success = 1;
            $this->load->model(array('user_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                $checkvenderIdentifier = $this->user_model->checkVendorIdentifier($venderIdentifier);
                if (count($checkvenderIdentifier) > 0) {
                    $userid = $userdetail['userid'];
                    $result = $this->user_model->deleteDeviceToken($userid, $venderIdentifier);
                    if ($result) {
                        $success = 1;
                        $statusMessage = "Device token has deleted successfully";
                    } else {
                        $success = 0;
                        $statusMessage = "Operation Not Performed";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Vender Identifier Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

// send offer email

    function emailOffer() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $url = $this->input->post('url'); // Get venderIdentifier
        $BusinessUsername = $this->input->post('businessUsername');
        $offerId = $this->input->post('offerId'); // campaign id
        $notification = $this->input->post('notification');


        if (isset($userhash) && $userhash != '' && isset($offerId) && $offerId != '' && isset($url) && $url != '' && isset($BusinessUsername) && $BusinessUsername != '' && isset($notification) && $notification != '') {
            $this->load->model(array('user_model', 'email_model', 'offer_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                $result = $this->offer_model->saveEmailStatus($userdetail['userid'], $offerId);

                if ($result == 1 ) {
                    $username = $userdetail['username'];
                    $email = $userdetail['email'];
                    $success = 1;
                    //// SEND  EMAIL START
                    $this->emailConfig();   //Get configuration of email
                    //// GET EMAIL FROM DATABASE

                    $email_template = $this->email_model->getoneemail('get_offer');

                    //// MESSAGE OF EMAIL
                    $messages = $email_template->message;


                    $hurree_image = base_url() . 'assets/template/frontend/img/redeem_success.png';

                    //// replace strings from message
                    $messages = str_replace('{Username}', ucfirst($username), $messages);
                    $messages = str_replace('{Url}', $url, $messages);
                    $messages = str_replace('{BusinessUsername}', ucfirst($BusinessUsername), $messages);
                    $messages = str_replace('{NotificationText}', $notification, $messages);
                    $messages = str_replace('{Hurree_Image}', $hurree_image, $messages);

                    //// FROM EMAIL
                    $this->email->from($email_template->from_email, 'Hurree');
                    $this->email->to($email);
                    $this->email->subject($email_template->subject);
                    $this->email->message($messages);
                    $sent = $this->email->send();

                    ////  EMAIL SEND
                    if ($sent) {


                        $success = 1;
                        $statusMessage = "Email Sent Sucessfully";
                    } else {
                        $success = 1;
                        $statusMessage = "Error occoured during email sending process";
                    }
                } else {
                    $success = 1;
                    $statusMessage = "Email Sent Alreday";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // send push notification when only 10 min remain to get offer

    public function sendOfferPushNotification() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        if (isset($userhash) && $userhash != '') {
            $this->load->model(array('user_model', 'offer_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success = 1;
                //code for aws push notification
                $deviceInfo = $this->user_model->getdeviceToken($userdetail['userid']);
                if (count($deviceInfo) > 0) {
                    foreach ($deviceInfo as $device) {


                        $deviceToken = $device->key;
                        $deviceType = $device->deviceTypeID;
                        $title = 'My Test Message';
                        $sound = 'default';
                        $msgpayload = json_encode(array(
                            'aps' => array(
                                'alert' => 'You have only 10 min to grab this amazing offer. Hurry!!!',
                                'sound' => $sound,
                        )));


                        $message = json_encode(array(
                            'default' => $title,
                            'APNS_SANDBOX' => $msgpayload
                        ));


                        $result = $this->amazonSns($deviceToken, $message, $deviceType);
                    }
                }

                // end
                $success = 1;
                $statusMessage = "Notification Has Sent";
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    //redeem offer via 4 digit code

    function redeemOfferViaCode() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $offerId = $this->input->post('offerId'); // Get offerId
        $code = $this->input->post('code'); // Get offerId

        if (isset($userhash) && $userhash != '' && isset($offerId) && $offerId != '' && isset($code) && $code != '') {
            $this->load->model(array('user_model', 'offer_model', 'score_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success = 1;
                // check offer id exists or not

                $checkoffer = $this->offer_model->checkOffer($offerId);
                if ($checkoffer) {

                    // check this already redeemed or not
                    $redeemedOffer = $this->offer_model->redeemedOffer($offerId, $userdetail['userid']);
                    if ($redeemedOffer == 0) {
                        // get offer
                        $offerResult = $this->offer_model->getOffer($offerId, $code);

                        if ($offerResult) {
                            if($offerResult->availability > 0){
                            $businessUserDetails = $this->user_model->getBusinessName($offerResult->user_id);
                            if ($offerResult->discount_percentage == 0) {
                                $type = 'coins';
                                $value = $offerResult->coins;
                                $message = 'You got '.$offerResult->coins.' Hurree Coins from @' . ucfirst($businessUserDetails->businessName);
                            } else {
                                $type = 'discount';
                                $value = $offerResult->discount_percentage;
                                $message = 'You got ' . $value . ' % discount and '.$offerResult->coins.' Hurree Coins from @' . ucfirst($businessUserDetails->businessName) . '. ' . "\n\n" . 'Show this to the cashier to get your discount!';
                            }

                            $arr = array(
                                'userid' => $userdetail['userid'],
                                'offerId' => $offerId,
                                'code' => $code,
                                'offerOwnerId' => $offerResult->user_id,
                                $type => $value,
                                'redeemType' => 3,
                                //'businessId' => $offerResult->businessId,
                                'active' => 1,
                                'isDelete' => 0
                            );

                            $result = $this->offer_model->redeemOffer($arr); // save redeem offer details

                            $userTotalCoins = $this->score_model->getUserCoins($userdetail['userid']);
                            $updateCoins = $userTotalCoins->coins + $offerResult->coins; // static value, add 200 more coins in total coins
                            $update = array(
                                'userid' => $userdetail['userid'],
                                'coins' => $updateCoins
                            );
                            $this->score_model->update($update); // update user total coins
                            $insert = array(
                                'userid' => $userdetail['userid'],
                                'coins' => $offerResult->coins,
                                'actionType' => 'add',
                                'coins_type' => 13,
                                'businessid' => $offerResult->businessId,
                                'createdDate' => date('Y-m-d h:i:s')
                            );
                            $this->score_model->insert($insert); // update user  coins
                            if ($result) {
                                // decrease number of Availability users
                                if ($offerResult->availability > 0) {
                                    $updatedCount = $offerResult->availability - 1;
                                } else {
                                    $updatedCount = $offerResult->availability;
                                }
                                $this->offer_model->updateAvailability($offerId, $updatedCount);
                                $this->offer_model->deleteOffer($offerId);
                                $this->offer_model->deleteOfferfromNoti($offerId,$type = 'publicOffer');                                // code for upadte user offers coins in businessConsumerRedeemCoins table
                                $data['businessUserId'] = $offerResult->user_id;
                                $data['consumerId'] = $userdetail['userid'];
                                $coins = $offerResult->coins;
                                $this->user_model->OfferCoins($data,$coins);
                                $success = 1;
                                // email code

                                $this->offer_model->redeemSuccessEmailtoConsumer($offerResult->notification, $userdetail['username'], $userdetail['email']);
                                $arr['redeemedUsername'] = $userdetail['username'];
                                $arr['campaignsOwnerUsername'] = $businessUserDetails->username;
                                $arr['campaignsOwnerEmail'] = $businessUserDetails->email;
                                $this->offer_model->redeemSuccessEmailtoBusinessuser($arr);

                                // end email code


                                $statusMessage = $message;
                            } else {
                                $success = 0;
                                $statusMessage = "Something Went Wrong";
                            }
                            } else {
                            $success = 0;
                            $statusMessage = "Number of availability has been finished!";
                        }
                        } else {
                            $success = 0;
                            $statusMessage = "You entered invalid code!";
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "You already redeemed this offer!";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Offer Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                //$offerDetails = $arr
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // redeem via beacon
    function redeemOfferViaBeacon() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $type = $this->input->post('type'); // Get offer type - publicOffer or individualOffer
        $offerId = $this->input->post('offerId'); // Get campaignId
        $major = $this->input->post('major'); // Get major
        $minor = $this->input->post('minor'); // Get minor

        if (isset($userhash) && $userhash != '' && isset($offerId) && $offerId != '' && isset($major) && $major != '' && isset($minor) && $minor != '' && isset($type) && $type != '') {
            $this->load->model(array('user_model', 'offer_model', 'score_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success = 1;
                // check offer id exists or not
                if ($type == 'publicOffer') {
                    $checkoffer = $this->offer_model->checkOffer($offerId, 1);
                } else {
                    $checkoffer = $this->offer_model->checkIndividualOffer($offerId, 1);
                }
                if ($checkoffer) {


                    // check major , minor exists or not for this offer
                    if ($type == 'publicOffer') {
                        $checkmajorMinor = $this->offer_model->checkmajorMinor($offerId, $major, $minor);
                    } else {
                        $checkmajorMinor = $this->offer_model->checkmajorMinorForIndividual($offerId, $major, $minor);
                    }
                    if ($checkmajorMinor) {

                         if($checkoffer->availability > 0){
                        // check this already redeemed or not
                        $redeemedOffer = $this->offer_model->redeemedOffer($offerId, $userdetail['userid']);

                        if ($redeemedOffer == 0) {
                            $offerCoins = isset($checkoffer->coins)?$checkoffer->coins:200;
                            $businessUserDetails = $this->user_model->getBusinessName($checkoffer->user_id);
                            if ($checkoffer->discount_percentage == 0) {
                                $cointype = 'coins';
                                $value = $offerCoins;
                                $message = 'You got '.$offerCoins.' Hurree Coins from @' . ucfirst($businessUserDetails->businessName);
                            } else {
                                $type = 'discount';
                                $value = $checkoffer->discount_percentage;
                                $message = 'You got ' . $value . ' % discount and '.$offerCoins.' Hurree Coins from @' . ucfirst($businessUserDetails->businessName) . '. ' . "\n\n" . ' Show this to the cashier to get your discount!';
                            }
                            $arr = array(
                                'userid' => $userdetail['userid'],
                                'offerId' => $offerId,
                                'offerOwnerId' => $checkoffer->user_id,
                                 $cointype => $value,
                                'redeemType' => 1,
                                //'businessId' => $checkoffer->businessId,
                                'active' => 1,
                                'isDelete' => 0
                            );

                             if ($type == 'publicOffer') {

                            $result = $this->offer_model->redeemOffer($arr); // save redeem offer details
                             }else{
                               $result = $this->offer_model->redeemIndividualOffer($arr); // save redeem offer details
                             }
                            $userTotalCoins = $this->score_model->getUserCoins($userdetail['userid']);
                            $updateCoins = $userTotalCoins->coins + $offerCoins; // static value, add 200 more coins in total coins
                            $update = array(
                                'userid' => $userdetail['userid'],
                                'coins' => $updateCoins
                            );
                            $this->score_model->update($update); // update user total coins
                            $insert = array(
                                'userid' => $userdetail['userid'],
                                'coins' => $offerCoins,
                                'actionType' => 'add',
                                'coins_type' => 13,
                                'businessid' => $checkoffer->businessId,
                                'createdDate' => date('Y-m-d h:i:s')
                            );
                            $this->score_model->insert($insert); // update user  coins
                            if ($result) {
                                // decrease number of Availability users
                                if ($type == 'publicOffer') {
                                if ($checkoffer->availability > 0) {
                                    $updatedCount = $checkoffer->availability - 1;
                                } else {
                                    $updatedCount = $checkoffer->availability;
                                }

                                $this->offer_model->updateAvailability($offerId, $updatedCount);
                                }
                                // delete offer from save offer
                                $this->offer_model->deleteOffer($offerId);
                                // delete offer from save notification
                                $this->offer_model->deleteOfferfromNoti($offerId,$type);
                                // code for upadte user offers coins in businessConsumerRedeemCoins table
                                $data['businessUserId'] = $checkoffer->user_id;
                                $data['consumerId'] = $userdetail['userid'];
                                $coins = $offerCoins;
                                $this->user_model->OfferCoins($data,$coins);

                                // email code

                                $this->offer_model->redeemSuccessEmailtoConsumer($checkoffer->notification, $userdetail['username'], $userdetail['email']);
                                $arr['redeemedUsername'] = $userdetail['username'];
                                $arr['campaignsOwnerUsername'] = $businessUserDetails->username;
                                $arr['campaignsOwnerEmail'] = $businessUserDetails->email;
                                $this->offer_model->redeemSuccessEmailtoBusinessuser($arr);


                                // end email code
                                $success = 1;
                                //$offerDetails = $arr;
                                $statusMessage = $message;
                            } else {
                                $success = 0;
                                $statusMessage = "Something Went Wrong";
                            }
                        } else {
                            $success = 0;
                            $statusMessage = " You already redeemed this offer!";
                        }
                        } else {
                        $success = 0;
                        $statusMessage = 'Number of availability has been finished.';
                    }
                    } else {
                        $success = 0;
                        $statusMessage = 'Beacon is out of range or invalid major or minor for this offer.';
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Offer Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                //$offerDetails = $arr
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // redeem via QR code

    public function redeemOfferViaQRCode() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $code = base64_decode($this->input->post('qrcode'));
        $offerId = $this->input->post('offerId');


        if (isset($userhash) && $userhash != '' && isset($offerId) && $offerId != '' && isset($code) && $code != '') {
            $this->load->model(array('user_model', 'offer_model', 'score_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success = 1;
                // check offer id exists or not

                $checkoffer = $this->offer_model->checkOffer($offerId, 1);
                if ($checkoffer) {
                    // check this already redeemed or not
                    // get offer
                    $offerResult = $this->offer_model->getOffer($offerId, $code);

                    if ($offerResult) {

                        $redeemedOffer = $this->offer_model->redeemedOffer($offerId, $userdetail['userid']);
                        if($offerResult->availability>0){
                        if ($redeemedOffer == 0) {

                            $businessUserDetails = $this->user_model->getBusinessName($offerResult->user_id);
                            if ($checkoffer->discount_percentage == 0) {
                                $type = 'coins';
                                $value = $offerResult->coins;
                                $message = 'You got '.$offerResult->coins.' Hurree Coins from @' . $businessUserDetails->businessName;
                            } else {
                                $type = 'discount';
                                $value = $offerResult->discount_percentage;
                                $message = 'You got ' . $value . '% discount and '.$offerResult->coins.' Hurree Coins from @' . ucfirst($businessUserDetails->businessName) . '. ' . "\n\n" . ' Show this to the cashier to get your discount!';
                            }
                            $arr = array(
                                'userid' => $userdetail['userid'],
                                'offerId' => $offerId,
                                'offerOwnerId' => $offerResult->user_id,
                                $type => $value,
                                'redeemType' => 2,
                               // 'businessId' => $offerResult->businessId,
                                'active' => 1,
                                'isDelete' => 0
                            );

                            $result = $this->offer_model->redeemOffer($arr); // save redeem offer details
                            $userTotalCoins = $this->score_model->getUserCoins($userdetail['userid']);
                            $updateCoins = $userTotalCoins->coins + $offerResult->coins; // static value, add 200 more coins in total coins
                            $update = array(
                                'userid' => $userdetail['userid'],
                                'coins' => $updateCoins
                            );
                            $this->score_model->update($update); // update user total coins
                            $insert = array(
                                'userid' => $userdetail['userid'],
                                'coins' => $offerResult->coins,
                                'actionType' => 'add',
                                'coins_type' => 13,
                                'businessid' => $offerResult->businessId,
                                'createdDate' => date('Y-m-d h:i:s')
                            );
                            $this->score_model->insert($insert); // update user  coins
                            if ($result) {
                                // decrease number of Availability users
                                if ($offerResult->availability > 0) {
                                    $updatedCount = $offerResult->availability - 1;
                                } else {
                                    $updatedCount = $offerResult->availability;
                                }

                                $this->offer_model->updateAvailability($offerId, $updatedCount);
                                $this->offer_model->deleteOffer($offerId);
                                $this->offer_model->deleteOfferfromNoti($offerId,$type= 'publicOffer');
                                // code for upadte user offers coins in businessConsumerRedeemCoins table
                                $data['businessUserId'] = $offerResult->user_id;
                                $data['consumerId'] = $userdetail['userid'];
                                $coins = $offerResult->coins; // for now it is static
                                $this->user_model->OfferCoins($data,$coins);
                                // email code

                                $this->offer_model->redeemSuccessEmailtoConsumer($offerResult->notification, $userdetail['username'], $userdetail['email']);
                                $arr['redeemedUsername'] = $userdetail['username'];
                                $arr['campaignsOwnerUsername'] = $businessUserDetails->username;
                                $arr['campaignsOwnerEmail'] = $businessUserDetails->email;
                                $this->offer_model->redeemSuccessEmailtoBusinessuser($arr);

                                // end email code
                                $success = 1;
                                //$offerDetails = $arr;
                                $statusMessage = $message;
                            } else {
                                $success = 0;
                                $statusMessage = "Something Went Wrong";
                            }
                        } else {
                            $success = 0;
                            $statusMessage = "You already redeemed this offer!";
                        }
                        } else {
                            $success = 0;
                            $statusMessage = "Number of availability has been finished!";
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "You entered invalid QR code!";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Offer Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                //$offerDetails = $arr
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // save offer for the future

    public function saveOffer() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $offerId = $this->input->post('offerId');
        $type = $this->input->post('type');


        if (isset($userhash) && $userhash != '' && isset($offerId) && $offerId != '' && isset($type) && $type != '') {
            $this->load->model(array('user_model', 'offer_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                // check offer id exists or not
                if($type == 'publicOffer'){
                $checkoffer = $this->offer_model->checkOffer($offerId,1);
                }
                else{
                $checkoffer = $this->offer_model->checkIndividualOffer($offerId,1);
                }
                if ($checkoffer) {

                       $alredaySaved = $this->offer_model->checkAlreadySave($userdetail['userid'], $offerId,$type);


                    if (!$alredaySaved) {
                        // save offer
                        $saveOffer = $this->offer_model->saveOffer($userdetail['userid'], $offerId, $type);
                        if ($saveOffer) {
                            $success = 1;
                            $statusMessage = "Offer saved successfully";
                        } else {
                            $success = 0;
                            $statusMessage = "Something went wrong";
                        }
                    } else {
                        $success = 0;
                        $statusMessage = "You have already saved this offer";
                    }
                } else {
                    $success = 0;
                    $statusMessage = "Offer Not Found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                //$offerDetails = $arr
                )
            );
        }
        echo json_encode($response);
        exit;
    }

// fetch user's saved offers

    function getSavedOffers() {
        $userhash = $this->input->post('userHash'); // Get UserHash
        $page = $this->input->post('page'); // Get UserHash


        if (isset($userhash) && $userhash != '' && isset($page)) {
            $this->load->model(array('user_model', 'offer_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                // get saved offers
                $arr['user_id'] = $userdetail['userid'];
                $arr['isDelete'] = 0;

                $records = $this->offer_model->getSavedOffers($arr, '', '');
                if ($page == '') {
                    $page = 0;
                }

                $limit = 50;
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'index.php/userservice/getSavedOffers/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

                $data['page'] = $page;

                $result = $this->offer_model->getSavedOffers($arr, $page, $limit);  //// Get Record

                if (!empty($result) > 0) {
                    foreach ($result as $data) {
                        if (empty($data->offerimage)) {
                            $data->offerimage = '';
                        } else {
                            $data->offerimage = base_url() . 'upload/status_image/full/' . $data->offerimage;
                        }
                    }
                } else {
                    $success = 1;
                    $result = '';
                    $cnt_all_record = 0;
                    $statusMessage = "No more offers found";
                }

                $cnt_all_record = count($result);

                if ($result) {
                    $success = 1;
                    $cnt_all_record = $cnt_all_record;
                    $statusMessage = 'Offers list';
                } else {
                    $success = 1;
                    $result = '';
                    $cnt_all_record = $cnt_all_record;
                    $statusMessage = "No more offers found";
                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }
        } else {
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

        if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage
                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,
                    'savedOffers' => $result,
                    'cnt_all_record' => $cnt_all_record
                )
            );
        }
        echo json_encode($response);
        exit;
    }

    // show business user with associated rewards

    public function getcardsBusinessUsers(){
        $userhash = $this->input->post('userHash'); // Get UserHash
        $page = $this->input->post('page'); // Get UserHash



        if (isset($userhash) && $userhash != '' && isset($page) ) {
            $this->load->model(array('user_model', 'offer_model','reward_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                $success  = 1;
                // fetch redeemed offer's owners list
                $where['redeem_offer.userid'] = $userdetail['userid'];
                $where['redeem_offer.active'] = 1;
                $where['redeem_offer.isDelete'] = 0;
                $where['users.isDelete'] = 0;
                $records = $this->offer_model->getOffersOwners($where, '', '');
                if ($page == '') {
                    $page = 0;
                }

                $limit = 50;
                $data['records'] = count($records);
                $config['base_url'] = base_url() . 'index.php/userservice/getcardsBusinessUsers/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '50';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY
                $cnt_all_record = count($records);
                $data['page'] = $page;
                $result =  $this->offer_model->getOffersOwners($where,$page,$limit);

                if(count($result)>0){
                   $userids = array();
                   foreach($result as $businessUser){
//                        $arr['userid'] = $businessUser->userid;
//                        $arr['active'] = 1;
//                        $arr['isDelete'] = 0;
                        $userids[] = $businessUser->userid;

                        // get all rewards of requested business user
                        $rewardDetails =   $this->reward_model->getAllReward($businessUser->userid,$userdetail['userid']);

                        if(count($rewardDetails)>0){
                          $businessUser->rewards = $rewardDetails;
                        }else{
                          $businessUser->rewards = array();
                        }

                   }

//                   $rewards = $this->reward_model->getAllReward($userids,$userdetail['userid']);
//
//                   if(count($rewards) >0){
//
//                    $rewards = $rewards;
//                   }else{
//                     $rewards = array();
//                   }

                    $businessUserDetails= $result;

                }else{
                    $success = 1;
                    $statusMessage = "No Record Found";
                    $businessUserDetails= new ArrayObject();
                    $cnt_all_record = 0;
                }


            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }

        }else{
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

         if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => 'No record found',
                    "businessUserDetails" => $businessUserDetails,


                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "businessUserDetails" => $businessUserDetails,

                    'cnt_all_record' => $cnt_all_record
                )
            );
        }
        echo json_encode($response);
        exit;

    }

//     public function getBusinessUserDetails(){
//        $userhash = $this->input->post('userHash'); // Get UserHash
//        $userId = $this->input->post('userId'); // Get UserHash
//
//        if (isset($userhash) && $userhash != '' && isset($userId) && $userId!='') {
//            $this->load->model(array('user_model', 'reward_model'));
//            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details
//
//            if ($userdetail['status'] == 1) {
//                $success  = 1;
//                // get busineuser details start
//                $baseurl = base_url();
//                $select = 'user_Id as userid, currentOffersCoins, username, CONCAT(firstname, " ", lastname) as name,CONCAT("' . $baseurl . 'upload/profile/thumbnail/",image ) as userimage';
//                $arr['user_Id'] = $userId;
//                $arr['isDelete'] = 0;
//                $arr['usertype'] = 2;
//                $result =  $this->user_model->getOneUserDetails($arr,$select);
//                if(count($result)>0){
//                $businessUserDetails = $result;
//                $where['userid'] = $userId;
//                $where['active'] = 1;
//                $where['isDelete'] = 0;
//                // get all rewards of requesed business user
//                $rewardDetails =  $this->reward_model->getRewards($where);
//                if(count($rewardDetails)>0){
//                    $rewardDetails = $rewardDetails;
//                }else{
//                    $rewardDetails = new ArrayObject();
//                }
//                }else{
//                   $success = 0;
//                $statusMessage = "Error Occured. Requested user not found or not business user";
//                }
//            } else {
//                $success = 0;
//                $statusMessage = "Error Occured. User Not Found";
//            }
//
//        }else{
//            $success = 0;
//            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
//        }
//
//         if ($success == 0) {
//            $response = array(
//                "c2dictionary" => true,
//                "data" => array(
//                    "status" => "error",
//                    "statusMessage" => $statusMessage,
//
//                )
//            );
//        } else {
//
//            $response = array(
//                "c2dictionary" => true,
//                "data" => array(
//                    "status" => "success",
//                    "businessUserDetails" => $businessUserDetails,
//                    "rewardDetails"=>$rewardDetails
//
//                )
//            );
//        }
//        echo json_encode($response);
//        exit;
//     }
//
     public function redeemReward(){
         $userhash = $this->input->post('userHash'); // Get UserHash
         $rewardId = $this->input->post('rewardId'); // Get rewardId

        if (isset($userhash) && $userhash != '' && isset($rewardId) && $rewardId!='') {
            $this->load->model(array('user_model', 'reward_model','offer_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success  = 1;
                // check reward exists or not
               $checkReward =  $this->reward_model->checkReward($rewardId);
               if($checkReward){
                   if($checkReward->availability>0){
                   // already redeemed or not

                   $alreadyRedeemed = $this->reward_model->alreadyRedeemed($rewardId,$userdetail['userid']);
                   if($alreadyRedeemed == 0){
                   // can redeem or not
                   $arr['businessUserId'] = $checkReward->userid;
                   $arr['consumerId'] = $userdetail['userid'];
                   $coins = $checkReward->coins;

                   $canReedem =  $this->reward_model->canRedeemReward($arr,$coins);
                   if($canReedem){
                   // redeem reward
                   $data['userid'] = $userdetail['userid'];
                   $data['RewardOwnerId'] = $checkReward->userid;
                   $data['rewardId'] = $rewardId;
                   $data['active'] = 1;
                   $data['isDelete'] = 0;
                   $data['createdDate'] = date('Y-m-d h:i:s');
                   $result =  $this->reward_model->redeemReward($data);
                  if($result){
                     // decrease number of Availability users
                     if ($checkReward->availability > 0) {
                     $updatedCount = $checkReward->availability - 1;
                     } else {
                     $updatedCount = $checkReward->availability;
                     }

                     $this->reward_model->updateAvailability($rewardId, $updatedCount);
                     $businessUserConis['businessUserId'] = $checkReward->userid;
                     $businessUserConis['consumerId'] = $userdetail['userid'];
                     $businessUserConis['coins'] = $coins;
                     $this->reward_model->updatetotalCoins($businessUserConis);
                     //$this->reward_model->deleteReward($rewardId);
                     $success = 1;
                     $statusMessage = "Reward redeemed successfully";
                     $ownerDetail = $this->user_model->getOneUser($checkReward->userid);
                     $arr ['redeemedUsername'] = $userdetail['username'];
		     $arr ['campaignsOwnerUsername'] = $ownerDetail->username;
		     $arr ['campaignsOwnerEmail'] = $ownerDetail->email;
		     $this->offer_model->redeemSuccessEmailtoBusinessuser ( $arr );
                  }else{
                    $success = 0;
                    $statusMessage = "Something went wrong";
                  }
                   }else{
                      $success = 0;
                    $statusMessage = "You don't have enough coins from this business user to reedem this reward ";
                   }
                    }else{
                $success = 0;
                $statusMessage = "You have already redeem this reward.";

               }
                    }else{
                $success = 0;
                $statusMessage = "Number of availability has been finished.";
               }

               }else{
                $success = 0;
                $statusMessage = "Reward Not Found";
               }


            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }

     }else{
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

           if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage,

                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",
                    "statusMessage" => $statusMessage,


                )
            );
        }
        echo json_encode($response);
        exit;
     }
        public function userWhoRedeemed(){
         $userhash = $this->input->post('userHash'); // Get UserHash
         $rewardId = $this->input->post('rewardId'); // Get rewardId

        if (isset($userhash) && $userhash != '' && isset($rewardId) && $rewardId!='') {
            $this->load->model(array('user_model', 'reward_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success  = 1;
                // check reward exists or not
               $checkReward =  $this->reward_model->checkReward($rewardId);
               if($checkReward){
                   $success = 1;
                   $userLists = $this->reward_model->redeemedUsers($rewardId,$source = 'webservice');


               }else{
                $success = 0;
                $statusMessage = "Reward Not Found";
               }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }

       }else{
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

           if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage,

                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",

                    "userLists"=>$userLists


                )
            );
        }
        echo json_encode($response);
        exit;

        }

        public function quoteStatus(){
              $userhash = $this->input->post('userHash'); // Get UserHash
              $statusId = $this->input->post('statusId'); // Get statusId
              $status = $this->input->post('status'); // Get statusId

        if (isset($userhash) && $userhash != '' && isset($statusId) && $statusId!='' && isset($status)) {
            $this->load->model(array('user_model','status_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {

                $success  = 1;
                $arr['quoteFromStatusId'] = $statusId;
                $arr['createdDate'] = date('YmdHis');
                $arr['isDelete'] = 0;
                $result = $this->status_model->quoteStatus($arr);
                if($result){
                  $success = 0;
                $statusMessage = "Error Occured. User Not Found";
                }else{

                }
            } else {
                $success = 0;
                $statusMessage = "Error Occured. User Not Found";
            }

       }else{
            $success = 0;
            $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }
          if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage,

                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",

                    "userLists"=>$userLists


                )
            );
        }
        echo json_encode($response);
        exit;
        }

//         function getGeofence(){
//         $userhash = $this->input->post('userHash'); // Get UserHash
//         $latitude = $this->input->post('latitude'); // Get latitude
//         $longitde = $this->input->post('longitude'); // Get longitude
//
//        if (isset($userhash) && $userhash != '' && isset($latitude) && $latitude!= '' && isset($longitde) && $longitde!='') {
//        // if (isset($userhash) && $userhash != ''){
//            $this->load->model(array('user_model','geofence_model'));
//            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details
//
//            if ($userdetail['status'] == 1) {
//                $geofence = $this->geofence_model->getAllGeofence($latitude,$longitde,$radius = 10);
//              // echo '<pre>'; print_r($geofence);
//               $list = array();
//              for ($i = 0; $i < count($geofence); $i++) {
//                list($lat,$lng) =  explode(',',$geofence[$i]['latlng']);
//                $list[] = array('latitude'=>$lat,'longitude'=>$lng,'type'=>$geofence[$i]['type'],'radius'=>$geofence[$i]['radius']);
//               }
//
//              $success = 1;
//              $geofence = (array)$list ;
//
//            }else{
//               $success = 0;
//               $statusMessage = "Error Occured. User Not Found";
//            }
//        }else{
//           $success = 0;
//           $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
//        }
//
//          if ($success == 0) {
//            $response = array(
//                "c2dictionary" => true,
//                "data" => array(
//                    "status" => "error",
//                    "statusMessage" => $statusMessage,
//
//                )
//            );
//        } else {
//
//            $response = array(
//                "c2dictionary" => true,
//                "data" => array(
//                    "status" => "success",
//
//                    "geofence"=>$geofence
//
//
//                )
//            );
//        }
//        echo json_encode($response);
//        exit;
//
//    }

    // geofence notification, when user comes in geofence area
//    public function geofenceNotification(){
//         $userhash = $this->input->post('userHash'); // Get UserHash
//         $geofenceId = $this->input->post('geofenceId'); // Get geofenceId
//         $type = $this->input->post('type'); // Get geofenceId
//         $event = $this->input->post('event'); // Get geofenceId
//         $reciverUserhash = $this->input->post('reciverUserhash'); // Get UserHash
//          if (isset($userhash) && $userhash != '' && isset($geofenceId) && $geofenceId!='' && isset($type) && $type!='' && isset($reciverUserhash) && $reciverUserhash!='' && isset($event) && $event!='') {
//            $this->load->model(array('user_model','geofence_model'));
//            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details
//            $recieveruserdetail = $this->user_model->getuserHash($reciverUserhash); // Get UserHash Details
//
//            if ($userdetail['status'] == 1) {
//              $arr['userId'] = $recieveruserdetail['userid'];
//              $arr['geofenceId'] = $geofenceId;
//              $arr['event'] = 'entry';
//              $arr['type'] = $type;
//
//              // check type
//              if($type == 'geofence'){
//               $geofenceDetails = $this->geofence_model->getGeofence($geofenceId);
//                    $alert = $geofenceDetails->geofenceText;
//                    $offerId= '';
//                    $OfferUrl = '';
//                    $name = '';
//                    $username = '';
//                    $userimage= '';
//                    $offerimage= '';
//                    $createdDate= '';
//                    $availability= '';
//                    $discountValue= '';
//                    $coins= '';
//                    $type= $geofenceDetails['type'];
//
//
//              }else{
//                $campaignDetails = $this->offer_model->checkOffer($geofenceId);
//                    $alert = $campaignDetails->autoText;
//                    $offerId= $campaignDetails->offerId;
//                    $OfferUrl = $campaignDetails->offerUrl;
//                    $name = $campaignDetails->name;
//                    $username = $campaignDetails->username;
//                    $userimage= $campaignDetails->userimage;
//                    $offerimage= $campaignDetails->offerimage;
//                    $createdDate= $campaignDetails->createdDate;
//                    $availability= $campaignDetails->availability;
//                    $discountValue= $campaignDetails->discountValue;
//                    $coins= $campaignDetails->coins;
//                    $type= 'publicOffer';
//
//              }
//              //code for aws push notification
//              $deviceInfo = $this->user_model->getdeviceToken($recieveruserdetail['userid']);
//
//                                    if (count($deviceInfo) > 0) {
//
//                                        foreach ($deviceInfo as $device) {
//
//
//                                            $deviceToken = $device->key;
//                                            $deviceType = $device->deviceTypeID;
//                                            $title = 'My Test Message';
//
//                                            $sound = 'default';
//                                            $msgpayload = json_encode(array(
//                                                'aps' => array(
//                                                'alert' => $alert,
//                                                'offerId'=> $offerId,
//                                                'OfferUrl' => $OfferUrl,
//                                                'name' => $name,
//                                                'username'=>$username,
//                                                'userimage'=>$userimage,
//                                                'offerimage'=>$offerimage,
//                                                'createdDate'=>$createdDate,
//                                                'availability'=>$availability,
//                                                'discountValue'=>$discountValue,
//                                                'coins'=>$coins,
//                                                'type'=>$type,
//                                                'sound'=>$sound
//
//                                                )));
//
//
//                                            $message = json_encode(array(
//                                                'default' => $title,
//                                                'APNS_SANDBOX' => $msgpayload
//                                            ));
//
//
//                                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
//                                        }
//                                    }
//
//                                    //end notification code
//              $result = $this->geofence_model->saveGeofenceNotification();
//              if($result){
//                  $success = 1;
//                  $statusMessage  = 'Notification sent successfully';
//              }else{
//                  $success = 0;
//                  $statusMessage ='Already sent';
//              }
//
//
//            }else{
//               $success = 0;
//               $statusMessage = "Error Occured. User Not Found";
//            }
//        }else{
//           $success = 0;
//           $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
//        }
//
//          if ($success == 0) {
//            $response = array(
//                "c2dictionary" => true,
//                "data" => array(
//                    "status" => "error",
//                    "statusMessage" => $statusMessage,
//
//                )
//            );
//        } else {
//
//            $response = array(
//                "c2dictionary" => true,
//                "data" => array(
//                    "status" => "success",
//
//                    "statusMessage"=>$statusMessage
//
//
//                )
//            );
//        }
//        echo json_encode($response);
//        exit;
//    }

        function getGeofence(){
         $userhash = $this->input->post('userHash'); // Get UserHash


        if (isset($userhash) && $userhash != '') {

            $this->load->model(array('user_model','geofence_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
                $geofence = $this->geofence_model->getAllGeofence();

                $list = array();
                for($i = 0; $i< count($geofence); $i++){

                  list($lat,$lng) =  explode(',',$geofence[$i]->latlong);
                  $list[] = array('latitude'=>$lat,'longitude'=>$lng,'radius'=>$geofence[$i]->radius,'type'=>$geofence[$i]->type);
                }
              $success = 1;
              $geofence = (array)$list;

            }else{
               $success = 0;
               $statusMessage = "Error Occured. User Not Found";
            }
        }else{
           $success = 0;
           $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

          if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage,

                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",

                    "geofence"=>$geofence


                )
            );
        }
        echo json_encode($response);
        exit;

    }

    public function geofenceNotification(){
         $userhash = $this->input->post('userHash'); // Get UserHash
         $latitude = trim($this->input->post('latitude')); // Get latitude
         $longitude = trim($this->input->post('longitude')); // Get geofenceId
         $event = $this->input->post('event'); // Get entry or exit event


          if (isset($userhash) && $userhash != '' && isset($latitude) && $latitude!='' && isset($longitude) && $longitude!=''  &&  isset($event) && $event!='' ) {
          //if (isset($userhash) && $userhash != '' ) {
            $this->load->model(array('user_model','geofence_model','location_model'));
            $userdetail = $this->user_model->getuserHash($userhash); // Get UserHash Details

            if ($userdetail['status'] == 1) {
              $receiverUserhashDetail = $this->user_model->getuserHash($receiverUserhash); // Get UserHash Details

              $getGeofenceCampaign = $this->location_model->getGeofenceCampaign($userdetail['userid'],$latitude,$longitude,$event);
              if(count($getGeofenceCampaign)>0){
              foreach($getGeofenceCampaign as $geoCamp){
              // check type
              if($geoCamp->type == 'geofence'){

                    $alert = $geoCamp->geofenceText;
                    $offerId= '';
                    $OfferUrl = '';
                    $name = '';
                    $username = '';
                    $userimage= '';
                    $offerimage= '';
                    $createdDate= '';
                    $availability= '';
                    $discountValue= '';
                    $coins= '';
                    $type= $geoCamp->type;

                    // set array for save notification data

                    $arr['userId'] = $userdetail['userid'];
                    $arr['geofenceId'] = $geoCamp->geofence_id;
                    $arr['event'] = $event;
                    $arr['type'] = $geoCamp->type;


              }else{
                    $alert = $geoCamp->notification;
                    $offerId= $geoCamp->offerId;
                    $OfferUrl = base_url().'redeemOffer/index/' . $geoCamp->offerId;
                    $name = $geoCamp->name;
                    $username = $geoCamp->username;
                    $userimage= $geoCamp->userimage;
                    $offerimage= $geoCamp->offerimage;
                    $createdDate= $geoCamp->createdDate;
                    $availability= $geoCamp->availability;
                    $discountValue= $geoCamp->discountValue;
                    $coins= $geoCamp->coins;
                    $type= 'publicOffer';

                    // set array for save notification data

                    $arr['userId'] = $userdetail['userid'];
                    $arr['geofenceId'] = $geoCamp->offerId;
                    $arr['event'] = $event;
                    $arr['type'] = $geoCamp->type;

              }
              //code for aws push notification

              $result = $this->geofence_model->checkGeofenceNotification($arr);

              if($result){
              $deviceInfo = $this->user_model->getdeviceToken($userdetail['userid']);

                                    if (count($deviceInfo) > 0) {

                                        foreach ($deviceInfo as $device) {


                                            $deviceToken = $device->key;
                                            $deviceType = $device->deviceTypeID;
                                            $title = 'My Test Message';

                                            $sound = 'default';
                                            $msgpayload = json_encode(array(
                                                'aps' => array(
                                                'alert' => $alert,
                                                'offerId'=> $offerId,
                                                'OfferUrl' => $OfferUrl,
                                                'name' => $name,
                                                'username'=>$username,
                                                'userimage'=>$userimage,
                                                'offerimage'=>$offerimage,
                                                'createdDate'=>$createdDate,
                                                'availability'=>$availability,
                                                'discountValue'=>$discountValue,
                                                'coins'=>$coins,
                                                'type'=>$type,
                                                'sound'=>$sound

                                                )));


                                            $message = json_encode(array(
                                                'default' => $title,
                                                'APNS_SANDBOX' => $msgpayload
                                            ));


                                            $result = $this->amazonSns($deviceToken, $message, $deviceType);
                                            if($result){
                                             $this->geofence_model->saveGeofenceNotification($arr);
                                            }
                                        }
                                    }

                                    //end notification code
                  $success = 1;
                  $statusMessage  = 'Notification sent successfully';

              }else{
              $success = 1;
              $statusMessage  = 'Notification sent already';
              }

              }
              }else{
                  $success = 1;
                  $statusMessage = "No geofence or campaign found";
              }



            }else{
               $success = 0;
               $statusMessage = "Error Occured. User Not Found";
            }
        }else{
           $success = 0;
           $statusMessage = "Error occoured. Parameters not found or values can not be blank.";
        }

          if ($success == 0) {
            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "error",
                    "statusMessage" => $statusMessage,

                )
            );
        } else {

            $response = array(
                "c2dictionary" => true,
                "data" => array(
                    "status" => "success",

                    "statusMessage"=>$statusMessage


                )
            );
        }
        echo json_encode($response);
        exit;
    }





        // set headers code, added by shiwangi

        public function setHttpHeaders($statusCode){

		$statusMessage = $this->getHttpStatusMessage($statusCode);
		$contentType = "application/x-www-form-urlencoded; charset=utf-8";
		header($this->httpVersion. " ". $statusCode ." ". $statusMessage);
		header("Content-Type:". $contentType);
	}

	public function getHttpStatusMessage($statusCode){
		$httpStatus = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported');
		return ($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : $status[500];
	}


}
