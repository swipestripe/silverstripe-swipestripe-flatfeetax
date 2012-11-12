<?php
/**
 * Default settings.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage admin
 */

//Extensions
Object::add_extension('ShopConfig', 'FlatFeeTaxRate_Extension');
Object::add_extension('CheckoutPage_Controller', 'FlatFeeTaxModifierField_Extension');

if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('FlatFeeTaxRate', 'ExchangeRate_Extension');
}