<?php

class Gocoin_Gocoinpayment_Block_Invoice extends Mage_Checkout_Block_Onepage_Payment {

    public function createInvoice($coin_type='BTC') {
        if (!($quote = Mage::getSingleton('checkout/session')->getQuote()) or !($payment = $quote->getPayment()) or !($instance = $payment->getMethodInstance()) or ($instance->getCode() != 'Gocoinpayment'))
            return -1;

        $quote = $this->getQuote();
        $quoteId = $quote->getId();
        $orderid = $quote->getReservedOrderId();
        $options = array(
            'currency' => $quote->getQuoteCurrencyCode(),
            'fullNotifications' => 'true',
            "callback_url" => Mage::getUrl('gocoin_callback'),
            "redirect_url" => Mage::getUrl('checkout/onepage/success'),
        );

        // add customer address data to options
        $method = Mage::getModel('Gocoinpayment/paymentMethod');
        $options += $method->getAddressValue($quote->getShippingAddress());

        $price = round($quote->getGrandTotal(), 4);

        Mage::log('create invoice for ' . $price . ' ' . $quote->getQuoteCurrencyCode(), NULL, 'gocoin.log');
        $invoice = Mage::helper('Gocoinpayment')->createInvoice($orderid, $price, $options,$coin_type);
        Mage::log($invoice, NULL, 'gocoin.log');

        if (isset($invoice->error)) {
            Mage::log('Error creating gocoin invoice', null, 'gocoin.log');
            Mage::log($invoice->error, null, 'gocoin.log');
            Mage::throwException("Error creating GoCoin invoice.  Please try again or use another payment option.");
            return false;
        } 
        elseif(empty($invoice->merchant_id) || empty($invoice->id))
        {
            Mage::log('Error creating gocoin invoice', null, 'gocoin.log');
            Mage::log("Merchant ID or Invoice ID is blank", null, 'gocoin.log');
            Mage::throwException("Error creating GoCoin invoice.  Please try again or use another payment option.");
            return false;
        }
        else {
            //save invoice to database
            Mage::helper('Gocoinpayment')->addInvoiceData($orderid, $invoice);
        }

        $url = "https://gateway.gocoin.com/merchant/" . $invoice->merchant_id . "/invoices/" . $invoice->id;
        Mage::getSingleton('checkout/session')->setData("invoice_url", $url);
        return $url;
    }

}
