<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contactos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contactos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pessoa_id',
        'nip',
        'email',
        'telefones',
        'contactoPrincipal',
        'contactoAlternativo',
        'contactoEmergencia',
    ];

    protected function casts(): array
    {
        return [
            'telefones' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
