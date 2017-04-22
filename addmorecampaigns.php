<?php
if(count($push_campaigns) > 0){
	
	foreach($push_campaigns as $group){ 
		$platform = ucfirst($group->platform) .'<br />';
		?>
		<li class="licloseList">
                    <a href="<?php echo base_url();?>appUser/editCampaigns/<?php echo $group->id; ?>"><small><?php echo $group->campaignName." - $platform ($group->app_group_name)"; if($group->isDraft == '1'){ echo ' (Draft)';} if($group->automation == 1){ echo ' (Saved for Workflow)';}?></small></a>
                <?php if($group->isDraft == '1' || $group->automation == 1){?><a href="<?php echo base_url(); ?>appUser/deleteCampaignPopUp/<?php echo $group->id; ?>" class="closeli modalPopup" data-class="fbPop submitOffer2 addLocation" data-title="Delete Campaign"><i class="fa fa-times-circle"></i></a><?php } ?>
                </li>
	<?php }
	
}
?>