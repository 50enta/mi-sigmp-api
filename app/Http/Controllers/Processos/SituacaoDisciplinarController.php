<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\situacaoDisciplinarRequest;
use App\Models\SituacaoDisciplinar;
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

        $query = SituacaoDisciplinar::query();

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['situacao_disciplinares' => $registros], 200);
    }

    public function newProcess(situacaoDisciplinarRequest $request){
        $data = $request->validated();
        $situacaoDisciplinar = SituacaoDisciplinar::create($data);
        return response()->json(['situacao_disciplinar' => $situacaoDisciplinar], 201);
    }
   
}
