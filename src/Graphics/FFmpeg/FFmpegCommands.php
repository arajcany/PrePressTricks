<?php


namespace arajcany\PrePressTricks\Graphics\FFmpeg;


use arajcany\PrePressTricks\Graphics\Common\BaseCommands;
use arajcany\ToolBox\Utility\TextFormatter;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Throwable;

class FFmpegCommands extends BaseCommands
{
    private null|string $ffmpegPath = null;
    private null|FFMpeg $FFMpeg = null;

    private null|string $ffprobePath = null;
    private null|FFProbe $FFProbe = null;

    private array $analysisCache = [];

    /**
     * GhostscriptCommands constructor.
     * @param null $ffPath
     */
    public function __construct($ffPath = null)
    {
        parent::__construct();

        $basePath = '';

        if ($ffPath) {
            if (is_dir($ffPath)) {
                $basePath = $ffPath;
                $basePath = TextFormatter::makeDirectoryTrailingSmartSlash($basePath);
            } elseif (is_file($ffPath)) {
                $basePath = pathinfo($ffPath, PATHINFO_DIRNAME);
                $basePath = TextFormatter::makeDirectoryTrailingSmartSlash($basePath);
            }
        } else {
            $command = "where ffprobe";
            $output = [];
            $return_var = '';
            exec($command, $output, $return_var);
            if (isset($output[0])) {
                if (is_file($output[0])) {
                    $basePath = pathinfo($output[0], PATHINFO_DIRNAME);
                    $basePath = TextFormatter::makeDirectoryTrailingSmartSlash($basePath);
                }
            }
        }

        $ffmpegPath = "{$basePath}ffmpeg.exe";
        $ffprobePath = "{$basePath}ffprobe.exe";

        if (is_file($ffprobePath) && is_file($ffmpegPath)) {
            $this->ffmpegPath = $ffmpegPath;
            $this->ffprobePath = $ffprobePath;

            $options = [
                'ffmpeg.binaries' => $this->ffmpegPath,
                'ffprobe.binaries' => $this->ffprobePath,
            ];
            $this->FFMpeg = FFMpeg::create($options);
            $this->FFProbe = FFProbe::create($options);
        }

    }

    public function getFFMpeg(): FFMpeg|null
    {
        return $this->FFMpeg;
    }

    public function getFFProbe(): FFProbe|null
    {
        return $this->FFProbe;
    }

    /**
     * Check if Callas is alive and working.
     *
     * @return bool
     */
    public function isAlive(): bool
    {
        try {
            $cliVersion = $this->getCliVersion();

            if (!$cliVersion) {
                return false;
            }

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * Get the version string
     *
     * @return false|mixed
     */
    public function getCliVersion(): mixed
    {
        $version = $this->cli("-version");
        if (isset($version[0])) {
            return $version[0];
        } else {
            return false;
        }
    }

    /**
     * Generic function to run a command
     *
     * @param string $cliCommand
     * @return array|false
     */
    private function cli(string $cliCommand): array|false
    {
        $cmd = "\"{$this->ffmpegPath}\" {$cliCommand}";
        exec($cmd, $out, $ret);

        if ($ret == 0) {
            if (isset($out[0])) {
                $return = $out;
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * @param string $videoClipPath
     * @return float|false
     */
    public function videoFramerate(string $videoClipPath): float|false
    {
        $val = $this->videoAnalyse($videoClipPath)['frame_rate'] ?? false;

        if ($val) {
            return floatval($val);
        } else {
            return $val;
        }
    }

    /**
     * @param string $videoClipPath
     * @return false|int
     */
    public function videoWidth(string $videoClipPath): false|int
    {
        $val = $this->videoAnalyse($videoClipPath)['width'] ?? false;

        if ($val) {
            return intval($val);
        } else {
            return $val;
        }
    }

    /**
     * @param string $videoClipPath
     * @return false|int
     */
    public function videoHeight(string $videoClipPath): false|int
    {
        $val = $this->videoAnalyse($videoClipPath)['height'] ?? false;

        if ($val) {
            return intval($val);
        } else {
            return $val;
        }
    }

    /**
     * @param string $videoClipPath
     * @return false|float
     */
    public function videoDuration(string $videoClipPath): float|false
    {
        $val = $this->videoAnalyse($videoClipPath)['duration'] ?? false;

        if ($val) {
            return floatval($val);
        } else {
            return $val;
        }
    }

    /**
     * @param string $videoClipPath
     * @param string $thumbnailOutput
     * @param float|int $timePosition
     * @return bool
     */
    public function videoThumbnail(string $videoClipPath, string $thumbnailOutput, float|int $timePosition = 0): bool
    {
        $analysis = $this->videoAnalyse($videoClipPath);
        if (!$analysis) {
            return false;
        }

        $duration = $this->videoDuration($videoClipPath);
        $lastFrameTimePosition = floor($duration); //gives a time somewhere near the end of the clip

        $timePosition = min($lastFrameTimePosition, $timePosition); //make sure the $timePosition is within the duration

        $video = $this->FFMpeg->open($videoClipPath);

        $frame = $video
            ->frame(TimeCode::fromSeconds($timePosition))
            ->save($thumbnailOutput);

        if ($frame && is_file($thumbnailOutput)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $videoClipPath
     * @return array|false
     */
    public function videoAnalyse(string $videoClipPath): false|array
    {
        if (!is_file($videoClipPath)) {
            return false;
        }

        $cacheKey = sha1($videoClipPath);
        if (isset($this->analysisCache[$cacheKey])) {
            return $this->analysisCache[$cacheKey];
        }

        $analysisData = $this->FFProbe
            ->format($videoClipPath)
            ->all();

        $videoStream = $this->FFProbe
            ->streams($videoClipPath)
            ->videos()
            ->first();

        $width = $videoStream->get('width');
        $height = $videoStream->get('height');
        $frameRate = $videoStream->get('r_frame_rate'); // returns a string like "30000/1001"
        list($num, $den) = explode('/', $frameRate);
        $frameRateFloat = $den != 0 ? floatval($num) / floatval($den) : 0.0;

        $analysisData["width"] = $width;
        $analysisData["height"] = $height;
        $analysisData["frame_rate"] = $frameRateFloat;

        $this->analysisCache[$cacheKey] = $analysisData;

        return $this->analysisCache[$cacheKey];
    }

}