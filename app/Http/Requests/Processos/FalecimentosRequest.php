<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FalecimentosRequest extends ProcessRequest
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
            'certidaoObito' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dataMorte' => 'required|date',
            'causaDaMorte' => 'required',
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'abertoPor' => 'required|exists:pessoas,id',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'certidaoObito' => 'A certidão de óbito é obrigatória',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'dataMorte' => 'A data da morte é obrigatória',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'causaDaMorte' => 'A causa da morte é obrigatória',
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
