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

class ProcessReceivedMessageAdministrativeManagement implements ShouldQueue
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
        // Opciones de selección
        $opciones = [
            "doa_area_contabilidad" => "Área de Contabilidad 🧮",
            "doa_area_tesoreria" => "Área de Tesorería 💰",
            "doa_area_recursos_humanos" => "Área de Recursos Humanos 👥",
            "doa_area_logistica" => "Área de Logística 🚚",
            "doa_area_control_patrimonial" => "Área de Control Patrimonial 🏛️",
            "doa_area_archivo_biblioteca" => "Área de Archivo y Biblioteca 📚",
            "doa_area_ti" => "Área de TI 💻",
            "doa_area_tramite_documentario" => "Área de Trámite Documentario 📑",
        ];

        // Mensajes personalizados para cada opción
        $responseMessages = [
            "doa_area_contabilidad" => "Has seleccionado Área de Contabilidad 🧮.",
            "doa_area_tesoreria" => "Has seleccionado Área de Tesorería 💰.",
            "doa_area_recursos_humanos" => "Has seleccionado Área de Recursos Humanos 👥.",
            "doa_area_logistica" => "Has seleccionado Área de Logística 🚚.",
            "doa_area_control_patrimonial" => "Has seleccionado Área de Control Patrimonial 🏛️.",
            "doa_area_archivo_biblioteca" => "Has seleccionado Área de Archivo y Biblioteca 📚.",
            "doa_area_ti" => "Has seleccionado Área de TI 💻.",
            "doa_area_tramite_documentario" => "Has seleccionado Área de Trámite Documentario 📑.",
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
                case "doa_area_contabilidad":
                    $this->handleContabilidad($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_tesoreria":
                    $this->handleTesoreria($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_recursos_humanos":
                    $this->handleRecursosHumanos($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_logistica":
                    $this->handleLogistica($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_control_patrimonial":
                    $this->handleControlPatrimonial($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_archivo_biblioteca":
                    $this->handleArchivoBiblioteca($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_ti":
                    $this->handleTI($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "doa_area_tramite_documentario":
                    $this->handleTramiteDocumentario($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                default:
                    Log::error("Opción no manejada: {$this->opcionId}");
                    break;
            }
        }
    }

    protected function handleContabilidad($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Contabilidad"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleTesoreria($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Tesorería"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleRecursosHumanos($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Recursos Humanos"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleLogistica($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Logística"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleControlPatrimonial($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Control Patrimonial"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleArchivoBiblioteca($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Archivo y Biblioteca"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleTI($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de TI"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleTramiteDocumentario($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Área de Trámite Documentario"
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
