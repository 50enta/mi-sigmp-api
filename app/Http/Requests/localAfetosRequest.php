<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class localAfetosRequest extends FormRequest
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
            'localEfuncoes.dataFim' => 'nullable|date|after_or_equal:localEfuncoes.dataDespacho',
            'localEfuncoes.nrDespacho' => 'required|string|max:255',
            'localEfuncoes.dataDespacho' => 'required|date',
            'localEfuncoes.despacho' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'localEfuncoes.local_id' => 'required|uuid|exists:locals,id',
            'localEfuncoes.pessoa_id' => 'required|exists:pessoas,id',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'localEfuncoes.pessoa_id.required' => 'A pessoa é obrigatória.',
            'localEfuncoes.local_id.required' => 'O local é obrigatório.',
            'localEfuncoes.nrDespacho.required' => 'O número do despacho é obrigatório.',
            'localEfuncoes.dataDespacho.required' => 'A data do despacho é obrigatória.',
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
