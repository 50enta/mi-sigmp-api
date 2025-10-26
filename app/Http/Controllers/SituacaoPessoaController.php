<?php

namespace App\Http\Controllers;

use App\Models\SituacaoPessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SituacaoPessoaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = SituacaoPessoa::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['situacao_pessoas' => $registros], 200);
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
            'situacao_id' => 'required|uuid|exists:situacoes,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'isMudanca' => 'required|boolean',
            'despacho' => 'nullable|string',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar vínculo de situação', 'errors' => $validation->errors()], 409);
        }

        $registro = SituacaoPessoa::create($validation->validated());

        return response()->json(['message' => 'Vínculo criado com sucesso!', 'situacao_pessoa' => $registro], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = SituacaoPessoa::findOrFail($id);
        return response()->json($registro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SituacaoPessoa $situacaoPessoa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */    
    public function update(Request $request, $id)
    {
        $registro = SituacaoPessoa::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'situacao_id' => 'required|uuid|exists:situacoes,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'isMudanca' => 'required|boolean',
            'despacho' => 'nullable|string',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar vínculo', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Vínculo actualizado com sucesso!', 'situacao_pessoa' => $registro], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = SituacaoPessoa::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Vínculo eliminado com sucesso!'], 200);
    }
}
