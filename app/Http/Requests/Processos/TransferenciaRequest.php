<?php

namespace App\Http\Requests\Processos;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferenciaRequest extends ProcessRequest
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
            'pessoa_id' => 'required|array',
            'pessoa_id.*' => 'required|exists:pessoas,id',
            'regime' => 'required',
            'abertoPor' => 'required|exists:pessoas,id',
            'nrDespacho' => 'required|string|max:255',
            'dataDespacho' => 'required|date',
            'nrDespachoPermutador' => 'required_if:regime,permuta|nullable|string|max:255',
            'dataDespachoPermutador' => 'required_if:regime,permuta|nullable|date',
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
            'nrDespacho.required' => 'O número do despacho é obrigatório.',
            'dataDespacho.required' => 'A data do despacho é obrigatória.',
            'nrDespachoPermutador.required_if' => 'O número do despacho do membro da permuta é obrigatório.',
            'dataDespachoPermutador.required_if' => 'A data do despacho do membro da permuta é obrigatória.',
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
