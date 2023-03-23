<?php

    use App\Http\Controllers\ApiController;
    use App\Http\Controllers\MobileController;
    use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::any('/error_auth', [ApiController::class, 'errorAuth']);

Route::group(
    [
        'middleware' => [
            'auth_headers',
            'logging_requests',
        ]
    ],
    function () {
        Route::post('/register',            [ApiController::class, 'register']);
        Route::post('/auth',                [ApiController::class, 'auth']);
        Route::post('/registerToDoctor',    [ApiController::class, 'registerToDoctor']);
        Route::post('/getRegisterToDoctor', [ApiController::class, 'getRegisterToDoctor']);
    });

Route::group(
    [
        'middleware' => [
            'jwt.verify',
            'logging_requests',
        ]
    ],
    function () {
        Route::get('/getMessagesFrom',     [ApiController::class, 'getMessagesFrom']);
//        Route::get('/getMessagesFromTo',   [ApiController::class, 'getMessagesFromTo']);
        Route::get('/getUsersByParams',    [ApiController::class, 'getUsersByParams']);
        Route::get('/getUserById',         [ApiController::class, 'getUserById']);
        Route::get('/getDialogBetween',    [ApiController::class, 'getDialogBetween']);
        Route::get('/getActualDialogues',  [ApiController::class, 'getActualDialogues']);
        Route::get('/getUsersByPage',      [ApiController::class, 'getUsersByPage']);
        Route::get('/getImagesByUserUuid', [ApiController::class, 'getImagesByUserUuid']);
        Route::post('/newMessage',         [ApiController::class, 'newMessage']);
        Route::post('/uploadImage',        [ApiController::class, 'uploadImage']);
        Route::post('/updateProfile',      [ApiController::class, 'updateProfile']);
        Route::post('/storeImage',         [ApiController::class, 'storeImage']);
});
