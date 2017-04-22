<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>

<style>
#loading{
  background: rgba(204, 204, 204, 0.86) url("<?php echo base_url(); ?>assets/template/frontend/img/loader.svg") no-repeat center center;
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

<span id="success_message"></span>
<div class="cardInfo">
    <div class="row">
        <div class="col-sm-6 col-xs-12">
        <input name="firstname" id="cardfirstname" type="text" placeholder="First Name on Card*" maxlength="20">
        <span id="error_cardfirstname" style="font-size:12px;color:#424141;"></span>
        </div>
        <div class="col-sm-6 col-xs-12">
        <input name="lastname" id="cardlastname" type="text" placeholder="Last Name on Card*" maxlength="20">
        <span id="error_cardlastname" style="font-size:12px;color:#424141;"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-xs-12">
        <input name="cardNumber" id="cardNumber" type="text" placeholder="Card Number*" maxlength="16">
        <span id="error_cardNumber" style="font-size:12px;color:#424141;"></span>
        </div>
        <div class="col-sm-6 col-xs-12">
	        <select class="SlectBox" name="card_type" id="card_type">
	        <option value="">Card type *</option>
            <?php if(!empty($card_type)) {
            foreach ($card_type as $card) { ?>
            <option value="<?php echo $card->card; ?>">
            <?php echo $card->card; ?>
            </option>
            <?php } ?>
            <?php } ?>
	        </select>
	        <span id="error_cardtype" style="font-size:12px;color:#424141;"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-xs-12">
	        <select class="SlectBox" name="expire_month" id="expire_month">
	        <option value="">Expiry Month *</option>
            <option value="01">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
	        </select>
	        <span id="error_expire_month" style="font-size:12px;color:#424141;"></span>
        </div>
        <div class="col-sm-6 col-xs-12">
	        <select class="SlectBox" name="expire_year" id="expire_year">
	        <option value="">Expiry Year *</option>
            <?php
            $year = date('Y');
            $j = $year + 10;
            for ($k = $year; $k <= $j; $k++) {
            ?>
            <option value="<?php echo $k; ?>">
            <?php echo $k; ?>
            </option>
            <?php } ?>
	        </select>
	        <span id="error_expire_year" style="font-size:12px;color:#424141;"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-xs-8">
        <input name="cvv" id="cvv" type="password" placeholder="CVV Code*" maxlength="3">
        <span id="error_cvv" style="font-size:12px;color:#424141;"></span>
        </div>
    </div>
</div>


<div class="personalInfo">
    <div class="row">
        <div class="col-sm-6 col-xs-12">
        <input name="address" id="address" type="text" placeholder="Address*" maxlength="200">
        <span id="error_address" style="font-size:12px;color:#424141;"></span>
        </div>
        <div class="col-sm-6 col-xs-12">
        <select class="SlectBox" name="country" id="country">
        <option value="">Select Country *</option>
        <?php foreach ($countries as $coun) { ?>
        <option value="<?php echo $coun->country_id; ?>">
        <?php echo $coun->country_name; ?>
        </option>
        <?php } ?>
        </select>
        <span id="error_country" style="font-size:12px;color:#424141;"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-xs-12">
        <input name="state" id="state" type="text" placeholder="State*" maxlength="20">
        <span id="error_state" style="font-size:12px;color:#424141;"></span>
        </div>
        <div class="col-sm-6 col-xs-12">
        <input name="city" id="city" type="text" placeholder="City*" maxlength="20">
        <span id="error_city" style="font-size:12px;color:#424141;"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-xs-8">
        <input name="zip" id="zip" type="text" placeholder="Zip Code*" maxlength="10">
        <span id="error_zip" style="font-size:12px;color:#424141;"></span>
        </div>
        <?php $login = $this->administrator_model->front_login_session();
        			$checkUserTrail =  checkUserTrailAccount($login->user_id); ?>
        <?php if($checkUserTrail['remainingDays'] < 1 && $checkUserTrail['accountType'] == "trail"){ ?>
        <div class="col-sm-6 col-xs-8">
            <a style="float:left" class="btn green-btn" href="<?php echo base_url()?>home/logout">Logout</a>
        </div>
        <?php } ?>

    </div>
</div>


<div class="cartInfo">
  <div class="row">
    <div class="col-sm-7 col-xs-12">
	     <p>Your card will be charged per month - <strong>$<?php echo $package['price']; ?></strong></p>
    </div>
<div class="col-sm-5 col-xs-12">
  <input type="hidden" id="baseurl" value="<?php echo base_url();?>" />
	<input type="hidden" id="paymentMode" value="<?php echo $package['paymentMode']; ?>" />
	<input type="hidden" id="amount" value="<?php echo $package['price']; ?>" />
	<input type="hidden" id="currency" value="<?php echo $package['currency_type']; ?>" />
	<input type="hidden" id="brand_checkout" value="brand_signup_checkout" />
	<input onclick="return brandCheckout();" type="button" class="btn green-btn" value="Pay Now" />
  <input onclick="return brandPaymentByPaypal();" type="button" class="btn green-btn" value="Pay with PayPal" />
</div>
  </div>
</div>

<script type="text/javascript">
$('.bootstrap-dialog-message').css('background-image', 'none');
$('.SlectBox').SumoSelect({search: true, searchText: 'Enter here.'});
$('#cardNumber').on('input', function (event) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
$('#cvv').on('input', function (event) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
<script src="<?php echo base_url(); ?>assets/template/frontend/js/innerjquery.js" type="text/javascript"></script>
</body>
</html>
