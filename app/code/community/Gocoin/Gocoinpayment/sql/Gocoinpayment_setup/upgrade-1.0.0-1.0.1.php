<?php
$installer = $this;
$installer->startSetup(); 
$installer->run("
Alter Table `{$installer->getTable('Gocoinpayment/ipn')}` ADD COLUMN `coin_currency` VARCHAR(50) NULL AFTER `updated_time`; 
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `coin_type` VARCHAR( 50 ) NOT NULL ;
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `coin_type` VARCHAR( 50 ) NOT NULL ;
");
$installer->endSetup();