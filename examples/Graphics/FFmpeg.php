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

$analysis = $ffMpegCommands->videoAnalyse($clip);
//dd($analysis);

//dump("Framerate: " . $ffMpegCommands->videoFramerate($clip));
//dump("Width: " . $ffMpegCommands->videoWidth($clip));
//dump("Height: " . $ffMpegCommands->videoHeight($clip));
//dump("Duration: " . $ffMpegCommands->videoDuration($clip));

$ffMpegCommands->videoThumbnail($clip, $screenGrabJpg, 2); //easy as most videos gor for more than 2 seconds
$ffMpegCommands->videoThumbnail($clip, $screenGrabPng, 150); //test to see if we get close to the last frame
