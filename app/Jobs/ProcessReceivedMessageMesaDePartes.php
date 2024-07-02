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

class ProcessReceivedMessageMesaDePartes implements ShouldQueue
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
        // Opciones de selección con descripción extendida
        $opciones = [
            "mesa_de_partes_opcion_rectificacion" => "Rectificación de áreas y linderos 🗺️.",
            "mesa_de_partes_opcion_diagnostico" => "Diagnóstico del área de catastro 💼 (27 soles según TUPA).",
            "mesa_de_partes_opcion_evaluacion_expediente" => "Evaluación de expediente 📋 (27 soles).",
            "mesa_de_partes_opcion_informacion_coordenadas" => "Información de coordenadas 📍. Necesitamos la solicitud, el plano o punto de coordenadas y la copia del DNI.",
            "mesa_de_partes_opcion_copias_fedateadas" => "Solicitud de copias fedateadas 📄. Presentar la solicitud y copia del DNI.",
            "mesa_de_partes_opcion_numero_expediente" => "Acceso al número de expediente del GSTRAMITE 📁.",
            "mesa_de_partes_opcion_asignacion_codigo" => "Asignación de código 🏢 con referencia al Área de Catastro.",
        ];

        // Mensajes personalizados para cada opción
        $responseMessages = [
            "mesa_de_partes_opcion_rectificacion" => "Has seleccionado Rectificación de áreas y linderos 🗺️.",
            "mesa_de_partes_opcion_diagnostico" => "Has seleccionado Diagnóstico del área de catastro 💼 (27 soles según TUPA).",
            "mesa_de_partes_opcion_evaluacion_expediente" => "Has seleccionado Evaluación de expediente 📋 (27 soles).",
            "mesa_de_partes_opcion_informacion_coordenadas" => "Has seleccionado Información de coordenadas 📍. Necesitamos la solicitud, el plano o punto de coordenadas y la copia del DNI.",
            "mesa_de_partes_opcion_copias_fedateadas" => "Has seleccionado Solicitud de copias fedateadas 📄. Presentar la solicitud y copia del DNI.",
            "mesa_de_partes_opcion_numero_expediente" => "Has seleccionado Acceso al número de expediente del GSTRAMITE 📁.",
            "mesa_de_partes_opcion_asignacion_codigo" => "Has seleccionado Asignación de código 🏢 con referencia al Área de Catastro.",
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
                case "mesa_de_partes_opcion_rectificacion":
                    $this->handleRectificacion($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "mesa_de_partes_opcion_diagnostico":
                    $this->handleDiagnostico($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "mesa_de_partes_opcion_evaluacion_expediente":
                    $this->handleEvaluacionExpediente($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "mesa_de_partes_opcion_informacion_coordenadas":
                    $this->handleInformacionCoordenadas($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "mesa_de_partes_opcion_copias_fedateadas":
                    $this->handleCopiasFedateadas($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "mesa_de_partes_opcion_numero_expediente":
                    $this->handleNumeroExpediente($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "mesa_de_partes_opcion_asignacion_codigo":
                    $this->handleAsignacionCodigo($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                default:
                    Log::error("Opción no manejada: {$this->opcionId}");
                    break;
            }
        }
    }

    protected function handleRectificacion($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Rectificación de áreas y linderos."
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleDiagnostico($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Diagnóstico por parte del área de catastro"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleEvaluacionExpediente($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Evaluación de expediente"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleInformacionCoordenadas($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Información de coordenadas"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleCopiasFedateadas($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Solicitud de copias fedateadas"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleNumeroExpediente($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Acceso de número de expediente del GSTRAMITE"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleAsignacionCodigo($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "Asignación de código con referencia al Área de Catastro"
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
