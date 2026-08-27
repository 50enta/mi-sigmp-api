<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\PromocaoRequest;
use App\Models\CategoriaPolicia;
use App\Models\Processos\Promocao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromocaoController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = Promocao::query()
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

        $query = Promocao::query()
            ->select(
                'promocaos.*',
                'p.nomeCompleto as pessoaNome',
                'ab.nomeCompleto as abertoPorNome',
            )
            ->leftJoin('pessoas as p', 'p.id', '=', 'promocaos.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'promocaos.abertoPor')

            ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
            })

            ->when(request('nip'), function ($q, $nip) {
                $q->where('p.nip', 'like', "%$nip%");
            })

            ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                $q->where('promocaos.nrProcesso', 'like', "%$nrProcesso%");
            })

            ->when(request('systemId'), function ($q, $systemId) {
                $q->where('promocaos.systemId', 'like', "%$systemId%");
            })

            ->when(request('createdAt'), function ($q, $createdAt) {
                $q->whereDate('promocaos.created_at', $createdAt);
            });

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['data' => $registros], 200);
    }

    public function newProcess(PromocaoRequest $request)
    {
        try {
            $data = $request->validated();
            $filename = null;

            if ($request->hasFile('despacho')) {
                $filename = time().'_'.$request->file('despacho')->getClientOriginalName();
                $request->file('despacho')->move(public_path('uploads'), $filename);
            }

            DB::transaction(function () use ($data, $filename): void {
                $personId = $data['pessoa_id'][0];
                $dispatchDate = $data['dataDespacho'];
                $promotion = Promocao::query()->create([
                    ...$data,
                    'pessoa_id' => $personId,
                    'despacho' => $filename,
                ]);

                CategoriaPolicia::query()
                    ->where('pessoa_id', $personId)
                    ->where(function ($query) {
                        $query->whereNull('dataFim')
                            ->orWhere('activo', true);
                    })
                    ->update([
                        'dataFim' => $dispatchDate,
                        'activo' => false,
                    ]);

                CategoriaPolicia::query()->create([
                    'nrProcesso' => $promotion->systemId,
                    'categoria_id' => $data['novaCategoria'],
                    'pessoa_id' => $personId,
                    'nrDespacho' => $data['nrDespacho'],
                    'despacho' => $filename,
                    'dataInicio' => $dispatchDate,
                    'dataDespacho' => $dispatchDate,
                    'obs' => $data['obs'] ?? null,
                ]);
            });

            return response()->json(['success' => 'Processo de promoção registado com sucesso'], 201);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }
}
