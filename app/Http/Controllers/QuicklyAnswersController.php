<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\QuicklyAnswers;

class QuicklyAnswersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $userId)
    {
        $answers = QuicklyAnswers::where(function ($query) use ($userId) {
            $query->where('type', 'answers')
                  ->where('user_id', $userId);
        })
        ->orWhere(function ($query) {
            $query->where('type', 'template');
        })->get();

        return response()->json([
            'status' => 'success',
            'data' => $answers
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'message' => 'required'
            ]);

            $user = auth()->user();

            $answer = QuicklyAnswers::updateOrCreate(
                ['id' => $request->id],
                [
                    'title' => $request->title,
                    'message' => $request->message,
                    'user_id' => $user->id,
                    'type' => $request->type
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Respuesta rapida creada con exito',
                'quickly answers' => $answer
            ], Response::HTTP_CREATED);
        }
        catch (Exception $error){
            return response()->json([
                'status' => 'success',
                'message' => 'Hubo un errro al momentos de crear la respuesta rapida ',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id)
    {
        $asnwer = QuicklyAnswers::find($id);
        $asnwer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Respuesta rapida eliminada'
        ], Response::HTTP_OK);
    }
}
