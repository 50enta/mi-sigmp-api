<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferenciaRequest extends FormRequest
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
            'origem' => 'required',
            'destino' => 'required',
            'pessoa_id' => 'required|exists:pessoas,id',
            'regime' => 'required',
            'abertoPor' => 'required|exists:pessoas,id',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'regime' => 'O regime é obrigatório',
            'origem' => 'A origem é obrigatória.',
            'destino' => 'O destino é obrigatório.',
            'pessoa_id' => 'O agente é obrigatório.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'nrProcesso' => 'required|string',
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
