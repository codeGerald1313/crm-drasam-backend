<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Response;
use App\Events\UserStatusUpdated;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WspController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Assignment;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\RoleStatus;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected function respondWithToken($token)
    {

        $userData = User::with('roles.permissions')->findOrFail(auth()->id());
        $permissions = collect();
        $roles = $userData->roles->map(function ($rol) use (&$permissions) {
            $permissions = $permissions->merge($rol->permissions);

            return ['name' => $rol->name, 'guard_name' => $rol->guard_name];
        });

        if ($userData->hasRole('admin')) {
            $permissions = Permission::all();
        } else {
            $permissions = $permissions->merge($userData->permissions)->map(function ($per) {
                return ['name' => $per->name, 'guard_name' => $per->guard_name];
            })->unique();
        }

        $UserRolePermission =  [
            'id' => auth()->id(),
            'name' => $userData->name,
            'last_name' => $userData->last_name,
            'email' => $userData->email,
            'status' => $userData->status,
            'validate' => $userData->admin,
            'permissions' => $permissions,
            'roles' => $roles
        ];

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $UserRolePermission
        ]);
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo refrescar el token',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas'
            ], Response::HTTP_BAD_REQUEST);
        }

        $now = now();
        $startTime = Carbon::parse($user->hour_start);
        $endTime = Carbon::parse($user->hour_end);

        if (!$now->between($startTime, $endTime)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No estás autorizado para ingresar en este momento'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales invalidas'
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch (JWTException $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar el token',
                'error' => $error->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user->update(['last_login' => now()]);

        if ($user->admin != 1) {
            $user->update(['inline' => 1]);

            event(new UserStatusUpdated($user, 1));
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        try {
            $user_id = auth()->user()->id;

            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            } else {
                return response()->json(['message' => 'Token no encontrado'], Response::HTTP_BAD_REQUEST);
            }

            $user = User::find($user_id);

            if ($user->admin != 1) {

                $user->update(['inline' => 0]);

                event(new UserStatusUpdated($user, 0));
            }

            return response()->json(['message' => 'Sesión cerrada con éxito'], Response::HTTP_OK);
        } catch (JWTException $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar la sesión',
                'error' => $error->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function register(RegisterRequest $request)
    {
        // Validar la solicitud
        $data = $request->only('email', 'password');

        // Generar un número de documento correlativo de 8 dígitos
        $latestUser = User::latest('id')->first();
        $latestDocumentNumber = $latestUser ? (int)$latestUser->document : 0;
        $newDocumentNumber = str_pad($latestDocumentNumber + 1, 8, '0', STR_PAD_LEFT);


        // Verificar si el usuario ya existe
        if (User::where('email', $data['email'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'El usuario ya existe'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Crear el usuario
        $user = User::create([
            'document' => $newDocumentNumber,
            'name' => $data['name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 2,
            'admin' => 0,
            'hour_start' => \Carbon\Carbon::createFromTime(0, 0, 0)->format('H:i:s'),
            'hour_end' => \Carbon\Carbon::createFromTime(12, 0, 0)->format('H:i:s')
        ]);

        // Asignar el rol de usuario (o admin si necesario)
        $role = Role::where('name', 'admin')->first();
        $user->assignRole($role);

        // Actualizar estado de rol
        RoleStatus::create(['status' => 1, 'id_role' => intval($role->id)]);

        // Permisos predeterminados para el rol
        $permissions = [
            // 'users.list', 'users.create', 'users.edit', 'users.record', 'users.delete',
            //'contacts.list', 'contacts.create', 'contacts.edit', 'contacts.record', 'contacts.delete',
            //'type_document.list',
            //'role.list', 'role.create', 'role.edit', 'role.delete', 'role.destroy',
            //'permission.list', 'permission.give_permissions', 'permission.revoke_permissions',
            //'conexion.list', 'conexion.create', 'conexion.edit', 'conexion.delete', 'conexion.destroy',
            //'closure_reasons.list', 'closure_reasons.create', 'closure_reasons.update', 'closure_reasons.record', 'closure_reasons.delete',
            //'quickly_answers.list', 'quickly_answers.create', 'quickly_answers.delete',
            // 'data.records', 'data.conversations', 'data.messages', 'data.asign', 'data.contact_create', 'data.asign_chat', 'data.close_conversation', 'data.reasing_conversation', 'data.reminder', 'data.add_tag',
            //'reports.list', 'reports.new_conversations', 'reports.monitor_activities', 'reports.team_performance', 'reports.filter_by',
            //'mass_messages.list', 'mass_messages.send_mass_messages', 'mass_messages.details_mass_messages',
            //'dashboard.list',
            'data',
        ];

        // Asignar permisos al rol
        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission);
        }

        // Generar token de acceso para el usuario usando JWT
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo crear el token'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Crear una nueva conversación
        try {
            $contactId = 24; // ID de contacto fijo
            $contact = Contact::find($contactId);

            $conversation = Conversation::create([
                'contact_id' => $contactId,
                'status' => 'open',
                'status_bot' => 0,
                'start_date' => now(),
                'last_activity' => now()
            ]);

            // Crear nueva asignación
            $assignment = Assignment::create([
                'contact_id' => $contactId,
                'conversation_id' => $conversation->id,
                'advisor_id' => $user->id,
                'interes_en' => $data['interes_en'] ?? null
            ]);

            // Enviar mensaje estructurado predeterminado
            $wspController = new WspController();

            // Enviar mensaje estructurado predeterminado
            $welcomeMessage = "🌟 ¡Bienvenido a Drasam CRM! 🌟\n\nEn Drasam, estamos aquí para ti, nuestra comunidad. 🏠 Antes de comenzar, necesitamos tu número de DNI para brindarte la mejor asistencia posible. 📋\n\n¡Por favor, compártelo con nosotros para ayudarte a resolver tus consultas rápidamente! 🙌";
            $responseMessage = $wspController->struct_message($welcomeMessage, $contact->num_phone);

            // Enviar el mensaje estructurado
            $messageResponse = $wspController->envia($responseMessage);
            // Guardar el mensaje de bienvenida utilizando $messageResponse
            $messageGuardado = $wspController->createMessageWelcome($conversation, $messageResponse, $responseMessage, $user);

            // Respuesta exitosa
            return response()->json([
                'access_token' => $token,
                'messsage_api_wpp' => $messageResponse,
                'messageGuardado_message' => $messageGuardado,
                'token_type' => 'Bearer',
                'expires_in' => 3600, // Tiempo de expiración del token (ajustar según necesidad)
                'user' => $user->load('permissions', 'roles'),
                'message' => 'Asignación creada correctamente',
                'data' => $assignment
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Manejo de errores
            Log::error('Error en el registro de asignación: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el registro de asignación'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
