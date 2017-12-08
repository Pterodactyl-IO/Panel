<?php

namespace Pterodactyl\Console\Commands\Cloud;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\DatabaseHost;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Location;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Pack;
use Pterodactyl\Models\Schedule;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Subuser;
use Pterodactyl\Models\User;

class UsagePingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p:cloud:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a usage ping';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @param \GuzzleHttp\Client    $client
     */
    public function __construct(GuzzleClient $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->client->request('POST', 'https://pterodactyl.cloud/api/usageData/panel', [
                'timeout' => config('pterodactyl.guzzle.timeout'),
                'connect_timeout' => config('pterodactyl.guzzle.connect_timeout'),
                'json' => [
                    'uuid' => config('pterodactyl.cloud.uuid'),
                    'hostname' => config('app.url'),
                    'appAuthor' => config('pterodactyl.service.author'),
                    'version' => config('app.version'),
                    'features' => [
                        'newDaemon' => config('pterodactyl.daemon.use_new_daemon'),
                        'recaptcha' => config('recaptcha.enabled'),
                        'theme' => config('themes.active'),
                    ],
                    'usage' => [
                        'databases' => Database::count(),
                        'database_hosts' => DatabaseHost::count(),
                        'eggs' => Egg::count(),
                        'locations' => Location::count(),
                        'nests' => Nest::count(),
                        'nodes' => Node::count(),
                        'packs' => Pack::count(),
                        'schedules' => Schedule::count(),
                        'servers' => Server::count(),
                        'users' => User::count(),
                        'subusers' => Subuser::count(),
                    ],
                    'drivers' => [
                        'cacheDriver' => config('cache.default'),
                        'sessionDriver' => config('session.driver'),
                        'mailDriver' => config('mail.driver'),
                        'queueDriver' => config('queue.default'),
                    ],
                ],
            ]);
        } catch (RequestException $e) {
            // do nothing if this fails
        }

    }
}