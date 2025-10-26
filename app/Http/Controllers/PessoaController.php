<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PessoaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Pessoa::query();

        $pessoas = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['pessoas' => $pessoas], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'aprovado' => 'required|integer',
            'nip' => 'required|string|unique:pessoas,nip',
            'isGerivel' => 'required|boolean',
            'nomeCompleto' => 'required|string|max:255',
            'nomeMae' => 'nullable|string|max:255',
            'nomePai' => 'nullable|string|max:255',
            'dataNasc' => 'nullable|date',
            'nuit' => 'nullable|string|max:20',
            'estadoCivil' => 'nullable|in:solteiro,casado,divorciado,viuvo',
            'grupoSangue' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|in:Maputo Cidade,Maputo Provincia,Gaza,Inhambane,Sofala,Manica,Zambezia,Nampula,Tete,Cabo Delgado,Niassa',
            'residencia' => 'nullable|string|max:255',
            'genero' => 'nullable|in:Masculino,Feminino,Outro',
            'BI' => 'nullable|string|max:50',
            'altura' => 'nullable|numeric|min:0|max:3',
            'linguas' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar pessoa', 'errors' => $validation->errors()], 409);
        }

        $pessoa = Pessoa::create($validation->validated());

        return response()->json(['message' => 'Pessoa criada com sucesso!', 'pessoa' => $pessoa], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pessoa = Pessoa::findOrFail($id);
        return response()->json($pessoa, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pessoa $pessoa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'aprovado' => 'required|integer',
            'nip' => 'required|string|unique:pessoas,nip',
            'isGerivel' => 'required|boolean',
            'nomeCompleto' => 'required|string|max:255',
            'nomeMae' => 'nullable|string|max:255',
            'nomePai' => 'nullable|string|max:255',
            'dataNasc' => 'nullable|date',
            'nuit' => 'nullable|string|max:20',
            'estadoCivil' => 'nullable|in:solteiro,casado,divorciado,viuvo',
            'grupoSangue' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|in:Maputo Cidade,Maputo Provincia,Gaza,Inhambane,Sofala,Manica,Zambezia,Nampula,Tete,Cabo Delgado,Niassa',
            'residencia' => 'nullable|string|max:255',
            'genero' => 'nullable|in:Masculino,Feminino,Outro',
            'BI' => 'nullable|string|max:50',
            'altura' => 'nullable|numeric|min:0|max:3',
            'linguas' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar pessoa', 'errors' => $validation->errors()], 409);
        }

        $pessoa->update($validation->validated());

        return response()->json(['message' => 'Pessoa actualizada com sucesso!', 'pessoa' => $pessoa], 200);
    }
    /**
     * Remove the specified resource from storage.
     */    public function destroy($id)
    {
        $pessoa = Pessoa::findOrFail($id);
        $pessoa->delete();

        return response()->json(['message' => 'Pessoa eliminada com sucesso!'], 200);
    }
}
