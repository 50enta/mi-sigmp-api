<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EscalaoPolicia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'escalao_policias';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'activo',
        'escala_id',
        'pessoa_id',
        'despacho',
        'dataInicio',
        'dataFim',
        'obs',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function escala()
    {
        return $this->belongsTo(Escala::class, 'escala_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
