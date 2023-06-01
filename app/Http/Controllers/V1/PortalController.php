<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\JwtDomain;
use App\Http\Requests\Portal\InstallPortalRequest;
use App\Services\PortalService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\BaseFormRequest;

class PortalController extends Controller
{
    /**
     * Create a new UserController instance.
     * @return void
     */
    public function __construct(public PortalService $service)
    {
        $this->middleware(JwtDomain::class);
    }

    /**
     * @param InstallPortalRequest $request
     * @return JsonResponse
     */
    public function install(InstallPortalRequest $request): JsonResponse
    {
        return $this->service->install($request);
    }

    /**
     * @param BaseFormRequest $request
     * @return JsonResponse
     */
    public function uninstall(BaseFormRequest $request): JsonResponse
    {
        return $this->service->uninstall($request);
    }
}
