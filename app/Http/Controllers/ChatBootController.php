<?php

namespace App\Http\Controllers;

use App\Helpers\YourClass;
use App\Jobs\ProcessReceivedMessageAdministrativeManagement;
use App\Jobs\ProcessReceivedMessageDDCA;
use App\Jobs\ProcessReceivedMessageDIA;
use App\Jobs\ProcessReceivedMessageDOA;
use App\Jobs\ProcessReceivedMessageDrasam;
use App\Jobs\ProcessReceivedMessageDTRTYCR;
use App\Jobs\ProcessReceivedMessageLevelTwo;
use App\Jobs\ProcessReceivedMessageMesaDePartes;
use App\Jobs\ProcessReceivedMessageOPEA;
use App\Jobs\ProcessReceivedMessageRealInit;
use App\Jobs\ProcessReceivedMessageSatisfaction;
use App\Jobs\ProcessReceivedMessageWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatBootController extends Controller
{

    public function guardarSeleccion(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|integer'
        ]);

        // Dispatch the job with the validated data
        ProcessReceivedMessageDrasam::dispatch($validatedData);

        return response()->json(['message' => 'Seleccion procesada correctamente'], 200);
    }

    public function guardarSeleccionOPEA(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|string'
        ]);

        // Dispatch the job with the validated data
        ProcessReceivedMessageOPEA::dispatch($validatedData);

        return response()->json(['message' => 'Seleccion procesada correctamente'], 200);
    }

    public function guardarSeleccionDTRTYCR(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|string'
        ]);

        // Dispatch the job with the validated data
        ProcessReceivedMessageDTRTYCR::dispatch($validatedData);

        return response()->json(['message' => 'Seleccion procesada correctamente'], 200);
    }

    public function guardarSeleccionDIA(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|string'
        ]);

        ProcessReceivedMessageDIA::dispatch($validatedData);

        return response()->json(['message' => 'Seleccion procesada correctamente'], 200);
    }


    public function guardarSeleccionDDCA(Request $request)
    {
        $data = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id' => 'required|string',
        ]);

        // Enviar el Job correspondiente
        ProcessReceivedMessageDDCA::dispatch($data);

        return response()->json(['message' => 'Selección procesada correctamente'], 200);
    }


    public function guardarSeleccionDOA(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id' => 'required|string',
        ]);

        // Disparar el Job con los datos validados
        ProcessReceivedMessageDOA::dispatch($validatedData);

        return response()->json(['status' => 'success', 'message' => 'Selección guardada y procesada.']);
    }

    public function guardarSeleccionMesaDePartes(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id' => 'required|string',
        ]);

        // Disparar el Job con los datos validados
        ProcessReceivedMessageMesaDePartes::dispatch($validatedData);

        return response()->json(['status' => 'success', 'message' => 'Selección guardada y procesada.']);
    }


    public function guardarSeleccionAdministrativeManagement(Request $request)
    {
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|string'
        ]);

        // Dispatch the job with the validated data
        ProcessReceivedMessageAdministrativeManagement::dispatch($validatedData);

        return response()->json(['message' => 'Selección procesada correctamente para Administración.'], 200);
    }

    public function guardarSeleccionRealInit(Request $request)
    {
        $validatedData = $request->validate([
            'document' => 'required|string'
        ]);

        Log::info('Datos validados:', ['validatedData' => $validatedData]);

        // Dispatch the job with the validated data
        ProcessReceivedMessageRealInit::dispatch($validatedData);

        return response()->json(['message' => 'Selección procesada correctamente para Real Init.'], 200);
    }


    public function guardarSeleccionWebhook(Request $request)
    {
        $validatedData = $request->validate([
            'message' => 'required|string'
        ]);

        Log::info('Datos validados:', ['validatedData' => $validatedData]);

        // Dispatch the job with the validated data
        ProcessReceivedMessageWebhook::dispatch($validatedData);

        return response()->json(['message' => 'Selección procesada correctamente para Webhook.'], 200);
    }

    public function guardarSeleccionWebhookLevelTwo(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|string'
        ]);

        // Log de los datos validados para verificar
        Log::info('Datos validados:', ['validatedData' => $validatedData]);

        // Dispatch del job con los datos validados
        ProcessReceivedMessageLevelTwo::dispatch($validatedData);
        // Obtener la información del evento usando el helper
        $yourClass = new YourClass();
        $eventInfo = $yourClass->getEventInfo();

        // Respuesta JSON indicando que la selección ha sido procesada correctamente
        return response()->json([
            'message' => 'Selección procesada correctamente para Webhook.',
            'event_info' => $eventInfo
        ], 200);
    }

    public function guardarSeleccionWebhookEncuesta(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'mensaje_id' => 'required|integer',
            'opcion_id'  => 'required|string'
        ]);

        // Log de los datos validados para verificar
        Log::info('Datos validados:', ['validatedData' => $validatedData]);

        // Dispatch del job con los datos validados
        ProcessReceivedMessageSatisfaction::dispatch($validatedData);


        // Respuesta JSON indicando que la selección ha sido procesada correctamente
        return response()->json(['message' => 'Selección procesada correctamente para Webhook.'], 200);
    }
}
