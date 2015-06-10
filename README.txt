=== Woocommerce Multiple Addresses ===
Contributors: n3wnormal, dix.alex, ya.kuzmenko
Tags: woocommerce, shipping, multiple addresses, predefined addresses
Requires at least: 3.0.1
Tested up to: 4.2.2
Stable tag: 1.0.7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin allows customers have more than one shipping and/or billing addresses.

== Description ==
The plugin allows customers add more than one shipping or billing address. Customers can switch between the addresses at checkout and setup a default one in My Account.

The plugin requires WooCommerce 2.3 Handsome Hippo or higher and compatible up to WooCommerce 2.3.10.

We support the plugin.
Also developers can create pull requests on our [BitBucket repo](https://bitbucket.org/n3wnormal-dev/woocommerce-multiple-addresses/).

== Installation ==
Ensure you have WooCommerce plugin installed and activated.

1. Download the plugin file to your computer and unzip it
2. Using FTP software, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Screenshots ==
1. Manage multiple addresses page.
2. Notice reminding the customer to edit addresses and 'predefined addresses' select boxes at checkout.

== Changelog ==
= 1.0.7.1 =
* Fix for not adding label to new addresses
* Added French translation (thanks to [denizgezgin](http://wordpress.org/support/profile/denizgezgin))

= 1.0.7 =
* Added labels for addresses (now it shows label and zipcode in 'predefined addresses')
* Fixed not populating state field
* Added Turkish translation (thanks to [denizgezgin](http://wordpress.org/support/profile/denizgezgin))

= 1.0.6 =
* State Field Not Pre-Populated bug fix
* Fix calculating shipping when changing country on checkout
* Disable 'default shipping' option if shipping is disabled
* Fix My Account Shipping Address edit link
* Fix German translations
* Update compatibility with WordPress 4.2.X and WooCommerce 2.3.X

= 1.0.5 =
* Fixed critical bug related to WordPress Network (MultiSite) support
* Improved WooCommerce plugin dependency logic

= 1.0.4 =
* Added compatibility to WooCommerce 2.1.7 and WordPress 3.9
* Added Multisite Network support
* Added translation support
* Added German translation (thanks to [markus.reich](http://wordpress.org/support/profile/markusreich)
* Added 'predefined addresses' select box on checkout for both shipping and billing addresses (thanks to [markus.reich](http://wordpress.org/support/profile/markusreich))
* Other fixes

= 1.0.3 =
* Added compatibility to WooCommerce 2.1.2 and WordPress 3.8.1

= 1.0.2 =
* Minor bug fixes including the issue with not pre-populating original shipping to the plugin

= 1.0.1 =
* Bug fix (fatal error on activation)

= 1.0 =
* Initial release.