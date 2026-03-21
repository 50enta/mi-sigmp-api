<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrecaoDeDados extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    protected $table = 'correcao_de_dados';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'CD';

    protected $fillable = [
        'id',
        'motivoEobs',
        'dataNasc',
        'novoNome',
        'comprovativo',
        'pessoa_id',
        'abertoPor',
        'nrProcesso',
        'systemId',
        'tipoCorrecao'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
