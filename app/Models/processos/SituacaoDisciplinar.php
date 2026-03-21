<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SituacaoDisciplinar extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    protected $table = 'gestao_disciplinars';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'GP';

    protected $fillable = [
        'estado',
        'nrProcesso',
        'systemId',
        'pessoa_id',
        'despacho',
        'proposta',
        'abertoPor',
        'dataDecisao',
        'decididoPor',
        'origem',
        'infraccao',
        'anexos',
        'nrDespacho',
        'dataDespacho'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
