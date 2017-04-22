<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<!-- <script src="<?php echo base_url(); ?>assets/template/frontend/js/3.0/jquery-1.11.3.min.js"></script> -->
<script src="<?php echo base_url(); ?>assets/template/frontend/js/developer.js"></script>
<script src="<?php echo base_url(); ?>assets/template/frontend/js/3.0/jquery.sumoselect.min.js"></script>

<style>
#loading{
	background: rgba(204, 204, 204, 0.86) url("<?php echo base_url(); ?>assets/template/frontend/img/loader.svg") no-repeat center center !important;
	height: 100%;
	width: 100%;
	display: block;
	position: absolute;
	z-index: 99999999999;
	margin-left: -15px;
	bottom: 0;
}
</style>

</head>

<body>

<div id="loading" style="display:none;"></div>
<span id="success" style="color:green;"></span>
<div class="row">
<div class="col-xs-12">

<ul>
	<li>
    	<input type="text" placeholder="First Name" id="firstname" name="firstname" maxlength="20" />
    	<span id="error_firstname" style="position:relative;bottom: 12px; font-size:12px;color:#424141;"></span>
    </li>

    <li>
    	<input type="text" placeholder="Last Name" id="lastname" name="lastname" maxlength="20" />
    	<span id="error_lastname" style="position:relative;bottom: 12px; font-size:12px;color:#424141;"></span>
    </li>

    <li>
    	<input type="text" placeholder="Email" id="email" name="email" onkeyup="nospaces(this)" maxlength="40">
    	<span id="error_email" style="position:relative;bottom: 12px; font-size:12px;color:#424141;"></span>
    </li>

</ul>



<ul>
<li>
<input type="radio" class="usertype" name="usertype" value="8" checked> App Admin
<input type="radio" class="usertype" name="usertype" value="9"> App Sub User
</li>
</ul>
<div id="brandUser" style="display:none;">
<ul class=" location-box">
	<li>
    	<select class="SlectBox" id="select0">
    	<option value="">Select App Group</option>
    	<?php foreach($group as $grp){?>
    	<option value="<?php echo $grp->app_group_id; ?>"><?php echo $grp->app_group_name;?></option>
    	<?php }?>
    	</select>
    	<span id="error_group" style="position:relative;bottom: 12px; font-size:12px;color:#424141;"></span>
    </li>
    <li>Roles</li>
    <?php foreach($permissions as $permission){?>
    <li class="permissions">
    	<input class="permission" type="checkbox" id="<?php echo $permission->role_id;?>" value="<?php echo $permission->role_id;?>"><label style="padding-left:5px;" for="1"><?php echo $permission->roleName;?></label>
    </li>
    <?php } ?>
    <span id="error_permission" style="position:relative;bottom: 3px; font-size:12px;color:#424141;"></span>
    </ul>


<div id="addmoreGroup"> </div>

<ul class="customItems">
    <li>
    	<input type="submit" value="Assign More Group" onclick="addmoreGroup(this);"  class="btn green-btn">
    </li>
</ul>
</div>
<input type="hidden" id="baseurl" value="<?php echo base_url(); ?>" />
<input type="hidden" id="count" value="1" />
<div class="btnWrap"><input type="submit" value="Submit" class="btn purple-btn" onclick="return addappUser();" /></div>
</div>
</div>
<script>
$('.SlectBox').SumoSelect();
/* $('#select0').on('change', function() {
	  //alert( this.value );
	  if(this.value != ''){
		$(".permissions").css('display','block');
	  }else{
		  $('.permission').prop('checked', false);
		  $(".permissions").css('display','none');
	  }
	}); */
$(".usertype").on('change', function(){
	//debugger;
	if(this.value == '9'){
		$("#brandUser").css('display','block');
	}else{
		$("#brandUser").css('display','none');
	}
});
</script>

</body>
</html>
