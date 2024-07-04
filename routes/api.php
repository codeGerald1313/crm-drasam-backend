<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WspController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ConexionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatBootController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MassMessagesController;
use App\Http\Controllers\ClousureReasonController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\QuicklyAnswersController;
use App\Http\Controllers\ContactWaitTimeController;
use App\Http\Controllers\CustomBroadcastingAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Login 

Route::group(['prefix' => '/v1'], function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::post('register', [AuthController::class, 'register']);

    
    Route::middleware('jwt.verify')->group(function () {

        Route::post('custom-broadcasting-auth', [CustomBroadcastingAuthController::class, 'authenticate']);

        //Refresh Token
        Route::controller(AuthController::class)->prefix('auth')->group(function () {
            Route::post('/refresh-token', 'refresh');
            Route::post('/logout', 'logout');
        });

        //Users
        Route::controller(UserController::class)->prefix('users')->group(function () {
            Route::get('list', 'index')->middleware('can:users.list');
            Route::post('create', 'store')->middleware('can:users.create');

            Route::put('update-password/{id}', 'updatePassword')->middleware('can:users.edit');
            Route::put('edit/{id}', 'store')->middleware('can:users.edit');
            Route::put('edit_config/{id}', 'update')->middleware('can:users.edit');

            Route::get('records', 'records');
            Route::get('record/{id}', 'show')->middleware('can:users.record');
            Route::delete('delete/{id}', 'destroy')->middleware('can:users.delete');
        })->middleware('can:users');

        //Contacts
        Route::controller(ContactController::class)->prefix('contacts')->group(function () {
            Route::get('list', 'index')->middleware('can:contacts.list');
            Route::post('create', 'store')->middleware('can:contacts.create');
            Route::put('edit/{id}', 'store')->middleware('can:contacts.edit');
            Route::get('record/{id}', 'show')->middleware('can:contacts.record');
            Route::delete('delete/{id}', 'destroy')->middleware('can:contacts.delete');
        })->middleware('can:contacts');

        //Type Document
        Route::controller(DocumentController::class)->prefix('documents')->group(function () {
            Route::get('list', 'index')->middleware('can:documents')->middleware('can:type_document.list');
        })->middleware('can:type_document');

        //Role
        Route::controller(RoleController::class)->prefix('roles')->group(function () {
            Route::get('/list', 'index')->middleware('can:role.list');
            Route::get('/list/{id}', 'querypermisos')->middleware('can:role.list');

            Route::post('/create', 'store')->middleware('can:role.create');
            Route::put('/edit/{id}', 'update')->middleware('can:role.edit');
            Route::delete('/delete/{id}', 'update_destroy')->middleware('can:role.delete');
            Route::delete('/destroy/{id}', 'destroy')->middleware('can:role.destroy');
            Route::get('/active-status/{id}', 'sendActiveStatus')->middleware('can:role.edit');
        })->middleware('can:roles');

        //Permission
        Route::controller(PermissionController::class)->prefix('permissions')->group(function () {
            Route::get('list', 'index')->middleware('can:permission.list');
            Route::get('permissionAll/{id}', [PermissionController::class, 'permissionAll'])->middleware('can:role.list');

            //Give permissions to users
            Route::post('givePermissions/{id}', 'givePermissions')->middleware('can:permission.give_permissions');
            Route::delete('revokePermissions/{id}', 'revokePermissions')->middleware('can:permission.revoke_permissions');
        })->middleware('can:permissions');

        //Conexion
        Route::controller(ConexionController::class)->prefix('conexions')->group(function () {
            Route::get('list', 'index')->middleware('can:conexion.list');
            Route::post('create', 'store')->middleware('can:conexion.create');
            Route::post('updateStatusBot', 'updateStatusBot')->middleware('can:conexion.edit');
            Route::put('edit/{id}', 'update')->middleware('can:conexion.edit');
            Route::delete('delete/{id}', 'delete')->middleware('can:conexion.delete');
            Route::delete('destroy/{id}', 'destroy')->middleware('can:conexion.destroy');
        })->middleware('can:conexion');


        //Closure Reasons
        Route::controller(ClousureReasonController::class)->prefix('closure_reasons')->group(function () {
            Route::get('list', 'index')->middleware('can:closure_reasons.list');
            Route::post('create', 'store')->middleware('can:closure_reasons.create');
            Route::put('update/{id}', 'store')->middleware('can:closure_reasons.update');
            Route::get('record/{id}', 'show')->middleware('can:closure_reasons.record');
            Route::delete('delete/{id}', 'destroy')->middleware('can:closure_reasons.delete');
        })->middleware('can:closure_reasons');

         //Quickly Answers 
         Route::controller(QuicklyAnswersController::class)->prefix('quickly_answers')->group(function () {
            Route::get('list/{userId}', 'index')->middleware('can:quickly_answers.list');
            Route::post('create', 'store')->middleware('can:quickly_answers.create');
            Route::delete('delete/{id}', 'destroy')->middleware('can:quickly_answers.delete');
        })->middleware('can:quickly_answers');

        Route::controller(DataController::class)->prefix('data')->group(function () {
            Route::get('/records', 'records')->middleware('can:data.records');
            Route::get('/conversations', 'conversations')->middleware('can:data.conversations');
            Route::get('/messages/{id}', 'messages')->middleware('can:data.messages');
            Route::post('/asign/{conversationId}/{advisorId}', 'updateAssignment')->middleware('can:data.asign');
            Route::post('/contact/create', 'registerContactAsign')->middleware('can:data.contact_create');
            Route::post('/asign/chat/{asignId}/{advisorId}', 'updateAssignmentChat')->middleware('can:data.asign_chat');
            Route::post('/asign/chat/contact/{asignId}/{advisorId}', 'updateregisterContactAsign')->middleware('can:data.asign_chat');
            Route::post('/close/conversation/{asignId}/{selectedReasonInt}', 'closeReasonConversation')->middleware('can:data.close_conversation');
            Route::get('/reasing/conversation/{id}', 'getCustomers')->middleware('can:data.reasing_conversation');
            Route::post('/remenber/create', 'addSaveRemenber')->middleware('can:data.reminder');
            Route::delete('/remenber/update/{id}', 'addUpdateRemenber')->middleware('can:data.reminder');
            Route::get('/unread-customer-messages/{conversationId}', 'getUnreadCustomerMessages');
            Route::get('/template', 'getTemplate');
            Route::get('/quickly_answers/list/{userId}', 'getRequestFast');
            Route::post('/asigntag/{tagId}/{assigId}', 'asignTagCategory')->middleware('can:data.add_tag');            
        })->middleware('can:data');

        //Reset Password
        Route::controller(ForgotPasswordController::class)->prefix('resets')->group(function () {
            Route::post('forgot-password', 'sendResetLink');
        });


        
        // ChatBoot Controller
        Route::controller(ChatBootController::class)->prefix('chatboot')->group(function () {

            // Webhooks
            Route::post('init', 'guardarSeleccionRealInit');
            Route::post('webhook', 'guardarSeleccionWebhook');
            Route::post('webhook-titulacion', 'guardarSeleccionWebhookTitulacion');
            Route::post('webhook-titulacion-response', 'guardarSeleccionWebhookTitulacionResponse');

            Route::post('webhook-level-2', 'guardarSeleccionWebhookLevelTwo');
            Route::post('webhook-encuesta', 'guardarSeleccionWebhookEncuesta');


            Route::post('guardar-seleccion', 'guardarSeleccion');
            Route::post('init', 'guardarSeleccionRealInit');
            Route::post('/guardar-seleccion-opea',  'guardarSeleccionOPEA');
            Route::post('/guardar-seleccion-dtrtycr',  'guardarSeleccionDTRTYCR');
            Route::post('/guardar-seleccion-ddca', 'guardarSeleccionDDCA');
            Route::post('/guardar-seleccion-doa', 'guardarSeleccionDOA'); 
            Route::post('/guardar-seleccion-mesa-de-partes', 'guardarSeleccionMesaDePartes');
            Route::post('/guardar-seleccion-oficina-doa-administrativa', 'guardarSeleccionAdministrativeManagement');
        });


        Route::controller(WspController::class)->prefix('wsp')->group(function () {
            Route::post('/send_message', 'sendMessage');
        });

        // Vision  General
        Route::controller(DashboardController::class)->prefix('dashboard')->group(function () {
            Route::get('/wait-times', 'getWaitTimes');
            Route::get('/messages-count/{periodo}', 'getMessagesData');
            Route::get('/live-activities', 'getActyvitiesInLive');
            Route::get('/assignments-info', 'getTable');
            Route::get('/notifications', 'getNotifiaciones');
            Route::get('/count-all', 'getAssignmentCount');
            Route::get('/listado-json', 'listContactWaitTimes');
            Route::get('/calculate-average-wait-time', 'calculateAverageWaitTimeInLast24Hours');
        });

        Route::controller(ContactWaitTimeController::class)->prefix('contact-wait')->group(function () {
            Route::get('/calculate-average-wait-time', 'calculateAverageWaitTimeInLast24Hours');
        });

        // Informes
        Route::controller(ReportsController::class)->prefix('reports')->group(function () {
            Route::get('week', 'week');
            Route::get('month', 'month');
            Route::post('personalized', 'personalized');
        });

        // Mensajes Masivos
        Route::controller(MassMessagesController::class)->prefix('mass_messages')->group(function () {
            Route::get('/mass_messages', 'getMessages');
            Route::get('/details_messages/{id}', 'detailMessages');
            Route::post('/envio-masivo', 'sendMassMessages');
            Route::post('/date_range', 'filterCustomer');
        });

        Route::controller(ImageController::class)->prefix('image')->group(function () {
            Route::post('/save_image', 'store');
        });
    });

   

    Route::controller(ImageController::class)->prefix('image')->group(function () {
        Route::get('/files/{destination}/{filename}', 'getFile');
    });
});



//Envia mensajes
Route::post('/envia', [WspController::class, 'envia']);

Route::post('/send-api-request', [WspController::class, 'sendApiRequest']);

//Verifica el webhook
Route::get('/webhook', [WspController::class, 'verifyWebhook']);

//Procesa los mensajes enviados por el chatbot y usuario al chatbot
Route::post('/webhook', [WspController::class, 'webhook']);

//Procesa los mensajes enviados por el chatbot y usuario al chatbot
// Route::get('/webhook', [WspController::class, 'sendInitialMessage']);
