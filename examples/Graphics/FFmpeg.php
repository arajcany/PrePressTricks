<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use arajcany\PrePressTricks\Graphics\FFmpeg\FFmpegCommands;

require __DIR__ . '/../../vendor/autoload.php';


$clip = __DIR__ . '/../../tests/Graphics/FFmpeg/30_Seconds_Countdown.mp4';
$screenGrabJpg = __DIR__ . '/../../tests/Graphics/FFmpeg/30_Seconds_Countdown.mp4.jpg';
$screenGrabPng = __DIR__ . '/../../tests/Graphics/FFmpeg/30_Seconds_Countdown.mp4.png';

$ffMpegCommands = new FFmpegCommands();

//$info = $ffMpegCommands->videoAnalyse($clip);

dump("Framerate: " . $ffMpegCommands->videoFramerate($clip));
dump("Width: " . $ffMpegCommands->videoWidth($clip));
dump("Height: " . $ffMpegCommands->videoHeight($clip));
dump("Duration: " . $ffMpegCommands->videoDuration($clip));

//$ffMpegCommands->videoThumbnail($clip, $screenGrabJpg, 2);
$ffMpegCommands->videoThumbnail($clip, $screenGrabPng, 150);
