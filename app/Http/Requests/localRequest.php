<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LocalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajuste conforme sua lógica de autorização
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string',
            'parent_id' => 'required|uuid|exists:locals,id',
            'code' => 'required',
            'description' => 'nullable|string'
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'parent_id.required' => 'Id do pai é obrigatório',
            'code.required' => 'O código é obrigatório',
        ];
    }

    /**
     * Sobrescreve o comportamento padrão de erro (opcional)
     * O Laravel já retorna JSON com status 422 por padrão em APIs.
     * Só use se precisar de formato personalizado.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erro de validação',
            'errors' => $validator->errors()
        ], 422));
    }
}
