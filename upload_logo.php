<style>
#loader{
  background: rgba(204, 204, 204, 0.86) url("<?php echo base_url(); ?>assets/template/frontend/img/loader.svg") no-repeat center center !important;
  height: 100%;
  width: 100%;
  display: block;
  position: absolute;
  z-index: 99999999999;
  margin-left: 0px;
  bottom: 0;
}
</style>
<div class="pageStarts">
<div id="loader" style="display:none;"></div>
    <div class="container-fluid">
        <div class="col-xs-12">

            <div class="col-xs-12 pageTitle">
                <h1>Upload Company Logo</h1>
            </div>

            <div class="sidebar pull-left">
                <?php
                $data['page'] = $page;
                $this->load->view('3.1/brandUserLeftSidebar', $data);
                ?>
            </div>

            <div class="pageContent statsPage userManagement">
                <div class="floatContainer">


					<div class="row" style="margin: 10px 0;">
					<span id="saveResponse"></span>
					 <div class="fileUploader">
                            <upload-box></upload-box>
                            <input type="hidden" class="jfilestyle" id="profilePic" name="profilePic">
							<input type="hidden" class="jfilestyle" id="developerLogoImage" name="developerLogoImage">
                        </div>
					</div>

                    <div class="row">
						<div class="col-sm-4 col-xs-12">
							<div class="uploadLogoDiv">
							 <i class="fa fa-cloud-upload"></i>
                        	 <h2>Upload Logo</h2>
                        	 <small>Upload Company Logo Image</small>
                        	 <a id="developerLogoPic" href="">Upload Logo</a>
                        	</div>
                        </div>
                    	<div class="col-sm-4 col-xs-12">
							<div class="uploadLogoDiv ImageRefect">
							<?php if($userDetails->developerLogo == ''){?>
							 <i id="defaultImg" class="fa fa-picture-o"></i>
							 <?php }else{?> 
							 <span id="addDefaultImg" style="display:none;"><i id="defaultImg" class="fa fa-picture-o"></i></span>
							 <?php }?>
							 <!-- <i id="defaultImg" class="fa fa-picture-o" style="display:none;"></i> -->
							 <img id="developerimgprv" src="<?php if($userDetails->developerLogo != ''){ echo base_url(); ?>upload/profile/developerlogo/<?php echo $userDetails->developerLogo; }else{ echo '';}?>" alt="">
							 <a id="crossLogoImage" href="#" style="<?php if($userDetails->developerLogo != ''){?>display:block;<?php }else{?>display:none;<?php }?>">Remove Logo</a>                     	
                        	</div>
                        </div>
                        </div>
                        
                        <div class="row">
                        <div class="col-sm-4 col-xs-12">
                        <input type="hidden" id="removelogo" value="0" />
                        <a href="javascript:void(0)" onclick="return saveCompnayLogo();" class="btn purple-btn">Save</a>
                        </div>
                        </div>

                    
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" value="<?= base_url() ?>" id="baseurl">
