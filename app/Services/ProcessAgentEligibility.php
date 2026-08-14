<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessAgentEligibility
{
    public const CONTINUAR_ESTUDOS = 'continuarEstudos';

    public const EXONERAR = 'exonerar';

    public const SUBSIDIO_FUNEBRE = 'subsidioFunebre';

    public const RESERVA_APOSENTADO_ACTIVO = 'reservaAposentadoActivo';

    /**
     * @return array{blocked: bool, reason: string|null}
     */
    public function evaluate(?string $processo, ?string $situacao, bool $continuacaoEmAndamento): array
    {
        $isMorto = strtolower(trim((string) $situacao)) === 'morto';

        return match ($processo) {
            self::EXONERAR => in_array(strtolower(trim((string) $situacao)), ['exonerado', 'morto'], true)
                ? ['blocked' => true, 'reason' => "Agente com situação {$situacao} não pode ser exonerado."]
                : ['blocked' => false, 'reason' => null],
            self::SUBSIDIO_FUNEBRE => $isMorto
                ? ['blocked' => true, 'reason' => 'O agente já se encontra na situação Morto.']
                : ['blocked' => false, 'reason' => null],
            self::CONTINUAR_ESTUDOS => $continuacaoEmAndamento
                ? ['blocked' => true, 'reason' => 'O agente já possui um processo de Continuação dos Estudos em andamento.']
                : ['blocked' => false, 'reason' => null],
            self::RESERVA_APOSENTADO_ACTIVO => $isMorto
                ? ['blocked' => true, 'reason' => 'Um agente morto não pode ser activado, colocado na reserva ou aposentado.']
                : ['blocked' => false, 'reason' => null],
            default => ['blocked' => false, 'reason' => null],
        };
    }

    public function ensureEligible(string $processo, string $pessoaId): void
    {
        $situacao = DB::table('situacao_pessoas')
            ->where('pessoa_id', $pessoaId)
            ->whereNull('deleted_at')
            ->latest('created_at')
            ->latest('id')
            ->value('situacao');

        $continuacaoEmAndamento = $processo === self::CONTINUAR_ESTUDOS
            && DB::table('continuacao_estudos')
                ->where('pessoa_id', $pessoaId)
                ->where('estado', 'aberto')
                ->whereNull('deleted_at')
                ->exists();

        $eligibility = $this->evaluate($processo, $situacao, $continuacaoEmAndamento);

        if ($eligibility['blocked']) {
            throw ValidationException::withMessages([
                'pessoa_id' => [$eligibility['reason']],
            ]);
        }
    }
}
