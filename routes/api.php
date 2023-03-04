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
Route::prefix('/')
    ->middleware('auth_headers')
    ->group(function () {
        Route::post('/register', [ApiController::class, 'register']);
        Route::post('/auth', [ApiController::class, 'auth']);
        Route::post('/newMessage', [ApiController::class, 'newMessage']);
        Route::get('/getMessagesFrom', [ApiController::class, 'getMessagesFrom']);
        Route::get('/getMessagesFromTo', [ApiController::class, 'getMessagesFromTo']);
        Route::get('/getUsersByParams', [ApiController::class, 'getUsersByParams']);
        Route::post('/uploadImage', [ApiController::class, 'uploadImage']);
        Route::get('/getImagesByUserId', [ApiController::class, 'getImagesByUserId']);
        Route::get('/getActualDialogues', [ApiController::class, 'getActualDialogues']);
        Route::get('/getDialogBetween', [ApiController::class, 'getDialogBetween']);

        Route::post('/updateProfile', [ApiController::class, 'updateProfile']);

        Route::post('/registerToDoctor', [ApiController::class, 'registerToDoctor']);
        Route::post('/getRegisterToDoctor', [ApiController::class, 'getRegisterToDoctor']);

        Route::post('/getUsersByPage', [ApiController::class, 'getUsersByPage']);
        Route::post('/storeImage', [ApiController::class, 'storeImage']);
    });


Route::prefix('/mobile')->group(function () {
    Route::post('/register', [MobileController::class, 'register']);
    Route::post('/auth', [MobileController::class, 'auth']);
    Route::post('/token', [MobileController::class, 'token']);
    Route::get('/getProducts', [MobileController::class, 'getProducts']);
});
