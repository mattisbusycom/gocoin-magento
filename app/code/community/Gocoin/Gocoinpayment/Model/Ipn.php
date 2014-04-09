<?php

class Gocoin_Gocoinpayment_Model_Ipn extends Mage_Core_Model_Abstract
{
	function _construct()
	{
		$this->_init('Gocoinpayment/ipn');
		return parent::_construct();
	}
	
	function addInvoiceData($invoice, $status)
	{
            $url = "https://gateway.gocoin.com/merchant/".$invoice->merchant_id."/invoices/".$invoice->id;
            
            $collection = Mage::getModel('Gocoinpayment/ipn')->getCollection()
                                    ->AddFilter('order_id', $invoice->order_id);
//            if (count($collection) > 0) {
//                foreach( $collection as $item) {
//                    $item->delete();
//                }
//            }
        
            return $this
                    ->setOrderId($invoice->order_id)
                    ->setInvoiceId($invoice->id)
                    ->setUrl($url)
                    ->setStatus($status)
                    ->setBtcPrice($invoice->price)
                    ->setPrice($invoice->base_price)
                    ->setCurrency($invoice->base_price_currency)
                    ->setInvoiceTime($invoice->created_at)
                    ->setExpirationTime($invoice->expires_at)
                    ->setUpdatedTime($invoice->updated_at)
                    ->setCoinCurrency($invoice->price_currency)
                    ->setFingerprint($invoice->user_defined_8)
                    ->save();
	}
	
	function checkOrderStatus($quoteId, $statuses)
	{
		if (!$quoteId) return false;
					
		$quote = Mage::getModel('sales/quote')->load($quoteId, 'entity_id');
		if (!$quote)
		{
			Mage::log('quote not found', NULL, 'gocoin.log');
			return false;
		}

		$collection = $this->getCollection()->AddFilter('order_id', $quoteId);
		foreach($collection as $i) {
			if (in_array($i->getStatus(), $statuses)) {
                return true;
			}
		}
				
		return false;		
	}
	
	function checkOrderPaid($quoteId) {
		return $this->checkOrderStatus($quoteId, array('paid', 'overpaid'));
	}
	
	function checkOrderCompleted($quoteId) {
		return $this->checkOrderStatus($quoteId, array('ready_to_ship', 'fulfilled'));
	}
    
        function getInvoice($quoteId) {
            $collection = $this->getCollection()->AddFilter('order_id', $quoteId);
            if (count($collection) == 0 ) return false;
            $invoice = $collection->getFirstItem();

            return $invoice;
        }
        
        function validateFingerprint($quoteId,$fingerprint)
        {
            $collection = $this->getCollection()->AddFilter('order_id', $quoteId)->AddFilter('status', 'pending_payment')->AddFilter('fingerprint', $fingerprint);
            if (count($collection) == 0 ) 
                return false;
            else
                return true;
        }

}

?>