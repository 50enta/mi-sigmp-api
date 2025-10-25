<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'locals';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'activo',
        'isLogico',
        'nivel',
        'descricao',
        'comentarios',
        'hasPai',
        'pai_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function parent()
    {
        return $this->belongsTo(Local::class, 'pai_id');
    }

    public function children()
    {
        return $this->hasMany(Local::class, 'pai_id');
    }
}
