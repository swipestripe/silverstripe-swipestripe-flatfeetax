<?php
/**
 * Form field that represents {@link FlatFeeTaxRate}s in the Checkout form.
 */
class FlatFeeTaxModifierField extends ModificationField_Hidden
{
    
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
    public function FieldHolder($properties = array())
    {
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::javascript('swipestripe-flatfeetax/javascript/FlatFeeTaxModifierField.js');
        return $this->renderWith($this->template);
    }
    
    /**
     * Set the amount that this field represents.
     * 
     * @param Money $amount
     */
    public function setAmount(Money $amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Return the amount for this tax rate for displaying in the {@link CheckoutForm}
     * 
     * @return String
     */
    public function Description()
    {
        return $this->amount->Nice();
    }
}

class FlatFeeTaxModifierField_Extension extends Extension
{

    public function updateFields($fields)
    {
        Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
        Requirements::javascript('swipestripe-flatfeetax/javascript/FlatFeeTaxModifierField.js');
    }
}
