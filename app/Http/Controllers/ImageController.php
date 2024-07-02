<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    
    public function store(Request $request)
    {
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $allowedImageMimeTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                $allowedVideoMimeTypes = ['video/mp4', 'video/3gpp'];
                $allowedVideoMimeTypesAudio = ['audio/mp3', 'audio/ogg', 'audio/aac', 'audio/amr', 'audio/mpeg', 'audio/wav', 'audio/x-wav','aac','audio/webm','webm', 'video/webm'];
                $allowedDocumentMimeTypes = [
                    '.rar',
                    'application/x-rar-compressed',
                    'application/zip',
                    'application/pdf',
                    'application/vnd.ms-powerpoint',
                    'application/msword',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];

                $extension = $file->getClientOriginalExtension();
                $mime = $file->getMimeType();
                
                if (in_array($mime, $allowedImageMimeTypes)) {
                    $storageFolder = in_array($extension, ['png', 'jpg', 'jpeg']) ? 'public/files/images' : 'public/files/videos';
                } elseif (in_array($mime, $allowedVideoMimeTypes)) {
                    $storageFolder = $extension === 'mp4' ? 'public/files/videos' : 'public/files/images';
                } elseif (in_array($mime, $allowedVideoMimeTypesAudio)) {
                    $storageFolder = 'public/files/audios';
                } else{
                    $storageFolder = 'public/files/documents';
                }
                
                $path = $file->store($storageFolder);

                $url = Storage::url($path);
                

                $image = new Image();
                $image->path = $url;
                $image->save();

                $filename = pathinfo($path, PATHINFO_BASENAME);

                return $filename;
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al subir el archivo.'], 500);
        }
    }

    public function getFile($destination,$filename)
    {
        $path = storage_path("app/public/files/$destination/$filename");

        if (!file_exists($path)) {
            return response()->json(['error' => 'Image not found.'], 404);
        }

        return response()->file($path);
    }

}
