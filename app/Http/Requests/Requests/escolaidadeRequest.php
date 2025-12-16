<?php

namespace App\Http\Requests\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class escolaidadeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            "validation" => true,
            'warning' => $validator->errors()
        ])->toResponse($this->container->make(Request::class)));
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        //TODO
        //Validate formação pessoal

        return [
            'instituicao' => 'required|string',
            'nivel' => 'nullable|in:elementar,basico,medio,licenciatura,mestrado,phd',
            'curso' => 'nullable|string',
            'dataInicio' => 'nullable|date',
            'certificado' => 'required|file|mimes:pdf|max:2048',
            'dataFim' => 'nullable|date',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
        ];
    }
}
