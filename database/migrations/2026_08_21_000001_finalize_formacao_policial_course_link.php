<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const LEGACY_COLUMNS = ['instituicao', 'curso', 'dataInicio', 'dataConclusao'];

    public function up(): void
    {
        if (! Schema::hasTable('formacaoPolicial')) {
            return;
        }

        if (! Schema::hasColumn('formacaoPolicial', 'curso_id')) {
            Schema::table('formacaoPolicial', function (Blueprint $table) {
                $table->uuid('curso_id')->nullable();
                $table->foreign('curso_id')->references('id')->on('cursos')->nullOnDelete();
            });
        }

        if (! $this->hasAllLegacyColumns()) {
            return;
        }

        DB::table('formacaoPolicial')
            ->whereNull('curso_id')
            ->orderBy('id')
            ->each(function ($formacao): void {
                $cursoId = DB::table('cursos')
                    ->where('descricao', $formacao->curso)
                    ->value('id');

                if (! $cursoId) {
                    $cursoId = (string) Str::uuid();
                    $categoria = match ($formacao->instituicao) {
                        'acipol' => 'superior',
                        'esapol' => 'medio',
                        default => 'basico',
                    };

                    DB::table('cursos')->insert([
                        'id' => $cursoId,
                        'descricao' => $formacao->curso ?: 'Formação policial histórica',
                        'dataInicio' => $formacao->dataInicio,
                        'dataFim' => $formacao->dataConclusao,
                        'local' => $formacao->instituicao,
                        'categoria' => $categoria,
                        'cancelado' => false,
                        'created_at' => $formacao->created_at,
                        'updated_at' => $formacao->updated_at,
                    ]);
                }

                DB::table('formacaoPolicial')
                    ->where('id', $formacao->id)
                    ->update(['curso_id' => $cursoId]);
            });

        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->dropColumn(self::LEGACY_COLUMNS);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('formacaoPolicial') || $this->hasAllLegacyColumns()) {
            return;
        }

        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->string('instituicao')->nullable();
            $table->string('curso')->nullable();
            $table->date('dataInicio')->nullable();
            $table->date('dataConclusao')->nullable();
        });

        DB::table('formacaoPolicial')
            ->whereNotNull('curso_id')
            ->orderBy('id')
            ->each(function ($formacao): void {
                $curso = DB::table('cursos')->where('id', $formacao->curso_id)->first();
                if (! $curso) {
                    return;
                }

                DB::table('formacaoPolicial')
                    ->where('id', $formacao->id)
                    ->update([
                        'instituicao' => match ($curso->categoria) {
                            'superior' => 'acipol',
                            'medio' => 'esapol',
                            default => 'matalane',
                        },
                        'curso' => $curso->descricao,
                        'dataInicio' => $curso->dataInicio,
                        'dataConclusao' => $curso->dataFim,
                    ]);
            });
    }

    private function hasAllLegacyColumns(): bool
    {
        return collect(self::LEGACY_COLUMNS)
            ->every(fn (string $column): bool => Schema::hasColumn('formacaoPolicial', $column));
    }
};
