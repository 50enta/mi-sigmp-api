<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Escolaridade extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'escolaridades';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nivel',
        'instituicao',
        'curso',
        'dataInicio',
        'dataFim',
        'isConcluido',
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
