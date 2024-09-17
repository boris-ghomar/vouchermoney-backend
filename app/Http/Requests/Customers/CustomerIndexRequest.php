<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class CustomerIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sortBy' => 'nullable|string|in:name,balance,max_vouchers_count,max_voucher_amount,created_at,updated_at',
            'sortDir' => 'nullable|string|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'count' => 'nullable|integer|min:10',
            'search' => 'nullable|string|max:255',
        ];
    }
}
