<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PessoaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $info = $this->input('info', []);
        if (! is_array($info)) {
            return;
        }

        if (array_key_exists('email', $info) && is_string($info['email'])) {
            $info['email'] = trim($info['email']) ?: null;
        }

        if (isset($info['telefones']) && is_array($info['telefones'])) {
            $info['telefones'] = collect($info['telefones'])
                ->filter(fn ($telefone) => is_string($telefone))
                ->map(fn ($telefone) => trim($telefone))
                ->filter()
                ->values()
                ->all();
        }

        $this->merge(['info' => $info]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'info.nomeCompleto' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $names = preg_split('/\s+/', trim($value));

                    if (count($names) < 2) {
                        $fail('O nome completo deve conter pelo menos dois nomes.');
                    }
                },
            ],
            'info.nomeMae' => 'nullable|string|max:255',
            'info.nomePai' => 'nullable|string|max:255',
            'info.dataNasc' => 'required|date|before_or_equal:'.now()->subYears(18)->toDateString(),
            'info.nuit' => 'required|string|max:20',
            'info.estadoCivil' => 'nullable|in:Solteiro,Casado,Divorciado,Viuvo',
            'info.grupoSangue' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'info.distrito' => 'required|string|max:255',
            'info.provincia' => 'required|in:Maputo Cidade,Maputo Provincia,Gaza,Inhambane,Sofala,Manica,Zambezia,Nampula,Tete,Cabo Delgado,Niassa',
            'info.residencia' => 'nullable|string|max:255',
            'info.genero' => 'required|in:Masculino,Feminino',
            'info.BI' => ['required', 'string', 'max:50', 'regex:/^\d+[A-Za-z]$/'],
            'info.altura' => 'nullable|numeric|min:0|max:3',
            'info.linguas' => 'nullable|string',
            'info.email' => ['nullable', 'email', 'max:255', Rule::unique('pessoas', 'email')],
            'info.telefones' => 'required|array|min:1',
            'info.telefones.0' => 'required|string|max:50',
            'info.telefones.*' => ['string', 'max:50', 'distinct', Rule::unique('pessoa_telefones', 'numero')],
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'info.nomeCompleto.required' => 'O nome completo deve ter pelo menos 2 nomes.',
            'info.nomeCompleto.max' => 'O nome completo não pode exceder 255 caracteres.',

            'info.dataNasc.required' => 'A data de nascimento é obrigatória.',
            'info.dataNasc.date' => 'A data de nascimento deve ser uma data válida.',
            'info.dataNasc.before_or_equal' => 'O agente deve ter pelo menos 18 anos.',

            'info.nuit.required' => 'O NUIT é obrigatório.',
            'info.nuit.max' => 'O NUIT não pode exceder 20 caracteres.',

            'info.estadoCivil.in' => 'O estado civil deve ser Solteiro, Casado, Divorciado ou Viúvo.',

            'info.grupoSangue.in' => 'O grupo sanguíneo informado é inválido.',

            'info.distrito.required' => 'O distrito é obrigatório.',

            'info.provincia.required' => 'A província é obrigatória.',
            'provincia.in' => 'A província informada é inválida.',

            'info.genero.required' => 'O género é obrigatório.',
            'info.genero.in' => 'O género deve ser Masculino ou Feminino.',

            'info.BI.required' => 'O número do BI é obrigatório.',
            'info.BI.max' => 'O número do BI não pode exceder 50 caracteres.',
            'info.BI.regex' => 'O BI deve conter apenas dígitos e terminar com uma letra.',

            'info.altura.numeric' => 'A altura deve ser um número.',
            'info.altura.min' => 'A altura não pode ser inferior a 0.',
            'info.altura.max' => 'A altura não pode ser superior a 3 metros.',

            'info.email.unique' => 'Este email já está registado.',
            'info.telefones.*.distinct' => 'O mesmo número de contacto não pode ser repetido.',
            'info.telefones.*.unique' => 'Este número de contacto já está registado.',
        ];
    }

    /**
     * Resposta personalizada para erro de validação (API)
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
