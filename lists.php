<?php

if (!defined('BASEPATH'))
    exit(
            'No direct script access allowed');

class Lists extends CI_Controller {

    public function __construct() {
        parent::__construct();

        //$this->load->helper(array('salesforce_helper', 'hubspot_helper'));
        //$this->load->library(array('form_validation', 'pagination'));
        $this->load->model(array('administrator_model', 'contact_model', 'permission_model', 'groupapp_model', 'hubSpot_model', 'brand_model', 'country_model', "referfriend_model", 'lists_model'));
//        $login = $this->administrator_model->front_login_session();
//        if (isset($login->active) && $login->active == 0) {
//            redirect(base_url());
//        }
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');
    }

    function index($app_group_id = NULL) {

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

            if (count($persona_demo_users) >
                    0) {
                $data['persona_demo_users'] = $persona_demo_users;
            }
            $data['loginUerLists'] = $this->lists_model->getAllListsOfLoginUser($login->user_id);

            //echo '<pre>'; print_r($data); die;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/lists', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }
    
    function listListingResponse() {
        ($type = 'type') ? $type = NULL : $type = $type;
        $login = $this->administrator_model->front_login_session();
        $subQuery = '';

        if ($login->active != 0) {

            /* Array of database columns which should be read and sent back to DataTables. Use a space where
             * you want to insert a non-database field (for example a counter or static image)
             */
            $select = "list_id, name,   type, createdDate, list_id actionId";
            $aColumns = array('list_id', 'name', 'type','createdDate', "actionId");
            $sTable = 'lists';
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
                $whereString =  '';
                for ($i = 0; $i < count($aColumns); $i++) {
                    $bSearchable = $this->input->get_post('bSearchable_' . $i, true);
                    
                    // Individual column filtering
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] != 'actionId') {
                      //  $this->db->or_like($aColumns[$i], $this->db->escape_like_str($sSearch)); 
                        $whereString .= " $aColumns[$i]  LIKE '%$sSearch%' OR ";
                    }
                     if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'actionId') {
                        
                    }
                    
                }
            }           


            $this->db->_protect_identifiers = false;            
            $this->db->select('SQL_CALC_FOUND_ROWS ' . $select, false); 
            $this->db->from($sTable); 
            $this->db->where(['userId' => $login->businessId, "isDelete" =>0]);  
            
            if (isset($sSearch) && !empty($sSearch)) {
                $whereString = rtrim($whereString," OR ");
                $this->db->where("($whereString)");  
            }
            
           
            $rResult = $this->db->get();  
 //          echo $this->db->last_query(); die;
            
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
                  
                    switch ($col) {                        
                        case "actionId": $listId = $aRow[$col] ;  $row[] = '<a href="' . base_url() . 'lists/deleteListPopUp/' . $aRow["actionId"]  . '" class="modalPopup" data-title="Delete List" data-class="fbPop submitOffer2 addLocation"><i class="fa fa-remove" ></i></a>';
                            break;
                       case "isActive": $row[] = ($aRow[$col]==1) ? "Active": " Not Active";
                            break;
                        case "createdDate": $row[] = date("Y-m-d" , strtotime($aRow[$col]));
                            break;
                        case "name": $row[] = '<a href="' . base_url() . 'lists/editList/edit/' . $aRow["actionId"] . '" >'.$aRow[$col].'</a>';
                            break;
                        case "list_id":  unset($aRow["list_id"]);
                            break;
                          
                            
                           default: $row[] = ($aRow[$col] == "") ?  "--No Value--" : $aRow[$col] ;
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

    function newList($app_group_id = NULL) {

        $data = array();
        $login = $this->administrator_model->front_login_session();

        // echo $login->user_id; die;
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
                $data['allgroups'] = $this->groupapp_model->getUserGroup(
                        $login->user_id);
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

            $data['push_campaigns_notification'] = $this->brand_model->getPushCampaignsByBusinessIdForList($login->businessId, NULL, NULL);

            $data['push_campaigns_email'] = $this->brand_model->getEmailCampaignsByBusinessIdForList($login->businessId, NULL, NULL);

            $data['users_list'] = $this->lists_model->getAllListsOfLoginUser($login->businessId);
//                       echo "<pre>";
//print_r($data['push_campaigns_notification']);
//echo "</pre>";die;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/newList', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }
    
    public function editList($app_group_id = NULL,$listId = NULL) {
        
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
                $data['allgroups'] = $this->groupapp_model->getUserGroup(
                        $login->user_id);
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

            $data['push_campaigns_notification'] = $this->brand_model->getPushCampaignsByBusinessId($login->businessId, NULL, NULL);

            $data['push_campaigns_email'] = $this->brand_model->getEmailCampaignsByBusinessId($login->businessId, NULL, NULL);

            $data['current_list'] = $this->lists_model->getListDataById($listId);
            $data['users_list'] = $this->lists_model->getAllListsOfLoginUser($login->businessId);
            
            
            
 //        echo '<pre>'; print_r( $data); die;

            $this->load->view('3.1/inner_headerBrandUser', $header);
            $this->load->view('3.1/editList', $data);
            $this->load->view('3.1/inner_footerBrandUser');
        } else {
            redirect(base_url());
        }
    }
    
    public function contactListingResponse($type = NULL, $app_group_id = NULL) { 
        ($type = 'type') ? $type = NULL : $type = $type;
        $login = $this->administrator_model->front_login_session();
        $subQuery = '';
        if ($login->active != 0) {       
        $login = $this->administrator_model->front_login_session();
        $select = "user_image, firstname, lastname, email, phoneNumber, app_group.app_group_name appGroupName";          
            $aColumns = array('user_image', 'firstname','lastname','email','phoneNumber','appGroupName');
            $sTable = 'external_users';
            $iDisplayStart = $this->input->get_post('iDisplayStart', true);
            $iDisplayLength = $this->input->get_post('iDisplayLength', true);
            $iSortCol_0 = $this->input->get_post('iSortCol_0', true);
            $iSortingCols = $this->input->get_post('iSortingCols', true);
            $sSearch = $this->input->get_post('sSearch', true);
            $sEcho = $this->input->get_post('sEcho', true); 
            $firstVariable = $this->input->get_post('first_variable', true); 
           
        $condition = '';
        $condition1 = '';
        $condition2 = '';
        if (isset($firstVariable['mainArray'])) {
            $mainArray = json_decode($firstVariable['mainArray']);
            if (!empty($mainArray)) {
                foreach ($mainArray as $subArray) {
                    if (!empty($mainArray)) {

                        foreach ($subArray as $value) {

                            if ($value->mainProperty == 'Contact_Property') {
                                
                                switch ($value->mainPropertyValue) {
                                    case "firstName":
                                        switch ($value->subProperty) {
                                            case "is equal to": $condition1.= ' AND external_users.firstName = "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "not equal to": $condition1.= ' AND external_users.firstName  != "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "start with": $condition1.= ' AND external_users.firstName  like "' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "end with": $condition1.= ' AND external_users.firstName  like "%' . $value->subPropertyvalue . '"';
                                                break;
                                            case "contains": $condition1.= ' AND external_users.firstName  like "%' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "does not contain": $condition1.= ' AND external_users.firstName  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                                break;
                                        }

                                        break;
                                    case "lastName":
                                        switch ($value->subProperty) {
                                            case "is equal to": $condition1.= ' AND external_users.lastName = "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "not equal to": $condition1.= ' AND external_users.lastName  != "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "start with": $condition1.= ' AND external_users.lastName  like "' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "end with": $condition1.= ' AND external_users.lastName  like "%' . $value->subPropertyvalue . '"';
                                                break;
                                            case "contains": $condition1.= ' AND external_users.lastName  like "%' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "does not contain": $condition1.= ' AND external_users.lastName  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                                break;
                                        }
                                        break;
                                    case "email":
                                        switch ($value->subProperty) {
                                            case "is equal to": $condition1.= ' AND external_users.email = "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "not equal to": $condition1.= ' AND external_users.email  != "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "start with": $condition1.= ' AND external_users.email  like "' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "end with": $condition1.= ' AND external_users.email  like "%' . $value->subPropertyvalue . '"';
                                                break;
                                            case "contains": $condition1.= ' AND external_users.email  like "%' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "does not contain": $condition1.= ' AND external_users.email  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                                break;
                                        }
                                        break;
                                    case "phoneNumber":
                                        switch ($value->subProperty) {
                                            case "is equal to": $condition1.= ' AND external_users.phoneNumber = "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "not equal to": $condition1.= ' AND external_users.phoneNumber  != "' . $value->subPropertyvalue . '"';
                                                break;
                                            case "start with": $condition1.= ' AND external_users.phoneNumber  like "' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "end with": $condition1.= ' AND external_users.phoneNumber  like "%' . $value->subPropertyvalue . '"';
                                                break;
                                            case "contains": $condition1.= ' AND external_users.phoneNumber  like "%' . $value->subPropertyvalue . '%"';
                                                break;
                                            case "does not contain": $condition1.= ' AND external_users.phoneNumber  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                                break;
                                        }
                                        break;
                                    case "appGroup":
                                        $appGroupData = $this->lists_model->getAppGroup($value->subPropertyvalue);  //$value->subProperty
                                    //print_r($appGroupData); die;
                                        //$app = array();
                                        $i=0;
                                        if(count($appGroupData) > 0){
                                            foreach ($appGroupData as $group) {

                                                $groupArray[] = $group->app_group_id;
                                            }
                                        
                                    }else{
                                        $groupArray = '';
                                    }
                                    //print_r($groupArray); die;
                                        if($groupArray != ''){
                                            switch($value->subProperty){
                                            case "is equal to": $condition1.= ' AND external_users.app_group_id IN ('.implode(",",$groupArray).')';      
                                                break;
                                            case "not equal to": $condition1.= ' AND external_users.app_group_id  NOT IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "start with": $condition1.= ' AND external_users.app_group_id IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "end with": $condition1.= ' AND external_users.app_group_id IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "contains": $condition1.= ' AND external_users.app_group_id IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "does not contain": $condition1.= ' AND external_users.app_group_id  NOT IN ('.implode(",",$groupArray).')';
                                                break;
                                        }
                                        break;
                                        }
                                    
                                        
                                }
                            }

                            if ($value->mainProperty == 'Email_list') {
                                switch ($value->subProperty) { 
                                    case "Contact sent email":
                                        $EmailSentContacts = $this->brand_model->getExternalUserFromBrandEmailCampaignsInfo($value->subPropertyvalue);
                                        $oneDimensionalArrayOfContacts = array_unique(array_map('current', $EmailSentContacts));
                                        
                                        if(!empty($oneDimensionalArrayOfContacts)){
                                        $string = implode(",", $oneDimensionalArrayOfContacts);
                                        $condition1.= ' AND external_users.external_user_id  IN (' . $string . ')';  
                                        }                                        
                                        
                                        break;
                                }
                            }

                            if ($value->mainProperty == 'List_membership') {
                                switch ($value->subProperty) {
                                    case "Contact is member of list":
                                        $string = $this->datafind($value->subPropertyvalue);
                                        $condition1.= ' AND external_users.external_user_id  IN (' . $string . ')';
                                        break;
                                    case "Contact is not member of list":
                                        $string = $this->datafind($value->subPropertyvalue);
                                        $condition1.= ' AND external_users.external_user_id  NOT IN (' . $string . ') AND businessId  = '.$login->businessId;
                                        break;
                                }
                            }

                            if ($value->mainProperty == 'Push_Notification') { 
                                switch ($value->subProperty) { 
                                    case "push notification sent": 
                                        $responseOfNotificationSendHistory = $this->brand_model->getExternalUserFromNotificationSendHistory($value->subPropertyvalue, 'sent');
                                        $oneDimensionalArrayOfPushNotification = array_unique(array_map('current', $responseOfNotificationSendHistory));
                                        $string = implode(",", $oneDimensionalArrayOfPushNotification);
                                        if (trim($string) != '') {
                                            $condition1.= ' AND external_users.external_user_id  IN (' . $string . ')';
                                        } else {
                                            $condition1.= ' AND 1= 0';
                                        }
                                        break;
                                    case "push notification view":
                                        $responseOfNotificationSendHistory = $this->brand_model->getExternalUserFromNotificationSendHistory($value->subPropertyvalue, 'view');
                                        $oneDimensionalArrayOfPushNotification = array_unique(array_map('current', $responseOfNotificationSendHistory));
                                        $string = implode(",", $oneDimensionalArrayOfPushNotification);
                                        if (trim($string) != '') {
                                            $condition1.= ' AND external_users.external_user_id  IN (' . $string . ')';
                                        } else {
                                            $condition1.= ' AND 1= 0';
                                        }
                                        break;
                                }
                            }
                        }
                        if($condition1 !=''){
                           $condition2.= '(' . ltrim($condition1, ' AND ') . ')' . " OR "; 
                        }                       

                        $condition1 = '';
                    }
                }
            }
        } 

        if ($condition2 != "") {
            $condition = rtrim($condition2, ' OR ');
            $condition = ' AND (' . $condition.')';
        }
        


        
            

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
                $whereString =  '';
                for ($i = 0; $i < count($aColumns); $i++) {
                    $bSearchable = $this->input->get_post('bSearchable_' . $i, true);
                    
                    // Individual column filtering
                    if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] != 'appGroupName') {
                      //  $this->db->or_like($aColumns[$i], $this->db->escape_like_str($sSearch)); 
                        $whereString .= " $aColumns[$i]  LIKE '%$sSearch%' OR ";
                    }
                     if (isset($bSearchable) && $bSearchable == 'true' && $aColumns[$i] == 'appGroupName') {
                        
                         $whereString .= " app_group.app_group_name  LIKE '%$sSearch%' OR ";
                    }
                    
                }
            } 
            
            $this->db->_protect_identifiers = false;            
            $this->db->select('SQL_CALC_FOUND_ROWS ' . $select, false); 
            $this->db->from('external_users');
            $this->db->join('app_group', 'app_group.app_group_id = external_users.app_group_id', 'left');

           
            if ($login->usertype == 8) {
                if ($app_group_id != NULL) {
                    $subQuery = ' AND EU.app_group_id = ' . $app_group_id;
                }
                $where = " app_group.businessId=" . $login->businessId . "  AND external_users.isDelete =0" . $subQuery . $condition;
                
                //echo $where; die("1"); 
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
                $where = ' app_group.app_group_id IN (' . $appsId . ') AND  external_users.isDelete =0 ' . $condition;
                
              //  echo $where; die("2"); 
            }
            
            $this->db->where("($where)"); 
           
              if (isset($sSearch) && !empty($sSearch)) {
                $whereString = rtrim($whereString, ' OR ');
                $this->db->where("($whereString)");  
            }
            
           
           
            $rResult = $this->db->get(); 
         //    echo $this->db->last_query(); die;
            // Data set length after filtering
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
                        case "user_image":
                            if ($aRow[$col] == '') {
                                $image = "<img src='" . base_url() . "upload/profile/thumbnail/user.png' alt='userImage' style='width: 45%; border-radius: 10%;' $onError />";
                            } else {
                                $image = "<img src='" . base_url() . "upload/profile/thumbnail/" . $aRow[$col] . "' alt='userImage' style='width: 45%; border-radius: 10%;' $onError />";
                            }
                            $row[] = $image;
                            break;                  
                        default: $row[] = ($aRow[$col] == "") ?  "--No Value--" : $aRow[$col] ;
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

    public function saveList() {

        //save the data in database; 
        $data = array();
        $login = $this->administrator_model->front_login_session();
        $listName = $_POST['listName'];
        $condition = json_encode($_POST['condition']);
        $count = $_POST['count'];
        $type = $_POST['type'];
        $date = date('YmdHis');
// $json = '{"PROFILEID":"I%2d6X6UL61PJASE","PROFILESTATUS":"ActiveProfile","TIMESTAMP":"2016%2d11%2d04T12%3a57%3a57Z","CORRELATIONID":"d473eb925ef74","ACK":"Success","VERSION":"109%2e0","BUILD":"24616352"}';
        $data = array('userId' => $login->user_id, 'businessId' => $login->businessId, "type" => $type, "name" => $listName, "count" => $count, "json_data" => $condition, "isActive" => 1, "IsDelete" => 0, "createdDate" => $date, "modifiedDate" => $date);
        $result = $this->lists_model->insertList($data);

        if ($result > 0) {
            $response = array('success' => TRUE, 'message' => "Insert successfully");
        } else {
            $response = array('success' => FALSE, 'message' => "Email id already exist");
        }
        print json_encode($response);
        exit;
    }
    
    public function saveEditList(){
         //save the data in database; 
        $data = array();
        $login = $this->administrator_model->front_login_session();
      
        $listId = $_POST['listId'];
        $condition = json_encode($_POST['condition']);
        $count = $_POST['count'];        
        $date = date('YmdHis');
        $whereValue = array('userId' => $login->user_id,'listId' =>$listId);
        $data = array("count" => $count, "json_data" => $condition, "modifiedDate" => $date);
        $result = $this->lists_model->updateList($data, $whereValue);

        if ($result > 0) {
            $response = array('success' => TRUE, 'message' => "Insert successfully");
        } else {
            $response = array('success' => FALSE, 'message' => "Email id already exist");
        }
        print json_encode($response);
        exit;  
    }

    public function deleteList() {
        $result = $this->lists_model->deleteListUsingListId($_POST['listId']);
        echo 1;
        die;
    }

    public function deleteListPopUp($listId) {
        $data['listId'] = $listId;
        $this->load->view('deleteList', $data);
    }
    
    public function datafind($listId = NULL) {        
        $login = $this->administrator_model->front_login_session();
        $EmailSentContacts = $this->lists_model->getListDataById($listId);
//        echo "<pre>";
//       print_r($login);
//        print_r($EmailSentContacts);
//        echo "</pre>"; die;
        $decodedJsonConditon = json_decode($EmailSentContacts['json_data']);
        $mainArray = json_decode($decodedJsonConditon->mainArray);
        $condition = '';
        $condition2 = '';
        $condition1 = '';
        if (!empty($mainArray)) {
            foreach ($mainArray as $subArray) {

                if (!empty($mainArray)) {

                    foreach ($subArray as $value) {

                        if ($value->mainProperty == 'Contact_Property') {
                            switch ($value->mainPropertyValue) {
                                case "firstName":
                                    switch ($value->subProperty) {
                                        case "is equal to": $condition1.= ' AND EU.firstName = "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "not equal to": $condition1.= ' AND EU.firstName  != "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "start with": $condition1.= ' AND EU.firstName  like "' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "end with": $condition1.= ' AND EU.firstName  like "%' . $value->subPropertyvalue . '"';
                                            break;
                                        case "contains": $condition1.= ' AND EU.firstName  like "%' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "does not contain": $condition1.= ' AND EU.firstName  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                            break;
                                    }

                                    break;
                                case "lastName":
                                    switch ($value->subProperty) {
                                        case "is equal to": $condition1.= ' AND EU.lastName = "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "not equal to": $condition1.= ' AND EU.lastName  != "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "start with": $condition1.= ' AND EU.lastName  like "' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "end with": $condition1.= ' AND EU.lastName  like "%' . $value->subPropertyvalue . '"';
                                            break;
                                        case "contains": $condition1.= ' AND EU.lastName  like "%' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "does not contain": $condition1.= ' AND EU.lastName  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                            break;
                                    }
                                    break;
                                case "email":
                                    switch ($value->subProperty) {
                                        case "is equal to": $condition1.= ' AND EU.email = "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "not equal to": $condition1.= ' AND EU.email  != "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "start with": $condition1.= ' AND EU.email  like "' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "end with": $condition1.= ' AND EU.email  like "%' . $value->subPropertyvalue . '"';
                                            break;
                                        case "contains": $condition1.= ' AND EU.email  like "%' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "does not contain": $condition1.= ' AND EU.email  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                            break;
                                    }
                                    break;
                                case "phoneNumber":
                                    switch ($value->subProperty) {
                                        case "is equal to": $condition1.= ' AND EU.phoneNumber = "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "not equal to": $condition1.= ' AND EU.phoneNumber  != "' . $value->subPropertyvalue . '"';
                                            break;
                                        case "start with": $condition1.= ' AND EU.phoneNumber  like "' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "end with": $condition1.= ' AND EU.phoneNumber  like "%' . $value->subPropertyvalue . '"';
                                            break;
                                        case "contains": $condition1.= ' AND EU.phoneNumber  like "%' . $value->subPropertyvalue . '%"';
                                            break;
                                        case "does not contain": $condition1.= ' AND EU.phoneNumber  NOT LIKE "%' . $value->subPropertyvalue . '%"';
                                            break;
                                    }
                                    break;
                                
                                case "appGroup":
                                        $appGroupData = $this->lists_model->getAppGroup($value->subPropertyvalue);  //$value->subProperty
                                    //print_r($appGroupData); die;
                                        //$app = array();
                                        $i=0;
                                        if(count($appGroupData) > 0){
                                            foreach ($appGroupData as $group) {

                                                $groupArray[] = $group->app_group_id;
                                            }
                                        
                                    }else{
                                        $groupArray = '';
                                    }
                                    //print_r($groupArray); die;
                                        if($groupArray != ''){
                                            switch($value->subProperty){
                                            case "is equal to": $condition1.= ' AND EU.app_group_id IN ('.implode(",",$groupArray).')';      
                                                break;
                                            case "not equal to": $condition1.= ' AND EU.app_group_id  NOT IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "start with": $condition1.= ' AND EU.app_group_id IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "end with": $condition1.= ' AND EU.app_group_id IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "contains": $condition1.= ' AND EU.app_group_id IN ('.implode(",",$groupArray).')';
                                                break;
                                            case "does not contain": $condition1.= ' AND EU.app_group_id  NOT IN ('.implode(",",$groupArray).')';
                                                break;
                                        }
                                        break;
                                        }
                            }
                        }

                        if ($value->mainProperty == 'Email_list') {
                            switch ($value->subProperty) {
                                case "Contact sent email":
                                    $EmailSentContacts = $this->brand_model->getExternalUserFromBrandEmailCampaignsInfo($value->subPropertyvalue);
                                    $oneDimensionalArrayOfContacts = array_unique(array_map('current', $EmailSentContacts));
                                    $string = implode(",", $oneDimensionalArrayOfContacts);
                                    $condition1.= ' AND EU.external_user_id  IN (' . $string . ')';
                                    break;
                            }
                        }

                        if ($value->mainProperty == 'List_membership') {
                            switch ($value->subProperty) {
                                case "Contact is member of list":
                                    $string = $this->datafind($value->subPropertyvalue);
                                    $condition1.= ' AND EU.external_user_id  IN (' . $string . ')';
                                    break;
                                case "Contact is not member of list":
                                    $string = $this->datafind($value->subPropertyvalue);
                                    $condition1.= ' AND EU.external_user_id  NOT IN (' . $string . ')';
                                    break;
                            }
                        }

                        if ($value->mainProperty == 'Push_Notification') {
                            switch ($value->subProperty) {
                                case "push notification sent":
                                    $responseOfNotificationSendHistory = $this->brand_model->getExternalUserFromNotificationSendHistory($value->subPropertyvalue, 'sent');
                                    $oneDimensionalArrayOfPushNotification = array_unique(array_map('current', $responseOfNotificationSendHistory));
                                    $string = implode(",", $oneDimensionalArrayOfPushNotification);
                                    if (trim($string) != '') {
                                        $condition1.= ' AND EU.external_user_id  IN (' . $string . ')';
                                    } else {
                                        $condition1.= ' AND 1= 0';
                                    }
                                    break;
                                case "push notification view":
                                    $responseOfNotificationSendHistory = $this->brand_model->getExternalUserFromNotificationSendHistory($value->subPropertyvalue, 'view');
                                    $oneDimensionalArrayOfPushNotification = array_unique(array_map('current', $responseOfNotificationSendHistory));
                                    $string = implode(",", $oneDimensionalArrayOfPushNotification);
                                    if (trim($string) != '') {
                                        $condition1.= ' AND EU.external_user_id  IN (' . $string . ')';
                                    } else {
                                        $condition1.= ' AND 1= 0';
                                    }
                                    break;
                            }
                        }
                    }

                    $condition2.= '(' . ltrim($condition1, ' AND ') . ')' . " OR ";

                    $condition1 = '';
                }
            }
        }
        
        if ($condition2 != "") {
            $condition = rtrim($condition2, ' OR ');
            $condition =  $condition. ' AND businessId  = '.$login->businessId . ' AND EU.isDelete = 0';
        }
        
        $result = $this->contact_model->getExternalContactsDataByConditon($condition);
        $oneDimensionalArrayOfContacts = array_unique(array_map('current', $result));
        $string = implode(",", $oneDimensionalArrayOfContacts);
        return  $string;
    }
                                
}
