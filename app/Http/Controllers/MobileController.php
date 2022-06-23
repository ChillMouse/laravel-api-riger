<?php

namespace App\Http\Controllers;

use App\Models\MobileUser;
use App\Models\Products;
use App\Models\Tokens;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function React\Promise\map;

class MobileController extends Controller
{
    // ~(!?:2J";x%5)Nw>
    public function register(Request $request) {
        if ($login = $request->input('login') and $password = $request->input('password')) {
            $input = ['login' => $login];
            $rules = [
                'login' => 'unique:mysql_etmobile.mobile_users'
            ];
            $validate = Validator::make($input, $rules);

            $rules = [
                'login' => 'max:50'
            ];
            $validatelen = Validator::make($input, $rules);

            if ($validate->passes() and $validatelen->passes()) {
                $user = new MobileUser();

                $user->login = $login;
                $user->password = $password;

                $user->save();
                $answer = ['status' => 'success', 'text' => 'Успешно зарегистрирован'];
            } else {
                if ($validate->fails()) {
                    $answer = ['status' => 'error', 'text' => 'Такой пользователь уже существует'];
                }
                if ($validatelen->fails()) {
                    $answer = ['status' => 'error', 'text' => 'Длина логина должна быть меньше 50'];
                }
            }

        } else {
            $answer = ['status' => 'error', 'text' => 'Не указан логин или пароль'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function auth(Request $request) {
        $answer = ['status' => 'error', 'text' => 'Не указан логин или пароль'];
        $user = null;

        if ($login = $request->input('login') and $password = $request->input('password')) {
            $user = MobileUser::where([
                    ['login', $login],
                    ['password', $password]
                ]
            )->first();

            if ($user != null) {
                $table_tokens = Tokens::where('id_user', '=', $user->id)->first();
                if ($table_tokens == null) {
                    $table_tokens = new Tokens();
                }
                $token = Str::random(30);
                $answer = [
                    'status' => 'success',
                    'token' => $token,
                    'account' => $user
                ];
                $table_tokens->token = $token;
                $table_tokens->id_user = $user->id;
                $table_tokens->save();
            }


            if ($user == null) {
                $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
            }
        }

        return response()->json($answer);
    }

    public function token(Request $request) {
        $oauth = true;
        if ($request->client_id != '~(!?:2J`;x%5)Nw>') {
            $answer = [
                'status' => 'error',
                'text' => 'OAuth error'
            ];
            $oauth = false;
        }

        if ($oauth) {
            if ($token = $request->token) {
            $actualToken = Tokens::where('token', '=', $token)->first();
                if ($actualToken != null) {
                    $timeCreate = Carbon::createFromDate($actualToken->updated_at);
                    $timeNow = Carbon::now();
                    $actual = 30 > $timeNow->diffInDays($timeCreate);
                    $answer = [
                        'status' => 'success',
                        'text' => 'Успешно',
                        'result' => $actual
                    ];
                } else {
                    $answer = [
                        'status' => 'error',
                        'text' => 'Токен не найден'
                    ];
            }

        } else {
                $answer = [
                    'status' => 'error',
                    'text' => 'Не передан токен'
                ];
            }
        }

        return response()->json($answer);
    }

    public function getProducts(Request $request) {
        $oauth = true;
        $answer = null;
        if ($request->client_id != '~(!?:2J`;x%5)Nw>') {
            $answer = [
                'status' => 'error',
                'text' => 'OAuth error'
            ];
            $oauth = false;
        }
        if ($oauth) {
            $products = Products::all();
            $count = $products->count();
            $products = $products->map(function($v) {
                    $v->image = base64_encode($v->image);
                    return $v;
                });
            if ($count > 0) {
                $answer = [
                    'status' => 'success',
                    'results' => $products
                ];
            } else {
                $answer = [
                    'status' => 'error',
                    'text' => 'Товаров нет'
                ];
            }
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
