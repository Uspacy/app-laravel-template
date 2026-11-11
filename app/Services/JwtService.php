<?php

namespace App\Services;

use App\Exceptions\JwtDecodeException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private const ALGORITHM = 'HS256';

    /**
     * @return array{headers: array<string, mixed>, claims: array<string, mixed>}
     *
     * @throws JwtDecodeException
     */
    public function decode(string $token, #[\SensitiveParameter] string $secret): array
    {
        try {
            $headers = new \stdClass;
            $claims = JWT::decode(
                $token,
                new Key($secret, self::ALGORITHM),
                $headers,
            );

            return [
                'headers' => (array) $headers,
                'claims' => (array) $claims,
            ];
        } catch (\Exception $ex) {
            throw new JwtDecodeException(
                code: $ex->getCode(),
                previous: $ex,
            );
        }
    }
}
