<?php

namespace App\Jobs;

use App\Models\Story;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessStoryVideo implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $userId;
    protected $tempPath;

    public function __construct($userId, $tempPath)
    {
        $this->userId = $userId;
        $this->tempPath = $tempPath;
    }

    public function handle()
    {
        $filename = basename($this->tempPath);
        $finalPath = "stories/{$filename}";

        Storage::disk('public')->move($this->tempPath, $finalPath);

        Story::create([
            'user_id' => $this->userId,
            'media' => $finalPath,
            'type' => 'video',
        ]);
    }
}
