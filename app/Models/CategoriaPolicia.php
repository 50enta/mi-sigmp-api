<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CategoriaPolicia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categoria_policias';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'categoria_id',
        'pessoa_id',
        'despacho',
        'nrDespacho',
        'dataDespacho',
        'dataInicio',
        'dataFim',
        'obs',
        'nrProcesso',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
    
}
