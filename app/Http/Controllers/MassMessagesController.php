<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Diffusion;
use Illuminate\Support\Str;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WspController;
use App\Http\Resources\MessageCollection;
use App\Http\Resources\NewMessageCollection;
use App\Http\Resources\MassMessageCollection;

class MassMessagesController extends Controller
{

    public function getMessages(Request $request)
    {
        $searchTerm = $request->input('search');
        $user = User::find($request->input('user_id'));

        $massMessages = Diffusion::with(['user'])->where('status', 1)
                        ->where(function ($query) use ($searchTerm) {
                            $query->where('campaign_name', 'like', "%{$searchTerm}%")
                                ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                                    $terms = explode(' ', $searchTerm);
                                    foreach ($terms as $term) {
                                        $userQuery->where(function ($nameQuery) use ($term) {
                                            $nameQuery->where('name', 'like', "%{$term}%")
                                                ->orWhere('last_name', 'like', "%{$term}%");
                                        });
                                    }
                                });
                        })
                        ->where(function ($subQuery) use ($user) {
                            if($user->admin!=1) $subQuery->where('user_id', $user->id);
                        })
                        ->paginate(8);

        return response()->json([
            'success' => true,
            'data' => new MassMessageCollection($massMessages->items()),
            'pagination' => [
                'current_page' => $massMessages->currentPage(),
                'per_page' => $massMessages->perPage(),
                'total' => $massMessages->total(),
            ],
        ], Response::HTTP_OK);
        
    }

    public function detailMessages(int $id)
    {
        $detilDifussion = Diffusion::with(['messages'])
        ->where('id', $id)
        ->first();

        $messages = $detilDifussion->messages()->paginate(8);

        return response()->json([
            'success' => true,
            'data' => new NewMessageCollection($messages->items()),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
            'messages' => 'Datos recuperados con exito'
        ], Response::HTTP_OK);
    }
    
    public function sendMassMessages(Request $request)
    {
        try {
            $bodyContent = json_decode($request->getContent(), true);
            $messageData = $bodyContent['message'];
            $CampaignName = $messageData['campaign_name'];
            $contentType = $messageData['content_type'];

            if($messageData['content_type'] == 'template_image' || $messageData['content_type'] == 'template_video')
            {
                $contentReference = $messageData['content_reference'][0]['title'];
                $linkMedia = $messageData['content_reference'][0]['link'];
            } else if($messageData['content_type'] == 'template')
            {
                $contentReference = $messageData['content_reference'][0]['title'];
                $linkMedia = '';
            } else {
                $contentReference = $messageData['content_reference'];
                $linkMedia = '';
            }
            
            $sendToAll = $bodyContent['send_to_all'];
            $allRecipientPhoneNumbers = Contact::pluck('num_phone')->toArray();

            $diffusion = new Diffusion();
            $diffusion->campaign_name = $CampaignName;
            $diffusion->content_type = $contentType;
            $diffusion->content_reference = $contentReference;
            $diffusion->user_id = Auth::user()->id;
            $diffusion->date = now();
            $diffusion->save();

            if ($sendToAll) {
                $this->sendMessagesToRecipients($contentType, $contentReference, $allRecipientPhoneNumbers, $diffusion, $linkMedia);
            } else {
                $recipientContactIds = $bodyContent['recipient_contact_ids'];
                $recipientPhoneNumbers = Contact::whereIn('id', $recipientContactIds)->pluck('num_phone')->toArray();
                $this->sendMessagesToRecipients($contentType, $contentReference, $recipientPhoneNumbers, $diffusion, $linkMedia);
            }

            return response()->json([
                'success' => true,
                'data' => 'Envio de mensajes con exito',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendMessagesToRecipients($contentType, $contentReference, $phoneNumbers, $diffusion, $linkMedia)
    {
        $wspController = new WspController();
        
        foreach ($phoneNumbers as $phoneNumber) {

            $responseMessage = $this->struct_Messagess($contentType, $contentReference, $phoneNumber, $linkMedia);
            
            $body = $wspController->envia($responseMessage);
            
            $this->createMessage($responseMessage, $body, $diffusion);
            
            sleep(3);
        }
    }

    //Registrar mensaje
    private function createMessage($response, $body, $diffusion)
    {
        
        try{
            DB::beginTransaction();

            $user = auth()->user();
            $contact = Contact::where('num_phone', $response['to'])->first();
            $lastConversation = Conversation::where('contact_id', $contact->id)
                ->orderBy('created_at', 'desc')
                ->first();

            switch ($response['type']) {
                case 'text':
                    $menssage = $response['text'];
                    break;
                case 'image':
                    $menssage = $response['image'];
                    break;
                case 'audio':
                    $menssage = $response['audio'];
                    break;
                case 'video':
                    $menssage = $response['video'];
                    break;
                case 'document':
                    $menssage = $response['document'];
                    break;
                case 'template':
                    $menssage = $response['template'];
                    break;
                default:
                    $menssage = 'Tipo de mensaje no encontrado';
                    break;
            }

            Message::create([
                'conversation_id' => $lastConversation->id,
                'api_id' => $body['messages'][0]['id'],
                'content' => json_encode($menssage),
                'type' => $response['type'],
                'date_of_issue' => null,
                'status' => null,
                'emisor_id' => $user->id,
                'emisor' => 'Advisor',
                'mass_message_id' => $diffusion->id
            ]);

            DB::commit();

        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Error durante la creación del mensaje:', ['error' => $th->getMessage()]);
        }
    }


    private function struct_Messagess($contentType, $contentReference, $phoneNumber, $linkMedia)
    {
        $contact = Contact::where('num_phone',$phoneNumber)->first();

        if (Str::contains($contentReference, '{nombre}')) {
            $textReference = str_replace('{nombre}', $contact->name, $contentReference);
        }else{
            $textReference = $contentReference;
        }
        
        $message = [
            "messaging_product" => "whatsapp",
            "to" => $phoneNumber,
            "type" => ($contentType === 'template_video' || $contentType === 'template_image') ? 'template' : $contentType,
        ];

        switch ($contentType) {
            case 'text':
                $message['text'] = ["body" => $textReference];
                break;
            case 'template':
                $message['template'] =  [
                    "name" => $textReference,
                    "language" => [
                        "code" => "es",
                    ]
                ];
                break;
            case 'template_image':
                $message['template'] =  [
                    "name" => $textReference,
                    "language" => [
                        "code" => "es",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        "link" => $linkMedia
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case 'template_video':
                $message['template'] =  [
                    "name" => $textReference,
                    "language" => [
                        "code" => "es",
                    ],
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "video",
                                    "video" => [
                                        "link" => $linkMedia
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                break;
            case 'document':
                $message['document'] = [
                    "link" => $textReference,
                    "caption" => "Esto sería la descripción de cada archivo",
                    "filename" => "Ejemplo"
                ];
                break;
            case 'image':
                $message['image'] = ["link" => $textReference, "caption" => "Image Caption"];
                break;
            case 'video':
                $message['video'] = ["id" => $textReference];
                break;
            case 'audio':
                $message['audio'] = ["id" => $textReference];
                break;
            default:
                return [];
        }

        return $message;
    }
    
    // Filtrar clientes dentro del rango de conversacion de 24 horas
    public function filterCustomer(Request $request)
    {
        $data = $request->all();
        
        $user = User::find($data['user_id']);

        $filterCustomerConversation = Conversation::with('assignment');

        if ($data['type_date'] == 1) {
            $filterCustomerConversation->where('status', 'open')
            ->whereHas('assignment', function ($query) use ($data, $user) {
                $query->where('tag_id', $data['tag'])
                    ->where('time', '>=', $data['startDate'])
                    ->where(function ($subQuery) use ($user) {
                        if($user->admin!=1) $subQuery->where('advisor_id', $user->id);
                    })
                    ->whereNotNull('advisor_id')
                    ->orWhereNull('contact_id');
            });
        } else {
            $filterCustomerConversation->where('status', 'open')
                ->whereHas('assignment', function ($query) use ($data, $user) {
                    $query->where('tag_id', $data['tag'])
                        ->where('time', '>=', $data['startDate']['_value'])
                        ->where(function ($subQuery) use ($user) {
                            if($user->admin!=1) $subQuery->where('advisor_id', $user->id);
                        })
                        ->whereNotNull('advisor_id')
                        ->orWhereNull('contact_id');
                })->where('status', 'open')
                ->where('last_activity', '<=', $data['currentDate']['_value']);
        }

        $filteredData = $filterCustomerConversation->get();

        $filteredData = $filteredData->map(function ($item) {
            return [
                'customer_id' => $item->contact_id
            ];
        });

        return response()->json([
            'message' => 'Información recuperada con éxito',
            'data' => $filteredData
        ], Response::HTTP_OK);
    }


}
