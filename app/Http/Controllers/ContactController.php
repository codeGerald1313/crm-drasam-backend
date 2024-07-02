<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Response;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactCollection;

class ContactController extends Controller
{

    public function index()
    {
        try {
            $contact = Contact::get();
            $data = new ContactCollection($contact);

            return response()->json([
                'status' => 'success',
                'message' => 'Contactos recuperados con éxito',
                'data' => $data,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Error al recuperar contactos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(ContactRequest $request, $id = null)
    {
        try {
            $data = $request->validated();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Contacto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            /* if ($data['country_code'] !== '+1') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Codigo de pais invalido'
                ], Response::HTTP_BAD_REQUEST);
            } */ 

            $id = $request->input('id');

            $contact = Contact::firstOrNew(['id' => $id]);

            $contact->name = $request->input('name');
            $contact->num_phone = $request->input('num_phone');
            $contact->student = 0;
            // $contact->document = $request->input('document');
            // $contact->document_id = $request->input('document_id');
            // $contact->birthdate = $request->input('birthdate');
            // $contact->email = $request->input('email');
            // $contact->country_code = $request->input('country_code');

            $contact->save();

            if ($id) {
                $contact->makeHidden(['remember_token']);
            }

            return response()->json([
                'status' => 'success',
                'message' => $id ? 'Contacto actualizado' : 'Contacto registrado',
                'data' => $contact
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $id ? 'Error al actualizar al contacto' : 'Error al registar el contacto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function show($id)
    {

        try {
            $contact = Contact::findOrFail($id);

            if (!$contact) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Contacto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Contacto recuperado con éxito',
                'data' => new ContactResource($contact)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al recuperar el contacto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $contact = Contact::find($id);

            // Cambia el estado del contacto a "borrado"
            $contact->update([
                'status' => 0
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registro eliminado exitosamente',
                'data' => $contact
            ], Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al eliminar el registro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
