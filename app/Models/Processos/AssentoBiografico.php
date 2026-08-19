<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssentoBiografico extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    protected $table = 'assentos_biograficos';
    protected $processPrefix = 'AB';

    protected $fillable = [
        'estado', 'systemId', 'nrProcesso', 'pessoa_id', 'tipoRegisto',
        'dataRegisto', 'descricao', 'documento', 'abertoPor',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
