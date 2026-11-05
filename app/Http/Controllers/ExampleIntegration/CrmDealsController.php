<?php

namespace App\Http\Controllers\ExampleIntegration;

use App\Factories\UspacySDKFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Uspacy\SDK\DTOs\Crm\EntityDTO;

class CrmDealsController extends Controller
{
    public function index(UspacySDKFactory $sdkFactory): JsonResponse
    {
        $deals = $sdkFactory->fromPortal(\portal())
            ->crmDeals()
            ->getEntities(['page' => 1, 'list' => 10]);

        return response()->json([
            'data' => array_map(fn (EntityDTO $deal) => $deal->raw, $deals->data),
            'meta' => $deals->meta->raw,
        ]);
    }
}
