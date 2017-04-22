<div class="pageStarts">
<div class="container-fluid">

    <div class="col-xs-12">

    <div class="campaign-loader" style="display: none;"></div>

    <div class="col-xs-12 pageTitle">
    	<h1>Create Campaign</h1>
    </div>

    <div class="sidebar pull-left">
    	<!--<div class="box grey sideNavTitle">
    	<h2>Sub Menu</h2>
        <ul class="sideNav">
            <li><a href="#" class="active"><i class="fa fa-user"></i> My Profile</a></li>
            <li><a href="#"><i class="fa fa-building"></i> Company &amp; Billing Info</a></li>
            <li><a href="#"><i class="fa fa-users"></i> Users</a></li>
        </ul>
      </div>-->
      <div class="box grey" style="max-height: 450px;">
        <h2>Push Notifications</h2>
          <ul class="app-groups-list">
            <?php if(count($push_campaigns) > 0){ ?>
            <li><a>Select Push Notification</a></li>
            <?php foreach($push_campaigns as $group){
              $platform = ucfirst($group->platform) .'<br />';
              if($group->isDraft == 1){
                $isDraft = " - (Draft)";
              }else{
                $isDraft = "";
              }
            if(in_array('7', $allPermision ) || $usertype == 8){


            ?>
              <li class="licloseList"><a <?php if($group->id == $groupId){?>class="active"<?php } ?> href="<?php echo base_url();?>appUser/editCampaigns/<?php echo $group->id; ?>"><small><?php echo $group->campaignName." - $platform ($group->app_group_name)"; if($group->isDraft == '1'){ echo ' (Draft)';} if($group->automation == 1){ echo ' (Saved for Workflow)'; }?></small></a>

              <?php if($group->isDraft == '1' || $group->automation == 1){?><a href="<?php echo base_url(); ?>appUser/deleteCampaignPopUp/<?php echo $group->id; ?>" class="closeli modalPopup" data-class="fbPop submitOffer2 addLocation" data-title="Delete Campaign"><i class="fa fa-times-circle"></i></a><?php } ?>
              </li>

            <?php } else { ?>

            <li>
                <a <?php if($group->id == $groupId){?>class="active"<?php } ?> href="<?php echo base_url();?>appUser/campaignError" class="modalPopup"><small><?php echo $group->campaignName." - $platform ($group->app_group_name)"; if($group->isDraft == '1'){ echo ' (Draft)';}?></small></a>
            <?php if($group->isDraft == '1'){?><a href="<?php echo base_url(); ?>appUser/deleteCampaignPopUp/<?php echo $group->id; ?>" class="closeli modalPopup" data-class="fbPop submitOffer2 addLocation" data-title="Delete Campaign"><i class="fa fa-times-circle"></i></a><?php } ?>
            </li>

<?php
            }

            }?>
            <div id="appendli" style="display: none;"></div>
            <?php if (count($push_campaigns) >= $noofcampaigns) {
            	?>
                        <li><a class="viewmore pagination" href="javascript:campaigns_more_activities_click();">See more campaigns</a>
                            <span class="loadingImagSpan"><img src="<?php echo base_url(); ?>assets/template/frontend/img/spinner-rosetta-gray.gif" id="seemoreLoading" class="loadingAddmoreImage" style="display: none; " /></span>
                     <?php } ?>
                    	<input type="hidden" name="statuscount" id="statuscount" value="<?php echo $statuscount; ?>" />
                        <input type="hidden" name="noofstatus" id="noofstatus" value="<?php echo $noofcampaigns; ?>" />
                        <input type="hidden" name="totalrecord" id="totalrecord" value="<?php echo $records; ?>" />
                        <input type="hidden" name="buisinessId" id="businessId" value="<?php echo $businessId; ?>" />
            <?php }else{ ?>
              <li><a>Push Notification will appear here</a></li>
        <?php } ?>


        </ul>
      </div>
    </div>
 <?php //if ($countTotalCampaign > 0 || ($extraCampaignQuantity > 0 || $extraCampaignQuantity === 'unlimited')) {  ?>
 <?php if($cookie_group != ''){ ?>
    <div class="pageContent statsPage createCampaign">

    		 <!-- <div class="col-xs-12 platform" id="selectType">

                <ul>
                	<li><input type="radio" value="pushNotifyCampaign" name="campaignType" id="androidPush"><label for="androidPush"><i class="fa fa-bell" aria-hidden="true"></i><strong>Push Notification</strong></label></li>
                	<li><input type="radio" value="emailCampaign" name="campaignType" id="iosPush"><label for="iosPush"><i class="fa fa-envelope" aria-hidden="true"></i><strong>Email</strong></label></li>
                </ul>
            </div>  -->

    <div class="floatContainer">
    <form id="pushform">

    <div class="row">
    <div class="col-xs-12">
    <ul class="steps">
    	<li class="active" id="composeTab"><a><em id="composeCheck">1</em>Compose</a></li> <!-- <a href="#compose"><em>1</em>Compose</a> -->
    	<li id="deliveryTab"><a><em id="deliveryCheckIcon">2</em>Delivery</a></li>   <!-- <a href="#delivery"><em>2</em>Delivery</a> -->
    	<li id="targetTab"><a><em id="targetCheck">3</em>Target Users</a></li>	<!-- <a href="#targetUsers"><em>3</em>Target Users</a> -->
    	<li id="confirmTab"><a><em id="confirmCheck">4</em>Confirm</a></li> <!-- <a href="#confirm"><em>4</em>Confirm</a> -->
    </ul>

    <div class="tab-content">
    	<div class="tab-pane" id="compose">
        <div class="border">
        <div class="row">
        	<div class="col-sm-6 col-xs-6">
              <div class="block">
              	<label class="title">Campaign Name</label>
                  <input type="text" placeholder="Enter Campaign Name" id="campaignName" name="campaignName" value="" ondrop="return false;">
                  <span id="error_campaignName" style="color:#424141; font-size:12px;"></span>
                  <a style="display: none;" id="choose_platform" href="<?php echo base_url();?>groupApp/choose_pushPlatform" class="modalPopup addAppBtn" data-class="fbPop addApp" data-size="size-small" data-title="Choose push platform"></a>
              </div>
              <div class="custom_block">
                <label class="">Select Persona (Optional)</label>
                 <select id="campaignPersonaUser" name="campaignPersonaUser" class="SlectBox" placeholder="" onchange="return showCampaignPersonaSuggestion();">
                    <option value="">Select Persona</option>
                   <?php if(count($persona_users) > 0){ ?>
                     <?php foreach($persona_users as $user){ ?>
                       <option value="<?php echo $user->persona_user_id; ?>"><?php echo $user->name; ?></option>
                     <?php } ?>
                   <?php } ?>
                 </select>
              </div>
              <div class="custom_block">
                <label class="">Select Lists (Optional) </label>
                <select id="campaignLists" name="campaignLists" class="SlectBox" placeholder="">
                   <option value="">Select Lists</option>
                  <?php if(count($lists) > 0){ ?>
                    <?php foreach($lists as $list){  //print_r($list); ?>
                      <option value="<?php echo $list['list_id']; ?>"><?php echo $list['name']; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
               </div>
              <div class="custom_block">
                 <label class="">Message Category</label>
                 <select id="message_category" name="message_category" class="SlectBox">
                    <option value="Reengagement">Reengagement</option>
                    <option value="Offer">Offer</option>
                    <option value="Engagement">Engagement </option>
                 </select>
              </div>
<!--              <div class="custom_block customCheck">
                <input type="checkbox" name="automation" id="automation" value="1"> <label for="automation">Save of Automation</label>
              </div>-->
            </div>
            <div class="col-sm-6 col-xs-6">
                <div class="block SuggestionBox">
                	<label class="title">Suggestion</label>
                    <span id="SuggestionBoxMsg">DUMMY DATA: 56% of this persona clicked through on an Offer.</span>
                </div>
            </div>
          </div>

        <div class="row">
        	<div class="col-xs-12"><hr></div>
        </div>

        <div class="row">
        	<div class="col-xs-12 platform">
            	<h2>Choose Push Platform</h2>
                <p>Start by choosing a mobile platform for this push campaign.</p>

                <ul>
                	<li><input type="radio" value="android" name="pushType" id="androidPush"><label for="androidPush"><i class="fa fa-android"></i><strong>Android Push</strong></label></li>
                	<li><input type="radio" value="ios" name="pushType" id="iosPush"><label for="iosPush"><i class="fa fa-apple"></i><strong>iOS Push</strong></label></li>
                </ul>
                <input type="hidden" id="selectedPlatform" value="" />
                <!-- <input type="hidden" id="campaign_type" value="" /> -->
                <input type="hidden" id="additional_profit" value="<?php echo $additional_profit; ?>" />
                <input type="hidden" id="totalAndroidCampaign" value="<?php echo $totalAndroidCampaign; ?>" />
                <input type="hidden" id="totaliOSCampaign" value="<?php echo $totaliOSCampaign; ?>" />
                <input type="hidden" id="campaignId" value="" />
                <span id="error_platform" style="color:#424141; font-size:12px;float:left;"></span>
            </div>
        </div>
        <div id="showForm">

        <div class="row">
        	<div class="col-xs-12"><h3>Compose Android Push
            <a href="javascript:void(0);" id="addonsTag" title="Media" data-toggle="tooltip" onclick="showAddons();" class="previewBtn addonActive" style="right: 36px;height: 36px;width: 36px;"><i class="fa fa-file-code-o" style="line-height: 30px;"></i></a>
            <a href="<?php echo base_url();?>appUser/preview" title="Preview" data-toggle="tooltip" class="previewBtn modalPopup" data-class="fbPop previewMsg" data-size="size-small" data-title="Preview" oncontextmenu="return false;"><i class="fa fa-external-link"></i></a></h3></div>
        </div>

        <div class="row">
        <div class="col-xs-12">
        	<div class="table">
            <div class="table-row custom-campaigns">
            <div class="table-cell">

        <div class="block">
            	<label class="title"><i class="fa fa-edit"></i> Compose Message</label>

                <label>Title</label>
                <input type="text" placeholder="Enter Title" id="push_title" ondrop="return false;">
                <span id="error_pushTitle" style="color:#424141; font-size:12px;position: relative;bottom: 14px;"></span>

                <label>Message</label>
                <textarea placeholder="Enter Message" id="push_message" ondrop="return false;"></textarea>
                <span id="error_pushMsg" style="color:#424141; font-size:12px;position: relative;bottom: 14px;"></span>

                <label>Push Notication Image (Optional) <a href="javascript:void(0)" onclick="removeImage();" class="btn white-btn" data-title="" style="width:auto;">Remove</a></label>

                <div class="imgInputField" ondrop="drop(event);" id="dropbox"> </div>

                <label>Summery Text / Image Caption (Optional)</label>
                <input type="text" placeholder="Enter Text" id="summery_text" ondrop="return false;">

            </div>

            <hr>

            <div class="block">
            	<label class="title"><i class="fa fa-hand-o-up"></i> ON CLICK BEHAVIOR</label>

                <small>Choose a custom URL to open when users click on this push notification. Note that you will need to update your app's broadcast receiver.</small>

                <select class="SlectBox" id="android_custom_url" name="android_custom_url">
                <option value="1">Redirect to Web URL</option>
                <option value="2">Deep link Into Application</option>
                <option selected value="3">Opens App</option>
                </select>
                <div class="col-xs-14" id="div_android_redirect_url" style="display:none;">
                <input type="text" name="android_redirect_url" id="android_redirect_url" placeholder="Example: https://www.example.com" />
                <span id="error_android_redirect_url" style="font-size:12px;position: relative;top: -10px;"></span>
                </div>
                <div class="col-xs-14" id="div_android_deep_link" style="display:none;">
                <input type="text" name="android_deep_link" id="android_deep_link" placeholder="Example: myapp://deeplink" />
                <span id="error_android_deep_link" style="font-size:12px;position: relative;top: -10px;"></span>
                </div>
            </div>

            <hr>

            <div class="block">
            	<label class="title"><i class="fa fa-mobile-phone"></i> DEVICE OPTIONS</label>
                <label><input name="send_push_to_recently_used_device" type="checkbox" value="1"> Only send this push to the user's most recently used device <i data-toggle="tooltip" title="If the user's most recent device is not push enabled, we will not send that user this message." class="fa fa-question-circle-o" aria-hidden="true"></i></label>
            </div>
            </div>

        	<div class="table-cell">

<!--- Addon Section -->
<div id="addonSection">
                <div class="block addOninEmailer" id="addOn">
                    <div class="row">
                        <div class="col-xs-4">
                            <a href="javascript:void(0)" onclick="showTemplate();">
                                <img src="<?php echo base_url();?>assets/template/frontend/img/emoji-icon.png" />
                                <p>Emojis</p>
                            </a>
                        </div>
                        <div class="col-xs-4">
                            <a href="javascript:void(0)" onclick="showGallery('Gif');">
                                <img src="<?php echo base_url();?>assets/template/frontend/img/gif-icon.png" />
                                <p>GIFs</p>
                            </a>
                        </div>
                        <div class="col-xs-4">
                            <a href="javascript:void(0)" onclick="showGallery('Banner');">
                                <img src="<?php echo base_url();?>assets/template/frontend/img/banner-icon.png" />
                                <p>Banner</p>
                            </a>
                        </div>
                    </div>
                </div><!-- main block -->

                <div class="block addOninEmailer " style="display: none;" id="templates">
                    <div class="row campaignEmojis" style="height:400px; overflow: auto;">
                        <div class="col-xs-2"  draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji1" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60a.png" />
                                <!-- <p>Template 1</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji2" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60b.png" />
                                <!-- <p>Template 2</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji3" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60c.png" />
                                <!-- <p>Template 3</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji4" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60d.png" />
                                <!-- <p>Template 4</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji5" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60e.png" />
                                <!-- <p>Template 5</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji6" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60f.png" />
                                <!-- <p>Template 6</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji7" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61a.png" />
                                <!-- <p>Template 7</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji8" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61b.png" />
                                <!-- <p>Template 8</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji9" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61c.png" />
                                <!-- <p>Template 9</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji10" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61d.png" />
                                <!-- <p>Template 10</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji11" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61e.png" />
                                <!-- <p>Template 11</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji12" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61f.png" />
                                <!-- <p>Template 12</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji13" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62a.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji14" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62b.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji15" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62c.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji16" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62d.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji17" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62e.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji18" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62f.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji19" src="<?php echo base_url();?>assets/template/frontend/emojis/1f600.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji20" src="<?php echo base_url();?>assets/template/frontend/emojis/1f601.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji21" src="<?php echo base_url();?>assets/template/frontend/emojis/1f602.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji22" src="<?php echo base_url();?>assets/template/frontend/emojis/1f603.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji23" src="<?php echo base_url();?>assets/template/frontend/emojis/1f604.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji24" src="<?php echo base_url();?>assets/template/frontend/emojis/1f605.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji25" src="<?php echo base_url();?>assets/template/frontend/emojis/1f606.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji26" src="<?php echo base_url();?>assets/template/frontend/emojis/1f607.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji27" src="<?php echo base_url();?>assets/template/frontend/emojis/1f608.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji28" src="<?php echo base_url();?>assets/template/frontend/emojis/1f609.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji29" src="<?php echo base_url();?>assets/template/frontend/emojis/1f610.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji30" src="<?php echo base_url();?>assets/template/frontend/emojis/1f611.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji31" src="<?php echo base_url();?>assets/template/frontend/emojis/1f612.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji32" src="<?php echo base_url();?>assets/template/frontend/emojis/1f613.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji33" src="<?php echo base_url();?>assets/template/frontend/emojis/1f614.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji34" src="<?php echo base_url();?>assets/template/frontend/emojis/1f615.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji35" src="<?php echo base_url();?>assets/template/frontend/emojis/1f616.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji36" src="<?php echo base_url();?>assets/template/frontend/emojis/1f617.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji37" src="<?php echo base_url();?>assets/template/frontend/emojis/1f618.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji38" src="<?php echo base_url();?>assets/template/frontend/emojis/1f619.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji39" src="<?php echo base_url();?>assets/template/frontend/emojis/1f620.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji40" src="<?php echo base_url();?>assets/template/frontend/emojis/1f621.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji41" src="<?php echo base_url();?>assets/template/frontend/emojis/1f622.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji42" src="<?php echo base_url();?>assets/template/frontend/emojis/1f623.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji43" src="<?php echo base_url();?>assets/template/frontend/emojis/1f624.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji44" src="<?php echo base_url();?>assets/template/frontend/emojis/1f625.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji45" src="<?php echo base_url();?>assets/template/frontend/emojis/1f626.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji46" src="<?php echo base_url();?>assets/template/frontend/emojis/1f627.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji47" src="<?php echo base_url();?>assets/template/frontend/emojis/1f628.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji48" src="<?php echo base_url();?>assets/template/frontend/emojis/1f629.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji49" src="<?php echo base_url();?>assets/template/frontend/emojis/1f630.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji50" src="<?php echo base_url();?>assets/template/frontend/emojis/1f631.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji51" src="<?php echo base_url();?>assets/template/frontend/emojis/1f632.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji52" src="<?php echo base_url();?>assets/template/frontend/emojis/1f633.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji53" src="<?php echo base_url();?>assets/template/frontend/emojis/1f634.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji54" src="<?php echo base_url();?>assets/template/frontend/emojis/1f635.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji55" src="<?php echo base_url();?>assets/template/frontend/emojis/1f636.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji56" src="<?php echo base_url();?>assets/template/frontend/emojis/1f637.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji57" src="<?php echo base_url();?>assets/template/frontend/emojis/1f641.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji58" src="<?php echo base_url();?>assets/template/frontend/emojis/1f642.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji59" src="<?php echo base_url();?>assets/template/frontend/emojis/1f643.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji60" src="<?php echo base_url();?>assets/template/frontend/emojis/1f644.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji61" src="<?php echo base_url();?>assets/template/frontend/emojis/1f910.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji62" src="<?php echo base_url();?>assets/template/frontend/emojis/1f911.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji63" src="<?php echo base_url();?>assets/template/frontend/emojis/1f912.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji64" src="<?php echo base_url();?>assets/template/frontend/emojis/1f913.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji65" src="<?php echo base_url();?>assets/template/frontend/emojis/1f914.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji66" src="<?php echo base_url();?>assets/template/frontend/emojis/1f915.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji67" src="<?php echo base_url();?>assets/template/frontend/emojis/1f917.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji68" src="<?php echo base_url();?>assets/template/frontend/emojis/1f920.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji69" src="<?php echo base_url();?>assets/template/frontend/emojis/1f922.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji70" src="<?php echo base_url();?>assets/template/frontend/emojis/1f923.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji71" src="<?php echo base_url();?>assets/template/frontend/emojis/1f924.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji72" src="<?php echo base_url();?>assets/template/frontend/emojis/1f925.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji73" src="<?php echo base_url();?>assets/template/frontend/emojis/1f927.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji74" src="<?php echo base_url();?>assets/template/frontend/emojis/263a.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji(this);" id="emoji75" src="<?php echo base_url();?>assets/template/frontend/emojis/2639.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <input type="hidden" id="campaignType" value="push" />
                    </div>
                </div>

                <div class="block addOninBanner" style="display: none;" id="bannerSection">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="gifSection">
                  <div class="row marginTop-15">
                  <div class="col-md-12">



                    <div class="giphySearch ui-widget">
                        <span class="giphyInput">
                        <input id="androidGiphySearch" autofocus type="text" name="q" placeholder="Search GIPHY" style="width:100%;max-width:600px;outline:0;" value="">

                        <label><i class="fa fa-search" onclick="getGifImages('android');" ></i></label>
                       </span>
                       <!--  <ul class="searchResult" id="androidGiphy"></ul>-->
                        <ul id="androidGiphImages">  </ul>

                    </div>
                    </div>
                    </div>
                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="Agree">

                </div>


                <div class="block addOningif campaignGif" style="display: none;" id="Applause">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="Dance">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="EyeRoll">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="No">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="OMG">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="ThankYou">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="ThumbsUp">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="YouGotThis">

                </div>

                </div>



<!-- End Addon Section   -->

                <div class="block imageOption">
                	<label class="title"><i class="fa fa-image"></i> PUSH ICON IMAGE</label>

                    <em class="em" style="cursor: pointer;" id="pushIconImage">
                    <span class="fileUploader">
                    	<!-- <input type="file"> -->

                        <i class="fa fa-cloud-upload"></i> Add Image
                    </span>
                    </em>

                    <div class="upload-image" style="position: relative;float: left;">
                    <i id="crossPushImage" class="push fa fa-times" aria-hidden="true" style="display: none;"></i>
                    <img style="display:none;width:30px;height:30px;border: none;" id="android_app_img" src="" alt="">
                    </div>
                    <span id="push_or">OR</span>

                    <input type="text" placeholder="Enter Image URL" id="push_img_url" ondrop="return false;">
                </div>
                <hr>

                <div class="block imageOption">
                	<label class="title"><i class="fa fa-image"></i> EXPANDED NOTIFICATION IMAGE</label>

                    <em class="em" style="cursor: pointer;" id="expandedIconImage">
                    <span class="fileUploader">
                    	<!-- <input type="file"> -->
                        <i class="fa fa-cloud-upload"></i> Add Image
                    </span>
                    </em>

                    <div class="upload-image" style="position: relative;float: left;">
                    <i id="crossExpandedImage" class="push fa fa-times" aria-hidden="true" style="display: none;"></i>
                    <img style="display:none;width:30px;height:30px;border: none;" id="ios_app_img" src="" alt="">
                    </div>
                    <span id="expanded_or">OR</span>

                    <input type="text" placeholder="Enter Image URL" id="expanded_img_url" ondrop="return false;">
                    <small>We recommend that you do not use images with transparent backgrounds, as differences in background color may cause transparent images to be blurred or difficult to see on some devices.</small>
                </div>
      
                <hr>

                <div class="block">
                	<label class="title"><i class="fa fa-file-code-o"></i> ATTRIBUTES & PROPERTIES (0 DETECTED)</label>
                    <h4>Add a personal touch!</h4>
                    <small>Use our supported personalization attributes. Learn about how to use them here.</small>
                    <p><a href="<?php echo base_url()?>appUser/androidSupportedAttributes" class="modalPopup" data-title="Supported Personalization Attributes" data-class="supportedAttributes" data-size="size-large">View list of supported attributes</a></p>
                </div>
            </div>

            </div>
            </div>
        </div>
        <div class="col-xs-12"><hr></div>

        <div class="col-xs-12"><p>For more details on setting up Android Push, see our <a href="<?php echo base_url().'hurreeSDK/android'; ?>" target="_blank"><b>Integration documentation</b></a>.</p></div>
        </div>

        </div>

        <!--  iOS Platform -->
        <div id="showForm_ios" style="display: none;">

        <div class="row">
        	<div class="col-xs-12"><h3>Compose iOS Push
            <a href="javascript:void(0);" id="addonsTag" title="Media" data-toggle="tooltip" onclick="showAddons1();" class="previewBtn addonActive" style="right: 36px;height: 36px;width: 36px;"><i class="fa fa-file-code-o" style="line-height: 30px;"></i></a>
            <a href="<?php echo base_url();?>appUser/preview" title="Preview" data-toggle="tooltip" class="previewBtn modalPopup" data-class="fbPop previewMsg" data-size="size-small" data-title="Preview" oncontextmenu="return false;"><i class="fa fa-external-link"></i></a></h3></div>
        </div>

        <div class="row">
        <div class="col-xs-12">
        	<div class="table">
            <div class="table-row custom-campaigns">
            <div class="table-cell">

        <div class="block">
            	<!-- <label class="title"><i class="fa fa-edit"></i> Compose Message</label> -->

               <!--  <label>Title</label>
                <input type="text" placeholder="Enter Title" id="push_title">
                <span id="error_pushTitle" style="color:#424141; font-size:12px;position: relative;bottom: 14px;"></span> -->

                <label>Message</label>
                <textarea placeholder="Enter Message" id="push_iOS_message" ondrop="return false;"></textarea>
                <span id="error_iOSpushMsg" style="color:#424141; font-size:12px;position: relative;bottom: 0px;"></span>

                <label>Push Notication Image (Optional) <a href="javascript:void(0)" onclick="removeImage();" class="btn white-btn" data-title="" style="width:auto;">Remove</a></label>
                <div class="imgInputField" ondrop="drop(event);" id="dropbox1"> </div>

                <!-- <label>Summery Text / Image Caption (Optional)</label>
                <input type="text" placeholder="Enter Text" id="summery_text"> -->

            </div>

            <hr>

            <div class="block">
            	<label class="title"><i class="fa fa-hand-o-up"></i> ON CLICK BEHAVIOR</label>

                <small>Choose a custom URL to open when users click on this push notification. Note that you will need to update your app's broadcast receiver.</small>

                <select class="SlectBox" id="ios_custom_url" name="ios_custom_url">
                <option value="1">Redirect to Web URL</option>
                <option value="2">Deep link Into Application</option>
                <option selected value="3">Opens App</option>
                </select>
                <div class="col-xs-14" id="div_ios_redirect_url" style="display:none;">
                <input type="text" name="ios_redirect_url" id="ios_redirect_url" placeholder="Example: https://www.example.com" />
                <span id="error_ios_redirect_url" style="font-size:12px;position: relative;top: 0px;"></span>
                </div>
                <div class="col-xs-14" id="div_ios_deep_link" style="display:none;">
                <input type="text" name="ios_deep_link" id="ios_deep_link" placeholder="Example: myapp://deeplink" />
                <span id="error_ios_deep_link" style="font-size:12px;position: relative;top: 0px;"></span>
                </div>
            </div>

            <!-- <hr> -->

            <!-- <div class="block">
            	<label class="title"><i class="fa fa-mobile-phone"></i> DEVICE OPTIONS</label>
                <label><input name="send_push_to_recently_used_device" type="checkbox" value="1"> Only send this push to the user's most recently used device</label>
            </div> -->
            </div>

        	<div class="table-cell">
        	<!--
            	<div class="block imageOption">
                	<label class="title"><i class="fa fa-image"></i> PUSH ICON IMAGE</label>

                    <em style="cursor: pointer;" id="pushIconImage">
                    <span class="fileUploader">
                        <i class="fa fa-cloud-upload"></i> Add Image
                    </span>
                    </em>

                    <div class="upload-image" style="position: relative;float: left;">
                    <i id="crossPushImage" class="push fa fa-times" aria-hidden="true" style="display: none;"></i>
                    <img style="display:none;width:30px;height:30px;border: none;" id="android_app_img" src="" alt="">
                    </div>
                    <span id="push_or">OR</span>

                    <input type="text" placeholder="Enter Image URL" id="push_img_url">
                </div>
                <hr>

                <div class="block imageOption">
                	<label class="title"><i class="fa fa-image"></i> EXPANDED NOTIFICATION IMAGE</label>

                    <em style="cursor: pointer;" id="expandedIconImage">
                    <span class="fileUploader">
                        <i class="fa fa-cloud-upload"></i> Add Image
                    </span>
                    </em>

                    <div class="upload-image" style="position: relative;float: left;">
                    <i id="crossExpandedImage" class="push fa fa-times" aria-hidden="true" style="display: none;"></i>
                    <img style="display:none;width:30px;height:30px;border: none;" id="ios_app_img" src="" alt="">
                    </div>
                    <span id="expanded_or">OR</span>

                    <input type="text" placeholder="Enter Image URL" id="expanded_img_url">
                    <small>We recommend that you do not use images with transparent backgrounds, as differences in background color may cause transparent images to be blurred or difficult to see on some devices.</small>
                </div>
                -->
                <!-- <hr> -->
<!--
                <div class="block">
                	<label class="title"><i class="fa fa-file-code-o"></i> ATTRIBUTES & PROPERTIES (0 DETECTED)</label>
                    <h4>Make it Personal!</h4>
                    <small>Use our supported personalization attributes. Learn about how to use them <a href="#">here</a>.</small>
                    <p><a href="<?php //echo base_url()?>appUser/supportedAttributes" class="modalPopup" data-title="Supported Personalization Attributes" data-class="supportedAttributes" data-size="size-large">View list of supported attributes</a></p>
                </div>

                <hr>-->


                <!--- Addon Section -->
<div id="addonSection">
                <div class="block addOninEmailer" id="addOn1">
                    <div class="row">
                        <div class="col-xs-4">
                            <a href="javascript:void(0)" onclick="showTemplate1();">
                                <img src="<?php echo base_url();?>assets/template/frontend/img/emoji-icon.png" />
                                <p>Emojis</p>
                            </a>
                        </div>
                        <div class="col-xs-4">
                            <a href="javascript:void(0)" onclick="showGallery1('Gif');">
                                <img src="<?php echo base_url();?>assets/template/frontend/img/gif-icon.png" />
                                <p>GIFs</p>
                            </a>
                        </div>
                        <div class="col-xs-4">
                            <a href="javascript:void(0)" onclick="showGallery1('Banner');">
                                <img src="<?php echo base_url();?>assets/template/frontend/img/banner-icon.png" />
                                <p>Banner</p>
                            </a>
                        </div>
                    </div>
                </div><!-- main block -->

                <div class="block addOninEmailer " style="display: none;" id="templates1">
                    <div class="row campaignEmojis" style="height:400px; overflow: auto;">
                        <div class="col-xs-2"  draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji1(this);" id="emoji1" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60a.png" />
                                <!-- <p>Template 1</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji1(this);" id="emoji2" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60b.png" />
                                <!-- <p>Template 2</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji1(this);" id="emoji3" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60c.png" />
                                <!-- <p>Template 3</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji1(this);" id="emoji4" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60d.png" />
                                <!-- <p>Template 4</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img onclick="addEmoji1(this);" id="emoji5" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60e.png" />
                                <!-- <p>Template 5</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji6" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60f.png" />
                                <!-- <p>Template 6</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji7" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61a.png" />
                                <!-- <p>Template 7</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji8" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61b.png" />
                                <!-- <p>Template 8</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji9" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61c.png" />
                                <!-- <p>Template 9</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji10" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61d.png" />
                                <!-- <p>Template 10</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji11" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61e.png" />
                                <!-- <p>Template 11</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji12" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61f.png" />
                                <!-- <p>Template 12</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji13" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62a.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji14" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62b.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji15" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62c.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji16" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62d.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji17" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62e.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji18" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62f.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji19" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f600.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji20" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f601.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji21" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f602.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji22" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f603.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji23" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f604.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji24" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f605.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji25" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f606.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji26" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f607.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji27" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f608.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji28" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f609.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji29" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f610.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji30" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f611.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji31" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f612.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji32" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f613.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji33" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f614.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji34" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f615.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji35" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f616.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji36" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f617.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji37" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f618.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji38" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f619.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji39" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f620.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji40" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f621.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji41" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f622.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji42" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f623.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji43" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f624.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji44" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f625.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji45" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f626.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji46" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f627.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji47" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f628.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji48" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f629.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji49" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f630.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji50" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f631.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji51" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f632.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji52" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f633.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji53" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f634.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji54" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f635.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji55" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f636.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji56" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f637.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji57" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f641.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji58" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f642.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji59" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f643.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji60" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f644.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji61" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f910.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji62" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f911.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji63" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f912.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji64" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f913.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji65" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f914.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji66" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f915.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji67" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f917.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji68" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f920.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji69" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f922.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji70" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f923.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji71" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f924.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji72" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f925.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji73" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f927.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji74" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/263a.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji75" onclick="addEmoji1(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/2639.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                    </div>
                </div>

                <div class="block addOninBanner" style="display: none;" id="bannerSection1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="gifSection1">

                <div class="row marginTop-15">
                  <div class="col-md-12">
                    <div class="giphySearch">
                        <span class="giphyInput">

                        <input id="iOSGiphySearch" autofocus type="text" name="q" placeholder="Search GIPHY" style="width:100%;max-width:600px;outline:0;" value="">
                        <label><i class="fa fa-search" onclick="getGifImages('iOS');"></i></label>
                       </span>
                        <!-- <ul class="searchResult" id="iOSGiphy">
                        </ul> -->

                        <ul id="iOSGiphImages">

                        </ul>

                    </div>
                    </div>
                    </div>

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="Agree1">

                </div>


                <div class="block addOningif campaignGif" style="display: none;" id="Applause1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="Dance1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="EyeRoll1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="No1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="OMG1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="ThankYou1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="ThumbsUp1">

                </div>

                <div class="block addOningif campaignGif" style="display: none;" id="YouGotThis1">

                </div>

                </div>



<!-- End Addon Section   -->

                <div class="block">
	            	<label class="title"><i class="fa fa-mobile-phone"></i> DEVICE OPTIONS</label>
	                <label><input name="send_push_to_recently_used_device" type="checkbox" value="1"> Only send this push to the user's most recently used device <i data-toggle="tooltip" title="If the user's most recent device is not push enabled, we will not send that user this message." class="fa fa-question-circle-o" aria-hidden="true"></i></label>
            		<label><input name="limit_this_push_to_iPad_devices" type="checkbox" value="1"> Limit this push to iPad devices</label>
            		<label><input name="limit_this_push_to_iphone_and_ipod_devices" type="checkbox" value="1"> Limit this push to iPhone and iPod devices</label>
            	</div>
<hr>

                <div class="block">
                	<label class="title"><i class="fa fa-file-code-o"></i> ATTRIBUTES & PROPERTIES (0 DETECTED)</label>
                    <h4>Make it Personal!</h4>
                    <small>Use our supported personalization attributes. Learn about how to use them <a href="#">here</a>.</small>
                    <p><a href="<?php echo base_url()?>appUser/iosSupportedAttributes" class="modalPopup" data-title="Supported Personalization Attributes" data-class="supportedAttributes" data-size="size-large">View list of supported attributes</a></p>
                </div>

            </div>

            </div>
            </div>
        </div>
        <div class="col-xs-12"><hr></div>

        <div class="col-xs-12"><p>For more details on setting up iOS Push, see our <a href="<?php echo base_url().'hurreeSDK/ios'; ?>" target="_blank"><b>Integration documentation</b></a>.</p></div>
        </div>

        </div>


        <!--  -->

        </div>

        <div class="pagination defaultBtns pull-right" >
        <?php if($cookie_group != ''){ ?>
        <input type="hidden" id="groupId" value="<?php echo $cookie_group[0]; ?>" />
        <input type="hidden" id="androidCrentials" value="<?php echo $androidCredentials; ?>" />
        <input type="hidden" id="iosCredentials" value="<?php echo $iosCredentials; ?>" />
        <?php } ?>

        				<div class="fileUploader">
                            <upload-box></upload-box>
                            <input type="hidden" class="jfilestyle" id="push_icon" name="push_icon">
                            <input type="hidden" class="jfilestyle" id="expandedImage" name="expandedImage">
                        </div>
        	<input type="hidden" id="upload_push" value="" />
        	<input type="hidden" id="upload_extended" value="" />

            <div class="inlineBtn">
            <a href="javascript:void(0)" onclick="return validateCampaign();" class="btn">Delivery</a>
            <a href="javascript:void(0)" onclick="return validateCampaign();" class="btn">Save As Draft</a>
            <div class="custom_block customCheck">
                    <input type="checkbox" name="automation" id="automation1" value="1"> <label for="automation">Save for Workflow</label>
             </div>
            </div>
                </div>


        <div class="pagination firstBtns">
            <div class="inlineBtn">
                <a id="deliveryButton" href="javascript:void(0)" class="btn" onclick="return validateCompose();">Delivery</a>
                <a id="draftButton" href="javascript:void(0)" onclick="return saveComposeAsDraft();" class="btn">Save As Draft</a>
            <div class="custom_block customCheck">
                    <input type="checkbox" name="automation" id="automation" value="1"> <label for="automation">Save for Workflow</label>
             </div>
            <a href="<?php echo base_url();?>appUser/confirmAutomation" class="modalPopup storeButton" data-class="fbPop submitOffer2 addLocation" data-title="Create Workflow" data-backdrop="static" data-keyboard="false" id="confirmAutomation" style="display: none;"></a>
            </div>
        </div>

        </div>

    	<div class="tab-pane" id="delivery">
        <div class="border">
        	<div class="row">
            	<div class="col-xs-12">
                <div class="block">
                	<label class="title">Delivery Type</label>
                <ul class="deliveryType">
                    	<li>
                	<input type="radio" name="deliveryType" id="scheduleDelivery" checked value="schedule-delivery">
                    <label for="scheduleDelivery">
                    	<strong>Schedule Delivery</strong>
                        <small>Send at designated time of the day/week</small>
                    </label>
                </li>
                <li>
                	<input type="radio" name="deliveryType" id="actionDelivery" value="action-delivery">
                    <label for="actionDelivery">
                    	<strong>Action Based Delivery</strong>
                        <small>Send when user perform an action</small>
                    </label>
                </li>
               </ul>
            </div>

			<hr>

            <div class="block active hiddenDV timeBased delivery" id="schedule-delivery">
            	<label class="title">Time Based Scheduling</label>
            	<ul>
                    <li>
                        <input type="radio" name="timeBased" id="atLaunch" value="at-launch" checked>
                        <label for="atLaunch">
                        <strong>At Launch</strong>
                        <small>Send notifications as soon as campaign is launched</small>
                        </label>
                    </li>
                    <li>
                        <input type="radio" name="timeBased" id="designatedTime" value="designated-time">
                        <label for="designatedTime">
                        <strong>Designated Time</strong>
                        <small>Choose an optimal time for users to receive this message</small>
                        </label>
                    </li>
                    <li>
                        <input type="radio" name="timeBased" id="intelligentDelivery" value="intelligent-delivery">
                        <label for="intelligentDelivery">
                        <strong>Intelligent Delivery</strong>
                        <small>Each user will receive the campaign at the time they are most likely to engage</small>
                        </label>
                    </li>
                </ul>

                <div class="wrap">

                <div class="hiddenDV active" id="at-launch">
                <div class="row">
                   <div class="col-xs-12">
            		<ul class="deliveryType">
                    	<li>
                        	<input type="checkbox" name="atlaunch" id="atlaunch1" value="1">
                            <label for="atlaunch1">
                            <small>Allow users to become re-eligible to receive campaign</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="atlaunch" id="atlaunch2" value="1">
                            <label for="atlaunch2">
                            <small>Ignore frequency capping settings for this campaign</small>
                            </label>
                        </li>
                  </ul>
                  </div>
                </div>
                </div>

                <div class="col-xs-12 time delivery-specific" id="atlaunch_reEligible" style="display:none;">
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text" name="atlaunch_time" id="atlaunch_reEligibleTime" value="1"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="atlaunch_reEligibleTimeInterval">
					                    <option value="minutes">minutes</option>
					                    <option value="days" selected>days</option>
					                    <option value="weeks">weeks</option>
					                    <option value="months">months</option>
					                </select>
		                  		</div>
		                  	</div>
		                  	<div><span id="error_atlaunch_reEligible" style="font-size: 12px;"></span></div>
                  		</div>
                  	</div>
			</div>

                <div class="hiddenDV" id="designated-time">
                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                    <label>Send</label>
                    <select class="SlectBox" id="send">
                    <option value="once">Once</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    </select>
                    </div>

                    <div class="col-sm-4 col-xs-12 time">
                    <label>Starting At</label>
                    <div class="row">
                    <div class="col-xs-4">
	                    <select class="SlectBox" id="starting_at_hour">
	                    <option value="12">12</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
	                    </select>
                    </div>
                    <div class="col-xs-4">
	                    <select class="SlectBox" id="starting_at_min">
	                    <option value="00">00</option>
						<option value="05">05</option>
						<option value="10">10</option>
						<option value="15">15</option>
						<option value="20">20</option>
						<option value="25">25</option>
						<option value="30">30</option>
						<option value="35">35</option>
						<option value="40">40</option>
						<option value="45">45</option>
						<option value="50">50</option>
						<option value="55">55</option>
	                    </select>
                    </div>
                    <div class="col-xs-4">
	                    <select class="SlectBox" id="starting_at_am_pm">
	                    <option value="AM">AM</option>
	                    <option value="PM">PM</option>
	                    </select>
                    </div>
                    </div>
                    </div>

                    <div class="col-sm-4 col-xs-12" id="onDate">
                    <label>On Date</label>
                    <div class="calendar">
                    <input type="text" class="date" id="date">
                    </div>
                    </div>

                    <div class="col-xs-12" id="daily" style="display: none;">
                    <label>Every day(s)</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="everyDay">
			                    <?php for($i=1;$i<=28;$i++){?>
			                    <option value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="beginning_date">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="ending">
		                	 <option value="never">Never</option>
		                	 <option value="on_the_date">On the date</option>
		                	 <option value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_ending_on_the_date" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="ending_on_the_date">
		                	</div>

		                	<div class="col-sm-2" id="section_ending_after_occurances" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="ending_after_occurances" maxlength="2" style="width:50%">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="weekly" style="display: none;">

                    <!--
                    <label>Every weeks</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="everyWeek">
			                    <?php for($i=1;$i<=30;$i++){?>
			                    <option value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                 -->
		                <div class="row">
		      				<div class="col-xs-12 timeBased">
		      				<label>On the days</label>
            		<ul>
                    	<li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday1" value="Sun">
                            <label for="weekday1">
                            <small>Sunday</small>
                            </label>
                        </li>
                    	<li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday2" value="Mon" checked>
                            <label for="weekday2">
                            <small>Monday</small>
                            </label>
                        </li>
                    	<li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday3" value="Tue">
                            <label for="weekday3">
                            <small>Tuesday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday4" value="Wed">
                            <label for="weekday4">
                            <small>Wednesday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday5" value="Thu">
                            <label for="weekday5">
                            <small>Thursday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday6" value="Fri">
                            <label for="weekday6">
                            <small>Friday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday7" value="Sat">
                            <label for="weekday7">
                            <small>Saturday</small>
                            </label>
                        </li>
                  </ul>
                  <span id="error_weekday" style="font-size: 12px; position: relative; bottom: 10px;"></span>
                  </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="weeks_beginning_date">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="weeks_ending">
		                	 <option value="never">Never</option>
		                	 <option value="on_the_date">On the date</option>
		                	 <option value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_weeks_ending_on_the_date" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="weeks_ending_on_the_date">
		                	</div>

		                	<div class="col-sm-2" id="section_weeks_ending_after_occurances" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="weeks_ending_after_occurances" maxlength="2" style="width:50%">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="monthly" style="display: none;">
                    <label>Every months</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="everyMonth">
			                    <?php for($i=1;$i<=12;$i++){?>
			                    <option value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="month_beginning_date">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="month_ending">
		                	 <option value="never">Never</option>
		                	 <option value="on_the_date">On the date</option>
		                	 <option value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_monthly_ending_on_the_date" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="monthly_ending_on_the_date">
		                	</div>

		                	<div class="col-sm-2" id="section_monthly_ending_after_occurances" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="monthly_ending_after_occurances" maxlength="2" style="width:50%">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12 timeBased">
            		<ul>
                    	<li>
                        	<input type="checkbox" name="designatedtime" id="designatedtime1">
                            <label for="designatedtime1">
                            <small>Send campaign to users in their local time zone</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="designatedtime" id="designatedtime2">
                            <label for="designatedtime2">
                            <small>Allow users to become re-eligible to receive campaign</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="designatedtime" id="designatedtime3">
                            <label for="designatedtime3">
                            <small>Ignore frequency capping settings for this campaign</small>
                            </label>
                        </li>
                  </ul>
                  </div>

                  <div class="col-xs-12 time delivery-specific" id="designatedTime_reEligible" style="display:none;">
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text" name="designatedTime_reEligibleTime" id="designatedTime_reEligibleTime" value="0"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="designatedTime_reEligibleTimeInterval">
					                    <option value="minutes">minutes</option>
					                    <option value="days" selected>days</option>
					                    <option value="weeks">weeks</option>
					                    <option value="months">months</option>
					                </select>
		                  		</div>
		                  	</div>
		                  	<div><span id="error_designatedTime_reEligible" style="font-size: 12px;"></span></div>
                  		</div>
                  	</div>
				</div>
                </div>
                </div>


                <div class="hiddenDV" id="intelligent-delivery">
                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                    <label>Send</label>
                    <select class="SlectBox" id="intelligent_send">
                    <option value="once">Once</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    </select>
                    </div>

                    <div class="col-sm-4 col-xs-12 time">
                    <p>at optimal time</p>
                    </div>
                    <div class="col-sm-4 col-xs-12" id="intelligent_on_date">
                    <label>On Date</label>
                    <div class="calendar">
                    <input type="text" class="date" id="intelligent_onDate">
                    </div>
                    </div>

                    <div class="col-xs-12" id="intelligent_daily" style="display: none;">
                    <label>Every day(s)</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="intelligent_everyDay">
			                    <?php for($i=1;$i<=28;$i++){?>
			                    <option value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="intelligent_beginning_date">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="intelligent_ending">
		                	 <option value="never">Never</option>
		                	 <option value="on_the_date">On the date</option>
		                	 <option value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_daily_ending_on_the_date" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="intelligent_daily_ending_on_the_date">
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_daily_ending_after_occurances" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="intelligent_daily_ending_after_occurances" maxlength="2" style="width:50%">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="intelligent_weekly" style="display: none;">

					<!--
                    <label>Every weeks</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="intelligent_everyWeek">
			                    <?php for($i=1;$i<=30;$i++){?>
			                    <option value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>-->
		                <div class="row">
		      				<div class="col-xs-12 timeBased">
		      				<label>On the days</label>
            		<ul>
                    	<li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday1" value="Sun">
                            <label for="intelligent_weekday1">
                            <small>Sunday</small>
                            </label>
                        </li>
                    	<li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday2" value="Mon" checked>
                            <label for="intelligent_weekday2">
                            <small>Monday</small>
                            </label>
                        </li>
                    	<li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday3" value="Tue">
                            <label for="intelligent_weekday3">
                            <small>Tuesday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday4" value="Wed">
                            <label for="intelligent_weekday4">
                            <small>Wednesday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday5" value="Thu">
                            <label for="intelligent_weekday5">
                            <small>Thursday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday6" value="Fri">
                            <label for="intelligent_weekday6">
                            <small>Friday</small>
                            </label>
                        </li>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday7" value="Sat">
                            <label for="intelligent_weekday7">
                            <small>Saturday</small>
                            </label>
                        </li>
                  </ul>
                  <span id="error_intelligent_weekday" style="font-size: 12px; position: relative; bottom: 10px;"></span>
                  </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="intelligent_weeks_beginning_date">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="intelligent_weeks_ending">
		                	 <option value="never">Never</option>
		                	 <option value="on_the_date">On the date</option>
		                	 <option value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_weekly_ending_on_the_date" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="intelligent_weekly_ending_on_the_date">
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_weekly_ending_after_occurances" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="intelligent_weekly_ending_after_occurances" maxlength="2" style="width:50%">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="intelligent_monthly" style="display: none;">
                    <label>Every months</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="intelligent_everyMonth">
			                    <?php for($i=1;$i<=12;$i++){?>
			                    <option value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="intelligent_month_beginning_date">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="intelligent_month_ending">
		                	 <option value="never">Never</option>
		                	 <option value="on_the_date">On the date</option>
		                	 <option value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_monthly_ending_on_the_date" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="intelligent_monthly_ending_on_the_date">
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_monthly_ending_after_occurances" style="display:none;">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="intelligent_weekly_monthly_after_occurances" maxlength="2" style="width:50%">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                   <div class="col-xs-12 timeBased">
            		<ul>
                    	<li>
                        	<input type="checkbox" name="intelliSent" id="intelliSent1" value="intelliSent1">
                            <label for="intelliSent1">
                            <small>Only send this campaign during a specific portion of the day</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="intelliSent" id="intelliSent2">
                            <label for="intelliSent2">
                            <small>Allow users to become re-eligible to receive campaign</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="intelliSent" id="intelliSent3">
                            <label for="intelliSent3">
                            <small>Ignore frequency capping settings for this campaign</small>
                            </label>
                        </li>
                  </ul>
                  </div>


                  <div class="col-xs-12 time delivery-specific" id="specificPortion" style="display:none;">

                  	<div class="row">
                  		<div class="col-sm-6">
                  		<div class="col-xs-2"><p>Between</p></div>
                  			<div class="col-xs-3">
		                	 	<select class="SlectBox" id="specific_start_hours">
		                	 	<option value="12">12</option>
		                	 	<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8" selected>8</option>
								<option value="9">9</option>
								<option value="10">10</option>
								<option value="11">11</option>
		                	 </select>
		                	</div>
		                	<div class="col-xs-3">
			                    <select class="SlectBox" id="specific_start_mins">
			                    <option value="00">00</option>
								<option value="05">05</option>
								<option value="10">10</option>
								<option value="15">15</option>
								<option value="20">20</option>
								<option value="25">25</option>
								<option value="30">30</option>
								<option value="35">35</option>
								<option value="40">40</option>
								<option value="45">45</option>
								<option value="50">50</option>
								<option value="55">55</option>
			                    </select>
                    		</div>
		                    <div class="col-xs-3">
			                    <select class="SlectBox" id="specific_start_am_pm">
			                    <option value="AM">AM</option>
			                    <option value="PM">PM</option>
			                    </select>
		                    </div>

		                   </div>

		                   <div class="col-sm-6">
		                 	<div class="col-xs-2"><p>And</p></div>
		                    <div class="col-xs-3">
		                	 	<select class="SlectBox" id="specific_end_hours">
		                	 	<option value="12">12</option>
		                	 	<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
								<option value="11">11</option>
		                	 </select>
		                	</div>
		                	<div class="col-xs-3">
			                    <select class="SlectBox" id="specific_end_mins">
			                    <option value="00">00</option>
								<option value="05">05</option>
								<option value="10">10</option>
								<option value="15">15</option>
								<option value="20">20</option>
								<option value="25">25</option>
								<option value="30">30</option>
								<option value="35">35</option>
								<option value="40">40</option>
								<option value="45">45</option>
								<option value="50">50</option>
								<option value="55">55</option>
			                    </select>
                    		</div>
		                    <div class="col-xs-3">
			                    <select class="SlectBox" id="specific_end_am_pm">
			                    <option value="AM">AM</option>
			                    <option value="PM">PM</option>
			                    </select>
		                    </div>
                  	</div>
                  </div>
			</div>

			<div class="col-xs-12 time delivery-specific" id="intelligentTime_reEligible" style="display:none;">
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text" name="intelligentTime_reEligibleTime" id="intelligentTime_reEligibleTime" value="0"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="intelligentTime_reEligibleTimeInterval">
					                    <option value="minutes">minutes</option>
					                    <option value="days" selected>days</option>
					                    <option value="weeks">weeks</option>
					                    <option value="months">months</option>
					                </select>
		                  		</div>
		                  	</div>
		                  	<div><span id="error_intelligentTime_reEligible" style="font-size: 12px;"></span></div>
                  		</div>
                  	</div>
				</div>

</div>
</div>
</div>


            </div>


            <div class="block hiddenDV delivery" id="action-delivery">
            	<div class="row">
               <div class="col-sm-12 col-xs-12">
                    <label class="title">Action-Based Scheduling</label>
                 </div>
                 <div class="row">
                 	<div class="col-sm-12 col-xs-12">
		                 <div class="col-sm-9 col-xs-12">
		                    <label>New Trigger Action</label>
		                    <select multiple class="SlectBox" id="triggerAction" name="triggerAction">
		                        <option value="1">Purchase</option>
		                        <option value="2">Perform Custom Event</option>
		                        <option value="3">Interact with view iOS campaigns</option>
		                        <option value="4">Interact with sent iOS campaigns</option>
		                        <option value="5">Interact with view android campaign</option>
		                        <option value="6">Interact with sent android campaign</option>
		                        <option value="7">Interact with view email campaigns</option>
		                        <option value="8">Interact with sent email campaigns</option>
		                        <option value="9">Interact with view cross campaigns</option>
		                        <option value="10">Interact with sent cross campaigns</option>
		                        <option value="11">Interact with view in-app messaging</option>
		                        <option value="12">Interact with sent in-app messaging</option>
		                        <option value="13">Interact with view webhooks</option>
		                        <option value="14">Interact with sent webhooks</option>
		                    </select>
		                    <span id="error_triggerAction" style="font-size:12px;position: relative;top: -12px;"></span>
		                 </div>
                 </div>
                 </div>

	 				<div class="row">
	                 	<div class="col-sm-12 col-xs-12">
	                 		<div class="col-xs-12">
	                 			<label>Schedule Delay</label>
	                 		</div>
			                 <div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="scheduleDelay">
			                        <option value="Immediately">Immediately</option>
			                        <option value="After">After</option>
			                      <!--  <option value="On the next">On the Next</option>-->
			                    </select>
			                    <small>Once trigger event criteria are met, send this campaign </small>
			                    <div><span id="error_afterTimeInterval" style="font-size: 12px;"></span></div>
			                 </div> <!-- end of col sm 4 -->
			                 <div id="after" style="display: none;">
			                 	<div class="col-sm-1 col-xs-6">
				                    <input type="text" name="scheduleDelay_afterTime" id="scheduleDelay_afterTime" value="0"/>
				                 </div> <!-- end of col sm 4 -->
	                			 <div class="col-sm-2 col-xs-6">
				                    <select class="SlectBox" id="scheduleDelay_afterTimeInterval">
				                        <option value="minutes">minutes</option>
				                        <option value="days">days</option>
				                        <option value="weeks">weeks</option>
				                    </select>
				                 </div> <!-- end of col sm 4 -->

			                 </div>
			                 <div id="on_the_next" style="display:none;">
	                 			 <div class="col-sm-2 col-xs-6">
				                    <select class="SlectBox" id="on_the_next_day">
				                        <option value="Sunday">Sunday</option>
				                        <option value="Monday">Monday</option>
				                        <option value="Tuesday">Tuesday</option>
				                        <option value="Wednesday">Wednesday</option>
				                        <option value="Thursday">Thursday</option>
				                        <option value="Friday">Friday</option>
				                        <option value="Saturday">Saturday</option>
				                    </select>
				                 </div> <!-- end of col sm 4 -->
	                 			 <div class="col-sm-3 col-xs-6">
				                    <select class="SlectBox" id="deliveryTime">
				                        <option value="at">at</option>
				                        <option value="using intelligent delivery">using intelligent delivery</option>
				                    </select>
				                 </div> <!-- end of col sm 4 -->
				                 <div id="at">
		                			 <div class="col-sm-1 col-xs-6">
					                    <select class="SlectBox" id="on_the_next_hours">
					                        <option value="12">12</option>
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
											<option value="6">6</option>
											<option value="7">7</option>
											<option value="8">8</option>
											<option value="9">9</option>
											<option value="10">10</option>
											<option value="11">11</option>
					                    </select>
					                 </div> <!-- end of col sm 4 -->
					                 <div class="col-sm-1 col-xs-6">

					                    <select class="SlectBox" id="on_the_next_mins">
					                        <option value="00">00</option>
											<option value="05">05</option>
											<option value="10">10</option>
											<option value="15">15</option>
											<option value="20">20</option>
											<option value="25">25</option>
											<option value="30">30</option>
											<option value="35">35</option>
											<option value="40">40</option>
											<option value="45">45</option>
											<option value="50">50</option>
											<option value="55">55</option>
					                    </select>
					                 </div> <!-- end of col sm 4 -->
					                  <div class="col-sm-1 col-xs-6">
					                    <select class="SlectBox" id="on_the_next_am">
					                        <option value="AM">AM</option>
					                        <option value="PM">PM</option>
					                    </select>
					                 </div> <!-- end of col sm 4 -->
				                 </div>
			                 </div>

	                 	</div><!-- end of col-sm-12 col-xs-12 -->
	                 </div>
	                 <!--<div class="row" id="unless_the_user" style="display: none;">
                 	<div class="col-sm-12 col-xs-12">
		                 <div class="col-sm-9 col-xs-12">
		                 <p><hr></p>
		                    <label>Unless The User (Optional)</label>
		                    <select multiple class="SlectBox" id="unless_the_user_list">
                          <option value="1">Purchase</option>
                          <option value="2">Perform Custom Event</option>
                          <option value="3">Interact with view iOS campaigns</option>
                          <option value="4">Interact with sent iOS campaigns</option>
                          <option value="5">Interact with view android campaign</option>
                          <option value="6">Interact with sent android campaign</option>
                          <option value="7">Interact with view email campaigns</option>
                          <option value="8">Interact with sent email campaigns</option>
                          <option value="9">Interact with view cross campaigns</option>
                          <option value="10">Interact with sent cross campaigns</option>
                          <option value="11">Interact with view in-app messaging</option>
                          <option value="12">Interact with sent in-app messaging</option>
                          <option value="13">Interact with error webhooks</option>
                          <option value="14">Interact with sent webhooks</option>
		                    </select>
		                 </div>
                 </div>
               </div>-->
                 </div>


                 <div class="row"><div class="col-xs-12">
                 <p><hr></p>
                 </div></div>

                 <div class="row">
                 <div class="col-sm-12 col-xs-12">
                    <label class="title">Campaign Duration</label>
                 </div>

                 <div class="col-sm-12 col-xs-12">

                    <label>Start Time (Required)</label>

                    <div class="row">
                    	<div class="col-sm-3 col-xs-12">
                        <div class="calendar">
                        	<input type="text" class="date" id="actionDeliveryStartDate">
                        </div>
                        </div>

                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryStartHours">
	                    <option value="12">12</option>
					    <option value="1">1</option>
					    <option value="2">2</option>
					    <option value="3">3</option>
					    <option value="4">4</option>
					    <option value="5">5</option>
					    <option value="6">6</option>
					    <option value="7">7</option>
					    <option value="8">8</option>
					    <option value="9">9</option>
					    <option value="10">10</option>
					    <option value="11">11</option>
	                    </select>
                    </div>
                    <div class="col-sm-2 col-xs-4">
                    	<select class="SlectBox" id="actionDeliveryStartMins">
                    	<option value="00">00</option>
					    <option value="05">05</option>
					    <option value="10">10</option>
					    <option value="15">15</option>
					    <option value="20">20</option>
					    <option value="25">25</option>
					    <option value="30">30</option>
					  	<option value="35">35</option>
					  	<option value="40">40</option>
					  	<option value="45">45</option>
					  	<option value="50">50</option>
					  	<option value="55">55</option>
                    	</select>
                    </div>
                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryStartAm">
	                    <option value="AM">AM</option>
	                    <option value="PM">PM</option>
	                    </select>
                    </div>
                    </div>
                    <label><input type="checkbox" id="actionDeliveryEndTimeEnabled" name="actionDeliveryEndTimeEnabled" checked> End Time (Optional)</label>
                    <div class="row editEndTime">
                    	<div class="col-sm-3 col-xs-12">
                        <div class="calendar">
                        	<input type="text" class="date" id="actionDeliveryEndDate" disabled>
                        </div>
                        </div>

                        <div class="col-sm-2 col-xs-4">
                        <select class="SlectBox" id="actionDeliveryEndHours" disabled>
                        <option value="12">12</option>
					    <option value="1">1</option>
					    <option value="2">2</option>
					    <option value="3">3</option>
					    <option value="4">4</option>
					    <option value="5">5</option>
					    <option value="6">6</option>
					    <option value="7">7</option>
					    <option value="8">8</option>
					    <option value="9">9</option>
					    <option value="10">10</option>
					    <option value="11">11</option>
                        </select>
                        </div>
                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryEndMins" disabled>
	                    <option value="00">00</option>
					    <option value="05">05</option>
					    <option value="10">10</option>
					    <option value="15">15</option>
					    <option value="20">20</option>
					    <option value="25">25</option>
					    <option value="30">30</option>
					  	<option value="35">35</option>
					  	<option value="40">40</option>
					  	<option value="45">45</option>
					  	<option value="50">50</option>
					  	<option value="55">55</option>
	                    </select>
                    </div>
                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryEndAm" disabled>
	                    <option value="AM">AM</option>
		                <option value="PM">PM</option>
	                    </select>
                    </div>
                    </div>
                    <span id="error_campaignDuration" style="font-size: 12px;"></span>
<!--

                    <div class="row">
                    	<div class="col-xs-12">
                        <div class="localTimeZone">
                        <input type="checkbox" id="localTimeZone">
                        <label for="localTimeZone">Send campaign to users in their local time zone</label>
                        </div>
                        </div>
                    </div>

                     <div class="row">
                    	<div class="col-xs-12">
                        <div class="timeBased">
                        <ul>
                        <li>
                        <input type="checkbox" name="campDuration" id="campDuration1">
                        <label for="campDuration1"><strong>Only send this campaign during a specific portion of the day</strong></label>
                        </li>
                        <li>
                        <input type="checkbox" name="campDuration" id="campDuration2">
                        <label for="campDuration2"><strong>Allow users to become re-eligible to receive campaign</strong></label>
                        </li>
                        <li>
                        <input type="checkbox" name="campDuration" id="campDuration3">
                        <label for="campDuration3"><strong>Ignore frequency capping settings for this campaign</strong></label>
                        </li>
                        </ul>
                        </div>
                        </div>
                    </div>
-->
                    <div class="col-xs-12 time delivery-specific" id="actionDelivery_specificPortion" style="display:none;">

                  	<div class="row">
                  		<div class="col-sm-5">
	                  		<div class="row">
		                  		<div class="col-xs-2"><p>Between</p></div>
		                  		<div class="col-xs-3">
				                	 	<select class="SlectBox" id="actionDelivery_specific_start_hours">
				                	 	<option value="12">12</option>
				                	 	<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8" selected>8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
				                	 </select>
				                	</div>
			                	<div class="col-xs-4">
				                    <select class="SlectBox" id="actionDelivery_specific_start_mins">
				                    <option value="00">00</option>
									<option value="05">05</option>
									<option value="10">10</option>
									<option value="15">15</option>
									<option value="20">20</option>
									<option value="25">25</option>
									<option value="30">30</option>
									<option value="35">35</option>
									<option value="40">40</option>
									<option value="45">45</option>
									<option value="50">50</option>
									<option value="55">55</option>
				                    </select>
	                    		</div>
			                    <div class="col-xs-3">
				                    <select class="SlectBox" id="actionDelivery_specific_start_am_pm">
				                    <option value="AM">AM</option>
				                    <option value="PM">PM</option>
				                    </select>
			                    </div>
	               			</div>
		                </div>

		                   <div class="col-sm-7">
			                   <div class="row">
				                 	<div class="col-xs-1" align="right"><p>And</p></div>
				                    <div class="col-xs-2">
				                	 	<select class="SlectBox" id="actionDelivery_specific_end_hours">
				                	 	<option value="12">12</option>
				                	 	<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
				                	 </select>
				                	</div>
				                	<div class="col-xs-3">
					                    <select class="SlectBox" id="actionDelivery_specific_end_mins">
					                    <option value="00">00</option>
										<option value="05">05</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
										<option value="35">35</option>
										<option value="40">40</option>
										<option value="45">45</option>
										<option value="50">50</option>
										<option value="55">55</option>
					                    </select>
		                    		</div>
				                    <div class="col-xs-2">
					                    <select class="SlectBox" id="actionDelivery_specific_end_am_pm">
					                    <option value="AM">AM</option>
					                    <option value="PM">PM</option>
					                    </select>
				                    </div>
		                  	  		<div class="col-sm-4"><p>in the user's local times</p></div>
		                  	  </div>
	                  	  </div>
                  </div>
		                  <div class="localTimeZone" style="border-top: none;margin-top: 0px;">
		                  	<input type="checkbox" name="actionDelivery_nextAvailableTime" id="actionDelivery_nextAvailableTime"/><label for="actionDelivery_nextAvailableTime">Send at the next available time if the delivery time falls outside the specified portion of the day</label>
		                  </div>
			</div>

			<div class="col-xs-12 time delivery-specific" id="actionDelivery_reEligible" style="display:none;">
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text"name="actionDeliveryTime_reEligibleTime" id="actionDeliveryTime_reEligibleTime" value="0"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="actionDeliveryTime_reEligibleTimeInterval">
					                    <option value="minutes">minutes</option>
					                    <option value="days" selected>days</option>
					                    <option value="weeks">weeks</option>
					                    <option value="months">months</option>
					                </select>
		                  		</div>
		                  	</div>
		                  	<div><span id="error_actionDeliveryTime_reEligible" style="font-size: 12px;"></span></div>
                  		</div>
                  	</div>
			</div>



                 </div>


                </div>
            </div>

            <hr>
            <!-- <div class="col-sm-12 col-xs-12 nextTime"><p>Next Send Time: 21 Jun 2016 at 9:50am BST</p></div> -->
           </div>



            </div>


        </div>

        <div class="pagination inner">
			<a href="javascript:void(0)" onclick="backToCompose();" class="btn back">Back</a>
            <a href="javascript:void(0)" onclick="return saveDeliveryAsDraft();" class="btn">Save As Draft</a>
            <a href="javascript:void(0)" onclick="return validateDelivery()" class="btn">Target Users</a>
        </div>
        </div><!-- End delivery -->




    	<div class="tab-pane" id="targetUsers">

        <div class="border">
        	<div class="row">
            	<div class="col-xs-12">
                <div class="block">
                <label class="title">Target Users</label>
                <small>Target users by choosing multiple segments they must fall into. Further refine your audience by adding additional filters.</small>
                </div>
                </div>
             </div>
        	<div class="row">
            	<div class="col-xs-12">
                <hr>
                </div>
             </div>

        	<div class="row">
            	<div class="col-xs-12" >
                <div class="table">
                	<div class="table-row">
                		<div class="table-cell">
                        <div class="block">
                         <select id="segments">
                            <option>+ Add Segments Here <em></em></option>
                            <option id="1segment" value="1">Lapsed Users - 7 days</option>
                            <option id="2segment" value="2">User Onboarding - First Week</option>
                            <option id="3segment" value="3">User Onboarding - Second Week</option>
                            <option id="4segment" value="4">Engaged Recent Users</option>
                            <option id="5segment" value="5">All Users</option>

                         </select>

                         <div id="segmentWrap" class="tags"></div>
                         <div><span id="error_segment" style="font-size: 12px;"></span></div>
                        </div>
                        </div>

                		<div class="table-cell">
                        <div class="block">
                        <select id="addFilters">
                            <option>+ Add Filters Here <em></em></option>

	                            <!-- <option id="1filter" value="1">Custom Attributes</option>
	                            <option id="2filter" value="2">Custom Event</option>-->
	                            <!-- <option id="3filter" value="3">First Did Custom Event</option> -->
	                            <!-- <option id="4filter" value="4">Last Did Custom Event</option> -->
	                            <!-- <option id="5filter" value="5">X Custom Event In Y Days</option> -->

                            	<!--<option id="6filter" value="6">First Made Purchase</option>-->
                            	<option id="7filter" value="7">First Purchased Product</option>
                            	<option id="8filter" value="8">First Used App</option>
                            	<!--<option id="9filter" value="9">Last Made Purchase</option>-->
                            	<option id="10filter" value="10">Last Purchased Product</option>
                            	<option id="11filter" value="11">Last Submitted Feedback</option>
                            	<option id="12filter" value="12">Last Used App</option>
                            	<!-- <option id="13filter" value="13">Median Session Duration</option> -->
                            	<!-- <option id="14filter" value="14">Money Spent In-App</option> -->
                            	<option id="15filter" value="15">Most Recent App Version</option>
                            	<!-- <option id="16filter" value="16">Most Recent Location</option> -->
                            	<!-- <option id="17filter" value="17">Number of Feedback Items</option> -->
                            	<option id="18filter" value="18">Purchased Product</option>
                            	<!-- <option id="19filter" value="19">Session Count</option> -->
                            	<!-- <option id="20filter" value="20">Total Number of Purchases</option>  -->
                            	<!-- <option id="21filter" value="21">Uninstall Date</option> -->
                            	<!-- <option id="22filter" value="22">Uninstalled</option> -->
                            	<!-- <option id="23filter" value="23">X Money Spent in Last Y Days</option> -->
                            	<!-- <option id="24filter" value="24">X Product Purchased In Y Days</option> -->
                            	<!-- <option id="25filter" value="25">X Purchases in Last Y Days</option> -->
                            	<!-- <option id="26filter" value="26">X Sessions in Last Y Days</option> -->
                            	<option id="27filter" value="27">User views app page</option>

                         </select>

                         <div id="filterWrap" class="tags"></div>
                         <div><span id="error_filter" style="font-size: 12px;"></span></div>
                        </div>
                        </div>
                    </div>
                </div>
                </div>
             </div>
             <hr>
             <div class="block">
             <div class="row">
            	<div class="col-sm-6 col-xs-12" >
                	<label>Send To</label>
                    <select class="SlectBox" id="sendCampaignToUserType">
                    	<option value="1">Users who are subscribed or opted-in</option>
                    	<option value="2">Opted-in users only</option>
                    	<option value="0">All users including unsubscribed users</option>
                    </select>

                    <div class="localTimeZone">
                	<input type="checkbox" id="targetUsers_whoWillReceiveCampaign" name="targetUsers_whoWillReceiveCampaign" checked><label for="targetUsers_whoWillReceiveCampaign">Send the number of people who will receive this campaign</label>
                	</div>
                </div>

            	<div class="col-sm-12 col-xs-12" id="selectedUsers">
                <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <select class="SlectBox" id="selectedUsers_receiveCampaign">
                    	<option value="1">In total, this campaign should</option>
                    	<option value="2">Every time this campaign is scheduled</option>
                    </select>
                    </div>
                <div class="col-sm-6 col-xs-12">
                   Message a maximum of <input type="text" class="inline" style="margin:0;width:76px;" maxlength="7" id="no_of_users_who_receive_campaigns" name="no_of_users_who_receive_campaigns" value="1000"> users
                   <span id="error_noOfUsersWhoReceiveCampaigns" style="float: left;width: 100%;font-size: 12px;"></span>
                </div>
                </div>
<!--                <div><span id="error_noOfUsersWhoReceiveCampaigns"></span></div>-->
                </div>


            	<div class="col-sm-12 col-xs-12" >
                <div class="localTimeZone">
                   <input type="checkbox" id="send_this_push_to_users_most_recently_used_device" name="send_this_push_to_users_most_recently_used_device"><label for="send_this_push_to_users_most_recently_used_device"> Only send this push to the user's most recently used device</label>
                </div>
                </div>

            	<div class="col-sm-12 col-xs-12" id="messages_per_minute_block" style="display: none;">
                <div class="row perminute">
                <div class="col-sm-3 col-xs-12">
                    <select class="SlectBox" id="messages_per_minute">
                    	<option value="50">50</option>
						<option value="100">100</option>
						<option value="500">500</option>
						<option value="1000">1,000</option>
						<option value="2500">2,500</option>
						<option value="5000">5,000</option>
						<option value="10000">10,000</option>
						<option value="25000">25,000</option>
						<option value="50000">50,000</option>
						<option value="100000">100,000</option>
						<option value="250000">250,000</option>
						<option value="500000" selected>500,000</option>
                    </select>
                    </div>
                <div class="col-sm-3 col-xs-12">
                   <p>per minute</p>
                </div>
                </div>
                </div>


                </div>
             </div>


         </div>

        <div class="pagination inner">
			<a href="javascript:void(0)" onclick="backToDelivery();" class="btn back">Back</a>
            <a href="javascript:void(0)" onclick="return saveTargetAsDraft();" class="btn">Save As Draft</a>
            <a href="javascript:void(0)" onclick="return validateTarget();" class="btn">Confirm</a>
        </div>
        </div> <!-- End targetUsers -->




    	<div class="tab-pane" id="confirm">

        <div class="border">
        	<div class="row">
            	<div class="col-xs-12">
                <div class="block">
                <a href="javascript:void(0)" class="edit" onclick="backToCompose();"><i class="fa fa-pencil"></i></a>
                	<label class="title">Compose</label>

                    <div class="row">
                    	<div class="col-sm-6 col-xs-12">
                        <label>Standard Notification View</label>
                        	<div class="mobile">
                            	<div class="msg">
                                	<div class="icon" style="border-radius: 15px;width: 50px;height: 50px;" id="standard_preview_icon"></div>
                                    <div class="info extended">
                                    	<p><strong id="standard_preview_title"></strong></p>
                                        <em class="time">9:07 PM</em>
                                        <small id="standard_preview_message"></small>
                                    </div>
                                    <div class="dragImg">
                                        <!-- <img src="http://hurree.local/assets/template/frontend/email_template/gif/Agree/Agree-alt.gif" /> -->
                                    </div>
                                    <div id="standard_preview_expanded_crop"></div>
                                </div>

                            </div>
                        </div>
                    	<div class="col-sm-6 col-xs-12">
                        <label>Extended Notification View</label>
                        	<div class="mobile">
                            	<div class="msg">
                                	<div class="icon" style="border-radius: 15px;width: 50px;height: 50px;" id="extended_preview_icon"></div>
                                    <div class="info">
                                    	<p><strong id="extended_preview_title"></strong></p>
                                        <em class="time">9:07 PM</em>
                                        <small id="extended_preview_message"></small>
                                    </div>
                                    <div class="dragImg">
                                        <!-- <img src="http://hurree.local/assets/template/frontend/email_template/gif/Agree/Agree-alt.gif" /> -->
                                    </div>
                                    <div id="extended_preview_expanded_crop"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="block">
                <a href="javascript:void(0)" onclick="backToDelivery();" class="edit"><i class="fa fa-pencil"></i></a>
                	<label class="title">Delivery</label>
                    <div class="row">
                    	<div class="col-sm-6 col-xs-12">
                        	<label>Campaign Rules</label>
                        	<small>Users will only receive messages from this campaign once.</small>
                    	</div>
                    	<div class="col-sm-6 col-xs-12">
                        	<label>Time-Based Scheduling Options</label>
                        	<small id="preview_time_based"></small>
                    	</div>
                    </div>
                </div>

                <hr>

                <div class="block">
                <a href="javascript:void(0)" onclick="backToTarget();" class="edit"><i class="fa fa-pencil"></i></a>
                	<label class="title">Target Users</label>
                    <div class="row">
                    	<div class="col-sm-6 col-xs-12">
                        	<label>Segments</label>
                        	<small id="segmentsList"></small>
                    	</div>
                    	<div class="col-sm-6 col-xs-12">
                        	<label>Filters</label>
                        	<small id="filtersList"></small>
                    	</div>
                    </div>
                    <div class="row">
                    	<div class="col-sm-12 col-xs-12">
                        	<label>Send To</label>
                        	<small>Users who are subscribed</small>
                        	<small>In total, this campaign should message a maximum 1000 users</small>
                    	</div>
                    </div>
                </div>
                <hr>

                <div class="block" id="copy_push_block">
	                <div class="row">
		                <div class="col-sm-12 col-xs-12">
		                <input type="checkbox" id="copy_push" name="copy_push" value="1"><label class="title" for="copy_push" style="display: inline;float: none;width: auto;cursor: pointer;"> Duplicate to <span id="copy_other_platform"></span></label>
		                </div>
		                <div class="col-sm-6 col-xs-12">
		                <input type="text" id="copy_title" placeholder="Enter Title" name="copy_title" style="display:none;">
		                <span id="error_copy_title" style="font-size: 12px;"></span>
		                </div>
	                </div>
                </div>

                </div>
            </div>
         </div>
		<div>
		<input type="hidden" id="iOSAppImage" value="<?php echo $iOSAppImage; ?>" />
		<a href="<?php echo base_url();?>appUser/launchCampaignSuccessPopUp" id="launchCampaign" style="display: none;" class="modalPopup" data-class="fbPop addApp" data-title="Success">Confirm</a>
		</div>
        <div class="pagination inner">
          <a href="javascript:void(0);" id="post_facebook_page" class="modalPopup" data-title="Facebook Page" data-class="fbPop delete fbPopClose" data-size="size-small"><span></span></a>

			<a href="javascript:void(0)" class="btn back" onclick="backToTarget();">Back</a>
            <a href="javascript:void(0)" onclick="return saveConfirmAsDraft();" class="btn">Save As Draft</a>

            <a href="<?php echo base_url();?>appUser/confirmationlaunch" data-size="size-medium" data-title="Confirm Campaign"  data-class="fbPop delete launchCampaign" id="saveCampaign" class="btn modalPopup" oncontextmenu="return false;" style="display:block;float:right;">Launch Campaign</a>

            <a href="javascript:void(0)" id="androidCredentialsPopUp" data-toggle="modal" data-target=".androidCrentials" class="btn" oncontextmenu="return false;" style="display:none;float:right;">Launch Campaign</a>
            <a href="javascript:void(0)" id="iOSCredentialsPopUp" data-toggle="modal" data-target=".iOSCrentials" class="btn" oncontextmenu="return false;" style="display:none;float:right;">Launch Campaign</a>
            <a href="javascript:void(0)" onclick="return checkTitle();" id="check_title" class="btn" style="display: none;">Launch Campaign</a>
        </div>
        </div>
    </div>
    </div>

    </div>
    </form>
    </div> <!-- End floatContainer -->
    </div><!-- End createCampaign  -->
    <?php }else{?>

	<div class="pageContent statsPage appGroups">
	<div class="appGroupInner campaign_kk" style="float:none; text-align: center; margin:0 auto;">
	<div class="row">
	    <div class="col-xs-12">
	    <?php
      if(!empty($groupApps)){
        if(count($groupApps) > 0){
	        foreach($groupApps as $groups){
	    ?>
	         <a href="javascript:void(0)" onclick="return selectPushGroup(<?php echo $groups['app_group_id'];?>)"><img src="<?php echo $groups['image'];?>" /><p><?php echo $groups['app_group_name']; ?></p></a>
	    <?php
          } ?>
            <input type="hidden" id="push_message" />
        <input type="hidden" id="push_title" />
        <input type="hidden" id="subject" />
        <input type="hidden" id="push_iOS_message" />     
	     <?php }
     }else{ ?>
        <p>No apps exist for added groups, please add apps for added groups</p>
        <p>OR</p>
        <p>Create App Group</p>
        <br>
        <p><a style="" href="<?php echo base_url(); ?>groupApp/addgroup_popup" class="modalPopup addAppGroupBtn" data-class="fbPop addApp" data-size="size-small" title="Add New App Group" data-title="Add New App Group" data-backdrop="static" data-size="size-small"><i class="fa fa-plus"></i> <strong>Create App Group</strong></a></p>
        <input type="hidden" id="push_message" />
        <input type="hidden" id="push_title" />
        <input type="hidden" id="subject" />
        <input type="hidden" id="push_iOS_message" />
     <?php }  ?>
		</div>
	</div>
	</div><!-- end of app group  main -->

      </div>

    <?php } ?>
    <?php //}else{?>
    		<!-- <p style="text-align:center;">You have no campaigns left, do you want to buy more?</p>
                    <p style="text-align:center;margin-top: 25px;">
                    <a href="<?php //echo base_url(); ?>appUser/store" class="btn purple-btn modalPopup" data-title="Hurree Store" data-class="tips submitOffer2" >Store</a></p> -->
    <?php //}?>
</div>

</div><!-- End container-fluid -->
</div>
