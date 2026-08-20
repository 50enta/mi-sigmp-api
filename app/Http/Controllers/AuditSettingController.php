<?php

namespace App\Http\Controllers;

use App\Models\AuditSetting;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuditSettingController extends Controller
{
    public function show()
    {
        return response()->json(['data' => AuditSetting::current()], 200);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'log_reads' => ['required', 'boolean'],
            'log_creates' => ['required', 'boolean'],
            'log_updates' => ['required', 'boolean'],
            'log_deletes' => ['required', 'boolean'],
            'log_logins' => ['required', 'boolean'],
            'capture_ip' => ['required', 'boolean'],
            'capture_user_agent' => ['required', 'boolean'],
            'retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
        ]);

        $settings = AuditSetting::current();
        $before = $settings->only(array_keys($data));
        $settings->update($data);

        AuditTrail::create([
            'pessoa_id' => $request->user()?->pessoa_id,
            'info' => 'Configurações de auditoria actualizadas',
            'status' => 'sucesso',
            'event' => 'update',
            'method' => 'PUT',
            'path' => '/api/audit-settings',
            'ip_address' => $settings->capture_ip ? $request->ip() : null,
            'user_agent' => $settings->capture_user_agent ? $request->userAgent() : null,
            'http_status' => 200,
            'metadata' => ['before' => $before, 'after' => $settings->only(array_keys($data))],
        ]);

        return response()->json(['success' => 'Configurações de auditoria actualizadas.', 'data' => $settings], 200);
    }

    public function logs(Request $request)
    {
        $filters = $request->validate([
            'event' => ['nullable', 'in:read,create,update,delete,login'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $logs = AuditTrail::query()
            ->with('pessoa:id,nomeCompleto,nip')
            ->when($filters['event'] ?? null, fn ($q, $event) => $q->where('event', $event))
            ->when($filters['occurred_at'] ?? null, function ($q, $occurredAt) {
                $q->where('created_at', '>=', Carbon::parse($occurredAt));
            })
            ->when($request->input('search'), function ($q, $search) {
                $q->where(fn ($query) => $query
                    ->where('path', 'like', "%{$search}%")
                    ->orWhere('info', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json(['data' => $logs], 200);
    }
}
