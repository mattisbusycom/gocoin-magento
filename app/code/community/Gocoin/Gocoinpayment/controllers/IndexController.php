<?php

class Gocoin_Gocoinpayment_IndexController extends Mage_Core_Controller_Front_Action {
    
	public function indexAction() {
		
		Mage::log(file_get_contents('php://input'), null, 'gocoin.log');
		
		$client_id = Mage::getStoreConfig('payment/Gocoinpayment/client_id');
		$response = Mage::helper('Gocoinpayment')->getNotifyData();
		
		if (isset($response->error))
			Mage::log($response->error, null, 'gocoin.log');
		else {
            $orderId = (int)$response->payload->order_id;
			if ($orderId) {
				$quoteId = $orderId;
				$order = Mage::getModel('sales/order')->load($quoteId, 'quote_id');
			}
            
			Mage::getModel('Gocoinpayment/ipn')->createInvoice($response->payload, $response->event); 
			
			// update the order if it exists already
			if ($order->getId()) {
				switch($response->event)
                {
                    case 'invoice_created':
                    case 'invoice_payment_acknowledged':
                    case 'invoice_underpaid':
                    case 'invoice_overpaid':
                        break;
                    case 'invoice_paid':
                        $method = Mage::getModel('Gocoinpayment/paymentMethod');
                        $method->markOrderPaid($order);
                        break;
                }
            }
        }
	}

}
