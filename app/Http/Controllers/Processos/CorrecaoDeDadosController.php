<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\CorrecaoDadosRequest;
use App\Models\Pessoa;
use App\Models\Processos\CorrecaoDeDados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CorrecaoDeDadosController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = CorrecaoDeDados::query()
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

            $query = CorrecaoDeDados::query()
                ->select(
                    'correcao_de_dados.*',
                    'p.nomeCompleto as pessoaNome',
                    'ab.nomeCompleto as abertoPorNome',
                )
                ->leftJoin('pessoas as p', 'p.id', '=', 'correcao_de_dados.pessoa_id')
                ->leftJoin('pessoas as ab', 'ab.id', '=', 'correcao_de_dados.abertoPor')

                ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                    $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
                })

                ->when(request('nip'), function ($q, $nip) {
                    $q->where('p.nip', 'like', "%$nip%");
                })

                ->when(request('nrProcesso'), function ($q, $nrProcesso) {
                    $q->where('correcao_de_dados.nrProcesso', 'like', "%$nrProcesso%");
                })

                ->when(request('systemId'), function ($q, $systemId) {
                    $q->where('correcao_de_dados.systemId', 'like', "%$systemId%");
                })

                ->when(request('createdAt'), function ($q, $createdAt) {
                    $q->whereDate('correcao_de_dados.created_at', $createdAt);
                });

            $registros = $paging
                ? $query->paginate($pageSize, ['*'], 'page', $page)
                : $query->simplePaginate($pageSize, ['*'], 'page', $page);

            return response()->json(['data' => $registros], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }

    public function newProcess(CorrecaoDadosRequest $request)
    {
        $uploadedFilePath = null;

        try {
            $filename = time().'_'.$request->file('comprovativo')->getClientOriginalName();
            $request->file('comprovativo')->move(public_path('uploads'), $filename);
            $uploadedFilePath = public_path('uploads/'.$filename);

            $data = $request->validated();
            $data['comprovativo'] = $filename;
            $data['pessoa_id'] = $request->input('pessoa_id')[0];

            DB::transaction(function () use ($data) {
                CorrecaoDeDados::create($data);

                if ((int) $data['tipoCorrecao'] === 0) {
                    Pessoa::query()
                        ->whereKey($data['pessoa_id'])
                        ->update(['nomeCompleto' => $data['novoNome']]);
                } else {
                    Pessoa::query()
                        ->whereKey($data['pessoa_id'])
                        ->update(['dataNasc' => $data['dataNasc']]);
                }
            });

            return response()->json(['success' => 'Processo de correção de dados criado com sucesso!'], 201);
        } catch (\Throwable $th) {
            if ($uploadedFilePath !== null) {
                File::delete($uploadedFilePath);
            }

            report($th);

            return response()->json([
                'message' => 'Não foi possível registar a correção de dados. Tente novamente.',
            ], 500);
        }
    }
}
