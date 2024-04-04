<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'required|max:255',
            'image_url' => 'required|url',
            'price_for_guest' => 'required|numeric|min:0',
            'price_for_employee' => 'required|numeric|min:0',
            'meal_type' => 'required|in:breakfast,lunch',
            'is_fasting' => 'required|boolean',
            'is_drink' => 'required|boolean',
            'is_available' => 'required|boolean',
            'available_amount' => 'required|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
