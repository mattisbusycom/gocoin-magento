<?php
/**
 * Description of Placeorder
 *
 * @author mumate
 */
class  Gocoin_Gocoinpayment_Model_Placeorder {
    
    public function afterSaveOrder($observer)
    {
        $order= $observer->getEvent()->getOrder();
        $py = $order->getQuote()->getPayment()->getMethod();
       // $code = $order->getQuote()->getPayment()->getCode();
        if(stripos($py, "Gocoinpayment") !== FALSE)
        {
            $block = new Gocoin_Gocoinpayment_Block_Invoice();
            $url = $block->createInvoice();
            if(!empty($url))
            {
               Mage::getSingleton('checkout/session')->setData("invoice_url", $url);
            }
        }
    }
}

?>
