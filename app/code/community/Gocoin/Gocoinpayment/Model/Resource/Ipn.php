<?php

class Gocoin_Gocoinpayment_Model_Resource_Ipn extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('Gocoinpayment/ipn', 'id');
	}
}

?>