<?php

namespace App\Http\Controllers;

use App\Http\Requests\Requests\pessoaRequest;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PessoaController extends Controller
{
    function getByEstado($year, $previousYear)
    {
        try {
            return DB::table('pessoas')
                ->join('situacao_pessoas as sp', 'sp.pessoa_id', '=', 'pessoas.id')
                ->whereNull('pessoas.deleted_at')
                ->whereNull('sp.deleted_at')
                ->select(
                    'sp.situacao as situacao',
                    DB::raw("SUM(CASE WHEN YEAR(sp.created_at) <= $year THEN 1 ELSE 0 END) as total"),
                    DB::raw("SUM(CASE WHEN YEAR(sp.created_at) <= $previousYear THEN 1 ELSE 0 END) as previous_total")
                )
                ->groupBy('sp.situacao')
                ->get()
                ->map(function ($item) {
                    $item->rate_change = $item->previous_total > 0
                        ? (($item->total - $item->previous_total) / $item->previous_total) * 100
                        : null;
                    return $item;
                });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    function getByEspecialidade($year, $previousYear)
    {
        try {
            return DB::table('pessoas')
                ->join('especialidade_pessoas as cp', 'cp.pessoa_id', '=', 'pessoas.id')
                ->whereNull('pessoas.deleted_at')
                ->whereNull('cp.deleted_at')
                ->select(
                    'cp.especialidade_id as especialidade',
                    'pessoas.genero',
                    DB::raw("SUM(CASE WHEN YEAR(cp.created_at) <= $year THEN 1 ELSE 0 END) as total"),
                    DB::raw("SUM(CASE WHEN YEAR(cp.created_at) <= $previousYear THEN 1 ELSE 0 END) as previous_total")
                )
                ->groupBy('cp.especialidade_id', 'pessoas.genero')
                ->orderBy('pessoas.genero')
                ->get()
                ->map(function ($item) {
                    $item->rate_change = $item->previous_total > 0
                        ? (($item->total - $item->previous_total) / $item->previous_total) * 100
                        : null;

                    // 📈 Comparação textual
                    if ($item->previous_total === 0 && $item->total > 0) {
                        $item->trend = 'Aumentou'; // não tinha no ano anterior
                    } elseif ($item->total > $item->previous_total) {
                        $item->trend = 'Aumentou';
                    } elseif ($item->total < $item->previous_total) {
                        $item->trend = 'Diminuiu';
                    } else {
                        $item->trend = 'Manteve';
                    }

                    return $item;
                });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    function getDashData(Request $request)
    {
        try {
            $year = $request->input('year', now()->year);
            $previousYear = $year - 1;

            $totalCurrent = Pessoa::whereNull('deleted_at')->count();

            $totalPrevious = Pessoa::whereNull('deleted_at')
                ->whereYear('created_at', '<=', $previousYear)
                ->count();

            $rateChange = $totalPrevious > 0
                ? (($totalCurrent - $totalPrevious) / $totalPrevious) * 100
                : null;

            $pessoasProvincia = Pessoa::query()
                ->whereNull('deleted_at')
                ->select(
                    'provincia',
                    'genero',
                    DB::raw("SUM(CASE WHEN YEAR(created_at) <= $year THEN 1 ELSE 0 END) as total"),
                    DB::raw("SUM(CASE WHEN YEAR(created_at) <= $previousYear THEN 1 ELSE 0 END) as previous_total")
                )
                ->groupBy('provincia', 'genero')
                ->get();

            return response()->json([
                'pessoas' => ['total' => $totalCurrent, 'variation' => $rateChange],
                'pessoasPorEstado' => $this->getByEstado($year, $previousYear),
                'pessoasEspecialidade' => $this->getByEspecialidade($year, $previousYear),
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
        $year = $request->input('year', now()->year);
        $previousYear = $year - 1;

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
            ? $query->paginate($pageSize, [], 'page', $page)
            : $query->simplePaginate($pageSize, [], 'page', $page);

        return response()->json(['pessoas' => $pessoas, 'pessoasPorEstado' => $this->getByEstado($year, $previousYear)], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(pessoaRequest $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $pessoa = Pessoa::create($request->all());

            return response(['pessoa' => $pessoa], 201);
        } catch (\Throwable $th) {
            return response(['error' => 'Error inesperado'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $pessoa = Pessoa::query()
                ->select(
                    'pessoas.*',
                    'situacao_pessoas.situacao',
                    'categoria_policias.categoria_id',
                    'especialidade_pessoas.especialidade_id',
                    'curso_policias.*'
                )
                ->where('pessoas.id', $id)
                ->leftJoin('especialidade_pessoas', 'especialidade_pessoas.pessoa_id', '=', 'pessoas.id')
                ->leftJoin('categoria_policias', 'categoria_policias.pessoa_id', '=', 'pessoas.id')
                ->leftJoin('situacao_pessoas', 'situacao_pessoas.pessoa_id', '=', 'pessoas.id')
                ->leftJoin('curso_policias', 'curso_policias.pessoa_id', '=', 'pessoas.id')
                ->get();

            return response()->json($pessoa, 200);
        } catch (\Throwable $th) {
            dd($th);
            //throw $th;
        }
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
