<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubsideosFunebres extends Model
{
    use HasFactory, HasProcessNumber;

    public $incrementing = false;
    protected $table = 'subsideos_funebres';
    protected $keyType = 'string';
    protected $processPrefix = 'SF';

    protected $fillable = [
        'nrProcesso',
        'systemId',
        'pessoa_id',
        'abertoPor',
        'beneficiarios',
        'tipoPensao',
        'documento',
        'referenciaDocumento',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
