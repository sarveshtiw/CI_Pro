<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Permission extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model(array('user_model','administrator_model','permission_model','role_model'));
        $this->output->set_header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
           $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
           $this->output->set_header('Cache-Control: post-check=0, pre-check=0',false);
           $this->output->set_header('Pragma: no-cache');

    }

    // fetch all permission for listing

     function index(){
        $login = $this->administrator_model->front_login_session();
        $header['page']  = 'permission';
        $header['userid']  = $login->user_id;
        $header['loggedInUser'] = $this->user_model->getOneUser($login->user_id);
        $header['usertype'] = $login->usertype;
        $header['campaignPermission'] = $this->permission_model->getCampaignPermission($login->user_id);
        $arr['roles.businessId'] = $login->businessId;
        $arr['roles.isDelete'] = 0;

        $roles = $this->role_model->getRoles($arr);
//echo '<pre>'; print_r($roles); exit;
        foreach ($roles as $role){
         $permissionIds = $role->permissionId;
         $permissionIds = explode(',',$permissionIds);
         $permissions = $this->permission_model->getPermissionName($permissionIds);
         $newpermission = array();
         foreach($permissions as $permission){
           $newpermission[] =  $permission->permissionName;
         }
         $newpermission1 = implode(',', $newpermission);
         $role->permissions = $newpermission1;
        }

        $data['roles'] = $roles;
       
        $this->load->view('inner_header3.0',$header);
        $this->load->view('permissionListing',$data);
        $this->load->view('inner_footer3.0');
    }

    function createRole(){
        $where['isDelete'] = 0;
        $where['version'] = "3.0";
        $data['permissions'] = $this->permission_model->getPermissions($where);

        $this->load->view('addPermission',$data);
    }
    function saveRole(){

       $login = $this->administrator_model->front_login_session();
       $createdBy = $login->user_id;
       $permissions = $_POST['permissions'];
       $permissions = implode(',', $permissions);

       $roleName = $_POST['roleName'];
       $permissionIds = $permissions;
       $businessId = $login->businessId;
       $result = $this->role_model->saveRole($roleName,$permissionIds,$createdBy,$businessId);
       if($result){
          echo '1';
       }else{
          echo '0';
       }
    }

    // get only one permission to edit
    function editRole($roleId=false,$createdBy=false){

       if(!empty($_POST)){

       $login = $this->administrator_model->front_login_session();
       $createdBy = $login->user_id;
       $permissions = $_POST['permissions'];
       $roleId = $_POST['roleId'];
       $permissions = implode(',', $permissions);

       $roleName = $_POST['roleName'];
       $permissionIds = $permissions;
     
       $result = $this->role_model->editRole($roleName,$permissionIds,$createdBy,$roleId);
       if($result){
          echo '1';
       }else{
          echo '0';
       }
       }
       else{

       $arr['permissionRoleConnection.roleId'] = $roleId;
       $arr['permissionRoleConnection.createdBy'] = $createdBy;
       $roleDetails = $this->role_model->getRole($arr);

         $permissionIds = $roleDetails->permissionId;
         $permissionIds = explode(',',$permissionIds);
         $permission = $this->permission_model->getPermission($permissionIds);
         $roleDetails->permission = $permission;

       $data['roleDetails'] = $roleDetails;
       $where['isDelete'] = 0;
       $where['version'] = '3.1';
       $data['permissions'] = $this->permission_model->getPermissions($where);
       $this->load->view('addPermission',$data);
       }

    }

    function deleteRole($roleId= false){
        if(!empty($_POST)){
           $where['role_id'] = $_POST['id'];
           $result =  $this->role_model->deleteRole($where);
           echo $result; exit;

        }else{

          $data['roleId'] = $roleId;
          $this->load->view('deletePermission',$data);
        }
    }

}
