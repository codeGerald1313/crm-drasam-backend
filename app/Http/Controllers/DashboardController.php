<?php

namespace App\Http\Controllers;

use App\Events\ConversationCreated;
use App\Http\Resources\ConversationCollectionCustom;
use App\Http\Resources\WaitTimeCollection;
use App\Models\Assignment;
use App\Models\ClousureReason;
use App\Models\Contact;
use App\Models\ContactWaitTime;
use App\Models\Conversation;
use App\Models\DateRemenber;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{


    public function getMessagesData(Request $request, $periodo = null)
    {
        switch ($periodo) {
            case 'ultimos_7_dias':
                return $this->getLast7DaysData();
            case 'este_mes':
                return $this->getThisMonthData();
            case 'personalizado':
                return $this->getCustomData($request);
            default:
                return response()->json(['error' => 'Período no válido'], 400);
        }
    }

    private function getLast7DaysData()
    {
        $endDate = Carbon::now('America/Lima');
        $startDate = $endDate->copy()->subDays(6);

        $data = $this->generateDataForPeriod($startDate, $endDate);
        $totalMessages = $this->calculateTotalMessages($data);

        return $this->formatData($totalMessages, $data);
    }

    private function getThisMonthData()
    {
        $startDate = Carbon::now('America/Lima')->startOfMonth();
        $endDate = Carbon::now('America/Lima')->endOfMonth();

        $data = $this->generateDataForPeriod($startDate, $endDate);

        return $this->formatMonthlyData($data);
    }

    private function getCustomData(Request $request)
    {
        $selectedMonth = $request->input('selected_month');
        $selectedYear = $request->input('selected_year');


        $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $data = $this->generateDataForPeriod($startDate, $endDate);

        return $this->formatMonthlyData($data);
    }


    private function formatMonthlyData($data)
    {
        $cantidades = [
            'Lun' => 0,
            'Mar' => 0,
            'Mie' => 0,
            'Jue' => 0,
            'Vie' => 0,
            'Sab' => 0,
            'Dom' => 0,
        ];

        foreach ($data as $item) {
            $diaSemana = $item['fecha'];
            $cantidades[$diaSemana] += $item['cantidad'];
        }

        $formattedData = [
            'fechas' => array_keys($cantidades),
            'cantidades' => array_values($cantidades),
        ];

        return response()->json([
            'total_mensajes' => array_sum($formattedData['cantidades']),
            'data' => $formattedData,
        ]);
    }

    private function generateDataForPeriod($startDate, $endDate)
    {
        $diasSemana = [
            'Monday' => 'Lun',
            'Tuesday' => 'Mar',
            'Wednesday' => 'Mie',
            'Thursday' => 'Jue',
            'Friday' => 'Vie',
            'Saturday' => 'Sab',
            'Sunday' => 'Dom',
        ];

        $data = [];

        while ($startDate <= $endDate) {
            $formattedDay = $diasSemana[$startDate->format('l')];
            $count = Message::whereDate('created_at', $startDate->toDateString())->count();

            $data[] = [
                'fecha' => $formattedDay,
                'cantidad' => $count,
            ];

            $startDate->addDay();
        }

        return $data;
    }

    private function calculateTotalMessages($data)
    {
        return array_sum(array_column($data, 'cantidad'));
    }

    private function formatData($totalMessages, $data)
    {
        return response()->json([
            'total_mensajes' => $totalMessages,
            'data' => $data,
        ]);
    }

    public function getActyvitiesInLive()
    {
        $today = now(); 
        $assignmentDetails = DB::table('assignments')
            ->join('users as advisor', 'assignments.advisor_id', '=', 'advisor.id')
            ->join('conversations', 'assignments.conversation_id', '=', 'conversations.id')
            ->join('contacts', 'conversations.contact_id', '=', 'contacts.id')
            ->select(
                'assignments.id',
                'advisor.name as advisor_name',
                'contacts.name as contact_name',
                'conversations.last_activity'
            )
            ->addSelect(DB::raw("(SELECT JSON_OBJECT( 'content', content, 'type', type) FROM messages WHERE conversation_id = assignments.id ORDER BY created_at DESC LIMIT 1) as messages"))
            ->whereDate('conversations.last_activity', $today) 
            ->orderBy('conversations.last_activity', 'desc')
            ->get();
    
        return new ConversationCollectionCustom($assignmentDetails);
    }
    

    public function getWaitTimes()
    {
        $conversaciones = Assignment::pluck('conversation_id');
        $tiemposDeEspera = [];

        foreach ($conversaciones as $conversationId) {
            $conversation = Conversation::find($conversationId);

            if ($conversation) {
                $assignment = Assignment::where('conversation_id', $conversationId)->first();
                $contact = $assignment->customer;
                $advisor = $assignment->advisor;
                $contactName = $contact->name;
                $advisorName = $advisor->name;

                $messages = Message::where('conversation_id', $conversationId)
                    ->whereIn('emisor', ['Customer', 'Advisor'])
                    ->get();

                $advisorWaitTimes = [];

                $totalWaitTime = 0;
                $lastEmisor = null;

                foreach ($messages as $message) {
                    if (($message->emisor === 'Customer' && $message->status === 'read') ||
                        ($message->emisor === 'Advisor' && $message->status === 'delivered')
                    ) {
                        $advisorId = $message->emisor === 'Advisor' ? $advisor->id : null;

                        if ($advisorId !== null) {
                            if (!isset($advisorWaitTimes[$advisorId])) {
                                $advisorWaitTimes[$advisorId] = 0;
                            }
                            $advisorWaitTimes[$advisorId] += $message->created_at->diffInSeconds($messages[0]->created_at);
                        }
                    }
                }


                foreach ($advisorWaitTimes as $advisorId => $waitTime) {
                    if ($waitTime > 0) {
                        $waitTimeString = ltrim($waitTime, '0');

                        $existingRecord = ContactWaitTime::where('contact_id', $contact->id)
                            ->where('advisor_id', $advisor->id)
                            ->first();
                        if ($existingRecord) {
                            // Si el registro ya existe, actualiza el tiempo de espera
                            // existingRecord->update(['wait_time' => $waitTime]);
                        } else {
                            // Si no existe, crea un nuevo registro
                            if ($advisor->id !== null) { 
                                ContactWaitTime::create([
                                    'contact_id' => $contact->id,
                                    'advisor_id' => $advisor->id,
                                    'wait_time' => $waitTime
                                ]);
                            }
                        }

                        $tiemposDeEspera[] = [
                            'Contacto' => $contactName,
                            'Asesor' => $advisorName,
                            'Espera' => $waitTimeString . ' s'
                        ];
                    }
                }
            }
        }
        return $tiemposDeEspera;
    }



    public function getTable()
    {
        $today = Carbon::today();

        $asesores = User::where('last_login', '>=', $today)
            ->where('admin', 0)
            ->get();


        $asesoresResponse = [];

        foreach ($asesores as $asesor) {
            $contactosCerradosVenta = Assignment::where('advisor_id', $asesor->id)
                ->whereNotNull('state')
                ->join('clousure_reasons', 'assignments.reason_id', '=', 'clousure_reasons.id')
                ->where('clousure_reasons.name', 'Venta')
                ->count();

            $conversaciones = Assignment::where('advisor_id', $asesor->id)
                ->pluck('conversation_id');

            $ultimaActividad = Conversation::whereIn('id', $conversaciones)
                ->orderBy('last_activity', 'desc')
                ->value('last_activity') ?: "Inactivo";

            $totalContactos = Assignment::where('advisor_id', $asesor->id)->count();
            Log::info("Contactos $totalContactos");

            $tiemposDeEspera = ContactWaitTime::where('advisor_id', $asesor->id)->get();

            $totalWaitTime = 0;

            foreach ($tiemposDeEspera as $tiempo) {
                $totalWaitTime += $tiempo->wait_time;
            }

            $averageWaitTime = ($totalContactos > 0) ? $totalWaitTime / $totalContactos : 0;
            $averageWaitTime = number_format($averageWaitTime, 1);


            Log::info("Calculado $averageWaitTime");

            $assignmentId = Assignment::where('advisor_id', $asesor->id)
                ->pluck('id')
                ->first();

            $asesorResponse = [
                'id_asignacion' => $assignmentId,
                'advisorName' => $asesor->name,
                'last_activity' => $ultimaActividad,
                'total_contactos' => $totalContactos,
                'contactos_cerrados' => $contactosCerradosVenta,
                'tiempo_espera_promedio' => $averageWaitTime . ' s',
            ];

            $asesoresResponse[] = $asesorResponse;
        }

        return response()->json($asesoresResponse);
    }

    public function getAssignmentCount()
    {

        $assignmentCount = Assignment::countAllAssignments();
        $transferCount = Assignment::where('is_transferred', 1)->count();

        return response()->json([
            'nuevos' => $assignmentCount,
            'transferidos' => $transferCount,
        ]);
    }

    public function getNotifiaciones(Request $request)
    {
        $user = $request->user();

        $notifications = DateRemenber::where('status', 1)
            ->with(['conversation', 'conversation.assignment', 'conversation.customer'])
            ->get();

        $formattedNotifications = [];

        foreach ($notifications as $notification) {
            $conversation = $notification->conversation;

            if ($conversation && $conversation->assignment) {
                $assignment = $conversation->assignment;
                $contact = $conversation->customer;

                if ($assignment->count() > 0) {
                    $advisorName = $assignment->first()->advisor->name;
                    $formattedNotification = [
                        'id' => $notification->id,

                        'contact_name' => $contact->name,
                        'last_activity' => $notification->date_to_remenber . ' ' . $notification->time_to_remenber,
                        'advisorName' => $advisorName
                    ];
                    $formattedNotifications[] = $formattedNotification;
                }
            }
        }

        return response()->json(['notifications' => $formattedNotifications]);
    }


    public function listContactWaitTimes()
    {
        $today = now(); 
    
        $contactWaitTimes = ContactWaitTime::whereDate('created_at', $today)
            ->latest()
            ->get();
    
        $data = [];
    
        foreach ($contactWaitTimes as $contactWaitTime) {
            $contact = $contactWaitTime->contact;
    
            $data[] = [
                'Contacto' => $contact->name,
                'Espera' => $contactWaitTime->wait_time . ' s',
            ];
        }
    
        return response()->json($data);
    }
    
}
