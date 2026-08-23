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

    protected $casts = [
        'activo' => 'boolean',
        'has_children' => 'boolean',
    ];

    private ?string $previousParentId = null;

    protected $fillable = [
        'id',
        'activo',
        'nivel',
        'description',
        'parent_id',
        'code',
        'nome'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });

        static::created(function (Local $local) {
            static::refreshHasChildren($local->parent_id);
        });

        static::updating(function (Local $local) {
            if ($local->isDirty('parent_id')) {
                $local->previousParentId = $local->getOriginal('parent_id');
            }
        });

        static::updated(function (Local $local) {
            if (!$local->wasChanged('parent_id')) {
                return;
            }

            static::refreshHasChildren($local->previousParentId);
            static::refreshHasChildren($local->parent_id);
            $local->previousParentId = null;
        });

        static::deleted(function (Local $local) {
            static::refreshHasChildren($local->parent_id);
        });

        static::restored(function (Local $local) {
            static::refreshHasChildren($local->parent_id);
        });
    }

    private static function refreshHasChildren(?string $parentId): void
    {
        if (!$parentId) {
            return;
        }

        $hasChildren = static::query()
            ->where('parent_id', $parentId)
            ->exists();

        // A bulk update intentionally bypasses model events: changing this
        // derived flag must not trigger another hierarchy recalculation.
        static::query()
            ->whereKey($parentId)
            ->update(['has_children' => $hasChildren]);
    }

    public function parent()
    {
        return $this->belongsTo(Local::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Local::class, 'parent_id');
    }
}
