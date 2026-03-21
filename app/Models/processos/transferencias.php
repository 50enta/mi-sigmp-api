<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transferencias extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    protected $table = 'transferencias';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'TRF';

    protected $fillable = [
        'estado',
        'nrProcesso',
        'pessoa_id',
        'permutador',
        'regime',
        'origem',
        'destino',
        'abertoPor',
        'data',
        'systemId',
        'nrDespacho',
        'dataDespacho'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
