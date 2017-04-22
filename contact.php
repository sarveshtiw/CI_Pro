<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contact extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper(array('salesforce_helper', 'hubspot_helper'));
        $this->load->library(array('form_validation', 'pagination'));
        $this->load->model(array('administrator_model', 'contact_model', 'permission_model', 'groupapp_model', 'hubSpot_model', 'brand_model', 'country_model'));
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active == 0) {
            redirect(base_url());
        }
        $this->output->set_header('Last-Modified:' . gmdate('D, d M Y H:i:s') . 'GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0', false);
        $this->output->set_header('Pragma: no-cache');
    }

    function index($app_group_id = NULL) {
//            echo $type;
//		echo $app_group_id; die;
        $data = array();
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'contact';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;


            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $groups = $this->groupapp_model->getUserGroups($login->user_id);
                if (count($groups) > 0) {
                    foreach ($groups as $group) {

                        $groupArray[] = $group->app_group_id;
                    }
                } else {
                    $groupArray = '';
                }

                $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
            }

            // echo '<pre>'; print_r($login); die;

            if ($login->usertype == 8) {
                $data['allgroups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $data['allgroups'] = $this->groupapp_model->getUserGroup($login->user_id);
            }
            $data['app_group_id'] = '/' . $app_group_id;
            $data['persona_users'] = array();
            $persona_users = $this->brand_model->getAllPersonasByBusinessId($login->businessId);
            $persona_demo_users = $this->brand_model->getPersonaDemoUser();
            if (count($persona_users) > 0) {
                $data['persona_users'] = $persona_users;
            }

            if (count($persona_demo_users) > 0) {
                $data['persona_demo_users'] = $persona_demo_users;
            }
            //echo '<pre>'; print_r($data); die;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/contactPage', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    function assignPersona($contactIds = 0) {
        $data = array();
        $data['personaUsers'] = array();
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active != 0) {
            $businessId = $login->businessId;
            $personaUsers = $this->brand_model->getAllPersonasByBusinessId($businessId);
            if (count($personaUsers) > 0) {
                $data['personaUsers'] = $personaUsers;
            }
            if($contactIds > 0)
            {
                $data['personaIds'] = $this->brand_model->getAssignedPersonaIds($contactIds);
            }
        }
        $this->load->view('3.1/assign_persona.php', $data);
    }

    function addContact($userId = NULL) {

        $login = $this->administrator_model->front_login_session();

        if ($_SERVER['HTTP_REFERER'] == base_url() . "contact") {
            $data['groupId'] = '';
        } else {
            $data['groupId'] = str_replace(base_url() . "contact/index/", "", $_SERVER['HTTP_REFERER']);
        }

        $data['userid'] = $userId;

        $data['external_user_id'] = '';
        $data['firstName'] = '';
        $data['lastName'] = '';
        $data['email'] = '';
        $data['phoneNumber'] = '';
        $data['user_image'] = '';
        $data['app_group_id'] = '';
        $data['persona_user_id'] = '';
        $data['company'] = '';

        if ($userId != '') {
            $where['external_user_id'] = $userId;
            $where['isDelete'] = 0;
            $select = "*";

            $userDetails = $this->contact_model->getConatctDetails($select, $where);

            $data['external_user_id'] = $userDetails->external_user_id;
            $data['firstName'] = $userDetails->firstName;
            $data['lastName'] = $userDetails->lastName;
            $data['email'] = $userDetails->email;
            $data['phoneNumber'] = $userDetails->phoneNumber;
            $data['app_group_id'] = $userDetails->app_group_id;
            $data['app_group_apps_id'] = $userDetails->app_group_apps_id;
            $data['user_image'] = $userDetails->user_image;
            $data['company'] = $userDetails->company;
            $personaUsers = $this->brand_model->getAllPersonaByExternalUserId($userId);
            if (count($personaUsers) > 0) {
                foreach ($personaUsers as $user) {
                    $persona_user_id[] = $user->persona_user_id;
                }
                //print_r($persona_user_id);exit;
                $data['persona_user_id'] = $persona_user_id;
            }
        }

        $data['allgroups'] = $this->groupapp_model->getGroups($login->businessId);
        $data['personaUsers'] = $this->brand_model->getAllPersonasByBusinessId($login->businessId);

        $this->load->view('3.1/add_contact', $data);
        $this->load->view('3.1/imageUpload');
    }

    function checkPhoneNumberExist() {
        $phone = trim($_POST['phone']);
        $external_user_id = trim($_POST['external_user_id']);

        $exist = $this->contact_model->checkPhoneNumber($phone, $external_user_id);
        echo count($exist);
    }

    function saveContact() {

        $login = $this->administrator_model->front_login_session();

// 		$cookies = $this->input->cookie ('group',true);
// 		$cookie_group = explode(",", $cookies);
        //print_r($cookie_group); die;

        $createdBy = $login->user_id;
        $businessId = $login->businessId;

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $appgroup = $_POST['appgroup'];
        $external_user_id = $_POST['external_user_id'];
        $removeImage = $_POST["removeImage"];
        $company = $_POST["company"];
        $persona_ids = $_POST["personaUsers"];

        $save['external_user_id'] = $external_user_id;
        $save['app_group_id'] = $appgroup;
        $save['firstname'] = $firstname;
        $save['lastname'] = $lastname;
        $save['email'] = $email;
        $save['phoneNumber'] = $phone;
        $save['isDelete'] = "0";
        if($external_user_id == ''){
        $save['createdDate'] = date('YmdHis');
        }
        $save['company'] = $company;
        if(empty($persona_ids)){
          $save['persona_ids'] = "";
        }else{
          $save['persona_ids'] = $persona_ids;
        }
        //print_r($save); exit;
        $ime = $_POST['pic'];
        //$userId = $last_id;
        if ($ime != '') {
            $image = explode(';base64,', $ime);

            if ($size = @getimagesize($ime)) {
                $responseImage = "1";
            } else {
                $responseImage = "0";
            }

            if ($responseImage == "1") {

                $type = $size['mime'];
                $typea = explode('/', $type);
                $extnsn = $typea[1];
                $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

                $img_cont = str_replace(' ', '+', $image[1]);
                //$img_cont=$image[1];
                $data = base64_decode($img_cont);
                $im = imagecreatefromstring($data);
                $filename = time() . '.' . $extnsn;
                //echo $im; exit;
                $thumbnailpath = 'upload/profile/thumbnail/' . $filename;
                $mediumpath = 'upload/profile/medium/' . $filename;
                $fullpath = 'upload/profile/full/' . $filename;

                // code for upload image in thumbnail folder
                imagealphablending($im, false);
                imagesavealpha($im, true);

                // code for upload image in medium folder
                imagealphablending($im, false);
                imagesavealpha($im, true);

                // code for upload image in full folder
                imagealphablending($im, false);
                imagesavealpha($im, true);

                if (in_array($extnsn, $valid_exts)) {
                    $quality = 0;
                    if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                        $quality = round((100 - $quality) * 0.09);
                        $resp = imagejpeg($im, $thumbnailpath, $quality);
                        $resp = imagejpeg($im, $mediumpath, $quality);
                        $resp = imagejpeg($im, $fullpath, $quality);
                    } else if ($extnsn == 'png') {
                        $quality = round((100 - $quality) * 0.09);
                        $resp = imagepng($im, $thumbnailpath, $quality);
                        $resp = imagepng($im, $mediumpath, $quality);
                        $resp = imagepng($im, $fullpath, $quality);
                    } else if ($extnsn == 'gif') {
                        $resp = imagegif($im, $thumbnailpath);
                        $resp = imagegif($im, $mediumpath);
                        $resp = imagegif($im, $fullpath);
                    }
                }

                $save['user_image'] = $filename;
            }
        } else {
            $responseImage = "1";

            if ($external_user_id != '' && $removeImage == "0") {

                $where['external_user_id'] = $external_user_id;
                $where['isDelete'] = 0;
                $select = "*";
                $userDetails = $this->contact_model->getConatctDetails($select, $where);

                $data['user_image'] = $userDetails->user_image;
            } elseif ($external_user_id != '' && $removeImage == "1") {
                $save['user_image'] = '';
            }
        }

        if ($responseImage == "1") {

            $last_id = $this->contact_model->saveContact($save);
        }




        if ($responseImage == "1" && $external_user_id == '') {
            $hubspot = $this->brand_model->getHubSpotDetails($login->user_id);
            if ($hubspot->on_off == 1) {
                if ($this->session->userdata('userHubId') != '') {

                    $arr = array();

                    $dt = array(
                        array(
                            'property' => 'email',
                            'value' => $email
                        ),
                        array(
                            'property' => 'firstname',
                            'value' => $firstname
                        ),
                        array(
                            'property' => 'lastname',
                            'value' => $lastname
                        ),
                        array(
                            'property' => 'phone',
                            'value' => $phone
                        )
                    );

                    $arr_Onecontact = array(
                        'email' => $email,
                        'properties' => $dt
                    );

                    array_push($arr, $arr_Onecontact);

                    //get hubspot details
                    //$hubDetails = $this->gethubspotDetails($login);

                    $portalId = $this->session->userdata('hubPortalId');
                    $hwhere ['userid'] = $login->user_id;
                    $hwhere['portalId'] = $portalId;
                    $hwhere ['isActive'] = 1;
                    $select = 'userHubSpotId, refresh_token, portalId, accress_token';
                    $hubDetails = $this->hubSpot_model->getHubSpotDetails($select, $hwhere);

                    $status = $this->hubspotAuthenticaion($portalId);
                    if ($status == 302) {

                        $post_json = json_encode($arr);

                        //$hapikey = '5f56055e-0e9f-4691-a1bc-69ba10a86e20';
                        //$endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch?hapikey=' . $hapikey;
                        $endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch?access_token=' . $hubDetails->accress_token . '&portalId=' . $hubDetails->portalId;

                        $ch = @curl_init();
                        @curl_setopt($ch, CURLOPT_POST, true);
                        @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
                        @curl_setopt($ch, CURLOPT_URL, $endpoint);
                        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                        $response = @curl_exec($ch);
                        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curl_errors = curl_error($ch);
                        @curl_close($ch);
                        //echo "curl Errors: " . $curl_errors;
                        //echo "\nStatus code: " . $status_code;
                        ///echo "\nResponse: " . $response;
                        //die;
                        //echo $status_code;
                    } else {
                        //echo "error";
                    }
                }
            }
            //Export to salesforce
            $salesforce = $this->brand_model->getSalesforceDetails($login->user_id);
            if (count($salesforce) > 0) {
                if ($salesforce->on_off == 1) {
                    $salescode = $this->session->userdata('salesCode');
                    if ($salescode != '') {

                        $where['userId'] = $login->user_id;
                        $select = 'refresh_token, instance_url, access_token';
                        $salesDetails = $this->hubSpot_model->getSalesUserDetails($select, $where);
                        //print_r($salesDetails); die;
                        $refresh_token = $salesDetails->refresh_token;
                        //$statusSalesforce = refreshSalesToken($refresh_token, $login->user_id);
                        //if($statusSalesforce == 200)
                        //{

                        $arr_user = array(
                            "FirstName" => $firstname,
                            "LastName" => $lastname,
                            "Email" => $email,
                            "Phone" => $phone,
                        );

                        $contactstatus = create_contact($arr_user, $salesDetails->instance_url, $salesDetails->access_token, $last_id);
                        if ($contactstatus != 201) {
                            $contactstatus = create_contact($arr_user, $salesDetails->instance_url, $salesDetails->access_token, $last_id);
                        }

                        //echo 'Contact Status: '.$contactstatus;
                        //}else{
                        //echo 'Salesforce Status: '.$statusSalesforce;
                        //}
                    }
                }
            }
        }

        if (isset($_POST['personaUsers']) && $responseImage == "1") {
            $personaUsers = $_POST['personaUsers'];
            if (strlen($personaUsers) > 0) {
                if (!empty($external_user_id)) {
                    $deletePersonas = $this->brand_model->deletePersonaByContacts($external_user_id);
                    $personaUsers = explode(',', $personaUsers);
                    foreach ($personaUsers as $persona_user_id) {
                        $savePersonaContacts = array(
                            'persona_user_id' => $persona_user_id,
                            'external_user_id' => $external_user_id,
                            'isDelete' => 0,
                            'createdDate' => date('Y-m-d H:i:s')
                        );
                        $last_insertId = $this->brand_model->savePersonaByContacts($savePersonaContacts);
                    }
                } else {
                    //$deletePersonas = $this->brand_model->deletePersonaByContacts($external_user_id);
                    $personaUsers = explode(',', $personaUsers);
                    foreach ($personaUsers as $persona_user_id) {
                        $savePersonaContacts = array(
                            'persona_user_id' => $persona_user_id,
                            'external_user_id' => $last_id,
                            'isDelete' => 0,
                            'createdDate' => date('Y-m-d H:i:s')
                        );
                        $last_insertId = $this->brand_model->saveAssignContacts($savePersonaContacts);
                    }
                }
            }
        }



        //$personaAssignContact
        //echo $this->db->last_query();
        if ($responseImage == "1") {
            echo $last_id;
        } else {
            echo 'Please upload another image';
        }
    }

    function saveprofileimage() {

        $ime = $_POST['pic'];
        $userId = $_POST['userid'];
        //if($ime != ''){
        $image = explode(';base64,', $ime);
        //$size = getimagesize($ime);
        if ($size = @getimagesize($ime)) {
            $responseImage = "1";
        } else {
            $responseImage = "0";
        }
        if ($responseImage == "1") {

            $type = $size['mime'];
            $typea = explode('/', $type);
            $extnsn = $typea[1];
            $valid_exts = array('jpeg', 'jpg', 'png', 'gif');

            $img_cont = str_replace(' ', '+', $image[1]);
            //$img_cont=$image[1];
            $data = base64_decode($img_cont);
            $im = imagecreatefromstring($data);
            $filename = time() . '.' . $extnsn;
            //echo $im; exit;
            $thumbnailpath = 'upload/profile/thumbnail/' . $filename;
            $mediumpath = 'upload/profile/medium/' . $filename;
            $fullpath = 'upload/profile/full/' . $filename;

            // code for upload image in thumbnail folder
            imagealphablending($im, false);
            imagesavealpha($im, true);

            // code for upload image in medium folder
            imagealphablending($im, false);
            imagesavealpha($im, true);

            // code for upload image in full folder
            imagealphablending($im, false);
            imagesavealpha($im, true);

            if (in_array($extnsn, $valid_exts)) {
                $quality = 0;
                if ($extnsn == 'jpeg' || $extnsn == 'jpg') {
                    $quality = round((100 - $quality) * 0.09);
                    $resp = imagejpeg($im, $thumbnailpath, $quality);
                    $resp = imagejpeg($im, $mediumpath, $quality);
                    $resp = imagejpeg($im, $fullpath, $quality);
                } else if ($extnsn == 'png') {
                    $quality = round((100 - $quality) * 0.09);
                    $resp = imagepng($im, $thumbnailpath, $quality);
                    $resp = imagepng($im, $mediumpath, $quality);
                    $resp = imagepng($im, $fullpath, $quality);
                } else if ($extnsn == 'gif') {
                    $resp = imagegif($im, $thumbnailpath);
                    $resp = imagegif($im, $mediumpath);
                    $resp = imagegif($im, $fullpath);
                }
            }
            // code for update user image
            $login = $this->administrator_model->front_login_session();

            if (!isset($_POST['usertype'])) {
                $userid = $userId;
                $update = array(
                    'external_user_id' => $userid,
                    'user_image' => $filename,
                );
                $this->contact_model->updateContact($update);
            } else {
                $userid = $userId;
                $update = array(
                    'persona_user_id' => $userid,
                    'user_image' => $filename,
                );
                $this->brand_model->savePersona($update);
            }
            return $resp;
        } else {
            echo 'Please upload another image';
        }


        /* }else{
          $filename = '';
          $resp = '';
          } */
    }

    public function contactListingResponse($type = NULL, $app_group_id = NULL) {
        ($type = 'type') ? $type = NULL : $type = $type;
        $login = $this->administrator_model->front_login_session();
        $subQuery = '';

        if ($login->active != 0) {

            /* Array of database columns which should be read and sent back to DataTables. Use a space where
             * you want to insert a non-database field (for example a counter or static image)
             */
            $select = "external_users.external_user_id exContactId, external_users.external_user_id contactId,  external_users.user_image contactImage, CONCAT(firstname,' ',lastname) contactName, email, company, phoneNumber, app_group_name, GROUP_CONCAT( persona_users.name ) personaName, external_users.external_user_id actionId";
            $aColumns = array('exContactId','contactId', 'contactImage', 'contactName', 'email','company', 'phoneNumber', 'app_group_name',  'personaName', 'actionId');


            $sTable = 'external_users';
            $iDisplayStart = $this->input->get_post('iDisplayStart', true);
            $iDisplayLength = $this->input->get_post('iDisplayLength', true);
            $iSortCol_0 = $this->input->get_post('iSortCol_0', true);
            $iSortingCols = $this->input->get_post('iSortingCols', true);
            $sSearch = $this->input->get_post('sSearch', true);
            $sEcho = $this->input->get_post('sEcho', true);

            // Paging
            if (isset($iDisplayStart) && $iDisplayLength != '-1') {
                $this->db->limit($this->db->escape_str($iDisplayLength), $this->db->escape_str($iDisplayStart));
            }



            // Ordering
            if (isset($iSortCol_0)) {
                for ($i = 0; $i < intval($iSortingCols); $i++) {
                    $iSortCol = $this->input->get_post('iSortCol_' . $i, true);
                    $bSortable = $this->input->get_post('bSortable_' . intval($iSortCol), true);
                    $sSortDir = $this->input->get_post('sSortDir_' . $i, true);

                    if ($bSortable == 'true') {
                        $this->db->order_by($aColumns[intval($this->db->escape_str($iSortCol))], $this->db->escape_str($sSortDir));
                    }
                }
            }

            /*
             * Filtering
             * NOTE this does not match the built-in DataTables filtering which does it
             * word by word on any field. It's possible to do here, but concerned about efficiency
             * on very large tables, and MySQL's regex functionality is very limited
             */


            if (isset($sSearch) && !empty($sSearch)) {
                $whereString = '';
                for ($i = 0; $i < count($aColumns); $i++) {
                    $bSearchable = $this->input->get_post('bSearchable_' . $i, true);

                    // Individual column filtering
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] != 'contactId' && $aColumns[$i] != 'contactName' && $aColumns[$i] != 'contactImage' && $aColumns[$i] != 'personaName' && $aColumns[$i] != 'actionId') {
                        //   $this->db->or_like($aColumns[$i], $this->db->escape_like_str($sSearch));
                        $whereString .= " $aColumns[$i]  LIKE '%$sSearch%' OR ";
                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'contactName') {
                        // $this->db->or_like("concat_ws(' ',firstname,lastname)", $this->db->escape_like_str($sSearch));

                        $whereString .= " concat_ws(' ',firstname,lastname)  LIKE '%$sSearch%' OR ";
                    }

                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'personaName') {
                        // $this->db->where("find_in_set('$sSearch',persona_users.name)  > 0");
                        // $this->db->or_like("persona_users.name", $this->db->escape_like_str($sSearch));

                        $whereString .= " persona_users.name  LIKE '%$sSearch%'";
                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'contactId') {

                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'actionId') {

                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'contactImage') {

                    }
                }
            }


            $this->db->_protect_identifiers = false;
            $this->db->select('SQL_CALC_FOUND_ROWS ' . $select, false);
            $this->db->from('external_users');
            $this->db->join('app_group', 'app_group.app_group_id = external_users.app_group_id', 'left');
//            $this->db->join('persona_assign_contacts', 'persona_assign_contacts.external_user_id = external_users.external_user_id', 'left');
//            $this->db->join('persona_users', "FIND_IN_SET(persona_users.persona_user_id,persona_assign_contacts.persona_user_id)", "left");

            $this->db->join('persona_users', "FIND_IN_SET(persona_users.persona_user_id,external_users.persona_ids) AND persona_users.isDelete = 0", "left");
          //  $this->db->where_in('persona_users.isDelete', 0);
            if ($login->usertype == 8) {
                if ($app_group_id != NULL) {
                    $this->db->where(['app_group.businessId' => $login->businessId, "external_users.isDelete" => 0, 'external_users.app_group_id' => $app_group_id]);
                } else {
                    $this->db->where(['app_group.businessId' => $login->businessId, "external_users.isDelete" => 0]);
                }
            } else {
                $userDetails = $this->groupapp_model->getUserGroup($login->user_id);
                $arr_apps = array();
                foreach ($userDetails as $usD) {
                    array_push($arr_apps, $usD->app_group_id);
                }
                $appsId = implode(",", $arr_apps);

                if ($app_group_id != NULL) {
                    $appsId = $app_group_id;
                }

                $this->db->where_in('app_group.app_group_id', $appsId);
                $this->db->where(["external_users.isDelete" => 0]);
            }

            if (isset($sSearch) && !empty($sSearch)) {
                $this->db->where("($whereString)");
            }

            $this->db->group_by('external_users.external_user_id');
            // $this->db->order_by('GROUP_CONCAT(persona_users.name)', 'desc');
            $rResult = $this->db->get();

            $this->db->select('FOUND_ROWS() AS found_rows');
            $iFilteredTotal = $this->db->get()->row()->found_rows;

            // Total data set length
            // $iTotal = $this->db->count_all($sTable);
            // Output
            $output = array(
                'sEcho' => intval($sEcho),
                'iTotalRecords' => $iFilteredTotal,
                'iTotalDisplayRecords' => $iFilteredTotal,
                'aaData' => array()
            );



            foreach ($rResult->result_array() as $aRow) {

                $row = array();
                foreach ($aColumns as $col) {
                    $onError = "onError=this.onerror=null;this.src='" . base_url() . "upload/profile/medium/user.png'; ';";
                    switch ($col) {
                        case "contactImage":
                            if ($aRow[$col] == '') {
                                $image = "<img src='" . base_url() . "upload/profile/thumbnail/user.png' alt='userImage' style='width: 100%; border-radius: 10%;' $onError />";
                            } else {
                                $image = "<img src='" . base_url() . "upload/profile/thumbnail/" . $aRow[$col] . "' alt='userImage' style='width: 100%; border-radius: 10%;' $onError />";
                            }
                            $row[] = $image;
                            break;
                        case "contactName": $contactName = (trim($aRow[$col]) == "" ||  empty(trim($aRow[$col])) || is_null(trim($aRow[$col]))) ? '<a href="'.base_url().'appUser/timeline/'.$aRow['actionId'].'">--No Value--</a>' : '<a href="'.base_url().'appUser/timeline/'.$aRow['actionId'].'">'.$aRow[$col].'</a>';

                            $row[] = $contactName;break;
                        case "contactId": $row[] = '<input type="checkbox" class="checkClass " name="chk[]" id="chk" value="' . $aRow[$col] . '" />';
                            break;
                        case "actionId": $row[] = '<a href="' . base_url() . 'contact/addContact/' . $aRow[$col] . '" class="modalPopup" data-title="Edit Contact" data-class="fbPop submitOffer2 addLocation" data-backdrop="static"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;&nbsp;<a href="' . base_url() . 'contact/deleteContactPopUp/' . $aRow[$col] . '" class="modalPopup" data-title="Delete Contact" data-class="fbPop submitOffer2 addLocation"><i class="fa fa-remove" ></i></a>';
                            break;
                        default: $row[] = ($aRow[$col] == "") ? "--No Value--" : $aRow[$col];
                            break;
                    }
                }

                $output['aaData'][] = $row;
            }


            echo json_encode($output);
        } else {
            redirect(base_url());
        }
    }

 public function assignContactListingResponse($type = NULL, $app_group_id = NULL) {
        ($type = 'type') ? $type = NULL : $type = $type;
        $login = $this->administrator_model->front_login_session();
        $subQuery = '';

        if ($login->active != 0) {

            /* Array of database columns which should be read and sent back to DataTables. Use a space where
             * you want to insert a non-database field (for example a counter or static image)
             */
            $select = "external_users.external_user_id contactId,  external_users.user_image contactImage, CONCAT(firstname,' ',lastname) contactName, email, phoneNumber, app_group_name, GROUP_CONCAT( persona_users.name ) personaName, external_users.external_user_id actionId";
            $aColumns = array('contactId', 'contactImage', 'contactName', 'email', 'phoneNumber', 'app_group_name',  'personaName');


            $sTable = 'external_users';
            $iDisplayStart = $this->input->get_post('iDisplayStart', true);
            $iDisplayLength = $this->input->get_post('iDisplayLength', true);
            $iSortCol_0 = $this->input->get_post('iSortCol_0', true);
            $iSortingCols = $this->input->get_post('iSortingCols', true);
            $sSearch = $this->input->get_post('sSearch', true);
            $sEcho = $this->input->get_post('sEcho', true);

            // Paging
            if (isset($iDisplayStart) && $iDisplayLength != '-1') {
                $this->db->limit($this->db->escape_str($iDisplayLength), $this->db->escape_str($iDisplayStart));
            }



            // Ordering
            if (isset($iSortCol_0)) {
                for ($i = 0; $i < intval($iSortingCols); $i++) {
                    $iSortCol = $this->input->get_post('iSortCol_' . $i, true);
                    $bSortable = $this->input->get_post('bSortable_' . intval($iSortCol), true);
                    $sSortDir = $this->input->get_post('sSortDir_' . $i, true);

                    if ($bSortable == 'true') {
                        $this->db->order_by($aColumns[intval($this->db->escape_str($iSortCol))], $this->db->escape_str($sSortDir));
                    }
                }
            }

            /*
             * Filtering

             * NOTE this does not match the built-in DataTables filtering which does it
             * word by word on any field. It's possible to do here, but concerned about efficiency
             * on very large tables, and MySQL's regex functionality is very limited
             */


            if (isset($sSearch) && !empty($sSearch)) {
                $whereString = '';
                for ($i = 0; $i < count($aColumns); $i++) {
                    $bSearchable = $this->input->get_post('bSearchable_' . $i, true);

                    // Individual column filtering
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] != 'contactId' && $aColumns[$i] != 'contactName' && $aColumns[$i] != 'contactImage' && $aColumns[$i] != 'personaName' && $aColumns[$i] != 'actionId') {

                        //   $this->db->or_like($aColumns[$i], $this->db->escape_like_str($sSearch));
                        $whereString .= " $aColumns[$i]  LIKE '%$sSearch%' OR ";
                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'contactName') {
                        // $this->db->or_like("concat_ws(' ',firstname,lastname)", $this->db->escape_like_str($sSearch));

                        $whereString .= " concat_ws(' ',firstname,lastname)  LIKE '%$sSearch%' OR ";
                    }

                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'personaName') {
                        // $this->db->where("find_in_set('$sSearch',persona_users.name)  > 0");
                        // $this->db->or_like("persona_users.name", $this->db->escape_like_str($sSearch));

                        $whereString .= " persona_users.name  LIKE '%$sSearch%'";
                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'contactId') {

                    }
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'contactImage') {

                    }
                }
            }


            $this->db->_protect_identifiers = false;
            $this->db->select('SQL_CALC_FOUND_ROWS ' . $select, false);
            $this->db->from('external_users');
            $this->db->join('app_group', 'app_group.app_group_id = external_users.app_group_id', 'left');
//            $this->db->join('persona_assign_contacts', 'persona_assign_contacts.external_user_id = external_users.external_user_id', 'left');
//            $this->db->join('persona_users', "FIND_IN_SET(persona_users.persona_user_id,persona_assign_contacts.persona_user_id)", "left");

            $this->db->join('persona_users', "FIND_IN_SET(persona_users.persona_user_id,external_users.persona_ids)", "left");

            if ($login->usertype == 8) {
                if ($app_group_id != NULL) {
                    $this->db->where(['app_group.businessId' => $login->businessId, "external_users.isDelete" => 0, 'external_users.app_group_id' => $app_group_id]);
                } else {
                    $this->db->where(['app_group.businessId' => $login->businessId, "external_users.isDelete" => 0]);
                }
            } else {
                $userDetails = $this->groupapp_model->getUserGroup($login->user_id);
                $arr_apps = array();
                foreach ($userDetails as $usD) {
                    array_push($arr_apps, $usD->app_group_id);
                }
                $appsId = implode(",", $arr_apps);

                if ($app_group_id != NULL) {
                    $appsId = $app_group_id;
                }

                $this->db->where_in('app_group.app_group_id', $appsId);
                $this->db->where(["external_users.isDelete" => 0]);
            }

            if (isset($sSearch) && !empty($sSearch)) {
                $this->db->where("($whereString)");
            }

            $this->db->group_by('external_users.external_user_id');
            // $this->db->order_by('GROUP_CONCAT(persona_users.name)', 'desc');
            $rResult = $this->db->get();

            $this->db->select('FOUND_ROWS() AS found_rows');
            $iFilteredTotal = $this->db->get()->row()->found_rows;

            // Total data set length
            // $iTotal = $this->db->count_all($sTable);
            // Output
            $output = array(
                'sEcho' => intval($sEcho),
                'iTotalRecords' => $iFilteredTotal,
                'iTotalDisplayRecords' => $iFilteredTotal,
                'aaData' => array()
            );



            foreach ($rResult->result_array() as $aRow) {

                $row = array();
                foreach ($aColumns as $col) {
                    $onError = "onError=this.onerror=null;this.src='" . base_url() . "upload/profile/medium/user.png'; ';";
                    switch ($col) {
                        case "contactImage":
                            if ($aRow[$col] == '') {
                                $image = "<img src='" . base_url() . "upload/profile/thumbnail/user.png' alt='userImage' style='width: 65%; border-radius: 10%;' $onError />";
                            } else {
                                $image = "<img src='" . base_url() . "upload/profile/thumbnail/" . $aRow[$col] . "' alt='userImage' style='width: 65%; border-radius: 10%;' $onError />";
                            }
                            $row[] = $image;
                            break;
                        case "contactName": $row[] = '<a href="'.base_url().'appUser/timeline/'.$aRow['actionId'].'">'.$aRow[$col].'</a>';break;
                        case "contactId": $row[] = '<input type="checkbox" class="checkClass " name="chk[]" id="chk" value="' . $aRow[$col] . '" />';
                            break;
                        default: $row[] = ($aRow[$col] == "") ? "--No Value--" : $aRow[$col];
                            break;
                    }
                }

                $output['aaData'][] = $row;
            }


            echo json_encode($output);
        } else {
            redirect(base_url());
        }
    }


    function deleteContactPopUp($contact_id) {
        $data['contact_id'] = $contact_id;
        $this->load->view('3.1/contact_delete', $data);
    }

    function deleteContact() {

        $update['external_user_id'] = $_POST['contact_id'];
        $update['isDelete'] = 1;
        $this->contact_model->updateContact($update);
        echo 1;
    }

    function deleteContactsPopUp() {

        $this->load->view('3.1/multiple_contacts_delete');
    }

    function getConatctDetails() {
        $email = $_POST['email'];
        $external_user_id = $_POST['external_user_id'];

        $where['email'] = $email;
        $where['isDelete'] = 0;
        $select = "*";
        $result = $this->contact_model->getConatctDetails($select, $where, '', $external_user_id);
        if (count($result) > 0) {
            echo 'exits';
        } else {
            echo "not exits";
        }
    }

    function showErrorPopUp($message = NULL) {
        $data['ok'] = 0;
        if ($_GET) {
            $message = $_GET['message'];
        } else {
            $message = '';
        }
        //echo $message; die;
        $data['message'] = '';
        if ($message == 1) {
            $data['message'] = "Please select contact(s) ";
        } else if ($message == 2) {
            $data['message'] = "Some errors occurred. Please try again";
        } else if ($message == 3) {
            $data['message'] = "Selected contacts has been export successfully!";
            $data['ok'] = 1;
        } else if ($message == 4) {
            $data['message'] = "Please Connect to Hubspot First! ";
        } else if ($message == 5) {
            $data['message'] = "Please Connect to Salesforce First! ";
        } else if ($message == 6) {
            $data['message'] = "Some errors occurs. Please try again to export timeline! ";
        } else if ($message == 7) {
            $data['message'] = "Timeline successfully export to Hubspot !! ";
        } else {
            $data['message'] = '';
        }
        $data['ok'] = 1;
        //	echo $data['message'] ; die;
        $this->load->view('3.1/contact_atertPopUp', $data);
    }

    function hubspotAuthenticaion($portalId) {
        $endpoint = 'https://app.hubspot.com/auth/authenticate?client_id=' . HUBCLIENTID . '&portalId=' . $portalId . '&redirect_uri=http://localhost/hurree/trunk/contact/exportToHubsort&scope=offline';

        $ch = @curl_init();
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = @curl_exec($ch);
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch);
        @curl_close($ch);
        // 		echo "curl Errors: " . $curl_errors;
        // 		echo "\nStatus code: " . $status_code;
        // 		echo "\nResponse: " . $response;
        return $status_code;
    }

    function exportToHubsort() {
        $login = $this->administrator_model->front_login_session();

        $data = $_POST['data'];
        $arr_externalUserId = explode(",", $data);

        $arr = array();
        foreach ($arr_externalUserId as $userId) {
            $where['external_user_id'] = $userId;
            $where['isDelete'] = 0;
            $select = "*";
            $oneUser = $this->contact_model->getConatctDetails($select, $where);

            $dt = array(
                array(
                    'property' => 'email',
                    'value' => $oneUser->email
                ),
                array(
                    'property' => 'firstname',
                    'value' => $oneUser->firstName
                ),
                array(
                    'property' => 'lastname',
                    'value' => $oneUser->lastName
                ),
                array(
                    'property' => 'phone',
                    'value' => $oneUser->phoneNumber
                )
            );

            $arr_Onecontact = array(
                'email' => $oneUser->email,
                'properties' => $dt
            );



            array_push($arr, $arr_Onecontact);
        }


        //get hubspot details
        $hubDetails = $this->gethubspotDetails($login);

        $portalId = $this->session->userdata('hubPortalId');
        $hwhere ['userid'] = $login->user_id;
        $hwhere['portalId'] = $portalId;
        $hwhere ['isActive'] = 1;
        $select = 'userHubSpotId, refresh_token, portalId, accress_token';
        $hubDetails = $this->hubSpot_model->getHubSpotDetails($select, $hwhere);

// // 		echo $this->db->last_query(); die;
// 		$refresh_token = $hubDetails->refresh_token;
// 		$grandType = "refresh_token";
// 		$this->refreshTokenOauth($refresh_token, $grandType);

        $status = $this->hubspotAuthenticaion($portalId);
        if ($status == 302) {

            $post_json = json_encode($arr);
            //echo $post_json; die;
            //$hapikey = HUBAPIKEY;
            //$endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch?hapikey=' . $hapikey;
            $endpoint = 'https://api.hubapi.com/contacts/v1/contact/batch?access_token=' . $hubDetails->accress_token . '&portalId=' . $hubDetails->portalId;
            $ch = @curl_init();
            @curl_setopt($ch, CURLOPT_POST, true);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
            @curl_setopt($ch, CURLOPT_URL, $endpoint);
            @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = @curl_exec($ch);
            $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errors = curl_error($ch);
            @curl_close($ch);
            // 		echo "curl Errors: " . $curl_errors;
            // 		echo "\nStatus code: " . $status_code;
            // 		echo "\nResponse: " . $response;

            echo $status_code;
        } else {
            echo "error";
        }
    }

    function gethubspotDetails($login) {
        $portalId = $this->session->userdata('hubPortalId');

        $hwhere ['userid'] = $login->user_id;
        $hwhere['portalId'] = $portalId;
        $hwhere ['isActive'] = 1;
        $select = 'userHubSpotId, refresh_token, portalId, accress_token';
        $hubDetails = $this->hubSpot_model->getHubSpotDetails($select, $hwhere);
        return $hubDetails;
    }

    function refreshTokenOauth($refresh_token, $grandType) {
        $endpoint = 'https://api.hubapi.com/auth/v1/refresh?refresh_token=' . $refresh_token . '&client_id=' . HUBCLIENTID . '&grant_type=' . $grandType;
        //echo $endpoint; die;
        $ch = @curl_init();
        //@curl_setopt($ch, CURLOPT_POST, true);
        //@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
        @curl_setopt($ch, CURLOPT_URL, $endpoint);
        @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = @curl_exec($ch);
        $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errors = curl_error($ch);
        @curl_close($ch);
        echo "curl Errors: " . $curl_errors;
        echo "\nStatus code: " . $status_code;
        echo "\nResponse: " . $response;
        die;
    }

    /**
     *  expot new sing up user data to aaron hubspot account
     */
    function exportContactToHubspot() {

        $status = hubspotAuthenticaion(HUBPORTALID);
        echo $status;
    }

    function exportToSalesforce() {
        $login = $this->administrator_model->front_login_session();

        $where['userId'] = $login->user_id;
        $select = 'refresh_token, instance_url, access_token';
        $salesDetails = $this->hubSpot_model->getSalesUserDetails($select, $where);
        //print_r($salesDetails); die;
        $refresh_token = $salesDetails->refresh_token;
        $status = refreshSalesToken($refresh_token, $login->user_id);

        // if refreshed
        if ($status == 200) {

            //
            //	show_accounts($salesDetails->instance_url, $salesDetails->access_token);

            $data = $_POST['data'];
            $arr_externalUserId = explode(",", $data);

            foreach ($arr_externalUserId as $userId) {
                $arr_where['external_user_id'] = $userId;
                $arr_where['isDelete'] = 0;
                $select = "*";
                $oneUser = $this->contact_model->getConatctDetails($select, $arr_where);
                $arr_user = array(
                    "FirstName" => $oneUser->firstName,
                    "LastName" => $oneUser->lastName,
                    "Email" => $oneUser->email,
                    "Phone" => $oneUser->phoneNumber,
                );

                //print_r($arr_user );

                if ($oneUser->salesID == '') {
                    $contactstatus = create_contact($arr_user, $salesDetails->instance_url, $salesDetails->access_token, $userId);
                    if ($contactstatus != 201) {
                        $contactstatus = create_contact($arr_user, $salesDetails->instance_url, $salesDetails->access_token, $userId);
                    }
                } else {
                    $salesID = $oneUser->salesID;
                    $contactstatus = update_contact($arr_user, $salesDetails->instance_url, $salesDetails->access_token, $salesID);
                    if ($contactstatus != 204) {
                        $contactstatus = update_contact($arr_user, $salesDetails->instance_url, $salesDetails->access_token, $salesID);
                    }
                }
            }
            echo '200';
        } else {
            echo $status;
        }
    }

    function exportTimeline() {
        // define Variables and Array
        $vid = '';
        // Get get Value
        $email = $_POST['email'];

        $login = $this->administrator_model->front_login_session();

        $select = "external_users.external_user_id, external_users.firstName, external_users.lastName, external_users.email, events.* ";

        $where['email'] = $email;
        $where['events.isDelete'] = 0;
        $where['isExportHubspot'] = 0;
        $events = $this->contact_model->getOneUserEvent($select, $where, 1);
        ///print_r($events )		; die;
        //get hubspot details
        $hubDetails = $this->gethubspotDetails($login);
        $access_token = $hubDetails->accress_token;
        $portalId = $hubDetails->portalId;

        // Get Contact Vid form hubspot
        $vid = getOneContactDetailsHibspot($email, $access_token, $portalId);

        if ($vid === 'false') {

            $select = '*';
            $whr ['email'] = $email;
            $whr['isDelete'] = 0;
            $oneUser = $this->contact_model->getConatctDetails($select, $whr);
            $dt = array
                (
                "properties" => Array
                    (
                    "0" => array
                        (
                        "property" => 'email',
                        "value" => $oneUser->email
                    ),
                    "1" => array
                        (
                        "property" => 'firstname',
                        "value" => $oneUser->firstName
                    ),
                    "2" => array
                        (
                        "property" => 'lastname',
                        "value" => $oneUser->lastName
                    ),
                    "3" => array
                        (
                        "property" => 'phone',
                        "value" => $oneUser->phoneNumber
                    )
                )
            );
            $vid = createOneContactHubspot($dt, $access_token, $portalId);
        }
        if (count($events) > 0) {
            foreach ($events as $evt) {
                if ($evt->eventType == 'contactNote') {
                    $eventText = $evt->eventName . ': ' . $evt->noteText;
                } else {
                    $eventText = $evt->eventName;
                }
                $request = array
                    (
                    "engagement" => array(
                        "active" => true,
                        "type" => "NOTE"
                    ),
                    "associations" => array
                        (
                        "contactIds" => Array
                            (
                            "0" => $vid
                        ),
                        "companyIds" => Array
                        (
                        ),
                        "dealIds" => Array
                        (
                        )
                    ),
                    "metadata" => array
                        (
                        "body" => $evt->firstName . ' ' . $evt->lastName . ' ' . $eventText
                    )
                );
                $eResponce = createEnganementHubspot($vid, $request, $access_token, $portalId);

                if ($eResponce == true) {
                    $save['eventId'] = $evt->eventId;
                    $save['isExportHubspot'] = 1;
                    $this->contact_model->saveEvent($save);
                }
                $external_user_id = $evt->external_user_id;
            }
        } else {
            $eResponce = 1;
        }

        echo $eResponce;

        //redirect('appUser/timeline/'.$external_user_id);
    }

    public function addPersona($persona_user_id = NULL) {
        $login = $this->administrator_model->front_login_session();
        if (!empty($persona_user_id)) {
            $personaUser = $this->brand_model->getPersonaByPersonaId($login->businessId, $persona_user_id);
            if (count($personaUser) > 0) {
                $data['personaUser'] = $personaUser;
                $data['countries'] = $this->country_model->get_countries();
                $data['user_image'] = '';
                $this->load->view('3.1/edit_persona', $data);
                $this->load->view('3.1/imageUpload');
            } else {
                $data['countries'] = $this->country_model->get_countries();
                $data['user_image'] = '';
                $this->load->view('3.1/add_persona', $data);
                $this->load->view('3.1/imageUpload');
            }
        } else {
            $data['countries'] = $this->country_model->get_countries();
            $data['user_image'] = '';
            $this->load->view('3.1/add_persona', $data);
            $this->load->view('3.1/imageUpload');
        }
    }

    public function savePersona() {
        $login = $this->administrator_model->front_login_session();
        $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
        $role = isset($_POST['role']) ? $_POST['role'] : '';
        $age = isset($_POST['age']) ? $_POST['age'] : '';
        $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
        $education = isset($_POST['education']) ? $_POST['education'] : '';
        $salary = isset($_POST['salary']) ? $_POST['salary'] : '';
        $family_group = isset($_POST['family_group']) ? $_POST['family_group'] : '';
        $interest_group = isset($_POST['interest_group']) ? $_POST['interest_group'] : '';
        $relationship_status = isset($_POST['relationship_status']) ? $_POST['relationship_status'] : '';
        $language = isset($_POST['language']) ? $_POST['language'] : '';
        $location = isset($_POST['location']) ? $_POST['location'] : '';
        $goals = isset($_POST['goals']) ? $_POST['goals'] : '';
        $challenges = isset($_POST['challenges']) ? $_POST['challenges'] : '';
        $marketing_message = isset($_POST['marketing_message']) ? $_POST['marketing_message'] : '';
        //$user_image     = isset($_POST['user_image']) ? $_POST['user_image']:'';

        $persona_user_id = '';

        if (!empty($_POST['persona_user_id'])) {
            $persona_user_id = $_POST['persona_user_id'];
        }

        if (!empty($interest_group) && count($interest_group) > 0) {
            $interest_group = implode(",", $interest_group);
        }

        if (!empty($language) && count($language) > 0) {
            $language = implode(",", $language);
        }

        /* if(empty($interest_group)){
          $interest_group = $language = 'array';
          } */

        $save = array(
            'persona_user_id' => $persona_user_id,
            'businessId' => $login->businessId,
            'createdBy' => $login->user_id,
            'name' => $full_name,
            'role' => $role,
            'age' => $age,
            'gender' => $gender,
            'education' => $education,
            'salary' => $salary,
            'family' => $family_group,
            'interests' => $interest_group,
            'releationship_status' => $relationship_status,
            'language' => $language,
            'location' => $location,
            'goals' => $goals,
            'challenges' => $challenges,
            'marketing_message' => $marketing_message,
            'isActive' => 1,
            'isDelete' => 0,
            'createdDate' => date('Y-m-d H:i:s'),
            'modifiedDate' => date('Y-m-d H:i:s')
        );

        /* if(empty($user_image)){
          $save = array_merge($save,array('user_image' => $user_image));
          } */

        $last_insertId = $this->brand_model->savePersona($save);
        if ($last_insertId > 0) {
            if (empty($save['persona_user_id'])) {
                $this->session->set_userdata('persona_user_id', $last_insertId);
            }

            $success = 'success';
            $statusMessage = 'Persona User added successfully!';

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage,
                    "persona_user_id" => "$last_insertId"
                )
            );
        } else {
            $success = 'error';
            $statusMessage = "Error occoured. Please try again.";

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit();
        //print_r($_POST); exit;
    }

    public function personaUser() {
        $this->session->unset_userdata('persona_user_id', 0);
    }

    public function personaTimeline() {
        $login = $this->administrator_model->front_login_session();
        if (isset($login->active) && $login->active != 0) {
            $header['page'] = 'contact';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;

            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $groups = $this->groupapp_model->getUserGroups($login->user_id);
                if (count($groups) > 0) {
                    foreach ($groups as $group) {
                        $groupArray[] = $group->app_group_id;
                    }
                } else {
                    $groupArray = '';
                }
                $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
            }

            $data = array();
            $data['personaUserCountry'] = array();
            $persona_user_id = $this->uri->segment(3);
            $personaUser = $this->brand_model->getPersonaByPersonaId($login->businessId, $persona_user_id);
            if (count($personaUser) > 0) {
                $data['personaUser'] = $personaUser;
                $country = $this->country_model->getOneCountry('country_id', $personaUser->location);
                if (count($country) > 0) {
                    $data['personaUserCountry'] = $country[0]['country_name'];
                }
                $contactUsers = $this->brand_model->getAssignContactsByPersonaId($persona_user_id);
                $contactUserIdsArr = array();

                if (count($contactUsers) > 0) {
                    foreach ($contactUsers as $users) {
                        if (!in_array($users->external_user_id, $contactUserIdsArr)) {
                            $contactUserIdsArr[] = $users->external_user_id;
                        }
                    }
                    $contactUserIds = implode(',', $contactUserIdsArr);
                    //$pushNotificationUsers = $this->brand_model->getPushViewExternalUsersById($contactUserIds);
                    //echo "<pre>"; print_r($pushNotificationUsers); exit;
                    /*
                      $campaignIds = array();
                      $pushUserIds = array();
                      $data['countCampaignIds'] = 0;
                      if(count($pushNotificationUsers) > 0){
                      foreach($pushNotificationUsers as $pushUser){
                      if(!in_array($pushUser->campaign_id,$campaignIds)){
                      $campaignIds[] = $pushUser->campaign_id;
                      }
                      if(!in_array($pushUser->external_user_id,$pushUserIds)){
                      $pushUserIds[] = $pushUser->external_user_id;
                      }
                      }

                      if(count($campaignIds) > 0){
                      $data['countCampaignIds'] = count($campaignIds);
                      }

                      if(count($pushUserIds) > 0){
                      $data['countPushUserIds'] = count($pushUserIds);
                      }
                      } */
                    $data['countPushUserIds'] = 0;
                    $data['maximumViewCampaignName'] = '';
                    $maximumViewCampaign = $this->brand_model->countPushViewExternalUsersById($contactUserIds);
                    $totalViewUsers = 0;
                    if (count($maximumViewCampaign) > 0) {
                        foreach ($maximumViewCampaign as $key => $campaign) {
                            if ($key == 0) {
                                $platform = $campaign->platform;
                                $campaign_id = $campaign->campaign_id;
                                $campaignName = $campaign->campaignName;
                                $totalViewUsers = $campaign->totalCampaignUsers;
                            }
                        }
                        $countPushSendUserIds = 0;
                        if ($platform == "android" || $platform == "iOS") {
                            $countPushSendUserIds = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                        } else if ($platform == "email") {
                            $countPushSendUserIds = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                        }
                        if (count($countPushSendUserIds) > 0) {
                            $countPushSendUserIds = count($countPushSendUserIds);
                        }
                        else
                            $countPushSendUserIds = 0;
                        if($countPushSendUserIds != 0)
                            $data['countPushUserIds'] = sprintf("%.2f", $totalViewUsers * 100 / $countPushSendUserIds);
                        else
                            $data['countPushUserIds'] = 0;

                        $data['maximumViewCampaignName'] = $campaignName;
                    }
                    $pushViewNotificationsArr = $this->brand_model->countUsersByPushViewTime($contactUserIds);
                    //echo "<pre>"; print_r($pushViewNotificationsArr); exit;
                    $viewNotificationUserArr = array();
                    $viewNotificationPercentageArr = array();
                    if (count($pushViewNotificationsArr) > 0) {
                        foreach ($pushViewNotificationsArr as $arr) {
                            if (!in_array($arr->notification_timezone_view_time, $viewNotificationUserArr)) {
                                $platform = $arr->platform;
                                $campaign_id = $arr->campaign_id;
                                $countPushSendUserIds = 0;
                                $totalViewUsers = 0;
                                if ($platform == "android" || $platform == "iOS") {
                                    $countPushSendUserIds = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                                } else if ($platform == "email") {
                                    $countPushSendUserIds = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                                }
                                if (count($countPushSendUserIds) > 0) {
                                    $countPushSendUserIds = count($countPushSendUserIds);
                                }
                                else
                                {
                                    $countPushSendUserIds = 0;
                                }

                                if($countPushSendUserIds != 0)
                                    $totalViewUsers = sprintf("%.2f", $arr->totalCampaignUsers * 100 / $countPushSendUserIds);
                                else
                                    $totalViewUsers = 0;
                                //$totalViewUsers = sprintf("%.2f", $arr->totalCampaignUsers * 100 / $countPushSendUserIds);
                                $viewNotificationUserArr[] = array('campaignName' => $arr->campaignName, 'viewtime' => date("Y-m-d", strtotime($arr->notification_timezone_view_time)), 'totalUsers' => $arr->totalCampaignUsers);
                                $viewNotificationPercentageArr[] = array('campaignName' => $arr->campaignName, 'viewtime' => date("Y-m-d", strtotime($arr->notification_timezone_view_time)), 'totalUsers' => $totalViewUsers);
                            }
                        }
                    }
                    $data['viewNotificationUserArr'] = $viewNotificationUserArr;
                    $data['viewNotificationPercentageArr'] = $viewNotificationPercentageArr;
                    // echo '<pre>'; print_r($viewNotificationPercentageArr); exit;
                } else {
                    $data['countPushUserIds'] = 0;
                    $data['maximumViewCampaignName'] = '';
                    $data['viewNotificationUserArr'] = array();
                    $data['viewNotificationPercentageArr'] = array();
                }
            } else {
                $persona_demo_users = $this->brand_model->getPersonaDemoUser();
                if (count($persona_demo_users) > 0) {
                    $data['personaUser'] = $persona_demo_users;
                }
                $country = $this->country_model->getOneCountry('country_id', $persona_demo_users->location);
                //print_r($country);exit;
                if (count($country) > 0) {
                    $data['personaUserCountry'] = $country[0]['country_name'];
                }
                $data['countPushUserIds'] = 0;
                $data['maximumViewCampaignName'] = '';
                $data['viewNotificationUserArr'] = array();
                $data['viewNotificationPercentageArr'] = array();
            }
            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/personaTimeline', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

    public function assignContacts($app_group_id = NULL) {
        $data = array();
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'contact';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;

            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $groups = $this->groupapp_model->getUserGroups($login->user_id);
                if (count($groups) > 0) {
                    foreach ($groups as $group) {
                        $groupArray[] = $group->app_group_id;
                    }
                } else {
                    $groupArray = '';
                }

                $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
            }

            if ($login->usertype == 8) {
                $data['allgroups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $data['allgroups'] = $this->groupapp_model->getUserGroup($login->user_id);
            }
            $data['app_group_id'] = '/' . $app_group_id;
            //echo '<pre>'; print_r($data['allgroups']); die;

            $this->load->view('3.1/assign_contacts_popup', $data);
        } else {
            redirect(base_url());
        }
    }

    public function saveAssignContacts() {
        $login = $this->administrator_model->front_login_session();
        $contactIds = isset($_POST['contactIds']) ? $_POST['contactIds'] : '';
        $persona_user_id = isset($_POST['persona_user_id']) ? $_POST['persona_user_id'] : '';
        $personaUsers = isset($_POST['personaUsers']) ? $_POST['personaUsers'] : '';

        if (!empty($personaUsers) && count($personaUsers) > 0) {
            $save = array(
                'isDelete' => 0,
                'createdDate' => date('Y-m-d H:i:s')
            );
            foreach ($contactIds as $key => $id) {
	        $personaIds = array();
                foreach ($personaUsers as $user) {
		    array_push($personaIds,$user);
                    $save = array_merge($save, array('external_user_id' => $id, 'persona_user_id' => $user));
                    $last_insertId = $this->brand_model->saveAssignContacts($save);
                } //print_r($personaIds); exit;
 	      $this->contact_model->updatePersonaList($id,$personaIds);
            }
        } else {
            $save = array(
                'persona_user_id' => $persona_user_id,
                'isDelete' => 0,
                'createdDate' => date('Y-m-d H:i:s')
            );

	    $external_userIds = array();
            if (!empty($contactIds) && count($contactIds) > 0) {
                foreach ($contactIds as $ids) {
                    $save = array_merge($save, array('external_user_id' => $ids));
                    $last_insertId = $this->brand_model->saveAssignContacts($save);
	 	    $savePersona = array('persona_ids' => $persona_user_id);
	 	    $this->contact_model->updatePersona($ids,$savePersona);
                }
            }
        }

        if ($last_insertId > 0) {
            $this->session->unset_userdata('persona_user_id', 0);

            $success = 'success';
            $statusMessage = 'Contacts Assign successfully!';

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage,
                    "persona_user_id" => "$last_insertId"
                )
            );
        } else {
            $success = 'error';
            $statusMessage = "Error occoured. Please try again.";

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage
                )
            );
        }

        echo json_encode($response);
        exit();
        //print_r($_POST); exit;
    }

    public function clonePersona($persona_user_id = false) {
        if (isset($_POST['persona_user_id'])) {
            $persona_user_id = $_POST['persona_user_id'];
            $personaUserId = $this->brand_model->getPersona($persona_user_id);
            if (count($personaUserId) > 0) {
                $lastRow = $this->brand_model->getPersonaById($personaUserId);
                if (count($lastRow) > 0) {
                    $update = array('persona_user_id' => $lastRow->persona_user_id, 'name' => $lastRow->name . ' ' . '(Clone)', 'createdDate' => date('Y-m-d H:i:s'));
                    $this->brand_model->savePersona($update);
                    //echo $lastRow->id; //exit;

                    $this->session->set_flashdata('personaCloneSuccess', 'Persona clone added successfully!');

                    $success = 'success';
                    $statusMessage = 'Persona clone added successfully!';

                    $response = array(
                        "data" => array(
                            "status" => $success,
                            "statusMessage" => $statusMessage,
                            "persona_user_id" => "$lastRow->persona_user_id"
                        )
                    );
                } else {
                    $success = 'error';
                    $statusMessage = "Error occoured. Please try again.";

                    $response = array(
                        "data" => array(
                            "status" => $success,
                            "statusMessage" => $statusMessage
                        )
                    );
                }
            } else {
                $success = 'error';
                $statusMessage = "Error occoured. Please try again.";

                $response = array(
                    "data" => array(
                        "status" => $success,
                        "statusMessage" => $statusMessage
                    )
                );
            }
            echo json_encode($response);
            exit();
        } else {
            $data['persona_user_id'] = $persona_user_id;
            $this->load->view('3.1/clone_persona', $data);
        }
    }

    public function deletePersona($persona_user_id = false) {
        if (isset($_POST['persona_user_id'])) {
            $persona_user_id = $_POST['persona_user_id'];
            $personaRow = $this->brand_model->getPersonaById($persona_user_id);
            if (count($personaRow) > 0) {
                $update = array('persona_user_id' => $personaRow->persona_user_id, 'isDelete' => 1, 'modifiedDate' => date('Y-m-d H:i:s'));
                $this->brand_model->savePersona($update);
                //echo $lastRow->id; //exit;

                //Get External users which have assigned perosona id of request persona_user_id
                $assignPersonaUsers = $this->brand_model->getExterusersByPersonaId($persona_user_id);

                if(count($assignPersonaUsers) > 0){
                    foreach($assignPersonaUsers as $externalUser){
                    $external_user_id = $externalUser->external_user_id;
                    //echo $externalUser->persona_ids;
                    $persona_ids_array = explode(',',$externalUser->persona_ids);
                    if(($key = array_search($persona_user_id, $persona_ids_array)) !== false) {
                        unset($persona_ids_array[$key]);
                    }
                    $persona_ids = implode(",",$persona_ids_array);
                    $data[] = array(
                            'external_user_id' => $external_user_id,
                            'persona_ids' => $persona_ids
                        );
                    }
                    // Update Persona_id for external users in external_users table
                    $this->brand_model->updateExternalUsersPersona($data);

                    //Update persona_assign_contacts table for persona_user_id column
                    $updateAssignContact['persona_user_id'] = $persona_user_id;
                    $updateAssignContact['isDelete'] = 1;
                    $this->brand_model->updatePersonaAssignContacts($updateAssignContact);
                }


                $success = 'success';
                $statusMessage = 'Persona deleted successfully!';

                $response = array(
                    "data" => array(
                        "status" => $success,
                        "statusMessage" => $statusMessage
                    )
                );
            } else {
                $success = 'error';
                $statusMessage = "Error occoured. Please try again.";

                $response = array(
                    "data" => array(
                        "status" => $success,
                        "statusMessage" => $statusMessage
                    )
                );
            }
            echo json_encode($response);
            exit();
        } else {
            $data['persona_user_id'] = $persona_user_id;
            $this->load->view('3.1/delete_persona', $data);
        }
    }

    /* created by yogesh. Date: 7 september 2016 */

    public function addXls($userId = NULL) {

        $this->load->view('3.1/add_xls');
        $this->load->view('3.1/imageUpload');
    }

    public function saveXls($userId = NULL) {
        ini_set("max_execution_time", 0);
        ini_set("memory_limit", "-1");
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '1024M');
        $login = $this->administrator_model->front_login_session();
        $this->load->library('upload');
        if (!empty($_FILES['file']['name'])) {
            $config['upload_path'] = './upload/files';
            $config['allowed_types'] = 'xlsx|xls';
            $this->upload->initialize($config);
            if ($this->upload->do_upload('file')) {
                $fileData = $this->upload->data();
                $file_type =  $fileData['file_ext'];
                $file_name = $fileData['file_name'];
                $file = './upload/files/' . $file_name;
                $save['isDelete'] = "0";
                $save['isActive'] = "1";
                $save['status'] = 0;
                $save['createdDate'] = date('YmdHis');
                $save['userId'] = $login->user_id;
                $save['businessId'] = $login->businessId;
                $save['businessId'] = $login->businessId;
                $save['name'] = $file_name;
                $save['file_type'] = $file_type;
                $last_id = $this->contact_model->savecontactFile($save);
                if ($last_id > 0) {
                    echo 1;
                }
            }
        }
    }

    public function saveXlsOldBackup($userId = NULL) {
        ini_set("max_execution_time", 0);
        ini_set("memory_limit", "-1");
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        $login = $this->administrator_model->front_login_session();
        $this->load->library('upload');
        $this->load->library('excel');
        if (!empty($_FILES['file']['name'])) {
            $config['upload_path'] = './upload/files';
            $config['allowed_types'] = 'xlsx|xls';
            $this->upload->initialize($config);
            if ($this->upload->do_upload('file')) {
                $fileData = $this->upload->data();
                $file_name = $fileData['file_name'];
                $file = './upload/files/' . $file_name;

                try {
                    $inputFileType = PHPExcel_IOFactory::identify($file);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($file);
                } catch (Exception $e) {
                    die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
                }

                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $count = 0;
                for ($row = 2; $row <= $highestRow; $row++) {
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                    if ($this->groupapp_model->checkGroupId($rowData[0][1], $login->businessId)) {
                        $save['isDelete'] = "0";
                        $save['createdDate'] = date('YmdHis');
                        $save['app_group_id'] = $rowData[0][1];
                        $save['firstName'] = $rowData[0][2];
                        $save['lastName'] = $rowData[0][3];
                        $save['email'] = $rowData[0][4];
                        $save['phoneNumber'] = $rowData[0][5];
                        $save['exteranal_app_user_id'] = 0;
                        $save['app_group_apps_id'] = 0;
                        $save['user_image'] = '';
                        $save['gender'] = '';
                        $save['date_of_birth'] = '';
                        $save['salesID'] = '';
                        $save['latitude'] = '';
                        $save['longitude'] = '';
                        $save['loginDate'] = date('YmdHis');
                        $save['timezone'] = '';
                        $save['external_user_id'] = '';
                        $last_id = $this->contact_model->saveContact($save);
                        $count++;
                    }
                }
                if ($count > 0) {
                    echo 1;
                }die;
            }
        }
    }

    public function addCsv($userId = NULL) {

        $this->load->view('3.1/add_csv');
        $this->load->view('3.1/imageUpload');
    }

    public function saveCsv($userId = NULL) {
        $login = $this->administrator_model->front_login_session();
        $this->load->library('upload');
        $image_path = '';
        if (!empty($_FILES['file']['name'])) {

            if (0 < $_FILES['file']['error']) {
                echo 'Error: ' . $_FILES['file']['error'] . '<br>';
            } else {


$path_parts = pathinfo($_FILES["file"]["name"]);
$image_path = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];


                move_uploaded_file($_FILES['file']['tmp_name'], './upload/files/' . $image_path);
            }

            $file = './upload/files/' . $_FILES['file']['name'];
            $save['isDelete'] = "0";
                $save['isActive'] = "1";
                $save['status'] = 0;
                $save['createdDate'] = date('YmdHis');
                $save['userId'] = $login->user_id;
                $save['businessId'] = $login->businessId;
                $save['businessId'] = $login->businessId;
                $save['name'] = $image_path;
                $save['file_type'] = '.csv';
                $last_id = $this->contact_model->savecontactFile($save);
                if ($last_id > 0) {
                    echo 1;
                }

        }
        die;
    }

    public function saveCsvOldBackup($userId = NULL) {
        $login = $this->administrator_model->front_login_session();
        $this->load->library('upload');
        $this->load->library('excel');
        if (!empty($_FILES['file']['name'])) {

            if (0 < $_FILES['file']['error']) {
                echo 'Error: ' . $_FILES['file']['error'] . '<br>';
            } else {
                move_uploaded_file($_FILES['file']['tmp_name'], './upload/files/' . $_FILES['file']['name']);
            }

            $file = './upload/files/' . $_FILES['file']['name'];
            try {
                $inputFileType = PHPExcel_IOFactory::identify($file);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($file);
            } catch (Exception $e) {
                die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }

            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $count = 0;
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                if ($this->groupapp_model->checkGroupId($rowData[0][1], $login->businessId)) {
                    $save['isDelete'] = "0";
                    $save['createdDate'] = date('YmdHis');
                    $save['app_group_id'] = $rowData[0][1];
                    $save['firstName'] = $rowData[0][2];
                    $save['lastName'] = $rowData[0][3];
                    $save['email'] = $rowData[0][4];
                    $save['phoneNumber'] = $rowData[0][5];
                    $save['exteranal_app_user_id'] = 0;
                    $save['app_group_apps_id'] = 0;
                    $save['user_image'] = '';
                    $save['external_user_id'] = '';
                    $save['gender'] = '';
                    $save['date_of_birth'] = '';
                    $save['salesID'] = '';
                    $save['latitude'] = '';
                    $save['longitude'] = '';
                    $save['loginDate'] = date('YmdHis');
                    $save['timezone'] = '';
                    $this->contact_model->saveContact($save);
                    $count ++;
                }
            }
            if ($count > 0) {
                echo 1;
            }
        }
        die;
    }

    public function viewAppGroup() {
        $login = $this->administrator_model->front_login_session();
        $data['allgroups'] = $this->groupapp_model->getGroups($login->businessId);
        $this->load->view('3.1/view_appgroup', $data);
        $this->load->view('3.1/imageUpload');
    }

    function getPersonaSuggestionMsg() {
        if (isset($_POST['persona_user_id'])) {
            $persona_user_id = $_POST['persona_user_id'];
            $contactUsers = $this->brand_model->getAssignContactsByPersonaId($persona_user_id);
            $contactUserIdsArr = array();

            if (count($contactUsers) > 0) {
                foreach ($contactUsers as $users) {
                    if (!in_array($users->external_user_id, $contactUserIdsArr)) {
                        $contactUserIdsArr[] = $users->external_user_id;
                    }
                }
                $contactUserIds = implode(',', $contactUserIdsArr);

                $maximumViewCampaignName = '';
                $totalViewUsers = 0;
                $maximumViewCampaign = $this->brand_model->countPushViewExternalUsersById($contactUserIds);
                //echo "<pre>"; print_r($maximumViewCampaign); exit;
                if (count($maximumViewCampaign) > 0) {
                    foreach ($maximumViewCampaign as $key => $campaign) {
                        if ($key == 0) {
                            $platform = $campaign->platform;
                            $campaign_id = $campaign->campaign_id;
                            $campaignName = $campaign->campaignName;
                            $totalViewUsers = $campaign->totalCampaignUsers;
                        }
                    }
                    $countPushSendUserIds = 0;
                    if ($platform == "android" || $platform == "iOS") {
                        $countPushSendUserIds = $this->brand_model->countCampaignSendHistoryByCampaignId($campaign_id);
                    } else if ($platform == "email") {
                        $countPushSendUserIds = $this->brand_model->countEmailCampaignSendHistoryByCampaignId($campaign_id);
                    }
                    if (count($countPushSendUserIds) > 0) {
                        $countPushSendUserIds = count($countPushSendUserIds);
                    }
                    $totalViewUsers = sprintf("%.2f", $totalViewUsers * 100 / $countPushSendUserIds);
                    $maximumViewCampaignName = $campaignName;
                }
            } else {
                $totalViewUsers = 0;
                $maximumViewCampaignName = '';
            }

            $success = 'success';
            if (!empty($maximumViewCampaignName)) {
                $maximumViewCampaignName = " for $maximumViewCampaignName.";
            } else {
                $maximumViewCampaignName = '.';
            }
            $statusMessage = "$totalViewUsers% of this persona clicked through on an offer$maximumViewCampaignName <br /><br /><strong>Why not try sending a similar offer?</strong>";

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage
                )
            );
        } else {
            $success = 'error';
            $statusMessage = "Error occoured. Please try again.";

            $response = array(
                "data" => array(
                    "status" => $success,
                    "statusMessage" => $statusMessage
                )
            );
        }
        echo json_encode($response);
        exit();
    }

    function deleteMultipleContacts() {

        $contactIds = isset($_POST['contactIds']) ? $_POST['contactIds'] : '';

        if (!empty($contactIds) && count($contactIds) > 0) {
            $success = 'success';
            $statusMessage = 'Contacts deleted successfully';
            foreach ($contactIds as $ids) {

                $update = array('external_user_id' => $ids, 'isDelete' => 1);
                $this->contact_model->savecontact($update);
            }
        } else {
            $success = 'error';
            $statusMessage = 'Error occured while deleting contacts';
        }

        $response = array(
            "status" => $success,
            "statusMessage" => $statusMessage
        );

        echo json_encode($response);
    }

    public function getTable() {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $aColumns = array('user_Id', 'firstname', 'lastname');

        // DB table to use
        $sTable = 'users';
        //

        $iDisplayStart = $this->input->get_post('iDisplayStart', true);
        $iDisplayLength = $this->input->get_post('iDisplayLength', true);
        $iSortCol_0 = $this->input->get_post('iSortCol_0', true);
        $iSortingCols = $this->input->get_post('iSortingCols', true);
        $sSearch = $this->input->get_post('sSearch', true);
        $sEcho = $this->input->get_post('sEcho', true);

        // Paging
        if (isset($iDisplayStart) && $iDisplayLength != '-1') {
            $this->db->limit($this->db->escape_str($iDisplayLength), $this->db->escape_str($iDisplayStart));
        }

        // Ordering
        if (isset($iSortCol_0)) {
            for ($i = 0; $i < intval($iSortingCols); $i++) {
                $iSortCol = $this->input->get_post('iSortCol_' . $i, true);
                $bSortable = $this->input->get_post('bSortable_' . intval($iSortCol), true);
                $sSortDir = $this->input->get_post('sSortDir_' . $i, true);

                if ($bSortable == 'true') {
                    $this->db->order_by($aColumns[intval($this->db->escape_str($iSortCol))], $this->db->escape_str($sSortDir));
                }
            }
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        if (isset($sSearch) && !empty($sSearch)) {
            for ($i = 0; $i < count($aColumns); $i++) {
                $bSearchable = $this->input->get_post('bSearchable_' . $i, true);

                // Individual column filtering
                if (isset($bSearchable) && $bSearchable == 'true') {
                    $this->db->or_like($aColumns[$i], $this->db->escape_like_str($sSearch));
                }
            }
        }

        // Select Data
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $aColumns)), false);
        $rResult = $this->db->get($sTable);

        // Data set length after filtering
        $this->db->select('FOUND_ROWS() AS found_rows');
        $iFilteredTotal = $this->db->get()->row()->found_rows;

        // Total data set length
        $iTotal = $this->db->count_all($sTable);

        // Output
        $output = array(
            'sEcho' => intval($sEcho),
            'iTotalRecords' => $iTotal,
            'iTotalDisplayRecords' => $iFilteredTotal,
            'aaData' => array()
        );



        foreach ($rResult->result_array() as $aRow) {

            $row = array();

            foreach ($aColumns as $col) {
                $row[] = $aRow[$col];
            }

            $output['aaData'][] = $row;
        }



        echo json_encode($output);
    }

    public function showTable() {
        $data = array();
        $login = $this->administrator_model->front_login_session();
        if ($login->active != 0) {
            $header['page'] = 'contact';
            $header['userid'] = $login->user_id;
            $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
            $header['usertype'] = $login->usertype;
            $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);

            $cookies = $this->input->cookie('group', true);
            if (!empty($cookies)) {
                $cookie = $this->input->cookie('group', true);
                $cookie_group = explode(",", $cookie);
            } else {
                $cookie_group = '';
            }

            $header['cookie_group'] = $cookie_group;


            if ($login->usertype == 8) {
                $header['groups'] = $this->groupapp_model->getGroups($login->businessId);
            } else {
                $groups = $this->groupapp_model->getUserGroups($login->user_id);
                if (count($groups) > 0) {
                    foreach ($groups as $group) {

                        $groupArray[] = $group->app_group_id;
                    }
                } else {
                    $groupArray = '';
                }

                $header['groups'] = $this->groupapp_model->getUserGroupData($groupArray);
            }

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/contactDataTable');
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }

}
