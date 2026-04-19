<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PensoesRquest extends FormRequest
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
            'estadoActual' => 'required|string',
            'novoEstado' => 'required|string|different:estadoActual',
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
            'novoEstado.different' => 'A novo estado deve ser diferente da actual.',
            'nrDespacho' => 'O número do despacho é obrigatório',
            'dataDespacho' => 'A data de despacho é obrigatória',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'motivo' => 'O motivo do processo pensoe é obrigatório.',
            'nrProcesso' => 'O número do processo é obrigatório',
            'novoEstado' => 'O novo estado é obrigatório.',
            'estadoActual' => 'O estado actual é obrigatório.',
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
