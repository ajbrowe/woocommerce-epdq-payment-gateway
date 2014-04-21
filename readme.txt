=== Barclay ePDQ payment gateway for wordpress===
Contributors: maksbd19
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=453VDL4HEHWKQ
Tags: woocommerce, payment-gateway, barclay, epdq
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will add a barclay epdq payment gateway and will allow you to take payment directly in your barclay account.  

== Description ==

This plugin will add a barclay epdq payment gateway in the default list of woocommerce and will allows you to take payment directly 
in your barclay account.  This plugin allows you to take payments directly in your barclay epdq account. But before you need to 
configure the plugin accordingly. It is crucial that parameters match both in the back office and in your plugin setting.

Please read the instruction manual in the [Barclay ePDQ ecommerce site](http://www.barclaycard.co.uk/business/accepting-payments/epdq-ecomm/)
carefully to set the parameters and configure the back office and how to go live in the back office. You should have good idea about
the SHA-IN and SHA-OUT parameters. 

Note that, if something unexpected happens while processing the payment and if the payment processor returns the customer without recieving
any payment regarding to the order then the order will be marked as failed. Customer can process the order again by going to the account page.
If the customer cancels the order then the order will be cancelled in the shop.   
For instruction on how to setup the plugin in the admin panel see other notes.

Please donate if you find this helpful for your project. Thanks.

This plugin is now tested upto wordpress 3.8 verson.


== Settings and Configuration==

Parameters in this plugin :

*	Enable/disable		:	Enable/disbale the gateway
*	AAVCHECK			:	Default value of the AAVCHECK parameter, if nothing is returned by the epdq processor in the transection
							feedback url even if it is selected. Not everytime the processor returns a valid data for this parameter. 
							In case you selected this parameter in the back office in the dynamic parameter listing then you better
							consult with the help desk. I noticed that when I selected this parameter and tested the purchase
							operation it was returning nothing for this parameter. After a long night searching fo this 
							and many trial and error attempts I figured out that if the default value is "NO" then even if
							the processor doesn't return any value for this parameter, the returned shasign matches with the
							generated one.   
*	CVCCHECK			:	Default value of the AAVCHECK parameter, if nothing is returned by the epdq processor in the transection
							feedback url even if it is selected. Description is same as the AAVCHECK.
*	Title				:	Title of the gateway. This name will be shown gateways list everywhere.
*	Description			:	This text will be shown when a customer click on the radio button associated with this gateway in the
							checkout pge.
*	PSPID				:	PSPID of the barclay epdq account.
*	Store status		:	Whether the store is live or under testing environment.
							The only difference is in the form processing url for both cases.
							For test environment, the form will be submitted to the test url of the barclay which is https://mdepayments.epdq.co.uk/ncol/test/orderstandard.asp 						
							For live environment, the form will be submitted to the live url of the barclay which is https://mdepayments.epdq.co.uk/ncol/live/orderstandard.asp 						
*	SHA-IN Passphrase	:	Sha in pass phrase. For more info please refer to the installation manual [Go to barclay](http://www.barclaycard.co.uk/business/accepting-payments/epdq-ecomm/).
*	SHA-OUT Passphrase	:	Sha out pass phrase. For more info please refer to the installation manual [Go to barclay](http://www.barclaycard.co.uk/business/accepting-payments/epdq-ecomm/).
*	SHA encryption method:	Encryption method you choose in bacrlay's back office. In can be either sha-1, sha-256 or sha-512.
*	Error notice		:	If something unexpected happen and no explanation found for the exception then this message will be shown.
*	Back Url			:	URL of the web page to display to the customer when he clicks the "Back" button on barclay's secure payment page.
*	CATALOGURL			:	(Absolute) URL of your catalogue. When the transaction has been processed, your customer is requested to return to this URL via a button.
*	Payment Page		:	Enable/disable the following template design parameters for the payment processing page.
*	Payment Page Title	:	Title and header of the payment processing page.
*	Background Color	:	Background colour of the payment processing page.
*	Text Color			:	Text Color of the payment processing page.
*	Table Background Color	:	Table background colour of the payment processing page.
*	Table Text Color	:	Table Text Color of the payment processing page.
*	Button Background Color	:	Button Background Color of the payment processing page.
*	Button Text Color	:	Button Text Color of the payment processing page.
*	Font Type			:	Font Type of the payment processing page.
*	Logo				:	Logo to be used in the payment processing page (it is required to be stored in a ssl enabled location).

You can change the color and font of the layout by changing the parameters. They are bind to a on blur method which will allow you see the
effect when you defocused the input field. You can click on the <strong>update layout</strong> button to get the result all at once.


== Installation ==

Installation of this plugin is very simple.

1. Download and unzip the "wordpress-epdq.zip" file.
2. Upload the "wordpress-epdq" folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to WooCommerce > Settings > Payment Gateways > EPDQ Checkout and set the parameters. 

== Frequently Asked Questions ==

There is no FAQ.
Please comment and review this plugin. I look forward to hearing from you.

== Screenshots ==

1. screenshot-1.png is the screen shot of the plugin settings page from a working example. 


== Changelog ==

= 1.0 =
* This is the first version of this plugin.

 == Upgrade Notice == 
 
 There is no updrade notice




=== AJBROWE fork - fixing for woocommerce 2.1.x ===

as explained in this forum comment some changes are necessary for woocommerce 2.1.x
http://wordpress.org/support/topic/not-compatible-with-woocommerce-21x
