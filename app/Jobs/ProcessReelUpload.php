<?php

namespace App\Jobs;

use App\Models\Reel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessReelUpload implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $path;
    protected $caption;

    public function __construct($userId, $path, $caption = null)
    {
        $this->userId = $userId;
        $this->path = $path;
        $this->caption = $caption;
    }

    public function handle()
    {
        $source = $this->path;
        $filename = basename($source);
        $destination = 'reels/' . $filename;

        Storage::disk('public')->move($source, $destination);

        Reel::create([
            'user_id' => $this->userId,
            'video' => $destination,
            'caption' => $this->caption,
        ]);
    }
}
