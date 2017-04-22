<script>
    var chart;
    //$(document).ready(function () {
        $(document).on('click', '#compare-campaign', function (e) {
            if ($('.selected-icon').length == 0) {
                alert("Please select a value to compare");
            } else {
                var baseurl = $("#baseurl").val();
                var selectedId = $('.selected-icon').parent().attr('id').split("-")[1];
                var prevViewCampaign = $("#prevViewCampaign").val();
                var prevSentCampaign = $("#prevSentCampaign").val();
                var currentCampaign = $("#currentCampaign").val();

                $.ajax({
                    url: baseurl + "appUser/compairCampaign/",
                    type: "POST",
                    data: {selectedId: selectedId, prevViewCampaign: prevViewCampaign, prevSentCampaign: prevSentCampaign,currentCampaign:currentCampaign},
                    success: function (json) {

                      var obj=jQuery.parseJSON(json)


                        $("#container").remove();
                        $("#insert-after").empty();
                        $("#append-container").append('<div id="container1"></div>');

        chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'container1',
                                type: 'spline',
                            },
                            title: {
                                text: 'View and Sent Campaign Performance'
                            },
                            subtitle: {
                                text: ''
                            },
                            exporting: {
            enabled: true
          },
                            xAxis: {
                                type: 'datetime',
                                dateTimeLabelFormats: {// don't display the dummy year
                                    month: '%e. %b',
                                    year: '%b'
                                },
                                title: {
                                    text: 'Date'
                                }
                            },
                            yAxis: {
                                title: {
                                    text: 'Campaign Performance'
                                },
                                min: 0
                            },
                            tooltip: {
                                shared: true,
                                headerFormat: '<b></b>',
                              //  pointFormat: '{point.x:%e. %b}: {point.y:.2f} m'
                            },
                            plotOptions: {
                                spline: {
                                    marker: {
                                        enabled: true
                                    }
                                }
                            },
                            series: obj.graph
                        });


                      $("#insert-after").append('<div class="row performance profiling"><div class="col-xs-12"> <div class="row performance"> <div class="col-xs-6"> <div class="innerPerformace"> <strong>Campaign Name :</strong>'+obj.newCampaignData.campaignName+'</div></div><div class="col-xs-6"> <div class="innerPerformace"> <strong>Push Title :</strong>'+obj.newCampaignData.push_title+'</div></div></div></div><div class="col-xs-12"> <div class="row"> <div class="col-xs-6"> <div class="inner purpleBg"> <p><strong>'+obj.newCampaignData.countViewCampaigns+'</strong></p><h4>Views</h4> </div></div><div class="col-xs-6"> <div class="inner purpleBg"> <p><strong>'+obj.newCampaignData.countSendCampaigns+'</strong></p><h4>Sent</h4> </div></div></div></div></div>');

                   $(".close").trigger("click");
                   }
                });
            }

        });


        /** Added function for InApp campare **/
        $(document).on('click', '#compare-in-app', function (e) {
          //alert("fkjfghjf");
            if ($('.selected-icon').length == 0) {
                alert("Please select a value to compare");
            } else {
                var baseurl = $("#baseurl").val();
                var selectedId = $('.selected-icon').parent().attr('id').split("-")[1];
                var prevViewInApp = $("#prevViewInApp").val();
                var prevSentInApp = $("#prevSentInApp").val();
                var currentInApp = $("#currentInApp").val();

                $.ajax({
                    url: baseurl + "inAppMessaging/compareInApp/",
                    type: "POST",
                    data: {selectedId: selectedId, prevViewInApp: prevViewInApp, prevSentInApp: prevSentInApp,currentInApp:currentInApp},
                    success: function (json) {
                        var obj=jQuery.parseJSON(json)
                        $("#container").remove();
                        $("#insert-after").empty();
                        $("#append-container").append('<div id="container1"></div>');

                        chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'container1',
                                type: 'spline',
                            },
                            title: {
                                text: 'View and Sent In-App Messaging Performance'
                            },
                            subtitle: {
                                text: ''
                            },
                            exporting: {
                                enabled: true
                            },
                            xAxis: {
                                type: 'datetime',
                                dateTimeLabelFormats: {// don't display the dummy year
                                    month: '%e. %b',
                                    year: '%b'
                                },
                                title: {
                                    text: 'Date'
                                }
                            },
                            yAxis: {
                                title: {
                                    text: 'In-App Messaging Performance'
                                },
                                min: 0
                            },
                            tooltip: {
                                headerFormat: '<b>{series.name}</b><br>',
                                pointFormat: '{point.x:%e. %b}: {point.y:.2f} m'
                            },
                            plotOptions: {
                                spline: {
                                    marker: {
                                        enabled: true
                                    }
                                }
                            },
                            series: obj.graph
                        });

                      $("#insert-after").append('<div class="row performance profiling"><div class="col-xs-12"> <div class="row performance"> <div class="col-xs-6"> <div class="innerPerformace"> <strong>Campaign Name : </strong>'+obj.newInAppData.campaignName+'</div></div><div class="col-xs-6"> <div class="innerPerformace"> <strong>Push Title : </strong>'+obj.newInAppData.push_title+'</div></div></div></div><div class="col-xs-12"> <div class="row"> <div class="col-xs-6"> <div class="inner purpleBg"> <p><strong>'+obj.newInAppData.countViewInApp+'</strong></p><h4>Views</h4> </div></div><div class="col-xs-6"> <div class="inner purpleBg"> <p><strong>'+obj.newInAppData.countViewInApp+'</strong></p><h4>Sent</h4> </div></div></div></div></div>');

                      $(".close").trigger("click");
                   }
                });
            }

        });
  //  });
</script>
