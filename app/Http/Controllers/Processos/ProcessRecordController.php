<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Models\Processos\ActualizacaoNivelAcademico;
use App\Models\Processos\AssentoBiografico;
use App\Models\Processos\ContinuacaoEstudo;
use App\Models\Processos\CorrecaoDeDados;
use App\Models\Processos\Exoneracao;
use App\Models\Processos\Falecimentos;
use App\Models\Processos\Pensoes;
use App\Models\Processos\Promocao;
use App\Models\Processos\Reafetacao;
use App\Models\Processos\SituacaoDisciplinar;
use App\Models\Processos\SubsideosFunebres;
use App\Models\Processos\Transferencias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\ProcessAgentEligibility;

class ProcessRecordController extends Controller
{
    private const MODELS = [
        'disciplinar' => SituacaoDisciplinar::class,
        'exonerar' => Exoneracao::class,
        'reafetar' => Reafetacao::class,
        'transferir' => Transferencias::class,
        'continuarEstudos' => ContinuacaoEstudo::class,
        'atualizacaoAcademica' => ActualizacaoNivelAcademico::class,
        'promover' => Promocao::class,
        'corrigirDados' => CorrecaoDeDados::class,
        'falecimento' => Falecimentos::class,
        'reservaAposentadoActivo' => Pensoes::class,
        'subsidioFunebre' => SubsideosFunebres::class,
        'assentoBiografico' => AssentoBiografico::class,
    ];

    public function update(Request $request, string $id)
    {
        $record = $this->findRecord($request, $id);
        $data = $request->only($record->getFillable());

        if ($request->route('process') === ProcessAgentEligibility::RESERVA_APOSENTADO_ACTIVO) {
            $pessoaId = $data['pessoa_id'] ?? $record->pessoa_id;
            $pessoaId = is_array($pessoaId) ? ($pessoaId[0] ?? null) : $pessoaId;
            app(ProcessAgentEligibility::class)->ensureEligible(
                ProcessAgentEligibility::RESERVA_APOSENTADO_ACTIVO,
                $pessoaId,
            );
        }

        foreach ($request->allFiles() as $field => $file) {
            if (!in_array($field, $record->getFillable(), true)) {
                continue;
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data[$field] = $filename;
        }

        foreach (['pessoa_id', 'permutador'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][0] ?? null;
            }
        }

        $record->fill($data)->save();

        return response()->json(['success' => 'Processo actualizado com sucesso!', 'data' => $record], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $record = $this->findRecord($request, $id);
        $record->delete();

        return response()->json(['success' => 'Processo removido com sucesso!'], 200);
    }

    private function findRecord(Request $request, string $id): Model
    {
        $process = $request->route('process');
        abort_unless(isset(self::MODELS[$process]), 404, 'Tipo de processo inválido.');

        $model = self::MODELS[$process];

        return $model::query()->findOrFail($id);
    }
}
