<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cursos';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'descricao',
        'dataInicio',
        'especialidade',
        'dataFim',
        'numero',
        'grau',
        'local',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
