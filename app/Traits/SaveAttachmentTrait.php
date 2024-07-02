<?php

namespace App\Traits;

use GuzzleHttp\Client;
use App\Models\Conexion;
use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait SaveAttachmentTrait
{

    public function getFileExtension($filename)
    {
        $parts = pathinfo($filename);
        return $parts['extension'];
    }

    public function SaveAttachment($attachment,$messageId,$destination)
    {
        $token = Conexion::first();

        $attachmentId = ($destination == 'documents') ? $attachment['id'] : $attachment;

        $graphApiUrl = "https://graph.facebook.com/v17.0/$attachmentId";

        $client = new Client();
        try {
            $responseJson = $client->request('GET', $graphApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->token
                ]
            ]);
    
            if($responseJson){
                $responseData = json_decode($responseJson->getBody()->getContents(), true);

               $getFile = $client->request('GET', $responseData['url'], [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->token
                    ]
                ]);

                if ($getFile->getStatusCode() == 200) {

                    $fileContent = $getFile->getBody();
                    
                    $contentType = $getFile->getHeaderLine('Content-Type');

                    $extension = ($destination=='documents') ? $this->getFileExtension($attachment["filename"]) : explode('/', $contentType)[1];
                    
                    $timestamp = time();
                    $randomValue = mt_rand();
                    $fileName = 'file_' . $timestamp . '_' . $randomValue . '.' . $extension;
                    Storage::put("public/files/$destination/$fileName", $fileContent);

                    $urlFile = $this->getFiles($destination,$fileName);

                    Attachment::create([
                        'attachment_id' => $responseData['id'],
                        'message_id' => $messageId,
                        'url' => $urlFile,
                        'mime_type' => $responseData['mime_type'],
                        'sha256' => $responseData['sha256'],
                        'file_size' => $responseData['file_size'],
                        'messaging_product' => $responseData['messaging_product']
                    ]);
                }

                
            }else {
                Log::error('La solicitud no fue exitosa. Código de estado: ' . $responseJson->getStatusCode());
            }
    
        } catch (\Throwable $th) {
            Log::error('Error en la solicitud: ' . $th->getMessage());
        }
    }

    public function getFiles($destination,$filename)
    {
        $path = storage_path("app/public/files/$destination/$filename");

        if (!file_exists($path)) {
            return null;
        }

        return asset("api/v1/image/files/$destination/$filename");
    }
}