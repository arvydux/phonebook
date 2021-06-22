<?php

namespace App\Traits;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image;

trait PhotoTrait {

    public function storePhoto(Request $request, $id)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'image' => 'mimes:jpeg,bmp,png'
            ]);
            Image::load($request->file)->width(250)->height(250)->save();
            $request->file->store('photo', 'public');
            $photo = new Photo([
                "phone_number_id" => $id,
                "file_name" => $request->file->hashName()
            ]);
            $photo->save(); // Finally, save the record.
        }
    }

    public function showPhoto($id)
    {
        $photo = Photo::where('phone_number_id', $id)->first();
        if (isset($photo->file_name))
        return $photo->file_name;
    }

    public function updatePhoto(Request $request, $id)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'image' => 'mimes:jpeg,bmp,png'
            ]);
            Image::load($request->file)->width(250)->height(250)->save();
            $request->file->store('photo', 'public');
            $photo = Photo::where('phone_number_id', $id)->first();
            if (isset($photo)) {

                Storage::disk('public')->delete("photo/". $photo->file_name);
                $photo->update(['file_name' => $request->file->hashName()]);
            } else {
                $photo = new Photo([
                    "phone_number_id" => $id,
                    "file_name" => $request->file->hashName()
                ]);
            }
            $photo->save();
            return true;
        }
        return false;
    }

    public function deletePhoto($id)
    {
        $photo = Photo::where('phone_number_id', $id)->first();
        if (isset($photo)) {
            Storage::disk('public')->delete("photo/". $photo->file_name);
            $photo->delete();
            return true;
        }
        return false;
    }
}
