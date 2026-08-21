<?php

namespace App\Http\Requests\Processos;

use App\Http\Requests\ProcessRequest;

class FeriasRequest extends ProcessRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pessoa_id' => ['required', 'array', 'size:1'],
            'pessoa_id.0' => ['required', 'exists:pessoas,id'],
            'dataInicio' => ['required', 'date_format:Y-m-d'],
            'dataFim' => ['required', 'date_format:Y-m-d', 'after_or_equal:dataInicio'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'abertoPor' => ['required', 'exists:pessoas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'pessoa_id.required' => 'O agente é obrigatório.',
            'pessoa_id.size' => 'Seleccione apenas um agente.',
            'dataInicio.required' => 'A data de início é obrigatória.',
            'dataFim.required' => 'A data de fim é obrigatória.',
            'dataFim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
        ];
    }
}
