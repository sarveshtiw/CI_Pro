<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

<script>

$(document).ready(function() {


	// Timeline Page
	$('select[multiple].SlectBox').SumoSelect({csvDispCount:7, placeholder:"Select Trigger Type"});

	$('.SlectBox').SumoSelect();

	$('#uploadImage .block .img img').click(function(){
		$('#uploadImage').find('.dropdown-menu').toggleClass('open');
	});
	$('#uploadImage .dropdown-menu a').click(function(){
		$(this).parents('.dropdown-menu').removeClass('open');
	});


});


</script>



<script type="text/javascript" src="<?php echo base_url(); ?>assets/template/frontend/js/jquery.fancybox.js?v=2.1.5"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/template/frontend/css/jquery.fancybox.css?v=2.1.5" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/template/frontend/css/cropper-main.css" media="screen" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/template/frontend/css/cropper.min.css">
<a class="fancybox" href="#photoUploader"></a>
<div id="photoUploader" style="width:400px; display: none; height:300px;">
  <div style="float:left; width: 100%">
    <div style="float:left;"><span size="9" style="font-size: 24px;font-weight: bold;">Upload Image</span></div>
    <div style="float: right;" class="cropper-header"><button-box></button-box></div>
  </div>
  <div class="respmsg"></div>
  <canvas-box></canvas-box>
</div>
<script id="button-box" type="text/x-template">

  <div @click="click" class="menu"><label for="file" title="Upload" v-show="!uploaded" class="menu__button"><span class="fa fa-upload"></span></label>
  <button data-action="restore" title="Undo" v-show="cropped" class="menu__button restorelbtn cropedunvdo"><span class="fa fa-undo"></span></button>
  <button class="delete" data-action="remove" style="display:none">
  <!--<button data-action="clear" title="Cancel (Esc)" v-show="cropping" class="menu__button  cancelbtn menu__button--danger"><span class="fa fa-ban"></span></button>-->
  <button data-action="crop" title="Crop selected" id="cropng" v-show="cropping" class="menu__button menu__button--success"><span class="fa fa-check"></span></button>
  <button data-action="crop" title="Upload cropped image " id="upld" v-show="cropped;" class="menu__button"><span class="fa fa-upload"></span></button>
  </div>
</script>

<script id="upload-box" type="text/x-template">
  <div @change="change" @dragover="dragover" @drop="drop" v-show="!uploaded" class="upload"><span class="fileUploadBtn"><input class="file" type="file" accept="image/*" id="uploadProfilePic" style="display:none;"></span></div>
</script>
<script id="canvas-box" type="text/x-template"> <div v-show="editable" class="canvas"><div @dblclick="dblclick" class="editor"><template v-if="url"><img src="{{ url }}" alt="{{ name }}" @load="load"></template></div></div><div @click="click" v-show="cropper" class="toolbar"><button data-action="crop" title="Crop (C)" class="toolbar__button cropperselection"><span class="fa fa-crop"></span></button><button data-action="zoom-in" title="Zoom In (I)" class="toolbar__button"><span class="fa fa-search-plus"></span></button><button data-action="zoom-out" title="Zoom Out (O)" class="toolbar__button"><span class="fa fa-search-minus"></span></button><button data-action="rotate-left" title="Rotate Left (L)" class="toolbar__button"><span class="fa fa-rotate-left"></span></button><button data-action="rotate-right" title="Rotate Right (R)" class="toolbar__button"><span class="fa fa-rotate-right"></span></button><button data-action="flip-horizontal" title="Flip Horizontal (H)" class="toolbar__button"><span class="fa fa-arrows-h"></span></button><button data-action="flip-vertical" title="Flip Vertical (V)" class="toolbar__button"><span class="fa fa-arrows-v"></span></button></div> </script>

<script src="<?php echo base_url(); ?>assets/template/frontend/js/vue.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/template/frontend/js/cropper.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/template/frontend/js/main.js"></script>
<script>
var platform = '';
     //For Android
     $("#profilePicFile").on('click', function(e){
        e.preventDefault();
        platform = 'android';
       //alert(platform);
        $("#uploadProfilePic").trigger('click');

    });
     $('#uploadProfilePic').change(function(){
        // alert($('#uploadProfilePic').val());
         });

     //For iOS
     $("#iosPicFile").on('click', function(e){
        e.preventDefault();
        platform = 'ios';
        //alert(platform);
        $("#uploadProfilePic").trigger('click');
    });

   // $('.fancybox').fancybox();
   $('.fancybox').fancybox({
          helpers: {overlay: {closeClick: false}},
          'beforeClose': function () {
              $('.cancelbtn').trigger('click');
              $('.delete').trigger('click');
              $('.upload').attr('style', 'display:block');
          }
      });
    $('.file').change(function(){

         $('.fancybox').trigger('click');

         $('.canvas').attr('style','overflow:auto; height:250px; float:left;');

         $("#imgprv").css('display','block');

    });

     $('#upld,#upld-default').click(function () {

          var croppedimage = $('.editor img').attr('src');
          if($('.file').val()!='')
          {
              if(platform == 'android'){
	              $('#android_app_img').attr('src',croppedimage);
	              $('#profilePic').attr('value',croppedimage);
	              $("#imgprv").attr('src',croppedimage);
	              $("#crossPushImage").css('display','block');
              }else{
            	  $('#ios_app_img').attr('src',croppedimage);
                  $('#iosPic').attr('value',croppedimage);
              }
          }
          else
          {
        	  if(platform == 'android'){
              $('#profilePic').attr('value',croppedimage);
              $('#android_app_img').attr('src',croppedimage);
              $("#crossPushImage").css('display','block');
        	  }else{
        		  $('#iosPic').attr('value',croppedimage);
                  $('#ios_app_img').attr('src',croppedimage);
        	  }
          }

          var croppedimage = $('.editor img').attr('src');
          if($('.file').val()!='')
          {
              $('#imgprv').attr('src',croppedimage);
              $('#profilePic').attr('value',croppedimage);
          }
          else
          {
              $('#profilePic').attr('value',croppedimage);
              $('#imgprv').attr('src',croppedimage);
          }


          $.fancybox.close();
          $('.cancelbtn').trigger('click');
          $('.delete').trigger('click');
      });


		 $('#crossPushImage').click(function () {
			var baseurl = $("#baseurl").val();
			var userid = $("#external_user_id").val();
			var croppedImage = '';
			 $.ajax({
                 type: "POST",
                 url: baseurl + 'contact/saveprofileimage',
                 data: "pic=" + croppedImage+"&userid="+userid, //likeimage 12
                 cache: false,
                 processData: false,
                 contentType: "application/x-www-form-urlencoded",
                 success: function(data) {
                	 	$("#crossPushImage").css('display','none');
			     					$("#imgprv").attr('src','');
			     					$("#imgprv").css('display','none');
			     					$("#profilePic").val("");
                 },

             });


			});

</script>



<script>
//document.getElementById('upload').onchange = uploadOnChange;
function uploadOnChange() {
    var filename = this.value;
    var lastIndex = filename.lastIndexOf("\\");
    if (lastIndex >= 0) {
        filename = filename.substring(lastIndex + 1);
    }
    document.getElementById('filename').value = filename;
}

</script>

<!-- Start of hurree Zendesk Widget script -->

  <!-- End of hurree Zendesk Widget script -->
