<?php
class Gocoin_Gocoinpayment_Block_Adminhtml_System_Config_Field_Tokenurl
        extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
    const CLIENT_ID      = 'payment/Gocoinpayment/client_id';
    const CLIENT_SECRET  = 'payment/Gocoinpayment/client_secret';
    protected function _decorateRowHtml($element, $html)
    {
        $baseurl =  Mage::getBaseUrl() ;
        $client_id = Mage::getStoreConfig(self::CLIENT_ID);
        $client_secret = Mage::getStoreConfig(self::CLIENT_SECRET);
        
        $html.='<script type="text/javascript">var currentUrl="'.$baseurl.'gocoin_callback/index/showtoken"</script>';
        $html.='<script type="text/javascript">var cid="'.$client_id.'"</script>';
        $html.='<script type="text/javascript">var csec="'.$client_secret.'"</script>';

        return '<tr id="row_' . $element->getHtmlId() . '" style="display:none" >' . $html. '</tr>';
    }
}
