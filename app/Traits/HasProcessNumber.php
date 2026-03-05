<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasProcessNumber
{
    /**
     * Gera automaticamente o nrProcesso ao criar o registro
     * 
     * @param string $prefix Prefixo do processo (ex: 'GP')
     */
    public static function bootHasProcessNumber()
    {
        static::creating(function ($model) {
            if (empty($model->nrProcesso)) {
                $year = date('Y');
                $prefix = property_exists($model, 'processPrefix') ? $model->processPrefix : 'XX';

                // pega último nrProcesso do mesmo prefixo e ano
                $last = DB::table($model->getTable())
                    ->where('nrProcesso', 'like', "$prefix-%/$year")
                    ->orderBy('nrProcesso', 'desc')
                    ->first();

                if ($last) {
                    preg_match('/' . $prefix . '-(\d+)\/' . $year . '/', $last->nrProcesso, $matches);
                    $number = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
                } else {
                    $number = 1;
                }

                // formata 0001
                $model->nrProcesso = sprintf('%s-%04d/%s', $prefix, $number, $year);
            }
        });
    }
}
