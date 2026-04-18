<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\SituacaoDisciplinarRequest;
use App\Models\Processos\SituacaoDisciplinar as ProcessosSituacaoDisciplinar;
use Illuminate\Http\Request;

class SituacaoDisciplinarController extends Controller
{

    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = ProcessosSituacaoDisciplinar::query()
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

        $query = ProcessosSituacaoDisciplinar::query()
            ->select(
                'gestao_disciplinars.*',
                'p.nomeCompleto as pessoaNome',
                'ab.nomeCompleto as abertoPorNome',
                'locals.nome as localNome'
            )
            ->leftJoin('pessoas as p', 'p.id', '=', 'gestao_disciplinars.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'gestao_disciplinars.abertoPor')
            ->leftJoin('locals', 'locals.id', '=', 'gestao_disciplinars.origem')

            ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
            })

            ->when(request('nip'), function ($q, $nip) {
                $q->where('p.nip', 'like', "%$nip%");
            })

            ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                $q->where('gestao_disciplinars.nrProcesso', 'like', "%$nrProcesso%");
            })

            ->when(request('systemId'), function ($q, $systemId) {
                $q->where('gestao_disciplinars.systemId', 'like', "%$systemId%");
            })

            ->when(request('createdAt'), function ($q, $createdAt) {
                $q->whereDate('gestao_disciplinars.created_at', $createdAt);
            });

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['data' => $registros], 200);
    }

    public function newProcess(SituacaoDisciplinarRequest $request)
    {
        try {
            $data = $request->all();

            if (null !== $request->file('anexo')) {
                $filename = time() . '_' . $request->file('anexo')->getClientOriginalName();
                $request->file('anexo')->move(public_path('uploads'), $filename);
                $data['anexo'] = $filename;
            }

            foreach ($request->input('pessoa_id') as $pessoa_id) {
                $data['pessoa_id'] = $pessoa_id;
                
                ProcessosSituacaoDisciplinar::create($data);
            }
            return response()->json(['success' => 'Processo disciplinar criado com sucesso'], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }
}

