<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClousureReasonRequest;
use App\Http\Resources\ClousureReasonCollection;
use App\Http\Resources\ClousureReasonResource;
use App\Models\ClousureReason;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ClousureReasonController extends Controller
{
    public function index()
    {
        try {

            $clousureReasons = ClousureReason::where('status', '!=', 0)->get();

            if ($clousureReasons->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron motivos de cierre activos.',
                    'status' => 'error'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => 'Motivos de cierre activos recuperados con éxito.',
                'status' => 'success',
                'data' => new ClousureReasonCollection($clousureReasons)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error.',
                'status' => ['error' => $e->getMessage()]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {

            $clousureReason = ClousureReason::where('id', $id)
                ->where('status', '!=', 0)
                ->first();

            if (!$clousureReason) {
                return response()->json([
                    'message' => 'Razón de cierre no encontrada o está deshabilidata.',
                    'status' => 'error'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => 'Razón de cierre recuperada con éxito.',
                'status' => 'success',
                'data' => new ClousureReasonResource($clousureReason)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'status' => ['error' => $e->getMessage()]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(ClousureReasonRequest $request, $id = null)
    {
        DB::beginTransaction();

        try {
            if ($id) {
                $clousureReason = ClousureReason::findOrFail($id);
                if ($clousureReason->status === 0) {
                    return response()->json([
                        'message' => 'No se puede editar. La razón de cierre está deshabilitada.',
                        'status' => 'error'
                    ], Response::HTTP_BAD_REQUEST);
                }
                $messageAction = 'actualizado';
            } else {
                $clousureReason = new ClousureReason();
                $messageAction = 'registrado';
            }

            $clousureReason->name = $request->input('name');

            $clousureReason->save();

            DB::commit();

            $statusCode = $id ? Response::HTTP_OK : Response::HTTP_CREATED;

            return response()->json([
                'message' => "Razón de cierre {$messageAction} con éxito.",
                'status' => 'success',
                'data' => $clousureReason
            ], $statusCode);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Ha ocurrido un error',
                'status' => ['error' => $e->getMessage()],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $clousureReason = ClousureReason::find($id);

            if (!$clousureReason) {
                return response()->json([
                    'message' => 'Motivo de cierre no encontrado.',
                    'status' => 'error',
                ], Response::HTTP_NOT_FOUND);
            }

            if ($clousureReason->status === 0) {
                return response()->json([
                    'message' => 'No se puede eliminar. La razón de cierre ya está deshabilitada.',
                    'status' => 'error'
                ], Response::HTTP_BAD_REQUEST);
            }

            $clousureReason->update(['status' => 0]);

            DB::commit();

            return response()->json([
                'message' => 'Motivo de cierre eliminado con éxito.',
                'status' => 'success',
            ], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error.',
                'status' => ['error' => $e->getMessage()],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
