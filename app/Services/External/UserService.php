<?php

namespace App\Services\External;

use App\Models\Portal;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;

class UserService
{
    private string $apiVersion;
    private int $repeatTimes;
    private int $sleepMilliseconds;
    private int $timeout;
    private int $connectTimeout;

    public function __construct()
    {
        $this->apiVersion = config('portal.api_version');
        $this->repeatTimes = config('portal.repeat_times');
        $this->sleepMilliseconds = config('portal.sleep_milliseconds');
        $this->timeout = config('portal.timeout');
        $this->connectTimeout = config('portal.connect_timeout');
    }

    /**
     * @param object $appCredentials
     * @param array $tokenData
     * @return object|array
     */
    public function refreshToken(Portal $portal)
    {
        $apiVersion = $this->apiVersion;
        $url = 'https://' . $portal->domain .  "/company/{$apiVersion}/apps/refresh_token";

        $response = Http::retry($this->repeatTimes, $this->sleepMilliseconds)
            ->timeout($this->timeout)->connectTimeout($this->connectTimeout)
            ->withToken($portal->token)
            ->post($url);

        $result = $response->object();

        if (empty($result) || empty($result->token)) {
            throw new HttpResponseException(response()->json(
                $response->json(), $response->status())
            );
        }

        return $result;
    }
}
