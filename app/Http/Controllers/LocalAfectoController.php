<?php

namespace App\Http\Controllers;

use App\Http\Requests\localAfetosRequest;
use App\Models\LocalAfecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocalAfectoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = LocalAfecto::query();

        $afetos = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['local_afetos' => $afetos], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(localAfetosRequest $request)
    {

        try {
            $afeto = LocalAfecto::create($request->validated());

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(localAfetosRequest $request, $id)
    {
        $afeto = LocalAfecto::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'local_id' => 'required|uuid|exists:locais,id',
            'pessoa_id' => 'required|uuid|exists:pessoas,id',
            'despacho' => 'nullable|string',
            'dataInicio' => 'nullable|date',
            'dataFim' => 'nullable|date',
            'isTransferencia' => 'required|boolean',
            'transferidor_id' => 'nullable|uuid',
            'aprovador' => 'nullable|string',
            'aprovado' => 'nullable|integer',
            'local_origem' => 'nullable|string',
            'regime' => 'nullable|in:Pedido,Permuta',
            'permutador' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar afectação', 'errors' => $validation->errors()], 409);
        }

        $afeto->update($validation->validated());

        return response()->json(['message' => 'Afectação actualizada com sucesso!', 'local_afeto' => $afeto], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $afeto = LocalAfecto::findOrFail($id);
        $afeto->delete();

        return response()->json(['message' => 'Afectação eliminada com sucesso!'], 200);
    }
}
