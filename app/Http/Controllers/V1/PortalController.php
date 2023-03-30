<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\InstallPortalRequest;
use App\Services\PortalService;
use Illuminate\Http\JsonResponse;

class PortalController extends Controller
{
    /**
     * Create a new UserController instance.
     * @return void
     */
    public function __construct(public PortalService $service)
    {
    }

    /**
     * @param InstallPortalRequest $request
     * @return JsonResponse
     */
    public function install(InstallPortalRequest $request): JsonResponse
    {
        return $this->service->install($request);
    }
}
