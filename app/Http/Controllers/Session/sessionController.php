<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Http\Controllers\mailController;
use App\Http\Utils\User\generatePassword;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class sessionController extends Controller
{
    public function endpointTest(): JsonResponse
    {
        return response()->json(['message' => 'Endpoint testado com sucesso!']);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'activo' => true])) {
            return response()->json(['message' => 'Email ou senha invalidos!'], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function requestPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            $user = User::query()->where('email', $request->string('email'))->first();

            if ($user) {
                $passwordGenerator = new generatePassword;
                $temporaryPassword = $passwordGenerator->returnRandomString();
                $mail = new mailController;

                if ($mail->passwordReset($user->email, $temporaryPassword) === 1) {
                    $user->forceFill([
                        'password' => Hash::make($temporaryPassword),
                        'ja_acedeu' => false,
                    ])->save();
                }
            }

            // Keep this response identical whether or not the account exists.
            return response()->json([
                'success' => 'Se o email existir, recebera instrucoes para recuperar a senha.',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Nao foi possivel processar o pedido.'], 500);
        }
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::forgetGuards();

        return response()->noContent();
    }

    public function checkToken(Request $request): JsonResponse
    {
        return response()->json([
            'valid' => true,
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * Preserve the person id expected by existing process forms while keeping
     * the authentication user's database id available separately.
     *
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        $user->loadMissing('pessoa');

        return [
            ...($user->pessoa?->toArray() ?? []),
            'id' => $user->pessoa_id,
            'userId' => $user->getKey(),
            'email' => $user->email,
            'acesso' => $user->acesso,
            'activo' => (bool) $user->activo,
            'jaAcedeu' => (bool) $user->ja_acedeu,
        ];
    }
}
