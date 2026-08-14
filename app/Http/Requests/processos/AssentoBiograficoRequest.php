<?php

namespace App\Http\Requests\Processos;

use App\Http\Requests\ProcessRequest;

class AssentoBiograficoRequest extends ProcessRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nrProcesso' => ['required', 'string', 'max:100'],
            'pessoa_id' => ['required', 'array', 'size:1'],
            'pessoa_id.0' => ['required', 'exists:pessoas,id'],
            'tipoRegisto' => ['required', 'string', 'max:100'],
            'dataRegisto' => ['required', 'date'],
            'descricao' => ['required', 'string'],
            'documento' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'abertoPor' => ['required', 'exists:pessoas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrProcesso.required' => 'O número do processo é obrigatório.',
            'pessoa_id.required' => 'Seleccione um agente.',
            'tipoRegisto.required' => 'O tipo de registo é obrigatório.',
            'dataRegisto.required' => 'A data do registo é obrigatória.',
            'descricao.required' => 'A descrição é obrigatória.',
        ];
    }
}
