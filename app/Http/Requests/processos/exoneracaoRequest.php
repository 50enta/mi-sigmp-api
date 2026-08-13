<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExoneracaoRequest extends ProcessRequest
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
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'motivo' => 'required|string',
            'abertoPor' => 'required|exists:pessoas,id',
            'nrProcesso' => 'required|string',
            'nrDespacho' => 'required',
            'dataDespacho' => 'required',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'nrDespacho' => 'O número do despacho é obrigatório',
            'dataDespacho' => 'A data de despacho é obrigatória',
            'despacho' => 'O despacho é obrigatório',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'motivo' => 'O motivo é obrigatório.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'data' => 'A data é obrigatória e deve ser uma data válida.',
            'nrProcesso' => 'O número do processo é obrigatório',
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
