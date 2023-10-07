<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class OrderAffectation implements Rule
{
    protected $confirmation;
    protected $user;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($confirmation)
    {
        $this->confirmation = $confirmation;
        $this->user = Auth::user();
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
        if($this->user->hasRole('follow-up')) {
            return true;
        }

        return $value != null ? in_array($this->confirmation, ['confirmer', 'refund','change']): true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Cannot update affectation: Order not confirmed.';
    }
}
