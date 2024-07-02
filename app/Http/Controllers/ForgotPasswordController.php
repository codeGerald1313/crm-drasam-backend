<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(PasswordResetRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        $existingToken = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if ($existingToken) {
            $token = $existingToken->token;
        } else {
            $token = JWTAuth::fromUser($user);
            DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now()
            ]);
        }

        config([
            'mail.mailer' => 'smtp',
            'mail.host' => env('MAIL_HOST'),
            'mail.port' => env('MAIL_PORT'),
            'mail.username' => env('MAIL_USERNAME'),
            'mail.password' => env('MAIL_PASSWORD'),
            'mail.encryption' => env('MAIL_ENCRYPTION'),
        ]);

        try {
            Mail::send([], [], function ($message) use ($user, $token) {

                
                $appName = config('app.name');
                $subject = "Recuperación de Contraseña - $appName " . now()->format('Y-m-d H:i');

                $message->to($user->email)
                    ->subject($subject)
                    ->html('<p>Hola ' . $user->name . ',</p>
                        <p>Recientemente solicitaste restablecer tu contraseña en PROMOLIDER. Si no lo hiciste, puedes ignorar este correo.</p>
                        <p>Si deseas restablecer tu contraseña, haz clic en el siguiente enlace:</p>
                        <p><a href="' . url('reset-password/' . $token) . '">Restablecer Contraseña</a></p>
                        <p>Este enlace caducará en 1 hora por motivos de seguridad.</p>
                        <p>Gracias,</p>
                        <p>El equipo de PROMOLIDER</p>');
            });

            return response()->json([
                'message' => 'Enlace de recuperación enviado por correo electrónico',
                'token' => $token
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al enviar el correo electrónico',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
