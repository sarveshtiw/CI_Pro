<?php if(!empty($sendCampaigns) > 0 || !empty($viewCampaigns)){
        $subtitletext = 'View and Sent Campaign Performance';
        $yaxistext = 'Campaign Performance';
      }

      if(!empty($sendInAppUsers) > 0 || !empty($viewInAppUsers)){
        $subtitletext = 'View and Sent In-App Messaging Performance';
        $yaxistext = 'In-App Messaging Performance';
        $sendCampaigns = $sendInAppUsers;
        $viewCampaigns = $viewInAppUsers;
      }
  ?>
  <script src="<?php echo base_url(); ?>assets/template/frontend/js/highcharts.js"></script>
  <script src="<?php echo base_url(); ?>assets/template/frontend/js/exporting.js"></script>
  <script type="text/javascript">
      $('#container').highcharts({
          chart: {
              type: 'areaspline'
          },
          title: {
              text: ''
          },
          subtitle: {
            text: '<?php echo $subtitletext; ?>'
          },
          credits: {
            enabled: false
          },
          exporting: {
            enabled: true
          },
          xAxis: {
              type: 'datetime',
              dateTimeLabelFormats: { // don't display the dummy year
                 // month: '%e. %b',
                  year: '%b'
              },
              title: {
                  text: 'Date'
              }
          },
          yAxis: {
              title: {
                  text: '<?php echo $yaxistext; ?>'
              },
              min: 0
          },
           tooltip: {
            formatter: function () {
                var s1 = this.series.chart.series[0].processedYData[this.point.index];
                var s2 = this.series.chart.series[1].processedYData[this.point.index];
                if (s1 == s2) {
                    return '<b>' + this.series.chart.series[0].name + ' :' + s1 + '</b><br/><b>' + this.series.chart.series[1].name + ' :' + s2 + '</b>';
                }
                return '<b>' + this.series.name + ' :' + this.y + '</b>';
            }
        },

          plotOptions: {
              spline: {
                  marker: {
                      enabled: true
                  }
              }
          },

          series: [{
              name: 'View',
              // Define the data points. All series have a dummy year
              // of 1970/71 in order to be compared on the same x axis. Note
              // that in JavaScript, months start at 0 for January, 1 for February etc.
              data: [
                  <?php echo $viewCampaigns; ?>
               ]
          }, {
              name: 'Sent',
              data: [
                  <?php echo $sendCampaigns; ?>
                ]
          }]
      });
  </script>
  <!-- End Gender Stats  -->
