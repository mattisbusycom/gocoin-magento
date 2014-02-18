2014 GoCoin
Version 1.0.0
Note: This version of this plugin only supports USD as a base currency. 

Installation
------------
Merge the contents of this folder into your Magento Root directory. From a bash terminal:

```
$ cp -r ./gocoin-magento-master/ ./magento/
```

You may need to clear your Magento Cache by going to System -> Cache Management.

Configuration
-------------
1. Go to Admin System->Configuration->Payment Methods.
2. Set Client Id and Client Secret and save these values. You must click 'Save Config' in order to get an access token from GoCoin. 
3. You will need to create an application within the GoCoin dashboard. Login to https://dashboard.gocoin.com.
Visit Applications, create a new app, and be sure to set the redirect_uri to "YOUR_SITE_URL/index.php/gocoin_callback/index/showtoken"
3. Click 'Get Token'. A popup window will open to dashboard.gocoin.com. Allow your application to access your account and create invoices on your behalf. You will be redirected back to your site and shown given an access token. Copy and Paste this into your Magento Backend in the Access Token field.
4. Be sure to 'Save Config' again.

Usage
-----
In checkout page, when you are in payment method step, you can see the GoCoin payment option.
check this option and go to next step, then you will show the window for GoCoin invoice.
in this window, you need to pay for this invoice, and after that click place order button.

This plugin is configured to automatically receive payment updates from GoCoin via callbacks. See the GoCoin API documentation for more details. 
	
	