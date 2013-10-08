<?php

//TODO: Move to yml file and test
if (class_exists('ExchangeRate_Extension')) {
	Object::add_extension('FlatFeeTaxRate', 'ExchangeRate_Extension');
}