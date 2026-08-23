<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocalRequest;
use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if ($request->boolean('tree')) {
                return $this->treeLevel($request);
            }

            $locais = Local::all();

            return response()->json(['locais' => $locais], 200);
        } catch (\Throwable $th) {
            return response(['error' => 'Error inesperado'], 500);
        }
    }

    /**
     * Return only the levels needed by a lazily-loaded tree.
     *
     * The initial request returns every root and its direct children. Requests
     * containing parent_id return only that parent's direct children.
     */
    private function treeLevel(Request $request)
    {
        $parentId = $request->query('parent_id');
        $nodeId = $request->query('node_id');

        if ($nodeId !== null) {
            $local = Local::query()->find($nodeId);

            if (!$local) {
                return response()->json(['message' => 'Unidade não encontrada.'], 404);
            }

            return response()->json(['locais' => [$local]], 200);
        }

        if ($parentId !== null && !Local::whereKey($parentId)->exists()) {
            return response()->json(['message' => 'Unidade pai não encontrada.'], 404);
        }

        $query = Local::query()->orderBy('nome');

        if ($parentId !== null) {
            $locais = $query->where('parent_id', $parentId)->get();
        } else {
            $rootIds = Local::query()->whereNull('parent_id')->pluck('id');

            $locais = $query
                ->where(function ($builder) use ($rootIds) {
                    $builder->whereNull('parent_id')
                        ->orWhereIn('parent_id', $rootIds);
                })
                ->get();
        }

        return response()->json(['locais' => $locais], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocalRequest $local)
    {
        try {
            $data = $local->input();
            DB::transaction(fn () => Local::create($data));

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

            DB::transaction(fn () => $local->update($data));

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
            DB::transaction(fn () => $local->delete());

            return response()->json(['success' => 'Local eliminado com sucesso!'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erro ao eliminar local']);
        }
    }
}
