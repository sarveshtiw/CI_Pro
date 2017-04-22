<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<style>
  #loading{
      background: rgba(204, 204, 204, 0.86) url("<?php echo base_url(); ?>assets/template/frontend/img/loader.svg") no-repeat center center !important;
      height: 100%;
      width: 100%;
      display: block;
      position: fixed;
      z-index: 99999999999;
      bottom: 0;
  }
</style>
</head>

<body>
<div id="loading" style="display:none;"></div>

<div class="row">
    <div class="col-sm-6">
      <input type="text" placeholder="Full Name" id="full_name" name="full_name" value="" >
      <span id="error_full_name" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <input type="text" placeholder="Role" id="role" name="role" value="" >
      <span id="error_role" style="color:#424141;font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row-->

<div class="row">
    <div class="col-sm-6">
      <select id="age" name="age" class="SlectBox">
        <?php $ageArray = array('0-10','10-18','18-24','25-34','35-44','45-55','55-64','65 or over'); ?>
        <option value="">Select Age</option>
        <?php foreach($ageArray as $age){ ?>
          <option value="<?php echo $age; ?>"><?php echo $age; ?></option>
        <?php } ?>
      </select>
      <span id="error_age" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <select id="gender" name="gender" class="SlectBox">
        <?php $genders = array('Male','Female'); ?>
        <option value="">Select Gender</option>
        <?php foreach($genders as $gender){ ?>
          <option value="<?php echo $gender; ?>"><?php echo $gender; ?></option>
        <?php } ?>
      </select>
      <span id="error_gender" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row -->

<div class="row">
    <div class="col-sm-6">
      <select id="education" name="education" class="SlectBox">
        <?php $education_groups = array('Primary','Secondary','Higher Secondary','Graduate','Post Graduate'); ?>
        <option value="">Select Education</option>
        <?php foreach($education_groups as $education_group){ ?>
          <option value="<?php echo $education_group; ?>"><?php echo $education_group; ?></option>
        <?php } ?>
      </select>
      <span id="error_education" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <select id="salary" name="salary" class="SlectBox">
        <?php $salaryArray = array('0-12,000','12,000- 15,000','15,000- 22,000','22,000- 30,000','30,000- 50,000','50,000- 80,000','80,000-100,000','100,000+'); ?>
        <option value="">Select Salary</option>
        <?php foreach($salaryArray as $salary){ ?>
          <option value="<?php echo $salary; ?>"><?php echo '$'.$salary; ?></option>
        <?php } ?>
      </select>
      <span id="error_salary" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row -->
<div class="row">
    <div class="col-sm-6">
      <select id="family_group" name="family_group" class="SlectBox">
        <?php $family_groups = array('Nuclear family','Single-parent family','Extended family'); ?>
        <option value="">Select Family</option>
        <?php foreach($family_groups as $family_group){ ?>
          <option value="<?php echo $family_group; ?>"><?php echo $family_group; ?></option>
        <?php } ?>
      </select>
      <span id="error_family_group" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <select id="interest_group" name="interest_group" class="SlectBox" multiple="multiple" placeholder="Select Interests">
        <?php $interest_groups = array('Animals','Art','Architecture','Dancing','Fashion','Film','Fitness','Food',
                                        'Gaming','Literature','Monuments & memorials','Music','Nature','Photography/ Videography',
                                        'Reading','Shopping','Sports'); ?>

        <?php foreach($interest_groups as $interest_group){ ?>
          <option value="<?php echo $interest_group; ?>"><?php echo $interest_group; ?></option>
        <?php } ?>
      </select>
      <span id="error_interest_group" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row -->
<div class="row">
    <div class="col-sm-6">
      <select id="relationship_status" name="relationship_status" class="SlectBox">
        <?php $relationship_status = array('Single','In a relationship','Engaged','Married','It’s complicated','In an open relationship','Widowed','Separated',
                                        'Divorced','In a civil union','In a domestic relationship'); ?>
        <option value="">Select Relationship Status</option>
        <?php foreach($relationship_status as $relationship){ ?>
          <option value="<?php echo $relationship; ?>"><?php echo $relationship; ?></option>
        <?php } ?>
      </select>
      <span id="error_relationship_status" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <select id="language" name="language" class="SlectBox" multiple="multiple" placeholder="Select Language">
        <?php $languages = array('Albanian','Arabic','Armenian','Azeri','Belarusian','Bengali','Bulgarian','Chinese','Croatian','Czech','Danish','English','Estonian','
Farsi Persian','Finnish','French','Georgian','German','Greek','Hebrew','Hindi','Hungarian','Italian','Japanese','Kazakh','Korean','Kyrgyz','
Latvian','Lithuanian','Malayalam','Marathi','Mongolian','Norwegian','Polish','Portuguese','Punjabi','Romanian','Russian','Serbian','Slovenian
Spanish','Swedish','Syriac','Tamil','Tatar','Telugu','Thai','Turkish','Ukrainian','Urdu','Uzbek','Vietnamese','Other'); ?>

          <?php foreach($languages as $language){ ?>
            <option value="<?php echo $language; ?>"><?php echo $language; ?></option>
          <?php } ?>
      </select>
      <span id="error_language" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row -->
<div class="row">
    <div class="col-sm-6">
      <select id="location" name="location" class="SlectBox">
        <option value="">Select Location</option>
        <?php foreach ($countries as $coun) { ?>
          <option value="<?php echo $coun->country_id; ?>"> <?php echo $coun->country_name; ?> </option>
        <?php } ?>
      </select>
      <span id="error_location" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <input type="text" placeholder="Goals" id="goals" name="goals" value="" >
      <span id="error_goals" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row -->
<div class="row">
    <div class="col-sm-6">
      <input type="text" placeholder="Challenges" id="challenges" name="challenges" value="" >
      <span id="error_challenges" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
      <input type="text" placeholder="Marketing Message" id="marketing_message" name="marketing_message" value="" >
      <span id="error_marketing_message" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
</div><!-- end of row-->


<div class="row">
  <div class="col-xs-12">
    <div class="addpersonBtnOuter">
      <!--<em id="profilePicFile"><img src="<?php echo base_url().'assets/template/frontend/img/camera-purple-big.png'; ?>" title ="Upload Image" /></em>-->
      <em id="profilePicFile" style="cursor: pointer; color: #424141; font-size:small; font-weight: bold;"><img src="<?php echo base_url(); ?>assets/template/frontend/img/camera-purple-big.png" title ="Upload Image" /></em>

      <?php if($user_image !='') { ?>
      <i id="crossPushImage" class="push fa fa-times" aria-hidden="true" style="position: absolute; top: 5px; right: 22px; display: block;"></i>
      <img id="imgprv" src="<?php if($user_image==''){

      } else {
      if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$user_image)){
            echo $user_image;
        }else{
          echo base_url().'upload/profile/thumbnail/'.$user_image;
        }
      }?>" alt="" onerror="" style="width: 11%; margin: 2%;float:right;">
      <?php }else{ ?>
      <i id="crossPushImage" class="push fa fa-times" aria-hidden="true" style="position: absolute; top: 5px; right: 22px; display: none;"></i>
      <img id="imgprv" src="" alt="" onerror="" style="width: 11%; margin: 2%;float:right;display:none;">
      <?php }?>
    </li>
    <div class="fileUploader">
        <upload-box></upload-box>
        <input type="hidden" class="jfilestyle" id="profilePic" name="profilePic">
    </div>

      <input type="submit" onclick="return addPersonaValidation();" value="Add Persona" class="btn purple-btn submitBtn">
    </div>
  </div>
</div>

<script>
$('.SlectBox').SumoSelect();
</script>
</body>
</html>
