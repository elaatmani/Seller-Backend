<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class OrderNote implements Rule
{

    protected $confirmation;
    protected $require_note = [ 'annuler' ];

    protected $custom_messages = [
        'annuler' => 'Add cancellation note.'
    ];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($confirmation)
    {
        $this->confirmation = $confirmation;
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
        return in_array($this->confirmation, $this->require_note) ? !!$value : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if(in_array($this->confirmation, $this->require_note)) {
            return $this->custom_messages[$this->confirmation];
        }

        return 'Note is not valid';
    }
}
