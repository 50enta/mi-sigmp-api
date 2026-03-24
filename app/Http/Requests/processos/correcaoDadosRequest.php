<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CorrecaoDadosRequest extends FormRequest
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
            'comprovativo' => 'required',
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'tipoCorrecao' => 'required|string',
            'motivoEobs' => 'required|string',
            'abertoPor' => 'required|exists:pessoas,id',
            'dataNasc' => 'required_if:tipoCorrecao,1',
            'novoNome' => 'required_if:tipoCorrecao,0',
            'nrProcesso' => 'required|string',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'comprovativo' => 'O comprovativo é obrigatório',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'motivoEobs' => 'O motivo/observação é obrigatório.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'data' => 'A data é obrigatória e deve ser uma data válida.',
            'dataNasc' => 'A nova data de nascimento é obrigatória',
            'novoNome' => 'O novo nome é obrigatório',
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
