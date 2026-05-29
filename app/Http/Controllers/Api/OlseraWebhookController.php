<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OlseraWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info($request->all());

        return response()->json([
            'success' => true
        ]);
    }
}