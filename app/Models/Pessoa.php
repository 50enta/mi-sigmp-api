<?php

namespace App\Models;

use App\Models\Processos\Ferias;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Pessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pessoas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'aprovado',
        'nip',
        'email',
        'isGerivel',
        'nomeCompleto',
        'nomeMae',
        'nomePai',
        'dataNasc',
        'nuit',
        'estadoCivil',
        'grupoSangue',
        'distrito',
        'provincia',
        'residencia',
        'genero',
        'BI',
        'altura',
        'linguas',
        'stepFinished',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaPolicia::class, 'pessoa_id');
    }

    public function categoriaAtual(): HasOne
    {
        return $this->hasOne(CategoriaPolicia::class, 'pessoa_id')
            ->ofMany(['dataInicio' => 'max'], fn (Builder $query) => $this->vigenteEm($query));
    }

    public function especialidades(): HasMany
    {
        return $this->hasMany(EspecialidadePessoa::class, 'pessoa_id');
    }

    public function especialidadeAtual(): HasOne
    {
        return $this->hasOne(EspecialidadePessoa::class, 'pessoa_id')
            ->ofMany(['dataInicio' => 'max'], fn (Builder $query) => $this->vigenteEm($query));
    }

    public function locaisAfectos(): HasMany
    {
        return $this->hasMany(LocalAfecto::class, 'pessoa_id');
    }

    public function localTrabalhoAtual(): HasOne
    {
        return $this->hasOne(LocalAfecto::class, 'pessoa_id')
            ->ofMany(['dataInicio' => 'max'], fn (Builder $query) => $this->vigenteEm($query));
    }

    public function situacoes(): HasMany
    {
        return $this->hasMany(SituacaoPessoa::class, 'pessoa_id');
    }

    public function situacaoAtual(): HasOne
    {
        return $this->hasOne(SituacaoPessoa::class, 'pessoa_id')
            ->latestOfMany('created_at');
    }

    public function feriasAtuais(): HasOne
    {
        $hoje = today()->toDateString();

        return $this->hasOne(Ferias::class, 'pessoa_id')
            ->whereDate('dataInicio', '<=', $hoje)
            ->whereDate('dataFim', '>=', $hoje)
            ->latestOfMany('dataInicio');
    }

    public function cursosPoliciais(): HasMany
    {
        return $this->hasMany(CursoPolicia::class, 'pessoa_id');
    }

    public function formacoesPoliciais(): HasMany
    {
        return $this->hasMany(FormacaoPolicial::class, 'pessoa_id');
    }

    public function phoneNumbers(): HasMany
    {
        return $this->hasMany(PessoaTelefone::class, 'pessoa_id')->orderBy('ordem');
    }

    private function vigenteEm(Builder $query): void
    {
        $hoje = today()->toDateString();

        $query
            ->whereDate('dataInicio', '<=', $hoje)
            ->where(function (Builder $query) use ($hoje) {
                $query
                    ->whereNull('dataFim')
                    ->orWhereDate('dataFim', '>', $hoje);
            });
    }
}
