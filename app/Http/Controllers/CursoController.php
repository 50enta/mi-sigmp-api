<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = min(max((int) $request->input('pageSize', 10), 1), 100);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Curso::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->string('categoria'));
        }

        $estado = $request->input('estado');
        if ($estado === 'cancelado') {
            $query->where('cancelado', true);
        } elseif ($estado === 'finalizado') {
            $query->where('cancelado', false)->whereDate('dataFim', '<', today());
        } elseif ($estado === 'em_curso') {
            $query->where('cancelado', false)->whereDate('dataFim', '>=', today());
        } elseif ($request->has('cancelado')) {
            $query->where('cancelado', $request->boolean('cancelado'));
        }

        $query->orderByDesc('dataInicio');

        $cursos = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->get();

        return response()->json(['cursos' => $cursos], 200);
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
        $validation = Validator::make($request->all(), [
            'descricao' => 'required|string',
            'dataInicio' => 'required|date',
            'especialidade' => 'nullable|string',
            'dataFim' => 'required|date|after_or_equal:dataInicio',
            'categoria' => ['required', Rule::in(['basico', 'medio', 'superior'])],
            'numero_despacho' => 'required|string|max:100',
            'documento_despacho' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'local' => 'required|string|max:255',
            'total_esperado' => 'nullable|integer|min:0',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar o curso', 'errors' => $validation->errors()], 409);
        }

        $data = $validation->validated();
        $data['documento_despacho'] = $this->storeDocumento($request);
        $curso = Curso::create($data);

        return response()->json(['message' => 'Curso criado com sucesso!', 'curso' => $curso], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $curso = Curso::findOrFail($id);

        return response()->json($curso, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'descricao' => 'required|string',
            'dataInicio' => 'required|date',
            'especialidade' => 'nullable|string',
            'dataFim' => 'required|date|after_or_equal:dataInicio',
            'categoria' => ['required', Rule::in(['basico', 'medio', 'superior'])],
            'numero_despacho' => 'required|string|max:100',
            'documento_despacho' => [Rule::requiredIf(! $curso->documento_despacho), 'nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:10240'],
            'local' => 'required|string|max:255',
            'total_esperado' => 'nullable|integer|min:0',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar o curso', 'errors' => $validation->errors()], 409);
        }

        $data = $validation->validated();
        if ($request->hasFile('documento_despacho')) {
            $data['documento_despacho'] = $this->storeDocumento($request);
        } else {
            unset($data['documento_despacho']);
        }
        $curso->update($data);

        return response()->json(['message' => 'Curso actualizado com sucesso!', 'curso' => $curso], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return response()->json(['message' => 'Curso eliminado com sucesso!'], 200);
    }

    public function cancel($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->update(['cancelado' => true]);

        return response()->json(['message' => 'Curso cancelado com sucesso!', 'curso' => $curso], 200);
    }

    public function cancelled(Request $request)
    {
        $request->merge(['estado' => 'cancelado']);

        return $this->index($request);
    }

    private function storeDocumento(Request $request): string
    {
        $file = $request->file('documento_despacho');
        $filename = uniqid('curso_despacho_', true).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $filename);

        return $filename;
    }
}
