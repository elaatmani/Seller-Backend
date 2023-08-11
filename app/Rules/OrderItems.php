<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OrderItems implements Rule
{

    protected $messageText;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach($value as $item) {
            if(!is_numeric($item['product_id']) || !$item['product_id']) {
                $this->messageText = "Select item's product.";
                return false;
            }

            if(!is_numeric($item['product_variation_id']) || !$item['product_variation_id']) {
                $this->messageText = "Select item's product variation.";
                return false;
            }

            if(!is_numeric($item['quantity']) || $item['quantity'] == 0) {
                $this->messageText = 'Quantity is cannot be ' . $item['quantity'];
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->messageText;;
    }
}
