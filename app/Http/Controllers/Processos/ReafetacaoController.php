<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\ReafetacaoRequest;
use App\Models\Processos\Reafetacao;
use App\Services\ProcessAffiliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReafetacaoController extends Controller
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

                ->when(request('systemId'), function ($q, $systemId) {
                    $q->where('reafetacaos.systemId', 'like', "%$systemId%");
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

    public function newProcess(ReafetacaoRequest $request, ProcessAffiliationService $affiliations)
    {
        $filename = null;
        if ($request->hasFile('despacho')) {
            $filename = time().'_'.$request->file('despacho')->getClientOriginalName();
            $request->file('despacho')->move(public_path('uploads'), $filename);
        }

        DB::transaction(function () use ($request, $affiliations, $filename) {
            $personId = $request->input('pessoa_id')[0];
            $data = $request->except(['cargo', 'pessoa_id']);
            $data['despacho'] = $filename;
            $data['pessoa_id'] = $personId;

            $reafectation = Reafetacao::query()->create($data);

            $affiliations->move(
                $personId,
                $request->input('origem'),
                $request->input('destino'),
                $request->input('dataDespacho'),
                [
                    'reafetacao_id' => $reafectation->systemId,
                    'cargo' => $request->input('cargo'),
                    'despacho' => $filename,
                    'nrDespacho' => $request->input('nrDespacho'),
                    'dataDespacho' => $request->input('dataDespacho'),
                    'isReafetacao' => true,
                ],
            );
        });

        return response()->json(['success' => 'Reafectação criada com sucesso!'], 201);
    }
}
