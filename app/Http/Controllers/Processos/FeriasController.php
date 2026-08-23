<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\FeriasRequest;
use App\Models\Pessoa;
use App\Models\Processos\Ferias;
use App\Services\VacationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeriasController extends Controller
{
    public function __construct(private readonly VacationService $vacations) {}

    public function index(Request $request)
    {
        $pageSize = (int) $request->input('pageSize', $request->input('per_page', 10));

        $query = Ferias::query()
            ->select('ferias.*', 'p.nomeCompleto as pessoaNome', 'p.nip', 'ab.nomeCompleto as abertoPorNome')
            ->leftJoin('pessoas as p', 'p.id', '=', 'ferias.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'ferias.abertoPor')
            ->when($request->input('nomeAgente'), fn ($q, $value) => $q->where('p.nomeCompleto', 'like', "%{$value}%"))
            ->when($request->input('nip'), fn ($q, $value) => $q->where('p.nip', 'like', "%{$value}%"))
            ->when($request->input('nrProcesso'), fn ($q, $value) => $q->where('ferias.nrProcesso', 'like', "%{$value}%"))
            ->when($request->input('systemId'), fn ($q, $value) => $q->where('ferias.systemId', 'like', "%{$value}%"))
            ->when($request->input('createdAt'), fn ($q, $value) => $q->whereDate('ferias.created_at', $value))
            ->latest('ferias.dataInicio');

        return response()->json(['data' => $query->paginate(max(1, min($pageSize, 100)))], 200);
    }

    public function show(string $id)
    {
        return response()->json(['data' => Ferias::query()->findOrFail($id)], 200);
    }

    public function stats(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $stats = Ferias::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN estado = "aberto" THEN 1 ELSE 0 END) as aberto, SUM(CASE WHEN estado = "fechado" THEN 1 ELSE 0 END) as fechado, COALESCE(SUM(diasFerias), 0) as dias')
            ->whereYear('dataInicio', $year)
            ->first();

        $stats->agentesAtivos = Ferias::query()
            ->whereDate('dataInicio', '<=', today())
            ->whereDate('dataFim', '>=', today())
            ->distinct('pessoa_id')
            ->count('pessoa_id');
        $stats->agentesQueGozaram = Ferias::query()
            ->whereYear('dataInicio', $year)
            ->whereDate('dataFim', '<', today())
            ->distinct('pessoa_id')
            ->count('pessoa_id');

        return response()->json(['data' => $stats], 200);
    }

    public function balance(Request $request)
    {
        $data = $request->validate([
            'pessoa_id' => ['required', 'exists:pessoas,id'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
        ]);
        $pessoa = Pessoa::query()->findOrFail($data['pessoa_id']);
        $year = (int) ($data['year'] ?? now()->year);

        return response()->json([
            'data' => [
                'year' => $year,
                'balance' => $this->vacations->balanceThroughYear($pessoa, $year),
                'annual_entitlement' => VacationService::ANNUAL_ENTITLEMENT,
                'max_days_per_year' => VacationService::MAX_DAYS_PER_YEAR,
                'max_periods_per_year' => VacationService::MAX_PERIODS_PER_YEAR,
                'max_days_per_period' => VacationService::MAX_DAYS_PER_PERIOD,
            ],
        ]);
    }

    public function newProcess(FeriasRequest $request)
    {
        $despacho = $this->storeDispatch($request);

        $record = DB::transaction(function () use ($request, $despacho) {
            $pessoa = Pessoa::query()->lockForUpdate()->findOrFail($request->validated('pessoa_id')[0]);
            $period = $this->vacations->validatePeriod(
                $pessoa,
                $request->validated('dataInicio'),
                $request->validated('dataFim'),
            );

            return Ferias::query()->create([
                ...$request->safe()->only(['dataInicio', 'dataFim', 'nrDespacho', 'dataDespacho', 'observacoes', 'abertoPor']),
                'pessoa_id' => $pessoa->id,
                'despacho' => $despacho,
                'estado' => 'fechado',
                'diasFerias' => $period['days'],
                'saldoAntes' => $period['balance_before'],
                'saldoDepois' => $period['balance_after'],
            ]);
        });

        return response()->json(['success' => 'Processo de férias criado com sucesso!', 'data' => $record], 201);
    }

    public function update(FeriasRequest $request, string $id)
    {
        $despacho = $this->storeDispatch($request);

        $record = DB::transaction(function () use ($request, $id, $despacho) {
            $ferias = Ferias::query()->lockForUpdate()->findOrFail($id);
            $pessoa = Pessoa::query()->lockForUpdate()->findOrFail($request->validated('pessoa_id')[0]);
            $period = $this->vacations->validatePeriod(
                $pessoa,
                $request->validated('dataInicio'),
                $request->validated('dataFim'),
                $ferias->id,
            );

            $data = [
                ...$request->safe()->only(['dataInicio', 'dataFim', 'nrDespacho', 'dataDespacho', 'observacoes', 'abertoPor']),
                'pessoa_id' => $pessoa->id,
                'diasFerias' => $period['days'],
                'saldoAntes' => $period['balance_before'],
                'saldoDepois' => $period['balance_after'],
            ];

            if ($despacho !== null) {
                $data['despacho'] = $despacho;
            }

            $ferias->fill($data)->save();

            return $ferias;
        });

        return response()->json(['success' => 'Processo de férias actualizado com sucesso!', 'data' => $record], 200);
    }

    public function destroy(string $id)
    {
        Ferias::query()->findOrFail($id)->delete();

        return response()->json(['success' => 'Processo de férias removido com sucesso!'], 200);
    }

    private function storeDispatch(FeriasRequest $request): ?string
    {
        if (! $request->hasFile('despacho')) {
            return null;
        }

        $file = $request->file('despacho');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);

        return $filename;
    }
}
