<?php

namespace App\Http\Requests;

use App\Rules\CityExists;
use App\Rules\OrderAffectation;
use App\Rules\OrderItems;
use App\Rules\OrderNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fullname' => ['required'],
            'phone' => ['required'],
            'adresse' => [ 'required' ],
            'city' => ['required', new CityExists($this->input('confirmation'))],
            'cancel_reason' => [new OrderNote($this->input('confirmation'))],
            'affectation' => [new OrderAffectation($this->input('confirmation'))],
            'items' => ['required', new OrderItems()],
        ];
    }

    public function attributes()
    {
        return [
            'fullname' => 'Client Name',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The form is not valid',
            'code' => 'VALIDATION_ERROR',
            'errors' => $this->formatErrors($validator),
        ], 422));
    }

    protected function formatErrors(Validator $validator)
    {
        $messages = [];

        foreach ($validator->errors()->getMessages() as $field => $errorMessages) {
            $messages[$field] = $errorMessages[0];
        }

        return $messages;
    }
}
