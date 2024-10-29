=== Plugin Name ===
Contributors: kutuco
Tags: afterbuy, order, export, woocommerce
Requires at least: 4.0
Tested up to: 4.9.2
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to export your orders from WooCommerce to the ERP Afterbuy via the Shop-API from Afterbuy.


== Description ==

This plugin allows you to export your orders from WooCommerce to the ERP Afterbuy. 
Only orders that have the status "completed" are exported! 
If an order has a different status, you can switch it to "completed" in "WooCommerce => Orders" 
and it will be exported magically. Only PayPal Payments are exported automatically. 
Shipping Costs are NOT calculated by Afterbuy.

The data of the order is sent to the Afterbuy-Shop-API by calling " https://api.afterbuy.de/afterbuy/ShopInterface.aspx ". Afterbuy is a third-party ERP.

Here is a list of data that will NOT be exported, but may be added in further updates: 

- Anrede
- Fax
- Geburtsdatum
- Afterbuy Stammartikel-ID
- Zahlarten-Aufschlag (z.B bei Nachnahme)
- Kommentar
- ZFunktions ID (Afterbuy-interne ID)  
- Bestandart
- Prüfung auf Packstation
- Bankdaten
- Umsatzsteuer-ID  
- Markierung-ID (afterbuy-intern)  
- Billsafe-Daten
- Artikelgewicht

== Installation ==

This section describes how to install the plugin and get it working. You need your Afterbuy's Shop-API credentials in order to set this plugin up!

1. Upload the plugin files to the `/wp-content/plugins/afterwoo` directory, 
or install the plugin through the WordPress plugins screen directly.

2. Activate the plugin through the 'Plugins' screen in WordPress.

3. Use the Settings->Afterbuy screen to configure the plugin. !IMPORTANT!

You need your API-Credentials from Afterbuy which you get by ordering the Afterbuy Shop-API.

== Changelog ==

= 1.0 =
* Initial release.
