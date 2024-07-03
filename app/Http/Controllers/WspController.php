<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Conexion;
use App\Models\Assignment;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\ProcessWebhookEvent;
use App\Events\ConversationCreated;
use App\Traits\SaveAttachmentTrait;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessReceivedMessage;
use App\Models\ConversationReopening;
use App\Http\Resources\NewConversationResource;

class WspController extends Controller
{
    use SaveAttachmentTrait;

    public function sendMessage(Request $request)
    {
        try {
            // Obtención de usuario autenticado
            $user = auth()->user();
            $body = $request->all();

            // Verificar que el tipo de mensaje no sea 'note' y enviar el mensaje
            if ($body['type'] != 'note') {
                $response = $this->envia($body);
            }

            // Buscar el contacto por número de teléfono
            $contact = Contact::where('num_phone', $body['to'])->first();

            // Obtener el conversation_id desde el request
            $conversationId = $body['conversation_id'];

            // Buscar la conversación con el ID proporcionado
            $conversacion = Conversation::find($conversationId);

            // Verificar si la conversación existe y está abierta
            if (!$conversacion) {
                $conversacion = $this->createConversation($contact);
            } elseif ($conversacion->status != 'open') {
                $conversacion = $this->createConversationClose($conversacion, $user);
            }

            // Crear el mensaje
            if ($body['type'] != 'note') {
                $this->createMessage($conversacion, $response, $body, $user);
            } else {
                $this->createMessageNote($conversacion, $body, $user);
                event(new ConversationCreated([
                    'notes' => $conversacion->id
                ]));
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => $body['type'] != 'note' ? $response : '',
                'conversation_id' => $conversacion->id
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el mensaje',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function webhook(Request $request)
    {

        $data = json_decode($request->getContent(), true);

        try {
            $bodyContent = $data['entry'][0]['changes'][0]['value'];

            //VALIDATE REQUEST SEND MESSAGES
            if ($bodyContent['messaging_product'] === 'whatsapp' && isset($bodyContent['statuses'])) {
                ProcessWebhookEvent::dispatch($bodyContent)->onQueue('webhooks');
            }

            //VALIDATE MESSAGES
            if ($bodyContent['messaging_product'] === 'whatsapp' && isset($bodyContent['messages'])) {
                ProcessReceivedMessage::dispatch($bodyContent)->onQueue('received_messages');
            }
        } catch (\Throwable $th) {
            Log::error('Respuesta de error', [$th->getMessage()]);
        }
    }

    //Registrar convesación
    public function createConversation($contact)
    {
        $conversation = Conversation::create([
            'contact_id' => $contact->id,
            'status' => 'open',
            'status_bot' => 0
        ]);

        Assignment::create([
            'contact_id' => $contact->contact_id,
            'conversation_id' => $conversation->id,
            'time' => now(),
            'reason_id' => null
        ]);

        return $conversation;
    }

    public function createConversationClose($lastConversation, $user)
    {
        $lastConversation->update([
            'status' => 'open',
            'status_bot' => 0
        ]);

        Assignment::create([
            'advisor_id' => $user->id,
            'contact_id' => $lastConversation->contact_id,
            'conversation_id' => $lastConversation->id,
            'time' => now(),
            'reason_id' => null
        ]);
    }

    public function createMessageNote($conversation, $body, $user)
    {
        Message::create([
            'conversation_id' => $conversation->id,
            'api_id' => time() . now(),
            'content' => json_encode($body['text']),
            'type' => $body['type'],
            'date_of_issue' => now(),
            'status' => null,
            'emisor_id' => $user->id,
            'emisor' => 'Advisor'
        ]);
    }

    //Registrar mensaje
    public function createMessage($conversation, $response, $body, $user)
    {

        switch ($body['type']) {
            case 'text':
                $menssage = $body['text'];
                break;
            case 'image':
                $menssage = $body['image'];
                break;
            case 'audio':
                $menssage = $body['audio'];
                break;
            case 'video':
                $menssage = $body['video'];
                break;
            case 'document':
                $menssage = $body['document'];
                break;
            case 'template':
                $menssage = $body['template'];
                break;
            default:
                $menssage = 'Tipo de mensaje no encontrado';
                break;
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'api_id' => $response['messages'][0]['id'],
            'content' => json_encode($menssage),
            'type' => $body['type'],
            'date_of_issue' => now(),
            'status' => null,
            'emisor_id' => $user->id,
            'emisor' => 'Advisor'
        ]);
    }

    public function createMessageWelcome($conversation, $response, $body, $user)
    {
        switch ($body['type']) {
            case 'text':
                $menssage = is_array($body['text']) ? $body['text'] : ['body' => $body['text']];
                break;
            case 'image':
                $menssage = $body['image'];
                break;
            case 'audio':
                $menssage = $body['audio'];
                break;
            case 'video':
                $menssage = $body['video'];
                break;
            case 'document':
                $menssage = $body['document'];
                break;
            case 'template':
                $menssage = $body['template'];
                break;
            case 'interactive':
                // Verificar si el contenido interactivo está en el formato esperado
                $menssage = is_array($body['interactive']) ? $body['interactive'] : ['body' => $body['interactive']];
                break;
            default:
                $menssage = 'Tipo de mensaje no encontrado';
                break;
        }
        Message::create([
            'conversation_id' => $conversation->id,
            'api_id' => $response['messages'][0]['id'],
            'content' => json_encode($menssage),
            'type' => $body['type'],
            'date_of_issue' => now(),
            'status' => null,
            'emisor_id' => $user->id,
            'emisor' => 'Customer' // Establecer el emisor como 'Customer'
        ]);
    }

    public function struct_messages_list_ddca($text, $num)
    {
        $mensaje = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $num,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $text,
                ],
                'action' => [
                    'button' => 'Ver opciones',
                    'sections' => [
                        [
                            'title' => 'Opciones DDCA',
                            'rows' => [
                                [
                                    'id' => 'ddca_opcion_gestion',
                                    'title' => 'Gestión Agro 🌾',
                                ],
                                [
                                    'id' => 'ddca_opcion_desarrollo',
                                    'title' => 'Desarrollo Agro-F 🚜', // Ajustado a 20 caracteres
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $mensaje;
    }

    public function struct_messages_list_mesa_de_partes($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Mesa de Partes-Opciones",
                            "rows" => [
                                [
                                    "id" => "mesa_de_partes_opcion_rectificacion",
                                    "title" => "Rectificación áreas 🗺️"
                                ],
                                [
                                    "id" => "mesa_de_partes_opcion_diagnostico",
                                    "title" => "Diagnóstico área 📊"
                                ],
                                [
                                    "id" => "mesa_de_partes_opcion_evaluacion_expediente",
                                    "title" => "Evaluación expediente 📋"
                                ],
                                [
                                    "id" => "mesa_de_partes_opcion_informacion_coordenadas",
                                    "title" => "Info coordenadas 📍"
                                ],
                                [
                                    "id" => "mesa_de_partes_opcion_copias_fedateadas",
                                    "title" => "Solicitud copias 📄"
                                ],
                                [
                                    "id" => "mesa_de_partes_opcion_numero_expediente",
                                    "title" => "Acceso expediente 📁"
                                ],
                                [
                                    "id" => "mesa_de_partes_opcion_asignacion_codigo",
                                    "title" => "Asignación código 🏢"
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $mensaje;
    }

    public function struct_messages_list_doa_administrative_managanment($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Gestión Admimistrativa",
                            "rows" => [
                                [
                                    "id" => "doa_area_contabilidad",
                                    "title" => "Contabilidad 🧮",
                                ],
                                [
                                    "id" => "doa_area_tesoreria",
                                    "title" => "Tesorería 💰",
                                ],
                                [
                                    "id" => "doa_area_recursos_humanos",
                                    "title" => "Recursos Humanos 👥",
                                ],
                                [
                                    "id" => "doa_area_logistica",
                                    "title" => "Logística 🚚",
                                ],
                                [
                                    "id" => "doa_area_control_patrimonial",
                                    "title" => "Control Patrimonial 🏛️",
                                ],
                                [
                                    "id" => "doa_area_archivo_biblioteca",
                                    "title" => "Archivo y Biblioteca 📚",
                                ],
                                [
                                    "id" => "doa_area_ti",
                                    "title" => "Área de TI 💻",
                                ],
                                [
                                    "id" => "doa_area_tramite_documentario",
                                    "title" => "Trámite Documentario 📑",
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $mensaje;
    }



    public function struct_messages_list_doa($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Opciones DOA",
                            "rows" => [
                                [
                                    "id" => "doa_opcion_gestion",
                                    "title" => "Gestión Admin. 📜"
                                ],
                                [
                                    "id" => "doa_opcion_presupuestal",
                                    "title" => "Gestión Presup. 💵"
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $mensaje;
    }



    public function struct_messages_list_dia($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Opciones de DIA",
                            "rows" => [
                                [
                                    "id" => "dia_opcion_riego_tecnificado",
                                    "title" => "Riego Tecnificado 🌾"
                                ],
                                [
                                    "id" => "dia_opcion_supervision_agua",
                                    "title" => "Supervisión de Agua 🌊"
                                ],
                                [
                                    "id" => "dia_opcion_maquinaria_agricola",
                                    "title" => "Maquinaria Agrícola 🚜"
                                ],

                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $mensaje;
    }

    public function struct_messages_list_satisfaction_customers($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Calificaciones",
                    "sections" => [
                        [
                            "title" => "Encuesta de Satisfacción",
                            "rows" => [
                                [
                                    "id" => "rating_1",
                                    "title" => "Muy malo 😠",
                                ],
                                [
                                    "id" => "rating_2",
                                    "title" => "Malo 😞",
                                ],
                                [
                                    "id" => "rating_3",
                                    "title" => "Regular 😐",
                                ],
                                [
                                    "id" => "rating_4",
                                    "title" => "Bueno 🙂",
                                ],
                                [
                                    "id" => "rating_5",
                                    "title" => "Muy bueno 😃",
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ];
    
        return $mensaje;
    }
    

    public function struct_messages_list_dtrtycr($optionsMessage, $contactNumber)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            'recipient_type' => 'individual',
            'to' => $contactNumber,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $optionsMessage,
                ],
                'action' => [
                    'button' => 'Opciones',
                    'sections' => [
                        [
                            'title' => 'Opciones Disp.',
                            'rows' => [
                                [
                                    'id' => 'dtrtycr_opcion_titulacion',
                                    'title' => 'Tit. - ALEX 📜',
                                ],
                                [
                                    'id' => 'dtrtycr_opcion_catastro',
                                    'title' => 'Catastro - FRANKEL 🌍',
                                ],
                                [
                                    'id' => 'dtrtycr_opcion_comunidades',
                                    'title' => 'Com. - PETER 🌿',
                                ],
                                [
                                    'id' => 'dtrtycr_opcion_mesa_de_partes',
                                    'title' => 'Mesa Partes 🗄️',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $mensaje;
    }



    //Aperturar conversacion cerrada
    public function reopenAndSaveMessage($conversation, $messageData)
    {
        $conversation->update([
            'status' => 'open',
            'status_bot' => 1
        ]);

        $searchContact = Assignment::where('contact_id', $conversation->contact_id)
            ->where('reason_id', 1)->first();

        ConversationReopening::create([
            'conversation_id' => $conversation->id,
            'reopened_at' => now(),
            'reason' => null
        ]);

        Assignment::create([
            'contact_id' => $conversation->contact_id,
            'conversation_id' => $conversation->id,
            'time' => now(),
            'advisor_id' => $searchContact ? $searchContact->advisor_id : null
        ]);

        $this->saveMessage($conversation, $messageData);
    }

    //Validar conversacion, SI | NO
    public function saveMessage($conversationOrContact, $messageData)
    {
        if ($conversationOrContact instanceof Conversation) {
            $conversation = $conversationOrContact;
            $contact = $conversation->customer;

            $conversation->update([
                'last_activity' => Carbon::createFromTimestamp($messageData['timestamp'])->format('Y-m-d H:i:s'),
            ]);
        } else {
            $contact = $conversationOrContact;
            $conversation = Conversation::create([
                'contact_id' => $contact->id,
                'status' => 'open',
                'status_bot' => 1,
                'start_date' => Carbon::createFromTimestamp($messageData['timestamp'])->format('Y-m-d H:i:s'),
                'last_activity' => Carbon::createFromTimestamp($messageData['timestamp'])->format('Y-m-d H:i:s'),
            ]);

            Assignment::create([
                'contact_id' => $contact->id,
                'conversation_id' => $conversation->id,
                'time' => now()
            ]);
        }

        switch ($messageData['type']) {
            case 'text':
                $menssage = $messageData['text'];
                break;
            case 'reaction':
                $menssage = $messageData['reaction'];
                break;
            case 'image':
                $menssage = $messageData['image'];
                $this->SaveAttachment($messageData['image']['id'], $messageData['id'], 'images');
                break;
            case 'audio':
                $menssage = $messageData['audio'];
                $this->SaveAttachment($messageData['audio']['id'], $messageData['id'], 'audios');
                break;
            case 'video':
                $menssage = $messageData['video'];
                $this->SaveAttachment($messageData['video']['id'], $messageData['id'], 'videos');
                break;
            case 'document':
                $menssage = $messageData['document'];
                $this->SaveAttachment($messageData['document'], $messageData['id'], 'documents');
                break;
            default:
                $menssage = 'Tipo de mensaje no encontrado';
                break;
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'api_id' => $messageData['id'],
            'context' => isset($messageData['context']) ? json_encode($messageData['context']) : null,
            'content' => json_encode($menssage),
            'referral' => isset($messageData['referral']) ? json_encode($messageData['referral']) : null,
            'type' => $messageData['type'],
            'date_of_issue' => Carbon::createFromTimestamp($messageData['timestamp'])->format('Y-m-d H:i:s'),
            'status' => 'delivered',
            'emisor_id' => $contact->id,
            'emisor' => 'Customer'
        ]);

        return $conversation;
    }

    public function struct_messages_list_opyea($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Seleccione una opción",
                            "rows" => [
                                [
                                    "id" => "opea_opcion_estrategia",
                                    "title" => "ESTRATEGIA 📊",
                                ],
                                [
                                    "id" => "opea_opcion_monitoreo",
                                    "title" => "MONITOREO 📈",
                                ],
                                [
                                    "id" => "opea_opcion_inversiones",
                                    "title" => "INVERSIONES 💼",
                                ],
                                [
                                    "id" => "opea_opcion_informatica",
                                    "title" => "INFORMÁTICA 💻",
                                ],
                                [
                                    "id" => "opea_opcion_estadistica",
                                    "title" => "ESTADÍSTICA 📉",
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ];

        return $mensaje;
    }




    public function saveMessageChatBootInit($conversationOrContact, $messageData)
    {
        $apiId = $messageData['messages'][0]['id'] ?? null;


        if ($conversationOrContact instanceof Conversation) {
            $conversation = $conversationOrContact;
            $contact = $conversation->customer;

            $conversation->update([
                'last_activity' => now()->format('Y-m-d H:i:s'),
            ]);
        } else {
            $contact = $conversationOrContact;
            $conversation = Conversation::create([
                'contact_id' => $contact->id,
                'status' => 'open',
                'status_bot' => 1,
                'start_date' => now()->format('Y-m-d H:i:s'),
                'last_activity' => now()->format('Y-m-d H:i:s'),
            ]);

            Assignment::create([
                'contact_id' => $contact->id,
                'conversation_id' => $conversation->id,
                'time' => now()
            ]);
        }

        $messageContent = $messageData['messageContent'];

        switch ($messageContent['type']) {
            case 'interactive':
                $menssage = is_array($messageContent['interactive']) ? $messageContent['interactive'] : ['body' => $messageContent['interactive']];
                break;
            case 'text':
                $menssage = $messageContent['text'];
                break;
            case 'reaction':
                $menssage = $messageContent['reaction'];
                break;
            case 'image':
                $menssage = $messageContent['image'];
                $this->SaveAttachment($messageData['image']['id'], $messageData['id'], 'images');
                break;
            case 'audio':
                $menssage = $messageContent['audio'];
                $this->SaveAttachment($messageData['audio']['id'], $messageData['id'], 'audios');
                break;
            case 'video':
                $menssage = $messageContent['video'];
                $this->SaveAttachment($messageData['video']['id'], $messageData['id'], 'videos');
                break;
            case 'document':
                $menssage = $messageContent['document'];
                $this->SaveAttachment($messageData['document'], $messageData['id'], 'documents');
                break;
            default:
                $menssage = 'Tipo de mensaje no encontrado';
                break;
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'api_id' => $apiId,
            'context' => isset($messageContent['context']) ? json_encode($messageContent['context']) : null,
            'content' => json_encode($menssage),
            'referral' => isset($messageContent['referral']) ? json_encode($messageContent['referral']) : null,
            'type' => $messageContent['type'],
            'date_of_issue' => now()->format('Y-m-d H:i:s'),
            'status' => 'delivered',
            'emisor_id' => $contact->id,
            'emisor' => 'Advisor'
        ]);

        return $conversation;
    }

    public function envia($mensaje)
    {
        //Parametros
        $token = Conexion::first();
        $idPhone = $token->phone_id;
        $url = "https://graph.facebook.com/v19.0/$idPhone/messages";
        $header = [
            "Authorization: Bearer " . $token->token,
            "Content-Type: application/json",
            "message: EVENT_RECEIVED"
        ];

        //Envio de mensajeriia
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($mensaje)); // Convertir el array en JSON
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

        if ($response === false) {
            echo "Error en la solicitud cURL: " . curl_error($curl);
        } else {
            $response_array = json_decode($response, true);
        }

        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        // Return request
        if (isset($error)) {
            return ['error' => $error];
        } else {
            return $response_array;
        }
    }


    public function verifyWebhook(Request $request)
    {
        try {
            $verifyToken = 'meatyhamhock';
            $query = $request->query();

            $mode = $query['hub_mode'];
            $token = $query['hub_verify_token'];
            $challenge = $query['hub_challenge'];

            if ($mode && $token) {
                if ($mode == "subscribe" && $token == $verifyToken) {
                    return response($challenge, 200)->header('Content-Type', 'text/plain');
                }
            }
            throw new Exception('Invalid request');
        } catch (Exception $e) {
            // Registrar el error en el log
            Log::error('Webhook verification error: ' . $request);

            return response()->json([
                'success' => false,
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function respuesta($body)
    {
        if (strpos($body, 'hola') !== false) {
            $notificacion = 'Hola este es un mensaje sin usar templates';
        } else {
            $notificacion = 'Hola este es un mensaje sin usar templates para cualquier respuesta';
        }

        return $notificacion;
    }

    //Tipos de mensajes mas usados
    //Envio de mensaje simple
    public function struct_Message($notificacion, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            'recipient_type' => "individual",
            "to" => $num,
            "type" => "text",
            "text" => [
                "body" => $notificacion,
            ]
        ];

        return $mensaje;
    }

    public function messageWelcome($conversation, $idMessage, $contentMessage)
    {
        Message::create([
            'conversation_id' => $conversation->id,
            'api_id' => $idMessage['messages'][0]['id'],
            'content' => json_encode($contentMessage['text']),
            'type' => $contentMessage['type'],
            'date_of_issue' => now(),
            'status' => 'delivered',
            'emisor_id' => 1,
            'emisor' => 'Advisor'
        ]);
    }

    //Envio de Mensaje con botones
    //Envio maximo de 3 botones
    public function struct_message_buttons()
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => "+51980825604",
            "type" => "interactive",
            "interactive" => [
                "type" => "button",
                "body" => [
                    "text" => "Indique el nivel de satisfaccion de la comunicacion con el asesor 👇"
                ],
                "action" => [
                    "buttons" => [
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => "1",
                                "title" => "1"
                            ]
                        ],
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => "2",
                                "title" => "2"
                            ]
                        ],
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => "3",
                                "title" => "3"
                            ]
                        ],
                    ]
                ]
            ]
        ];
        return $mensaje;
    }

    public function struct_messages_list($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Opciones de Oficinas",
                            "rows" => [
                                [
                                    "id" => "1",
                                    "title" => "DIR. REGIONAL 🏢",
                                ],
                                [
                                    "id" => "2",
                                    "title" => "TRANSPARENCIA 📊",
                                ],
                                [
                                    "id" => "3",
                                    "title" => "OCI 🛡️",
                                ],
                                [
                                    "id" => "4",
                                    "title" => "OPyEA 📈",
                                ],
                                [
                                    "id" => "5",
                                    "title" => "ASES. JURÍDICA ⚖️",
                                ],
                                [
                                    "id" => "6",
                                    "title" => "DOA 🚜",
                                ],
                                [
                                    "id" => "7",
                                    "title" => "DTRTYCR 🗺️",
                                ],
                                [
                                    "id" => "8",
                                    "title" => "DIA 🏗️",
                                ],
                                [
                                    "id" => "9",
                                    "title" => "DDCA 🌾",
                                ],
                            ]
                        ],
                    ]
                ]
            ]
        ];

        return $mensaje;
    }

    public function struct_messages_list_webhook($text, $num)
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $num,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $text
                ],
                "action" => [
                    "button" => "Opciones",
                    "sections" => [
                        [
                            "title" => "Opciones de Oficinas",
                            "rows" => [
                                [
                                    "id" => "office_catastro",
                                    "title" => "Oficina de Catastro 🗺️",
                                ],
                                [
                                    "id" => "office_titulacion",
                                    "title" => "Oficina de Titulación 📜",
                                ],
                                [
                                    "id" => "office_saneamiento",
                                    "title" => "Oficina de Saneamiento 🌍",
                                ],

                            ]
                        ],
                    ]
                ]
            ]
        ];

        return $mensaje;
    }


    public function sendMessageFiles($contentMessage, $textCaption, $phoneNumber)
    {
        // Estructura del mensaje para enviar un archivo (imagen) en WhatsApp
        $mensaje = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phoneNumber,
            "type" => "image",
            "image" => [
                "caption" => $textCaption,
                "link" => $contentMessage
            ]
        ];
    
        // Retornar el mensaje estructurado
        return $mensaje;
    }
    


    //Envio de documentos pdf
    public function struct_message_doc()
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            'recipient_type' => "individual",
            "to" => "+51980825604",
            "type" => "document",
            "document" => [
                "link" => "https://www.turnerlibros.com/wp-content/uploads/2021/02/ejemplo.pdf",
                "caption" => "Archivo publico"
            ]
        ];
        return $mensaje;
    }

    //Envio de locaciones
    public function struct_message_location()
    {
        $mensaje = [
            "messaging_product" => "whatsapp",
            "to" => "+51980825604",
            "type" => "location",
            "location" => [
                "longitude" => "-12.089618634107495",
                "latitude" => "-77.05310979379249",
                "name" => "Real Plaza Salaverry",
                "address" => "Salaverry"
            ]
        ];
        return $mensaje;
    }
}
