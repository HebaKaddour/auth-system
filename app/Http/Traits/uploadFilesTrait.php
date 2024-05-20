<?php

namespace App\Http\Traits;

use App\Models\User;


trait uploadFilesTrait
{


    public function uploadImage($image, $disk, $path)
    {
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs($path, $imageName, $disk);

        return $imageName;
    }


    public function uploadfile($file, $disk, $path)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs($path, $fileName, $disk);

        return $fileName;
    }
}
