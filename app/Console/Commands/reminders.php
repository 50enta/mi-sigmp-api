<?php

namespace App\Console\Commands;

use App\Http\Controllers\Common\Utils;
use App\Http\Controllers\mailController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class reminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dentroDoPrazo = [];
        $foraDoPrazo = [];
        $utils = new Utils();
        $diasDeFecho = DB::table('closeconfig')
            ->select('*')
            ->get();

        $pendingData = DB::table('nearmisses')
            ->select('id', 'hash', 'descricao', 'local', 'responsaveis', 'classe', 'dataAcontecimento')
            ->where('estado', '1')
            ->get();

        $locais = DB::table('locais')
            ->select('*')
            ->get();

        foreach ($pendingData as $key => $value) {
            $dias = $diasDeFecho[0]->{'classe' . $value->classe};
            $startDate = Carbon::parse($value->dataAcontecimento);
            $daysPassed = $startDate->diffInDays(Carbon::now(), false);
            $endDate = $startDate->copy()->addDays($dias);
            $id = $value->local;

            $localName = optional($locais->first(function ($local) use ($id) {
                return $local->id == $id;
            }))->local;

            if ($daysPassed <= $dias) {
                $emails = $utils->getEmails('nearmisses', 'near_miss', $value->id);

                foreach ($emails['responsaveis'] as $key => $email) {
                    $dentroDoPrazo[$email][] = [
                        "descricao" => $value->descricao,
                        "local" => $localName,
                        "dataAcontecimento" => $value->dataAcontecimento,
                        "hash" => $value->hash,
                        'dataFim' => $endDate
                    ];
                }
            } else if ($daysPassed > $dias) {
                $emails = $utils->getEmails('nearmisses', 'near_miss', $value->id);

                foreach ($emails['responsaveis'] as $key => $email) {
                    $foraDoPrazo[$email][] = [
                        "descricao" => $value->descricao,
                        "local" => $localName,
                        "dataAcontecimento" => $value->dataAcontecimento,
                        "hash" => $value->hash,
                        'dataFim' => $endDate
                    ];
                }
            }
        }

        $mailController = new mailController();
        $teste['valter.sitoe@uem.ac.mz'][] = [
            "descricao" => 'Teste',
            "local" => 'Teste',
            "dataAcontecimento" => 'sem data',
            "hash" => 'no hash',
            'dataFim' => ''
        ];

        $teste2['edmilsonnhabinde5@gmail.com'][] = [
            "descricao" => 'Teste',
            "local" => 'Teste',
            "dataAcontecimento" => 'sem data',
            "hash" => 'no hash',
            'dataFim' => ''
        ];

        try {
            $mailController->reportsGen($dentroDoPrazo, 'Segue abaixo um relatório dos near misses registados e sem nenhum desfecho');
            $mailController->reportsGen($foraDoPrazo, 'Segue abaixo um relatório dos near misses registados, sem nenhum desfecho e com a data de fecho expirada!');
            $mailController->reportsGen($teste, 'Segue abaixo um relatório dos near misses registados, sem nenhum desfecho e com a data de fecho expirada!');
            $mailController->reportsGen($teste2, 'Segue abaixo um relatório dos near misses registados, sem nenhum desfecho e com a data de fecho expirada!');
        } catch (\Exception $e) {
            Log::error('Erro ao enviar relatório: ' . $e->getMessage());
        }
    }
}
