<?php

namespace App\Http\Controllers;

use App\Http\Requests\pessoaRequest;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PessoaController extends Controller
{
    function getByEstado($year, $previousYear)
    {
        try {
            $uncomplete = DB::table('pessoas')
                ->where('stepFinished', '<', 4)
                ->count();

            $byStatus = DB::table('pessoas')
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

            $byStatus[] = (object)[
                'situacao' => 'Incompleto',
                'total' => $uncomplete,
            ];

            return $byStatus;
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error inesperado'], 500);
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
            return response()->json(['error' => 'Error inesperado'], 500);
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
        // return response()->json($this->getByEstado(2025, 2024));
        $pageSize = $request->input('per_page', 10);
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
     * Store a newly created resource in storage.
     */
    public function store(PessoaRequest $request)
    {
        try {
            $data = $request->input('info');
            $data['nip'] = NipGenerator::generate();
            $pessoa = Pessoa::create($data);

            $sitController = new SituacaoController();
            $sitController->addDefaultStatus($pessoa->id);

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
                    'curso_policias.*',
                    'pessoas.*',
                    'pessoas.id as pessoa_id',
                    'situacao_pessoas.situacao',
                    'categoria_policias.categoria_id',
                    'especialidade_pessoas.especialidade_id'
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

    public function updateStep($step, $id)
    {
        try {
            $pessoa = Pessoa::findOrFail($id);
            $pessoa->stepFinished = $step;
            $pessoa->save();
        } catch (\Throwable $th) {
            return response(['error' => 'Error inesperado'], 500);
        }
    }

    public function search($query)
    {
        try {
            $pessoas = Pessoa::query()
                ->select(
                    'pessoas.*',
                    'local_afectos.local_id',
                    'local_afectos.cargo',
                    'categoria_policias.categoria_id',
                    'especialidade_pessoas.especialidade_id',
                    'sp.situacao',
                    'sp.created_at as situacao_created_at'
                )

                // Join com a situação mais recente
                ->leftJoin('situacao_pessoas as sp', function ($join) {
                    $join->on('pessoas.id', '=', 'sp.pessoa_id')
                        ->whereIn('sp.situacao', ['Activo', 'Reserva', 'Aposentado', 'Suspenso'])
                        ->whereRaw('sp.created_at = (
                SELECT MAX(sp2.created_at)
                FROM situacao_pessoas sp2
                WHERE sp2.pessoa_id = pessoas.id
            )');
                })

                // ⭐ Garantir que só pegue pessoas com situação válida
                ->whereNotNull('sp.id')

                ->leftJoin('local_afectos', function ($join) {
                    $join->on('pessoas.id', '=', 'local_afectos.pessoa_id')
                        ->where(function ($q) {
                            $q->whereNull('local_afectos.dataFim')
                                ->orWhere('local_afectos.dataFim', '>=', now());
                        });
                })
                ->leftJoin('especialidade_pessoas', 'especialidade_pessoas.pessoa_id', '=', 'pessoas.id')
                ->leftJoin('categoria_policias', 'categoria_policias.pessoa_id', '=', 'pessoas.id')
                ->when($query, function ($q, $nome) {
                    $q->where('pessoas.nomeCompleto', 'LIKE', "%{$nome}%");
                })
                ->get();

            return response()->json(['pessoas' => $pessoas], 200);
        } catch (\Throwable $th) {
            dd($th);
            //throw $th;
        }
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
