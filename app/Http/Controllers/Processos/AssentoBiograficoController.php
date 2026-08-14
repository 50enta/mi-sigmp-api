<?php

namespace App\Http\Controllers\Processos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Processos\AssentoBiograficoRequest;
use App\Models\Processos\AssentoBiografico;
use Illuminate\Http\Request;

class AssentoBiograficoController extends Controller
{
    public function stats(Request $request)
    {
        $stats = AssentoBiografico::query()
            ->selectRaw('COUNT(*) as total, SUM(estado = "aberto") as aberto, SUM(estado = "fechado") as fechado')
            ->whereYear('created_at', $request->input('year', date('Y')))
            ->first();

        return response()->json(['data' => $stats], 200);
    }

    public function index(Request $request)
    {
        $query = AssentoBiografico::query()
            ->select('assentos_biograficos.*', 'p.nomeCompleto as pessoaNome', 'ab.nomeCompleto as abertoPorNome')
            ->leftJoin('pessoas as p', 'p.id', '=', 'assentos_biograficos.pessoa_id')
            ->leftJoin('pessoas as ab', 'ab.id', '=', 'assentos_biograficos.abertoPor')
            ->when($request->input('nomeAgente'), fn ($q, $value) => $q->where('p.nomeCompleto', 'like', "%{$value}%"))
            ->when($request->input('nip'), fn ($q, $value) => $q->where('p.nip', 'like', "%{$value}%"))
            ->when($request->input('nrProcesso'), fn ($q, $value) => $q->where('assentos_biograficos.nrProcesso', 'like', "%{$value}%"))
            ->when($request->input('systemId'), fn ($q, $value) => $q->where('assentos_biograficos.systemId', 'like', "%{$value}%"))
            ->when($request->input('createdAt'), fn ($q, $value) => $q->whereDate('assentos_biograficos.created_at', $value))
            ->orderByDesc('assentos_biograficos.created_at');

        $pageSize = $request->input('per_page', $request->input('pageSize', 10));
        $records = $query->paginate($pageSize, ['*'], 'page', $request->input('page', 1));

        return response()->json(['data' => $records], 200);
    }

    public function newProcess(AssentoBiograficoRequest $request)
    {
        $data = $request->validated();
        $data['pessoa_id'] = $data['pessoa_id'][0];
        $data['estado'] = 'fechado';

        if ($request->hasFile('documento')) {
            $filename = time() . '_' . $request->file('documento')->getClientOriginalName();
            $request->file('documento')->move(public_path('uploads'), $filename);
            $data['documento'] = $filename;
        }

        $record = AssentoBiografico::create($data);

        return response()->json(['success' => 'Assento biográfico criado com sucesso!', 'data' => $record], 201);
    }

    public function show(string $id)
    {
        $record = AssentoBiografico::query()
            ->with('pessoa')
            ->findOrFail($id);

        return response()->json(['data' => $record], 200);
    }
}
