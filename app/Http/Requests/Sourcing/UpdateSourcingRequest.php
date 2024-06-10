<?php

namespace App\Http\Requests\Sourcing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSourcingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('seller');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'destination_country' => [ 'required' ],
            'shipping_method' => [ 'required', 'not_in:not-selected' ],
            'product_name' => [ 'required' ],
            'product_url' => [ 'required' ],
            'estimated_quantity' => [ 'required', 'numeric' ],
        ];
    }
}
