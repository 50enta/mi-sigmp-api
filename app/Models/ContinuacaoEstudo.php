<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ContinuacaoEstudo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'continuacao_estudos';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'activo',
        'despacho',
        'instituicao',
        'curso',
        'nivelPretendido',
        'situacao',
        'dataInicio',
        'dataPrevisaoTermino',
        'obs',
        'pessoa_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
