<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = AuditTrail::query();

        $auditTrails = $paging
            ? $query->paginate($pageSize, ['*'], 'page', $page)
            : $query->simplePaginate($pageSize, ['*'], 'page', $page);

        return response()->json(['audit_trails' => $auditTrails], 200);
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
            'pessoa_id' => 'required|exists:pessoas,id',
            'info' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao criar', 'errors' => $validation->errors()], 409);
        }

        $auditTrail = AuditTrail::create($validation->validated());

        return response()->json(['message' => 'Registado com sucesso!', 'audit_trail' => $auditTrail], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $auditTrail = AuditTrail::findOrFail($id);
        return response()->json($auditTrail, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditTrail $auditTrail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $auditTrail = AuditTrail::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'pessoa_id' => 'required|exists:pessoas,id',
            'info' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['message' => 'Erro ao actualizar o registo de auditoria', 'errors' => $validation->errors()], 409);
        }

        $auditTrail->update($validation->validated());

        return response()->json(['message' => 'Registo actualizado com sucesso!', 'audit_trail' => $auditTrail], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $auditTrail = AuditTrail::findOrFail($id);
        $auditTrail->delete();

        return response()->json(['message' => 'Registo eliminado com sucesso!'], 200);
    }
}
