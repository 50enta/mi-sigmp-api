<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reafetacao extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'RF';

    protected $fillable = [
        'estado',
        'nrProcesso',
        'pessoa_id',
        'despacho',
        'motivo',
        'origem',
        'destino',
        'abertoPor',
        'data',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
