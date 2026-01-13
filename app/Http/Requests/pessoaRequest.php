<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class pessoaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            "validation" => true,
            'warning' => $validator->errors()
        ])->toResponse($this->container->make(Request::class)));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activo' => 'required|boolean',
            'nip' => 'required|string|unique:pessoas,nip',
            'nomeCompleto' => 'required|string|max:255',
            'nomeMae' => 'nullable|string|max:255',
            'nomePai' => 'nullable|string|max:255',
            'dataNasc' => 'required|date',
            'nuit' => 'required|string|max:20',
            'estadoCivil' => 'nullable|in:Solteiro,Casado,Divorciado,Viuvo',
            'grupoSangue' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'distrito' => 'required|string|max:255',
            'provincia' => 'required|in:Maputo Cidade,Maputo Provincia,Gaza,Inhambane,Sofala,Manica,Zambezia,Nampula,Tete,Cabo Delgado,Niassa',
            'residencia' => 'nullable|string|max:255',
            'genero' => 'required|in:Masculino,Feminino',
            'BI' => 'required|string|max:50',
            'altura' => 'nullable|numeric|min:0|max:3',
            'linguas' => 'nullable|string',
        ];
    }
}
