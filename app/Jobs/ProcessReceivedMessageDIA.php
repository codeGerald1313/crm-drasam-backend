<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\WspController;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Resources\NewConversationResource;
use Illuminate\Support\Facades\Log;

class ProcessReceivedMessageDIA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mensajeId;
    protected $opcionId;

    public function __construct($data)
    {
        $this->mensajeId = $data['mensaje_id'];
        $this->opcionId = $data['opcion_id'];
    }

    public function handle(): void
    {
        $opciones = [
            "dia_opcion_riego_tecnificado" => "PROGRAMA DE RIEGO TECNIFICADO 💧",
            "dia_opcion_supervision_agua" => "ÁREA DE SUPERVISIÓN DE AGUA DE RIEGO 🌊",
            "dia_opcion_maquinaria_agricola" => "ÁREA DE MAQUINARIA AGRÍCOLA, AGROINDUSTRIAL Y PESADA 🚜"
        ];

        $responseMessages = [
            "dia_opcion_riego_tecnificado" => "Has seleccionado PROGRAMA DE RIEGO TECNIFICADO 💧.",
            "dia_opcion_supervision_agua" => "Has seleccionado ÁREA DE SUPERVISIÓN DE AGUA DE RIEGO 🌊.",
            "dia_opcion_maquinaria_agricola" => "Has seleccionado ÁREA DE MAQUINARIA AGRÍCOLA, AGROINDUSTRIAL Y PESADA 🚜."
        ];

        $selectedOption = $opciones[$this->opcionId] ?? null;
        if (!$selectedOption) {
            Log::error("Opción no válida: {$this->opcionId}");
            return;
        }

        $responseMessageFlujo = [
            'id' => $this->mensajeId,
            'title' => $selectedOption
        ];

        $responseMessage = [
            'body' => $responseMessages[$this->opcionId] ?? $selectedOption
        ];

        $messageContent = [
            'type' => 'text',
            'text' => $responseMessage,
            'timestamp' => now()->timestamp
        ];

        $contact = Contact::find(24);
        if (!$contact) {
            Log::error("Contacto con ID 24 no encontrado");
            return;
        }

        $lastConversation = Conversation::where('contact_id', $contact->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastConversation && $lastConversation->status == 'open') {
            $wspController = new WspController();
            switch ($this->opcionId) {
                case "dia_opcion_riego_tecnificado":
                    $this->handleRiegoTecnificado($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "dia_opcion_supervision_agua":
                    $this->handleSupervisionAgua($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "dia_opcion_maquinaria_agricola":
                    $this->handleMaquinariaAgricola($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                default:
                    Log::error("Opción no manejada: {$this->opcionId}");
                    break;
            }
        }
    }

    protected function handleRiegoTecnificado($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleSupervisionAgua($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleMaquinariaAgricola($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Enviar mensaje de respuesta
        $response_message = $wspController->struct_Message($responseMessage, $contact->num_phone);
        $messageResponse = $wspController->envia($response_message);

        // Incorporar messageContent en messageResponse
        $messageResponse['messageContent'] = $messageContent;

        // Registrar en el log el contenido de $messageResponse
        Log::info('Contenido de messageResponse:', ['response' => $messageResponse]);

        // Guardar el mensaje de bienvenida utilizando $messageResponse
        $wspController->saveMessageChatBootInit($lastConversation, $messageResponse);

        event(new ConversationCreated(new NewConversationResource($lastConversation)));
    }
}
