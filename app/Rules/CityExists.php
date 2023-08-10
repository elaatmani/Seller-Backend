<?php

namespace App\Rules;

use App\Models\City;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class CityExists implements Rule
{
    protected $confirmation;
    protected $require_validation = ['confirmer'];
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
        return in_array($this->confirmation, $this->require_validation) ? City::where('name', $value)->exists() : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The city is not valid';
    }
}
