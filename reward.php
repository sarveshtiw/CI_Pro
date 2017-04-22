<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Reward extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('hurree'));
        $this->load->library(array('form_validation',));
        $this->load->model(array('user_model', 'reward_model', 'administrator_model', 'businessstore_model', 'campaign_model','location_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    // get all reward created by logged in business user
    public function index() {
        $login = $this->administrator_model->front_login_session();
        if ($login->true == 1 && $login->accesslevel != '') {
            $usertype = $login->usertype;
            if ($usertype == 2 || $usertype == 5) {
                $this->load->helper('convertlink');
                 $userId = $login->user_id;
                 $where['userid'] = $userId;
                 $where['active'] = 1;
                 $where['isDelete'] = 0;
                  /* Start Pagination for Rewards */
                //$userid,$start=NULL, $pagesize=NULL,$count=NULL,$noticeStatusid=NULL,$max_status_id=NULL,$loginUserId=NULL
                $data['records'] = $this->reward_model->getRewardsWebsite($where, '', '', $count = 1);   //// Get Total No of Records in Database

                $config['base_url'] = base_url() . 'index.php/reward/index/';
                $config['total_rows'] = $data['records'];
                $config['per_page'] = '6';
                $config['uri_segment'] = 3;

                $this->pagination->initialize($config); //// SEND TO PAGINATION LIBRARY

               $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
               $data['page'] = $page;
               $data['loggedinUsertype'] = $login->usertype;
               $data['rewardDetails'] = $this->reward_model->getRewardsWebsite($where, $data['page'], $config['per_page'], '');  //// Get Record
                // get all rewards of requesed business user
                //$data['rewardDetails'] = $this->reward_model->getRewardsWebsite($where);
                $data['campaigns'] = $this->campaign_model->getAllCampaigns($userId);

                $data['packages'] = $this->businessstore_model->getAllpackages();
                $header['viewPage'] = 'reward';
                $header['loginuser'] = $userId;
                $header['user'] = $this->user_model->getOneUser($userId);
                //$data['noticeStatusid'] = $noticeStatusid;
                $data['rewardscount'] = count($data['rewardDetails']);
                $data['per_page'] = $config['per_page'];
                $this->load->view('inner_header', $header);
                $this->load->view('reward_listing', $data);
                $this->load->view('inner_footer');
            } else {
                redirect("timeline");
            }
        } else {
            $this->session->set_flashdata('error_message', 'Session Has been Expire. Please Sign in again');
            redirect("home");
        }
    }


    function rewardPagination(){
         $login = $this->administrator_model->front_login_session();
         $userId = $login->user_id;
            $totalrecord = $_POST['totalrecord'];
            $rewardscount = $_POST['rewardscount'];
            $per_page = $_POST['per_page'];
            $max_status_id = @$_POST['status_id'];

            $start = $rewardscount;
                 $where['userid'] = $userId;
                 $where['active'] = 1;
                 $where['isDelete'] = 0;
            $data['rewardDetails'] = $this->reward_model->getRewardsWebsite($where, $start, $per_page, '');


             $this->load->view('pagination_reward_listing', $data);
    }
    // call create reward view

    function createReward(){
         $login = $this->administrator_model->front_login_session();
          $userid = $login->businessId;
       if($login->usertype == 7 || $login->usertype == 2 ){

          $data['locations'] = $this->location_model->getUserLocations1($userid);
        }

         if($login->usertype == 6){
          $data['locations'] = $this->user_model->getAllBranchesByUserId($login->businessId,$userid);
        }
        $this->load->view('create_reward',$data);
    }

    // save rewards

    function saveReward(){
        $login = $this->administrator_model->front_login_session();
        $userid = $login->user_id;
        $username = $login->username;

        if (@$_FILES['imageReward']['size'] > 0) {

            // Image upload in full size in profile directory
            $extionArray = array('jpg', 'jpeg', 'png', 'gif');
            //echo '<pre>'; print_r($_FILES);die;
            // Image upload in full size in profile directory

            $uploads_dir = 'upload/status_image/full/' . $userid;
            $mediumImagePath = 'upload/status_image/medium/' . $userid;
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

            $tmp_name = $_FILES["imageReward"]["tmp_name"];
            $name = mktime() . $_FILES["imageReward"]["name"];
            move_uploaded_file($tmp_name, "$uploads_dir/$name");

            // image resize in medium size in medium directory
            $this->load->library('image_lib');
            $config['image_library'] = 'gd2';
            $config['source_image'] = "upload/status_image/full/" . $userid . "/" . $name;
            $config['new_image'] = 'upload/status_image/medium/' . $userid . "/" . $name;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 400;
            $config['height'] = 350;
            $this->image_lib->initialize($config);
            $rtuenval = $this->image_lib->resize();
            $this->image_lib->clear();

            $notification_image = $userid . "/" . $name;
            // Image Upload End
        } else {
            $notification_image = '';
        }

        $notification = $_POST['reward_notification'];
        $price = $_POST['reward_price1'];
        $available = $_POST['reward_available'];
        $date = date('Y-m-d H:i:s');
        $startdate = $_POST['day'];
        $enddate = $_POST['endday'];
        $insert = array(
            'reward_id' => '',
            'userid' => $userid,
            'rewardText' => $notification,
            'rewardImage' => $notification_image,
            'businessId' => $login->businessId,
            'coins' => $_POST['reward_coins'],
            'startDate' => $startdate,
            'totalAvailable'=>$available,
            'endDate' => $enddate,
            'price'=>$price,
            'availability' => $available,
            'active' => 1,
            'isDelete' => 0,
            'createdDate' => $date
        );

        $insertId = $this->reward_model->saveReward($insert);
        $this->reward_model->rewardLocationMap($insertId,$_POST['location']);
        echo $insertid;
    }

    // confirm box for delete reward

    function deleteRewardConfirm($rewardId){
        $data['rewardId'] = $rewardId;
        $this->load->view('delete_reward',$data);

    }

     // soft delete reward

    function deleteReward($rewardId){
        $result = $this->reward_model->deleteReward($rewardId);
        echo 1; exit;

    }

    function redeemedUsers() {

        $offerid = $this->uri->segment(3);
        $data['redeemedUsers'] = $this->reward_model->redeemedUsers($offerid);
        $this->load->view('redeemed_users', $data);
    }

    public static function ago_time($date = NULL) {
        if (empty($date)) {
            return "No date provided";
        }

        $periods = array("s", "m", "h", "d", "w", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $unix_date = strtotime($date);

        // check validity of date
        if (empty($unix_date)) {
            return "Bad date";
        }

        // is it future date or past date
        /* echo $now.' </br>' ;
          echo $unix_date ; */
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";
        } else {
            $difference = $unix_date - $now;
            //$tense         = "from now";
            $tense = "ago";
        }

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        /* if($difference != 1) {
          $periods[$j].= "s";
          } */

        $showtime = "$difference $periods[$j]";

        if ($periods[$j] == 'month' || $periods[$j] == 'year') {
            $showtime = date("d/m/y", strtotime($date));
        }

        return $showtime;
    }

   function performance($rewardId = false) {
        $login      = $this->administrator_model->front_login_session();
        $user_id    = $login->user_id;
        $businessId = $login->businessId;
        $type       = 'reward';

      	$data['reawrdId'] = $rewardId;
        $data['genderUsers'] = $this->reward_model->getPerformances($type,$rewardId,$user_id);
        $rewardNotiPer = $this->reward_model->getPerformancesByDate($type,$rewardId,$user_id);
        //echo '<pre>';
        //print_r($data['genderUsers']); exit;
        $data['rewardNotiPer'] = $rewardNotiPer;
        //echo '<pre>'; print_r($data['rewardNotiPer']); exit;

      	$this->load->view('reward_performance',$data);

   }

}
