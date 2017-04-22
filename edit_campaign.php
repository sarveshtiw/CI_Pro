<div class="pageStarts">
<div class="container-fluid">

    <div class="col-xs-12">

    <div class="campaign-loader" style="display: none;"></div>

    <div class="col-xs-12 pageTitle">
    	<h1>Create Campaign</h1>
    </div>



    <div class="sidebar pull-left">
    	
      <div class="box grey" style="max-height: 450px;">
        <h2>Push Notifications</h2>
        <ul>
            <li><a href="<?php echo base_url();?>appUser/campaigns">Add Push Notifications</a></li>
        </ul>
          <ul class="app-groups-list">
            <?php if(count($push_campaigns) > 0){ ?>
            <li><a>Select Push Notification</a></li>
            <?php foreach($push_campaigns as $group){ $platform = ucfirst($group->platform) .'<br />';

            if(in_array('7', $allPermision ) || $usertype == 8){


            ?>
              <li class="licloseList"><a <?php if($group->id == $groupId){?>class="active"<?php } ?> href="<?php echo base_url();?>appUser/editCampaigns/<?php echo $group->id; ?>"><small><?php echo $group->campaignName." - $platform ($group->app_group_name)"; if($group->isDraft == '1'){ echo ' (Draft)';} if($group->automation == 1){ echo ' (Saved for Workflow)'; }?></small></a>
                  <?php if($group->isDraft == '1' || $group->automation == 1){?><a href="<?php echo base_url(); ?>appUser/deleteCampaignPopUp/<?php echo $group->id; ?>" class="closeli modalPopup" data-class="fbPop submitOffer2 addLocation" data-title="Delete Campaign"><i class="fa fa-times-circle"></i></a><?php } ?>
              </li>
            <?php } else { ?>

            	<li><a <?php if($group->id == $groupId){?>class="active"<?php } ?> href="<?php echo base_url();?>appUser/campaignError" class="modalPopup"><small><?php echo $group->campaignName." - $platform ($group->app_group_name)"; if($group->isDraft == '1'){ echo ' (Draft)';}?></small></a></li>


<?php
            }

            } ?>
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
          <?php  }else{ ?>
              <li><a>Push Notification will appear here</a></li>
        <?php } ?>
        </ul>
      </div>
    </div>
 <?php //if($cookie_group != ''){ ?>
    <div class="pageContent statsPage createCampaign">

    		 <!-- <div class="col-xs-12 platform" id="selectType">

                <ul>
                	<li><input type="radio" value="pushNotifyCampaign" name="campaignType" id="androidPush"><label for="androidPush"><i class="fa fa-bell" aria-hidden="true"></i><strong>Push Notification</strong></label></li>
                	<li><input type="radio" value="emailCampaign" name="campaignType" id="iosPush"><label for="iosPush"><i class="fa fa-envelope" aria-hidden="true"></i><strong>Email</strong></label></li>
                </ul>
            </div>  -->

    <div class="floatContainer">

    <div class="row">
    <div class="col-xs-12">
    <ul class="steps">
    	<li class="active" id="composeTab"><a><em>1</em>Compose</a></li> <!-- <a href="#compose"><em>1</em>Compose</a> -->
    	<?php if($campaign->automation != '1'){ ?>
        <li id="deliveryTab"><a><em>2</em>Delivery</a></li>   <!-- <a href="#delivery"><em>2</em>Delivery</a> -->
    	<li id="targetTab"><a><em>3</em>Target Users</a></li>	<!-- <a href="#targetUsers"><em>3</em>Target Users</a> -->
    	<li id="confirmTab"><a><em>4</em>Confirm</a></li> <!-- <a href="#confirm"><em>4</em>Confirm</a> -->
        <?php } ?>
    </ul>

    <div class="tab-content">
    	<div class="tab-pane" id="compose">
        <div class="border">
        <div class="row">
        	<div class="col-sm-6 col-xs-12">
            <div class="block">
            	<label class="title">Campaign Name</label>
                <input type="text" placeholder="Enter Campaign Name" id="campaignName" name="campaignName" <?php if(count($campaign) > 0){?>value="<?php echo $campaign->campaignName; ?>"<?php }else{?> value=""<?php }?> ondrop="return false;">
                <span id="error_campaignName" style="color:#424141; font-size:12px;"></span>
                <a style="display: none;" id="choose_platform" href="<?php echo base_url();?>groupApp/choose_pushPlatform" class="modalPopup addAppBtn" data-class="fbPop addApp" data-size="size-small" data-title="Choose push platform"></a>
            </div>
            <div class="custom_block">
              <label class="">Select Persona (Optional)</label>
               <select id="campaignPersonaUser" name="campaignPersonaUser" class="SlectBox" placeholder="" onchange="return showCampaignPersonaSuggestion();">
                  <option value="">Select Persona</option>
                 <?php if(count($persona_users) > 0){ ?>
                   <?php foreach($persona_users as $user){ ?>
                     <option value="<?php echo $user->persona_user_id; ?>" <?php if($campaign->persona_user_id == $user->persona_user_id){?>selected<?php }?> ><?php echo $user->name; ?></option>
                   <?php } ?>
                 <?php } ?>
               </select>
            </div>
            <div class="custom_block">
               <label class="">Select Lists (Optional)</label>
                <select id="campaignLists" name="campaignLists" class="SlectBox" placeholder="">
                   <option value="">Select Lists</option>
                  <?php if(count($lists) > 0){ ?>
                    <?php foreach($lists as $list){ ?>
                      <option value="<?php echo $list['list_id']; ?>" <?php if($campaign->list_id == $list['list_id']){?>selected<?php }?> ><?php echo $list['name']; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
             </div>
            <div class="custom_block">
              <label class="">Message Category</label>
                 <select id="message_category" name="message_category" class="SlectBox">
                    <option <?php if($campaign->message_category == 'Reengagement'){ echo 'selected';} ?> value="Reengagement">Reengagement</option>
                    <option <?php if($campaign->message_category == 'Offer'){ echo 'selected';} ?> value="Offer">Offer</option>
                    <option <?php if($campaign->message_category == 'Engagement'){ echo 'selected';} ?> value="Engagement">Engagement </option>
                 </select>
              </div>

          </div>
          <div class="col-sm-6 col-xs-6">
              <div class="block SuggestionBox">
                <label class="title">Suggestion</label>
                  <span id="SuggestionBoxMsg">
                  <?php echo $suggestion; ?>
                  <?php if($googleSearchKeyword != ''){ echo $googleSearchKeyword;} ?>
                  <?php if($twitterSearchKeyword != ''){ echo $twitterSearchKeyword ;} ?>
                  </span>
              </div>
          </div>
        </div>

        <div class="row">
        	<div class="col-xs-12"><hr></div>
        </div>

        <div class="row" <?php if(count($campaign) > 0){ ?> style="display: none;" <?php }?>>
        	<div class="col-xs-12 platform">
            	<h2>Choose Push Platform</h2>
                <p>Start by choosing a mobile platform for this push campaign.</p>

                <ul>
                	<li><input type="radio" value="android" name="pushType" id="androidPush"><label for="androidPush"><i class="fa fa-android"></i><strong>Android Push</strong></label></li>
                	<li><input type="radio" value="ios" name="pushType" id="iosPush"><label for="iosPush"><i class="fa fa-apple"></i><strong>iOS Push</strong></label></li>
                </ul>
                <input type="hidden" id="selectedPlatform" value="<?php echo $campaign->platform;?>" />
                <input type="hidden" id="campaignId" value="<?php echo $campaign->id;?>" />
                <!-- <input type="hidden" id="campaign_type" value="" /> -->
                <span id="error_platform" style="color:#424141; font-size:12px;float:left;"></span>
            </div>1
        </div>
        <div id="showForm" <?php if(count($campaign) > 0 && $campaign->platform == 'android'){ ?> style="display: block;" <?php }else{?> style="display: none;" <?php }?>>

        <div class="row">
        	<div class="col-xs-12"><h3>Compopse Android Push
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
                <?php if($campaign->push_title != ''){
                    
                    $subject = $campaign->push_title;
                    $subject = str_replace('{{${email_address}}}', '{ { ${email_address} } }', $subject);
                    $subject = str_replace('{{${date_of_birth}}}', '{ { ${date_of_birth} } }', $subject);
                    $subject = str_replace('{{${first_name}}}', '{ { ${first_name} } }', $subject);
                    $subject = str_replace('{{${gender}}}', '{ { ${gender} } }', $subject);
                    $subject = str_replace('{{${last_name}}}', '{ { ${last_name} } }', $subject);
                    $subject = str_replace('{{${last_used_app_date}}}', '{ { ${last_used_app_date} } }', $subject);
                    $subject = str_replace('{{${most_recent_app_version}}}', '{ { ${most_recent_app_version} } }', $subject);
                    $subject = str_replace('{{${phone_number}}}', '{ { ${phone_number} } }', $subject);
                    $subject = str_replace('{{${time_zone}}}', '{ { ${time_zone} } }', $subject);
                    $subject = str_replace('{{${company}}}', '{ { ${company} } }', $subject);
                    $subject = str_replace('{{campaign.${name}}}', '{ { campaign.${name} } }', $subject);
                    $subject = str_replace('{{${set_user_to_unsubscribed_url}}}', '{ { ${set_user_to_unsubscribed_url} } }', $subject);
                    
                } ?>
                <label>Title</label>
                <input type="text" placeholder="Enter Title" id="push_title" <?php if(count($campaign) > 0){?>value="<?php echo $subject; ?>"<?php }else{?> value=""<?php }?> ondrop="return false;">
                <span id="error_pushTitle" style="color:#424141; font-size:12px;position: relative;bottom: 14px;"></span>
                <?php if($campaign->push_message != ''){
                    
                    $message = $campaign->push_message;
                    $message = str_replace('{{${email_address}}}', '{ { ${email_address} } }', $message);
                    $message = str_replace('{{${date_of_birth}}}', '{ { ${date_of_birth} } }', $message);
                    $message = str_replace('{{${first_name}}}', '{ { ${first_name} } }', $message);
                    $message = str_replace('{{${gender}}}', '{ { ${gender} } }', $message);
                    $message = str_replace('{{${last_name}}}', '{ { ${last_name} } }', $message);
                    $message = str_replace('{{${last_used_app_date}}}', '{ { ${last_used_app_date} } }', $message);
                    $message = str_replace('{{${most_recent_app_version}}}', '{ { ${most_recent_app_version} } }', $message);
                    $message = str_replace('{{${phone_number}}}', '{ { ${phone_number} } }', $message);
                    $message = str_replace('{{${time_zone}}}', '{ { ${time_zone} } }', $message);
                    $message = str_replace('{{${company}}}', '{ { ${company} } }', $message);
                    $message = str_replace('{{campaign.${name}}}', '{ { campaign.${name} } }', $message);
                    $message = str_replace('{{${set_user_to_unsubscribed_url}}}', '{ { ${set_user_to_unsubscribed_url} } }', $message);
                    
                } ?>
                <label>Message</label>
                <textarea placeholder="Enter Message" id="push_message" ondrop="return false;"><?php if(count($campaign) > 0 && $campaign->platform == 'android'){ echo str_replace("<br />", "\n", $message); }else{ echo ''; }?></textarea>
                <span id="error_pushMsg" style="color:#424141; font-size:12px;position: relative;bottom: 14px;"></span>

                <label>Push Notication Image (Optional) <a href="javascript:void(0)" onclick="removeImage();" class="btn white-btn" data-title="" style="width:auto;">Remove</a></label>
                <div class="imgInputField" ondrop="drop(event);" id="dropbox"><?php if($campaign->push_notification_image != ''){?>
                <img id="android_push_notification_image" src="<?php echo $campaign->push_notification_image; ?>">
                <?php } ?> </div>

                <label>Summery Text / Image Caption (Optional)</label>
                <input type="text" placeholder="Enter Text" id="summery_text" <?php if(count($campaign) > 0){?>value="<?php echo $campaign->summery_text; ?>"<?php }else{?> value=""<?php }?> ondrop="return false;">

            </div>

            <hr>

            <div class="block">
            	<label class="title"><i class="fa fa-hand-o-up"></i> ON CLICK BEHAVIOR</label>

                <small>Choose a custom URL to open when users click on this push notification. Note that you will need to update your app's broadcast receiver.</small>

                <select class="SlectBox" id="android_custom_url" name="android_custom_url">
                <option <?php if($campaign->custom_url == '1'){?>selected<?php }?> value="1">Redirect to Web URL</option>
                <option <?php if($campaign->custom_url == '2'){?>selected<?php }?> value="2">Deep link Into Application</option>
                <option <?php if($campaign->custom_url == '3'){?>selected<?php }?> value="3">Opens App</option>
                </select>

                <div class="col-xs-14" id="div_android_redirect_url" style="<?php if($campaign->custom_url == '1'){?>display:block;<?php }else{?>display:none;<?php }?>">
                <input type="text" name="android_redirect_url" id="android_redirect_url" placeholder="Example: https://www.example.com" value="<?php if($campaign->custom_url == '1'){ echo $campaign->redirect_url;}else{echo '';}?>" />
                <span id="error_android_redirect_url" style="font-size:12px;position: relative;top: -10px;"></span>
                </div>

                <div class="col-xs-14" id="div_android_deep_link" style="<?php if($campaign->custom_url == '2'){?>display:block;<?php }else{?>display:none;<?php }?>">
                <input type="text" name="android_deep_link" id="android_deep_link" placeholder="Example: myapp://deeplink" value="<?php if($campaign->custom_url == '2'){ echo $campaign->redirect_url;}else{echo '';}?>" />
                <span id="error_android_deep_link" style="font-size:12px;position: relative;top: -10px;"></span>
                </div>
            </div>

            <hr>

            <div class="block">
            	<label class="title"><i class="fa fa-mobile-phone"></i> DEVICE OPTIONS</label>
                <label><input <?php if($campaign->send_push_to_recently_used_device == 1){ echo 'checked';}?> name="send_push_to_recently_used_device" type="checkbox" value="1"> Only send this push to the user's most recently used device <i data-toggle="tooltip" title="If the user's most recent device is not push enabled, we will not send that user this message." class="fa fa-question-circle-o" aria-hidden="true"></i></label>
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
                                <img id="emoji6" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f60f.png" />
                                <!-- <p>Template 6</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji7" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61a.png" />
                                <!-- <p>Template 7</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji8" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61b.png" />
                                <!-- <p>Template 8</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji9" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61c.png" />
                                <!-- <p>Template 9</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji10" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61d.png" />
                                <!-- <p>Template 10</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji11" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61e.png" />
                                <!-- <p>Template 11</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji12" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f61f.png" />
                                <!-- <p>Template 12</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji13" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62a.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji14" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62b.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji15" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62c.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji16" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62d.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji17" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62e.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji18" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f62f.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji19" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f600.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji20" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f601.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji21" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f602.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji22" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f603.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji23" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f604.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji24" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f605.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji25" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f606.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji26" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f607.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji27" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f608.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji28" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f609.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji29" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f610.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji30" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f611.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji31" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f612.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji32" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f613.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji33" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f614.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji34" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f615.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji35" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f616.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji36" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f617.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji37" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f618.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji38" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f619.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji39" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f620.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji40" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f621.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji41" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f622.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji42" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f623.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji43" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f624.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji44" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f625.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji45" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f626.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji46" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f627.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji47" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f628.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji48" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f629.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji49" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f630.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji50" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f631.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji51" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f632.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji52" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f633.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji53" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f634.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji54" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f635.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji55" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f636.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji56" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f637.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji57" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f641.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji58" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f642.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji59" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f643.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji60" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f644.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji61" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f910.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji62" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f911.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji63" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f912.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji64" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f913.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji65" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f914.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji66" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f915.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji67" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f917.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji68" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f920.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji69" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f922.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji70" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f923.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji71" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f924.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji72" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f925.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji73" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/1f927.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji74" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/263a.png" />
                                <!-- <p>Template 13</p> -->
                            </a>
                        </div>
                        <div class="col-xs-2" draggable="true">
                            <a href="javascript:void(0)">
                                <img id="emoji75" onclick="addEmoji(this);" src="<?php echo base_url();?>assets/template/frontend/emojis/2639.png" />
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
                    <div class="giphySearch">
                        <span class="giphyInput">
                        <input id="androidGiphySearch" autofocus type="text" name="q" placeholder="Search GIPHY" style="width:100%;max-width:600px;outline:0;" value="">
                        <label><i class="fa fa-search" onclick="getGifImages('android');" ></i></label>
                       </span>
                        <!-- <ul class="searchResult" id="androidGiphy">

                        </ul> -->
                        <ul id="androidGiphImages">

                        </ul>
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

                    <em style="cursor: pointer;<?php if($campaign->push_img_url != ''){?>pointer-events: none;<?php }?>margin-bottom: 10px;float: left;" id="pushIconImage">
                    <span class="fileUploader">
                    	<!-- <input type="file"> -->

                        <i class="fa fa-cloud-upload"></i> Add Image
                    </span>
                    </em>

                    <div class="upload-image" style="position: relative;float: left;">
                    <i id="crossPushImage" class="push fa fa-times" aria-hidden="true" style="<?php if($campaign->push_icon != ''){?>display:block;<?php }else{?>display:none;<?php }?>"></i>
                    <img style="<?php if($campaign->push_icon != ''){?>display:block;<?php }else{?>display:none;<?php }?>width:30px;height:30px;border: none;" id="android_app_img" <?php if($campaign->push_icon != ''){?>src="<?php echo base_url().'upload/pushNotificationCampaigns/icon/'.$campaign->push_icon;?>"<?php }else{?>src=""<?php }?> alt="">
                    </div>

                    <span id="push_or" style="<?php if($campaign->push_icon == ''){ ?>display:block;<?php }else{?>display:none;<?php }?>">OR</span>

                    <input type="text" style="<?php if($campaign->push_icon == ''){ ?>display:block;<?php }else{?>display:none;<?php }?>" placeholder="Enter Image URL" id="push_img_url" <?php if(count($campaign) > 0){?>value="<?php echo $campaign->push_img_url; ?>"<?php }else{?> value=""<?php }?>>

                </div>
                <hr>

                <div class="block imageOption">
                	<label class="title"><i class="fa fa-image"></i> EXPANDED NOTIFICATION IMAGE</label>

                    <em style="cursor: pointer;<?php if($campaign->expanded_img_url != ''){?>pointer-events: none;<?php }?>margin-bottom: 10px;float: left;" id="expandedIconImage">
                    <span class="fileUploader">
                    	<!-- <input type="file"> -->
                        <i class="fa fa-cloud-upload"></i> Add Image
                    </span>
                    </em>

                    <div class="upload-image" style="position: relative;float: left;">
                    <i id="crossExpandedImage" class="push fa fa-times" aria-hidden="true" style="<?php if($campaign->expandedImage != ''){?>display:block;<?php }else{?>display:none;<?php }?>"></i>
                    <img style="<?php if($campaign->expandedImage != ''){?>display:block;<?php }else{?>display:none;<?php }?>width:30px;height:30px;border: none;" id="ios_app_img" <?php if($campaign->expandedImage != ''){?>src="<?php echo base_url().'upload/pushNotificationCampaigns/expandedImage/'.$campaign->expandedImage;?>"<?php }else{?>src=""<?php }?> alt="">
                    </div>
                    <span id="expanded_or" style="<?php if($campaign->expandedImage == ''){ ?>display:block;<?php }else{?>display:none;<?php }?>">OR</span>

                    <input type="text" style="<?php if($campaign->expandedImage == ''){ ?>display:block;<?php }else{?>display:none;<?php }?>" placeholder="Enter Image URL" id="expanded_img_url" <?php if(count($campaign) > 0){?>value="<?php echo $campaign->expanded_img_url; ?>"<?php }else{?> value=""<?php }?>>
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
        <div id="showForm_ios" <?php if(count($campaign) > 0 && $campaign->platform == 'iOS'){ ?> style="display: block;" <?php }else{?> style="display: none;" <?php }?>>

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
                <?php if($campaign->push_message != ''){
                    
                    $message1 = $campaign->push_message;
                    $message1 = str_replace('{{${email_address}}}', '{ { ${email_address} } }', $message1);
                    $message1 = str_replace('{{${date_of_birth}}}', '{ { ${date_of_birth} } }', $message1);
                    $message1 = str_replace('{{${first_name}}}', '{ { ${first_name} } }', $message1);
                    $message1 = str_replace('{{${gender}}}', '{ { ${gender} } }', $message1);
                    $message1 = str_replace('{{${last_name}}}', '{ { ${last_name} } }', $message1);
                    $message1 = str_replace('{{${last_used_app_date}}}', '{ { ${last_used_app_date} } }', $message1);
                    $message1 = str_replace('{{${most_recent_app_version}}}', '{ { ${most_recent_app_version} } }', $message1);
                    $message1 = str_replace('{{${phone_number}}}', '{ { ${phone_number} } }', $message1);
                    $message1 = str_replace('{{${time_zone}}}', '{ { ${time_zone} } }', $message1);
                    $message1 = str_replace('{{${company}}}', '{ { ${company} } }', $message1);
                    $message1 = str_replace('{{campaign.${name}}}', '{ { campaign.${name} } }', $message1);
                    $message1 = str_replace('{{${set_user_to_unsubscribed_url}}}', '{ { ${set_user_to_unsubscribed_url} } }', $message1);
                    
                } ?>
                <label>Message</label>
                <textarea placeholder="Enter Message" id="push_iOS_message" ondrop="return false;"><?php if(count($campaign) > 0 && $campaign->platform == 'iOS'){ echo str_replace("<br />", "\n", $message1); }else{ echo ''; }?></textarea>
                <span id="error_iOSpushMsg" style="color:#424141; font-size:12px;position: relative;bottom: 0px;"></span>

                <!-- <label>Summery Text / Image Caption (Optional)</label>
                <input type="text" placeholder="Enter Text" id="summery_text"> -->

                <label>Push Notication Image (Optional) <a href="javascript:void(0)" onclick="removeImage();" class="btn white-btn" data-title="" style="width:auto;">Remove</a></label>
                <div class="imgInputField" ondrop="drop(event);" id="dropbox1"><?php if($campaign->push_notification_image != ''){?>
                    <img id="ios_push_notification_image" src="<?php echo $campaign->push_notification_image; ?>">
                 <?php } ?>
                </div>

            </div>

            <hr>

            <div class="block">
            	<label class="title"><i class="fa fa-hand-o-up"></i> ON CLICK BEHAVIOR</label>

                <small>Choose a custom URL to open when users click on this push notification. Note that you will need to update your app's broadcast receiver.</small>

                <select class="SlectBox" id="ios_custom_url" name="ios_custom_url">
                <option <?php if($campaign->custom_url == '1'){?>selected<?php }?> value="1">Redirect to Web URL</option>
                <option <?php if($campaign->custom_url == '2'){?>selected<?php }?> value="2">Deep link Into Application</option>
                <option <?php if($campaign->custom_url == '3'){?>selected<?php }?> value="3">Opens App</option>
                </select>
                <div class="col-xs-14" id="div_ios_redirect_url" style="<?php if($campaign->custom_url == '1'){?>display:block;<?php }else{?>display:none;<?php }?>">
                <input type="text" name="ios_redirect_url" id="ios_redirect_url" placeholder="Example: https://www.example.com" value="<?php if($campaign->custom_url == '1'){ echo $campaign->redirect_url;}else{echo '';}?>" />
                <span id="error_ios_redirect_url" style="font-size:12px;position: relative;top: 0px;"></span>
                </div>
                <div class="col-xs-14" id="div_ios_deep_link" style="<?php if($campaign->custom_url == '2'){?>display:block;<?php }else{?>display:none;<?php }?>">
                <input type="text" name="ios_deep_link" id="ios_deep_link" placeholder="Example: myapp://deeplink" value="<?php if($campaign->custom_url == '2'){ echo $campaign->redirect_url;}else{echo '';}?>" />
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
                        <ul class="searchResult" id="iOSGiphy">

                        </ul>

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
	                <label><input <?php if($campaign->send_push_to_recently_used_device == 1){ echo 'checked';}?> name="send_push_to_recently_used_device" type="checkbox" value="1"> Only send this push to the user's most recently used device <i data-toggle="tooltip" title="If the user's most recent device is not push enabled, we will not send that user this message." class="fa fa-question-circle-o" aria-hidden="true"></i></label>
            		<label><input <?php if($campaign->limit_this_push_to_iPad_devices == 1){ echo 'checked';}?> name="limit_this_push_to_iPad_devices" type="checkbox" value="1"> Limit this push to iPad devices</label>
            		<label><input <?php if($campaign->limit_this_push_to_iphone_and_ipod_devices == 1){ echo 'checked';}?> name="limit_this_push_to_iphone_and_ipod_devices" type="checkbox" value="1"> Limit this push to iPhone and iPod devices</label>
            	</div>
<hr>

                <div class="block">
                	<label class="title"><i class="fa fa-file-code-o"></i> ATTRIBUTES & PROPERTIES (0 DETECTED)</label>
                    <h4>Make it Personal!</h4>
                    <small>Use our supported personalization attributes. Learn about how to use them here.</small>
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

        <div class="pagination defaultBtns" style="text-align:right; display:block">
        <?php //if($cookie_group != ''){ ?>
        <input type="hidden" id="groupId" value="<?php echo $app_groupId; ?>" />
        <?php //} ?>

        				<div class="fileUploader">
                            <upload-box></upload-box>
                            <input type="hidden" class="jfilestyle" id="push_icon" name="push_icon">
                            <input type="hidden" class="jfilestyle" id="expandedImage" name="expandedImage">
                        </div>
        	<input type="hidden" id="upload_push" value="<?php if($campaign->push_icon != ''){ ?>1<?php }else{?>2 <?php }?>" />
        	<input type="hidden" id="upload_extended" value="<?php if($campaign->expandedImage != ''){ ?>1<?php }else{}?>" />
            <?php if($campaign->automation != '1'){ ?>
            <?php if($campaign->isDraft == '1'){?><a href="javascript:void(0)" onclick="return saveComposeAsDraft();" class="btn">Save As Draft</a><?php } ?>
            <a href="javascript:void(0)" onclick="return validateCompose();" class="btn">Delivery</a>
            <?php }else{ ?>
            <a href="javascript:void(0)" class="btn" onclick="return saveAutomation();">Update</a>
            <a href="<?php echo base_url();?>appUser/confirmAutomation" class="modalPopup storeButton" data-class="fbPop submitOffer2 addLocation" data-title="Create Workflow" data-backdrop="static" data-keyboard="false" id="confirmAutomation" style="display: none;"></a>
            <?php } ?>
        </div>

		<!--
        <div class="pagination firstBtns">

            <a href="javascript:void(0)" onclick="return saveComposeAsDraft();" class="btn">Save As Draft</a>
            <a href="javascript:void(0)" class="btn" onclick="return validateCompose();">Delivery</a>
        </div>-->

        </div>

    	<div class="tab-pane" id="delivery">
        <div class="border">
        	<div class="row">
            	<div class="col-xs-12">
                <div class="block">
                	<label class="title">Delivery Type</label>
                <ul class="deliveryType">
                    	<li>
                	<input <?php if($campaign->delivery_type == '1'){ echo 'checked'; }?> type="radio" name="deliveryType" id="scheduleDelivery" checked value="schedule-delivery">
                    <label for="scheduleDelivery">
                    	<strong>Schedule Delivery</strong>
                        <small>Send at designated time of the day/week</small>
                    </label>
                </li>
                <li>
                	<input <?php if($campaign->delivery_type == '2'){ echo 'checked'; }?> type="radio" name="deliveryType" id="actionDelivery" value="action-delivery">
                    <label for="actionDelivery">
                    	<strong>Action Based Delivery</strong>
                        <small>Send when user perform an action</small>
                    </label>
                </li>
               </ul>
            </div>

			<hr>

            <div class="block active hiddenDV timeBased delivery" id="schedule-delivery" <?php if($campaign->delivery_type == '1' || $campaign->delivery_type == '0'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?> >
            	<label class="title">Time Based Scheduling</label>
            	<ul>
                    <li>
                        <input type="radio" name="timeBased" id="atLaunch" value="at-launch" <?php if($campaign->time_based_scheduling == '1'){ echo 'checked'; }?>>
                        <label for="atLaunch">
                        <strong>At Launch</strong>
                        <small>Send notifications as soon as campaign is launched</small>
                        </label>
                    </li>
                    <li>
                        <input type="radio" name="timeBased" id="designatedTime" value="designated-time" <?php if($campaign->time_based_scheduling == '2'){ echo 'checked'; }?>>
                        <label for="designatedTime">
                        <strong>Designated Time</strong>
                        <small>Choose an optimal time for users to receive this message</small>
                        </label>
                    </li>
                    <li>
                        <input type="radio" name="timeBased" id="intelligentDelivery" value="intelligent-delivery" <?php if($campaign->time_based_scheduling == '3'){ echo 'checked'; }?>>
                        <label for="intelligentDelivery">
                        <strong>Intelligent Delivery</strong>
                        <small>Each user will receive the campaign at the time they are most likely to engage</small>
                        </label>
                    </li>
                </ul>

                <div class="wrap">

                <div class="hiddenDV active" id="at-launch" <?php if($campaign->time_based_scheduling == '1' || $campaign->time_based_scheduling == '0'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
                <div class="row">
                   <div class="col-xs-12">
            		<ul class="deliveryType">
                    	<li>
                        	<input type="checkbox" name="atlaunch" id="atlaunch1" value="1" <?php if($campaign->time_based_scheduling == '1' && $campaign->reEligible_to_receive_campaign == '1'){ echo 'checked'; }?> >
                            <label for="atlaunch1">
                            <small>Allow users to become re-eligible to receive campaign</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="atlaunch" id="atlaunch2" value="1" <?php if($campaign->time_based_scheduling == '1' && $campaign->ignore_frequency_capping_settings == '1'){ echo 'checked'; }?>>
                            <label for="atlaunch2">
                            <small>Ignore frequency capping settings for this campaign</small>
                            </label>
                        </li>
                  </ul>
                  </div>
                </div>
                </div>

                <div class="col-xs-12 time delivery-specific" id="atlaunch_reEligible" <?php if($campaign->time_based_scheduling == '1' && $campaign->reEligible_to_receive_campaign == '1'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text" name="atlaunch_time" id="atlaunch_reEligibleTime" value="<?php if($campaign->time_based_scheduling == '1'){ echo $campaign->reEligibleTime;}else{ echo '1'; } ?>"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="atlaunch_reEligibleTimeInterval">
					                    <option <?php if($campaign->time_based_scheduling == '1' && $campaign->reEligibleTimeInterval == 'minutes'){ echo 'selected';} ?> value="minutes">minutes</option>
					                    <option <?php if($campaign->time_based_scheduling == '1' && $campaign->reEligibleTimeInterval == 'days'){ echo 'selected';} ?> value="days" selected>days</option>
					                    <option <?php if($campaign->time_based_scheduling == '1' && $campaign->reEligibleTimeInterval == 'weeks'){ echo 'selected';} ?> value="weeks">weeks</option>
					                    <option <?php if($campaign->time_based_scheduling == '1' && $campaign->reEligibleTimeInterval == 'months'){ echo 'selected';} ?> value="months">months</option>
					                </select>
		                  		</div>
		                  	</div>
		                  	<div><span id="error_atlaunch_reEligible" style="font-size: 12px;"></span></div>
                  		</div>
                  	</div>
			</div>

                <div class="hiddenDV" id="designated-time" <?php if($campaign->time_based_scheduling == '2'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                    <label>Send</label>
                    <select class="SlectBox" id="send">
                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->send == 'once'){ echo 'selected';} ?> value="once">Once</option>
                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->send == 'daily'){ echo 'selected';} ?> value="daily">Daily</option>
                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->send == 'weekly'){ echo 'selected';} ?> value="weekly">Weekly</option>
                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->send == 'monthly'){ echo 'selected';} ?> value="monthly">Monthly</option>
                    </select>
                    </div>

                    <div class="col-sm-4 col-xs-12 time">
                    <label>Starting At</label>
                    <div class="row">
                    <div class="col-xs-4">
	                    <select class="SlectBox" id="starting_at_hour">
	                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '12'){ echo 'selected';} ?> value="12">12</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '1'){ echo 'selected';} ?> value="1">1</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '2'){ echo 'selected';} ?> value="2">2</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '3'){ echo 'selected';} ?> value="3">3</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '4'){ echo 'selected';} ?> value="4">4</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '5'){ echo 'selected';} ?> value="5">5</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '6'){ echo 'selected';} ?> value="6">6</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '7'){ echo 'selected';} ?> value="7">7</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '8'){ echo 'selected';} ?> value="8">8</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '9'){ echo 'selected';} ?> value="9">9</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '10'){ echo 'selected';} ?> value="10">10</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_hour == '11'){ echo 'selected';} ?> value="11">11</option>
	                    </select>
                    </div>
                    <div class="col-xs-4">
	                    <select class="SlectBox" id="starting_at_min">
	                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '00'){ echo 'selected';} ?> value="00">00</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '05'){ echo 'selected';} ?> value="05">05</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '10'){ echo 'selected';} ?> value="10">10</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '15'){ echo 'selected';} ?> value="15">15</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '20'){ echo 'selected';} ?>value="20">20</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '25'){ echo 'selected';} ?> value="25">25</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '30'){ echo 'selected';} ?> value="30">30</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '35'){ echo 'selected';} ?> value="35">35</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '40'){ echo 'selected';} ?> value="40">40</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '45'){ echo 'selected';} ?> value="45">45</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '50'){ echo 'selected';} ?> value="50">50</option>
						<option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_min == '55'){ echo 'selected';} ?> value="55">55</option>
	                    </select>
                    </div>
                    <div class="col-xs-4">
	                    <select class="SlectBox" id="starting_at_am_pm">
	                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_am_pm == 'AM'){ echo 'selected';} ?> value="AM">AM</option>
	                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->starting_at_am_pm == 'PM'){ echo 'selected';} ?> value="PM">PM</option>
	                    </select>
                    </div>
                    </div>
                    </div>

                    <div class="col-sm-4 col-xs-12" id="onDate" <?php if($campaign->time_based_scheduling == '2' && $campaign->send == 'once'){?> style="display:block;"<?php }else{?>style="display:none;"<?php }?> >
                    <label>On Date</label>
                    <div class="calendar">
                    <input type="text" class="date" id="date" value="<?php if($campaign->time_based_scheduling == '2' && $campaign->once_date != '0000-00-00'){echo $campaign->once_date; }else{ echo date('d-m-Y'); }?>">
                    </div>
                    </div>

                    <div class="col-xs-12" id="daily" <?php if($campaign->time_based_scheduling == '2' && $campaign->send == 'daily'){?>style="display: block;"<?php }else{?> style="display: none;"<?php }?>>
                    <label>Every day(s)</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="everyDay">
			                    <?php for($i=1;$i<=28;$i++){?>
			                    <option <?php if($campaign->everyDay == $i){ echo 'selected'; }?> value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="beginning_date" value="<?php if($campaign->send == 'daily'){ echo $campaign->beginning_date; }else{ echo date('d-m-Y');}?>">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="ending">
		                	 <option <?php if($campaign->send == 'daily' && $campaign->ending == 'never'){ echo 'selected'; }?> value="never">Never</option>
		                	 <option <?php if($campaign->send == 'daily' &&  $campaign->ending == 'on_the_date'){ echo 'selected'; }?> value="on_the_date">On the date</option>
		                	 <option <?php if($campaign->send == 'daily' &&  $campaign->ending == 'after'){ echo 'selected'; }?> value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_ending_on_the_date" style="<?php if($campaign->ending == 'on_the_date'){ ?>display:block;<?php }else{?>display:none;<?php }?>" >
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="ending_on_the_date" value="<?php if($campaign->send == 'daily'){ echo $campaign->ending_on_the_date; }else{ echo date('d-m-Y'); }?>">
		                	</div>

		                	<div class="col-sm-2" id="section_ending_after_occurances" style="<?php if($campaign->ending == 'after'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="ending_after_occurances" maxlength="2" style="width:50%" value="<?php if($campaign->send == 'daily'){ echo $campaign->ending_after_occurances;}else{ echo date('d-m-Y'); }?>">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="weekly" <?php if($campaign->send == 'weekly'){?>style="display: block;"<?php }else{?> style="display: none;"<?php }?>>

                    <!--
                    <label>Every weeks</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="everyWeek">
			                    <?php //for($i=1;$i<=30;$i++){?>
			                    <option value="<?php //echo $i;?>"><?php echo $i; ?></option>
			                    <?php //}?>
			                    </select>
		                    </div>
		                </div>
		                 -->
		                <div class="row">
		      				<div class="col-xs-12 timeBased">
		      				<label>On the days</label>
            		<ul>
            		<?php
					if (strpos($campaign->weekday, 'Sun') !== false) {
    					$sunChecked = 'checked';
					}else{
						$sunChecked = '';
            		}?>
                    	<li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday1" value="Sun" <?php echo $sunChecked; ?> >
                            <label for="weekday1">
                            <small>Sunday</small>
                            </label>
                        </li>
                    <?php
					if (strpos($campaign->weekday, 'Mon') !== false) {
    					$monChecked = 'checked';
					}else{
						$monChecked = '';
            		}?>
                    	<li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday2" value="Mon" <?php echo $monChecked; ?>>
                            <label for="weekday2">
                            <small>Monday</small>
                            </label>
                        </li>
                         <?php
						if (strpos($campaign->weekday, 'Tue') !== false) {
	    					$tueChecked = 'checked';
						}else{
							$tueChecked = '';
	            		}?>
                    	<li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday3" value="Tue" <?php echo $tueChecked; ?>>
                            <label for="weekday3">
                            <small>Tuesday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->weekday, 'Wed') !== false) {
	    					$wedChecked = 'checked';
						}else{
							$wedChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday4" value="Wed" <?php echo $wedChecked; ?>>
                            <label for="weekday4">
                            <small>Wednesday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->weekday, 'Thu') !== false) {
	    					$thuChecked = 'checked';
						}else{
							$thuChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday5" value="Thu" <?php echo $thuChecked; ?>>
                            <label for="weekday5">
                            <small>Thursday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->weekday, 'Fri') !== false) {
	    					$friChecked = 'checked';
						}else{
							$friChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday6" value="Fri" <?php echo $friChecked; ?>>
                            <label for="weekday6">
                            <small>Friday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->weekday, 'Sat') !== false) {
	    					$satChecked = 'checked';
						}else{
							$satChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="weekday" id="weekday7" value="Sat" <?php echo $satChecked; ?>>
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
		                	<input type="text" class="date" id="weeks_beginning_date" value="<?php if($campaign->send == 'weekly'){ echo $campaign->beginning_date; }else{ echo date('d-m-Y');}?>">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="weeks_ending">
		                	 <option <?php if($campaign->send == 'weekly' && $campaign->ending == 'never'){ echo 'selected'; }?> value="never">Never</option>
		                	 <option <?php if($campaign->send == 'weekly' && $campaign->ending == 'on_the_date'){ echo 'selected'; }?> value="on_the_date">On the date</option>
		                	 <option <?php if($campaign->send == 'weekly' && $campaign->ending == 'after'){ echo 'selected'; }?> value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_weeks_ending_on_the_date" style="<?php if($campaign->send == 'weekly' && $campaign->ending == 'on_the_date'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="weeks_ending_on_the_date" value="<?php if($campaign->send == 'weekly'){echo $campaign->ending_on_the_date;}else{ echo date('d-m-Y');}?>">
		                	</div>

		                	<div class="col-sm-2" id="section_weeks_ending_after_occurances" style="<?php if($campaign->send == 'weekly' && $campaign->ending == 'after'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="weeks_ending_after_occurances" maxlength="2" style="width:50%" value="<?php if($campaign->send == 'weekly'){echo $campaign->ending_after_occurances;}else{ echo '1'; }?>">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="monthly" <?php if($campaign->send == 'monthly'){?>style="display: block;"<?php }else{?> style="display: none;"<?php }?>>
                    <label>Every months</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="everyMonth">
			                    <?php for($i=1;$i<=12;$i++){?>
			                    <option <?php if($campaign->everyMonth == $i){echo 'selected';} ?> value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="month_beginning_date" value="<?php if($campaign->send == 'monthly'){ echo $campaign->beginning_date;}else{ echo date('d-m-Y');} ?>" >
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="month_ending">
		                	 <option <?php if($campaign->send == 'monthly' && $campaign->ending == 'never'){ echo 'selected'; }?> value="never">Never</option>
		                	 <option <?php if($campaign->send == 'monthly' && $campaign->ending == 'on_the_date'){ echo 'selected'; }?> value="on_the_date">On the date</option>
		                	 <option <?php if($campaign->send == 'monthly' && $campaign->ending == 'after'){ echo 'selected'; }?> value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_monthly_ending_on_the_date" style="<?php if($campaign->send == 'monthly' && $campaign->ending == 'on_the_date'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="monthly_ending_on_the_date" value="<?php if($campaign->send == 'monthly'){echo $campaign->ending_on_the_date;}else{ echo date('d-m-Y');}?>">
		                	</div>

		                	<div class="col-sm-2" id="section_monthly_ending_after_occurances" style="<?php if($campaign->send == 'monthly' && $campaign->ending == 'after'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="monthly_ending_after_occurances" maxlength="2" style="width:50%" value="<?php if($campaign->send == 'monthly'){echo $campaign->ending_after_occurances;}else{ echo '1'; }?>">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12 timeBased">
            		<ul>
                    	<li>
                        	<input type="checkbox" name="designatedtime" id="designatedtime1" <?php if($campaign->time_based_scheduling == '2' && $campaign->send_campaign_to_users_in_their_local_time_zone == '1'){ echo 'checked';}?>>
                            <label for="designatedtime1">
                            <small>Send campaign to users in their local time zone</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="designatedtime" id="designatedtime2" <?php if($campaign->time_based_scheduling == '2' && $campaign->reEligible_to_receive_campaign == '1'){ echo 'checked';}?>>
                            <label for="designatedtime2">
                            <small>Allow users to become re-eligible to receive campaign</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="designatedtime" id="designatedtime3" <?php if($campaign->time_based_scheduling == '2' && $campaign->ignore_frequency_capping_settings == '1'){ echo 'checked';}?>>
                            <label for="designatedtime3">
                            <small>Ignore frequency capping settings for this campaign</small>
                            </label>
                        </li>
                  </ul>
                  </div>

                  <div class="col-xs-12 time delivery-specific" id="designatedTime_reEligible" <?php if($campaign->time_based_scheduling == '2' && $campaign->reEligible_to_receive_campaign == '1'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text" name="designatedTime_reEligibleTime" id="designatedTime_reEligibleTime" value="<?php echo $campaign->reEligibleTime; ?>"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="designatedTime_reEligibleTimeInterval">
					                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->reEligibleTimeInterval == 'minutes'){ echo 'selected';} ?> value="minutes">minutes</option>
					                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->reEligibleTimeInterval == 'days'){ echo 'selected';} ?> value="days" selected>days</option>
					                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->reEligibleTimeInterval == 'weeks'){ echo 'selected';} ?> value="weeks">weeks</option>
					                    <option <?php if($campaign->time_based_scheduling == '2' && $campaign->reEligibleTimeInterval == 'months'){ echo 'selected';} ?> value="months">months</option>
					                </select>
		                  		</div>
		                  	</div>
		                  	<div><span id="error_designatedTime_reEligible" style="font-size: 12px;"></span></div>
                  		</div>
                  	</div>
				</div>
                </div>
                </div>


                <div class="hiddenDV" id="intelligent-delivery" <?php if($campaign->time_based_scheduling == '3'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                    <label>Send</label>
                    <select class="SlectBox" id="intelligent_send">
                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'once'){ echo 'selected';} ?> value="once">Once</option>
                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'daily'){ echo 'selected';} ?> value="daily">Daily</option>
                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'weekly'){ echo 'selected';} ?> value="weekly">Weekly</option>
                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'monthly'){ echo 'selected';} ?> value="monthly">Monthly</option>
                    </select>
                    </div>

                    <div class="col-sm-4 col-xs-12 time">
                    <p>at optimal time</p>
                    </div>
                    <div class="col-sm-4 col-xs-12" id="intelligent_on_date" <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'once'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
                    <label>On Date</label>
                    <div class="calendar">
                    <input type="text" class="date" id="intelligent_onDate" value="<?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'once'){ echo $campaign->intelligent_on_date; }else{ date('Y-m-d'); }?>">
                    </div>
                    </div>

                    <div class="col-xs-12" id="intelligent_daily" <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_send == 'daily'){?>style="display: block;"<?php }else{?> style="display: none;"<?php }?>>
                    <label>Every day(s)</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="intelligent_everyDay">
			                    <?php for($i=1;$i<=28;$i++){?>
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->intelligent_everyDay == $i){ echo 'selected'; }?> value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="intelligent_beginning_date" value="<?php if($campaign->intelligent_send == 'daily'){ echo $campaign->intelligent_beginning_date; }else{ echo date('d-m-Y');}?>">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="intelligent_ending">
		                	 <option <?php if($campaign->intelligent_send == 'daily' &&  $campaign->intelligent_ending == 'never'){ echo 'selected'; }?> value="never">Never</option>
		                	 <option <?php if($campaign->intelligent_send == 'daily' && $campaign->intelligent_ending == 'on_the_date'){ echo 'selected'; }?> value="on_the_date">On the date</option>
		                	 <option <?php if($campaign->intelligent_send == 'daily' && $campaign->intelligent_ending == 'after'){ echo 'selected'; }?> value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_daily_ending_on_the_date" style="<?php if($campaign->intelligent_ending == 'on_the_date'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="intelligent_daily_ending_on_the_date" value="<?php if($campaign->intelligent_send == 'daily'){ echo $campaign->intelligent_ending_on_the_date; }else{ echo date('d-m-Y');}?>">
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_daily_ending_after_occurances" style="<?php if($campaign->intelligent_ending == 'after'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="intelligent_daily_ending_after_occurances" maxlength="2" style="width:50%" value="<?php if($campaign->intelligent_send == 'daily'){ echo $campaign->intelligent_ending_after_occurances; }else{ echo '1'; }?>">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="intelligent_weekly" <?php if($campaign->intelligent_send == 'weekly'){?>style="display: block;"<?php }else{?> style="display: none;"<?php }?>>

					<!--
                    <label>Every weeks</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="intelligent_everyWeek">
			                    <?php //for($i=1;$i<=30;$i++){?>
			                    <option value="<?php //echo $i;?>"><?php echo $i; ?></option>
			                    <?php //}?>
			                    </select>
		                    </div>
		                </div>-->
		                <div class="row">
		      				<div class="col-xs-12 timeBased">
		      				<label>On the days</label>
            		<ul>
                    	<?php
						if (strpos($campaign->intelligent_weekday, 'Sun') !== false) {
	    					$sunChecked = 'checked';
						}else{
							$sunChecked = '';
	            		}?>
                    	<li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday1" value="Sun" <?php echo $sunChecked;?> >
                            <label for="intelligent_weekday1">
                            <small>Sunday</small>
                            </label>
                        </li>
                        <?php
						if (strpos($campaign->intelligent_weekday, 'Mon') !== false) {
	    					$monChecked = 'checked';
						}else{
							$monChecked = '';
	            		}?>
                    	<li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday2" value="Mon" <?php echo $monChecked;?> >
                            <label for="intelligent_weekday2">
                            <small>Monday</small>
                            </label>
                        </li>
                        <?php
						if (strpos($campaign->intelligent_weekday, 'Tue') !== false) {
	    					$tueChecked = 'checked';
						}else{
							$tueChecked = '';
	            		}?>
                    	<li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday3" value="Tue" <?php echo $tueChecked;?> >
                            <label for="intelligent_weekday3">
                            <small>Tuesday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->intelligent_weekday, 'Wed') !== false) {
	    					$wedChecked = 'checked';
						}else{
							$wedChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday4" value="Wed" <?php echo $wedChecked;?> >
                            <label for="intelligent_weekday4">
                            <small>Wednesday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->intelligent_weekday, 'Thu') !== false) {
	    					$thuChecked = 'checked';
						}else{
							$thuChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday5" value="Thu" <?php echo $thuChecked;?>>
                            <label for="intelligent_weekday5">
                            <small>Thursday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->intelligent_weekday, 'Fri') !== false) {
	    					$friChecked = 'checked';
						}else{
							$friChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday6" value="Fri" <?php echo $friChecked;?>>
                            <label for="intelligent_weekday6">
                            <small>Friday</small>
                            </label>
                        </li>

                        <?php
						if (strpos($campaign->intelligent_weekday, 'Sat') !== false) {
	    					$satChecked = 'checked';
						}else{
							$satChecked = '';
	            		}?>
                        <li style="width:auto">
                        	<input type="checkbox" name="intelligent_weekday" id="intelligent_weekday7" value="Sat" <?php echo $satChecked;?> >
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
		                	<input type="text" class="date" id="intelligent_weeks_beginning_date" value="<?php if($campaign->intelligent_send == 'weekly'){ echo $campaign->intelligent_beginning_date; }else{ echo date('d-m-Y');}?>">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="intelligent_weeks_ending">
		                	 <option <?php if($campaign->intelligent_send == 'weekly' && $campaign->intelligent_ending == 'never'){ echo 'selected'; }?> value="never">Never</option>
		                	 <option <?php if($campaign->intelligent_send == 'weekly' && $campaign->intelligent_ending == 'on_the_date'){ echo 'selected'; }?> value="on_the_date">On the date</option>
		                	 <option <?php if($campaign->intelligent_send == 'weekly' && $campaign->intelligent_ending == 'after'){ echo 'selected'; }?> value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_weekly_ending_on_the_date" style="<?php if($campaign->intelligent_send == 'weekly' && $campaign->intelligent_ending == 'on_the_date'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="intelligent_weekly_ending_on_the_date" value="<?php if($campaign->intelligent_send == 'weekly'){echo $campaign->intelligent_ending_on_the_date;}else{ echo date('d-m-Y');}?>" >
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_weekly_ending_after_occurances" style="<?php if($campaign->intelligent_send == 'weekly' && $campaign->intelligent_ending == 'after'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="intelligent_weekly_ending_after_occurances" maxlength="2" style="width:50%" value="<?php if($campaign->intelligent_send == 'weekly'){echo $campaign->intelligent_ending_after_occurances;}else{ echo '1'; }?>">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                    <div class="col-xs-12" id="intelligent_monthly" <?php if($campaign->intelligent_send == 'monthly'){?>style="display: block;"<?php }else{?> style="display: none;"<?php }?>>
                    <label>Every months</label>
                    	<div class="row">
		      				<div class="col-sm-4 col-xs-12">
			                    <select class="SlectBox" id="intelligent_everyMonth">
			                    <?php for($i=1;$i<=12;$i++){?>
			                    <option <?php if($campaign->intelligent_everyMonth == $i){echo 'selected';} ?> value="<?php echo $i;?>"><?php echo $i; ?></option>
			                    <?php }?>
			                    </select>
		                    </div>
		                </div>
		                <div class="row">
		                	<div class="col-sm-4 col-xs-12">
		                	<label>Beginning</label>
		                	<div class="calendar">
		                	<input type="text" class="date" id="intelligent_month_beginning_date" value="<?php if($campaign->intelligent_send == 'monthly'){ echo $campaign->intelligent_beginning_date;}else{ echo date('d-m-Y');} ?>">
		                	</div>
		                	</div>

		                	<div class="col-sm-4 col-xs-12">
		                	<label>Ending</label>
		                	 <select class="SlectBox" id="intelligent_month_ending">
		                	 <option <?php if($campaign->intelligent_send == 'monthly' && $campaign->intelligent_ending == 'never'){ echo 'selected'; }?> value="never">Never</option>
		                	 <option <?php if($campaign->intelligent_send == 'monthly' && $campaign->intelligent_ending == 'on_the_date'){ echo 'selected'; }?> value="on_the_date">On the date</option>
		                	 <option <?php if($campaign->intelligent_send == 'monthly' && $campaign->intelligent_ending == 'after'){ echo 'selected'; }?> value="after">After</option>
		                	 </select>
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_monthly_ending_on_the_date" style="<?php if($campaign->intelligent_send == 'monthly' && $campaign->intelligent_ending == 'on_the_date'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" class="date" id="intelligent_monthly_ending_on_the_date" value="<?php if($campaign->intelligent_send == 'monthly'){echo $campaign->intelligent_ending_on_the_date;}else{ echo date('d-m-Y');}?>">
		                	</div>

		                	<div class="col-sm-2" id="section_intelligent_monthly_ending_after_occurances" style="<?php if($campaign->intelligent_send == 'monthly' && $campaign->intelligent_ending == 'after'){ ?>display:block;<?php }else{?>display:none;<?php }?>">
		                		<label>&nbsp;&nbsp;&nbsp;</label>
		                		<input type="text" id="intelligent_weekly_monthly_after_occurances" maxlength="2" style="width:50%" value="<?php if($campaign->intelligent_send == 'monthly'){echo $campaign->intelligent_ending_after_occurances;}else{ echo '1'; }?>">
		                		<small>occurrences</small>
		                	</div>

		                </div>
                    </div>

                   <div class="col-xs-12 timeBased">
            		<ul>
                    	<li>
                        	<input type="checkbox" name="intelliSent" id="intelliSent1" value="intelliSent1" <?php if($campaign->time_based_scheduling == '3' && $campaign->send_this_campaign_during_a_specific_portion_of_day == '1'){ echo 'checked';} ?>>
                            <label for="intelliSent1">
                            <small>Only send this campaign during a specific portion of the day</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="intelliSent" id="intelliSent2" <?php if($campaign->time_based_scheduling == '3' && $campaign->reEligible_to_receive_campaign == '1'){ echo 'checked'; }?>>
                            <label for="intelliSent2">
                            <small>Allow users to become re-eligible to receive campaign</small>
                            </label>
                        </li>
                    	<li>
                        	<input type="checkbox" name="intelliSent" id="intelliSent3" <?php if($campaign->time_based_scheduling == '3' && $campaign->ignore_frequency_capping_settings == '1'){ echo 'checked'; }?>>
                            <label for="intelliSent3">
                            <small>Ignore frequency capping settings for this campaign</small>
                            </label>
                        </li>
                  </ul>
                  </div>


                  <div class="col-xs-12 time delivery-specific" id="specificPortion" style="<?php if($campaign->time_based_scheduling == '3' && $campaign->send_this_campaign_during_a_specific_portion_of_day == '1'){ echo 'display:block;';}else{ echo 'display:none;';} ?>">

                  	<div class="row">
                  		<div class="col-sm-6">
                  		<div class="col-xs-2"><p>Between</p></div>
                  			<div class="col-xs-3">
		                	 	<select class="SlectBox" id="specific_start_hours">
		                	 	<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '12'){ echo 'selected';}?> value="12">12</option>
		                	 	<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '1'){ echo 'selected';}?> value="1">1</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '2'){ echo 'selected';}?> value="2">2</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '3'){ echo 'selected';}?> value="3">3</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '4'){ echo 'selected';}?> value="4">4</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '5'){ echo 'selected';}?> value="5">5</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '6'){ echo 'selected';}?> value="6">6</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '7'){ echo 'selected';}?> value="7">7</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '8'){ echo 'selected';}?> value="8" selected>8</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '9'){ echo 'selected';}?> value="9">9</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '10'){ echo 'selected';}?> value="10">10</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_hours == '11'){ echo 'selected';}?> value="11">11</option>
		                	 </select>
		                	</div>
		                	<div class="col-xs-3">
			                    <select class="SlectBox" id="specific_start_mins">
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '00'){ echo 'selected';}?> value="00">00</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '05'){ echo 'selected';}?> value="05">05</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '10'){ echo 'selected';}?> value="10">10</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '15'){ echo 'selected';}?> value="15">15</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '20'){ echo 'selected';}?> value="20">20</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '25'){ echo 'selected';}?> value="25">25</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '30'){ echo 'selected';}?> value="30">30</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '35'){ echo 'selected';}?> value="35">35</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '40'){ echo 'selected';}?> value="40">40</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '45'){ echo 'selected';}?> value="45">45</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '50'){ echo 'selected';}?> value="50">50</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_mins == '55'){ echo 'selected';}?> value="55">55</option>
			                    </select>
                    		</div>
		                    <div class="col-xs-3">
			                    <select class="SlectBox" id="specific_start_am_pm">
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_am_pm == 'AM'){ echo 'selected';}?> value="AM">AM</option>
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_start_am_pm == 'PM'){ echo 'selected';}?> value="PM">PM</option>
			                    </select>
		                    </div>

		                   </div>

		                   <div class="col-sm-6">
		                 	<div class="col-xs-2"><p>And</p></div>
		                    <div class="col-xs-3">
		                	 	<select class="SlectBox" id="specific_end_hours">
		                	 	<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '12'){ echo 'selected';}?> value="12">12</option>
		                	 	<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '1'){ echo 'selected';}?> value="1">1</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '2'){ echo 'selected';}?> value="2">2</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '3'){ echo 'selected';}?> value="3">3</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '4'){ echo 'selected';}?> value="4">4</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '5'){ echo 'selected';}?> value="5">5</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '6'){ echo 'selected';}?> value="6">6</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '7'){ echo 'selected';}?> value="7">7</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '8'){ echo 'selected';}?> value="8">8</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '9'){ echo 'selected';}?> value="9">9</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '10'){ echo 'selected';}?> value="10">10</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_hours == '11'){ echo 'selected';}?> value="11">11</option>
		                	 </select>
		                	</div>
		                	<div class="col-xs-3">
			                    <select class="SlectBox" id="specific_end_mins">
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '00'){ echo 'selected';}?> value="00">00</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '05'){ echo 'selected';}?> value="05">05</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '10'){ echo 'selected';}?> value="10">10</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '15'){ echo 'selected';}?> value="15">15</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '20'){ echo 'selected';}?> value="20">20</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '25'){ echo 'selected';}?> value="25">25</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '30'){ echo 'selected';}?> value="30">30</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '35'){ echo 'selected';}?> value="35">35</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '40'){ echo 'selected';}?> value="40">40</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '45'){ echo 'selected';}?> value="45">45</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '50'){ echo 'selected';}?> value="50">50</option>
								<option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_mins == '55'){ echo 'selected';}?> value="55">55</option>
			                    </select>
                    		</div>
		                    <div class="col-xs-3">
			                    <select class="SlectBox" id="specific_end_am_pm">
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_am_pm == 'AM'){ echo 'selected';}?> value="AM">AM</option>
			                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->specific_end_am_pm == 'PM'){ echo 'selected';}?> value="PM">PM</option>
			                    </select>
		                    </div>
                  	</div>
                  </div>
			</div>

			<div class="col-xs-12 time delivery-specific" id="intelligentTime_reEligible" <?php if($campaign->time_based_scheduling == '3' && $campaign->reEligible_to_receive_campaign == '1'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text" name="intelligentTime_reEligibleTime" id="intelligentTime_reEligibleTime" value="<?php if($campaign->time_based_scheduling == '3'){ echo $campaign->reEligibleTime; }else{ echo '1'; } ?>"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="intelligentTime_reEligibleTimeInterval">
					                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->reEligibleTimeInterval == 'minutes'){ echo 'selected';} ?> value="minutes">minutes</option>
					                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->reEligibleTimeInterval == 'days'){ echo 'selected';} ?> value="days" selected>days</option>
					                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->reEligibleTimeInterval == 'weeks'){ echo 'selected';} ?> value="weeks">weeks</option>
					                    <option <?php if($campaign->time_based_scheduling == '3' && $campaign->reEligibleTimeInterval == 'months'){ echo 'selected';} ?> value="months">months</option>
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


            </div><!-- End Schedule Delivery -->


            <div class="block hiddenDV delivery" id="action-delivery" <?php if($campaign->delivery_type == '2'){?> style="display:block;"<?php }else{?>style="display:none;" <?php } ?>>
            	<div class="row">
               <div class="col-sm-12 col-xs-12">
                    <label class="title">Action-Based Scheduling</label>
                 </div>
                 <div class="row">
                 	<div class="col-sm-12 col-xs-12">
		                 <div class="col-sm-9 col-xs-12">
		                    <label>New Trigger Action</label>
		                    <?php
						if (strpos($campaign->triggerAction, '1') !== false) {
	    					$one = 'selected';
						}else{
							$one = '';
	            		}

            			if (strpos($campaign->triggerAction, '2') !== false) {
	    					$two = 'selected';
						}else{
							$two = '';
	            		}

	            		if (strpos($campaign->triggerAction, '3') !== false) {
	            			$three = 'selected';
	            		}else{
	            			$three = '';
	            		}

	            		if (strpos($campaign->triggerAction, '4') !== false) {
	            			$four = 'selected';
	            		}else{
	            			$four = '';
	            		}

	            		if (strpos($campaign->triggerAction, '5') !== false) {
	            			$five = 'selected';
	            		}else{
	            			$five = '';
	            		}

	            		if (strpos($campaign->triggerAction, '6') !== false) {
	            			$six = 'selected';
	            		}else{
	            			$six = '';
	            		}

                  if (strpos($campaign->triggerAction, '7') !== false) {
	            			$seven = 'selected';
	            		}else{
	            			$seven = '';
	            		}

                  if (strpos($campaign->triggerAction, '8') !== false) {
	            			$eight = 'selected';
	            		}else{
	            			$eight = '';
	            		}

                  if (strpos($campaign->triggerAction, '9') !== false) {
	            			$nine = 'selected';
	            		}else{
	            			$nine = '';
	            		}

                  if (strpos($campaign->triggerAction, '10') !== false) {
                    $ten = 'selected';
                  }else{
                    $ten = '';
                  }

                  if (strpos($campaign->triggerAction, '11') !== false) {
	            			$eleven = 'selected';
	            		}else{
	            			$eleven = '';
	            		}

                  if (strpos($campaign->triggerAction, '12') !== false) {
	            			$twelve = 'selected';
	            		}else{
	            			$twelve = '';
	            		}

                  if (strpos($campaign->triggerAction, '13') !== false) {
	            			$thirteen = 'selected';
	            		}else{
	            			$thirteen = '';
	            		}

                  if (strpos($campaign->triggerAction, '14') !== false) {
	            			$fourteen = 'selected';
	            		}else{
	            			$fourteen = '';
	            		}

	            		?>
		                    <select multiple class="SlectBox" id="triggerAction" name="triggerAction">
                            <option <?php echo $one; ?>  value="1">Purchase</option>
                            <option <?php echo $two; ?>  value="2">Perform Custom Event</option>
                            <option <?php echo $three; ?> vvalue="3">Interact with view iOS campaigns</option>
                            <option <?php echo $four; ?> value="4">Interact with sent iOS campaigns</option>
                            <option <?php echo $five; ?> value="5">Interact with view android campaign</option>
                            <option <?php echo $six; ?> value="6">Interact with sent android campaign</option>
                            <option <?php echo $seven; ?> value="7">Interact with view email campaigns</option>
                            <option <?php echo $eight; ?> value="8">Interact with sent email campaigns</option>
                            <option <?php echo $nine; ?> value="9">Interact with view cross campaigns</option>
                            <option <?php echo $ten; ?> value="10">Interact with sent cross campaigns</option>
                            <option <?php echo $eleven; ?> value="11">Interact with view in-app messaging</option>
                            <option <?php echo $twelve; ?> value="12">Interact with sent in-app messaging</option>
                            <option <?php echo $thirteen; ?> value="13">Interact with error webhooks</option>
                            <option <?php echo $fourteen; ?> value="14">Interact with sent webhooks</option>
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
			                        <option <?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay == 'Immediately'){ echo 'selected'; }?> value="Immediately">Immediately</option>
			                        <option <?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay == 'After'){ echo 'selected'; }?> value="After">After</option>
			                        <!--<option <?php //if($campaign->delivery_type == '2' && $campaign->scheduleDelay == 'On the next'){ echo 'selected'; }?> value="On the next">On the Next</option>-->
			                    </select>
			                    <small>Once trigger event criteria are met, send this campaign </small>
			                    <div><span id="error_afterTimeInterval" style="font-size: 12px;"></span></div>
			                 </div> <!-- end of col sm 4 -->
			                 <div id="after" style="<?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay == 'After'){ echo 'display:block;'; }else{ echo 'display:none;'; }?>">
			                 	<div class="col-sm-1 col-xs-6">
				                    <input type="text" name="scheduleDelay_afterTime" id="scheduleDelay_afterTime" value="<?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay == 'After'){ echo $campaign->scheduleDelay_afterTime; }else{ echo '0'; }?>"/>
				                 </div> <!-- end of col sm 4 -->
	                			 <div class="col-sm-2 col-xs-6">
				                    <select class="SlectBox" id="scheduleDelay_afterTimeInterval">
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay_afterTimeInterval == 'minutes'){ echo 'selected';} ?> value="minutes">minutes</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay_afterTimeInterval == 'days'){ echo 'selected';} ?> value="days">days</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay_afterTimeInterval == 'days'){ echo 'weeks';} ?> value="weeks">weeks</option>
				                    </select>
				                 </div> <!-- end of col sm 4 -->

			                 </div>
			                 <div id="on_the_next" style="<?php if($campaign->delivery_type == '2' && $campaign->scheduleDelay == 'On the next'){ echo 'display:block;'; }else{ echo 'display:none;'; }?>">
	                 			 <div class="col-sm-2 col-xs-6">
				                    <select class="SlectBox" id="on_the_next_day">
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Sunday'){ echo 'selected'; }?> value="Sunday">Sunday</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Monday'){ echo 'selected'; }?> value="Monday">Monday</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Tuesday'){ echo 'selected'; }?> value="Tuesday">Tuesday</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Wednesday'){ echo 'selected'; }?> value="Wednesday">Wednesday</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Thursday'){ echo 'selected'; }?> value="Thursday">Thursday</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Friday'){ echo 'selected'; }?> value="Friday">Friday</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_weekday == 'Friday'){ echo 'selected'; }?> value="Saturday">Saturday</option>
				                    </select>
				                 </div> <!-- end of col sm 4 -->
	                 			 <div class="col-sm-3 col-xs-6">
				                    <select class="SlectBox" id="deliveryTime">
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_deliveryTime == 'at'){ echo 'selected'; }?> value="at">at</option>
				                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_deliveryTime == 'using intelligent delivery'){ echo 'selected'; }?> value="using intelligent delivery">using intelligent delivery</option>
				                    </select>
				                 </div> <!-- end of col sm 4 -->
				                 <div id="at" style="<?php if($campaign->delivery_type == '2' && $campaign->on_the_next_deliveryTime == 'at'){ echo 'display:block;'; }else{ 'display:none;'; }?>" >
		                			 <div class="col-sm-1 col-xs-6">
					                    <select class="SlectBox" id="on_the_next_hours">
					                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '12'){ echo 'selected'; }?> value="12">12</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '1'){ echo 'selected'; }?> value="1">1</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '2'){ echo 'selected'; }?> value="2">2</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '3'){ echo 'selected'; }?> value="3">3</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '4'){ echo 'selected'; }?> value="4">4</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '5'){ echo 'selected'; }?> value="5">5</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '6'){ echo 'selected'; }?> value="6">6</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '7'){ echo 'selected'; }?> value="7">7</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '8'){ echo 'selected'; }?> value="8">8</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '9'){ echo 'selected'; }?> value="9">9</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '10'){ echo 'selected'; }?> value="10">10</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_hours == '11'){ echo 'selected'; }?> value="11">11</option>
					                    </select>
					                 </div> <!-- end of col sm 4 -->
					                 <div class="col-sm-1 col-xs-6">

					                    <select class="SlectBox" id="on_the_next_mins">
					                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '00'){ echo 'selected'; }?> value="00">00</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '05'){ echo 'selected'; }?> value="05">05</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '10'){ echo 'selected'; }?> value="10">10</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '15'){ echo 'selected'; }?> value="15">15</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '20'){ echo 'selected'; }?> value="20">20</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '25'){ echo 'selected'; }?> value="25">25</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '30'){ echo 'selected'; }?> value="30">30</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '35'){ echo 'selected'; }?> value="35">35</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '40'){ echo 'selected'; }?> value="40">40</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '45'){ echo 'selected'; }?> value="45">45</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '50'){ echo 'selected'; }?> value="50">50</option>
											<option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_mins == '55'){ echo 'selected'; }?> value="55">55</option>
					                    </select>
					                 </div> <!-- end of col sm 4 -->
					                  <div class="col-sm-1 col-xs-6">
					                    <select class="SlectBox" id="on_the_next_am">
					                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_am == 'AM'){ echo 'selected'; }?> value="AM">AM</option>
					                        <option <?php if($campaign->delivery_type == '2' && $campaign->on_the_next_am == 'PM'){ echo 'selected'; }?> value="PM">PM</option>
					                    </select>
					                 </div> <!-- end of col sm 4 -->
				                 </div>
			                 </div>

	                 	</div><!-- end of col-sm-12 col-xs-12 -->
	                 </div>
	                 <!--<div class="row" id="unless_the_user" style="<?php if($campaign->delivery_type == '2' && $campaign->unless_the_user != ''){ echo 'display:block;'; }else{ 'display:none;'; }?>">
                 	<div class="col-sm-12 col-xs-12">
		                 <div class="col-sm-9 col-xs-12">
		                 <p><hr></p>
		                 <?php
						/*if (strpos($campaign->unless_the_user, '1') !== false) {
	    					$oneUnless = 'selected';
						}else{
							$oneUnless = '';
	            		}

            			if (strpos($campaign->unless_the_user, '2') !== false) {
	    					$twoUnless = 'selected';
						}else{
							$twoUnless = '';
	            		}

	            		if (strpos($campaign->unless_the_user, '3') !== false) {
	            			$threeUnless = 'selected';
	            		}else{
	            			$threeUnless = '';
	            		}

	            		if (strpos($campaign->unless_the_user, '4') !== false) {
	            			$fourUnless = 'selected';
	            		}else{
	            			$fourUnless = '';
	            		}

	            		if (strpos($campaign->unless_the_user, '5') !== false) {
	            			$fiveUnless = 'selected';
	            		}else{
	            			$fiveUnless = '';
	            		}

	            		if (strpos($campaign->unless_the_user, '6') !== false) {
	            			$sixUnless = 'selected';
	            		}else{
	            			$sixUnless = '';
	            		}
*/
	            		?>
		                    <label>Unless The User (Optional)</label>
		                    <select multiple class="SlectBox" id="unless_the_user_list">
		                        <option <?php echo $oneUnless; ?> value="1">Make Purchase</option>
		                        <option <?php echo $twoUnless; ?> value="2">Start Session</option>
		                        <option <?php echo $threeUnless; ?> value="3">Perform Custom Event</option>
		                        <option <?php echo $fourUnless; ?> value="4">Interact With Campaign</option>
		                        <option <?php echo $fiveUnless; ?> value="5">Interact With Card</option>
		                        <option <?php echo $sixUnless; ?> value="6">Enter a Location</option>
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
                        	<input type="text" class="date" id="actionDeliveryStartDate" value="<?php if($campaign->delivery_type == '2'){echo $campaign->campaignDuration_startTime_date;}else{ echo date('d-m-Y'); } ?>">
                        </div>
                        </div>

                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryStartHours">
	                    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '12'){ echo 'selected'; }?> value="12">12</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '1'){ echo 'selected'; }?>value="1">1</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '2'){ echo 'selected'; }?> value="2">2</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '3'){ echo 'selected'; }?> value="3">3</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '4'){ echo 'selected'; }?> value="4">4</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '5'){ echo 'selected'; }?> value="5">5</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '6'){ echo 'selected'; }?> value="6">6</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '7'){ echo 'selected'; }?> value="7">7</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '8'){ echo 'selected'; }?> value="8">8</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '9'){ echo 'selected'; }?> value="9">9</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '10'){ echo 'selected'; }?> value="10">10</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_hours == '11'){ echo 'selected'; }?> value="11">11</option>
	                    </select>
                    </div>
                    <div class="col-sm-2 col-xs-4">
                    	<select class="SlectBox" id="actionDeliveryStartMins">
                    	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '00'){ echo 'selected'; }?> value="00">00</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '05'){ echo 'selected'; }?> value="05">05</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '10'){ echo 'selected'; }?> value="10">10</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '15'){ echo 'selected'; }?> value="15">15</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '20'){ echo 'selected'; }?> value="20">20</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '25'){ echo 'selected'; }?>value="25">25</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '30'){ echo 'selected'; }?> value="30">30</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '35'){ echo 'selected'; }?> value="35">35</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '40'){ echo 'selected'; }?> value="40">40</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '45'){ echo 'selected'; }?> value="45">45</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '50'){ echo 'selected'; }?> value="50">50</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_mins == '55'){ echo 'selected'; }?> value="55">55</option>
                    	</select>
                    </div>
                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryStartAm">
	                    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_am == 'AM'){ echo 'selected'; }?> value="AM">AM</option>
	                    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_startTime_am == 'PM'){ echo 'selected'; }?> value="PM">PM</option>
	                    </select>
                    </div>
                    </div>
                    <label><input type="checkbox" id="actionDeliveryEndTimeEnabled" name="actionDeliveryEndTimeEnabled" <?php if($campaign->campaignDuration_endTime_date == '0000-00-00'){ echo 'checked';} ?>> End Time (Optional)</label>
                    <div class="row editEndTime">
                    	<div class="col-sm-3 col-xs-12">
                        <div class="calendar">
                        	<input type="text" class="date" id="actionDeliveryEndDate" value="<?php if($campaign->campaignDuration_endTime_date != '0000-00-00'){ echo $campaign->campaignDuration_endTime_date;}else{ echo date('d-m-Y');} ?>" <?php if($campaign->campaignDuration_endTime_date == '0000-00-00'){ echo 'disabled'; }?> >
                        </div>
                        </div>

                        <div class="col-sm-2 col-xs-4">
                        <select class="SlectBox" id="actionDeliveryEndHours" <?php if($campaign->campaignDuration_endTime_date == '0000-00-00'){ echo 'disabled'; }?>>
                        <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '12'){ echo 'selected'; }?> value="12">12</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '1'){ echo 'selected'; }?>value="1">1</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '2'){ echo 'selected'; }?> value="2">2</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '3'){ echo 'selected'; }?> value="3">3</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '4'){ echo 'selected'; }?> value="4">4</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '5'){ echo 'selected'; }?> value="5">5</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '6'){ echo 'selected'; }?> value="6">6</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '7'){ echo 'selected'; }?> value="7">7</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '8'){ echo 'selected'; }?> value="8">8</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '9'){ echo 'selected'; }?> value="9">9</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '10'){ echo 'selected'; }?> value="10">10</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_hours == '11'){ echo 'selected'; }?> value="11">11</option>
                        </select>
                        </div>
                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryEndMins" <?php if($campaign->campaignDuration_endTime_date == '0000-00-00'){ echo 'disabled'; }?>>
	                    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '00'){ echo 'selected'; }?> value="00">00</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '05'){ echo 'selected'; }?> value="05">05</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '10'){ echo 'selected'; }?> value="10">10</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '15'){ echo 'selected'; }?> value="15">15</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '20'){ echo 'selected'; }?> value="20">20</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '25'){ echo 'selected'; }?>value="25">25</option>
					    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '30'){ echo 'selected'; }?> value="30">30</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '35'){ echo 'selected'; }?> value="35">35</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '40'){ echo 'selected'; }?> value="40">40</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '45'){ echo 'selected'; }?> value="45">45</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '50'){ echo 'selected'; }?> value="50">50</option>
					  	<option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_mins == '55'){ echo 'selected'; }?> value="55">55</option>
	                    </select>
                    </div>
                    <div class="col-sm-2 col-xs-4">
	                    <select class="SlectBox" id="actionDeliveryEndAm" <?php if($campaign->campaignDuration_endTime_date == '0000-00-00'){ echo 'disabled'; }?>>
	                    <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_am == 'AM'){ echo 'selected'; }?> value="AM">AM</option>
		                <option <?php if($campaign->delivery_type == '2' && $campaign->campaignDuration_endTime_am == 'PM'){ echo 'selected'; }?> value="PM">PM</option>
	                    </select>
                    </div>
                    </div>
                    <span id="error_campaignDuration" style="font-size: 12px;"></span>
<!--

                    <div class="row">
                    	<div class="col-xs-12">
                        <div class="localTimeZone">
                        <input type="checkbox" id="localTimeZone" <?php if($campaign->delivery_type == '2' && $campaign->send_campaign_at_local_time_zone == '1'){ echo 'checked'; }?>>
                        <label for="localTimeZone">Send campaign to users in their local time zone</label>
                        </div>
                        </div>
                    </div>

                     <div class="row">
                    	<div class="col-xs-12">
                        <div class="timeBased">
                        <ul>
                        <li>
                        <input type="checkbox" name="campDuration" id="campDuration1" <?php if($campaign->delivery_type == '2' && $campaign->send_this_campaign_during_a_specific_portion_of_day == '1'){ echo 'checked';} ?>>
                        <label for="campDuration1"><strong>Only send this campaign during a specific portion of the day</strong></label>
                        </li>
                        <li>
                        <input type="checkbox" name="campDuration" id="campDuration2" <?php if($campaign->delivery_type == '2' && $campaign->reEligible_to_receive_campaign == '1'){ echo 'checked';} ?>>
                        <label for="campDuration2"><strong>Allow users to become re-eligible to receive campaign</strong></label>
                        </li>
                        <li>
                        <input type="checkbox" name="campDuration" id="campDuration3" <?php if($campaign->delivery_type == '2' && $campaign->ignore_frequency_capping_settings == '1'){ echo 'checked';} ?>>
                        <label for="campDuration3"><strong>Ignore frequency capping settings for this campaign</strong></label>
                        </li>
                        </ul>
                        </div>
                        </div>
                    </div>
-->
                    <div class="col-xs-12 time delivery-specific" id="actionDelivery_specificPortion" style="<?php if($campaign->delivery_type == '2' && $campaign->send_this_campaign_during_a_specific_portion_of_day == '1'){ echo 'display: block;';}else{ echo 'display: none;'; } ?>">

                  	<div class="row">
                  		<div class="col-sm-5">
	                  		<div class="row">
		                  		<div class="col-xs-2"><p>Between</p></div>
		                  		<div class="col-xs-3">
				                	 	<select class="SlectBox" id="actionDelivery_specific_start_hours">
				                	 	<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '12'){ echo 'selected';}?> value="12">12</option>
				                	 	<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '1'){ echo 'selected';}?> value="1">1</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '2'){ echo 'selected';}?> value="2">2</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '3'){ echo 'selected';}?> value="3">3</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '4'){ echo 'selected';}?> value="4">4</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '5'){ echo 'selected';}?> value="5">5</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '6'){ echo 'selected';}?> value="6">6</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '7'){ echo 'selected';}?> value="7">7</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '8'){ echo 'selected';}?> value="8" selected>8</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '9'){ echo 'selected';}?> value="9">9</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '10'){ echo 'selected';}?> value="10">10</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_hours == '11'){ echo 'selected';}?> value="11">11</option>
				                	 </select>
				                	</div>
			                	<div class="col-xs-4">
				                    <select class="SlectBox" id="actionDelivery_specific_start_mins">
				                    <option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '00'){ echo 'selected';}?> value="00">00</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '05'){ echo 'selected';}?> value="05">05</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '10'){ echo 'selected';}?> value="10">10</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '15'){ echo 'selected';}?> value="15">15</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '20'){ echo 'selected';}?> value="20">20</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '25'){ echo 'selected';}?> value="25">25</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '30'){ echo 'selected';}?> value="30">30</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '35'){ echo 'selected';}?> value="35">35</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '40'){ echo 'selected';}?> value="40">40</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '45'){ echo 'selected';}?> value="45">45</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '50'){ echo 'selected';}?> value="50">50</option>
									<option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_mins == '55'){ echo 'selected';}?> value="55">55</option>
				                    </select>
	                    		</div>
			                    <div class="col-xs-3">
				                    <select class="SlectBox" id="actionDelivery_specific_start_am_pm">
				                    <option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_am_pm == 'AM'){ echo 'selected';}?> value="AM">AM</option>
				                    <option <?php if($campaign->delivery_type == '2' && $campaign->specific_start_am_pm == 'PM'){ echo 'selected';}?> value="PM">PM</option>
				                    </select>
			                    </div>
	               			</div>
		                </div>

		                   <div class="col-sm-7">
			                   <div class="row">
				                 	<div class="col-xs-1" align="right"><p>And</p></div>
				                    <div class="col-xs-2">
				                	 	<select class="SlectBox" id="actionDelivery_specific_end_hours">
				                	 	<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '12'){ echo 'selected';}?> value="12">12</option>
				                	 	<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '1'){ echo 'selected';}?> value="1">1</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '2'){ echo 'selected';}?> value="2">2</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '3'){ echo 'selected';}?> value="3">3</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '4'){ echo 'selected';}?> value="4">4</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '5'){ echo 'selected';}?> value="5">5</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '6'){ echo 'selected';}?> value="6">6</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '7'){ echo 'selected';}?> value="7">7</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '8'){ echo 'selected';}?> value="8" selected>8</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '9'){ echo 'selected';}?> value="9">9</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '10'){ echo 'selected';}?> value="10">10</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_hours == '11'){ echo 'selected';}?> value="11">11</option>
				                	 </select>
				                	</div>
				                	<div class="col-xs-3">
					                    <select class="SlectBox" id="actionDelivery_specific_end_mins">
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '00'){ echo 'selected';}?> value="00">00</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '05'){ echo 'selected';}?> value="05">05</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '10'){ echo 'selected';}?> value="10">10</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '15'){ echo 'selected';}?> value="15">15</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '20'){ echo 'selected';}?> value="20">20</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '25'){ echo 'selected';}?> value="25">25</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '30'){ echo 'selected';}?> value="30">30</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '35'){ echo 'selected';}?> value="35">35</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '40'){ echo 'selected';}?> value="40">40</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '45'){ echo 'selected';}?> value="45">45</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '50'){ echo 'selected';}?> value="50">50</option>
										<option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_mins == '55'){ echo 'selected';}?> value="55">55</option>
					                    </select>
		                    		</div>
				                    <div class="col-xs-2">
					                    <select class="SlectBox" id="actionDelivery_specific_end_am_pm">
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_am_pm == 'AM'){ echo 'selected';}?> value="AM">AM</option>
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->specific_end_am_pm == 'PM'){ echo 'selected';}?> value="PM">PM</option>
					                    </select>
				                    </div>
		                  	  		<div class="col-sm-4"><p>in the user's local times</p></div>
		                  	  </div>
	                  	  </div>
                  </div>
		                  <div class="localTimeZone" style="border-top: none;margin-top: 0px;">
		                  	<input type="checkbox" name="actionDelivery_nextAvailableTime" id="actionDelivery_nextAvailableTime" <?php if($campaign->delivery_type == '2' && $campaign->sendIfDeliveryTimeFallsOutsideSpecifiedPortion == '1'){ echo 'checked';}?> /><label for="actionDelivery_nextAvailableTime">Send at the next available time if the delivery time falls outside the specified portion of the day</label>
		                  </div>
			</div>

			<div class="col-xs-12 time delivery-specific" id="actionDelivery_reEligible" style="<?php if($campaign->delivery_type == '2' && $campaign->reEligible_to_receive_campaign == '1'){ echo 'display: block;';}else{ echo 'display: none;'; } ?>">
					<div class="row">
                  		<div class="col-sm-12">
                  			<div class="row">
		                  		<div class="col-sm-8"><p>After a user is messaged by this campaign, allow them to become re-eligible to receive the campaign again in</p></div>
		                  		<div class="col-sm-1"><input type="text"name="actionDeliveryTime_reEligibleTime" id="actionDeliveryTime_reEligibleTime" value="<?php if($campaign->delivery_type == '2'){ echo $campaign->reEligibleTime;}else{ echo '1'; }; ?>"/></div>
		                  		<div class="col-sm-2">
		                  			<select class="SlectBox" id="actionDeliveryTime_reEligibleTimeInterval">
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->reEligibleTimeInterval == 'minutes'){ echo 'selected';} ?> value="minutes">minutes</option>
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->reEligibleTimeInterval == 'days'){ echo 'selected';} ?> value="days" selected>days</option>
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->reEligibleTimeInterval == 'weeks'){ echo 'selected';} ?> value="weeks">weeks</option>
					                    <option <?php if($campaign->delivery_type == '2' && $campaign->reEligibleTimeInterval == 'months'){ echo 'selected';} ?> value="months">months</option>
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
            <?php if($campaign->isDraft == '1'){?><a href="javascript:void(0)" onclick="return saveDeliveryAsDraft();" class="btn">Save As Draft</a><?php } ?>
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
                        <?php $segment = explode(",",$campaign->segments); ?>
                         <select id="segments">
                            <option>+ Add Segments Here <em></em></option>
                            <?php  if(!in_array("1", $segment)){?><option id="1segment" value="1">Lapsed Users - 7 days</option> <?php } ?>
                            <?php  if(!in_array("2", $segment)){?><option id="2segment" value="2">User Onboarding - First Week</option> <?php } ?>
                            <?php  if(!in_array("3", $segment)){?><option id="3segment" value="3">User Onboarding - Second Week</option> <?php } ?>
                            <?php  if(!in_array("4", $segment)){?><option id="4segment" value="4">Engaged Recent Users</option> <?php } ?>
                            <?php  if(!in_array("5", $segment)){?><option id="5segment" value="5">All Users</option> <?php } ?>

                         </select>
                         <div id="segmentWrap" class="tags">
                         <?php if($campaign->segments != ''){
                         		if(in_array("1", $segment)){?>
                         			<span id='1segment' onclick="removeSegment('1','Lapsed Users - 7 days')">Lapsed Users - 7 days</span>
                         		<?php }
                         		if(in_array("2", $segment)){ ?>
                         			<span id='2segment' onclick="removeSegment('2','User Onboarding - First Week')">User Onboarding - First Week</span>
                         		<?php }
                         		if(in_array("3", $segment)){ ?>
                         			<span id='3segment' onclick="removeSegment('3','User Onboarding - Second Week')">User Onboarding - Second Week</span>
                         		<?php }
                         		if(in_array("4", $segment)){ ?>
                         			<span id='4segment' onclick="removeSegment('4','Engaged Recent Users')">Engaged Recent Users</span>
                         		<?php }
                         		if(in_array("5", $segment)){ ?>
                         			<span id='5segment' onclick="removeSegment('5','All Users')">All Users</span>
                         		<?php }

                         }
                         ?>
                         </div>
                         <div><span id="error_segment" style="font-size: 12px;"></span></div>
                        </div>
                        </div>

                		<div class="table-cell">
                        <div class="block">
                        <?php $filters = explode(",",$campaign->filters); ?>
                        <select id="addFilters">
                            	<option>+ Add Filters Here <em></em></option>
	                            <?php //if(!in_array("1", $filters)){?><!-- <option id="1filter" value="1">Custom Attributes</option> --><?php //} ?>
	                            <?php //if(!in_array("2", $filters)){?><!--<option id="2filter" value="2">Custom Event</option>--><?php //} ?>
	                            <?php //if(!in_array("3", $filters)){?><!-- <option id="3filter" value="3">First Did Custom Event</option> --><?php //} ?>
	                            <?php //if(!in_array("4", $filters)){?><!-- <option id="4filter" value="4">Last Did Custom Event</option> --><?php //} ?>
	                            <?php //if(!in_array("5", $filters)){?><!-- <option id="5filter" value="5">X Custom Event In Y Days</option> --><?php //} ?>
                            	<?php //if(!in_array("6", $filters)){?><!--<option id="6filter" value="6">First Made Purchase</option>--><?php// } ?>
                            	<?php if(!in_array("7", $filters)){?><option id="7filter" value="7">First Purchased Product</option><?php } ?>
                            	<?php if(!in_array("8", $filters)){?><option id="8filter" value="8">First Used App</option><?php } ?>
                            	<?php //if(!in_array("9", $filters)){?><!--<option id="9filter" value="9">Last Made Purchase</option>--><?php //} ?>
                            	<?php if(!in_array("10", $filters)){?><option id="10filter" value="10">Last Purchased Product</option><?php } ?>
                            	<?php if(!in_array("11", $filters)){?><option id="11filter" value="11">Last Submitted Feedback</option><?php } ?>
                            	<?php if(!in_array("12", $filters)){?><option id="12filter" value="12">Last Used App</option><?php } ?>
                            	<?php //if(!in_array("13", $filters)){?><!-- <option id="13filter" value="13">Median Session Duration</option> --><?php //} ?>
                            	<?php //if(!in_array("14", $filters)){?><!-- <option id="14filter" value="14">Money Spent In-App</option> --><?php //} ?>
                            	<?php if(!in_array("15", $filters)){?><option id="15filter" value="15">Most Recent App Version</option><?php } ?>
                            	<?php //if(!in_array("16", $filters)){?><!-- <option id="16filter" value="16">Most Recent Location</option> --><?php //} ?>
                            	<?php //if(!in_array("17", $filters)){?><!-- <option id="17filter" value="17">Number of Feedback Items</option> --><?php //} ?>
                            	<?php if(!in_array("18", $filters)){?><option id="18filter" value="18">Purchased Product</option><?php } ?>
                            	<?php //if(!in_array("19", $filters)){?><!-- <option id="19filter" value="19">Session Count</option> --><?php //} ?>
                            	<?php //if(!in_array("20", $filters)){?><!-- <option id="20filter" value="20">Total Number of Purchases</option> --><?php //} ?>
                            	<?php //if(!in_array("21", $filters)){?><!-- <option id="21filter" value="21">Uninstall Date</option> --><?php //} ?>
                            	<?php //if(!in_array("22", $filters)){?><!-- <option id="22filter" value="22">Uninstalled</option> --><?php //} ?>
                            	<?php //if(!in_array("23", $filters)){?><!-- <option id="23filter" value="23">X Money Spent in Last Y Days</option> --><?php //} ?>
                            	<?php //if(!in_array("24", $filters)){?><!-- <option id="24filter" value="24">X Product Purchased In Y Days</option> --><?php //} ?>
                            	<?php //if(!in_array("25", $filters)){?><!-- <option id="25filter" value="25">X Purchases in Last Y Days</option> --><?php //} ?>
                            	<?php //if(!in_array("26", $filters)){?><!-- <option id="26filter" value="26">X Sessions in Last Y Days</option> --><?php //} ?>
                            	<?php if(!in_array("27", $filters)){?><option id="27filter" value="27">User views app page</option><?php } ?>
                         </select>

                         <div id="filterWrap" class="tags">
                         <?php if($campaign->filters != ''){
                         		//if(in_array("1", $filters)){?>
                         			<!-- <span id='1filter' onclick="removeFilter('1','Custom Attributes')">Custom Attributes</span> -->
                         		<?php //}
                         		if(in_array("2", $filters)){ ?>
                         			<span id='2filter' onclick="removeFilter('2','Custom Event')">Custom Event</span>
                         		<?php }
                         		//if(in_array("3", $filters)){ ?>
                         			<!-- <span id='3filter' onclick="removeFilter('3','First Did Custom Event')">First Did Custom Event</span> -->
                         		<?php //}
                         		//if(in_array("4", $filters)){ ?>
                         			<!-- <span id='4filter' onclick="removeFilter('4','Last Did Custom Event')">Last Did Custom Event</span> -->
                         		<?php //}
                         		//if(in_array("5", $filters)){ ?>
                         			<!-- <span id='5filter' onclick="removeFilter('5','X Custom Event In Y Days')">X Custom Event In Y Days</span> -->
                         		<?php //}
                         		if(in_array("6", $filters)){ ?>
                         		    <span id='6filter' onclick="removeFilter('6','First Made Purchase')">First Made Purchase</span>
                         		<?php }
                         		if(in_array("7", $filters)){ ?>
                         		    <span id='7filter' onclick="removeFilter('7','First Purchased Product')">First Purchased Product</span>
                         		<?php }
                         		if(in_array("8", $filters)){ ?>
                         		    <span id='8filter' onclick="removeFilter('8','First Used App')">First Used App</span>
                         		<?php }
                         		if(in_array("9", $filters)){ ?>
                         		    <span id='9filter' onclick="removeFilter('9','Last Made Purchase')">Last Made Purchase</span>
                         		<?php }
                         		if(in_array("10", $filters)){ ?>
                         			<span id='10filter' onclick="removeFilter('10','Last Purchased Product')">Last Purchased Product</span>
                         		<?php }
                         		if(in_array("11", $filters)){ ?>
                         		    <span id='11filter' onclick="removeFilter('11','Last Submitted Feedback')">Last Submitted Feedback</span>
                         		<?php }
                         		if(in_array("12", $filters)){ ?>
                         		    <span id='12filter' onclick="removeFilter('12','Last Used App')">Last Used App</span>
                         		<?php }
                         		//if(in_array("13", $filters)){ ?>
                         		    <!-- <span id='13filter' onclick="removeFilter('13','Median Session Duration')">Median Session Duration</span> -->
                         		<?php //}
                         		//if(in_array("14", $filters)){ ?>
                         		    <!-- <span id='14filter' onclick="removeFilter('14','Money Spent In-App')">Money Spent In-App</span> -->
                         		<?php //}
                         		if(in_array("15", $filters)){ ?>
                         		    <span id='15filter' onclick="removeFilter('15','Most Recent App Version')">Most Recent App Version</span>
                         		<?php }
                         		//if(in_array("16", $filters)){ ?>
                         		    <!-- <span id='16filter' onclick="removeFilter('16','Most Recent Location')">Most Recent Location</span> -->
                         		<?php //}
                         		//if(in_array("17", $filters)){ ?>
                         		    <!-- <span id='17filter' onclick="removeFilter('17','Number of Feedback Items')">Number of Feedback Items</span> -->
                         		<?php //}
                         		if(in_array("18", $filters)){ ?>
                         		    <span id='18filter' onclick="removeFilter('18','Purchased Product')">Purchased Product</span>
                         		<?php }
                         		//if(in_array("19", $filters)){ ?>
                         		    <!-- <span id='19filter' onclick="removeFilter('19','Session Count')">Session Count</span> -->
                         		<?php //}
                         		//if(in_array("20", $filters)){ ?>
                         		   <!-- <span id='20filter' onclick="removeFilter('20','Total Number of Purchases')">Total Number of Purchases</span> -->
                         		<?php //}
                         		//if(in_array("21", $filters)){ ?>
                         		    <!-- <span id='21filter' onclick="removeFilter('21','Uninstall Date')">Uninstall Date</span> -->
                         		<?php //}
                         		//if(in_array("22", $filters)){ ?>
                         		    <!-- <span id='22filter' onclick="removeFilter('22','Uninstalled')">Uninstalled</span> -->
                         		<?php //}
                         		//if(in_array("23", $filters)){ ?>
                         		    <!-- <span id='23filter' onclick="removeFilter('23','X Money Spent in Last Y Days')">Uninstalled</span> -->
                         		<?php //}
                         		//if(in_array("24", $filters)){ ?>
                         		    <!-- <span id='24filter' onclick="removeFilter('24','X Product Purchased In Y Days')">X Product Purchased In Y Days</span> -->
                         		<?php //}
                         		//if(in_array("25", $filters)){ ?>
                         		    <!-- <span id='25filter' onclick="removeFilter('25','X Purchases in Last Y Days')">X Purchases in Last Y Days</span> -->
                         		<?php //}
                         		//if(in_array("26", $filters)){ ?>
                         		    <!-- <span id='26filter' onclick="removeFilter('26','X Sessions in Last Y Days')">X Sessions in Last Y Days</span> -->
                         		<?php //}
                         		if(in_array("27", $filters)){ ?>
                         		    <span id='27filter' onclick="removeFilter('27','User views app page')">User views app page</span>
                         		<?php }
                         }
                         ?>
                         </div>
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
                    	<option <?php if($campaign->send_to_users == '1'){ echo 'selected';} ?> value="1">Users who are subscribed or opted-in</option>
                    	<option <?php if($campaign->send_to_users == '2'){ echo 'selected';} ?> value="2">Opted-in users only</option>
                    	<option <?php if($campaign->send_to_users == '0'){ echo 'selected';} ?> value="0">All users including unsubscribed users</option>
                    </select>

                    <div class="localTimeZone">
                	<input type="checkbox" id="targetUsers_whoWillReceiveCampaign" name="targetUsers_whoWillReceiveCampaign" <?php if($campaign->receiveCampaignType != ''){ echo 'checked'; } ?> ><label for="targetUsers_whoWillReceiveCampaign">Send the number of people who will receive this campaign</label>
                	</div>
                </div>

            	<div class="col-sm-12 col-xs-12" id="selectedUsers" style="<?php if($campaign->receiveCampaignType != ''){ echo 'display: block;'; }else{ 'display: none;'; } ?>">
                <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <select class="SlectBox" id="selectedUsers_receiveCampaign">
                    	<option <?php if($campaign->receiveCampaignType == '1'){ echo 'selected';} ?> value="1">In total, this campaign should</option>
                    	<option <?php if($campaign->receiveCampaignType == '2'){ echo 'selected';} ?> value="2">Every time this campaign is scheduled</option>
                    </select>
                    </div>
                <div class="col-sm-6 col-xs-12">
                   Message a maximum of <input type="text" class="inline" style="margin:0;width:76px;" maxlength="7" id="no_of_users_who_receive_campaigns" name="no_of_users_who_receive_campaigns" value="<?php if($campaign->receiveCampaignType != ''){ echo $campaign->no_of_users_who_receive_campaigns; }else{ echo '1000'; }  ?>"> users
                   <span id="error_noOfUsersWhoReceiveCampaigns" style="float: left;width: 100%;font-size: 12px;"></span>
                </div>
                </div>
<!--                <div><span id="error_noOfUsersWhoReceiveCampaigns"></span></div>-->
                </div>


            	<div class="col-sm-12 col-xs-12" >
                <div class="localTimeZone">
                   <input type="checkbox" id="send_this_push_to_users_most_recently_used_device" name="send_this_push_to_users_most_recently_used_device" <?php if($campaign->messages_per_minute != ''){ echo 'checked'; } ?>><label for="send_this_push_to_users_most_recently_used_device"> Only send this push to the user's most recently used device</label>
                </div>
                </div>

            	<div class="col-sm-12 col-xs-12" id="messages_per_minute_block" style="<?php if($campaign->messages_per_minute != ''){ echo 'display: block;'; }else{ echo 'display: none;'; } ?>">
                <div class="row perminute">
                <div class="col-sm-3 col-xs-12">
                    <select class="SlectBox" id="messages_per_minute">
                    	<option <?php if($campaign->messages_per_minute == '50'){ echo 'selected';} ?> value="50">50</option>
						<option <?php if($campaign->messages_per_minute == '100'){ echo 'selected';} ?> value="100">100</option>
						<option <?php if($campaign->messages_per_minute == '500'){ echo 'selected';} ?> value="500">500</option>
						<option <?php if($campaign->messages_per_minute == '1000'){ echo 'selected';} ?> value="1000">1,000</option>
						<option <?php if($campaign->messages_per_minute == '2500'){ echo 'selected';} ?> value="2500">2,500</option>
						<option <?php if($campaign->messages_per_minute == '5000'){ echo 'selected';} ?> value="5000">5,000</option>
						<option <?php if($campaign->messages_per_minute == '10000'){ echo 'selected';} ?> value="10000">10,000</option>
						<option <?php if($campaign->messages_per_minute == '25000'){ echo 'selected';} ?> value="25000">25,000</option>
						<option <?php if($campaign->messages_per_minute == '50000'){ echo 'selected';} ?> value="50000">50,000</option>
						<option <?php if($campaign->messages_per_minute == '100000'){ echo 'selected';} ?> value="100000">100,000</option>
						<option <?php if($campaign->messages_per_minute == '250000'){ echo 'selected';} ?> value="250000">250,000</option>
						<option <?php if($campaign->messages_per_minute == '500000'){ echo 'selected';}else{ 'selected'; } ?> value="500000">500,000</option>
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
            <?php if($campaign->isDraft == '1'){?><a href="javascript:void(0)" onclick="return saveTargetAsDraft();" class="btn">Save As Draft</a><?php } ?>
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
                                    <div class="dragImg"> <!-- For Drag Image -->
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
                                    <div class="dragImg">  <!-- For Drag Image -->
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
                </div>
            </div>
         </div>
		<div>
		<a href="<?php echo base_url();?>appUser/launchCampaignSuccessPopUp" id="launchCampaign" style="display: none;" class="modalPopup" data-class="fbPop addApp" data-title="Success">Confirm</a>
		</div>
        <div class="pagination inner">
        <input type="hidden" id="iOSAppImage" value="<?php echo $iOSAppImage; ?>" />
          <a href="javascript:void(0);" id="post_facebook_page" class="modalPopup" data-title="Facebook Page" data-class="fbPop delete fbPopClose" data-size="size-small"><span></span></a>

      <a href="javascript:void(0)" class="btn back" onclick="backToTarget();">Back</a>
            <?php if($campaign->isDraft == '1'){?><a href="javascript:void(0)" onclick="return saveConfirmAsDraft();" class="btn">Save As Draft</a><?php } ?>
            <!-- <a href="javascript:void(0)" onclick="launchCampaign();" class="btn">Launch Campaign</a> -->
            <?php if($campaign->isDraft == '1'){ ?>
            <a href="<?php echo base_url();?>appUser/confirmationlaunch" data-size="size-medium" data-title="Confirm Campaign"  data-class="fbPop delete launchCampaign" id="saveCampaign" class="btn modalPopup" oncontextmenu="return false;" style="display:block;float:right;">Launch Campaign</a>
            <?php }else{ ?>
            <a href="<?php echo base_url();?>appUser/editconfirmationlaunch" data-size="size-medium" data-title="Confirm Campaign"  data-class="fbPop delete launchCampaign" class="btn modalPopup" oncontextmenu="return false;" style="display:block;float:right;">Launch Campaign</a>
              <?php  } ?>
            <!-- <a href="javascript:void(0)" data-toggle="modal" <?php //if($campaign->isDraft == '1'){?>data-target=".launchCampaign"<?php //}else{?>data-target=".editlaunchCampaign"<?php //}?> class="btn" oncontextmenu="return false;">Launch Campaign</a> -->
        </div>
        </div>
    </div>
    </div>

    </div>

    </div> <!-- End floatContainer -->
    </div><!-- End createCampaign  -->
    <?php //}else{?>

      <!-- <div class="col-xs-6">
        <p>Please select App or create App</p>
      </div> -->

    <?php //} ?>
</div>

</div><!-- End container-fluid -->
</div>
