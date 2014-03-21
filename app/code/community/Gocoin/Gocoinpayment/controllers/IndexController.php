<?php

class Gocoin_Gocoinpayment_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        Mage::log(file_get_contents('php://input'), null, 'gocoin_webhooks.log');

        $client_id = Mage::getStoreConfig('payment/Gocoinpayment/client_id');
        $response = Mage::helper('Gocoinpayment')->getNotifyData();
        if (isset($response->error))
            Mage::log($response->error, null, 'gocoin_webhooks_error.log');
        else {
            $orderId = $response->payload->order_id;
            if ($orderId) {
                $quoteId = $orderId;
                //$order = Mage::getModel('sales/order')->load($quoteId, 'quote_id');
                $order = Mage::getModel('sales/order')->loadByIncrementId($quoteId);
            }

            //Mage::getModel('Gocoinpayment/ipn')->createInvoice($response->payload, $response->event);
            
            // update the order if it exists already
            if ($order->getId()) {
                Mage::getModel('Gocoinpayment/ipn')->addInvoiceData($response->payload, $response->event);
                switch ($response->event) {
                    case 'invoice_created':
                    case 'invoice_payment_received':
                        break;
                    case 'invoice_ready_to_ship':
                        $method = Mage::getModel('Gocoinpayment/paymentMethod');
                        $method->markOrderPaid($order);
                        try{
                            $method->changeOrderStatus($order,Mage_Sales_Model_Order::STATE_PROCESSING);
                        }catch (Exception $e){Mage::logException($e);}
                        break;
                }
            }
        }
    }

    public function showtokenAction() {
        $params = $this->getRequest()->getParams();
        
        $token = Mage::helper('Gocoinpayment')->getAccessToken($params['code']);
        if ($token['success'] == true) {
            print_r('Copy this Access Token into your Magento Backend: ' . $token['data']);
        } else {
            print_r('Error: ' . $token['data']);
        }
        exit(0);
    }

}
