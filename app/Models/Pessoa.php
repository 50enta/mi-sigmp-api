<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Pessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pessoas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'dataNasc',
        'nuit',
        'estadoCivil',
        'sexo',
        'bi',
        'distrito',
        'provincia',
        'residencia',
        'grupoSangue',
        'nrProcesso',
        'situacaoDisciplinar',
        'situacao',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

}
