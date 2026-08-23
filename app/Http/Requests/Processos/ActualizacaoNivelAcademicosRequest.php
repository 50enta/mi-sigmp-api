<?php

namespace App\Http\Requests\Processos;

use App\Http\Requests\ProcessRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizacaoNivelAcademicosRequest extends ProcessRequest
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
            'certificado' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'instituicao' => 'required',
            'abertoPor' => 'required|exists:pessoas,id',
            'curso' => 'required',
            'nivel' => 'required',
            'dataDeConclusao' => 'required|date',
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
            'nivel' => 'O nível é obrigatório',
            'dataDeConclusao.required' => 'A data da conclusão é obrigatória.',
            'dataDeConclusao.date' => 'A data da conclusão deve ser uma data válida.',
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
            'errors' => $validator->errors(),
        ], 422));
    }
}
