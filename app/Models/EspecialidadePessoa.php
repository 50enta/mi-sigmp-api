<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'activo',
        'despacho',
        'dataInicio',
        'dataFim',
        'isMudanca',
        'especialidadeAnterior',
        'especialidade_id',
        'pessoa_id',
        'obs',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function especialidade()
    {
        return $this->belongsTo(Especialidade::class, 'especialidade_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

}
