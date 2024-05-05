# Block non-Cloudflare requests in Laravel

<p>
    <a href="https://packagist.org/packages/emotality/laravel-block-cf-origin"><img src="https://img.shields.io/packagist/l/emotality/laravel-block-cf-origin" alt="License"></a>
    <a href="https://packagist.org/packages/emotality/laravel-block-cf-origin"><img src="https://img.shields.io/packagist/v/emotality/laravel-block-cf-origin" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/emotality/laravel-block-cf-origin"><img src="https://img.shields.io/packagist/dt/emotality/laravel-block-cf-origin" alt="Total Downloads"></a>
</p>

Laravel package to block direct requests to your Cloudlfare-protected origin server.

<p>
    <a href="https://www.cloudflare.com" target="_blank">
        <img src="https://raw.githubusercontent.com/emotality/files/master/GitHub/Cloudflare.png" height="50">
    </a>
</p>

## Overview

This packages should only be used when the following applies:
1. You can't add firewall rules to only accept requests from Edge IP addresses because your server is shared with other projects that don't use Cloudflare. If you have a single app running on your server, rather add firewall rules.
2. You can't add deny/allow rules to your nginx/Apache config because you are using the `set_real_ip_from` / `mod_remoteip` module to forward the user's real IP _(X-Forwarded-For)_. If you don't need to forward the user's real IP, rather add deny/allow rules to your nginx/Apache config.

_See [Useful Links](#useful-links) section below for more information._

## Requirements

- PHP 8.0+
- PHP Redis extension
- Laravel 9.0+

_*Note: This package only supports the Redis cache driver!_

## Installation

1. `composer require emotality/laravel-block-cf-origin`
2. `php artisan vendor:publish --provider="Emotality\Cloudflare\CloudflareBlockOriginServiceProvider"`
3. Add the middleware in `app/Http/Kernel.php`:

```php
protected $middleware = [
    \Emotality\Cloudflare\BlockNonCloudflareRequests::class,
    ...
];
```
4. Add the cronjob to update Cloudflare's netmasks:

```php
protected function schedule(Schedule $schedule): void
{
    ...
    $schedule->call(new \Emotality\Cloudflare\GetNetmasks)->weekly();
}
```
5. Update your `config/cloudflare-block.php` config and `.env` accordingly.

## Useful Links

- [Cloudflare IP ranges](https://www.cloudflare.com/en-gb/ips/)
- [Cloudflare IP addresses](https://developers.cloudflare.com/fundamentals/concepts/cloudflare-ip-addresses/)
- [Protect your origin server](https://developers.cloudflare.com/fundamentals/basic-tasks/protect-your-origin-server/)
- [Authenticated Origin Pulls](https://developers.cloudflare.com/ssl/origin-configuration/authenticated-origin-pull/)
- [Restoring original visitor IPs](https://developers.cloudflare.com/support/troubleshooting/restoring-visitor-ips/restoring-original-visitor-ips/)

## Contributing

This package is in its early stages, feel free to report any issues or suggest improvements. Please do pull requests to the `master` branch.

## License

laravel-block-cf-origin is released under the MIT license. See [LICENSE](https://github.com/emotality/laravel-block-cf-origin/blob/master/LICENSE) for details.
