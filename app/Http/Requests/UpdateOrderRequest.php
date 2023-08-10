<?php

namespace App\Http\Requests;

use App\Rules\CityExists;
use App\Rules\OrderAffectation;
use App\Rules\OrderNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
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
            'city' => ['required', new CityExists($this->input('confirmation'))],
            'note' => [new OrderNote($this->input('confirmation'))],
            'affectation' => [new OrderAffectation($this->input('confirmation'))],
            // 'items' => 'required',
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
