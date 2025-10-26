<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SituacaoDisciplinar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'situacao_disciplinars';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'activo',
        'nrProcesso',
        'pessoa_id',
        'local',
        'proposta',
        'abertoPor',
        'dataAbertura',
        'fechadoPor',
        'dataFecho',
        'obsFecho',
        'aprovado',
        'aprovador',
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
