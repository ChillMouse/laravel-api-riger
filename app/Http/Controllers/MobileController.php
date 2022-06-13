<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileController extends Controller
{
    public function register(Request $request) {
        if ($login = $request->input('login') and $password = $request->input('password')) {
            $input = ['email' => $login];
            $rules = [
                'email' => 'unique:users'
            ];
            $validate = Validator::make($input, $rules);

            if ($validate->passes()) {
                $user = new User();

                $user->email = $login;
                $user->password = $password;

                $user->save();
                $answer = ['status' => 'success', 'text' => 'Успешно зарегистрирован'];
            } else {
                $answer = ['status' => 'error', 'text' => 'Такой пользователь уже существует'];
            }

        } else {
            $answer = ['status' => 'error', 'text' => 'Не указан логин или пароль'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function auth(Request $request) {

        if ($login = $request->input('login') and $password = $request->input('password')) {
            $user = User::where([
                    ['email', $login],
                    ['password', $password]
                ]
            )->get();
            if (empty($user)) {
                $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
            } else {
                $answer = $user;
            }
        } else {
            $answer = ['status' => 'error', 'text' => 'Не указан логин или пароль'];
        }
        return response()->json($answer);
    }
}
