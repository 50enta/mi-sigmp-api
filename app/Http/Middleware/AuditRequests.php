<?php

namespace App\Http\Middleware;

use App\Models\AuditSetting;
use App\Models\AuditTrail;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuditRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // Uploaded files may be moved by the controller, which removes PHP's
        // temporary file. Capture only safe metadata before calling $next.
        try {
            $auditContext = [
                'pessoa_id' => $request->user()?->pessoa_id,
                'input' => $this->sanitizeInput($request->input()),
                'files' => $this->fileNames($request->allFiles()),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];
        } catch (\Throwable $exception) {
            report($exception);
            $auditContext = [
                'pessoa_id' => $request->user()?->pessoa_id,
                'input' => [],
                'files' => [],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];
        }

        $response = $next($request);

        // Auditing must never make an otherwise valid authenticated request fail.
        // This also keeps login usable while a new audit migration is being deployed.
        try {
            $settings = AuditSetting::current();
        } catch (\Throwable $exception) {
            report($exception);

            return $response;
        }

        $isLogin = $request->is('api/login');
        $event = $isLogin ? 'login' : $this->eventFor($request->method());

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

        $settingKey = $isLogin ? 'log_logins' : "log_{$event}";

        if (!$settings->enabled || !$event || !$settings->{$settingKey} || $request->is('api/audit*')) {
            return $response;
        }

        try {
            $pessoaId = $auditContext['pessoa_id'];
            if ($isLogin && $request->filled('email')) {
                $pessoaId = User::query()
                    ->where('email', $request->input('email'))
                    ->value('pessoa_id');
            }

            AuditTrail::create([
                'pessoa_id' => $pessoaId,
                'info' => $isLogin
                    ? ($response->isSuccessful() ? 'Início de sessão bem-sucedido' : 'Tentativa de início de sessão falhada')
                    : strtoupper($request->method()) . ' ' . $request->path(),
                'status' => $response->isSuccessful() ? 'sucesso' : 'falha',
                'event' => rtrim($event, 's'),
                'method' => $request->method(),
                'path' => '/' . $request->path(),
                'ip_address' => $settings->capture_ip ? $auditContext['ip_address'] : null,
                'user_agent' => $settings->capture_user_agent ? $auditContext['user_agent'] : null,
                'http_status' => $response->getStatusCode(),
                'metadata' => [
                    'input' => $auditContext['input'],
                    'files' => $auditContext['files'],
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $response;
    }

    private function sanitizeInput(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && in_array(strtolower($key), ['password', 'password_confirmation', 'token'], true)) {
            return '[REDACTED]';
        }

        if (!is_array($value)) {
            return $value;
        }

        $sanitized = [];
        foreach ($value as $itemKey => $itemValue) {
            $sanitized[$itemKey] = $this->sanitizeInput($itemValue, (string) $itemKey);
        }

        return $sanitized;
    }

    private function fileNames(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return $value->getClientOriginalName();
        }

        if (!is_array($value)) {
            return $value;
        }

        return array_map(fn ($item) => $this->fileNames($item), $value);
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
