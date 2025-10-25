<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LocalAfecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'local_afectos';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'local_id',
        'pessoa_id',
        'despacho',
        'dataInicio',
        'dataFim',
        'isTransferencia',
        'transferidor_id',
        'aprovador',
        'aprovado',
        'local_origem',
        'regime',
        'permutador',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

}
