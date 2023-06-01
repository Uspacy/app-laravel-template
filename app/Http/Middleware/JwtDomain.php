<?php

namespace App\Http\Middleware;

use App\Models\Portal;
use App\Trait\HelperTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;

class JwtDomain
{
    use HelperTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $jwt = $request->bearerToken();
        
        // TODO - refactor
        $domain = $this->getDomainFromJwt($jwt, config('app.client_secret'));

        if (empty($domain)) {
            $this->onError(__('portal.empty_portal_domain'));
        }

        $portal = Portal::where('domain', $domain)->first();

        if ($request->route()->named('portals_install')) {
            if ($portal) {
                $this->onError(__('portal.installed'));
            }

            $request->attributes->add([
                'domain' => $domain
            ]);

            return $next($request);
        }

        if (!$portal) {
            $this->onError(__('portal.not_installed'));
        }
    
        $request->attributes->add([
            'domain' => $domain,
            'portal' => $portal,
        ]);

        return $next($request);
    }

    /**
     * @param string $jwt
     * @return string|null
     */
    protected function getDomainFromJwt(string $jwt, string $clientSecret): ?string
    {
        $signingKey = InMemory::plainText($clientSecret);

        try {
            $parsedToken = (new JwtFacade())->parse(
                $jwt,
                new Constraint\SignedWith(new Sha256(), $signingKey),
                new Constraint\StrictValidAt(
                    new FrozenClock(new \DateTimeImmutable())
                )
            );

            $domain = $parsedToken->headers()->get('domain');
        }
        catch (\Throwable $th) {
            $appName = config('api.code');

            Log::error(
                "App@{$appName} parse token for portal:" . $th->getMessage(), 
                ['jwt' => $jwt]
            );

            try {
                $parser = new Parser(new JoseEncoder());
                $parsedToken = $parser->parse($jwt);
                $domain = $parsedToken->claims()->get('domain');
            }
            catch (\Throwable $th) {
                $this->onError(__('portal.invalid_credentials'));
            }
        }

        return $domain;
    }
}
