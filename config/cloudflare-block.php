<?php

return [

    /*
     * Will only block if this option is true.
     */
    'enabled' => (bool) env('CF_BLOCK_ENABLED', true),

    /*
     * Will only log on blocks using Log::debug().
     */
    'debug' => (bool) env('CF_BLOCK_DEBUG', false),

    /*
     * Will only block in these environments.
     */
    'environments' => ['production', 'staging'],

    /*
     * The FastCGI param that will be used to fetch the Cloudflare
     * Edge (proxy) IP.
     * This value will be fetched from $_SERVER['CF_EDGE_IP'].
     * Nginx:   fastcgi_param CF_EDGE_IP $realip_remote_addr;
     */
    'fastcgi_param' => 'CF_EDGE_IP',

    /*
     * The HTTP exception's status code and message that should be thrown.
     */
    'exception' => [
        'status_code' => 403,
        'message' => 'Accessing the server directly is forbidden!',
    ],

    /*
     * Amount of days to cache IP results (if IP is from CF or not).
     * Cached IP results will automatically be flushed if Cloudflare
     * updated their IP ranges at https://www.cloudflare.com/ips/.
     */
    'cache_days' => 60,

];
