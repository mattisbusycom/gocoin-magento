<?php
$installer = $this;
$installer->startSetup(); 
$installer->run("
Alter Table `{$installer->getTable('Gocoinpayment/ipn')}` ADD COLUMN `fingerprint` VARCHAR(250) NULL; 
");
$installer->endSetup();