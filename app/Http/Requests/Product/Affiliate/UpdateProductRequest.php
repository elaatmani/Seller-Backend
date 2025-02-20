<?php

namespace App\Http\Requests\Product\Affiliate;

use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('affiliate-manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required','string','max:255'],
            'description' => ['string'],
            // 'sku' => ['required', 'unique:products,ref'],
            'buying_price' => ['required', 'numeric'],
            'selling_price' => ['required', 'numeric'],

            'category_id' => ['nullable'],
            'status' => ['nullable'],

            'delivery_rate' => ['nullable', 'numeric'],
            'confirmation_rate' => ['nullable', 'numeric'],
            'metadata' => ['nullable', 'array'],
            
            'media' => ['required'],

            'has_variations' => 'required|boolean',
            // 'initial_quantity' => [
            //     'required_if:has_variations,false',
            //     'integer',
            //     function ($attribute, $value, $fail) {
            //         if (!$this->has_variations && $value == 0) {
            //             $fail('The initial quantity field must be greater than 0 when the product has no variations.');
            //         }
            //     },
            // ],

            'variations' => [
                'required_if:has_variations,true',
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->has_variations && empty($value)) {
                        $fail('The ' . $attribute . ' field must not be empty when has_variations is true.');
                    }
                },
            ],

            // 'variations.*.quantity' => [
            //     'required_if:has_variations,true',
            //     'integer',
            //     'min:1',
            //     function ($attribute, $value, $fail) {
            //         if ($this->has_variations && $value <= 0) {
            //             $fail('Each variation quantity must be greater than 0.');
            //         }
            //     },
            // ],

        ];
    }

    public function messages()
    {
        return [
            'initial_quantity.required_if' => 'The initial quantity field is required when there is no variations.',
            'variations.required_if' => 'The variations field is required.',
            'variations.*.quantity.required_if' => 'Each variation quantity is required',
            'variations.*.quantity.min' => 'Each variation quantity must be at least 1.',
            'variations.array' => 'The variations field must be an array.',
            'variations.*.quantity.required_if' => 'Each variation must have a quantity when has_variations is true.',
            'variations.*.quantity.integer' => 'Each variation quantity must be an integer.',
            'variations.*.quantity.min' => 'Each variation quantity must be at least 1.',
        ];
    }


    protected function formatValidationErrors(Validator $validator)
    {
        $errors = $validator->errors()->getMessages();
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            if (preg_match('/variations\.\d+\.quantity/', $field)) {
                $formattedErrors['variations'] = array_merge(
                    $formattedErrors['variations'] ?? [],
                    $messages
                );
            } else {
                $formattedErrors[$field] = $messages;
            }
        }

        return $formattedErrors;
    }
}
