<?php

namespace Emotality\Cloudflare;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class BlockNonCloudflareRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next)
    {
        $config = Cloudflare::config();

        if ($config['enabled'] && App::environment($config['environments'])) {
            if (! IP::isFromCloudflare($request->ip())) {
                $exception = $config['exception'];

                if ($config['debug']) {
                    Log::debug(sprintf('Cloudflare: IP %s was blocked from accessing your site directly.', $request->ip()));
                }

                throw new CloudflareBlockException(
                    status: $exception['status_code'],
                    message: $exception['message'],
                    headers: $request->headers->all()
                );
            }
        }

        return $next($request);
    }
}
