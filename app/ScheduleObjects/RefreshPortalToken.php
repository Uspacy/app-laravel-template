<?php

namespace App\ScheduleObjects;

use App\Models\Portal;
use App\Services\External\UserService as ExternalUserService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RefreshPortalToken
{
    public function __invoke()
    {
        $externalUserService = new ExternalUserService();

        $filterTime = Carbon::now()->timestamp + config('portal.rerfesh_token_schedule');

        $portals = Portal::where('expiry_date', '<=', $filterTime)->get();

        $appName = config('api.code');

        foreach ($portals as $portal) {
            $portalDomain = $portal->domain;

            if (!$portal->is_active) {
                continue;
            }

            try {
                $tokenData = $externalUserService->refreshToken($portal);

                $portal->token = $tokenData->jwt;
                $portal->refresh_token = $tokenData->refreshToken;
                $portal->expiry_date = Carbon::now()->timestamp + $tokenData->expireInSeconds;
                $portal->failed_token_refresh_attempts = 0;
                $portal->save();
            }catch (RequestException $requestException) {
                $portal->failed_token_refresh_attempts++;
                if ($portal->failed_token_refresh_attempts >= 5) {
                    $portal->is_active = false;
                }

                $portal->save();

                Log::warning("App@{$appName} refresh token for portal {$portalDomain}:" . $requestException->getMessage());
            } catch (\Throwable $th) {
                Log::error("App@{$appName} refresh token for portal {$portalDomain}:" . $th->getMessage());
            }
        }
    }
}
