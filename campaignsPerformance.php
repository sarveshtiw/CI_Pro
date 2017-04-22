<div class="pageStarts">
  <div class="container-fluid">
    <div class="col-xs-12">
      <div class="sidebar pull-left">
        <div class="box grey">
          <h2>Performance By Campaigns</h2>
            <ul class="app-groups-list">
            	<?php if(count($push_campaigns) > 0){ $i = 1; ?>
              <li><a>Select Recent Campaigns</a></li>
            	<?php foreach($push_campaigns as $push_campaign){ ?>
            		<li><a <?php  if($push_campaign->id == $campaignId){?>class="active"<?php } ?> href="<?php echo base_url();?>appUser/campaignsPerformance/<?php echo $push_campaign->id; ?>"><small><?php echo $push_campaign->campaignName; ?></small></a></li>
            	<?php }
            	}else{ ?>
                <li><a>Campaigns will appear here</a></li>
          <?php } ?>
          </ul>
        </div>
      </div>


      <div class="pageContent statsPage">
        <div class="stats">
          <div class="col-xs-12">
            <div class="perfoMenu">
              <ul>
                <a href="<?php echo base_url();?>appUser/launchCampaignSuccessPopUp" id="launchCampaign" style="display: none;" class="modalPopup" data-class="fbPop addApp" data-title="Success">Confirm</a>

                <li><a href="<?php echo base_url();?>appUser/campaignsDelete/<?php echo $campaignId; ?>" class="modalPopup storeButton" data-class="fbPop submitOffer2 addLocation" data-title="Campaigns Delete" oncontextmenu="return false;">Delete</a></li>
                <!--<li><a href="">Archive </a></li>-->
                <li><a href="<?php echo base_url();?>appUser/campaignsClone/<?php echo $campaignId; ?>" class="modalPopup storeButton" data-class="fbPop submitOffer2 addLocation" data-title="Campaigns Clone" oncontextmenu="return false;">Clone </a></li>

                <?php if($platform == 'email'){
                  $redirect_action = 'editEmailCampaign';
                }else{
                  $redirect_action = 'editCampaigns';
                } ?>
                <li><a href="<?php echo base_url();?>appUser/<?php echo $redirect_action.'/'.$campaignId; ?>">Edit </a></li>
                 <li style=""><a href="<?php echo current_url() ?>">Reset</a></li>
                <li style=""><a href="<?php echo base_url();?>appUser/comparePopup/<?php echo $campaignId;?>" class="modalPopup storeButton" data-title="Compare" data-class="fbPop submitOffer2 addLocation camparePopup" oncontextmenu="return false;">Compare</a></li>
              <li style=""><a href="<?php echo base_url();?>appUser/pdf/<?php echo $campaignId;?>" >Download</a></li>
              </ul>
            </div>
          </div>
          <div class="col-xs-12">
            <div class="row  performance profiling" >
              <h3>Campaign Performance</h3>
              <div class="col-xs-12">
                <div class="row performance">
                  <div class="col-xs-6">
                      <div class="innerPerformace">
                         <strong>Campaign Name :</strong> <?php echo $campaignName; ?>
                      </div>
                  </div>
                  <div class="col-xs-6">
                    <div class="innerPerformace">
                       <strong>Push Title :</strong> <?php if(!empty($push_title)){ echo $push_title; } else { echo 'NA'; } ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xs-12">
                <div class="row">
                  <div class="col-xs-6">
                    <div class="inner purpleBg">
                       <p><strong><?php echo $countViewCampaigns; ?></strong></p>
                       <h4>Views</h4>
                     </div>
                  </div>
                  <div class="col-xs-6">
                    <div class="inner purpleBg">
                         <p><strong><?php echo $countSendCampaigns; ?></strong></p>
                         <h4>Sent</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              <div id="insert-after"></div>

            <div class="row  performance profiling" id="append-container">
              <h3>App User Performance</h3>
            <?php if(!empty($sendCampaigns) && !empty($viewCampaigns)) { ?>
              <div id="container"></div>
            <?php }else { ?>
                <p style="text-align:center; padding:2% 0;">App Users of your campaigns will appear here.</p>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" name="baseurl" id="baseurl" value="<?=base_url()?>">
  <input type="hidden" name="prevViewCampaign" id="prevViewCampaign" value="<?=$viewCampaigns?>">
  <input type="hidden" name="prevSentCampaign" id="prevSentCampaign" value="<?=$sendCampaigns?>">
  <input type="hidden" name="currentCampaign" id="currentCampaign" value="<?=$currentCampaign?>">
