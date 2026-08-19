<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->uuid('curso_id')->nullable()->after('curso');
            $table->foreign('curso_id')->references('id')->on('cursos')->nullOnDelete();
        });

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

                if ($cursoId) {
                    DB::table('formacaoPolicial')
                        ->where('id', $formacao->id)
                        ->update(['curso_id' => $cursoId]);
                }
            });

        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->dropColumn(['instituicao', 'curso', 'dataInicio', 'dataConclusao']);
        });
    }

    public function down(): void
    {
        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->string('instituicao')->nullable();
            $table->string('curso')->nullable();
            $table->date('dataInicio')->nullable();
            $table->date('dataConclusao')->nullable();
        });

        Schema::table('formacaoPolicial', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);
            $table->dropColumn('curso_id');
        });
    }
};
