<?php
/*
Plugin Name: FTSCoin Woocommerce Gateway
Plugin URI:
Description: Extends WooCommerce by adding a FTSCoin Gateway
Version: 3.0.0
Tested up to: 4.9.8
Author: mosu-forge, SerHack
Author URI: https://monerointegrations.com/
*/
// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('FTSCOIN_GATEWAY_EXPLORER_URL', 'http://ftscoin.xyz/block');
define('FTSCOIN_GATEWAY_ATOMIC_UNITS', 8);
define('FTSCOIN_GATEWAY_ATOMIC_UNIT_THRESHOLD', 100000000); // Amount under in atomic units payment is valid
define('FTSCOIN_GATEWAY_DIFFICULTY_TARGET', 120);

// Do not edit these constants
define('FTSCOIN_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FTSCOIN_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FTSCOIN_GATEWAY_ATOMIC_UNITS_POW', pow(10, FTSCOIN_GATEWAY_ATOMIC_UNITS));
define('FTSCOIN_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.FTSCOIN_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'ftscoin_init', 1);
function ftscoin_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-ftscoin-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new FTSCoin_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-ftscoin-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'ftscoin_gateway');
    function ftscoin_gateway($methods) {
        $methods[] = 'FTSCoin_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ftscoin_payment');
    function ftscoin_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=ftscoin_gateway_settings').'">'.__('Settings', 'ftscoin_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'ftscoin_cron_add_one_minute');
    function ftscoin_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'ftscoin_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'ftscoin_activate_cron');
    function ftscoin_activate_cron() {
        if(!wp_next_scheduled('ftscoin_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'ftscoin_update_event');
        }
    }

    add_action('ftscoin_update_event', 'ftscoin_update_event');
    function ftscoin_update_event() {
        FTSCoin_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.FTSCoin_Gateway::get_id(), 'ftscoin_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'ftscoin_order_page');
    add_action('woocommerce_email_after_order_table', 'ftscoin_order_email');

    function ftscoin_order_confirm_page($order_id) {
        FTSCoin_Gateway::customer_order_page($order_id);
    }
    function ftscoin_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            FTSCoin_Gateway::customer_order_page($order);
    }
    function ftscoin_order_email($order) {
        FTSCoin_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_ftscoin_gateway_payment_details', 'ftscoin_get_payment_details_ajax');
    function ftscoin_get_payment_details_ajax() {
        FTSCoin_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'ftscoin_add_currency');
    function ftscoin_add_currency($currencies) {
        $currencies['FTSCoin'] = __('FTSCoin', 'ftscoin_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'ftscoin_add_currency_symbol', 10, 2);
    function ftscoin_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'FTSCoin':
            $currency_symbol = 'FTS';
            break;
        }
        return $currency_symbol;
    }

    if(FTSCoin_Gateway::use_ftscoin_price()) {

        // This filter will replace all prices with amount in FTSCoin (live rates)
        add_filter('wc_price', 'ftscoin_live_price_format', 10, 3);
        function ftscoin_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return FTSCoin_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'ftscoin_order_item_price_format', 10, 3);
        function ftscoin_order_item_price_format($price_html, $item, $order) {
            return FTSCoin_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'ftscoin_order_total_price_format', 10, 2);
        function ftscoin_order_total_price_format($price_html, $order) {
            return FTSCoin_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'ftscoin_order_totals_price_format', 10, 3);
        function ftscoin_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = FTSCoin_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'ftscoin_enqueue_scripts');
    function ftscoin_enqueue_scripts() {
        if(FTSCoin_Gateway::use_ftscoin_price())
            wp_dequeue_script('wc-cart-fragments');
        if(FTSCoin_Gateway::use_qr_code())
            wp_enqueue_script('ftscoin-qr-code', FTSCOIN_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');

        wp_enqueue_script('ftscoin-clipboard-js', FTSCOIN_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('ftscoin-gateway', FTSCOIN_GATEWAY_PLUGIN_URL.'assets/js/ftscoin-gateway-order-page.js');
        wp_enqueue_style('ftscoin-gateway', FTSCOIN_GATEWAY_PLUGIN_URL.'assets/css/ftscoin-gateway-order-page.css');
    }

    // [ftscoin-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function ftscoin_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = FTSCoin_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.8f', $rate / 1e8);

        return "<span class=\"ftscoin-price\">1 FTS = $rate_formatted $currency</span>";
    }
    add_shortcode('ftscoin-price', 'ftscoin_price_func');


    // [ftscoin-accepted-here]
    function ftscoin_accepted_func() {
        return '<img src="'.FTSCOIN_GATEWAY_PLUGIN_URL.'assets/images/ftscoin-accepted-here.png" />';
    }
    add_shortcode('ftscoin-accepted-here', 'ftscoin_accepted_func');

}

register_deactivation_hook(__FILE__, 'ftscoin_deactivate');
function ftscoin_deactivate() {
    $timestamp = wp_next_scheduled('ftscoin_update_event');
    wp_unschedule_event($timestamp, 'ftscoin_update_event');
}

register_activation_hook(__FILE__, 'ftscoin_install');
function ftscoin_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "ftscoin_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(64) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "ftscoin_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(64) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "ftscoin_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
