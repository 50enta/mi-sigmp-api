<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Models\SituacaoDisciplinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class SituacaoDisciplinarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = SituacaoDisciplinar::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['situacao_disciplinares' => $registros], 200);
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
            'nrProcesso' => 'required|string|unique:situacao_disciplinares,nrProcesso',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'local' => 'nullable|string|max:255',
            'proposta' => 'nullable|string',
            'abertoPor' => 'nullable|string|max:255',
            'dataAbertura' => 'nullable|date',
            'fechadoPor' => 'nullable|string|max:255',
            'dataFecho' => 'nullable|date',
            'obsFecho' => 'nullable|string',
            'aprovado' => 'required|integer',
            'aprovador' => 'nullable|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar situação disciplinar', 'errors' => $validation->errors()], 409);
        }

        $registro = SituacaoDisciplinar::create($validation->validated());

        return response()->json(['message' => 'Situação disciplinar criada com sucesso!', 'situacao_disciplinar' => $registro], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $registro = SituacaoDisciplinar::findOrFail($id);
        return response()->json($registro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SituacaoDisciplinar $situacaoDisciplinar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */    public function update(Request $request, $id)
    {
        $registro = SituacaoDisciplinar::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'nrProcesso' => 'required|string|unique:situacao_disciplinares,nrProcesso,' . $id,
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'local' => 'nullable|string|max:255',
            'proposta' => 'nullable|string',
            'abertoPor' => 'nullable|string|max:255',
            'dataAbertura' => 'nullable|date',
            'fechadoPor' => 'nullable|string|max:255',
            'dataFecho' => 'nullable|date',
            'obsFecho' => 'nullable|string',
            'aprovado' => 'required|integer',
            'aprovador' => 'nullable|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar situação disciplinar', 'errors' => $validation->errors()], 409);
        }

        $registro->update($validation->validated());

        return response()->json(['message' => 'Situação disciplinar actualizada com sucesso!', 'situacao_disciplinar' => $registro], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $registro = SituacaoDisciplinar::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Situação disciplinar eliminada com sucesso!'], 200);
    }
}
