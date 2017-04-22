<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<script src="<?php echo base_url(); ?>assets/template/frontend/js/innerjquery.js" type="text/javascript"></script>
</head>

<body>
<div class="row">
	<div class="col-xs-12"><p>Are you sure you want to delete this Persona?</p></div>
</div>
<div class="row">
	<div class="col-xs-12"><div class="btnWrap">
    <button class="btn gray-btn" data-dismiss="modal">Cancel</button>
    <button class="btn purple-btn" onclick="deletePersona('<?php echo $persona_user_id; ?>');">Delete</button>
    <input type="hidden" id ="baseurl" value="<?=base_url()?>">
    </div></div>
</div>
</body>
</html>
