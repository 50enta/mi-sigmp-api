<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PessoaTelefone extends Model
{
    protected $table = 'pessoa_telefones';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pessoa_id',
        'numero',
        'ordem',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    protected static function booted(): void
    {
        static::creating(function (PessoaTelefone $telefone): void {
            $telefone->id ??= (string) Str::uuid();
        });
    }
}
