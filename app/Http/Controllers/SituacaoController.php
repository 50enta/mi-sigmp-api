<?php

namespace App\Http\Controllers;

use App\Models\Situacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SituacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Situacao::query();

        $situacoes = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['situacoes' => $situacoes], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'situacao' => 'required|in:suspenso,exonerado,expulso,morto,reservado,aposentado',
            'obs' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar situação', 'errors' => $validation->errors()], 409);
        }

        $situacao = Situacao::create($validation->validated());

        return response()->json(['message' => 'Situação criada com sucesso!', 'situacao' => $situacao], 201);
    }

    public function addDefaultStatus($id)
    {
        try {
            $data = [
                'id' => (string) Str::uuid(),
                'pessoa_id' => $id,
            ];

            DB::table('situacao_pessoas')->insert($data);
        } catch (\Throwable $th) {
            dd($th);
            return response(['error' => 'Error inesperado'], 500);
        }
    }
}
