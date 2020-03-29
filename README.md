# FTSCoin Gateway for WooCommerce

## Features

* Payment validation done through `ftscoin-service`.
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of FTSCoin.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* FTSCoin wallet to receive payments.
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/ftscoin/ftscoin-woocommerce-gateway/releases) or clone with `git clone https://github.com/ftscoin/ftscoin-woocommerce-gateway.git`
* Unzip or place the `ftscoin-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "FTSCoin Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

# Set-up FTSCoin daemon and FTSCoin-Service

* Root access to your webserver
* Latest [FTSCoin-currency binaries](https://github.com/ftscoin/ftscoin/releases)

After downloading (or compiling) the FTSCoin binaries on your server, run `FTSCoind` and `ftscoin-service`. You can skip running `FTSCoind` by using a remote node with `ftscoin-service` by adding `--daemon-address` and the address of a public node.

Note on security: using this option, while the most secure, requires you to run the FTSCoin-Service program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds.

## Configuration

* `Enable / Disable` - Turn on or off FTSCoin gateway. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: FTSCoin Gateway)
* `Discount for using FTSCoin` - Percentage discount applied to orders for paying with FTSCoin. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `FTSCoin Address` - Your public FTSCoin address starting with FTS. (No default)
* `FTSCoin-Service Host/IP` - IP address where `ftscoin-service` is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `FTSCoin-Service Port` - Port `ftscoin-service` is bound to with the `--bind-port` argument. (Default 8070)
* `FTSCoin-Service Password` - Password `ftscoin-service` was started with using the `--rpc-password` argument. (Default: blank)
* `Show QR Code` - Show payment QR codes. (Default: unchecked)
* `Show Prices in FTSCoin` - Convert all prices on the frontend to FTSCoin. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in FTSCoin is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded. (Default: 2)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of FTSCoin in the selected currency. If no currency is provided, the store's default currency will be used.

```
[ftscoin-price]
[ftscoin-price currency="BTC"]
[ftscoin-price currency="USD"]
```
Will display:
```
1 FTS = 0.00000149 LTC
1 FTS = 0.00003815 USD
```
