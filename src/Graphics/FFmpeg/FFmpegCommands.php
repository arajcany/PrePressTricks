<?php

namespace arajcany\PrePressTricks\Graphics\FFmpeg;

use arajcany\PrePressTricks\Graphics\Common\BaseCommands;
use arajcany\ToolBox\Utility\TextFormatter;
use Throwable;

class FFmpegCommands extends BaseCommands
{
    private ?string $ffmpegPath = null;
    private ?string $ffprobePath = null;

    private array $analysisCache = [];

    public function __construct($ffPath = null)
    {
        parent::__construct();

        $basePath = '';

        if ($ffPath) {
            if (is_dir($ffPath)) {
                $basePath = TextFormatter::makeDirectoryTrailingSmartSlash($ffPath);
            } elseif (is_file($ffPath)) {
                $basePath = TextFormatter::makeDirectoryTrailingSmartSlash(pathinfo($ffPath, PATHINFO_DIRNAME));
            }
        } else {
            exec('where ffprobe', $output);
            if (!empty($output[0]) && is_file($output[0])) {
                $basePath = TextFormatter::makeDirectoryTrailingSmartSlash(pathinfo($output[0], PATHINFO_DIRNAME));
            }
        }

        $ffmpegPath = "{$basePath}ffmpeg.exe";
        $ffprobePath = "{$basePath}ffprobe.exe";

        if (is_file($ffmpegPath) && is_file($ffprobePath)) {
            $this->ffmpegPath = $ffmpegPath;
            $this->ffprobePath = $ffprobePath;
        }
    }

    public function getFFMpeg(): ?string
    {
        return $this->ffmpegPath;
    }

    public function getFFProbe(): ?string
    {
        return $this->ffprobePath;
    }

    public function isAlive(): bool
    {
        try {
            return (bool)$this->getCliVersion();
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getCliVersion(): mixed
    {
        $version = $this->cli('-version');
        if (!isset($version[0])) {
            return false;
        }

        // Match the version pattern after "ffmpeg version" and before " Copyright"
        if (preg_match('/^ffmpeg version ([^\s]+) /', $version[0], $matches)) {
            return $matches[1];
        }

        return false;
    }


    private function cli(string $cliCommand): array|false
    {
        if (!$this->ffmpegPath || !is_file($this->ffmpegPath)) {
            return false;
        }

        $cmd = "\"{$this->ffmpegPath}\" {$cliCommand}";
        exec($cmd, $out, $ret);
        return $ret === 0 && !empty($out) ? $out : false;
    }

    public function videoFramerate(string $videoClipPath): float|false
    {
        $val = $this->videoAnalyse($videoClipPath)['frame_rate'] ?? false;
        return $val ? floatval($val) : false;
    }

    public function videoWidth(string $videoClipPath): int|false
    {
        $val = $this->videoAnalyse($videoClipPath)['width'] ?? false;
        return $val ? intval($val) : false;
    }

    public function videoHeight(string $videoClipPath): int|false
    {
        $val = $this->videoAnalyse($videoClipPath)['height'] ?? false;
        return $val ? intval($val) : false;
    }

    public function videoDuration(string $videoClipPath): float|false
    {
        $val = $this->videoAnalyse($videoClipPath)['duration'] ?? false;
        return $val ? floatval($val) : false;
    }

    public function videoThumbnail(string $videoClipPath, string $thumbnailOutput, float|int $timePosition = 0): bool
    {
        $analysis = $this->videoAnalyse($videoClipPath);
        if (!$analysis) {
            return false;
        }

        $duration = $analysis['summary']['duration'] ?? 0;
        $lastFrameTimePosition = floor($duration);
        $timePosition = min($lastFrameTimePosition, $timePosition);

        // Escape paths to ensure Windows backslashes are handled
        $videoClipPath = str_replace('/', DIRECTORY_SEPARATOR, $videoClipPath);
        $thumbnailOutput = str_replace('/', DIRECTORY_SEPARATOR, $thumbnailOutput);

        $cmd = "\"{$this->ffmpegPath}\" -loglevel error -hide_banner -y -ss {$timePosition} -i \"{$videoClipPath}\" -frames:v 1 \"{$thumbnailOutput}\"";

        exec($cmd, $out, $ret);

        return $ret === 0 && is_file($thumbnailOutput);
    }


    public function videoAnalyse(string $videoClipPath): array|false
    {
        if (!is_file($videoClipPath)) {
            return false;
        }

        $cacheKey = sha1($videoClipPath);
        if (isset($this->analysisCache[$cacheKey])) {
            return $this->analysisCache[$cacheKey];
        }

        $cmd = "\"{$this->ffprobePath}\" -v quiet -print_format json -show_format -show_streams \"{$videoClipPath}\"";
        exec($cmd, $output, $ret);

        if ($ret !== 0 || empty($output)) {
            return false;
        }

        $json = json_decode(implode('', $output), true);

        if (!is_array($json)) {
            return false;
        }

        $streams = $json['streams'] ?? [];
        $format = $json['format'] ?? [];

        $videoStream = null;
        foreach ($streams as $stream) {
            if (isset($stream['codec_type']) && $stream['codec_type'] === 'video') {
                $videoStream = $stream;
                break;
            }
        }

        if (!$videoStream) {
            return false;
        }

        $width = $videoStream['width'] ?? null;
        $height = $videoStream['height'] ?? null;
        $frameRateRaw = $videoStream['r_frame_rate'] ?? '0/1';
        [$num, $den] = explode('/', $frameRateRaw);
        $frameRate = ($den != 0) ? floatval($num) / floatval($den) : 0.0;
        $duration = isset($format['duration']) ? floatval($format['duration']) : 0.0;

        $analysis = [
            'width' => $width,
            'height' => $height,
            'frame_rate' => $frameRate,
            'duration' => $duration,
        ];

        $format['start_time'] = floatval($format['start_time']);
        $format['size'] = intval($format['size']);
        $format['bit_rate'] = intval($format['bit_rate']);

        $analysis =[
            'summary' => $analysis,
            'format' => $format,
            'streams' => $streams,
        ];

        $this->analysisCache[$cacheKey] = $analysis;

        return $analysis;
    }
}
