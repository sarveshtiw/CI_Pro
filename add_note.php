<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Untitled Document</title>
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
        </style>
    </head>
    <body>
        <div id="loading" style="display:none;"></div>
        <div class="row">
            <span id="success_fileupload" style="color:red; font-size:12px;bottom:14px;"></span>
            <div class="col-xs-12">
                <form method="post" id="formuploadcsv" name="fileinfo" onsubmit="return submitNoteForm();"  method="POST" enctype="multipart/form-data">
                    <ul>
                        
                        <li>
                            <textarea id="noteArea" name="noteArea"rows="10" cols="58" style="resize:vertical;" required="required"></textarea>
                        </li>
                        <span id="error_csvupload" style="color:red; font-size:12px;bottom:14px;"></span>
                        <li>
                            <input type="hidden" id="baseurl" value="<?php echo base_url(); ?>" />  
                            <input type="hidden" id="loginUserId" value="<?php echo $loginUserDetails->user_id; ?>" /> 
                            <input type="hidden" id="contactUserId" value="<?php echo $externalUserDetials->external_user_id; ?>" /> 
                            <button class="btn purple-btn submitBtn">Submit</button>
                            
                        </li>
                    </ul>
                </form> 
            </div>
        </div>

    </body>
</html>
