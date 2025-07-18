<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteExpiredStories extends Command
{
    protected $signature = 'stories:clean';
    protected $description = 'Delete stories older than 24 hours';

    public function handle()
    {
        $expired = Story::where('created_at', '<', now()->subDay())->get();

        foreach ($expired as $story) {
            Storage::disk('public')->delete($story->media);
            $story->delete();
        }

        $this->info('Expired stories cleaned.');
    }
}
