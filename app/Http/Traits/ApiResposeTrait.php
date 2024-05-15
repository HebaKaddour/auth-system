<?php

namespace App\Http\Traits;

trait ApiResposeTrait
{
    public function ApiResponse($data=null,$message=null,$status=null){

        //$msg = ["ok"];
        $array = [
             'data'=> $data,
             'message'=>$message,
             'status'=>$status
        ];
        return response($array,$status);

    }


}
