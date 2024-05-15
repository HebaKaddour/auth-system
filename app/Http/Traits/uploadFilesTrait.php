<?php

namespace App\Http\Traits;


trait uploadFilesTrait
{
    public function uploadProfilePhoto($request,$name,$folder)
    {
        $photo_name = $request->file($name)->getClientOriginalName();
        $request->file($name)->storeAs('uploadeI/',$folder.'/'.$photo_name,'profile_photo');

    }

    public function uploadCertificate($request,$name,$folder)
    {

        $file_name = $request->file($name)->getClientOriginalName();
        $request->file($name)->storeAs('uploadeI/',$folder.'/'.$file_name,'certificate');
    }

}
