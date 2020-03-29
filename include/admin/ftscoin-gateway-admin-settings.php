<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'ftscoin_gateway'),
        'label' => __('Enable this payment gateway', 'ftscoin_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'ftscoin_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'ftscoin_gateway'),
        'default' => __('FTSCoin Gateway', 'ftscoin_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'ftscoin_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'ftscoin_gateway'),
        'default' => __('Pay securely using FTSCoin. You will be provided payment details after checkout.', 'ftscoin_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using FTSCoin', 'ftscoin_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with FTSCoin', 'ftscoin_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'ftscoin_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'ftscoin_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'ftscoin_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'ftscoin_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'ftscoin_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'ftscoin_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately four minutes', 'ftscoin_gateway'),
        'type' => __('number'),
        'default' => '10'
    ),
    'ftscoin_address' => array(
        'title' => __('FTSCoin Address', 'ftscoin_gateway'),
        'label' => __('Public FTSCoin Address'),
        'type' => 'text',
        'desc_tip' => __('FTSCoin Wallet Address (FTS)', 'ftscoin_gateway')
    ),
    'daemon_host' => array(
        'title' => __('FTSCoin-Service Host/IP', 'ftscoin_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the ftscoin-service Host/IP to authorize the payment with', 'ftscoin_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('FTSCoin-Service Port', 'ftscoin_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the ftscoin-service port to authorize the payment with', 'ftscoin_gateway'),
        'default' => '8070',
    ),
    'daemon_password' => array(
        'title' => __('FTSCoin-Service Password', 'ftscoin_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the ftscoin-service password to authorize the payment with', 'ftscoin_gateway'),
        'default' => '',
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'ftscoin_gateway'),
        'label' => __('Show QR Code', 'ftscoin_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'use_ftscoin_price' => array(
        'title' => __('Show Prices in FTSCoin', 'ftscoin_gateway'),
        'label' => __('Show Prices in FTSCoin', 'ftscoin_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to FTSCoin (experimental)'),
        'default' => 'no'
    ),
    'use_ftscoin_price_decimals' => array(
        'title' => __('Display Decimals', 'ftscoin_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 2,
    ),
);
