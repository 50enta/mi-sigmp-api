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
            if ($request->file('especialidade.despachoEsp')) {
                $filename = time() . '_' . $request->file('especialidade.despachoEsp')->getClientOriginalName();
                $request->file('especialidade.despachoEsp')->move(public_path('uploads'), $filename);
            }

            if ($request->file('categoria.despachoEsp')) {
                $catFileName = time() . '_' . $request->file('categoria.despachoEsp')->getClientOriginalName();
                $request->file('categoria.despachoEsp')->move(public_path('uploads'), $catFileName);
            }

            $especialidade = DB::table('especialidade_pessoas') // substitua pelo nome real da sua tabela
                ->insert([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['especialidade']['pessoa_id'],
                    'especialidade_id'  => $request['especialidade']['especialidade'],
                    'dataInicio'  => $request['especialidade']['dataNomeacaoEsp'],
                    // 'dataFim'     => $request['especialidade']['dataFim'],
                    'obs'     => $request['especialidade']['observacoesEsp'],
                    'despacho' => $filename ?? null
                ]);

            $categoria = DB::table('categoria_policias')
                ->insert([
                    'id' => (string) Str::uuid(),
                    'pessoa_id' => $request['categoria']['pessoa_id'],
                    'categoria_id'  => $request['categoria']['categoria'],
                    'dataInicio'  => $request['categoria']['dataNomeacaoCat'],
                    // 'dataFim'     => $request['categoria']['dataFim'],
                    'obs'     => $request['categoria']['observacoesCat'],
                    'despacho' => $catFileName ?? null
                ]);

            $pessoa = new PessoaController();
            $pessoa->updateStep(3, $request['especialidade']['pessoa_id']);

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            dd($th);
            //throw $th;
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
}
