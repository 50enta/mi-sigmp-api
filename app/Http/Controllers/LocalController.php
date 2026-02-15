<?php

namespace App\Http\Controllers;

use App\Http\Requests\localRequest;
use App\Models\Local;

class LocalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $locais = Local::all();

            return response()->json(['locais' => $locais], 200);
        } catch (\Throwable $th) {
            return response(['error' => 'Error inesperado'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocalRequest $local)
    {
        try {
            $data = $local->input();
            Local::create($data);

            return response()->json(['success' => 'Local registado com sucesso!']);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erro ao registar local']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $local = Local::findOrFail($id);
        return response()->json($local, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocalRequest $localPayload, $id)
    {
        try {
            $local = Local::findOrFail($id);
            $data = $localPayload->input();

            $local->update($data);

            return response()->json([
                'success' => 'Local atualizado com sucesso!'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erro ao actualizar local']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $local = Local::findOrFail($id);
            $local->delete();

            return response()->json(['success' => 'Local eliminado com sucesso!'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erro ao eliminar local']);
        }
    }
}
