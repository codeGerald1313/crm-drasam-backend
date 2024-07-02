<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
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

    public function __construct($data)
    {
        $this->mensajeId = $data['mensaje_id'];
        $this->opcionId = $data['opcion_id'];
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
        // ID de contacto específico para Oficina de Catastro
        $contactId = 33; 
        $welcomeMessage = "🌟 ¡Bienvenido a la Oficina de Catastro de Drasam CRM! 🌟\n\nEn la Oficina de Catastro, estamos aquí para ti, nuestra comunidad. 🗺️ Antes de comenzar, necesitamos tu número de DNI para brindarte la mejor asistencia posible. 📋\n\n¡Por favor, compártelo con nosotros para ayudarte a resolver tus consultas rápidamente! 🙌";
        $this->processAssignment($user, $contactId, $responseMessage, $messageContent, $welcomeMessage);
    }

    protected function handleOficinaTitulacion($user, $responseMessage, $messageContent)
    {
        // ID de contacto específico para Oficina de Titulación
        $contactId = 53;
        $welcomeMessage = "🌟 ¡Bienvenido a la Oficina de Titulación de Drasam CRM! 🌟\n\nEn la Oficina de Titulación, estamos aquí para ti, nuestra comunidad. 📜 Antes de comenzar, necesitamos tu número de DNI para brindarte la mejor asistencia posible. 📋\n\n¡Por favor, compártelo con nosotros para ayudarte a resolver tus consultas rápidamente! 🙌";
        $this->processAssignment($user, $contactId, $responseMessage, $messageContent, $welcomeMessage);
    }

    protected function handleOficinaSaneamiento($user, $responseMessage, $messageContent)
    {
        // ID de contacto específico para Oficina de Saneamiento
        $contactId = 66;
        $welcomeMessage = "🌟 ¡Bienvenido a la Oficina de Saneamiento de Drasam CRM! 🌟\n\nEn la Oficina de Saneamiento, estamos aquí para ti, nuestra comunidad. 🌍 Antes de comenzar, necesitamos tu número de DNI para brindarte la mejor asistencia posible. 📋\n\n¡Por favor, compártelo con nosotros para ayudarte a resolver tus consultas rápidamente! 🙌";
        $this->processAssignment($user, $contactId, $responseMessage, $messageContent, $welcomeMessage);
    }

    protected function processAssignment($user, $contactId, $responseMessage, $messageContent, $welcomeMessage)
    {
        $contact = Contact::find($contactId);
        if (!$contact) {
            Log::error("Contacto con ID {$contactId} no encontrado");
            return;
        }

        $conversation = Conversation::create([
            'contact_id' => $contactId,
            'status' => 'open',
            'status_bot' => 0,
            'start_date' => now(),
            'last_activity' => now()
        ]);

        // Crear nueva asignación
        $assignment = Assignment::create([
            'contact_id' => $contactId,
            'conversation_id' => $conversation->id,
            'advisor_id' => $user->id,
            'interes_en' => $this->opcionId // Puedes ajustar esto según tus necesidades
        ]);

        // Enviar mensaje estructurado predeterminado
        $wspController = new WspController();
        $responseMessage = $wspController->struct_message($welcomeMessage, $contact->num_phone);

        // Enviar el mensaje estructurado
        $messageResponse = $wspController->envia($responseMessage);

        // Guardar el mensaje de bienvenida utilizando $messageResponse
        $wspController->createMessageWelcome($conversation, $messageResponse, $responseMessage, $user);

        // Registrar en el log el contenido de $messageResponse
        Log::info('Contenido de messageResponse:', ['response' => $messageResponse]);

        // Disparar evento de conversación creada
        event(new ConversationCreated(new NewConversationResource($conversation)));
    }
}
