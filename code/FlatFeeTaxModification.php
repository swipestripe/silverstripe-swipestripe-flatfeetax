<?php

class FlatFeeTaxModification extends Modification {

	public static $has_one = array(
		'FlatFeeTaxRate' => 'FlatFeeTaxRate'
	);

	public static $defaults = array(
		'SubTotalModifier' => false,
		'SortOrder' => 150
	);

	public function add($order, $value = null) {

		//Get valid rates for this order
		$rates = null;

		$country = Country_Shipping::get()
			->where("\"Code\" = '" . $order->ShippingCountryCode . "'")
			->first();
		$countryID = ($country && $country->exists()) ? $country->ID : null;

		$rates = ($countryID) 
			? $rates = FlatFeeTaxRate::get()->where("\"CountryID\" = '$countryID'")
			: null;

		if ($rates && $rates->exists()) {

			//Pick the rate
			$rate = $rates->find('ID', $value);

			if (!$rate || !$rate->exists()) {
				$rate = $rates->first();
			}

			//Generate the Modification now that we have picked the correct rate
			$mod = new FlatFeeTaxModification();

			$mod->Price = $rate->Amount($order)->getAmount();

			$mod->Description = $rate->Description;
			$mod->OrderID = $order->ID;
			$mod->Value = $rate->ID;
			$mod->FlatFeeTaxRateID = $rate->ID;
			$mod->write();
		}
	}

	public function getFormFields() {

		$fields = new FieldList();

		$rate = $this->FlatFeeTaxRate();
		$countryID = ($rate && $rate->exists()) ? $rate->CountryID : null;

		$rates = ($countryID) 
			? $rates = FlatFeeTaxRate::get()->where("\"CountryID\" = '$countryID'")
			: null;

		if ($rates && $rates->exists()) {

			$field = new FlatFeeTaxModifierField(
				$this,
				$rate->Label(),
				$rate->ID
			);
			
			//Set the amount for display on the Order form
			$field->setAmount($rate->Price($this->Order()));
			$fields->push($field);
		}

		if (!$fields->exists()) Requirements::javascript('swipestripe-flatfeetax/javascript/FlatFeeTaxModifierField.js');

		return $fields;
	}
}