<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Contact;
use App\Models\ContactWaitTime;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Nette\Utils\DateTime;

class ReportsController extends Controller
{
    private $days_week;

    public function __construct()
    {
        $this->days_week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    }

    public function newContactsWeek()
    {
        $week = date('Y-m-d', strtotime('-1 week'));
        $data = $this->getDataDays($week, Contact::get());

        return response()->json([
            'status' => 'success',
            'new_contacts' => $data[0],
            'total' => $data[1]
        ], Response::HTTP_OK);
    }

    public function newContactsMonth()
    {
        $month = date('Y-m-d', strtotime('-1 month'));
        $data = $this->getDataDays($month, Contact::get());

        return response()->json([
            'status' => 'success',
            'new_contacts' => $data[0],
            'total' => $data[1]
        ], Response::HTTP_OK);
    }

    public function newContactsPersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);
    
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT); // Asegurar dos dígitos en el mes
            $month_year = $request->year . '-' . $month;
    
            $data = $this->getDataDaysPersonalized(Contact::get(), $month_year);
    
            return response()->json([
                'status' => 'success',
                'new_contacts' => $data[0],
                'total' => $data[1]
            ], Response::HTTP_OK);
        } catch (Exception $error) {
    
            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entrega de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    

    public function closedConversationsWeek()
    {
        $closed_conversations = Assignment::where('state', 0)->get(); 
        $month = date('Y-m-d H:i:s', strtotime('-1 month'));
        $data = $this->getDataDays($month, $closed_conversations);
    
        return response()->json([
            'status' => 'success',
            'closed_contacts' => $data[0],
            'total' => $data[1]
        ], Response::HTTP_OK);
    }
    

    public function closedConversationsMonth()
    {
        $closed_conversations = Assignment::where('state', 0)->get(); 
        $week = date('Y-m-d H:i:s', strtotime('-1 week'));
        $data = $this->getDataDays($week, $closed_conversations);
    
        return response()->json([
            'status' => 'success',
            'closed_contacts' => $data[0],
            'total' => $data[1]
        ], Response::HTTP_OK);
    }
    

    public function closedConversationsPersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);
    
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT); // Asegurar dos dígitos en el mes
            $month_year = $request->year . '-' . $month;
    
            $closed_conversations = Assignment::where('state', 0)->get(); 
            $data = $this->getDataDaysPersonalized($closed_conversations, $month_year);
    
            return response()->json([
                'status' => 'success',
                'closed_contacts' => $data[0],
                'total' => $data[1]
            ], Response::HTTP_OK);
        } catch (Exception $error) {
    
            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entrega de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    

    public function closedClientsWeek()
    {
        date_default_timezone_set('America/Lima');
        $week = date('Y-m-d H:i:s', strtotime('-1 week'));
    
        $advisors = DB::select('SELECT DISTINCT advisor_id FROM assignments WHERE state = 0 AND created_at > ?', [$week]);
        $data = [];
    
        foreach ($advisors as $element) {
            $advisor = User::find($element->advisor_id);
            $assignments = Assignment::where('advisor_id', $element->advisor_id)
                ->where('state', 0) 
                ->where('created_at', '>', $week)
                ->get();
    
            $contacts = [];
    
            foreach ($assignments as $value) {
                $contact = Contact::find($value->contact_id);
    
                array_push($contacts, [
                    'contact' => $contact->name,
                    'date_closed' => date("Y-m-d H:i:s", strtotime($value->updated_at))
                ]);
            }
    
            array_push($data, [
                'advisor' => $advisor->name . ' ' . $advisor->last_name,
                'contacts' => $contacts
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], Response::HTTP_OK);
    }
    

    public function closedClientsMonth()
    {
        date_default_timezone_set('America/Lima');
        $month = date('Y-m-d H:i:s', strtotime('-1 month'));
    
        $advisors = DB::select('SELECT DISTINCT advisor_id FROM assignments WHERE state = ?', [0]);
        $data = [];
    
        foreach ($advisors as $element) {
            $advisor = User::find($element->advisor_id);
            $assignments = Assignment::where('advisor_id', $element->advisor_id)
                ->where('state', 0) 
                ->where('created_at', '>', $month)
                ->get();
    
            $contacts = [];
    
            foreach ($assignments as $value) {
                $contact = Contact::find($value->contact_id);
    
                array_push($contacts, [
                    'contact' => $contact->name,
                    'date_closed' => date("Y-m-d H:i:s", strtotime($value->updated_at))
                ]);
            }
    
            array_push($data, [
                'advisor' => $advisor->name . ' ' . $advisor->last_name,
                'contacts' => $contacts
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], Response::HTTP_OK);
    }
    
    

    public function closedClientsPersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);
    
            $month_year = '';
            if ($request->month > 9) {
                $month_year = $request->year . '-' . $request->month;
            } else {
                $month_year = $request->year . '-0' . $request->month;
            }
    
            $dateObj_init = date_create($month_year . '-01')->format('Y-m-d');
            $dateObj_finish = date_create($month_year . '-01')->modify('first day of next month')->format('Y-m-d');
    
            $advisors = DB::select('SELECT DISTINCT advisor_id FROM assignments WHERE state = ? AND created_at >= ? AND created_at < ?', [0, $dateObj_init, $dateObj_finish]);
    
            $data = [];
    
            foreach ($advisors as $element) {
                $advisor = User::find($element->advisor_id);
                $assignments = Assignment::where('advisor_id', $element->advisor_id)
                    ->where('state', 0)
                    ->where('created_at', '>=', $dateObj_init)
                    ->where('created_at', '<', $dateObj_finish)
                    ->get();
    
                if ($assignments->count() > 0) { // Solo agrega si hay asignaciones
                    $contacts = [];
    
                    foreach ($assignments as $value) {
                        $contact = Contact::find($value->contact_id);
    
                        array_push($contacts, [
                            'contact' => $contact->name,
                            'date_closed' => date("Y-m-d H:i:s", strtotime($value->updated_at))
                        ]);
                    }
    
                    array_push($data, [
                        'advisor' => $advisor->name . ' ' . $advisor->last_name,
                        'contacts' => $contacts,
                    ]);
                }
            }
    
            return response()->json([
                'status' => 'success',
                'data' => $data
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entrega de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    

    public function teamPerformanceWeek()
    {
        $week = date('Y-m-d H:i:s', strtotime('-1 week'));
    
        $advisors = DB::select('SELECT DISTINCT advisor_id FROM assignments WHERE state = ? AND created_at > ?', [0, $week]);
        $t_closed_conversations = Assignment::where('state', 0)
            ->where('created_at', '>', $week)
            ->count();
        $t_contacts = 0;
    
        $data = [];
        $advisor_inf = [];
    
        foreach ($advisors as $element) {
            $advisor = User::find($element->advisor_id);
            $count_contacts = Assignment::where('advisor_id', $element->advisor_id)
                ->where('created_at', '>', $week)
                ->count();
    
            $t_contacts += $count_contacts;
    
            $closed_conversations = Assignment::where('advisor_id', $element->advisor_id)
                ->where('state', 0)
                ->where('created_at', '>', $week)
                ->count();
        // Obtener el último wait_time
        $wait_time = ContactWaitTime::where('advisor_id', $element->advisor_id)
            ->where('created_at', '>', $week)
            ->latest('created_at')
            ->value('wait_time');
    
            array_push($advisor_inf, [
                'advisor' => $advisor->name . ' ' . $advisor->last_name,
                'total_contacts' => $count_contacts,
                'closed_conversations' => $closed_conversations,
                'wait_time' => $wait_time,
            ]);
        }
    
        array_push($data, [
            'advisor_inf' => $advisor_inf,
            't_contacts' => $t_contacts,
            't_closed_conversations' => $t_closed_conversations
        ]);
    
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], Response::HTTP_OK);
    }
    
    
    
    public function teamPerformanceMonth()
    {
        $month = date('Y-m-d H:i:s', strtotime('-1 month'));
    
        $advisors = DB::select('SELECT DISTINCT advisor_id FROM assignments WHERE state = ?', [0]);
        $t_closed_conversations = Assignment::where('state', 0)
            ->where('created_at', '>', $month)
            ->count();
        $t_contacts = 0;
    
        $data = [];
        $advisor_inf = [];
    
        foreach ($advisors as $element) {
            $advisor = User::find($element->advisor_id);
            $count_contacts = Assignment::where('advisor_id', $element->advisor_id)
                ->where('created_at', '>', $month)
                ->count();
    
            $t_contacts += $count_contacts;
    
            $closed_conversations = Assignment::where('advisor_id', $element->advisor_id)
                ->where('state', 0)
                ->where('created_at', '>', $month)
                ->count();
    
            // Obtener el último wait_time
            $wait_time = ContactWaitTime::where('advisor_id', $element->advisor_id)
                ->where('created_at', '>', $month)
                ->latest('created_at')
                ->value('wait_time');
    
            array_push($advisor_inf, [
                'advisor' => $advisor->name . ' ' . $advisor->last_name,
                'total_contacts' => $count_contacts,
                'closed_conversations' => $closed_conversations,
                'wait_time' => $wait_time,
            ]);
        }
    
        array_push($data, [
            'advisor_inf' => $advisor_inf,
            't_contacts' => $t_contacts,
            't_closed_conversations' => $t_closed_conversations
        ]);
    
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], Response::HTTP_OK);
    }
    
    

    public function teamPerformancePersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);
    
            $month_year = '';
    
            if ($request->month > 9) {
                $month_year = $request->year . '-' . $request->month . '-';
            } else if ($request->month <= 9) {
                $month_year = $request->year . '-0' . $request->month . '-';
            }
    
            $dateObj_init = date_create($month_year . '01')->format('Y-m-d');
            $dateObj_finish = date_create($month_year . '00')->modify('+1 month')->modify('previous day')->format('Y-m-d');
    
            $advisors = DB::select('SELECT DISTINCT advisor_id FROM assignments WHERE state = ? AND created_at >= ? AND created_at <= ?', [0, $dateObj_init, $dateObj_finish]);
            $t_closed_conversations = 0;
            $t_contacts = 0;
    
            $data = [];
            $advisor_inf = [];
    
            foreach ($advisors as $element) {
                $advisor = User::find($element->advisor_id);
                $count_contacts = Assignment::where('advisor_id', $element->advisor_id)
                    ->where('created_at', '>=', $dateObj_init)
                    ->where('created_at', '<=', $dateObj_finish)
                    ->count();
    
                $t_contacts += $count_contacts;
                $closed_conversations = Assignment::where('advisor_id', $element->advisor_id)
                    ->where('state', 0)
                    ->where('created_at', '>=', $dateObj_init)
                    ->where('created_at', '<=', $dateObj_finish)
                    ->count();
    
                $wait_time = ContactWaitTime::where('advisor_id', $element->advisor_id)
                    ->where('created_at', '>=', $dateObj_init)
                    ->where('created_at', '<=', $dateObj_finish)
                    ->latest('created_at')
                    ->value('wait_time');
    
                    $wait_time = ($wait_time !== null) ? $wait_time : 0;

                    
                $t_closed_conversations += $closed_conversations;
    
                array_push($advisor_inf, [
                    'advisor' => $advisor->name . ' ' . $advisor->last_name,
                    'total_contacts' => $count_contacts,
                    'closed_conversations' => $closed_conversations,
                    'wait_time' => $wait_time,
                ]);
            }
    
            array_push($data, [
                'advisor_inf' => $advisor_inf,
                't_contacts' => $t_contacts,
                't_closed_conversations' => $t_closed_conversations
            ]);
    
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (Exception $error) {
    
            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entrega de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    

    public function firstResponseTimeWeek()
    {
        date_default_timezone_set('America/Lima');
        $week = date('Y-m-d', strtotime('-1 week'));
        $conversations = Conversation::where('status', 0)->where('created_at', '>=', $week)->get();

        $moda = $this->FashionConvert($conversations);

        return response()->json([
            'status' => 'success',
            'data' => $moda
        ], Response::HTTP_OK);
    }

    public function firstResponseTimeMonth()
    {
        date_default_timezone_set('America/Lima');
        $week = date('Y-m-d', strtotime('-1 month'));
        $conversations = Conversation::where('status', 0)->where('created_at', '>=', $week)->get();

        $moda = $this->FashionConvert($conversations);

        return response()->json([
            'status' => 'success',
            'data' => $moda
        ], Response::HTTP_OK);
    }

    public function firstResponseTimePersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);

            $month_year = '';

            if ($request->month > 9) {
                $month_year = $request->year . '-' . $request->month . '-';
            } else if ($request->month <= 9) {
                $month_year = $request->year . '-0' . $request->month . '-';
            }

            $dateObj_init = date_create($month_year . '01')->format('Y-m-d');
            $dateObj_finish = date_create($month_year . '00')->modify('+1 month')->modify('previous day')->format('Y-m-d');

            $conversations = Conversation::where('status', 0)->where('created_at', '>=', $dateObj_init)->where('created_at', '<=', $dateObj_finish)->get();

            $moda = $this->FashionConvert($conversations);

            return response()->json([
                'status' => 'success',
                'data' => $moda
            ], Response::HTTP_OK);
        } catch (Exception $error) {

            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entraga de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function FashionConvert($conversations)
    {
        try {
            $arr_hours_init = [];
            $arr_hours_finish = [];

            $arr_mod_h = [];

            foreach ($conversations as $element) {
                $str_i = [];
                $str_f = [];

                for ($i = 11; $i < 19; $i++) {
                    array_push($str_i, strval($element->start_date)[$i]);
                    array_push($str_f, strval($element->updated_at)[$i]);
                }

                array_push($arr_hours_init, implode('', $str_i));
                array_push($arr_hours_finish, implode('', $str_f));
            }

            for ($i = 0; $i < count($arr_hours_init); $i++) {
                $diff_seg = strtotime($arr_hours_finish[$i]) - strtotime($arr_hours_init[$i]);
                $hours = floor($diff_seg / 3600);
                $seg_res = $diff_seg % 3600;
                $min = floor($seg_res / 60);
                $seg = $seg_res % 60;

                $msg = '';

                if ($hours > 0) {
                    $msg .= "$hours h ";
                }

                if ($min > 0) {
                    $msg .= "$min min ";
                }

                if ($seg > 0) {
                    $msg .= "$seg s ";
                }

                array_push($arr_mod_h, $msg);
            }

            $moda = $this->calculateFashion($arr_mod_h);

            return $moda;
        } catch (Exception $error) {
            return 'no hay data';
        }
    }

    private function calculateFashion($array)
    {
        try {
            if ($array == null) {
                return $fashion[0] = '0';
            } else {

                $frequencies = array_count_values($array);
                $max_frequencies = max($frequencies);
                $fashion = array();

                foreach ($frequencies as $element => $frequencies) {
                    if ($frequencies == $max_frequencies) {
                        $fashion[] = $element;
                    }
                }

                return $fashion;
            }
        } catch (Exception $error) {
            return $fashion[0] = '0';
        }
    }

    private function getDataDaysPersonalized($data, $month_year)
    {
        $total = 0;
        $list_contacts = [];
        $list_days_week = [];
        $list_day = [];

        foreach ($data as $element) {
            $date_contact_string = [];

            for ($i = 0; $i < 7; $i++) {
                $character = strval($element->created_at)[$i];
                array_push($date_contact_string, $character);
            }

            if (strcmp(implode('', $date_contact_string), strval($month_year)) === 0) {
                array_push($list_contacts, $element);
            }
        }

        foreach ($list_contacts as $element) {
            $date_contact_string = [];

            for ($i = 0; $i < 10; $i++) {
                $character = strval($element->created_at)[$i];
                array_push($date_contact_string, $character);
            }

            $name_day = date('l', strtotime(implode('', $date_contact_string)));
            array_push($list_days_week, $name_day);
        }

        for ($i = 0; $i < count($this->days_week); $i++) {
            $count = $this->modDay($list_days_week, $this->days_week[$i]);
            $total = $total + $count;
            $list_day[$i] = ['day' => $this->days_week[$i], 'number' => $count];
        }

        return [$list_day, $total];
    }

    private function getDataDays($type_filter, $data)
    {
        date_default_timezone_set('America/Lima');

        $date_number = [];
        $date_string = [];
        $list_day = [];
        $total = 0;

        foreach ($data as $element) {
            if ($element->created_at >= $type_filter) {
                $array_date = [];
                for ($i = 0; $i < 10; $i++) {
                    $character = strval($element->created_at)[$i];
                    array_push($array_date, $character);
                }
                $string_date = implode('', $array_date);
                array_push($date_number, $string_date);
            }
        }

        foreach ($date_number as $element) {
            $fecha = new DateTime($element);
            $date = $fecha->format('l');
            array_push($date_string, $date);
        }

        for ($i = 0; $i < count($this->days_week); $i++) {
            $count = $this->modDay($date_string, $this->days_week[$i]);
            $total = $total + $count;
            $list_day[$i] = ['day' => $this->days_week[$i], 'number' => $count];
        }

        return [$list_day, $total];
    }

    private function modDay($array, $day)
    {
        $count = 0;

        foreach ($array as $value) {
            if ($value == $day) {
                $count = $count + 1;
            }
        }

        return $count;
    }

    public function busiestConversationTimeWeek()
    {
        date_default_timezone_set('America/Lima');
        $week = date('Y-m-d', strtotime('-1 week'));
        $messages = Message::where('created_at', '>=', $week)->get();

        $data = $this->getBusiestConversationTime($messages);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function busiestConversationTimeMonth()
    {
        date_default_timezone_set('America/Lima');
        $month = date('Y-m-d', strtotime('-1 month'));
        $messages = Message::where('created_at', '>=', $month)->get();

        $data = $this->getBusiestConversationTime($messages);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function busiestConversationTimePersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);

            $month_year = '';

            if ($request->month > 9) {
                $month_year = $request->year . '-' . $request->month . '-';
            } else if ($request->month <= 9) {
                $month_year = $request->year . '-0' . $request->month . '-';
            }

            $dateObj_init = date_create($month_year . '01')->format('Y-m-d');
            $dateObj_finish = date_create($month_year . '00')->modify('+1 month')->modify('previous day')->format('Y-m-d');

            $messages = Message::where('created_at', '>=', $dateObj_init)->where('created_at', '<=', $dateObj_finish)->get();

            $data = $this->getBusiestConversationTime($messages);

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], Response::HTTP_OK);
        } catch (Exception $error) {

            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entraga de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function getBusiestConversationTime($messages)
    {
        $tomorrow_1 = date('H:i:s', strtotime('05:00:00'));
        $tomorrow_2 = date('H:i:s', strtotime('11:59:59'));

        $late_1 = date('H:i:s', strtotime('12:00:00'));
        $late_2 = date('H:i:s', strtotime('18:59:59'));

        $evening_1 = date('H:i:s', strtotime('19:00:00'));
        $evening_2 = date('H:i:s', strtotime('23:59:59'));

        $data = [];
        $data_time_1 = [];
        $data_time_2 = [];
        $data_time_3 = [];

        foreach ($messages as $element) {
            $timestamp = strtotime(str_replace("/", "-", $element->date_of_issue));
            $day_week = date("l", $timestamp);

            foreach ($this->days_week as $value) {
                if ($day_week == $value) {
                    $doi = strtotime(strval($element->date_of_issue));
                    $com = date("H:i:s", $doi);

                    if ($com >= $tomorrow_1 && $com <= $tomorrow_2) {
                        array_push($data_time_1, $day_week);
                    } else if ($com >= $late_1 && $com <= $late_2) {
                        array_push($data_time_2, $day_week);
                    } else if ($com >= $evening_1 && $com <= $evening_2) {
                        array_push($data_time_3, $day_week);
                    }
                }
            }
        }

        $tomorrow = $this->getCountDay($data_time_1);
        $late = $this->getCountDay($data_time_2);
        $evening = $this->getCountDay($data_time_3);

        $total_tomorrow = array_sum($tomorrow);
        $total_late = array_sum($late);
        $total_evening = array_sum($evening);
        
        // Calcula el total general
        $total_all = $total_tomorrow + $total_late + $total_evening;


        for ($i = 0; $i < 7; $i++) {
            $data[] = ['count' => $tomorrow[$i]];
            $data[] = ['count' => $late[$i]];
            $data[] = ['count' => $evening[$i]];
        }

        $data_1ss = [];
        array_push($data_1ss, ['tomorrow' => $tomorrow, 'late' => $late, 'evening' => $evening, 'total_all' => $total_all]);





        if ($data === null) {
            return 'No hay data';
        } else {
            return $data_1ss;
        }
    }

    private function getCountDay($arr)
    {
        $data = [];

        for ($i = 0; $i < count($this->days_week); $i++) {
            $count = $this->modDay($arr, $this->days_week[$i]);
            $data[$i] = $count;
        }

        return $data;
    }

    public function interactionsWithContactsWeek()
    {
        $week = date('Y-m-d', strtotime('-1 week'));
        $data = $this->getDataDays($week, Assignment::get());

        return response()->json([
            'status' => 'success',
            'data' => $data[0],
            'total' => $data[1]
        ], Response::HTTP_OK);
    }

    public function interactionsWithContactsMonth()
    {
        $month = date('Y-m-d', strtotime('-1 month'));
        $data = $this->getDataDays($month, Assignment::get());

        return response()->json([
            'status' => 'success',
            'data' => $data[0],
            'total' => $data[1]
        ], Response::HTTP_OK);
    }

    public function interactionsWithContactsPersonalized(Request $request)
    {
        try {
            $this->validate($request, [
                'month' => 'required',
                'year' => 'required'
            ]);
    
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT); 
            $month_year = $request->year . '-' . $month;
    
            $data = $this->getDataDaysPersonalized(Assignment::get(), $month_year);
    
            return response()->json([
                'status' => 'success',
                'data' => $data[0],
                'total' => $data[1]
            ], Response::HTTP_OK);
        } catch (Exception $error) {
    
            return response()->json([
                'status' => 'Error',
                'message' => 'Hubo un error en la entrega de datos',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    

    // week, month, personalized
    public function week()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'firtsResponseTime' => $this->firstResponseTimeWeek(),
                'newContacts' =>  $this->newContactsWeek(),
                'closedConversations' => $this->closedConversationsWeek(),
                'interactionsWithContacts' => $this->interactionsWithContactsWeek(),
                'closedClients' => $this->closedClientsWeek(),
                'teamPerformance' => $this->teamPerformanceWeek(),
                'busiestConversationTime' => $this->busiestConversationTimeWeek(),
            ]
        ]);
    }

    public function month()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'firtsResponseTime' => $this->firstResponseTimeMonth(),
                'newContacts' =>  $this->newContactsMonth(),
                'closedConversations' => $this->closedConversationsMonth(),
                'interactionsWithContacts' => $this->interactionsWithContactsMonth(),
                'closedClients' => $this->closedClientsMonth(),
                'teamPerformance' => $this->teamPerformanceMonth(),
                'busiestConversationTime' => $this->busiestConversationTimeMonth(),
            ]
        ], Response::HTTP_OK);
    }

    public function personalized(Request $request)
    {
        try {
            $request->validate([
                'month' => 'required',
                'year' => 'required'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'firtsResponseTime' => $this->firstResponseTimePersonalized($request),
                    'newContacts' =>  $this->newContactsPersonalized($request),
                    'closedConversations' => $this->closedConversationsPersonalized($request),
                    'interactionsWithContacts' => $this->interactionsWithContactsPersonalized($request),
                    'closedClients' => $this->closedClientsPersonalized($request),
                    'teamPerformance' => $this->teamPerformancePersonalized($request),
                    'busiestConversationTime' => $this->busiestConversationTimePersonalized($request),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'success',
                'message' => 'error al entregar la data',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
