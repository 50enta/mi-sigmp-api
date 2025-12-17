<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEspecialidadeCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'pessoa_id' => 'required|uuid|exists:pessoas,id',

            // === Grupo Especialidade ===
            'especialidade' => 'required|array',
            'especialidade.especialidade' => 'required|string|max:255',
            'especialidade.dataNomeacaoEsp' => 'required|date',
            // 'especialidade.despachoEsp' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            'especialidade.observacoesEsp' => 'nullable|string|max:1000',

            // === Grupo Categoria ===
            'categoria' => 'required|array',
            'categoria.categoria' => 'required|string|max:255',
            'categoria.dataNomeacaoCat' => 'required|date',
            // 'categoria.despachoCat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'categoria.observacoesCat' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'pessoa_id.required' => 'O ID da pessoa é obrigatório.',
            'pessoa_id.exists' => 'A pessoa selecionada não existe.',

            'especialidade.required' => 'O bloco de especialidade é obrigatório.',
            'especialidade.especialidade.required' => 'O campo especialidade é obrigatório.',
            'especialidade.dataNomeacaoEsp.required' => 'A data de nomeação da especialidade é obrigatória.',
            'especialidade.dataNomeacaoEsp.date' => 'A data de nomeação da especialidade deve ser válida.',
            'especialidade.despachoEsp.required' => 'O despacho da especialidade é obrigatório.',
            'especialidade.despachoEsp.file' => 'O despacho deve ser um arquivo.',
            'especialidade.despachoEsp.mimes' => 'O despacho deve ser PDF, JPG, JPEG ou PNG.',
            'especialidade.despachoEsp.max' => 'O despacho não pode exceder 10MB.',

            'categoria.required' => 'O bloco de categoria é obrigatório.',
            'categoria.categoria.required' => 'O campo categoria é obrigatório.',
            'categoria.dataNomeacaoCat.required' => 'A data de nomeação da categoria é obrigatória.',
            'categoria.dataNomeacaoCat.date' => 'A data de nomeação da categoria deve ser válida.',
            'categoria.despachoCat.required' => 'O despacho da categoria é obrigatório.',
            'categoria.despachoCat.mimes' => 'O despacho da categoria deve ser PDF, JPG, JPEG ou PNG.',
            'categoria.despachoCat.max' => 'O despacho da categoria não pode exceder 10MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'pessoa_id' => 'pessoa',

            'especialidade.especialidade' => 'especialidade',
            'especialidade.dataNomeacaoEsp' => 'data de nomeação (especialidade)',
            'especialidade.despachoEsp' => 'despacho (especialidade)',
            'especialidade.observacoesEsp' => 'observações (especialidade)',

            'categoria.categoria' => 'categoria',
            'categoria.dataNomeacaoCat' => 'data de nomeação (categoria)',
            'categoria.despachoCat' => 'despacho (categoria)',
            'categoria.observacoesCat' => 'observações (categoria)',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
