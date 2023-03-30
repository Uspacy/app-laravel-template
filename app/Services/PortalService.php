<?php

namespace App\Services;

use App\Http\Requests\Portal\InstallPortalRequest;
use App\Models\Portal;
use Illuminate\Http\JsonResponse;

class PortalService extends BaseService
{
    /**
     * @param InstallPortalRequest $request
     * @return JsonResponse
     */
    public function install(InstallPortalRequest $request): JsonResponse
    {
        $fields = $request->only(['token', 'refresh_token', 'expiry_date', 'domain']);

        Portal::create($fields);

        return $this->onSuccess();
    }
}
