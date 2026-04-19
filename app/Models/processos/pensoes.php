<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pensoes extends Model
{
    use HasFactory, HasProcessNumber;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'GPR';

    protected $fillable = [
        'estado',
        'nrProcesso',
        'systemId',
        'nrDespacho',
        'dataDespacho',
        'pessoa_id',
        'despacho',
        'motivo',
        'abertoPor',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
