<?php

namespace Emotality\Cloudflare;

use Illuminate\Support\Facades\Artisan;

class GetNetmasks
{
    public function __invoke()
    {
        Artisan::call('cloudflare:netmasks');
    }
}
