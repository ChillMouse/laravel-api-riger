<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'age' => 'required',
            'sex' => 'required',
        ]);
        if ($validator->fails()) {
            $answer = $validator->errors();
            //$answer = ['status' => 'error', 'text' => 'Не заполнены все поля'];
        } else {
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');
            $age = $request->input('age');
            $sex = $request->input('sex');
            $user = new User();

            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $user->age = $age;
            $user->sex = $sex;
            $user->save();
            $answer = ['status' => 'success', 'text' => 'Успешно зарегистрирован'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function auth(Request $request) {

        if ($login = $request->input('login') and $password = $request->input('password')) {
            $answer = User::where([
                    ['email', $login],
                    ['password', $password]
                ]
            )->get();
        } else {
            $answer = ['status' => 'error', 'text' => 'Не указан логин или пароль'];
        }



        return response()->json($answer);
    }

    public function getMessagesFrom(Request $request) {
        // Получить сообщения отправленные автору
        if ($id = $request->input('id') and is_numeric($id)) {
            $answer = User::find($id)->getMessagesFrom;
        } else {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден или передано не число'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);


        // Получить сообщения автора return response()->json(User::first()->getMessagesFrom, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function newMessage(Request $request) {
        $idToUser = $request->input('id_to_user');
        $idFromUser = $request->input('id_from_user');
        $text = $request->input('text');

        $messages = new Messages();

        $messages->text = $text;
        $messages->id_to_user = $idToUser;
        $messages->id_from_user = $idFromUser;

        $messages->save();

        return false;
    }

    public function getUsersByParams(Request $request) {
        $sex = "%";
        $ageStart = 0;
        $ageEnd = 100;

        if ($val = $request->input('sex'))
            $sex = $val;

        if ($val = $request->input('ageEnd'))
            $ageEnd = $val;

        if ($val = $request->input('ageStart'))
            $ageStart = $val;

        $answer = User::where('sex', 'like', $sex)->whereBetween('age', [$ageStart, $ageEnd])->get();
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

}
