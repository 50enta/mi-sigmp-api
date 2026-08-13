<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class StoreFormacaoRequest extends FormRequest
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
            'formacaoAcademica' => 'required|array',
            'formacaoAcademica.nivel' => 'required|string',
            'formacaoAcademica.curso' => 'required|string|max:255',
            'formacaoAcademica.instituicao' => 'required|string|max:255',
            'formacaoAcademica.dataInicio' => 'required|date',
            'formacaoAcademica.dataFim' => 'nullable|date|after_or_equal:formacaoAcademica.dataInicio',
            'formacaoAcademica.pessoa_id' => 'required|uuid|exists:pessoas,id',
            'formacaoAcademica.certificado' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:10240',

            'formacaoPolicia.pessoa_id' => 'nullable|uuid|exists:pessoas,id',
            'formacaoPolicia.dataInicioBasico' => 'required_with:formacaoPolicia.basico|nullable|date',
            'formacaoPolicia.dataConclusaoBasico' => 'nullable|date|after_or_equal:formacaoPolicia.dataInicioBasico',
            'formacaoPolicia.dataInicioMedio' => 'required_with:formacaoPolicia.medio|nullable|date',
            'formacaoPolicia.dataConclusaoMedio' => 'nullable|date|after_or_equal:formacaoPolicia.dataInicioMedio',
            'formacaoPolicia.dataInicioSuperior' => 'required_with:formacaoPolicia.superior|nullable|date',
            'formacaoPolicia.dataConclusaoSuperior' => 'nullable|date|after_or_equal:formacaoPolicia.dataInicioSuperior',

            'formacoesComplementares.pessoa_id' => 'nullable|uuid|exists:pessoas,id',
            'formacoesComplementares.instituicaoComplementar' => 'required_with:formacoesComplementares.cursoComplementar|nullable|string|max:255',
            'formacoesComplementares.anoConclusaoComplementar' => 'required_with:formacoesComplementares.cursoComplementar|nullable|integer|min:1900|max:'.date('Y'),
            'formacoesComplementares.certificadoComplementar' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:10240',
        ];
    }

    /**
     * Mensagens de erro personalizadas
     */
    public function messages(): array
    {
        return [
            'formacaoAcademica.nivel.required' => 'O nível de formação acadêmica é obrigatório.',
            'formacaoAcademica.nivel.in' => 'O nível selecionado não é válido.',
            'formacaoAcademica.curso.required' => 'O nome do curso acadêmico é obrigatório.',
            'formacaoAcademica.instituicao.required' => 'A instituição é obrigatória.',
            'formacaoAcademica.dataInicio.required' => 'A data de início é obrigatória.',
            'formacaoAcademica.dataFim.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início.',
            'formacaoAcademica.certificado.required' => 'O certificado é obrigatório.',
            'formacaoPolicia.curso.required' => 'O curso de formação policial é obrigatório.',
            'pessoa_id.exists' => 'A pessoa selecionada não existe.',
        ];
    }

    /**
     * Nomes de atributos personalizados (para mensagens mais amigáveis)
     */
    public function attributes(): array
    {
        return [
            'formacaoAcademica.nivel' => 'nível acadêmico',
            'formacaoAcademica.curso' => 'curso acadêmico',
            'formacaoAcademica.instituicao' => 'instituição acadêmica',
            'formacaoAcademica.dataInicio' => 'data de início',
            'formacaoAcademica.dataFim' => 'data de fim',
            'formacaoAcademica.certificado' => 'certificado acadêmico',
            'formacaoPolicia.curso' => 'curso policial',
            'pessoa_id' => 'pessoa',
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
