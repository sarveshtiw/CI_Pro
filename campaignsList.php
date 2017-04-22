<div class="pageStarts">
  <div class="container-fluid">
    <div class="col-xs-12">
      <div class="sidebar pull-left">
        <div class="box grey">
          <h2>Campaigns by App</h2>
          <h2>App Groups</h2>
            <ul class="app-groups-list">
            	<?php if(count($groups) > 0){ ?>
            	<li><a>Select App Group</a></li>
            	<?php foreach($groups as $group){ ?>
            		<li><a onclick="return selectGroup('<?php echo $group->app_group_id; ?>');" <?php  if($group->app_group_id == $groupId){?>class="active"<?php } ?> href="<?php echo base_url();?>appUser/campaignsList/<?php echo $group->app_group_id; ?>"><small><?php echo $group->app_group_name;?></small></a></li>
            	<?php }
            	}else{ ?>
                <li><a>Groups will appear here</a></li>
          <?php } ?>
          </ul>
        </div>
      </div>
      <div class="pageContent statsPage ">
        <div class="stats">
          <div class="col-xs-12">
            <div class="row performance">
              <h3>Campaigns List</h3>
              <?php if(count($pushCampaigns) > 0){  ?>
                      <table width="100%" class="grid dataCampTable ">
                        <tr>
                          <th></th>
                          <th>Campaign Name</th>
                          <th>Live Campaign Date </th>
                          <th>Action</th>
                        </tr>
                        <?php $i = 1; foreach ($pushCampaigns as $key => $value) { //print_r($pushCampaigns); exit; echo $platform; exit;
                          $platform = ucfirst($value->platform);
                          if($platform == 'Android' || $platform == 'IOS'){
                            $redirect_action = 'editCampaigns';
                          }else if($platform == 'Email'){
                            $redirect_action = 'editEmailCampaign';
                          }else if($platform == 'Cross'){
                            $redirect_action = 'editCrossChannel';
                          } ?>
                        <tr>
                            <td><?php echo $i; ?>.</td>
                            <td><a href="<?php echo base_url().'appUser/campaignsPerformance/'.$value->id; ?>"><?php if(isset($value->campaignName)){ echo $value->campaignName . " - " . " $platform"; } else { echo ""; } ?></a></td>
                            <td><?php
                                $time = $value->createdDate;
                              if(isset($value->deliveryType)){
                                if($value->deliveryType == 1){
                                  if($value->time_based_scheduling == 1){
                                    $time = $value->createdDate;
                                  }else if($value->time_based_scheduling == 2){
                                    if(!empty($value->once_date)){
                                      $time = $value->once_date.' '.$value->starting_at_hour.' '.$value->starting_at_min.' '.$value->starting_at_am_pm;
                                    }else{
                                      $time = $value->beginning_date;
                                    }
                                  }else if($value->time_based_scheduling == 3){
                                    if(!empty($value->once_date)){
                                      $time = $value->once_date.' '.$value->starting_at_hour.' '.$value->starting_at_min.' '.$value->starting_at_am_pm;
                                    }else{
                                      $time = $value->intelligent_beginning_date;
                                    }
                                  }
                                }else if($value->deliveryType == 2){
                                  $time = $value->campaignDuration_startTime_date.' '.$value->campaignDuration_startTime_hours.' '.$value->campaignDuration_startTime_mins.' '.$value->campaignDuration_startTime_am;
                                }
                              }
                              $time = strtotime($time);
                              date_default_timezone_set($userTimezone);
                              echo date('d-m-Y h:i A',$time);  ?></td>
                            <td>
                              <a href="<?php echo base_url().'appUser/campaignsPerformance/'.$value->id; ?>" class="btn purple-btn">Performance</a>&nbsp;&nbsp;&nbsp;
                              <a href="<?php echo base_url().'appUser/'.$redirect_action.'/'.$value->id; ?>" class="btn purple-btn">Edit</a>
                            </td>
                          </tr>
                    <?php $i++; } ?>
                      </table>
                <?php } else { ?>
                    <div class="col-xs-12">
                      <div class="row" style="margin-bottom: 15px; padding:15px;">
                         <p><center> Push Notification list will appear here. </center></p>
                      </div>
                    </div>
                <?php } ?>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<input type="hidden" name="baseurl" id="baseurl" value="<?=base_url()?>">
<script src="<?php echo base_url(); ?>assets/template/frontend/js/3.0/jquery-1.11.3.min.js"></script>
