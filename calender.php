

<div role="main">
    <div class="container">
        <section id="directives-calendar" ng-controller="CalendarCtrl">
            <div class="page-header">
                <h1>Calendar</h1>
            </div>
            <div class="well">
                <div class="row-fluid">
                
                    
                    
    <div class="col-xs-12 calenderButton">
	<?php if(count($campaignPermission)>0 || $usertype == 6){?>
    <span class="btn purple-btn createBtn">
    
    <a href="<?php echo base_url().'campaign/createCampaign/1'?>" style="color: #424141;" class="modalPopup" data-title="Create Campaign" data-class="fbPop submitOffer2 createCampaign calenderCampaign" data-backdrop="static">Create Campaign</a>
   	<a href="#" id="post_facebook_page" class="modalPopup" data-title="Facebook Page" data-class="fbPop delete" data-size="size-small"><span></span></a>
     <!-- <i class="fa fa-angle-down"></i>

        <ul>
        <li><a href="<?php echo base_url().'geoFence/createGeoFence'?>"  data-title="Create GeoFence" data-class="fbPop submitOffer2 createCampaign calenderCampaign" class="btn purple-btn modalPopup">Create GeoFencing</a></li>
        </ul> -->
    </span>
    <?php } ?>
    <input type="hidden" id="baseurl" value="<?php echo base_url();?>" />
    </div>
    

                    <div class="" >
                       <div onload="renderCalender('myCalendar3');">
                              <div class="alert-success calAlert">
                                <!--<h4>This calendar uses the extended form</h4>-->
                              </div>
                              <div class="btn-toolbar">
                                <p class="pull-right lead"></p>
                                <div class="btn-group">
                                	<button class="btn btn-success calender-date" ng-click="changeView('agendaDay', 'myCalendar3')">Day</button>
                                    <button class="btn btn-success calender-date" ng-click="changeView('agendaWeek', 'myCalendar3')">Week</button>
                                    <button class="btn btn-success calender-date" ng-click="changeView('month', 'myCalendar3')">Month</button>
                                </div>
                              </div>
                            <div class="calendar" ng-model="eventSources2" calendar="myCalendar3" ui-calendar="uiConfig.calendar"></div>
                       </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>