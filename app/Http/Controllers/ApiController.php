<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Appointments;
use App\Models\Images;
use App\Models\Messages;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    public function errorAuth(Request $request) {
        $answer = [
            'status' => 'error',
            'reason' => 'error auth'
        ];
        $response = response()->json($answer, '403', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);

        AppHelper::instance()->logWrite($request, $response);

        return $response;
    }

    public function sendResponse($data, $message, $status = 200)
    {
        $response = [
            'status' => 'success',
            'data' => $data,
            'message' => $message
        ];

        return response()->json($response, $status, ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function sendError($errorData, $message, $status = 500)
    {
        $response = [];
        $response['status'] = 'error';
        $response['message'] = $message;
        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }

        return response()->json($response, $status);
    }

    public function register(Request $request) {

        $input = $request->only('name', 'email', 'password', 'age', 'sex', 'city');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'age' => 'required',
            'sex' => 'required',
            'city' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }
        $password = $input['password'];

        $input['password'] = bcrypt($password);

        User::create($input);

        $email = $input['email'];

        $token = JWTAuth::attempt(['email' => $email, 'password' => $password]);

        $user = JWTAuth::setToken($token)->toUser();
        $user->load('images');

        $success = [
            'token' => $token,
            'user'  => $user
        ];

        return $this->sendResponse($success, 'successful registration', 200);
    }

    public function auth(Request $request) {
        $input = $request->only('email', 'password');

        $validator = Validator::make($input, [
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        try {
            // this authenticates the user details with the database and generates a token
            if (! $token = JWTAuth::attempt($input)) {
                return $this->sendError([], "invalid login credentials", 400);
            }
        } catch (JWTException $e) {
            return $this->sendError([], $e->getMessage(), 500);
        }

        $user = JWTAuth::setToken($token)->toUser();
        $user->load('images');

        $success = [
            'token' => $token,
            'user'  => $user
        ];

        return $this->sendResponse($success, 'successful login', 200);

    }

    public function getMessagesFrom(Request $request) {
        // Получить сообщения отправленные автору
        if ($id = $request->input('id') and is_numeric($id) and $user = User::find($id)) {
            $answer = $user->getMessagesFrom;
        } else {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function newMessage(Request $request) {
        if (
            $idToUser = $request->input('id_to_user')
                and
            $idFromUser = AppHelper::instance()->getIdFromJwt()
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

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);;
    }

    public function getUsersByParams(Request $request): JsonResponse {
        $input = [];
        $input['sex'] = "%";
        $input['city'] = "%";
        $input['ageStart'] = 0;
        $input['ageEnd'] = 100;
        $input['page'] = 1;
        $count = 15;

        $validator = Validator::make($request->all(), [
            'page' => 'integer|nullable',
            'sex' => 'string|nullable',
            'city' => 'string|nullable',
            'ageStart' => 'integer|nullable',
            'ageEnd' => 'integer|nullable',
        ]);

        if ($validator->fails()) {

            $answer = $validator->errors();
            $answer->add('status', 'error');

        } else {
            // без ошибок
            $input = array_merge($input, $request->all());

            foreach ($input as $key => $value) {
                if ($value == '') {
                    $input[$key] = '%';
                }
            }

            $conditions = [
                ['sex',  'like', $input['sex']],
                ['city', 'like', '%' . $input['city'] . '%'],
            ];

            $ageStart = (int) $input['ageStart'];
            $ageEnd   = (int) $input['ageEnd'];
            $page     = $input['page'];

            $query = User::where($conditions)->whereBetween('age', [$ageStart, $ageEnd]);

            $answer = array();

            $answer['found']   = $query->count();
            $answer['maximum'] = $count;
            $answer['users']   = $query->paginate($count, ['*'], 'page', $page)->load('images');
            $answer['above'] = $input;
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function updateProfile(Request $request) {
        $id = AppHelper::instance()->getIdFromJwt();

        if (isset($id)) {
            $answer = [
                'status' => 'error',
                'reason' => 'user undefined'
            ];
        } else {
            $user = User::find($id);
            $user->fill($request->all())->save();
            $answer = ['status' => 'success', 'text' => 'Пользователь обновлён'];
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function getImagesByUserUuid(Request $request) {
        if ($id = $request->id and $user = User::find($id)) {
            try {
                $images = $user->getImages;
                $answer = $images;
            } catch (\Exception $e) {
                $answer = ['status' => 'error', 'text' => 'Внутренняя ошибка'];
            }
        } else {
            $answer = ['status' => 'error', 'text' => 'Вы не указали id пользователя или пользователь не найден'];
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function getActualDialogues() {
        $success = true;
        if ($id = AppHelper::instance()->getIdFromJwt() and $user = User::find($id)) {
            $answer = $user->getDialogues->sortByDesc('created_at')->values()->unique('id_from_user');
        } else {
            $success = false;
        }

        if (!$success) {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function getDialogBetween(Request $request) {
        if ($id_to = $request->input('id_to')) {
            $messages = new Messages();

            $id_self = AppHelper::instance()->getIdFromJwt();

            $dialog = $messages->orWhere(function ($query) use ($id_to, $id_self) {
                $query->where([
                ['id_from_user', '=', $id_self],
                ['id_to_user', '=', $id_to]
                ]);
            })->orWhere(function($query) use ($id_to, $id_self) {
                $query->where([
                    ['id_to_user', '=', $id_self],
                    ['id_from_user', '=', $id_to]
                ]);
            })->get()->sortBy('created_at');

            $answer = $dialog;
        } else {
            $answer = ['status' => 'error', 'text' => 'Пользователь не найден'];
        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'], JSON_UNESCAPED_UNICODE);
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

    public function getUsersByPage(Request $request) {
        $validator = Validator::make($request->all(), [
            'count' => 'required|integer',
            'page' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $answer = $validator->errors();
            $answer->add('status', 'error');
            //$answer = ['status' => 'error', 'text' => 'Не заполнены все поля'];
        } else {
            $count = $request->input('count');
            $page = $request->input('page');

            $users = ['Пустой массив'];

            $count = intval($count);
            $page = intval($page);

            $id = AppHelper::instance()->getIdFromJwt();

            $users = User::skip($count * $page)->take($count)->where('id', '!=', $id)->get();

            $users->load('images'); // Load relationship in collection

            // $users = $users->except([$id]); // Except self-user

            $answer = ['status' => 'success', 'text' => 'Успешно', 'result' => $users];


        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function getUserById(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            $answer = $validator->errors();
            $answer->add('status', 'error');
        } else {
            $id_user = $request->input('id');

            $user = ['Пустой массив'];

            $user = User::find($id_user);
            $user->load('images');

            $answer = ['status' => 'success', 'text' => 'Успешно', 'result' => $user];


        }

        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    public function storeImage(Request $request) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
            'is_avatar' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $answer = $validator->errors();
            $answer->add('status', 'error');
        }

        if (!$validator->fails()) {
            $id = AppHelper::instance()->getIdFromJwt();
            $destination_path = 'public/images/avatars';
            $is_avatar = $request->input('is_avatar');
            $image = $request->file('image');
            $image_name = uniqid('img_');
            $ext = $image->extension();
            $allowExt = ['jpg', 'jpeg', 'png', 'bmp', 'webp', 'gif'];
            if (in_array($ext, $allowExt) ) {
                $path = $image->storeAs($destination_path, "$image_name.$ext");
                $http_address = env('APP_URL');
                $answer = ['status' => 'success', 'link' => "$http_address" . "storage/images/avatars/$image_name.$ext"];
                $image = new Images();

                $fullpath = "$http_address" . "storage/images/avatars/$image_name.$ext";

                $image->fill(['image_path' => "$fullpath", 'user_id' => $id, 'is_avatar' => $is_avatar])->save();

            } else {
                $answer = ['status' => 'error', 'text' => "Расширение не файла картинки"];
            }

            //http://localhost:8000/storage/images/avatars/ZIMAzz2F0Jmf3DHbHaHbbcRV0X6vKU9x.png

        }
        return response()->json($answer, '200', ['Content-type'=>'application/json;charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

}
