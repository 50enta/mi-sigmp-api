<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SituacaoController;
use App\Http\Requests\Processos\PensoesRquest;
use App\Models\Processos\Pensoes;
use App\Services\ProcessAgentEligibility;
use Illuminate\Http\Request;

class PensoesController extends Controller
{

    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = Pensoes::query()
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
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Pensoes::query()
            ->select(
                'pensoes.*',
                'p.nomeCompleto as pessoaNome',
                'ab.nomeCompleto as abertoPorNome',
            )
            ->leftJoin('pessoas as p', 'p.id', '=', 'pensoes.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'pensoes.abertoPor')

            ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
            })

            ->when(request('nip'), function ($q, $nip) {
                $q->where('p.nip', 'like', "%$nip%");
            })

            ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                $q->where('pensoes.nrProcesso', 'like', "%$nrProcesso%");
            })

            ->when(request('systemId'), function ($q, $systemId) {
                $q->where('pensoes.systemId', 'like', "%$systemId%");
            })

            ->when(request('createdAt'), function ($q, $createdAt) {
                $q->whereDate('pensoes.created_at', $createdAt);
            });

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['data' => $registros], 200);
    }

    public function newProcess(PensoesRquest $request)
    {
        foreach ($request->input('pessoa_id') as $pessoaId) {
            app(ProcessAgentEligibility::class)->ensureEligible(
                ProcessAgentEligibility::RESERVA_APOSENTADO_ACTIVO,
                $pessoaId,
            );
        }

        try {
            $data = $request->all();

            if (null !== $request->file('despacho')) {
                $filename = time() . '_' . $request->file('despacho')->getClientOriginalName();
                $request->file('despacho')->move(public_path('uploads'), $filename);
                $data['despacho'] = $filename;
            }

            foreach ($request->input('pessoa_id') as $pessoa_id) {
                $data['pessoa_id'] = $pessoa_id;
                $data['dataDespacho'] = date('Y-m-d', strtotime($request->input('dataDespacho')));

                $pensao = Pensoes::create($data);
                $sitController = new SituacaoController();
                $sitController->addDefaultStatus($pessoa_id, $data['novoEstado'], $data['nrDespacho'], $pensao->systemId);
            }
            
            return response()->json(['success' => 'Processo criado com sucesso'], 201);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }
}
