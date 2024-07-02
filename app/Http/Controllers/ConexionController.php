<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Conexion;
use App\Models\EncryptedQR;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Database\Eloquent\Collection;

class ConexionController extends Controller
{
    public function index() 
    {
        $conexions = Conexion::where('status', 1)->get();
        $data = Collection::make($conexions)->map(function ($row) {
            if ($row->status > 0) {
                return [
                    'id' => $row->id,
                    'company_name' => $row->company_name,
                    'token' => $row->token,
                    'phone' => $row->phone,
                    'phone_id' => $row->phone_id,
                    'welcome' => $row->welcome,
                    'status_bot' => $row->status_bot,
                    'updated_at' => $row->updated_at
                ];
            }
            else {
                return null;
            }
        });

        return response()->json([
            'status' => 'success',
            'conexions' => $data
        ], Response::HTTP_OK);
    }
    
    public function store(Request $request) 
    {
        try {
            $user = auth()->user();

            $this->validate($request, [
                'company_name' => 'required|string',
                // 'token' => 'required',
                'phone' => 'required|min:9|max:9',
                'welcome' => 'required|string'
            ]);

            $data = [
                'id' => $request->id,
                'company_name' => $request->company_name,
                'token' => 'EAADvTvLPznQBO3zlBnnEbKUIKQypiqcQ8cK6iAJgwgSo6BdS6aeBH1ZCmQLqWKjTUU1O7QXWrstjtRMx88U831lXZCDJk46ha3Xo6yCglMZAB4grWtnwyhdvUFUVer0GIjZBJyDDqqTz64ZAFM2oZASvcOZBLHJKcKbBkyykN3MD7gqlEB0odlwJ3QmrL2MDu6kX9HfTZAdIHF7e23dbdPkZD',
                'phone' => $request->phone,
                'phone_id' => $request->phone_id,
                'welcome' => $request->welcome,
                'status' => 1,
                'user_id' => $user->id
            ];

            $conexion = Conexion::updateOrCreate(
                ['id' =>  $data['id']],
                [
                    'company_name' => $data['company_name'],
                    'token' => 'EAADvTvLPznQBO3zlBnnEbKUIKQypiqcQ8cK6iAJgwgSo6BdS6aeBH1ZCmQLqWKjTUU1O7QXWrstjtRMx88U831lXZCDJk46ha3Xo6yCglMZAB4grWtnwyhdvUFUVer0GIjZBJyDDqqTz64ZAFM2oZASvcOZBLHJKcKbBkyykN3MD7gqlEB0odlwJ3QmrL2MDu6kX9HfTZAdIHF7e23dbdPkZD',
                    'phone' => $data['phone'],
                    'phone_id' => $data['phone_id'],
                    'welcome' => $data['welcome'],
                    'status' => $data['status'],
                    'user_id' => $data['user_id']
                ]
            );

            $hashedContent = Hash::make(json_encode($data));

            $encryptedQR = new EncryptedQR();
            $encryptedQR->hashed_content = $hashedContent;
            $encryptedQR->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Conexion creada con exito',
            ], Response::HTTP_CREATED);
        }
        catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la conexion',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateStatusBot(Request $request) 
    {
        try {
            $id = $request->input('id');
            
            $conexion = Conexion::find($id);
            
            $conexion->update([
                'status_bot' => $request->input('status_bot')
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Estado actualizado',
            ], Response::HTTP_CREATED);
        }
        catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al ejecutar este proceso',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete($id) 
    {
        try {
            $conexion = Conexion::find($id);
            $conexion->update([
                'status' => 0
            ]);
            $conexion->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Conexion eliminada',
                'conexion' => $conexion
            ], Response::HTTP_OK);
        }
        catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id) 
    {
        $conexion = Conexion::find($id);
        $conexion->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Conexion eliminada'
        ]);
    }
}
