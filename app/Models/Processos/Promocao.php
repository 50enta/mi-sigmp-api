<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocao extends Model
{
    use HasFactory, HasProcessNumber, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $processPrefix = 'PR0';

    protected $primaryKey = 'systemId';

    protected $fillable = [
        'id',
        'despacho',
        'abertoPor',
        'categoriaActual',
        'novaCategoria',
        'modalidade',
        'obs',
        'pessoa_id',
        'nrProcesso',
        'systemId',
        'nrDespacho',
        'dataDespacho',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
