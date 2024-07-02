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

class ProcessReceivedMessageOPEA implements ShouldQueue
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
            "opea_opcion_estrategia" => "ESTRATEGIA 📊",
            "opea_opcion_monitoreo" => "MONITOREO 📈",
            "opea_opcion_inversiones" => "INVERSIONES 💼",
            "opea_opcion_informatica" => "INFORMÁTICA 💻",
            "opea_opcion_estadistica" => "ESTADÍSTICA 📉",
        ];

        // Mensajes personalizados para cada opción
        $responseMessages = [
            "opea_opcion_estrategia" => "Has seleccionado ESTRATEGIA 📊.",
            "opea_opcion_monitoreo" => "Has seleccionado MONITOREO 📈.",
            "opea_opcion_inversiones" => "Has seleccionado INVERSIONES 💼.",
            "opea_opcion_informatica" => "Has seleccionado INFORMÁTICA 💻.",
            "opea_opcion_estadistica" => "Has seleccionado ESTADÍSTICA 📉.",
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
                case "opea_opcion_estrategia":
                    $this->handleEstrategia($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "opea_opcion_monitoreo":
                    $this->handleMonitoreo($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "opea_opcion_inversiones":
                    $this->handleInversiones($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "opea_opcion_informatica":
                    $this->handleInformatica($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "opea_opcion_estadistica":
                    $this->handleEstadistica($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                default:
                    Log::error("Opción no manejada: {$this->opcionId}");
                    break;
            }
        }
    }

    protected function handleEstrategia($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "ESTRATEGIA 📊"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleMonitoreo($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "MONITOREO 📈"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleInversiones($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "INVERSIONES 💼"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleInformatica($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "INFORMÁTICA 💻"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleEstadistica($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "ESTADÍSTICA 📉"
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
