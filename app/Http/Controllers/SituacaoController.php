<?php

namespace App\Http\Controllers;

use App\Models\Situacao;
use Illuminate\Http\Request;

class SituacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Situacao::query();

        $situacoes = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['situacoes' => $situacoes], 200);
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
            'situacao' => 'required|in:suspenso,exonerado,expulso,morto,reservado,aposentado',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar situação', 'errors' => $validation->errors()], 409);
        }

        $situacao = Situacao::create($validation->validated());

        return response()->json(['message' => 'Situação criada com sucesso!', 'situacao' => $situacao], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $situacao = Situacao::findOrFail($id);
        return response()->json($situacao, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Situacao $situacao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $situacao = Situacao::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'situacao' => 'required|in:suspenso,exonerado,expulso,morto,reservado,aposentado',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar situação', 'errors' => $validation->errors()], 409);
        }

        $situacao->update($validation->validated());

        return response()->json(['message' => 'Situação actualizada com sucesso!', 'situacao' => $situacao], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $situacao = Situacao::findOrFail($id);
        $situacao->delete();

        return response()->json(['message' => 'Situação eliminada com sucesso!'], 200);
    }
}
