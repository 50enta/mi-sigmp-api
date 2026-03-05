<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\SituacaoDisciplinarRequest;
use App\Models\Processos\SituacaoDisciplinar as ProcessosSituacaoDisciplinar;
use Illuminate\Http\Request;

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

        $query = ProcessosSituacaoDisciplinar::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['situacao_disciplinares' => $registros], 200);
    }

    public function newProcess(SituacaoDisciplinarRequest $request)
    {
        try {
            $data = $request->validated();
            $situacaoDisciplinar = ProcessosSituacaoDisciplinar::create($data);
            return response()->json(['success' => $situacaoDisciplinar], 201);
        } catch (\Throwable $th) {
            dd($th);
            return response(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }
}
