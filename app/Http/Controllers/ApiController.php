<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function register(Request $request) {
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');
            $user = new User();

            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $user->save();
        return false;
    }

    public function auth(Request $request) {
        $login = $request->input('login');
        $password = $request->input('password');

        $user = User::where([
                ['email', $login],
                ['password', $password]
            ]
        )->get();

        return response()->json($user);
    }

    public function getMessagesFrom($id) {
        // Получить сообщения отправленные автору
        return response()->json(User::find($id)->getMessagesFrom, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);


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
}
