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
    $store = Mage::app()->getStore();
    $name = $store->getName();
  $_sku = $_REQUEST['sku'];
  if(empty($_sku)){
    echo "SKU not found.";
    exit(1);
  }
  $_catalog = Mage::getModel('catalog/product');
  $_productId = $_catalog->getIdBySku($_sku);
  $_product = Mage::getModel('catalog/product')->load($_productId);
  $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
  $_categories = $_product-> getCategoryIds();
  $tier_prices = $_product->getTierPrice();
  $_media_gallery = $_product->getMediaGalleryImages();
  $_attributes = $_product->getAttributes();

  $_type = $_product->getTypeId();

  $_categories_json = array();
  $_gallery_json = array();
  $_tier_prices = array();
  $_attributes_json = array();
  $_parent_ids = array();

  if($_product->getTypeId() == "simple"){
    $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($_product->getId());
    if(!$parentIds)
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_product->getId());
  }


  foreach ($_attributes as $key => $value) {
    $attr = $_product->getAttributeText($key);
    if($attr != ''){
     array_push($_attributes_json, '{ "attribute_name": "'.$key.'", "value": "'.$attr.'"}');
    }
  }
  $_attributes_json = "[".implode($_attributes_json,", ")."]";

  foreach ($parentIds as $value) {
    $sku = Mage::getModel('catalog/product')->load($value)->getSku();
    array_push($_parent_ids,$sku);
  }

  foreach ($tier_prices as $tier) {
      array_push($_tier_prices,'{  "price_id": "'.$tier['price_id'].'",'.'"website_id": "'.$tier['website_id'].'",'.'"all_groups": "'.$tier['all_groups'].'",'.'"custom_group": "'.$tier['cust_group'].'",'.'"price_qty": "'.$tier['price_qty'].'",'.'"website_price": "'.$tier['website_price'].'",'.'"is_percent": "'.$tier['is_percent'].'",'.'"price": "'.$tier['price'].'"}');
  }
  $_tier_prices = "[".implode($_tier_prices,", ")."]";

  foreach ($_categories as $cat) {
      $category = Mage::getModel('catalog/category')->load($cat);
      array_push($_categories_json,'{ "id": "'.$category->getId().'",'.' "name": "'.$category->getName().'",'.' "description": "'.$category->getDescription().'"}');
  }
  $_categories_json = "[".implode($_categories_json,", ")."]";
   
  foreach ($_media_gallery as $gallery) {
      array_push($_gallery_json,'{ "id": "'.$gallery->getId().'",'.' "url": "'.$gallery->getUrl().'"}');
  }
  $_gallery_json = "[".implode($_gallery_json ,", ")."]";
  header('Content-Type: application/json');

  echo '{
          "id": "'.$_productId.'",
          "SKU": "'.$_sku.'",
          "TotalQuantity": "'.intval($qty).'",
          "price": "'.$_product->getPrice().'",
          "special_price": "'.$_product->getSpecialPrice().'",
          "special_price_start_date": "'.$_product->getSpecialFromDate().'",
          "special_price_from_date": "'.$_product->getSpecialToDate().'",
          "weight": "'.$_product->getWeight().'",
          "name": "'.$_product->getName().'",
          "description": "'.$_product->getDescription().'",
          "short_description": "'.$_product->getShortDescription().'",
          "status": "'.$_product->getStatus().'",
          "type": "'.$_product->getTypeId().'",
          "url": "'.$_product->getProductUrl().'",
          "image_url": "'.$_product->getImageUrl().'",
          "parent_product_sku": "['.implode($_parent_ids,",").']",
          "categories": '.$_categories_json.',
          "images": '.$_gallery_json.',
          "tier_prices": '.$_tier_prices.',
          "other_attributes": '.$_attributes_json.'
          }';

} catch (Exception $e) {
    Mage::printException($e);
    exit(1);
}