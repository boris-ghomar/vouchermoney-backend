<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

abstract class ApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            "status" => "error",
            "message" => "Validation error",
            "errors" => $validator->errors()
        ], 422));
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            "status" => "error",
            "message" => "Unauthorized",
        ], 401));
    }
}
