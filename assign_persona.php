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
.assignPersonaPopup .modal-body {
    min-height: 95px;
}
</style>
</head>

<body>
<div id="loading" style="display:none;"></div>
<div class="row">
  <?php if(count($personaUsers) > 0){ ?>
    <div class="col-sm-6">
      <select id="personaUsers" name="personaUsers" class="SlectBox" multiple="multiple" placeholder="Select Persona">
        <?php foreach($personaUsers as $user){ ?>
          <option value="<?php echo $user->persona_user_id; ?>"><?php echo $user->name; ?></option>
        <?php } ?>
      </select><br />
      <span id="error_persona" style="color:#424141; font-size:12px;position:relative;bottom:14px;"></span>
    </div>
    <div class="col-sm-6">
        <div class="addpersonBtnOuter">
           <input onclick="return assignPersonaValidation();" value="Submit" class="btn purple-btn submitBtn" type="submit">
        </div>
    </div>
  <?php } else { ?>
      <div class="col-sm-12">
         <p> Please add persona users first!.</p>
      </div>
  <?php } ?>
</div><!-- end of row -->

<script>
    $('.SlectBox').SumoSelect({csvDispCount: 2 });
    <?php if(isset($personaIds) && !empty($personaIds)) {
        $personaIds=explode(",",$personaIds);
        foreach ($personaIds as $item) {  ?>
    var $personaselectedIds = '<?=$item;?>';
    $('.SlectBox option').each(function(i) {
        if($(this).val() == $personaselectedIds)
            $('.SlectBox')[0].sumo.selectItem(i);
    });
<?php }  } ?>
</script>
</body>
</html>
