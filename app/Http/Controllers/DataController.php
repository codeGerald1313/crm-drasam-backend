<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Assignment;
use App\Models\Conversation;
use App\Models\DateRemenber;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ClousureReason;
use App\Models\QuicklyAnswers;
use App\Events\ReminderDeleted;
use App\Events\ListenNewMessage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\AsignAdvisorUpdated;
use App\Events\ListenRemenberEvent;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CustomCollection;
use App\Http\Resources\MessageCollection;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\NewConversationResource;

class DataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function records()
    {
        $advisors = User::where('admin', 0)->where('inline',1)->get();
        $clousureReasons = ClousureReason::get();

        return response()->json([
            'advisors' => $advisors,
            'clousureReasons' => $clousureReasons,
        ], Response::HTTP_OK);
    }

    // 1 - Sin Asignar, 2 - Asignado, 3 - Conversación Cerrada
    public function conversations()
    {
        $conversations = Conversation::with(['customer', 'messages', 'assignment', 'dateremenber'])
            ->leftJoin('assignments', 'conversations.id', '=', 'assignments.conversation_id')
            ->select(DB::raw('assignments.id as id_asignacion'), DB::raw('assignments.interes_en as interes_en'), 'conversations.*', DB::raw('
                        CASE
                            WHEN assignments.advisor_id IS NULL THEN "1"
                            WHEN assignments.state IS NULL AND assignments.advisor_id IS NOT NULL THEN "2"
                            WHEN assignments.state IS NOT NULL AND assignments.advisor_id IS NOT NULL THEN "3"
                            ELSE "2"
                        END AS estado_asignacion
                '))
            ->get();

        $filteredConversations = $conversations->filter(function ($conversation) {
            return $conversation->estado_asignacion === "3";
        });

        $uniqueConversations = $filteredConversations->unique('id');

        $finalConversations = collect($uniqueConversations->merge($conversations->where('estado_asignacion', '!=', '3')));

        return response()->json([
            'conversations' => new ConversationCollection($finalConversations)
        ]);
    }

    public function messages($id)
    {
        $conversations = Message::where('conversation_id', $id)->get();
        
        return response()->json([
            'messages' => new MessageCollection($conversations)
        ]);
    }

    // ASIGNACION DESDE EL PANEL DE CONVERSACION
    public function updateAssignment(int $conversationId, int $advisorId)
    {
        $token = JWTAuth::getToken();

        if ($token) {
            $payload = JWTAuth::decode($token);
            $userId = $payload['sub'];
        }
        
        try {
            $assignments = Assignment::where('conversation_id', $conversationId)
            ->whereNull('advisor_id')
            ->whereNull('state')
            ->orderBy('updated_at', 'desc')
            ->get();

            if ($assignments->isNotEmpty()) {
                $assignment = $assignments->first();
                $assignment->advisor_id = $advisorId;
                $assignment->save(); 

                $assignment->conversation->update([
                    'status_bot' => 0
                ]);
    
                $conversation = Conversation::find($assignment->conversation_id);
                
                event(new AsignAdvisorUpdated([
                    'asing' => new NewConversationResource($conversation),
                    'statusConversation' => '1',
                    'assignedToUserId' => $advisorId,
                    'assignedByUserId' => $userId
                ]));

                return response()->json([
                    'message' => 'Asignación de asesor exitosa',
                    'data' => $assignment
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'No se encontró una asignación adecuada'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al asignar asesor',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ASIGNACION DESDE EL CHAT
    public function updateAssignmentChat(int $asignId, int $advisorId)
    {
        try {
            $assignment = Assignment::find($asignId);
            $changeTrue = $assignment->is_transferred = 1;

            $assignment->update([
                'advisor_id' => $advisorId,
                'is_transferred' => $changeTrue
            ]);

            $conversation = Conversation::find($assignment->conversation_id);

           event(new AsignAdvisorUpdated([
                'asing' => new NewConversationResource($conversation),
                'statusConversation' => '0',
            ]));

            return response()->json([
                'message' => 'Operación exitosa',
                'data' => $assignment
            ], Response::HTTP_OK);
            
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al al realizar la operación',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Agianacion desde registro de contactos event
    public function updateregisterContactAsign(int $asignId, int $advisorId)
    {
        $token = JWTAuth::getToken();

        if ($token) {
            $payload = JWTAuth::decode($token);
            $userId = $payload['sub'];
        }
        
        try {
            $assignment = Assignment::find($asignId);

            $assignment->update([
                'advisor_id' => $advisorId
            ]);

            $conversation = Conversation::find($assignment->conversation_id);

            event(new AsignAdvisorUpdated([
                'asing' => new NewConversationResource($conversation),
                'statusConversation' => '1',
                'assignedToUserId' => $advisorId,
                'assignedByUserId' => $userId
            ]));

            return response()->json([
                'message' => 'Asignación de asesor exitosa',
                'data' => $assignment
            ], Response::HTTP_OK);
            
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al asignar asesor',
                'error' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Registrar contato y asignaciones
    public function registerContactAsign(Request $request)
    {
        $requestC = $request->all();
        
        // 1- Frio, 2- Tibio, 3- Caliente
        $contact = Contact::where('num_phone', $requestC['num_phone'])->first();
        
        if (!$contact) {
            $contact = Contact::create([
                'name' => $requestC['name'],
                'document' => "00000000",
                'email' => 'mail@mail.com',
                'num_phone' => $requestC['num_phone'],
                'status' => 1,
                'student' => 0
            ]);
        }else{
            $contact->update([
                'name' => $requestC['name'],
                'email' => $requestC['email'] ?? null
            ]);
        }

        $contactoId = $contact->id;

        $conversacionExistente = Conversation::where('contact_id', $contactoId)->first();

        if (!$conversacionExistente) {
            $conversacionExistente = Conversation::create([
                'contact_id' => $contactoId,
                'status' => 'open',
                'status_bot' => 0,
                'start_date' => now(),
                'last_activity' => now()
            ]);
        }else{
            $conversacionExistente->update([
                'status' => 'open',
                'status_bot' => 0,
                'last_activity' => now()
            ]);
        }

       
        
        try {
            $asignacionExistente = Assignment::where('conversation_id', $conversacionExistente->id)
            ->where(function ($query) {
                $query->whereNull('state')
                    ->orWhereNull('advisor_id');

                $query->whereNotNull('advisor_id')
                    ->whereNull('state');
            })
            ->first();

            if ($asignacionExistente) {
                if ($asignacionExistente->state === null && $asignacionExistente->advisor_id === null) {
                    $asignacionExistente->update([
                        'advisor_id' => $requestC['advisor_id'],
                        'interes_en' => $requestC['interes_en']
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Registro de asignación exitoso',
                        'data' => $asignacionExistente
                    ], Response::HTTP_CREATED);

                } else {
                    
                    if(!!$requestC['check']){
                        $asignacionExistente->update(['interes_en' => $requestC['interes_en']]);

                        return response()->json([
                            'status' => 'success',
                            'message' => 'Registro de asignación exitoso',
                            'data' => $asignacionExistente
                        ], Response::HTTP_OK);
                    }
                    else{
                       
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Registro actualizado',
                            'data' => $asignacionExistente
                        ], Response::HTTP_OK);
                    }
                }
            } else {

                $conversationss = Conversation::find($conversacionExistente->id);

                $asignacion = Assignment::create([
                    'contact_id' => $contactoId,
                    'conversation_id' => $conversationss->id,
                    'advisor_id' => $requestC['advisor_id'],
                    'state' => null,
                    'interes_en' => $requestC['interes_en'] ?? null
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Registro de asignación exitoso',
                    'data' => $asignacion
                ], Response::HTTP_CREATED);

               
            }

        } catch (\Exception $e) {
            Log::info('error', [$e->getMessage()]);
            return response()->json(['message' => 'Error en el registro de asignación'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function closeReasonConversation(int $asignId, int $selectedReasonInt)
    {
        $assignment = Assignment::find($asignId);
        $assignment->update([
            'state' => 0,
            'reason_id' => $selectedReasonInt,
            'time' => now()
        ]);

        $conversation = Conversation::where('id', $assignment->conversation_id)->first();

        if($selectedReasonInt == 1){
            $conversation->customer->update([
                'student' => 1
            ]);
        }

        $conversation->update([
            'status' => 'close',
            'status_bot' => 1
        ]);

        return response()->json([
            'message' => 'La conversación ha sido cerrada con Exito',
            'data' => $conversation
        ], Response::HTTP_OK);

    }

    public function getCustomers(int $id)
    {

        $user = User::find($id);

        if($user->admin == 1){
            $assignment = Assignment::with('customer')
            ->whereNull('state')
            ->orderBy('updated_at', 'desc')
            ->get();
        }else{
            $assignment = Assignment::with('customer')->where('advisor_id', $id)
            ->whereNull('state')
            ->orderBy('updated_at', 'desc')
            ->get();
        }

        return response()->json([
            'message' => 'Información recuperada con exito',
            'data' => new CustomCollection($assignment)
        ], Response::HTTP_OK);
    }

    public function addSaveRemenber(Request $request)
    {
        try {
            $remenber = DateRemenber::create([
                'date_to_remenber' => $request->datechatRecorder,
                'time_to_remenber' => $request->timechatRecorder,
                'conversation_id' => $request->conversation_id,
                'status' => 1
            ]);

            $assignment = Assignment::where('conversation_id', $request->conversation_id)->first();

            if ($assignment) {
                $advisor = $assignment->advisor;
                $contact = $assignment->customer;

                if ($advisor && $contact) {
                    event(new ListenRemenberEvent($remenber, $advisor, $contact, auth()->user()->id));
                }
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Registro exitoso',
                'data' => $remenber,
                'conversacionExistente' => new NewConversationResource($remenber->conversation)
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('mostrar error', [$th->getMessage()]);
        }
    }

    public function addUpdateRemenber(int $id)
    {
        try {
            // Buscar el recordatorio por su ID
            $remenber = DateRemenber::find($id);

            $conversation = Conversation::find($remenber->conversation_id);

            if (!$remenber) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontró el recordatorio con ID ' . $id
                ], Response::HTTP_NOT_FOUND);
            }

            $remenber->delete();

            event(new ReminderDeleted($id, $conversation));

            return response()->json([
                'status' => 'success',
                'message' => 'Recordatorio eliminado con éxito',
                'conversacionExistente' => new NewConversationResource($conversation)
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar el recordatorio', [$th->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Ha ocurrido un error al eliminar el recordatorio'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUnreadCustomerMessages($conversationId)
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            $unreadCustomerMessages = $conversation->messages()
                ->where('emisor', 'Customer')
                ->where('status', 'delivered')
                ->get();

            foreach($unreadCustomerMessages as $message){
                $message->update(['status' => 'read']);
            }

            event(new ListenNewMessage([
                'conversation' => $conversation,
                'userId' => auth()->user()->id
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Mensajes leidos con exito'
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error',
                'error' => $th->getMessage()
            ], Response::HTTP_OK);
        }
    }

    public function getRequestFast(int $userId)
    {
        $user = User::find($userId);

        if($user->admin == 1){
            $answers = QuicklyAnswers::orderBy('id')->get();
        }else{
            $answers = QuicklyAnswers::where(function ($query) use ($userId) {
                $query->where('type', 'answers')
                      ->where('user_id', $userId);
            })
            ->orWhere(function ($query) {
                $query->where('type', 'template');
            })
            ->orderBy('id')->get();
            
        }

        return response()->json([
            'status' => 'success',
            'data' => $answers
        ], Response::HTTP_OK);
    }

    public function getTemplate()
    {
        try {
            
            $template =  QuicklyAnswers::where('type', 'template')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Template recuperada con exito',
                'template' => $template
            ], Response::HTTP_OK);

            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error',
                'error' => $th->getMessage()
            ], Response::HTTP_OK);
        }
    }

    public function asignTagCategory(int $tagId, int $assigId)
    {
        try {
            $assignment = Assignment::find($assigId);

            $assignment->update([
                'tag_id' => $tagId
            ]);

            $conversation = Conversation::find($assignment->conversation_id);

           event(new AsignAdvisorUpdated([
                'asing' => new NewConversationResource($conversation),
                'statusConversation' => '2',
            ]));
            
            return response()->json([
                'message' => 'Operación exitosa',
                'data' => $assignment
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            
            return response()->json(['message' => 'Ocurrio un error al realizar esta operación'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}