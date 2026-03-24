<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizacaoNivelAcademicosRequest extends FormRequest
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
            'certificado' => 'required',
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'instituicao' => 'required',
            'abertoPor' => 'required|exists:pessoas,id',
            'curso' => 'required',
            'nivel' => 'required',
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
            'curso' => 'O curso é obrigatório',
            'nível' => 'O nível é obrigatório',
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
