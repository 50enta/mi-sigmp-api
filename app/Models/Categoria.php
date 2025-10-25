<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'descricao',
        'comentarios',
        'activo',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
