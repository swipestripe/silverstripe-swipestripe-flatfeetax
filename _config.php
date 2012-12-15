<?php

//Extensions
Object::add_extension('ShopConfig', 'FlatFeeTaxRate_Extension');
Object::add_extension('OrderForm', 'FlatFeeTaxModifierField_Extension');

if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('FlatFeeTaxRate', 'ExchangeRate_Extension');
}