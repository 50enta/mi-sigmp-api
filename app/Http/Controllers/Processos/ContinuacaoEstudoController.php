<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\EstudosRequest;
use App\Models\Processos\ContinuacaoEstudo;
use Illuminate\Http\Request;

class ContinuacaoEstudoController extends Controller
{

    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = ContinuacaoEstudo::query()
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = "aberto" THEN 1 ELSE 0 END) as aberto,
                    SUM(CASE WHEN estado = "fechado" THEN 1 ELSE 0 END) as fechado
                ')
                ->whereYear('created_at', $year);

            $stats = $query->first();

            return response()->json(['data' => $stats], 200);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $pageSize = $request->input('pageSize', 10);
            $page = $request->input('page', 1);
            $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

            $query = ContinuacaoEstudo::query()
                ->select(
                    'continuacao_estudos.*',
                    'p.nomeCompleto as pessoaNome',
                    'ab.nomeCompleto as abertoPorNome',
                )
                ->leftJoin('pessoas as p', 'p.id', '=', 'continuacao_estudos.pessoa_id')
                ->leftJoin('pessoas as ab', 'ab.id', '=', 'continuacao_estudos.abertoPor')

                ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                    $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
                })

                ->when(request('nip'), function ($q, $nip) {
                    $q->where('p.nip', 'like', "%$nip%");
                })

                ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                    $q->where('continuacao_estudos.nrProcesso', 'like', "%$nrProcesso%");
                })

                ->when(request('createdAt'), function ($q, $createdAt) {
                    $q->whereDate('continuacao_estudos.created_at', $createdAt);
                });

            $registros = $paging
                ? $query->paginate($pageSize, ['*'], 'page', $page)
                : $query->simplePaginate($pageSize, ['*'], 'page', $page);

            return response()->json(['data' => $registros], 200);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }

    public function newProcess(EstudosRequest $request)
    {
        try {
            $filename = time() . '_' . $request->file('despacho')->getClientOriginalName();
            $request->file('despacho')->move(public_path('uploads'), $filename);

            $data = $request->all();
            $data['despacho'] = $filename;

            ContinuacaoEstudo::create($data);

            return response()->json(['success' => 'Processo de continuacao com estudos criado com sucesso!'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ocorreu um erro inesperado'.$th], 500);
        }
    }
}
