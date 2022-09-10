<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

trait BaseResponse
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code=200)
    {
    	$response = [
            'status' => 'success',
            'message' => $message,
            'data'    => $result,
            
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code)
    {
    	$response = [
            'status' => 'false',
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['errors'] = $errorMessages;
        }

        return response()->json($response,$code);
    }
}
