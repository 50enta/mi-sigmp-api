<?php

namespace App\Http\Controllers;

use App\Http\Requests\localAfetosRequest;
use App\Models\LocalAfecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalAfectoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $locais = DB::table('local_afectos')
                ->join('locals', 'local_afectos.local_id', '=', 'locals.id')
                ->orderBy('local_afectos.dataInicio', 'desc')
                ->where('pessoa_id', $request->query('pessoa_id'))
                ->select('*')
                ->get();

            return response()->json(['locais' => $locais], 200);
        } catch (\Throwable $th) {

            return response()->json(['error' => 'Error inesperado'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(localAfetosRequest $request)
    {

        try {
            DB::beginTransaction();
            $data = $request->input('localEfuncoes');

            if ($request->file('localEfuncoes.despacho')) {
                $filename = time().'_'.$request->file('localEfuncoes.despacho')->getClientOriginalName();
                $request->file('localEfuncoes.despacho')->move(public_path('uploads'), $filename);
                $data['despacho'] = $filename;
            }

            LocalAfecto::create($data);

            $pessoa = new PessoaController();
            $pessoa->updateStep(4, $request['localEfuncoes']['pessoa_id']);

            DB::commit();

            return response()->json(['success' => true], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Error inesperado'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $afeto = LocalAfecto::findOrFail($id);
        $afeto->delete();

        return response()->json(['message' => 'Afectação eliminada com sucesso!'], 200);
    }
}
