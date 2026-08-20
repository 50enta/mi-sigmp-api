<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EspecialidadePessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'especialidade_pessoas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'especialidade_id',
        'pessoa_id',
        'despacho',
        'nrProcesso',
        'nrDespacho',
        'dataDespacho',
        'dataInicio',
        'dataFim',
        'obs',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id ??= (string) Str::uuid();
        });
    }

    public function especialidade(): BelongsTo
    {
        return $this->belongsTo(Especialidade::class, 'especialidade_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
