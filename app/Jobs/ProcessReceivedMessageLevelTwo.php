<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
use App\Helpers\YourClass;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\WspController;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Resources\NewConversationResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProcessReceivedMessageLevelTwo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mensajeId;
    protected $opcionId;
    protected $yourClass;

    public function __construct($data)
    {
        $this->mensajeId = $data['mensaje_id'];
        $this->opcionId = $data['opcion_id'];
        $this->yourClass = new YourClass();
    }

    public function handle(): void
    {
        // Opciones de selección
        $opciones = [
            "office_catastro" => "Oficina de Catastro 🗺️",
            "office_titulacion" => "Oficina de Titulación 📜",
            "office_saneamiento" => "Oficina de Saneamiento 🌍",
        ];

        // Mensajes personalizados para cada opción
        $responseMessages = [
            "office_catastro" => "Has seleccionado Oficina de Catastro 🗺️.",
            "office_titulacion" => "Has seleccionado Oficina de Titulación 📜.",
            "office_saneamiento" => "Has seleccionado Oficina de Saneamiento 🌍.",
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

        // Obtener el usuario autenticado
        $user = Auth::user();
        if (!$user) {
            Log::error("Usuario no autenticado");
            return;
        }

        // Acción basada en la opción seleccionada
        switch ($this->opcionId) {
            case "office_catastro":
                $this->handleOficinaCatastro($user, $responseMessages[$this->opcionId], $messageContent);
                break;
            case "office_titulacion":
                $this->handleOficinaTitulacion($user, $responseMessages[$this->opcionId], $messageContent);
                break;
            case "office_saneamiento":
                $this->handleOficinaSaneamiento($user, $responseMessages[$this->opcionId], $messageContent);
                break;
            default:
                Log::error("Opción no manejada: {$this->opcionId}");
                break;
        }
    }

    protected function handleOficinaCatastro($user, $responseMessage, $messageContent)
    {
        $contactId = 33;
        $advisorId = 2; // Advisor para Oficina de Catastro
        $welcomeMessage = "🌟 ¡Bienvenido a la Oficina de Catastro de Drasam CRM! 🌟\n\nEn la Oficina de Catastro, estamos aquí para ti, nuestra comunidad. 🗺️ Antes de comenzar, necesitamos tu número de DNI para brindarte la mejor asistencia posible. 📋\n\n¡Por favor, compártelo con nosotros para ayudarte a resolver tus consultas rápidamente! 🙌";
        $this->processAssignment($user, $contactId, $advisorId, $responseMessage, $messageContent, $welcomeMessage);
    }

    protected function handleOficinaTitulacion($user, $responseMessage, $messageContent)
    {
        $contactId = 53;
        $advisorId = 3; // Advisor para Oficina de Titulación
        $welcomeMessage = "🌟 ¡Bienvenido a la Oficina de Titulación de Drasam CRM! 🌟\n\nEn la Oficina de Titulación, estamos aquí para ti, nuestra comunidad. 📜\n\nPor favor, envíanos tu consulta personalizada y te ayudaremos a resolverla rápidamente. 🙌";
        $this->processAssignment($user, $contactId, $advisorId, $responseMessage, $messageContent, $welcomeMessage);
    }

    protected function handleOficinaSaneamiento($user, $responseMessage, $messageContent)
    {
        $contactId = 66;
        $advisorId = 4; // Advisor para Oficina de Saneamiento
        $welcomeMessage = "🌟 ¡Bienvenido a la Oficina de Saneamiento de Drasam CRM! 🌟\n\nEn la Oficina de Saneamiento, estamos aquí para ti, nuestra comunidad. 🌍 Antes de comenzar, necesitamos tu número de DNI para brindarte la mejor asistencia posible. 📋\n\n¡Por favor, compártelo con nosotros para ayudarte a resolver tus consultas rápidamente! 🙌";
        $this->processAssignment($user, $contactId, $advisorId, $responseMessage, $messageContent, $welcomeMessage);
    }

    protected function processAssignment($user, $contactId, $advisorId, $responseMessage, $messageContent, $welcomeMessage)
    {
        $contact = Contact::find($contactId);
        if (!$contact) {
            Log::error("Contacto con ID {$contactId} no encontrado");
            return;
        }

        // Crear primera conversación
        $conversation1 = Conversation::create([
            'contact_id' => $contactId,
            'status' => 'open',
            'status_bot' => 0,
            'start_date' => now(),
            'last_activity' => now()
        ]);
        
        // Crear nueva asignación con el usuario autenticado
        $userAssignment = Assignment::create([
            'contact_id' => $contactId,
            'conversation_id' => $conversation1->id,
            'advisor_id' => $user->id,
            'interes_en' => $this->opcionId
        ]);
        event(new ConversationCreated(new NewConversationResource($conversation1)));
        $this->yourClass->storeEventInfo(new NewConversationResource($conversation1));

        // Enviar mensaje estructurado predeterminado para la primera conversación
        $wspController = new WspController();
        $responseMessage1 = $wspController->struct_message($welcomeMessage, $contact->num_phone);

        // Enviar el mensaje estructurado para la primera conversación
        $messageResponse1 = $wspController->envia($responseMessage1);

        // Guardar el mensaje de bienvenida utilizando $messageResponse1 para la primera conversación
        $wspController->createMessageWelcome($conversation1, $messageResponse1, $responseMessage1, $user);

        // Registrar en el log el contenido de $messageResponse1
        Log::info('Contenido de messageResponse1:', ['response' => $messageResponse1]);

        // Crear segunda conversación
        $conversation2 = Conversation::create([
            'contact_id' => $contactId,
            'status' => 'open',
            'status_bot' => 0,
            'start_date' => now(),
            'last_activity' => now()
        ]);

        // Crear segunda asignación con advisor específico
        $advisorAssignment = Assignment::create([
            'contact_id' => $contactId,
            'conversation_id' => $conversation2->id,
            'advisor_id' => $advisorId,
            'interes_en' => $this->opcionId
        ]);
        event(new ConversationCreated(new NewConversationResource($conversation2)));
        $this->yourClass->storeEventInfo(new NewConversationResource($conversation2));

        // Enviar mensaje estructurado predeterminado para la segunda conversación
        $responseMessage2 = $wspController->struct_message($welcomeMessage, $contact->num_phone);

        // Enviar el mensaje estructurado para la segunda conversación
        $messageResponse2 = $wspController->envia($responseMessage2);

        // Guardar el mensaje de bienvenida utilizando $messageResponse2 para la segunda conversación
        $wspController->createMessageWelcome($conversation2, $messageResponse2, $responseMessage2, $user);

        // Registrar en el log el contenido de $messageResponse2
        Log::info('Contenido de messageResponse2:', ['response' => $messageResponse2]);
    }
}
