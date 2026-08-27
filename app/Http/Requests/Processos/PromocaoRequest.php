<?php

namespace App\Http\Requests\Processos;

use App\Http\Requests\ProcessRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PromocaoRequest extends ProcessRequest
{
    public const MODALIDADE_EXCEPCIONAL = 'A título excepcional';

    private const MODALIDADES = [
        'Habilitação com curso adequado',
        'Antiguidade',
        'Escolha',
        'Por orgânica',
        self::MODALIDADE_EXCEPCIONAL,
    ];

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
            'categoriaActual' => 'required|string',
            'novaCategoria' => 'required|string|different:categoriaActual', // ⭐
            'modalidade' => ['required', 'string', Rule::in(self::MODALIDADES)],
            'obs' => ['nullable', 'string', Rule::requiredIf(fn () => $this->input('modalidade') === self::MODALIDADE_EXCEPCIONAL)],
            'abertoPor' => 'required|exists:pessoas,id',
            'nrDespacho' => 'required',
            'dataDespacho' => 'required|date',
            'despacho' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'novaCategoria.different' => 'A nova categoria deve ser diferente da categoria actual.',
            'modalidade.required' => 'A modalidade da promoção é obrigatória.',
            'modalidade.in' => 'Seleccione uma modalidade de promoção válida.',
            'obs.required' => 'As observações são obrigatórias para uma promoção a título excepcional.',
            'nrDespacho' => 'O número do despacho é obrigatório',
            'dataDespacho' => 'A data de despacho é obrigatória',
            'pessoa_id' => 'A pessoa é obrigatória.',
            'proposta' => 'O proposta é obrigatório.',
            'abertoPor' => 'A pessoa que abriu o processo é obrigatória.',
            'data' => 'A data é obrigatória e deve ser uma data válida.',
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
