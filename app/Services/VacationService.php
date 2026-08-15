<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\Processos\Ferias;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class VacationService
{
    public const ANNUAL_ENTITLEMENT = 30;

    public const MAX_DAYS_PER_YEAR = 60;

    public const MAX_PERIODS_PER_YEAR = 2;

    public const MAX_DAYS_PER_PERIOD = 30;

    /** Feriados nacionais de Moçambique com data fixa (mês-dia). */
    private const NATIONAL_HOLIDAYS = [
        '01-01', '02-03', '04-07', '05-01', '06-25',
        '09-07', '09-25', '10-04', '12-25',
    ];

    /**
     * Conta os dias do período de forma inclusiva, sem descontar fins-de-semana
     * e sem contabilizar feriados nacionais.
     */
    public function countVacationDays(CarbonInterface|string $start, CarbonInterface|string $end): int
    {
        $cursor = CarbonImmutable::parse($start)->startOfDay();
        $last = CarbonImmutable::parse($end)->startOfDay();
        $days = 0;

        while ($cursor->lte($last)) {
            if (! in_array($cursor->format('m-d'), self::NATIONAL_HOLIDAYS, true)) {
                $days++;
            }
            $cursor = $cursor->addDay();
        }

        return $days;
    }

    /** @return array{days:int,balance_before:int,balance_after:int,used_in_year:int,periods_in_year:int} */
    public function validatePeriod(Pessoa $pessoa, string $startDate, string $endDate, ?string $exceptId = null): array
    {
        $start = CarbonImmutable::parse($startDate);
        $end = CarbonImmutable::parse($endDate);

        if ($start->year !== $end->year) {
            throw ValidationException::withMessages([
                'dataFim' => ['O período de férias deve começar e terminar no mesmo ano.'],
            ]);
        }

        $days = $this->countVacationDays($start, $end);
        if ($days < 1) {
            throw ValidationException::withMessages([
                'dataFim' => ['O período seleccionado contém apenas feriados.'],
            ]);
        }
        if ($days > self::MAX_DAYS_PER_PERIOD) {
            throw ValidationException::withMessages([
                'dataFim' => ['Cada período de férias pode ter no máximo 30 dias, sem contar feriados.'],
            ]);
        }

        $yearQuery = Ferias::query()
            ->where('pessoa_id', $pessoa->id)
            ->whereYear('dataInicio', $start->year)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId));

        $periods = (clone $yearQuery)->count();
        if ($periods >= self::MAX_PERIODS_PER_YEAR) {
            throw ValidationException::withMessages([
                'dataInicio' => ['O agente só pode gozar férias em dois períodos por ano.'],
            ]);
        }

        $overlaps = Ferias::query()
            ->where('pessoa_id', $pessoa->id)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->whereDate('dataInicio', '<=', $end->toDateString())
            ->whereDate('dataFim', '>=', $start->toDateString())
            ->exists();
        if ($overlaps) {
            throw ValidationException::withMessages([
                'dataInicio' => ['Este período sobrepõe-se a férias já registadas para o agente.'],
            ]);
        }

        $usedInYear = (int) (clone $yearQuery)->sum('diasFerias');
        if ($usedInYear + $days > self::MAX_DAYS_PER_YEAR) {
            throw ValidationException::withMessages([
                'dataFim' => ['O agente não pode gozar mais de 60 dias de férias no mesmo ano.'],
            ]);
        }

        $balanceBefore = $this->balanceThroughYear($pessoa, $start->year, $exceptId, false);
        if ($days > $balanceBefore) {
            throw ValidationException::withMessages([
                'dataFim' => ["Saldo insuficiente. O agente tem {$balanceBefore} dias de férias disponíveis."],
            ]);
        }

        return [
            'days' => $days,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore - $days,
            'used_in_year' => $usedInYear,
            'periods_in_year' => $periods,
        ];
    }

    public function balanceThroughYear(
        Pessoa $pessoa,
        int $year,
        ?string $exceptId = null,
        bool $zeroWithoutRecords = true,
    ): int {
        if ($zeroWithoutRecords && ! Ferias::query()->where('pessoa_id', $pessoa->id)->exists()) {
            return 0;
        }

        $firstYear = CarbonImmutable::parse($pessoa->created_at)->year;
        $entitlement = max(0, $year - $firstYear + 1) * self::ANNUAL_ENTITLEMENT;
        $used = (int) Ferias::query()
            ->where('pessoa_id', $pessoa->id)
            ->whereYear('dataInicio', '<=', $year)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->sum('diasFerias');

        return max(0, $entitlement - $used);
    }
}
