<?php

namespace Emotality\Cloudflare;

use Illuminate\Console\Command;

class CloudflareNetmasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:netmasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Cloudflare Edge IP ranges';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Cloudflare::downloadNetmasks();
    }
}
