<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EstudosRequest extends FormRequest
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
            'despacho' => 'required',
            'pessoa_id' => 'required|exists:pessoas,id',
            'instituicao' => 'required',
            'data' => 'required|date',
            'abertoPor' => 'required|exists:pessoas,id',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'despacho' => 'O despacho é obrigatório',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'instituicao' => 'A instituição é obrigatória.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'data' => 'A data é obrigatória e deve ser uma data válida.',
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
