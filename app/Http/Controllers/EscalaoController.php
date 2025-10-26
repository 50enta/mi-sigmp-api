<?php

namespace App\Http\Controllers;

use App\Models\Escalao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EscalaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Escalao::query();

        $escalaos = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['escalaos' => $escalaos], 200);
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
            'descricao' => 'required|string',
            'comentarios' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Erro ao criar a escalao',
                'errors' => $validation->errors()
            ], 409);
        }

        $escalaos = Escalao::create($validation->validated());

        return response()->json([
            'message' => 'Escalao criado com sucesso!',
            'escalao' => $escalaos
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $escalaos = Escalao::findOrFail($id);
        return response()->json($escalaos, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Escalao $escalao)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $escalaos = Escalao::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'descricao' => 'required|string',
            'comentarios' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar o escalao', 'errors' => $validation->errors()], 409);
        }

        $escalaos->update($validation->validated());

        return response()->json(['message' => 'Escalao actualizada com sucesso!', 'escalao' => $escalaos], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $escalao = Escalao::findOrFail($id);
        $escalao->delete();

        return response()->json(['message' => 'Escalao eliminado com sucesso!'], 200);
    }

}