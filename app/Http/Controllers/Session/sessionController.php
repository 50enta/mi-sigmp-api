<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Http\Controllers\mailController;
use App\Http\Utils\User\generatePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class sessionController extends Controller
{
    function enpointTest(){
        return response(['message' => 'Endpoint testado com sucesso!']);
    }
    
    function login(Request $request)
    {
        try {
            $valid = $request->validate([
                'email' => 'required|email|max:100',
                'password' => 'required|string'
            ]);

            $user = User::where('users.email', $valid['email'])
                ->where('users.activo', 1)
                ->join('pessoas', 'pessoas.id', '=', 'users.pessoa_id')
                ->select('users.*', 'pessoas.*') // IMPORTANTE
                ->first();

            if (!$user || !Hash::check($valid['password'], $user->password)) {
                return response(['error' => 'Email ou senha invalidos!'], 401);
            } else {
                try {
                    $token = $user->createToken('near_miss_api_login_token')->plainTextToken;
                    return response(['token' => $token, 'user' => $user]);
                } catch (Exception $e) {
                    return response(['error' => 'Ocorreu um erro ao fazer login!'], 401);
                }
            }
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response(['error' => 'Email ou senha sao invalidos!'], 401);
        }
    }

    function requestPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = User::select('email', 'id')
                ->where('email', '=', $request->email)
                ->get();

            if (sizeof($email) > 0) {
                $passwordGenrator = new generatePassword();
                $pass = $passwordGenrator->returnRandomString();
                $mail = new mailController();

                if ($mail->passwordReset($email[0]->email, $pass) == 1) {
                    User::where('id', $email[0]->id)
                        ->update([
                            'password' => Hash::make($pass),
                            'ja_acedeu' => '0'
                        ]);
                    return response(['success' => 'Foi enviado um codigo no seu email para a reposicao da senha!']);
                } else {
                    return response(['error' => 'Ocorreu um erro ao fazer a requisicao da nova senha!'], 500);
                }
            } else {
                return response(['error' => 'O email introduzido não existe!']);
            }
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response(['error' => 'Email introduzido e invalido']);
        }
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }

    function checkToken(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                return response(['valid' => true]);
            } else {
                return response(['valid' => false], 401);
            }
        } catch (Exception $e) {
            return response(['valid' => false, 'error' => 'Erro ao verificar token'], 500);
        }
    }
}
