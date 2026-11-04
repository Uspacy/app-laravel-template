<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UspacyWebhooksController extends Controller
{
    public function handle(Request $request): Response
    {
        // SNS sends JSON with Content-Type: text/plain, so $request->json() stays empty.
        $message = json_decode($request->getContent(), true);

        if (! is_array($message)) {
            \abort(400, 'Invalid SNS message');
        }

        Log::info('SNS message received', $message);

        return response()->noContent(200);
    }
}
