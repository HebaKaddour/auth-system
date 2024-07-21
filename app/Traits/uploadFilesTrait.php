<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait uploadFilesTrait
{

    public function uploadImage($file, $disk, $path)
    {
        $filename = date('Y-m-d') . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->storeAs($path, $filename, $disk);
        return $filename;
    }
    public function deleteUploadedFiles($user)
    {
            $image_path = public_path('uploads/photos/' . $user->profile_photo);
            if(isset($user->profile_photo) && file_exists($image_path)){
             unlink($image_path);
            }
            $file_path = public_path('uploads/certificates/' . $user->certificate);
            if (isset($user->certificate) && file_exists($file_path)) {
                unlink($file_path);
            }

        }
    }


