<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SituacaoDisciplinarRequest extends ProcessRequest
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
            'proposta' => 'required|string',
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'origem' => 'nullable|string',
            'abertoPor' => 'required|exists:pessoas,id',
            'infraccao' => 'required|string',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'proposta' => 'A proposta é obrigatória',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'origem' => 'A origem é obrigatória.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'infraccao' => 'A infraccao cometida é um campo obrigatório',
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
