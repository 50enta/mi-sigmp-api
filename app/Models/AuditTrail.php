<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AuditTrail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'audit_trails';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pessoa_id',
        'info',
        'status',
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
        return $this->belongsTo(Pessoa::class, 'ipessoa_id');
    }

}
