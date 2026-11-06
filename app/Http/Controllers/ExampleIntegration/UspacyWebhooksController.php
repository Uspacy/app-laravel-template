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
        $integrationConfig = config('uspacy.integrations')[$integration] ?? null;

        if (! $integrationConfig) {
            Log::warning('Webhook received for unconfigured integration', ['integration' => $integration]);

            return response()->noContent(200);
        }

        // The relay posts the envelope without a Content-Type, so $request->json() stays empty.
        $envelope = json_decode(request()->getContent(), true);
        $event = is_array($envelope) && is_string($envelope['Message'] ?? null)
            ? json_decode($envelope['Message'], true)
            : null;

        if (! is_array($event)) {
            Log::warning('Invalid webhook payload', ['content' => request()->getContent()]);

            return response()->noContent(200);
        }

        $portalDomain = $event['data']['domain'] ?? null;

        if (! $portalDomain) {
            Log::warning('Webhook event missing portal domain', ['event' => $event]);

            return response()->noContent(200);
        }

        $portal = Portal::whereDomain($portalDomain)
            ->whereIntegrationCode($integrationConfig['integration_code'])
            ->first();

        if (! $portal) {
            Log::warning('Portal not found for webhook event', ['domain' => $portalDomain]);

            return response()->noContent(200); // Prevent retrying the message
        }

        $this->portalContext->set($portal);

        Log::info('Webhook event received', ['message_id' => $envelope['MessageId'] ?? null, 'event' => $event]);

        return response()->noContent(200);
    }
}
