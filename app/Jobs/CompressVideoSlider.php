<?php

namespace App\Jobs;

use App\Models\Slider;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompressVideoSlider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Slider $slider,
        public string $rawPath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $rawFullPath = Storage::disk('public')->path($this->rawPath);

        if (!file_exists($rawFullPath)) return;

        $outputFilename = Str::uuid() . '.mp4';
        $outputRelative = 'sliders/' . $outputFilename;
        $outputFullPath = Storage::disk('public')->path($outputRelative);

        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => env('FFMPEG_PATH',  '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
                'timeout'          => 3600,
                'ffmpeg.threads'   => 2,
            ]);

            $video  = $ffmpeg->open($rawFullPath);
            $format = new X264('aac', 'libx264');
            $format
                ->setKiloBitrate(800)
                ->setAudioKiloBitrate(128);

            $video->save($format, $outputFullPath);

            $this->slider->update([
                'file_path' => $outputRelative,
                'is_processing' => false,
            ]);
        } catch (\Throwable $e) {
            Log::error('Video compression failed for slider ' . $this->slider->id . ': ' . $e->getMessage());

            // Gagal kompresi — tetap pakai file raw, tandai selesai
            $this->slider->update(['is_processing' => false]);
        } finally {
            Storage::disk('public')->delete($this->rawPath);
        }
    }
}
