<?php foreach($errors as $error): ?>
<div class="error"><p><strong>FTSCoin Gateway Error</strong>: <?php echo $error; ?></p></div>
<?php endforeach; ?>

<h1>FTSCoin Gateway Settings</h1>

<div style="border:1px solid #ddd;padding:5px 10px;">
    <?php
         echo 'Wallet height: ' . $balance['height'] . '</br>';
         echo 'Your balance is: ' . $balance['balance'] . '</br>';
         echo 'Unlocked balance: ' . $balance['unlocked_balance'] . '</br>';
         ?>
</div>

<table class="form-table">
    <?php echo $settings_html ?>
</table>

<h4><a href="https://github.com/ftscoin/ftscoin-woocommerce-gateway">Learn more about using the FTSCoin payment gateway</a></h4>

<script>
function ftscoinUpdateFields() {
    var useFTSCoinPrices = jQuery("#woocommerce_ftscoin_gateway_use_ftscoin_price").is(":checked");
    if(useFTSCoinPrices) {
        jQuery("#woocommerce_ftscoin_gateway_use_ftscoin_price_decimals").closest("tr").show();
    } else {
        jQuery("#woocommerce_ftscoin_gateway_use_ftscoin_price_decimals").closest("tr").hide();
    }
}
ftscoinUpdateFields();
jQuery("#woocommerce_ftscoin_gateway_use_ftscoin_price").change(ftscoinUpdateFields);
</script>

<style>
#woocommerce_ftscoin_gateway_ftscoin_address,
#woocommerce_ftscoin_gateway_viewkey {
    width: 100%;
}
</style>
