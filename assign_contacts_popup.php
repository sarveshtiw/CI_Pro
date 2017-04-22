
<style>
    #loading{
        background: rgba(204, 204, 204, 0.86) url("<?php echo base_url(); ?>assets/template/frontend/img/loader.svg") no-repeat center center !important;
        height: 100%;
        width: 100%;
        display: block;
        position: absolute;
        z-index: 99999999999;
        margin-left: -15px;
        bottom: 0;
    }
    input#chk {
        margin-left: 7px;
    }
   #example1 { width: 100%; }
   .addPersonaUser .modal-dialog {max-width: 960px; width: 100%;}
</style>
<div class="col-xs-12">
	<div class="table-responsive">
		<div class="col-xs-6">
			<input type="button" value="Assign Contact" class="btn purple-btn" id="assign-contacts" onclick="return personaAssignContactValidation();">
  	</div>
		<div class="col-xs-6">
			 <input type="hidden" value="<?php echo $this->session->userdata('persona_user_id'); ?>" name="persona_user_id" id="persona_user_id">
		   <span id="error_assign_persona"></span>
		</div>
		<!--  style="color:#424141; font-size:12px;position:relative;bottom:14px;"<table width="100%" border="0" customItems cellspacing="0" cellpadding="0" class="grid sms"> -->
		<table id="example1" class="display datatable grid" cellspacing="0" width="100%">
			<thead>
				<tr>

                    <th style="width: 1px;"></th>
                    <th style="width: 18px;" class="sorting_disabled" rowspan="1" colspan="1">
                        <input name="chkAll" id="example-select-all" value="" onclick="" type="checkbox">
                    </th>

                    <th style="width: 40px;"></th>
                    <th style="width: 107px;">Name</th>
                    <th style="width: 171px;">Email</th>
                    <th style="width: 107px;">Company</th>
                    <th>Phone Number</th>
                    <th>App Group</th>
                    <th style="width: 120px;">Persona</th>
				</tr>

			</thead>
		</table>

	</div>
</div>

<input type="hidden" value="<?= base_url() ?>" id="baseurl">

<div id="loading" style="display:none;"></div>

<script src="<?php echo base_url(); ?>assets/template/frontend/js/3.0/jquery-1.11.3.min.js"></script>

<script src="<?php echo base_url(); ?>assets/template/frontend/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/template/frontend/js/jquery.dataTables.delay.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#example1').dataTable({
        "sScrollY": "400px",
        "bProcessing": true,
        "bServerSide": true,
        "sServerMethod": "POST",
        "sAjaxSource": '<?=base_url();?>contact/contactListingResponse/type<?php echo $app_group_id ?>',
        "iDisplayLength": 10,
        // "aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50,100]],
        "aaSorting": [[0, 'desc']],
        "aoColumns": [
            { "bVisible": false, "bSearchable": false, "bSortable": true },
            { "bVisible": true, "bSearchable": false, "bSortable": false },
            { "bVisible": true, "bSearchable": true, "bSortable": false },
            { "bVisible": true, "bSearchable": true, "bSortable": true },
            { "bVisible": true, "bSearchable": true, "bSortable": true },
            { "bVisible": true, "bSearchable": true, "bSortable": true },
            { "bVisible": true, "bSearchable": true, "bSortable": true },
            { "bVisible": true, "bSearchable": true, "bSortable": true },
            { "bVisible": true, "bSearchable": true, "bSortable": true }
        ],
        "deferRender": true,
        drawCallback: function(settings) {
            var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
            pagination.toggle(this.api().page.info().pages > 1);
        }
	}).fnSetFilteringDelay(1000);
});
$('#example1 #example-select-all').on('click', function(){
    $('#example1 input[type="checkbox"]').prop('checked', this.checked);
});
</script>

