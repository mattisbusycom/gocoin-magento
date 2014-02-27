<?php
/**
 * Description of Cointype
 *
 * @author mumate
 */
class Gocoin_Gocoinpayment_Block_Payment_Info_Cointype extends Mage_Payment_Block_Info
{
    
//    protected function _construct()  
//    {  
//        parent::_construct();  
//        $this->setTemplate('gocoin/payment/form/coincurrency.phtml');
//    }  
//    
    
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $currency = $info->getAdditionalData();
        if(empty($currency))
            $currency = $this->getAdditionalData();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array(
            Mage::helper('payment')->__('Coin Currency') => $currency
        ));
        return $transport;
    }
   
}

?>
