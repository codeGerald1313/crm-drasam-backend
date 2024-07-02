<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
use App\Http\Controllers\WspController;
use App\Http\Resources\NewConversationResource;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessReceivedMessageRealInit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;

    /**
     * Create a new job instance.
     */
    public function __construct($document)
    {
        $this->document = $document['document'] ?? null; // Accede al campo 'document' del array
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Obtener el contacto con ID 24 (o el contacto correspondiente)
        $contact = Contact::find(24);
        if (!$contact) {
            Log::error("Contacto con ID 24 no encontrado");
            return;
        }

        // Verificar si existe una conversación abierta para el contacto
        $lastConversation = Conversation::where('contact_id', $contact->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastConversation && $lastConversation->status == 'open') {
            // Obtener datos del DNI
            $dniDataResponse = $this->getDniData($this->document);
            $dniData = json_decode($dniDataResponse->getContent());

            // Verificar si se obtuvo correctamente la información del DNI
            if (isset($dniData->success) && $dniData->success) {
                // Construir el mensaje dinámico con los datos del DNI
                $message = $this->buildMessageWithDniData($dniData->data, $contact->num_phone);

                // Acción basada en la respuesta del servicio externo
                $wspController = new WspController();
                $responseMessage = $wspController->struct_message($message, $contact->num_phone);
                $messageResponse = $wspController->envia($responseMessage);
                $wspController->createMessageWelcome($lastConversation, $messageResponse, $responseMessage, $contact);

                // Ejemplo de evento de conversación creada
                event(new ConversationCreated(new NewConversationResource($lastConversation)));
            } else {
                // Manejar caso de error en la obtención de datos del DNI
                Log::error('Error al obtener datos del DNI: ' . $dniDataResponse->getContent());
            }
        }
    }


    protected function buildMessageWithDniData($dniData, $phoneNumber)
    {
        // Extraer nombres y apellidos
        $nombres = $dniData->nombres;
        $apellidoPaterno = $dniData->apellido_paterno;
        $apellidoMaterno = $dniData->apellido_materno;
    
        // Construir el mensaje personalizado
        $message = "¡Hola $nombres $apellidoPaterno $apellidoMaterno! 🌟\n";
        $message .= "🎉 ¡Bienvenido/a a nuestro servicio! Estamos aquí para ayudarte. 😊\n\n";
        $message .= "¿En qué podemos asistirte hoy? 🤔\n";
        $message .= "Por favor, escribe tu consulta o pregunta. ❓";
    
        return $message;
    }
    


    protected function  getDniData($dni)
    {
        // Crear el JSON con el número de DNI
        $params = json_encode(['dni' => $dni]);
    
        // Iniciar la solicitud cURL
        $curl = curl_init();
    
        // Configurar las opciones de la solicitud cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apiperu.dev/api/dni",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,        
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer 8a62e261f044c12978c1dac7b2d4ad9a6b8ed19889009e176e39f45ab50a2a2c' // Reemplaza INGRESAR_TOKEN_AQUI con tu token de autorización
            ],
        ]);
    
        // Ejecutar la solicitud cURL
        $response = curl_exec($curl);
    
        // Obtener el posible error de la solicitud cURL
        $err = curl_error($curl);
    
        // Cerrar la solicitud cURL
        curl_close($curl);
    
        // Verificar si hubo algún error durante la solicitud
        if ($err) {
            // Si hubo un error, retornar el mensaje de error
            return response()->json(['error' => "cURL Error #: $err"], 500);
        } else {
            // Si no hubo error, retornar la respuesta
            return response()->json(json_decode($response), 200);
        }
    }

}
