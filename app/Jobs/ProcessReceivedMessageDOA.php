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

class ProcessReceivedMessageDOA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mensajeId;
    protected $opcionId;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->mensajeId = $data['mensaje_id'];
        $this->opcionId = $data['opcion_id'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Opciones de selección
        $opciones = [
            "doa_opcion_gestion" => "OF. GESTIÓN ADMIN. 🏢",
            "doa_opcion_presupuestal" => "OF. GESTIÓN PRESUP. 💰",
        ];
        
        // Mensajes personalizados para cada opción con emojis
        $responseMessages = [
            "doa_opcion_gestion" => "Has seleccionado OF. GESTIÓN ADMIN. 🏢.",
            "doa_opcion_presupuestal" => "Has seleccionado OF. GESTIÓN PRESUP. 💰.",
        ];
        
        
        // Obtener el valor seleccionado
        $selectedOption = $opciones[$this->opcionId] ?? null;
        if (!$selectedOption) {
            // Manejar error si la opción no es válida
            Log::error("Opción no válida: {$this->opcionId}");
            return;
        }

        // Crear el contenido del mensaje
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

        // Obtener el contacto con id 24
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

            // Acción basada en la opción seleccionada
            switch ($this->opcionId) {
                case "doa_opcion_gestion":
                    $this->handleGestionAdministrativa($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_opcion_presupuestal":
                    $this->handleGestionPresupuestal($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                default:
                    Log::error("Opción no manejada: {$this->opcionId}");
                    break;
            }
        }
    }

    protected function handleGestionAdministrativa($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);

        $optionsMessage = "Escoge una de las siguientes opciones";
        $interactiveMessage = $wspController->struct_messages_list_doa_administrative_managanment($optionsMessage, $contact->num_phone);

        // Enviar el mensaje estructurado
        $messageResponse = $wspController->envia($interactiveMessage);

        // Incorporar messageContent en messageResponse
        $messageResponse['messageContent'] = $messageContent;
        Log::info('Mensaje enviado para DOA-AREAS:', ['response' => $messageResponse]);

        // Guardar el mensaje utilizando $messageResponse
        $wspController->createMessageWelcome($lastConversation, $messageResponse, [
            'type' => 'interactive',
            'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
            'timestamp' => now()->timestamp
        ], $contact);

        // Llamar a sendMessageAndLog
        event(new ConversationCreated(new NewConversationResource($lastConversation)));
    }

    protected function handleGestionPresupuestal($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "OFICINA DE GESTIÓN PRESUPUESTAL"
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
