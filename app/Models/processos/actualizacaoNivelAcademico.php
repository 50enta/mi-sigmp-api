<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActualizacaoNivelAcademico extends Model
{
    use HasFactory, SoftDeletes, HasProcessNumber;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $processPrefix = 'ANA';

    protected $fillable = [
        'id',
        'certificado',
        'instituicao',
        'curso',
        'nivel',
        'dataDeConclusao',
        'pessoa_id',
        'abertoPor',
        'systemId',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
