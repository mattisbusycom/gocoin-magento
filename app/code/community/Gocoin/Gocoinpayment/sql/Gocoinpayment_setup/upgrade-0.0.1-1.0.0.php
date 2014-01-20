<?php
$installer = $this;
$installer->startSetup(); 
$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('Gocoinpayment/ipn')}` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned default NULL,
  `invoice_id` varchar(200) NOT NULL,
  `url` varchar(400) NOT NULL,
  `status` varchar(20) NOT NULL,
  `btc_price` decimal(16,8) NOT NULL,
  `price` decimal(16,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `invoice_time` datetime NOT NULL,
  `expiration_time` datetime NOT NULL,
  `updated_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;
 
");
 
$installer->endSetup();