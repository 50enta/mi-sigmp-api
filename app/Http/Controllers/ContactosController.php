<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactosController extends Controller
{
    public function show(Pessoa $pessoa): JsonResponse
    {
        return response()->json([
            'contactos' => $this->serialize($pessoa->load('phoneNumbers')),
        ]);
    }

    public function update(Request $request, Pessoa $pessoa): JsonResponse
    {
        $emailInput = $request->input('email');
        if (is_string($emailInput)) {
            $request->merge(['email' => trim($emailInput) ?: null]);
        }

        $telefonesInput = $request->input('telefones');
        if (is_array($telefonesInput)) {
            $request->merge([
                'telefones' => collect($telefonesInput)
                    ->filter(fn ($telefone) => is_string($telefone))
                    ->map(fn ($telefone) => trim($telefone))
                    ->filter()
                    ->values()
                    ->all(),
            ]);
        }

        $emailRule = Rule::unique('pessoas', 'email')->ignore($pessoa->id);
        $phoneRule = Rule::unique('pessoa_telefones', 'numero')
            ->where(fn ($query) => $query->where('pessoa_id', '!=', $pessoa->id));

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255', $emailRule],
            'telefones' => ['required', 'array', 'min:1'],
            'telefones.0' => ['required', 'string', 'max:50'],
            'telefones.*' => ['string', 'max:50', 'distinct', $phoneRule],
        ], [
            'email.unique' => 'Este email já está registado.',
            'telefones.*.distinct' => 'O mesmo número de contacto não pode ser repetido.',
            'telefones.*.unique' => 'Este número de contacto já está registado.',
        ]);

        $telefones = collect($validated['telefones'])
            ->map(fn ($telefone) => trim((string) $telefone))
            ->filter()
            ->values()
            ->all();

        $pessoa = DB::transaction(function () use ($pessoa, $validated, $telefones): Pessoa {
            $pessoa->update([
                'email' => filled($validated['email'] ?? null) ? trim($validated['email']) : null,
            ]);
            $pessoa->phoneNumbers()->delete();
            $pessoa->phoneNumbers()->createMany(
                collect($telefones)->map(fn (string $numero, int $ordem) => [
                    'numero' => $numero,
                    'ordem' => $ordem,
                ])->all(),
            );

            return $pessoa->load('phoneNumbers');
        });

        return response()->json([
            'success' => 'Contactos actualizados com sucesso.',
            'contactos' => $this->serialize($pessoa),
        ]);
    }

    private function serialize(Pessoa $pessoa): array
    {
        return [
            'id' => $pessoa->id,
            'email' => $pessoa->email,
            'telefones' => $pessoa->phoneNumbers->pluck('numero')->values()->all(),
        ];
    }
}
