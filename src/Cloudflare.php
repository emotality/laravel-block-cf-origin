<?php

namespace Emotality\Cloudflare;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Cloudflare
{
    private static array $config = [
        'enabled'      => true,
        'debug'        => false,
        'environments' => ['production'],
        'cache_days'   => 60,
        'exception'    => [
            'status_code' => 403,
            'message'     => 'Accessing the server directly is forbidden!',
        ],
    ];

    /**
     * Get this package's config.
     */
    public static function config(?string $key = null, mixed $default = null): mixed
    {
        $config = Config::get('cloudflare-block', self::$config);

        return $key ? ($config[$key] ?? $default ?? self::$config[$key] ?? null) : $config;
    }

    /**
     * Get cached Cloudflare netmasks if available, download if not.
     */
    public static function getNetmasks(): array
    {
        return self::cache()->get('cloudflare:netmasks') ?? self::downloadNetmasks();
    }

    /**
     * Download Cloudflare's netmasks.
     */
    public static function downloadNetmasks(): array
    {
        $netmasks = [];

        $urls = [
            'https://www.cloudflare.com/ips-v4',
            'https://www.cloudflare.com/ips-v6',
        ];

        try {
            foreach ($urls as $url) {
                $cidr_text = file_get_contents($url);
                $cidr_list = explode("\n", $cidr_text);

                foreach ($cidr_list as $cidr) {
                    if (! empty($netmask = trim($cidr))) {
                        $netmasks[] = $netmask;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error('Failed to download Cloudflare netmasks from www.cloudflare.com/ips, using offline netmasks that might be outdated.');

            $netmasks = self::offlineNetmasks();
        }

        if (count($netmasks)) {
            self::flushCachedResultsIfNetmasksOutdated($netmasks);
            self::cache()->put('cloudflare:netmasks', $netmasks);
        }

        return $netmasks;
    }

    /**
     * Netmasks as from Sep 28, 2023.
     */
    public static function offlineNetmasks(): array
    {
        return [
            '173.245.48.0/20', // NA
            '103.21.244.0/22', // NA
            '103.22.200.0/22', // AS
            '103.31.4.0/22', // NA
            '141.101.64.0/18', // EU
            '108.162.192.0/18', // NA
            '190.93.240.0/20', // NA
            '188.114.96.0/20', // NA
            '197.234.240.0/22', // AF
            '198.41.128.0/17', // NA
            '162.158.0.0/15', // OC (AU)
            '104.16.0.0/13', // NA
            '104.24.0.0/14', // NA
            '172.64.0.0/13', // EU
            '131.0.72.0/22', // NA
            '2400:cb00::/32', // NA
            '2606:4700::/32', // NA
            '2803:f800::/32', // NA
            '2405:b500::/32', // NA
            '2405:8100::/32', // NA
            '2a06:98c0::/29', // EU
            '2c0f:f248::/32', // AF
        ];
    }

    /**
     * Cache driver with tags.
     */
    private static function cache(): \Illuminate\Cache\TaggedCache
    {
        return Cache::driver('redis')->tags(['cloudflare']);
    }

    /**
     * Flush all Cloudflare cache.
     */
    public static function flushAllCache(): bool
    {
        return self::cache()->flush();
    }

    /**
     * Flush cached IP results if Cloudflare updated their IP ranges.
     *
     * @see https://www.cloudflare.com/ips/
     */
    private static function flushCachedResultsIfNetmasksOutdated(array $new_netmasks): void
    {
        $old_netmasks = self::cache()->get('cloudflare:netmasks', []);

        if (count($old_netmasks) && sha1(json_encode($old_netmasks)) !== sha1(json_encode($new_netmasks))) {
            IP::ipCache()->flush();
        }
    }
}
