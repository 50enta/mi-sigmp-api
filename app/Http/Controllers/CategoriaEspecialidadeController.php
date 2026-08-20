<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEspecialidadeCategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriaEspecialidadeController extends Controller
{
    function saveCatAndEsp(StoreEspecialidadeCategoriaRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->file('especialidade.despachoEsp')) {
                $filename = time() . '_' . $request->file('especialidade.despachoEsp')->getClientOriginalName();
                $request->file('especialidade.despachoEsp')->move(public_path('uploads'), $filename);
            }

            if ($request->file('categoria.despachoCat')) {
                $catFileName = time() . '_' . $request->file('categoria.despachoCat')->getClientOriginalName();
                $request->file('categoria.despachoCat')->move(public_path('uploads'), $catFileName);
            }

            $especialidade = DB::table('especialidade_pessoas') // substitua pelo nome real da sua tabela
                ->insert([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['especialidade']['pessoa_id'],
                    'especialidade_id'  => $request['especialidade']['especialidade'],
                    'dataInicio'  => $request['especialidade']['dataNomeacaoEsp'],
                    'nrDespacho' => $request['especialidade']['nrDespachoEsp'],
                    'dataDespacho' => $request['especialidade']['dataDespachoEsp'],
                    'dataFim'     => isset($request['especialidade']['dataFim']) ? $request['especialidade']['dataFim'] : null,
                    'obs'     => isset($request['especialidade']['observacoesEsp']) ? $request['especialidade']['observacoesEsp'] : null,
                    'despacho' => $filename ?? null
                ]);

            $categoria = DB::table('categoria_policias')
                ->insert([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['categoria']['pessoa_id'],
                    'categoria_id'  => $request['categoria']['categoria'],
                    'dataInicio'  => $request['categoria']['dataNomeacaoCat'],
                    'nrDespacho' => $request['categoria']['nrDespachoCat'],
                    'dataDespacho' => $request['categoria']['dataDespachoCat'],
                    'obs'     => isset($request['categoria']['observacoesCat']) ? $request['categoria']['observacoesCat'] : null,
                    'dataFim'     => isset($request['categoria']['dataFim']) ? $request['categoria']['dataFim'] : null,
                    'despacho' => $catFileName ?? null
                ]);

            $pessoa = new PessoaController();
            $pessoa->updateStep(3, $request['especialidade']['pessoa_id']);

            DB::commit();

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return response(['error' => 'Erro inesperado'], 500);
        }
    }




    function getCatEspHistory(Request $request)
    {
        try {
            // dd($request->query());
            $categoria = DB::table('categoria_policias')
                ->where('pessoa_id', $request->query('pessoa_id'))
                ->select('*')
                ->get();

            $especialidades = DB::table('especialidade_pessoas')
                ->where('pessoa_id', $request->query('pessoa_id'))
                ->select('*')
                ->get();

            return response()->json(['categorias' => $categoria, 'especialidades' => $especialidades], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    function updateCatEsp($request)
    {
        try {
            DB::table('categoria_policias')
                ->where('pessoa_id', $request['pessoa_id'])
                ->where(function ($query) {
                    $query->whereNull('dataFim')
                        ->orWhere('activo', true);
                })
                ->update(['dataFim' => $request['dataInicio'], 'activo' => false]);

            DB::table('categoria_policias')
                ->insert([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['pessoa_id'],
                    'categoria_id'  => $request['categoria_id'],
                    'dataInicio'  => $request['dataInicio'],
                    'obs'     => isset($request['obs']) ? $request['obs'] : null,
                    'despacho' => $request['despacho'],
                    'nrDespacho' => $request['nrDespacho'],
                    'dataDespacho' => $request['dataDespacho'],
                ]);

            return 1;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
