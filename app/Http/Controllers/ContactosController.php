<?php

namespace App\Http\Controllers;

use App\Models\Contactos;
use Illuminate\Http\Request;


class ContactosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $page = $request->input('page', 1);
        $paging = filter_var($request->input('paging', true), FILTER_VALIDATE_BOOLEAN);

        $query = Contactos::query();

        if ($paging) {
            $contactos = $query->paginate($pageSize, ['*'], 'page', $page);
        } else {
            $contactos = $query->simplePaginate($pageSize, ['*'], 'page', $page);
        }

        return response()->json(['contactos' => $contactos], 200);
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
            'nip' => 'required|string',
            'contactoPrincipal' => 'nullable|string',
            'contactoAlternativo' => 'nullable|string',
            'contactoEmergencia' => 'nullable|string',
        ]);

        return Contactos::create([
            'id' => (string) Str::uuid(),
            $request->only([
                'nip',
                'contactoPrincipal',
                'contactoAlternativo',
                'contactoEmergencia',
            ]),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Contactos::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contactos $contactos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contacto = Contactos::findOrFail($id);

        $request->validate([
            'nip' => 'required|string',
            'contactoPrincipal' => 'nullable|string',
            'contactoAlternativo' => 'nullable|string',
            'contactoEmergencia' => 'nullable|string',
        ]);

        $contacto->update($request->all());

        return $contacto;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Contactos::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
