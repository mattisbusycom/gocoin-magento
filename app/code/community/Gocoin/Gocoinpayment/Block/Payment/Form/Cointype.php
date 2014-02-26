<?php
/**
 * Description of Cointype
 *
 * @author mumate
 */
class Gocoin_Gocoinpayment_Block_Payment_Form_Cointype extends Mage_Payment_Block_Form_Cc  
{
    protected function _construct()  
    {  
        parent::_construct();  
        $this->setTemplate('gocoin/payment/form/cointype.phtml');
    }  
    
    public function getAvailableTypes()
    {
        $cModel = Mage::getModel('Gocoinpayment/coincurrency');
        $types = $cModel->getCoinCurrency();
        return $types;
    }
}

?>
