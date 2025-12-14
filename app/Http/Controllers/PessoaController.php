<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PessoaController extends Controller
{
    function getByEstado()
    {
        try {
            return DB::table('pessoas')
                ->join('situacao_pessoas as sp', 'sp.pessoa_id', '=', 'pessoas.id')
                ->whereNull('pessoas.deleted_at')
                ->whereNull('sp.deleted_at')
                ->select(
                    'sp.situacao as situacao',
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('sp.situacao',)
                ->get();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function getDashData()
    {
        try {
            $pessoas = Pessoa::count();

            $results = DB::table('pessoas')
                ->join('categoria_policias as cp', 'cp.pessoa_id', '=', 'pessoas.id')
                ->whereNull('pessoas.deleted_at')
                ->whereNull('cp.deleted_at')
                ->select(
                    'cp.categoria_id as categoria',
                    'pessoas.genero',
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('cp.categoria_id', 'pessoas.genero')
                ->orderBy('pessoas.genero')
                ->get();

            $pessoasProvincia = Pessoa::select('provincia', 'genero', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                ->join('categoria_policias as cp', 'cp.pessoa_id', '=', 'pessoas.id')
                ->whereNull('pessoas.deleted_at')
                ->whereNull('cp.deleted_at')
                ->groupBy('provincia', 'genero')
                ->get();

            return response()->json([
                'pessoas' => $pessoas,
                'pessoasPorEstado' => $this->getByEstado(),
                'pessoasCategoria' => $results,
                'pessoasProvincia' => $pessoasProvincia
            ], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Pessoa::query()
            ->select(
                'pessoas.*',
                'situacao_pessoas.situacao',
                'categoria_policias.categoria_id',
                'especialidade_pessoas.especialidade_id'
            )
            ->leftJoin('especialidade_pessoas', 'especialidade_pessoas.pessoa_id', '=', 'pessoas.id')
            ->leftJoin('categoria_policias', 'categoria_policias.pessoa_id', '=', 'pessoas.id')
            ->leftJoin('situacao_pessoas', 'situacao_pessoas.pessoa_id', '=', 'pessoas.id')
            ->leftJoin('curso_policias', 'curso_policias.pessoa_id', '=', 'pessoas.id')

            // nome completo
            ->when(request('nomeCompleto'), function ($q, $nome) {
                $q->where('pessoas.nomeCompleto', 'LIKE', "%{$nome}%");
            })

            // NIP
            ->when(request('nip'), function ($q, $nip) {
                $q->where('pessoas.nip', 'LIKE', "%{$nip}%");
            })

            // género
            ->when(request('genero'), function ($q, $genero) {
                $q->where('pessoas.genero', $genero);
            })

            // especialidade
            ->when(request('especialidade'), function ($q, $especialidade) {
                $q->where('especialidade_pessoas.especialidade_id', $especialidade);
                // ou ->where('especialidade_pessoas.especialidade', $especialidade)
            })

            // categoria
            ->when(request('categoria'), function ($q, $categoria) {
                $q->where('categoria_policias.categoria_id', $categoria);
            })

            // curso
            ->when(request('curso'), function ($q, $curso) {
                $q->where('curso_policias.curso_id', $curso);
            })

            // situação (ENUM)
            ->when(request('situacao'), function ($q, $situacao) {
                $q->where('situacao_pessoas.situacao', $situacao);
            });


        $pessoas = $paging
            ? $query->paginate($pageSize,[] ,'page', $page)
            : $query->simplePaginate($pageSize,[], 'page', $page);

        return response()->json(['pessoas' => $pessoas, 'pessoasPorEstado' => $this->getByEstado()], 200);
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
            'aprovado' => 'required|integer',
            'nip' => 'required|string|unique:pessoas,nip',
            'isGerivel' => 'required|boolean',
            'nomeCompleto' => 'required|string|max:255',
            'nomeMae' => 'nullable|string|max:255',
            'nomePai' => 'nullable|string|max:255',
            'dataNasc' => 'nullable|date',
            'nuit' => 'nullable|string|max:20',
            'estadoCivil' => 'nullable|in:solteiro,casado,divorciado,viuvo',
            'grupoSangue' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|in:Maputo Cidade,Maputo Provincia,Gaza,Inhambane,Sofala,Manica,Zambezia,Nampula,Tete,Cabo Delgado,Niassa',
            'residencia' => 'nullable|string|max:255',
            'genero' => 'nullable|in:Masculino,Feminino,Outro',
            'BI' => 'nullable|string|max:50',
            'altura' => 'nullable|numeric|min:0|max:3',
            'linguas' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar pessoa', 'errors' => $validation->errors()], 409);
        }

        $pessoa = Pessoa::create($validation->validated());

        return response()->json(['message' => 'Pessoa criada com sucesso!', 'pessoa' => $pessoa], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pessoa = Pessoa::findOrFail($id);
        return response()->json($pessoa, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pessoa $pessoa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'activo' => 'required|boolean',
            'aprovado' => 'required|integer',
            'nip' => 'required|string|unique:pessoas,nip',
            'isGerivel' => 'required|boolean',
            'nomeCompleto' => 'required|string|max:255',
            'nomeMae' => 'nullable|string|max:255',
            'nomePai' => 'nullable|string|max:255',
            'dataNasc' => 'nullable|date',
            'nuit' => 'nullable|string|max:20',
            'estadoCivil' => 'nullable|in:solteiro,casado,divorciado,viuvo',
            'grupoSangue' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|in:Maputo Cidade,Maputo Provincia,Gaza,Inhambane,Sofala,Manica,Zambezia,Nampula,Tete,Cabo Delgado,Niassa',
            'residencia' => 'nullable|string|max:255',
            'genero' => 'nullable|in:Masculino,Feminino,Outro',
            'BI' => 'nullable|string|max:50',
            'altura' => 'nullable|numeric|min:0|max:3',
            'linguas' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar pessoa', 'errors' => $validation->errors()], 409);
        }

        $pessoa->update($validation->validated());

        return response()->json(['message' => 'Pessoa actualizada com sucesso!', 'pessoa' => $pessoa], 200);
    }
    /**
     * Remove the specified resource from storage.
     */    public function destroy($id)
    {
        $pessoa = Pessoa::findOrFail($id);
        $pessoa->delete();

        return response()->json(['message' => 'Pessoa eliminada com sucesso!'], 200);
    }
}
