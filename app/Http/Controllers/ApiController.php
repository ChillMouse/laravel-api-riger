<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Images;
use App\Models\Messages;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;

class ApiController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
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
        $answer = ['status' => 'error', 'text' => 'Не указан логин или пароль'];
        $user = [];

        if ($login = $request->input('login') and $password = $request->input('password')) {
            $user = User::where([
                    ['email', $login],
                    ['password', $password]
                ]
            )->get();
            $answer = $user;
            if ($user->count() == 0) {
                $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
            }
        }

        return response()->json($answer);
    }

    public function getMessagesFrom(Request $request) {
        // Получить сообщения отправленные автору
        if ($id = $request->input('id') and is_numeric($id) and $user = User::find($id)) {
            $answer = $user->getMessagesFrom;
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
        if ($id = $request->id and $user = User::find($id)) {
            $answer = $user->getImages;
        } else {
            $answer = ['status' => 'error', 'text' => 'Вы не указали id пользователя или пользователь не найден'];
        }

        return response()->json($answer);
    }

    public function getActualDialogues(Request $request) {
        $success = true;
        if ($id = $request->id and $user = User::find($id)) {
            $answer = $user->getDialogues->sortByDesc('created_at')->values()->unique('id_from_user');
        } else {
            $success = false;
        }

        if (!$success) {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
        }

        return response()->json($answer);
    }

    public function getMessagesFromTo(Request $request) {
        // Получить сообщения отправленные автору
        if ($id_from = $request->input('id_from') and is_numeric($id_from) and $id_to = $request->input('id_to') and is_numeric($id_to) and $user = User::find($id_from)) {
            $answer = $user->getMessagesFrom->reverse()->values()->where('id_to_user', '=', $id_to);
        } else {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден или передано не число'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);


        // Получить сообщения автора return response()->json(User::first()->getMessagesFrom, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function getDialogBetween(Request $request) {
        if ($id_from = $request->input('id_from') and is_numeric($id_from) and $id_to = $request->input('id_to') and is_numeric($id_to)) {
            $messages = new Messages();

            $dialog = $messages->orWhere(function ($query) use ($id_to, $id_from) {
                $query->where([
                ['id_from_user', '=', $id_from],
                ['id_to_user', '=', $id_to]
                ]);
            })->orWhere(function($query) use ($id_to, $id_from) {
                $query->where([
                    ['id_to_user', '=', $id_from],
                    ['id_from_user', '=', $id_to]
                ]);
            })->get()->sortBy('created_at');

            $answer = $dialog;
        } else {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден или передано не число'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function registerToDoctor(Request $request) {
        $messages = [
            'telephone.required' => 'Номер телефона не указан',
            'client_firstname.required' => 'Имя клиента не указано',
            'client_lastname.required' => 'Фамилия клиента не указана',
            'doctor_firstname.required' => 'Имя доктора не указано',
            'doctor_lastname.required' => 'Фамилия доктора не указана',
            'appointment_time.required' => 'Дата приёма не указана',
            'telephone.max' => 'Длина телефона превышена',
            'client_firstname.max' => 'Длина имени клиента превышена',
            'client_lastname.max' => 'Длина фамилии клиента превышена',
            'doctor_firstname.max' => 'Длина имени доктора превышена',
            'doctor_lastname.max' => 'Длина фамилии доктора превышена',
            ];
        $rules = [
            'telephone' => 'required|max:20',
            'client_firstname' => 'required|max:20',
            'client_lastname' => 'required|max:20',
            'doctor_firstname' => 'required|max:20',
            'doctor_lastname' => 'required|max:20',
            'appointment_time' => 'required'
        ];

        $validated = Validator::make($request->all(), $rules, $messages);

        $validate_failed = $validated->fails();

        if ($validate_failed) {
            $answer = ['status' => 'error', 'errors' => $validated->errors()->all()];
        } else {
            $appointment = Appointments::create($request->all());
            $answer = ['status' => 'success', 'text' => 'Успешно добавлена запись'];
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function getRegisterToDoctor(Request $request) {
        $appointment = new Appointments;
        if ($telephone = $request->telephone) {
            $appointment = Appointments::where(['telephone' => $telephone])->get();

            if ($appointment->count() > 0) {
                $answer = ['status' => 'success', 'result' => $appointment];
            } else {
                $answer = ['status' => 'error', 'text' => "Пользователя с таким номером не найдено"];
            }

        } else {
            $answer = ['status' => 'success', 'result' => $appointment->all()];
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
