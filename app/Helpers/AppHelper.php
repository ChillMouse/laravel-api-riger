<?php
namespace App\Helpers;

use App\Models\LogReq;

class AppHelper
{
    public function logWrite($request, $response) {
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

    public static function instance()
    {
        return new AppHelper();
    }
}
