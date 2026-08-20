<?php

namespace App\Http\Controllers;

use App\Http\Requests\PessoaRequest;
use App\Models\Pessoa;
use App\Services\ProcessAgentEligibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PessoaController extends Controller
{
    public function getByEstado($year, $previousYear)
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
                    $item->change = $item->total - $item->previous_total;
                    $item->rate_change = $item->previous_total > 0
                        ? (($item->total - $item->previous_total) / $item->previous_total) * 100
                        : null;

                    return $item;
                });

            $byStatus[] = (object) [
                'situacao' => 'Incompleto',
                'total' => $uncomplete,
            ];

            $byStatus[] = (object) [
                'situacao' => 'Férias',
                'total' => DB::table('ferias')
                    ->whereNull('deleted_at')
                    ->whereDate('dataInicio', '<=', today())
                    ->whereDate('dataFim', '>=', today())
                    ->distinct('pessoa_id')
                    ->count('pessoa_id'),
            ];

            return $byStatus;
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error inesperado'], 500);
        }
    }

    public function getByEspecialidade($year, $previousYear, $estado = null)
    {
        try {
            return DB::table('pessoas')
                ->join('especialidade_pessoas as cp', 'cp.pessoa_id', '=', 'pessoas.id')
                ->when($estado, function ($q) use ($estado) {
                    $this->applySituationFilter($q, $estado);
                })
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

    public function getDashData(Request $request)
    {
        try {
            $year = $request->input('year', now()->year);
            $previousYear = $year - 1;
            $estado = $request->input('estado');
            $section = $request->input('section');

            if ($section === 'provincia') {
                $pessoasProvincia = Pessoa::query()
                    ->whereNull('pessoas.deleted_at')
                    ->when($estado, function ($q) use ($estado) {
                        $this->applySituationFilter($q, $estado);
                    })
                    ->select(
                        'pessoas.provincia as provincia',
                        'pessoas.genero as genero',
                        DB::raw("SUM(CASE WHEN YEAR(pessoas.created_at) <= $year THEN 1 ELSE 0 END) as total"),
                        DB::raw("SUM(CASE WHEN YEAR(pessoas.created_at) <= $previousYear THEN 1 ELSE 0 END) as previous_total")
                    )
                    ->groupBy('pessoas.provincia', 'pessoas.genero')
                    ->get();

                return response()->json(['pessoasProvincia' => $pessoasProvincia], 200);
            }

            if ($section === 'especialidade') {
                $pessoasEspecialidade = $this->getByEspecialidade($year, $previousYear, $estado);

                return response()->json(['pessoasEspecialidade' => $pessoasEspecialidade], 200);
            }

            $totalCurrent = Pessoa::whereNull('deleted_at')
                ->whereYear('created_at', '<=', $year)
                ->count();

            $totalPrevious = Pessoa::whereNull('deleted_at')
                ->whereYear('created_at', '<=', $previousYear)
                ->count();

            $rateChange = $totalPrevious > 0
                ? (($totalCurrent - $totalPrevious) / $totalPrevious) * 100
                : null;

            $pessoasProvincia = Pessoa::query()
                ->whereNull('pessoas.deleted_at')
                ->when($estado, function ($q) use ($estado) {
                    $this->applySituationFilter($q, $estado);
                })
                ->select(
                    'pessoas.provincia as provincia',
                    'pessoas.genero as genero',
                    DB::raw("SUM(CASE WHEN YEAR(pessoas.created_at) <= $year THEN 1 ELSE 0 END) as total"),
                    DB::raw("SUM(CASE WHEN YEAR(pessoas.created_at) <= $previousYear THEN 1 ELSE 0 END) as previous_total")
                )
                ->groupBy('pessoas.provincia', 'pessoas.genero')
                ->get();

            return response()->json([
                'pessoas' => [
                    'total' => $totalCurrent,
                    'change' => $totalCurrent - $totalPrevious,
                    'variation' => $rateChange,
                ],
                'pessoasPorEstado' => $this->getByEstado($year, $previousYear),
                'pessoasEspecialidade' => $this->getByEspecialidade($year, $previousYear, $estado),
                'pessoasProvincia' => $pessoasProvincia,
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
            ->with(['categoriaAtual', 'especialidadeAtual', 'situacaoAtual', 'feriasAtuais'])

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
                $q->whereHas('especialidadeAtual', function ($q) use ($especialidade) {
                    $q->where('especialidade_id', $especialidade);
                });
            })

            // categoria
            ->when(request('categoria'), function ($q, $categoria) {
                $q->whereHas('categoriaAtual', function ($q) use ($categoria) {
                    $q->where('categoria_id', $categoria);
                });
            })

            // curso
            ->when(request('curso'), function ($q, $curso) {
                $q->whereHas('cursosPoliciais', function ($q) use ($curso) {
                    $q->where('curso_id', $curso);
                });
            })

            // situação (ENUM)
            ->when(request('situacao'), function ($q, $situacao) {
                if ($this->isVacationSituation($situacao)) {
                    $q->whereHas('feriasAtuais');

                    return;
                }

                $q->whereDoesntHave('feriasAtuais')
                    ->whereHas('situacaoAtual', function ($q) use ($situacao) {
                        $q->whereRaw('LOWER(situacao) = ?', [mb_strtolower($situacao)]);
                    });
            })
            ->orderBy('pessoas.created_at')
            ->orderBy('pessoas.id');

        $pessoas = $paging
            ? $query->paginate($pageSize, ['pessoas.*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['pessoas.*'], 'page', $page);

        $pessoas->through(function (Pessoa $pessoa) {
            $pessoa->setAttribute('situacao', $pessoa->feriasAtuais ? 'Férias' : $pessoa->situacaoAtual?->situacao);
            $pessoa->setAttribute('categoria_id', $pessoa->categoriaAtual?->categoria_id);
            $pessoa->setAttribute('especialidade_id', $pessoa->especialidadeAtual?->especialidade_id);
            $pessoa->unsetRelation('situacaoAtual');
            $pessoa->unsetRelation('feriasAtuais');
            $pessoa->unsetRelation('categoriaAtual');
            $pessoa->unsetRelation('especialidadeAtual');

            return $pessoa;
        });

        return response()->json(['pessoas' => $pessoas, 'pessoasPorEstado' => $this->getByEstado($year, $previousYear)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PessoaRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->input('info');
            $data['nip'] = NipGenerator::generate();
            $pessoa = Pessoa::create($data);

            $sitController = new SituacaoController;
            $sitController->addDefaultStatus($pessoa->id);

            DB::commit();

            return response(['pessoa' => $pessoa], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response(['error' => 'Error inesperado'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $emFerias = Pessoa::query()->whereKey($id)->whereHas('feriasAtuais')->exists();
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

            if ($emFerias) {
                $pessoa->each->setAttribute('situacao', 'Férias');
            }

            return response()->json($pessoa, 200);
        } catch (\Throwable $th) {
            dd($th);
            // throw $th;
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

    public function search(Request $request, string $query)
    {
        try {
            $validated = $request->validate([
                'processo' => ['nullable', 'in:exonerar,subsidioFunebre,continuarEstudos,reservaAposentadoActivo'],
            ]);
            $processo = $validated['processo'] ?? null;

            $pessoas = Pessoa::query()
                ->with(['categoriaAtual', 'especialidadeAtual', 'localTrabalhoAtual.local', 'situacaoAtual', 'feriasAtuais'])
                ->select('pessoas.*')

                ->selectSub(function ($subquery) {
                    $subquery->from('continuacao_estudos')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('continuacao_estudos.pessoa_id', 'pessoas.id')
                        ->where('continuacao_estudos.estado', 'aberto')
                        ->whereNull('continuacao_estudos.deleted_at');
                }, 'continuacao_estudo_em_andamento')

                ->whereHas('situacaoAtual')
                ->when($query, function ($q, $nome) {
                    $q->where('pessoas.nomeCompleto', 'LIKE', "%{$nome}%");
                })
                ->get()
                ->map(function ($pessoa) use ($processo) {
                    $pessoa->setAttribute('local_id', $pessoa->localTrabalhoAtual?->local_id);
                    $pessoa->setAttribute('localTrabalho', $pessoa->localTrabalhoAtual?->local?->nome);
                    $pessoa->setAttribute('cargo', $pessoa->localTrabalhoAtual?->cargo);
                    $pessoa->setAttribute('categoria_id', $pessoa->categoriaAtual?->categoria_id);
                    $pessoa->setAttribute('especialidade_id', $pessoa->especialidadeAtual?->especialidade_id);
                    $pessoa->setAttribute('situacao', $pessoa->feriasAtuais ? 'Férias' : $pessoa->situacaoAtual?->situacao);
                    $pessoa->setAttribute('situacao_created_at', $pessoa->situacaoAtual?->created_at);

                    $eligibility = app(ProcessAgentEligibility::class)->evaluate(
                        $processo,
                        $pessoa->situacao,
                        (bool) $pessoa->continuacao_estudo_em_andamento,
                    );

                    $pessoa->selecaoBloqueada = $eligibility['blocked'];
                    $pessoa->motivoBloqueio = $eligibility['reason'];
                    $pessoa->unsetRelation('localTrabalhoAtual');
                    $pessoa->unsetRelation('categoriaAtual');
                    $pessoa->unsetRelation('especialidadeAtual');
                    $pessoa->unsetRelation('situacaoAtual');
                    $pessoa->unsetRelation('feriasAtuais');

                    return $pessoa;
                });

            return response()->json(['pessoas' => $pessoas], 200);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Nao foi possivel pesquisar os agentes.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pessoa = Pessoa::findOrFail($id);
        $pessoa->delete();

        return response()->json(['message' => 'Pessoa eliminada com sucesso!'], 200);
    }

    private function isVacationSituation(?string $situation): bool
    {
        return in_array(mb_strtolower(trim((string) $situation)), ['ferias', 'férias'], true);
    }

    private function applySituationFilter($query, string $situation): void
    {
        if ($this->isVacationSituation($situation)) {
            $query->whereExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('ferias')
                    ->whereColumn('ferias.pessoa_id', 'pessoas.id')
                    ->whereNull('ferias.deleted_at')
                    ->whereDate('ferias.dataInicio', '<=', today())
                    ->whereDate('ferias.dataFim', '>=', today());
            });

            return;
        }

        $query->join('situacao_pessoas as sp', 'sp.pessoa_id', '=', 'pessoas.id')
            ->whereNull('sp.deleted_at')
            ->whereRaw('LOWER(sp.situacao) = ?', [mb_strtolower($situation)])
            ->whereNotExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('ferias')
                    ->whereColumn('ferias.pessoa_id', 'pessoas.id')
                    ->whereNull('ferias.deleted_at')
                    ->whereDate('ferias.dataInicio', '<=', today())
                    ->whereDate('ferias.dataFim', '>=', today());
            });
    }
}
