<?php
namespace App\Helpers;

use App\Models\LogReq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
class AppHelper
{
    public function logWrite(Request $request, Response | JsonResponse $response): void {
        $logreq = new LogReq();
        $logreq->url = $request->url();
        $params = $request->all();
        $logreq->params = json_encode($params);

        $headers = collect($request->header())->transform(function ($item) {
            return $item[0];
        });

        $logreq->headers = json_encode($headers);
        $logreq->response = json_encode($response);

        $logreq->save();
    }

    public function getHash($str) {
        $salt = env('SALT');
        $now = Carbon::now()->toDateTimeString();

        return hash('sha3-224', $salt . hash('sha3-512', $salt . hash('sha3-384', $salt . $str . $now)));
    }

    public static function instance(): AppHelper
    {
        return new AppHelper();
    }
}
