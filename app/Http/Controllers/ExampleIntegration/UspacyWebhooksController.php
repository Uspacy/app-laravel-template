<?php

namespace App\Http\Controllers\ExampleIntegration;

use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Support\PortalContext;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UspacyWebhooksController extends Controller
{
    public function __construct(
        private readonly PortalContext $portalContext
    ) {}

    public function handle(string $integration): Response
    {
        $integration = config('uspacy.integrations')[$integration] ?? null;

        if (! $integration) {
            \abort(404, 'Integration not found');
        }

        // SNS sends JSON with Content-Type: text/plain, so $request->json() stays empty.
        $message = json_decode(request()->getContent(), true);

        if (! is_array($message)) {
            Log::warning('Invalid SNS message', ['content' => request()->getContent()]);

            return response()->noContent(200);
        }

        $portalDomain = $message['data']['domain'] ?? null;
        $portal = Portal::whereDomain($portalDomain)
            ->whereIntegrationCode($integration['integration_code'])
            ->first();

        if (! $portal) {
            Log::warning('Portal not found for SNS message', ['domain' => $portalDomain]);

            return response()->noContent(200); // Prevent retrying the message
        }

        $this->portalContext->set($portal);

        Log::info('SNS message received', $message);

        return response()->noContent(200);
    }
}
