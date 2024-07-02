<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Response;
use App\Http\Resources\DocumentCollection;

class DocumentController extends Controller
{
    public function index () {
        $users = Document::get();
        $data = new DocumentCollection($users);
        
        return response()->json([
            'status' => 'success',
            'users' => $data
        ], Response::HTTP_OK);
    }
}
