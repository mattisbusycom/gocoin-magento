2013 Gocoin

Installation
------------
Copy this folder and its contents into your magento root directory.

Configuration
-------------
1. Go to Admin System->Configuration->Payment Methods.
2. Set Client Id and Client Secret and save these values.
3. In App settings on dashboard.gocoin.com, set the app url to "http://yoursite.com/index.php/gocoin_callback/index/showtoken"
3. After that, you need to click Get Token button. it will show you new window to redirect dashboard.gocoin.com and please click allow button, then you will redirect above url and can see the access token value. please copy this value and paste it to access token field in magento admin.

Usage
-----
In checkout page, when you are in payment method step, you can see the gocoin payment options.
check this option and go to next step, then you will show the window for gocoin invoice.
in this window, you need to pay for this invoice, and after that click place order button.

	
	