<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssentoBiograficoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $pessoaId = '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab01';
        $localAnteriorId = '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab02';
        $localActualId = '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab03';

        DB::transaction(function () use ($now, $pessoaId, $localAnteriorId, $localActualId) {
            DB::table('pessoas')->updateOrInsert(
                ['nip' => 'AB-TESTE-001'],
                [
                    'id' => $pessoaId,
                    'aprovado' => 1,
                    'isGerivel' => true,
                    'nomeCompleto' => 'Amélia Esperança Macamo',
                    'nomeMae' => 'Rosa Esperança Macamo',
                    'nomePai' => 'Joaquim Alberto Macamo',
                    'dataNasc' => '1988-04-17',
                    'nuit' => '123456789',
                    'estadoCivil' => 'Casado',
                    'grupoSangue' => 'O+',
                    'distrito' => 'KaMpfumo',
                    'provincia' => 'Maputo Cidade',
                    'residencia' => 'Bairro da Coop, Maputo',
                    'genero' => 'Feminino',
                    'BI' => '110100123456A',
                    'altura' => 1.68,
                    'linguas' => 'Português, Xichangana e Inglês',
                    'stepFinished' => 4,
                    'created_at' => '2010-02-01 08:00:00',
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]
            );

            DB::table('locals')->updateOrInsert(
                ['id' => $localAnteriorId],
                ['activo' => true, 'nivel' => 2, 'nome' => 'Comando Provincial de Gaza', 'code' => 'CP-GAZA-TESTE', 'description' => 'Unidade de teste para assento biográfico', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null]
            );
            DB::table('locals')->updateOrInsert(
                ['id' => $localActualId],
                ['activo' => true, 'nivel' => 2, 'nome' => 'Comando da Cidade de Maputo', 'code' => 'CC-MPM-TESTE', 'description' => 'Unidade actual do agente de teste', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null]
            );

            $this->replaceHistory('situacao_pessoas', $pessoaId, [[
                'id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab04', 'situacao' => 'Activo',
                'nrProcesso' => 'SIT/001/2010', 'obs' => 'Agente no activo e em efectividade de funções.',
            ]], $now);

            $this->replaceHistory('categoria_policias', $pessoaId, [
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab05', 'activo' => false, 'categoria_id' => '1', 'nrProcesso' => 'PROM/014/2010', 'nrDespacho' => '12/CGPRM/2010', 'dataInicio' => '2010-02-01', 'dataFim' => '2018-06-30', 'obs' => 'Ingresso na carreira como Guarda.'],
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab06', 'activo' => true, 'categoria_id' => '3', 'nrProcesso' => 'PROM/087/2018', 'nrDespacho' => '87/CGPRM/2018', 'dataInicio' => '2018-07-01', 'dataFim' => null, 'obs' => 'Promovida à categoria de Sargento Chefe por mérito.'],
            ], $now);

            $this->replaceHistory('especialidade_pessoas', $pessoaId, [
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab07', 'activo' => false, 'especialidade_id' => 'pp', 'nrProcesso' => 'ESP/010/2010', 'dataInicio' => '2010-02-01', 'dataFim' => '2015-12-31', 'obs' => 'Polícia de Protecção.'],
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab08', 'activo' => true, 'especialidade_id' => 'transito', 'nrProcesso' => 'ESP/023/2016', 'dataInicio' => '2016-01-01', 'dataFim' => null, 'obs' => 'Especialização em Polícia de Trânsito.'],
            ], $now);

            $this->replaceHistory('local_afectos', $pessoaId, [
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab09', 'local_id' => $localAnteriorId, 'cargo' => 'Agente de patrulha', 'dataInicio' => '2010-02-01', 'dataFim' => '2017-08-31', 'observacoes' => 'Serviço operacional e patrulhamento.'],
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab10', 'local_id' => $localActualId, 'cargo' => 'Chefe de brigada', 'dataInicio' => '2017-09-01', 'dataFim' => null, 'observacoes' => 'Coordenação de brigada de fiscalização rodoviária.'],
            ], $now);

            $this->replaceHistory('escolaridades', $pessoaId, [
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab11', 'nivel' => 'Licenciatura', 'instituicao' => 'UEM', 'curso' => 'Administração Pública', 'dataInicio' => '2014-02-01', 'dataFim' => '2018-11-30', 'certificado' => 'certificado-demo-licenciatura.pdf'],
                ['id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab12', 'nivel' => 'Mestrado', 'instituicao' => 'UJC', 'curso' => 'Segurança Pública', 'dataInicio' => '2021-02-01', 'dataFim' => '2023-12-15', 'certificado' => 'certificado-demo-mestrado.pdf'],
            ], $now);

            $cursoBasicoId = DB::table('cursos')->where('categoria', 'basico')->value('id');
            $this->replaceHistory('formacaoPolicial', $pessoaId, $cursoBasicoId ? [[
                'id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab13',
                'curso_id' => $cursoBasicoId,
            ]] : [], $now);

            $this->replaceHistory('formacoesComplementares', $pessoaId, [[
                'id' => '8fcb60d8-8ae6-4b54-bb9e-1e54aca0ab14', 'cursoComplementar' => 'Fiscalização e Segurança Rodoviária',
                'instituicao' => 'Escola Prática da PRM', 'anoConlusao' => '2020', 'certificado' => 'certificado-demo-seguranca-rodoviaria.pdf',
            ]], $now);
        });
    }

    private function replaceHistory(string $table, string $pessoaId, array $rows, $now): void
    {
        DB::table($table)->where('pessoa_id', $pessoaId)->delete();

        foreach ($rows as $row) {
            DB::table($table)->insert(array_merge($row, [
                'pessoa_id' => $pessoaId,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]));
        }
    }
}
