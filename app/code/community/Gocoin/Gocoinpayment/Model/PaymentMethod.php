<?php
 
class Gocoin_Gocoinpayment_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {
    
    /**
    * payment method parameters
    */
	protected $_code = 'Gocoinpayment';    
	protected $_isGateway               = true; 
	protected $_canAuthorize            = true; 
	protected $_canCapture              = false;
	protected $_canCapturePartial       = false;
	protected $_canRefund               = false;
	protected $_canVoid                 = false;
	protected $_canUseInternal          = false;
	protected $_canUseCheckout          = true;
	protected $_canUseForMultishipping  = true;
        protected $_canSaveCc = false;
	
	function canUseForCurrency($currencyCode) {		
		//currently we can only use USD currency
        $result = $currencyCode == 'USD'?true:false;
		return $result;
	}
 	
	public function canUseCheckout() { 
		$client_id = Mage::getStoreConfig('payment/Gocoinpayment/client_id');
        $client_secret = Mage::getStoreConfig('payment/Gocoinpayment/client_secret');
        $access_token = Mage::getStoreConfig('payment/Gocoinpayment/access_token');
        
		if (!strlen($client_id)) {
			Mage::log('Gocoin/Gocoinpayment: Client ID should be entered', null, 'gocoin.log');
			return false;
		}
        
        if (!strlen($client_secret)) {
            Mage::log('Gocoin/Gocoinpayment: Client Secret should be entered', null, 'gocoin.log');
            return false;
        }
        
        if (!strlen($access_token)) {
            Mage::log('Gocoin/Gocoinpayment: Access Token should be entered', null, 'gocoin.log');
            return false;
        }
        
		return $this->_canUseCheckout;
    }

	public function authorize(Varien_Object $payment, $amount) {
	    return $this->checkInvoiceCreated($payment);
	}
	
	function checkInvoiceCreated($payment) {
		$quoteId = $payment->getOrder()->getQuoteId();
		$invoice = Mage::getModel('Gocoinpayment/ipn');
		if (!$invoice->getInvoice($quoteId)) {
			Mage::throwException("Invoice is not created on gocoin.com");
		} else if (!$invoice->checkOrderCompleted($quoteId)) {
			$payment->setIsTransactionPending(true);
		} else {
			$this->markOrderPaid($payment->getOrder());
		}
		return $this;
	}
	
	function markOrderPaid($order) {
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
		if (!count($order->getInvoiceCollection()))	
		{
			$invoice = $order->prepareInvoice()
				->setTransactionId(1)
				->addComment('Invoice created')
				->register()
				->pay();

			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder());
			$transactionSave->save();
		}
	}
	
	function getAddressValue($address) {
        $options = array (
            'customer_name' => $address->getName(),
            'customer_address_1' => $address->getStreet1(),
            'customer_address_2' => $address->getStreet2(),
            'customer_city' => $address->getCity(),
            'customer_region' => $address->getRegionCode(),
            'customer_postal_code' => $address->getPostcode(),
            'customer_country' => $address->getCountry(),
            'customer_phone' => $address->getTelephone(),
            'customer_email' => $address->getEmail(),
        );
        
		return $options;
	}
    
        public function getOrderPlaceRedirectUrl()
        {
            $url = "";
            $url = Mage::getSingleton('checkout/session')->getData("invoice_url");
            Mage::getSingleton('checkout/session')->setData("invoice_url","");
            return $url;
        }
}
?>
