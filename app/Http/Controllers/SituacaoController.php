<?php

namespace App\Http\Controllers;

use App\Models\Situacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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


    public function addDefaultStatus($id, $estado = 'Activo', $despacho = null, $nrProcesso = null)
    {
        try {
            $data = [
                'id' => (string) Str::uuid(),
                'situacao' => $estado,
                'pessoa_id' => $id,
                'despacho' => $despacho,
                'nrProcesso' => $nrProcesso,
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('situacao_pessoas')->insert($data);
        } catch (\Throwable $th) {
            return response(['error' => 'Error inesperado'], 500);
        }
    }
}
