<?php

namespace Emotality\Cloudflare;

use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class IP
{
    /**
     * Check if IP is in one of Cloudflare's netmasks.
     */
    public static function isFromCloudflare(string $ip): bool
    {
        $cache_key = sprintf('cloudflare:ips:%s', md5($ip));

        if (($result = self::cache()->get($cache_key)) !== null) {
            return $result;
        }

        $ttl = Carbon::now()->addDays(
            Cloudflare::config('cache_days', 60)
        );

        return self::cache()->remember($cache_key, $ttl, function () use ($ip) {
            $netmasks = Cloudflare::getNetmasks();

            foreach ($netmasks as $cidr) {
                $result = substr_count($ip, ':') > 1
                    ? self::checkIPv6($ip, $cidr)
                    : self::checkIPv4($ip, $cidr);

                if ($result) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Check if IPv4 is in a netmask (CIDR notation).
     */
    public static function checkIPv4(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return false;
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        [$address, $netmask] = explode('/', $cidr, 2);

        if ($netmask === '0') {
            return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        }

        if ($netmask < 0 || $netmask > 32) {
            return false;
        }

        if (ip2long($address) === false) {
            return false;
        }

        return substr_compare(sprintf('%032b', ip2long($ip)), sprintf('%032b', ip2long($address)), 0, $netmask) === 0;
    }

    /**
     * Check if IPv6 is in a netmask (CIDR notation).
     */
    public static function checkIPv6(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return false;
        }

        if (! ((extension_loaded('sockets') && defined('AF_INET6')) || @inet_pton('::1'))) {
            throw new \RuntimeException('Unable to check IPv6. Check that PHP was not compiled with option "disable-ipv6".');
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }

        [$address, $netmask] = explode('/', $cidr, 2);

        if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }

        if ($netmask === '0') {
            return (bool) unpack('n*', @inet_pton($address));
        }

        if ($netmask < 1 || $netmask > 128) {
            return false;
        }

        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($ip));

        if (! $bytesAddr || ! $bytesTest) {
            return false;
        }

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; $i++) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xFFFF >> $left) & 0xFFFF;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cache driver with all tags.
     */
    public static function cache(): TaggedCache
    {
        return Cache::driver('redis')->tags(['cloudflare', 'cloudflare_ips']);
    }

    /**
     * Cache driver with IP tags.
     */
    public static function ipCache(): TaggedCache
    {
        return Cache::driver('redis')->tags(['cloudflare_ips']);
    }
}
