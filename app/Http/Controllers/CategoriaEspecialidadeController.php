<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEspecialidadeCategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaEspecialidadeController extends Controller
{
    function saveCatAndEsp(StoreEspecialidadeCategoriaRequest $request)
    {
        try {
            dd($request['especialidade']);

            $especialidade = DB::table('especialidade_pessoas') // substitua pelo nome real da sua tabela
                ->insert([
                    'pessoa_id' => $request['especialidade']['pessoa_id'],
                    'especialidade_id'  => $request['especialidade']['especialidade'],
                    'dataInicio'  => $request['especialidade']['dataInicio'],
                    'dataFim'     => $request['especialidade']['dataFim'],
                    'dataFim'     => $request['especialidade']['observacoesEsp'],
                    'despacho' => ''
                ]);

            $categoria = DB::table('categoria_policias')
                ->insert([
                    'pessoa_id' => $request['categoria']['pessoa_id'],
                    'categoria_id'  => $request['categoria']['categoria'],
                    'dataInicio'  => $request['categoria']['dataInicio'],
                    'dataFim'     => $request['categoria']['dataFim'],
                    'dataFim'     => $request['especialidade']['observacoesCat'],
                    'despacho' => ''
                ]);

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
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
