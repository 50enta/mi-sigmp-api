<?php

namespace App\Http\Middleware;

use App\Models\AuditSetting;
use App\Models\AuditTrail;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuditRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Auditing must never make an otherwise valid authenticated request fail.
        // This also keeps login usable while a new audit migration is being deployed.
        try {
            $settings = AuditSetting::current();
        } catch (\Throwable $exception) {
            report($exception);

            return $response;
        }

        $event = $this->eventFor($request->method());

        try {
            Cache::remember('audit-retention-cleanup', now()->addDay(), function () use ($settings) {
                DB::table('audit_trails')
                    ->where('created_at', '<', now()->subDays($settings->retention_days))
                    ->delete();
                return true;
            });
        } catch (\Throwable $exception) {
            report($exception);
        }

        if (!$settings->enabled || !$event || !$settings->{"log_{$event}"} || $request->is('api/audit*')) {
            return $response;
        }

        $payload = collect($request->except(['password', 'password_confirmation', 'token']))
            ->map(fn ($value) => $value instanceof \Illuminate\Http\UploadedFile ? $value->getClientOriginalName() : $value)
            ->all();

        try {
            AuditTrail::create([
                'pessoa_id' => $request->user()?->pessoa_id,
                'info' => strtoupper($request->method()) . ' ' . $request->path(),
                'status' => $response->isSuccessful() ? 'sucesso' : 'falha',
                'event' => rtrim($event, 's'),
                'method' => $request->method(),
                'path' => '/' . $request->path(),
                'ip_address' => $settings->capture_ip ? $request->ip() : null,
                'user_agent' => $settings->capture_user_agent ? $request->userAgent() : null,
                'http_status' => $response->getStatusCode(),
                'metadata' => ['input' => $payload],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $response;
    }

    private function eventFor(string $method): ?string
    {
        return match (strtoupper($method)) {
            'GET' => 'reads',
            'POST' => 'creates',
            'PUT', 'PATCH' => 'updates',
            'DELETE' => 'deletes',
            default => null,
        };
    }
}
