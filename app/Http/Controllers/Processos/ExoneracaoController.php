<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SituacaoController;
use App\Http\Requests\Processos\ExoneracaoRequest;
use App\Models\Processos\Exoneracao;
use Illuminate\Http\Request;

class ExoneracaoController extends Controller
{

    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = Exoneracao::query()
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

        $query = Exoneracao::query()
            ->select(
                'exoneracaos.*',
                'p.nomeCompleto as pessoaNome',
                'ab.nomeCompleto as abertoPorNome',
            )
            ->leftJoin('pessoas as p', 'p.id', '=', 'exoneracaos.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'exoneracaos.abertoPor')

            ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
            })

            ->when(request('nip'), function ($q, $nip) {
                $q->where('p.nip', 'like', "%$nip%");
            })

            ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                $q->where('exoneracaos.nrProcesso', 'like', "%$nrProcesso%");
            })

            ->when(request('createdAt'), function ($q, $createdAt) {
                $q->whereDate('exoneracaos.created_at', $createdAt);
            });



        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['data' => $registros], 200);
    }

    public function newProcess(ExoneracaoRequest $request)
    {
        try {
            $filename = time() . '_' . $request->file('despacho')->getClientOriginalName();
            $request->file('despacho')->move(public_path('uploads'), $filename);
            
            $data = $request->all();
            $data['despacho'] = $filename;
            $data['estado'] = 'fechado';

            Exoneracao::create($data);

            $sitCon = new SituacaoController();
            $sitCon->addDefaultStatus($data['pessoa_id'], 'Exonerado');

            return response()->json(['success' => 'Processo criado com sucesso'], 201);
        } catch (\Throwable $th) {
            return response(['error' => 'Ocorreu um erro inesperado'.$th], 500);
        }
    }
}
