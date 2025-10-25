<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SituacaoPessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'situacao_pessoas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'activo',
        'situacao_id',
        'pessoa_id',
        'isMudanca',
        'despacho',
        'obs',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
