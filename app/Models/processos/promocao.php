<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocao extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'PR0';
    
    protected $fillable = [
        'id',
        'despacho',
        'abertoPor',
        'modalidade',
        'data',
        'proposta',
        'pessoa_id',
        'nrProcesso'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
