<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\CategoriaEspecialidadeController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\PromocaoRequest;
use App\Models\CategoriaPolicia;
use App\Models\Processos\Promocao;
use Illuminate\Http\Request;

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
            $data = $request->all();

            if (null !== $request->file('despacho')) {
                $filename = time() . '_' . $request->file('despacho')->getClientOriginalName();
                $request->file('despacho')->move(public_path('uploads'), $filename);
            }

            $data['pessoa_id'] = $request->input('pessoa_id')[0];
            $data['dataDespacho'] = date('Y-m-d', strtotime($request->input('dataDespacho')));

            $promo = Promocao::create($data);

            $new = [
                "nrProcesso" => $promo->nrProcesso,
                "categoria_id" => $promo->novaCategoria,
                "pessoa_id" => $promo->pessoa_id,
                "nrDespacho" => $promo->nrDespacho,
                "despacho" => null !== $request->file('despacho') ? $filename : null,
                "dataInicio" => $promo->dataDespacho,
                "obs" => null !== $request->input('obs') ? $data['obs'] : null,
            ];

            $updateCate = new CategoriaEspecialidadeController();
            
            if ($updateCate->updateCatEsp($new) == 1) {
                return response()->json(['success' => 'Processo de promoção registado com sucesso'], 201);
            } else {
                Promocao::find($promo->systemId)->delete();
                return response()->json(['error' => 'Ocorreu um erro ao registar o processo de promoção'], 500);
            }
            
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['error' => 'Ocorreu um erro inesperado'], 500);
        }
    }
}
