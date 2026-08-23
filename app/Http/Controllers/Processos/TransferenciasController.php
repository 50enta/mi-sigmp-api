<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\TransferenciaRequest;
use App\Models\Processos\Transferencias;
use App\Services\ProcessAffiliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferenciasController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = Transferencias::query()
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

            $query = Transferencias::query()
                ->select(
                    'transferencias.*',
                    'p.nomeCompleto as pessoaNome',
                    'ab.nomeCompleto as abertoPorNome',
                    'permutador.nomeCompleto as permutador',
                    'o.nome as origemNome',
                    'd.nome as destinoNome',
                )
                ->leftJoin('pessoas as p', 'p.id', '=', 'transferencias.pessoa_id')
                ->leftJoin('pessoas as ab', 'ab.id', '=', 'transferencias.abertoPor')
                ->leftJoin('pessoas as permutador', 'permutador.id', '=', 'transferencias.permutador')
                ->leftJoin('locals as o', 'o.id', '=', 'transferencias.origem')
                ->leftJoin('locals as d', 'd.id', '=', 'transferencias.destino')

                ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                    $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
                })

                ->when(request('nip'), function ($q, $nip) {
                    $q->where('p.nip', 'like', "%$nip%");
                })

                ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                    $q->where('transferencias.nrProcesso', 'like', "%$nrProcesso%");
                })

                ->when(request('systemId'), function ($q, $systemId) {
                    $q->where('transferencias.systemId', 'like', "%$systemId%");
                })

                ->when(request('createdAt'), function ($q, $createdAt) {
                    $q->whereDate('transferencias.created_at', $createdAt);
                });

            $registros = $paging
                ? $query->paginate($pageSize, ['*'], 'page', $page)
                : $query->simplePaginate($pageSize, ['*'], 'page', $page);

            return response()->json(['data' => $registros], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }

    public function newProcess(TransferenciaRequest $request, ProcessAffiliationService $affiliations)
    {
        $dispatch = null;
        if ($request->hasFile('despacho')) {
            $dispatch = time().'_'.$request->file('despacho')->getClientOriginalName();
            $request->file('despacho')->move(public_path('uploads'), $dispatch);
        }

        $permutatorDispatch = null;
        if ($request->hasFile('permutadorDespacho')) {
            $permutatorDispatch = time().'_'.$request->file('permutadorDespacho')->getClientOriginalName();
            $request->file('permutadorDespacho')->move(public_path('uploads'), $permutatorDispatch);
        }

        DB::transaction(function () use ($request, $affiliations, $dispatch, $permutatorDispatch) {
            $personId = $request->input('pessoa_id')[0];
            $permutatorId = $request->input('permutador.0');
            $data = $request->except(['pessoa_id', 'permutador']);
            $data['pessoa_id'] = $personId;
            $data['permutador'] = $permutatorId;
            $data['despacho'] = $dispatch;
            $data['permutadorDespacho'] = $permutatorDispatch;

            $transfer = Transferencias::query()->create($data);
            $effectiveDate = $request->input('dataDespacho');

            $affiliations->move(
                $personId,
                $request->input('origem'),
                $request->input('destino'),
                $effectiveDate,
                [
                    'transferencia_id' => $transfer->systemId,
                    'despacho' => $dispatch,
                    'nrDespacho' => $request->input('nrDespacho'),
                    'dataDespacho' => $effectiveDate,
                    'isTransferencia' => true,
                ],
            );

            if ($request->input('regime') === 'permuta') {
                $affiliations->move(
                    $permutatorId,
                    $request->input('destino'),
                    $request->input('origem'),
                    $effectiveDate,
                    [
                        'transferencia_id' => $transfer->systemId,
                        'despacho' => $permutatorDispatch,
                        'nrDespacho' => $request->input('nrDespachoPermutador'),
                        'dataDespacho' => $request->input('dataDespachoPermutador'),
                        'isTransferencia' => true,
                    ],
                );
            }
        });

        return response()->json(['success' => 'Transferência criada com sucesso!'], 201);
    }
}
