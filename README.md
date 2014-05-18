ams-oscommerce
==============

Artabit Merchant Service osCommerce Payment Module

Installation
------------
1. Copy ams_callback.php into your osCommerce catalog directory
2. Copy artabit directory into your osCommerce catalog directory
3. Copy includes directory into your osCommerce catalog directory and merge the contents

Requirements
------------
Make sure that your PHP environment has the following modules:
- php-json

Configuration
-------------
1. Create an application access (API Token and API Secret) at ams.artabit.com
2. Install "ArtaBit Merchant Service" module in your osCommerce admin panel (under Modules > Payment)
3. Edit and fill out configuration information

Usage
-----
ArtaBit Merchant Service will only be enabled if you use IDR currency code.

When a user chooses ArtaBit Merchant Service for bitcoin payment, they will be presented with an order summary as the next step. Upon confirming their order, the system takes them to artabit invoice page. Once payment is received, artabit will show invoice success page with a link to redirect them back to your osCommerce order success page. If the user cancels the payment, artabit will show invoice cancel page with a link to redirect them back to your osCommerce shopping cart page and the order is removed.