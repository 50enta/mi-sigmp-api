<?php

namespace App\Http\Controllers;

use App\Models\Contactos;
use App\Models\Pessoa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactosController extends Controller
{
    public function show(Pessoa $pessoa): JsonResponse
    {
        return response()->json([
            'contactos' => $this->serialize($this->findForPessoa($pessoa)),
        ]);
    }

    public function update(Request $request, Pessoa $pessoa): JsonResponse
    {
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

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'telefones' => ['required', 'array', 'min:1'],
            'telefones.0' => ['required', 'string', 'max:50'],
            'telefones.*' => ['string', 'max:50', 'distinct'],
        ]);

        $telefones = collect($validated['telefones'])
            ->map(fn ($telefone) => trim((string) $telefone))
            ->filter()
            ->values()
            ->all();

        $contactos = $this->findForPessoa($pessoa) ?? new Contactos;
        $contactos->fill([
            'pessoa_id' => $pessoa->id,
            'nip' => $pessoa->nip,
            'email' => filled($validated['email'] ?? null) ? trim($validated['email']) : null,
            'telefones' => $telefones,
            'contactoPrincipal' => $telefones[0] ?? null,
            'contactoAlternativo' => $telefones[1] ?? null,
            'contactoEmergencia' => $telefones[2] ?? null,
        ])->save();

        return response()->json([
            'success' => 'Contactos actualizados com sucesso.',
            'contactos' => $this->serialize($contactos),
        ]);
    }

    private function findForPessoa(Pessoa $pessoa): ?Contactos
    {
        return Contactos::query()
            ->where('pessoa_id', $pessoa->id)
            ->when($pessoa->nip, fn ($query) => $query->orWhere('nip', $pessoa->nip))
            ->first();
    }

    private function serialize(?Contactos $contactos): array
    {
        return [
            'id' => $contactos?->id,
            'email' => $contactos?->email,
            'telefones' => $contactos?->telefones ?? array_values(array_filter([
                $contactos?->contactoPrincipal,
                $contactos?->contactoAlternativo,
                $contactos?->contactoEmergencia,
            ])),
        ];
    }
}
