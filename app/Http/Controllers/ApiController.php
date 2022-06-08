<?php

namespace App\Http\Controllers;

use App\Models\Images;
use App\Models\Messages;
use App\Models\User;
use App\Models\UserImage;
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
        if (
            $idToUser = $request->input('id_to_user')
                and
            $idFromUser = $request->input('id_from_user')
                and
            $text = $request->input('text')
        ) {
            $messages = new Messages();

            $messages->text = $text;
            $messages->id_to_user = $idToUser;
            $messages->id_from_user = $idFromUser;

            $messages->save();
            $answer = ['status' => 'success', 'text' => 'Успешно'];
        } else {
            $answer = ['status' => 'error', 'text' => 'Не хватает параметров'];
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);;
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

    public function updateProfile(Request $request) {

    }

    public function uploadImage(Request $request) {
        if ($id_user = $request->id_user and $image = $request->image) {
            $isAvatar = false;
            if ($request->is_avatar) {
                $isAvatar = $request->is_avatar;
            }
            $pivot = new UserImage();
            $images = new Images();
            $images->image = $image;
            $images->isAvatar = $isAvatar;
            $images->save();
            $pivot->id_user = $id_user;
            $pivot->id_image = $images->id;
            $pivot->save();
            $answer = ['status' => 'success', 'id_image' => $images->id];
        } else {
            $answer = ['status' => 'error', 'text' => 'Вы не указали id пользователя или не передали картинку'];
        }
        return $answer;
    }

    public function getImagesByUserId(Request $request) {
        if ($id = $request->id) {
            $answer = User::find($id)->getImages;
        } else {
            $answer = ['status' => 'error', 'text' => 'Вы не указали id пользователя'];
        }

        return response()->json($answer);
    }

    public function getActualDialogues(Request $request) {
        if ($id = $request->id) {
            $answer = User::find($id)->getDialogues->sortByDesc('created_at')->values()->unique('id_from_user');
        } else {
            $answer = ['status' => 'error', 'text' => 'Вы не указали id пользователя'];
        }
        return response()->json($answer);
    }

    public function getMessagesFromTo(Request $request) {
        // Получить сообщения отправленные автору
        if ($id_from = $request->input('id_from') and is_numeric($id_from) and $id_to = $request->input('id_to') and is_numeric($id_to)) {
            $answer = User::find($id_from)->getMessagesFrom->reverse()->values()->where('id_to_user', '=', $id_to);
        } else {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден или передано не число'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);


        // Получить сообщения автора return response()->json(User::first()->getMessagesFrom, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
