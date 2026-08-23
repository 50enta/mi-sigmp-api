<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SituacaoController;
use App\Http\Requests\Processos\FalecimentosRequest;
use App\Models\Processos\Falecimentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FalecimentosController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $query = Falecimentos::query()
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

        $query = Falecimentos::query()
            ->select(
                'falecimentos.*',
                'p.nomeCompleto as pessoaNome',
                'ab.nomeCompleto as abertoPorNome',
            )
            ->leftJoin('pessoas as p', 'p.id', '=', 'falecimentos.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'falecimentos.abertoPor')

            ->when(request('nomeAgente'), function ($q, $nomeAgente) {
                $q->where('p.nomeCompleto', 'like', "%$nomeAgente%");
            })

            ->when(request('nip'), function ($q, $nip) {
                $q->where('p.nip', 'like', "%$nip%");
            })

            ->when(request('systemId'), function ($q, $systemId) {
                $q->where('falecimentos.systemId', 'like', "%$systemId%");
            })

            ->when(request('createdAt'), function ($q, $createdAt) {
                $q->whereDate('falecimentos.created_at', $createdAt);
            });

        $registros = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['data' => $registros], 200);
    }

    public function newProcess(FalecimentosRequest $request)
    {
        $uploadedFilePath = null;

        try {
            $filename = Str::uuid().'_'.$request->file('certidaoObito')->getClientOriginalName();
            $request->file('certidaoObito')->move(public_path('uploads'), $filename);
            $uploadedFilePath = public_path('uploads/'.$filename);

            $data = $request->validated();
            $data['certidaoObito'] = $filename;
            $data['pessoa_id'] = $request->input('pessoa_id')[0];

            DB::transaction(function () use ($data) {
                Falecimentos::create($data);

                app(SituacaoController::class)->addDefaultStatus($data['pessoa_id'], 'Morto');
            });

            return response()->json(['success' => 'Falecimento registado com sucesso'], 201);
        } catch (\Throwable $th) {
            if ($uploadedFilePath !== null) {
                File::delete($uploadedFilePath);
            }

            report($th);

            return response()->json([
                'message' => 'Não foi possível registar o falecimento. Tente novamente.',
            ], 500);
        }
    }
}
