<?php

/**
 * Description of Coincurrency
 *
 * @author mumate
 */
class Gocoin_Gocoinpayment_Model_Coincurrency {
    
    public function getCoinCurrency()
    {
        $currency= array('BTC' => 'Bitcoin','LTC' => 'Litecoin');
        return $currency;
    }
}

?>
