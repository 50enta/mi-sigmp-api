<?php

namespace App\Models\Processos;

use App\Models\Pessoa;
use App\Traits\HasProcessNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ferias extends Model
{
    use HasFactory, HasProcessNumber, SoftDeletes;

    protected $table = 'ferias';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $processPrefix = 'FER';

    protected $fillable = [
        'nrProcesso',
        'estado',
        'pessoa_id',
        'dataInicio',
        'dataFim',
        'nrDespacho',
        'dataDespacho',
        'despacho',
        'diasFerias',
        'saldoAntes',
        'saldoDepois',
        'observacoes',
        'abertoPor',
    ];

    protected $casts = [
        'dataInicio' => 'date:Y-m-d',
        'dataFim' => 'date:Y-m-d',
        'dataDespacho' => 'date:Y-m-d',
        'diasFerias' => 'integer',
        'saldoAntes' => 'integer',
        'saldoDepois' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $ferias) {
            $ferias->id ??= (string) Str::uuid();
        });
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
