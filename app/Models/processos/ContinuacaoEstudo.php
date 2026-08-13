<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ContinuacaoEstudo extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    protected $table = 'continuacao_estudos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'CE';

    protected $fillable = [
        'id',
        'despacho',
        'instituicao',
        'curso',
        'nivelPretendido',
        'pessoa_id',
        'abertoPor',
        'nrProcesso',
        'systemId',
        'nrDespacho',
        'dataDespacho',
        'data',
        'estado',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
