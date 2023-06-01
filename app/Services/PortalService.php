<?php

namespace App\Services;

use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\Portal\InstallPortalRequest;
use App\Models\Portal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PortalService extends BaseService
{
    /**
     * @param InstallPortalRequest $request
     * @return JsonResponse
     */
    public function install(InstallPortalRequest $request): JsonResponse
    {
        $fields = $request->only(['token', 'refresh_token', 'expiry_date']);
        $fields['domain'] = $request->attributes->get('domain');

        Portal::create($fields);

        return $this->onSuccess();
    }

    /**
     * @param BaseFormRequest $request
     * @return JsonResponse
     */
    public function uninstall(BaseFormRequest $request): JsonResponse
    {
        $portal = $request->attributes->get('portal');

        if ($portal?->delete()) {
            return $this->onSuccess('success', Response::HTTP_NO_CONTENT);
        }

        return $this->onError();
    }
}
