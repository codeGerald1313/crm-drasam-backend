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

class ProcessReceivedMessageDrasam implements ShouldQueue
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
            "1" => "DIRECCIÓN REGIONAL",
            "2" => "TRANSPARENCIA",
            "3" => "OCI",
            "4" => "OPyEA",
            "5" => "ASES. JURÍDICA",
            "6" => "DOA",
            "7" => "DTRTYCR",
            "8" => "DIA",
            "9" => "DDCA",
        ];

        // Mensajes personalizados para cada opción
        $responseMessages = [
            "1" => "¡La DRASAM está a cargo del director regional, máxima autoridad técnica normativa, y es el órgano de línea de la Gerencia Regional de Desarrollo Económico responsable de formular, adecuar, implementar, controlar y evaluar las políticas públicas sectoriales de desarrollo agrario regional. 

Para tal efecto, se encarga de dirigir, ejecutar, supervisar, coordinar, administrar y controlar los procesos técnicos vinculados con la agricultura, conforme a las competencias correspondientes al Gobierno Regional y las disposiciones establecidas por la Gerencia Regional de Desarrollo Económico. 🏢",
            "2" => "Brindar información en el marco de la Ley N° 27806, Ley de Transparencia y Acceso a la Información Pública. 

 

Atender las solicitudes de acceso a la información dentro de plazo de Ley, (7 días útiles contados a partir del día siguiente de presentado la solicitud de acceso a la informado ante la DRASAM, puede prorrogarse por 5 días útiles adicionales. De media circunstancias que hagan inusualmente difícil reunir la información solicitada. En este caso, el responsable de la Oficina de Transparencia y Acceso a la Información Pública comunicará por escrito antes del vencimiento del primer plazo. 

 

Solicitar información a las Oficinas o Áreas de la DRASAM la información necesaria para el libre acceso a la información pública por parte de la ciudadanía y su publicación en el portal de transparencia. 

 

Entregar al usuario la Información previa comprobación de la cancelación del costo de reproducción, según normado en el TUPA. 

 

Si la solicitud debe rechazarse por situaciones expresas en la ley de transparencia, se comunicará que no puede ser atendido por escrito al usuario solicitante, señalando las razones y la excepción o excepciones que justifican su negativa total o parcialmente, la información. 

 

Recibir los recursos de apelación que se hayan interpuesta contra la denegatoria total o parcial de la solicitud de acceso a la información, y cuando corresponda elevarlos a la Dirección de Operaciones-Agraria. 

 

Otras funciones que la Dirección de Operaciones - Agraria lo encomiende.  📊",
            "3" => "Es la unidad encargada de lograr la ejecución de control gubernamental en la DRASAM, mantiene dependencia técnica y funcional de la Contraloría General de la República y depende administrativamente del director regional de Agricultura San Martín, tiene asignado las siguientes funciones (Concordante con Directiva N° 007-2015-CG/PROCAL, aprobado con Resolución de Contraloría N° 353-2015-CG .  🛡️",
            "4" => "¡Hola! Has accedido a las opciones del CRM de la Oficina de Planeamiento y Estadística Agraria. Explora nuestros datos y herramientas. 📈",
            "5" => "Informar sobre proyectos de carácter legal que formulen las diferentes dependencias. 

Formular resoluciones, contratos, convenios y adendas  

Absolver las consultas de carácter jurídico legal 

Tramitar los recursos administrativos  ⚖️",
            "6" => "Controlar los sistemas administrativos de contabilidad, tesorería, logística, control patrimonial y gestión de recursos humanos. 

Subir contratos en el PAAC 

Emitir resoluciones administrativas 

Apoyar en gestión administrativa las acciones de las ADELs. 🚜",
            "7" => "¡Hola! Has accedido a las opciones del CRM de la Dirección de Titulación, Reversión de Tierras y Catastro Rural. Explora nuestros servicios y documentos. 🗺️",
            "8" => "¡Hola! Has accedido a las opciones del CRM de la Dirección de Infraestructura Agraria. Encuentra aquí todos nuestros recursos y servicios. 🏗️",
            "9" => "Cumplir y hacer cumplir el reglamento. 

 

Recomendar prioridades de política agraria para el departamento, en el marco de los instrumentos de desarrollo agrario regional. 

 

Conducir la formulación y aplicación del plan operativo agrario articulado regional y reportar avances de metas a las correspondientes instancias de planificación de los (3) niveles de gobierno. 

 

Participar en la articulación territorial de los programas presupuestales del sector agricultura y riego. 

 

Promover entre sus miembros, alianzas estratégicas para el uso eficiente de los recursos. 

 

Proponer la priorización de los proyectos de inversión en productos agrícolas, pecuarios y forestales del departamento. 

 

Promover la elaboración de estrategias de acceso a la información y difusión de mercado. 

 

Facilitar el acceso a los servicios que brinda el sector agricultura y riego 

 

Aprobar la participación de otras instituciones u organizaciones públicas ylo privadas en sesiones del comité de gestión regional agrario. 

 

Aprobar la participación del comité de gestión regional agrario o su representación en otros espacios de coordinación regional interregional e intergubernamental. 

 

Decidir la formación comisiones técnicas de trabajo que operen los acuerdos para el desarrollo agrario departamental. Estas comisiones técnicas son dirigidas por un miembro del CGRA y están integrados por otros miembros y por representantes de otras instituciones públicas o privadas relacionadas con la temática que trabaje la referida comisión. 

Aprobar el plan de trabajo y el cronograma que elaboran las comisiones técnicas de trabajos constituidos en el CGRA. 

 

Establecer mecanismos de seguimiento para la ejecución comprometida y obligatoria de las de las actividades y los acuerdos tomados por las instituciones integrantes de CGRA. 

 

Promover acciones de capacitación y asistencia a los representantes que integran los CGRA, para mejorar sus actividades y funcionamientos 🌾",
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
                case "1":
                    $this->handleDireccionRegional($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "2":
                    $this->handleTransparencia($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "3":
                    $this->handleOCI($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "4":
                    $this->handleOPEA($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "5":
                    $this->handleAsesoriaJuridica($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "6":
                    $this->handleDOA($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "7":
                    $this->handleDTRTYCR($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "8":
                    $this->handleDIA($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                case "9":
                    $this->handleDDCA($wspController, $responseMessages[$this->opcionId], $contact, $lastConversation, $messageContent);
                    break;
                default:
                    Log::error("Opción no manejada: {$this->opcionId}");
                    break;
            }
        }
    }

    protected function handleDireccionRegional($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "DIRECCIÓN REGIONAL"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleTransparencia($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "TRANSPARENCIA"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleOCI($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "OCI"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleOPEA($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);

        $optionsMessage = "Escoge una de las siguientes opciones";
        $interactiveMessage = $wspController->struct_messages_list_opyea($optionsMessage, $contact->num_phone);

        // Enviar el mensaje estructurado
        $messageResponse = $wspController->envia($interactiveMessage);

        // Incorporar messageContent en messageResponse
        $messageResponse['messageContent'] = $messageContent;
        Log::info('Mensaje enviado para OPyEA:', ['response' => $messageResponse]);

        // Guardar el mensaje utilizando $messageResponse
        $wspController->createMessageWelcome($lastConversation, $messageResponse, [
            'type' => 'interactive',
            'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
            'timestamp' => now()->timestamp
        ], $contact);

        // Llamar a sendMessageAndLog
        event(new ConversationCreated(new NewConversationResource($lastConversation)));
    }

    protected function handleAsesoriaJuridica($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "ASES. JURÍDICA"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);
    }

    protected function handleDOA($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
         // Aquí va la lógica específica para la opción "DTRTYCR"
         $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);

         $optionsMessage = "Escoge una de las siguientes opciones";
         $interactiveMessage = $wspController->struct_messages_list_doa($optionsMessage, $contact->num_phone);
 
         // Enviar el mensaje estructurado
         $messageResponse = $wspController->envia($interactiveMessage);
 
         // Incorporar messageContent en messageResponse
         $messageResponse['messageContent'] = $messageContent;
         Log::info('Mensaje enviado para OPyEA:', ['response' => $messageResponse]);
 
         // Guardar el mensaje utilizando $messageResponse
         $wspController->createMessageWelcome($lastConversation, $messageResponse, [
             'type' => 'interactive',
             'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
             'timestamp' => now()->timestamp
         ], $contact);
 
         // Llamar a sendMessageAndLog
         event(new ConversationCreated(new NewConversationResource($lastConversation)));
    }

    protected function handleDTRTYCR($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        // Aquí va la lógica específica para la opción "DTRTYCR"
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);

        $optionsMessage = "Escoge una de las siguientes opciones";
        $interactiveMessage = $wspController->struct_messages_list_dtrtycr($optionsMessage, $contact->num_phone);

        // Enviar el mensaje estructurado
        $messageResponse = $wspController->envia($interactiveMessage);

        // Incorporar messageContent en messageResponse
        $messageResponse['messageContent'] = $messageContent;
        Log::info('Mensaje enviado para OPyEA:', ['response' => $messageResponse]);

        // Guardar el mensaje utilizando $messageResponse
        $wspController->createMessageWelcome($lastConversation, $messageResponse, [
            'type' => 'interactive',
            'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
            'timestamp' => now()->timestamp
        ], $contact);

        // Llamar a sendMessageAndLog
        event(new ConversationCreated(new NewConversationResource($lastConversation)));
    }

    protected function handleDIA($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);

        $optionsMessage = "Escoge una de las siguientes opciones";
        $interactiveMessage = $wspController->struct_messages_list_dia($optionsMessage, $contact->num_phone);

        // Enviar el mensaje estructurado
        $messageResponse = $wspController->envia($interactiveMessage);

        // Incorporar messageContent en messageResponse
        $messageResponse['messageContent'] = $messageContent;
        Log::info('Mensaje enviado para OPyEA:', ['response' => $messageResponse]);

        // Guardar el mensaje utilizando $messageResponse
        $wspController->createMessageWelcome($lastConversation, $messageResponse, [
            'type' => 'interactive',
            'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
            'timestamp' => now()->timestamp
        ], $contact);

        // Llamar a sendMessageAndLog
        event(new ConversationCreated(new NewConversationResource($lastConversation)));
    }

    protected function handleDDCA($wspController, $responseMessage, $contact, $lastConversation, $messageContent)
    {
        $this->sendMessageAndLog($wspController, $responseMessage, $contact, $lastConversation, $messageContent);

        $optionsMessage = "Escoge una de las siguientes opciones";
        $interactiveMessage = $wspController->struct_messages_list_ddca($optionsMessage, $contact->num_phone);

        // Enviar el mensaje estructurado
        $messageResponse = $wspController->envia($interactiveMessage);

        // Incorporar messageContent en messageResponse
        $messageResponse['messageContent'] = $messageContent;
        Log::info('Mensaje enviado para OPyEA:', ['response' => $messageResponse]);

        // Guardar el mensaje utilizando $messageResponse
        $wspController->createMessageWelcome($lastConversation, $messageResponse, [
            'type' => 'interactive',
            'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
            'timestamp' => now()->timestamp
        ], $contact);

        // Llamar a sendMessageAndLog
        event(new ConversationCreated(new NewConversationResource($lastConversation)));
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
