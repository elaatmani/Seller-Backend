<?php

namespace App\Http\Requests\SupplyRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasRole('seller');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'quantity' => [ 'required', 'integer', 'gt:0' ],
            'product_id' => [ 'required', 'exists:products,id' ],
            'product_variation_id' => [ 'required', 'exists:product_variations,id' ],
        ];
    }

    public function messages()
    {
        return [
            'product_id.exists' => 'Select a valid product.',
            'product_variation_id.exists' => 'Select a valid product variation.'
        ];
    }
}
