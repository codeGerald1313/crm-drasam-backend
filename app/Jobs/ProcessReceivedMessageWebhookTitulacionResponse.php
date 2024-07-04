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

class ProcessReceivedMessageWebhookTitulacionResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($message)
    {
        $this->message = $message['message'] ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Obtener el contacto con ID 24 (o el contacto correspondiente)
        $contact = Contact::find(53);
        if (!$contact) {
            Log::error("Contacto con ID 24 no encontrado");
            return;
        }
    
        // Verificar todas las conversaciones abiertas para el contacto
        $openConversations = Conversation::where('contact_id', $contact->id)
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();
    
        if ($openConversations->isEmpty()) {
            Log::info("No hay conversaciones abiertas para el contacto con ID " . $contact->id);
            return;
        }
    
        foreach ($openConversations as $lastConversation) {
            // Acción basada en el mensaje recibido
            $wspController = new WspController();
    
            // Por ejemplo, puedes llamar a un método del controlador WspController para procesar el mensaje
            $responseText = $this->message;
    
            // Generar y enviar mensaje estructurado
            $structuredMessage = $wspController->struct_message($responseText, $contact->num_phone);
            $messageResponse = $wspController->envia($structuredMessage);
    
            // Crear mensaje de bienvenida
            $wspController->createMessageWelcome($lastConversation, $messageResponse, $structuredMessage, $contact);
    
            // Ejemplo de evento de conversación creada
            event(new ConversationCreated(new NewConversationResource($lastConversation)));
        }
    }
    
}
