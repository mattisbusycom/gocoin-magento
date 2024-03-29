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
            $coin_type = Mage::getSingleton('checkout/session')->getData("coin_type");
            $order->getQuote()->getPayment()->setAdditionalData($coin_type);
            $block = new Gocoin_Gocoinpayment_Block_Invoice();
            $url = $block->createInvoice($coin_type);
            if(!empty($url))
            {
               Mage::getSingleton('checkout/session')->setData("invoice_url", $url);
            }
        }
    }
}

?>
