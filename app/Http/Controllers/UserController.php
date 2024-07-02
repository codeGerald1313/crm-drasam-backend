<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::paginate(5);

        return response()->json([
            'status' => 'success',
            'users' => $data
        ], Response::HTTP_OK);
    }

    public function records()
    {
        $users = DB::table('users')
            ->select('users.id','users.name','users.last_name','users.email',DB::raw('"" as password'),'roles.name as role', 'roles.id as role_id','users.status as status','users.hour_start','users.hour_end')

            ->join('model_has_roles', function ($query) {
                $query->on('users.id', '=', 'model_has_roles.model_id');
            })
            ->leftJoin('roles', function ($query) {
                $query->on('model_has_roles.role_id', '=', 'roles.id');
            })->get();
        return response()->json([
            'status' => 'success',
            'users' => $users
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request, $id = null)
    {
        try {

            $id = $request->input('id');
            $user = User::firstOrNew(['id' => $id]);
            $user->name = $request->input('name');
            $user->last_name = $request->input('last_name');
            $user->email = $request->input('email');
            $user->document = $request->input('document');
            $user->hour_start = $request->input('hour_start');
            $user->hour_end = $request->input('hour_end');

            if($id){
                $user->password = !empty($request->input('password')) ? bcrypt($request->input('password')) : $user->password;
            }else{
                $user->password = bcrypt($request->input('password'));
            }

            $user->save();

            DB::table('model_has_roles')->where('model_id', $user->id)->delete();

            $user->assignRole(strval($request->role));

            return response()->json([
                'status' => 'success',
                'message' => $id ? 'Usuario actualizado' : 'Usuario registrado',
                'data' => $user
            ],  Response::HTTP_CREATED);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registro no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registro recuperado con éxito',
                'data' => new UserResource($user)
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al recuperar el registro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'nullable'
            ]);
    
            $user = User::find($id);
    
            if ($request->password == null) {
                $user->update([
                    'name' => $request->name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                ]);
            }
            else {
                $user->update([
                    'name' => $request->name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                ]);

                $this->updatePassword($request, $id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registro actualizado'
            ], Response::HTTP_OK);
        }
        catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar',
                'error' => $error->getMessage()              
            ]);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        try {
            
            $user = User::find($id);

            if($id) {
                $user->password = !empty($request->input('password')) ? bcrypt($request->input('password')) : $user->password;
            }
            else {
                $user->password = bcrypt($request->input('password'));
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $newPassword = $request->input('password');
            $user->password = Hash::make($newPassword);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña actualizada exitosamente'
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la contraseña',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            // Cambia el estado del contacto a "borrado"
            $user->update([
                'status' => 0
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registro eliminado exitosamente',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al eliminar el registro',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
