<?php

namespace App\Services;

use App\Models\LocalAfecto;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class ProcessAffiliationService
{
    public function move(
        string $personId,
        string $expectedOriginId,
        string $destinationId,
        string $effectiveDate,
        array $attributes = [],
    ): LocalAfecto {
        if ($expectedOriginId === $destinationId) {
            throw ValidationException::withMessages([
                'destino' => 'O destino deve ser diferente da afectação actual.',
            ]);
        }

        $effective = CarbonImmutable::parse($effectiveDate)->startOfDay();
        $today = CarbonImmutable::today();
        $current = LocalAfecto::query()
            ->where('pessoa_id', $personId)
            ->whereDate('dataInicio', '<=', $today->toDateString())
            ->where(function ($query) use ($today) {
                $query->whereNull('dataFim')
                    ->orWhereDate('dataFim', '>=', $today->toDateString());
            })
            ->orderByDesc('dataInicio')
            ->lockForUpdate()
            ->first();

        if (! $current) {
            throw ValidationException::withMessages([
                'origem' => 'O agente não tem uma afectação actual registada.',
            ]);
        }

        if ((string) $current->local_id !== $expectedOriginId) {
            throw ValidationException::withMessages([
                'origem' => 'A origem já não corresponde à afectação actual do agente.',
            ]);
        }

        if ($effective->lessThanOrEqualTo(CarbonImmutable::parse($current->dataInicio)->startOfDay())) {
            throw ValidationException::withMessages([
                'dataDespacho' => 'A data do despacho deve ser posterior ao início da afectação actual.',
            ]);
        }

        $current->update(['dataFim' => $effective->subDay()->toDateString()]);

        return LocalAfecto::query()->create([
            ...$attributes,
            'pessoa_id' => $personId,
            'local_id' => $destinationId,
            'dataInicio' => $effective->toDateString(),
        ]);
    }
}
