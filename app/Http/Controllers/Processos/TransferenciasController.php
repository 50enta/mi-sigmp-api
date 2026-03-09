<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\ReafetacaoRequest;
use App\Models\LocalAfecto;
use App\Models\Processos\Reafetacao;
use Illuminate\Http\Request;

class TransferenciasController extends Controller
{

    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = Reafetacao::query()
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = "aberto" THEN 1 ELSE 0 END) as aberto,
                    SUM(CASE WHEN estado = "fechado" THEN 1 ELSE 0 END) as fechado
                ')
                ->whereYear('created_at', $year);

            $stats = $query->first();

            return response()->json(['data' => $stats], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $pageSize = $request->input('pageSize', 10);
            $page = $request->input('page', 1);
            $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

            $query = Reafetacao::query()
                ->select(
                    'reafetacaos.*',
                    'p.nomeCompleto as pessoaNome',
                    'ab.nomeCompleto as abertoPorNome',
                    'o.nome as origemNome',
                    'd.nome as destinoNome',
                )
                ->leftJoin('pessoas as p', 'p.id', '=', 'reafetacaos.pessoa_id')
                ->leftJoin('pessoas as ab', 'ab.id', '=', 'reafetacaos.abertoPor')
                ->leftJoin('locals as o', 'o.id', '=', 'reafetacaos.origem')
                ->leftJoin('locals as d', 'd.id', '=', 'reafetacaos.destino')

                ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                    $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
                })

                ->when(request('nip'), function ($q, $nip) {
                    $q->where('p.nip', 'like', "%$nip%");
                })

                ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                    $q->where('reafetacaos.nrProcesso', 'like', "%$nrProcesso%");
                })

                ->when(request('createdAt'), function ($q, $createdAt) {
                    $q->whereDate('reafetacaos.created_at', $createdAt);
                });

            $registros = $paging
                ? $query->paginate($pageSize, ['*'], 'page', $page)
                : $query->simplePaginate($pageSize, ['*'], 'page', $page);

            return response()->json(['data' => $registros], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }

    public function newProcess(ReafetacaoRequest $request)
    {
        try {
            $filename = time() . '_' . $request->file('despacho')->getClientOriginalName();
            $request->file('despacho')->move(public_path('uploads'), $filename);

            $data = $request->except(['cargo']);
            $data['despacho'] = $filename;

            $reaf = Reafetacao::create($data);

            $newLocalData = [
                "reafetacao_id" => $reaf->nrProcesso,
                "cargo" => $request->input('cargo'),
                "local_id" => $request->input('destino'),
                "pessoa_id" => $request->input('pessoa_id'),
                "despacho" => $filename,
                "isReafetacao" => true,
                "dataInicio" => $request->input('data')
            ];

            LocalAfecto::create($newLocalData);

            return response()->json(['success' => 'Reacfectação criada com sucesso!'], 201);
        } catch (\Throwable $th) {
            dd($th);
            return response(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }
}
