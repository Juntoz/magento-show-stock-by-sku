<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// Change current directory to the directory of current script
chdir(dirname(__FILE__));
require 'app/Mage.php';
if (!Mage::isInstalled()) {
  echo "Application is not installed yet, please complete install wizard first.";
    exit;
}
// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);
try {

	$taxes = Mage::getModel('tax/calculation_rule')->getCollection();
	$tax_array = array();
	foreach($taxes as $tax) {

		$taxCustomerClassArray = array();
		$taxProductClassArray = array();
		$taxRatesArray = array();

		foreach ($tax->getCustomerTaxClasses() as $key => $value) {
			$taxClass = Mage::getModel('tax/class')->load($value);
			array_push($taxCustomerClassArray,'{"name": "'.$taxClass->getClassName().'"}');
		}

		foreach ($tax->getProductTaxClasses() as $key => $value) {
			$taxClass = Mage::getModel('tax/class')->load($value);
			array_push($taxProductClassArray,'{"name": "'.$taxClass->getClassName().'"}');
		}

		foreach ($tax->getRates() as $key => $value) {
			$rate = Mage::getModel('tax/calculation_rate')->load($value);
			array_push($taxRatesArray,'{"name": "'.$rate->getCode().'",
										"tax_calculation_rate_id": "'.$rate->getTaxCalculationRateId().'",
										"tax_country_id": "'.$rate->getTaxCountryId().'",
										"tax_region_id": "'.$rate->getTaxRegionId().'",
										"tax_postcode": "'.$rate->getTaxPostCode().'",
										"rate": "'.$rate->getRate().'",
										"zip_is_range": "'.$rate->getZipIsRange().'",
										"zip_from": "'.$rate->getZipFrom().'",
										"zip_to": "'.$rate->getZipTo().'"
										}');
		}

		array_push($tax_array,'{"code": "'.$tax->getCode().'" ,
								"calcilation_rule_id": "'.$tax->getTaxCalculationRuleId().'" ,  
								"priority": "'.$tax->getPriority().'" ,  
								"position": "'.$tax->getPosition().'" ,  
								"calculate_subtotal": "'.$tax->getCalculateSubtotal().'" ,  
								"customer_classes": ['.implode($taxCustomerClassArray,', ').'] ,  
								"product_classes": ['.implode($taxProductClassArray,', ').'] ,  
								"rates": ['.implode($taxRatesArray,', ').']
								}');

	}


	header('Content-Type: application/json');

	echo '['.implode($tax_array,',').']';

} catch (Exception $e) {
    Mage::printException($e);
    exit(1);
}