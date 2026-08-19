<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FormacaoPolicial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formacaoPolicial';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pessoa_id',
        'curso_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            $model->id ??= (string) Str::uuid();
        });
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }
}
