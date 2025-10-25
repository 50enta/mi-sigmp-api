<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PessoaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Pessoa::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dataNasc' => 'nullable|date',
            'nuit' => 'nullable|string',
            'estadoCivil' => 'nullable|string',
            'sexo' => 'nullable|string',
            'bi' => 'nullable|string',
            'distrito' => 'nullable|string',
            'provincia' => 'nullable|string',
            'residencia' => 'nullable|string',
            'grupoSangue' => 'nullable|string',
            'nrProcesso' => 'nullable|string',
            'situacaoDisciplinar' => 'nullable|string',
            'situacao' => 'nullable|string',
        ]);

        return Pessoa::create([
            'id' => (string) Str::uuid(),
            ...$request->only([
                'dataNasc', 'nuit', 'estadoCivil', 'sexo', 'bi',
                'distrito', 'provincia', 'residencia', 'grupoSangue',
                'nrProcesso', 'situacaoDisciplinar', 'situacao',
            ]),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Pessoa::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pessoa $pessoa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);
        $request->validate([
            'dataNasc' => 'nullable|date',
            'nuit' => 'nullable|string',
            'estadoCivil' => 'nullable|string',
            'sexo' => 'nullable|string',
            'bi' => 'nullable|string',
            'distrito' => 'nullable|string',
            'provincia' => 'nullable|string',
            'residencia' => 'nullable|string',
            'grupoSangue' => 'nullable|string',
            'nrProcesso' => 'nullable|string',
            'situacaoDisciplinar' => 'nullable|string',
            'situacao' => 'nullable|string',
        ]);

        $pessoa->update($request->all());

        return $pessoa;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Pessoa::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
