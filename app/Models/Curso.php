<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Curso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cursos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $attributes = ['cancelado' => false];

    protected $appends = ['estado'];

    protected $fillable = [
        'id',
        'descricao',
        'dataInicio',
        'especialidade',
        'dataFim',
        'numero',
        'grau',
        'local',
        'categoria',
        'numero_despacho',
        'documento_despacho',
        'total_esperado',
        'cancelado',
    ];

    protected function casts(): array
    {
        return [
            'dataInicio' => 'date:Y-m-d',
            'dataFim' => 'date:Y-m-d',
            'total_esperado' => 'integer',
            'cancelado' => 'boolean',
        ];
    }

    public function getEstadoAttribute(): string
    {
        if ($this->cancelado) {
            return 'cancelado';
        }

        return $this->dataFim?->isBefore(today()) ? 'finalizado' : 'em_curso';
    }

    public function formacoesPoliciais(): HasMany
    {
        return $this->hasMany(FormacaoPolicial::class, 'curso_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
