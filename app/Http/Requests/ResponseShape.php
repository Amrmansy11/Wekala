<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ResponseShape extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => "The given data was invalid.",
            'errors' => $validator->errors(),
            'status' => 'FAIL'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
        throw new ValidationException($validator, $response);
    }
}
