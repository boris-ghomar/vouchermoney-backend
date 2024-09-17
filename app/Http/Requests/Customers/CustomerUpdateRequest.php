<?php

namespace App\Http\Requests\Customers;

use App\Http\Requests\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends ApiRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer'); // Get the customer ID from the route

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name')->ignore($customerId)
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id)
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->user()->id)
            ],
            'password' => [
                'nullable', // Password is not required for updates
                'string',
                'min:8',
                'confirmed'
            ],
            'balance' => [
                'required',
                'numeric',
                'min:0'
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048'
            ],
            'max_vouchers_count' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'max_voucher_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'role_id' => [
                'required',
                'exists:roles,id'
            ]
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'The customer name has already been taken.',
            'email.unique' => 'The email has already been taken.',
            'username.unique' => 'The username has already been taken.',
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }
}
