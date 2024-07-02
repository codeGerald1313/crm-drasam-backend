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

class ProcessReceivedMessageSatisfaction implements ShouldQueue
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
        // Opciones de calificación
        $opciones = [
            "rating_1" => "Muy malo 😠",
            "rating_2" => "Malo 😞",
            "rating_3" => "Regular 😐",
            "rating_4" => "Bueno 🙂",
            "rating_5" => "Muy bueno 😃",
        ];

        // Mensajes personalizados para cada calificación
        $responseMessages = [
            "rating_1" => "Has calificado nuestro servicio como Muy malo 😠. Lamentamos escuchar esto y trabajaremos para mejorar.",
            "rating_2" => "Has calificado nuestro servicio como Malo 😞. Agradecemos tu feedback y buscaremos mejorar.",
            "rating_3" => "Has calificado nuestro servicio como Regular 😐. Gracias por tu feedback.",
            "rating_4" => "Has calificado nuestro servicio como Bueno 🙂. Nos alegra que hayas tenido una buena experiencia.",
            "rating_5" => "Has calificado nuestro servicio como Muy bueno 😃. ¡Gracias por tu excelente feedback!",
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

            // Acción basada en la calificación seleccionada
            $this->sendMessageAndLog($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
        }
    }

    protected function sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Enviar mensaje de respuesta
        $response_message = $wspController->struct_message($responseMessage, $contact->num_phone);
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
