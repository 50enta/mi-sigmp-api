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
        'aprovado',
        'nip',
        'isGerivel',
        'nomeCompleto',
        'nomeMae',
        'nomePai',
        'dataNasc',
        'nuit',
        'estadoCivil',
        'grupoSangue',
        'distrito',
        'provincia',
        'residencia',
        'genero',
        'BI',
        'altura',
        'linguas',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

}
