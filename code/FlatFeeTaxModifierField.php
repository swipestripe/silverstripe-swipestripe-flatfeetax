<?php
/**
 * Form field that represents {@link FlatFeeTaxRate}s in the Checkout form.
 * 
 * @author Frank Mullenger <frankmullenger@gmail.com>
 * @copyright Copyright (c) 2011, Frank Mullenger
 * @package swipestripe
 * @subpackage shipping
 */
class FlatFeeTaxModifierField extends ModifierHiddenField {
	
  /**
   * The amount this field represents e.g: 15% * order subtotal
   * 
   * @var Money
   */
	protected $amount;

  /**
   * Render field with the appropriate template.
   *
   * @see FormField::FieldHolder()
   * @return String
   */
  function FieldHolder() {
    Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
    Requirements::javascript('swipestripe-flatfeetax/javascript/FlatFeeTaxModifierField.js');
    return $this->renderWith($this->template);
  }
  
  /**
   * Set the amount that this field represents.
   * 
   * @param Money $amount
   */
  function setAmount(Money $amount) {
    $this->amount = $amount;
    return $this;
  }
  
  /**
   * Return the amount for this tax rate for displaying in the {@link CheckoutForm}
   * 
   * @return String
   */
  function Description() {
    return $this->amount->Nice();
  }
}

class FlatFeeTaxModifierField_Extension extends Extension {

  public function updateOrderForm($form) {
    Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
    Requirements::javascript('swipestripe-flatfeetax/javascript/FlatFeeTaxModifierField.js');
  }
}