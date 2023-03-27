<?php

namespace App\ScheduleObjects;

use App\Models\Portal;
use App\Services\External\UserService as ExternalUserService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RefreshPortalToken
{
    public function __invoke()
    {
        $externalUserService = new ExternalUserService();

        $filterTime = Carbon::now()->timestamp + config('portal.rerfesh_token_schedule');

        $portals = Portal::where('expiry_date', '<=', $filterTime)->get();
        
        foreach ($portals as $portal) {
            try {
                $tokenData = $externalUserService->refreshToken($portal);

                $portal->token = $tokenData->token;
                $portal->refresh_token = $tokenData->refresh_token;
                $portal->expiry_date = $tokenData->expiry_date;
                $portal->save();
            }
            catch (\Throwable $th) {
                $appName = config('api.code');
                $portalDomain = $portal->domain;
                Log::error("App@{$appName} refresh token for portal {$portalDomain}:" . $th->getMessage());
            }
        }
    }
}
