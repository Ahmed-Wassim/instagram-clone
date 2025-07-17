<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheStats extends Command
{
    protected $signature = 'cache:stats';
    protected $description = 'Show cache statistics';

    public function handle()
    {
        $redis = Redis::connection();

        $info = $redis->info();

        $this->info('Redis Cache Statistics:');
        $this->line('Connected clients: ' . $info['connected_clients']);
        $this->line('Used memory: ' . $info['used_memory_human']);
        $this->line('Total keys: ' . $redis->dbsize());

        // Show some cached keys
        $keys = $redis->keys('*');
        $this->line('Cached keys: ' . count($keys));

        if (count($keys) > 0) {
            $this->info('Sample cached keys:');
            foreach (array_slice($keys, 0, 10) as $key) {
                $this->line('- ' . $key);
            }
        }
    }
}
