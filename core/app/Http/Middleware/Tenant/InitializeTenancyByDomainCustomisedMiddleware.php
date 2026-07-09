<?php
declare(strict_types=1);
namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByRequestDataException;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\IdentificationMiddleware;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;
use Stancl\Tenancy\Database\Models\Domain;

class InitializeTenancyByDomainCustomisedMiddleware extends IdentificationMiddleware
{
    /** @var callable|null */
    public static $onFail;

    /** @var Tenancy */
    protected $tenancy;

    /** @var DomainTenantResolver */
    protected $resolver;

    public function __construct(Tenancy $tenancy, DomainTenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $host = $this->normalizeHost($request->getHost());

        if (in_array($host, config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        if (! Domain::query()->where('domain', $host)->exists()) {
            return $this->handleUnknownDomain($request, $next);
        }

        try {
            return $this->initializeTenancy($request, $next, $host);
        } catch (\Throwable $e) {
            return $this->handleUnknownDomain($request, $next, $e);
        }
    }

    private function normalizeHost(string $host): string
    {
        $parts = explode('.', $host);

        if (current($parts) === 'www') {
            return substr(implode('.', $parts), 4);
        }

        return $host;
    }

    private function handleUnknownDomain(Request $request, Closure $next, ?\Throwable $exception = null)
    {
        if (is_callable(static::$onFail)) {
            return (static::$onFail)(
                $exception ?? new TenantCouldNotBeIdentifiedOnDomainException($request->getHost()),
                $request,
                $next
            );
        }

        return redirect()->route('landlord.homepage');
    }
}
